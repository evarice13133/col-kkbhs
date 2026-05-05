<?php

namespace App\Services\Import;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Core\Database;
use PDO;
use Exception;

/**
 * StudentImportProcessor
 * 
 * Ce service est responsable de l'analyse, de la validation et du traitement final
 * des fichiers Excel (.xlsx) pour l'import des étudiants.
 * Il assure une décohérence forte entre l'UI et la logique de base de données.
 * 
 * @package App\Services\Import
 */
class StudentImportProcessor
{
    private PDO $db;
    private array $errors = [];
    private int $successCount = 0;
    private \App\Services\MatriculeService $matriculeService;

    // Cache pour optimiser les recherches de relations
    private array $cache = [
        'classes' => [],
        'sections' => [],
        'cycles' => []
    ];

    /**
     * Constructeur
     * 
     * @param PDO $db Connexion injectée pour la modularité
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->matriculeService = new \App\Services\MatriculeService($db);
        $this->warmupCache();
    }

    /**
     * Traite un fichier Excel d'importation.
     * 
     * @param string $filePath Chemin vers le fichier temporaire
     * @param string $lang Langue de l'import (pour validation des headers)
     * @return array Résultat de l'opération [success_count, errors]
     */
    public function process(string $filePath, string $lang = 'fr'): array
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $allSheets = $spreadsheet->getAllSheets();
            
            $this->db->beginTransaction();

            foreach ($allSheets as $sheet) {
                $sheetTitle = $sheet->getTitle();
                
                // On ignore la feuille de données cachée
                if ($sheetTitle === 'DATASOURCES') continue;

                $data = $sheet->toArray(null, true, true, true);
                
                // On ignore les feuilles vides
                if (count($data) < 2) continue;

                // --- 1. VALIDATION SOMMAIRE DES EN-TÊTES ---
                $headers = array_shift($data);
                // Si la ligne 1 ne ressemble pas à un en-tête (ex: pas de colonne Nom), on ignore
                if (!str_contains(strtolower(trim($headers['A'] ?? '')), 'nom') && 
                    !str_contains(strtolower(trim($headers['A'] ?? '')), 'last')) {
                    continue;
                }

                // --- 2. TRAITEMENT PAR LIGNE ---
                foreach ($data as $rowIndex => $row) {
                    // On ignore les lignes totalement vides
                    if (empty(trim($row['A'] ?? ''))) continue;

                    $this->processRow($row, $rowIndex + 2); // +2 car index 1-based + header
                }
            }

            if (empty($this->errors)) {
                $this->db->commit();
            } else {
                $this->db->rollBack();
            }

            return [
                'success' => count($this->errors) === 0,
                'count' => $this->successCount,
                'errors' => $this->errors
            ];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return [
                'success' => false,
                'count' => 0,
                'errors' => ["Erreur fatale : " . $e->getMessage()]
            ];
        }
    }

    /**
     * Analyse et insère une ligne d'étudiant après validation.
     */
    private function processRow(array $row, int $line)
    {
        $nom = trim($row['A'] ?? '');
        $prenom = trim($row['B'] ?? '');
        $sexe = strtoupper(trim($row['C'] ?? ''));
        $dob = trim($row['D'] ?? '');
        $lieuNais = trim($row['E'] ?? '');
        $className = trim($row['F'] ?? '');
        $isRedoublantRaw = strtoupper(trim($row['G'] ?? ''));

        // -- Validation de base --
        if (empty($nom) || empty($prenom)) {
            $this->logError($line, "Nom et Prénom sont requis.");
            return;
        }

        // -- Résolution de la classe --
        $classId = $this->resolveClass($className, $line);
        if (!$classId) return;

        // -- Validation Sexe --
        if (!in_array($sexe, ['M', 'F'])) {
            $sexe = null; 
        }

        // -- Validation Redoublant --
        $isRedoublant = in_array($isRedoublantRaw, ['OUI', 'YES']) ? 1 : 0;

        // -- Enregistrement --
        try {
            // Utilisation du service centralisé pour une unicité absolue
            $matricule = $this->matriculeService->generate($classId);

            $sql = "INSERT INTO students (nom, prenom, email, class_id, sexe, date_naissance, lieu_naissance, is_redoublant) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                strtoupper($nom),
                $prenom,
                $matricule, // Email sert de matricule dans ce système
                $classId,
                $sexe,
                $this->formatDate($dob),
                $lieuNais,
                $isRedoublant
            ]);

            $this->successCount++;
        } catch (Exception $e) {
            $this->logError($line, "Erreur base de données : " . $e->getMessage());
        }
    }

    /**
     * Vérifie que les en-têtes correspondent au template attendu.
     */
    private function validateHeaders(array $headers, string $lang)
    {
        // On vérifie au moins les deux premières colonnes pour plus de flexibilité
        $firstCol = strtolower(trim($headers['A'] ?? ''));
        if ($lang === 'fr' && !str_contains($firstCol, 'nom')) {
            throw new Exception("Format d'en-tête invalide. Utilisez le modèle officiel.");
        }
    }

    /**
     * Résout un nom de classe en ID numérique.
     */
    private function resolveClass(string $name, int $line): ?int
    {
        if (empty($name)) {
            $this->logError($line, "La classe est obligatoire.");
            return null;
        }

        if (isset($this->cache['classes'][$name])) {
            return $this->cache['classes'][$name];
        }

        $this->logError($line, "Classe '$name' introuvable dans le système.");
        return null;
    }

    /**
     * Pré-chargement des données pour éviter les requêtes N+1.
     */
    private function warmupCache()
    {
        $stmt = $this->db->query("SELECT id, nom FROM classes");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->cache['classes'][$row['nom']] = $row['id'];
        }
    }

    /**
     * Journalise une erreur de traitement.
     */
    private function logError(int $line, string $msg)
    {
        $this->errors[] = "Ligne $line : $msg";
    }

    /**
     * Formate une date depuis Excel vers MySQL.
     * Gère les formats numériques Excel ainsi que les formats textes localisés (FR/EN).
     */
    private function formatDate($value): ?string
    {
        if (empty($value)) return null;
        
        // 1. Cas d'une date numérique Excel (Très fréquent si cellule formatée Date)
        if (is_numeric($value)) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$value);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // 2. Cas d'une chaîne de caractères (Saisie manuelle ou Export CSV déguisé)
        $value = trim((string)$value);
        $value = str_replace('-', '/', $value); // Normalisation séparateurs

        // Test format ISO (YYYY/MM/DD ou YYYY-MM-DD)
        $dateISO = \DateTime::createFromFormat('Y/m/d', $value);
        if ($dateISO && $dateISO->format('Y/m/d') === $value) {
            return $dateISO->format('Y-m-d');
        }

        // Test format FR (DD/MM/YYYY)
        $dateFR = \DateTime::createFromFormat('d/m/Y', $value);
        if ($dateFR) {
            return $dateFR->format('Y-m-d');
        }

        return null;
    }

}
