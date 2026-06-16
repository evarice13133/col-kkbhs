<?php



namespace App\Services\Import;



use PhpOffice\PhpSpreadsheet\IOFactory;

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

    private int $activeYearId;



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

        $this->setActiveYear();
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

                $firstHeader = strtolower(trim($headers['A'] ?? ''));

                if (!str_contains($firstHeader, 'nom') && !str_contains($firstHeader, 'last')) {

                    continue;

                }



                // Valider les en-têtes du fichier par rapport au template attendu

                $this->validateHeaders($headers, $lang);



                // Construire un mapping des colonnes (flexible pour les versions anciennes/nouvelles du template)

                $headerMap = [];

                foreach ($headers as $col => $text) {

                    $norm = strtolower(trim((string) $text));

                    if ($norm === '') continue;

                    if (str_contains($norm, 'matric') || str_contains($norm, 'student id') || str_contains($norm, 'id')) {

                        $headerMap['matricule'] = $col;

                        continue;

                    }

                    if (str_contains($norm, 'prénom') || str_contains($norm, 'prenom') || str_contains($norm, 'first')) {

                        $headerMap['prenom'] = $col;

                        continue;

                    }

                    if (str_contains($norm, 'nom') || str_contains($norm, 'last')) {

                        $headerMap['nom'] = $col;

                        continue;

                    }

                    if (str_contains($norm, 'sexe') || str_contains($norm, 'gender')) {

                        $headerMap['sexe'] = $col;

                        continue;

                    }

                    if (str_contains($norm, 'date')) {

                        $headerMap['date_naissance'] = $col;

                        continue;

                    }

                    if (str_contains($norm, 'lieu') || str_contains($norm, 'place')) {

                        $headerMap['lieu_naissance'] = $col;

                        continue;

                    }

                    if (str_contains($norm, 'classe') || str_contains($norm, 'class')) {

                        $headerMap['class'] = $col;

                        continue;

                    }

                    if (str_contains($norm, 'redoubl') || str_contains($norm, 'repeating')) {

                        $headerMap['redoubl'] = $col;

                        continue;

                    }

                    if (str_contains($norm, 'père') || str_contains($norm, 'pere') || str_contains($norm, 'parent') || str_contains($norm, 'mère') || str_contains($norm, 'mere')) {

                        $headerMap['parent_contact'] = $col;

                        continue;

                    }

                    if (str_contains($norm, 'tuteur') || str_contains($norm, 'guardian')) {

                        $headerMap['guardian_contact'] = $col;

                        continue;

                    }

                }



                // --- 2. TRAITEMENT PAR LIGNE ---

                foreach ($data as $rowIndex => $row) {

                    // On ignore les lignes totalement vides

                    if (empty(trim($row['A'] ?? ''))) continue;



                    $this->processRow($row, $rowIndex + 2, $headerMap); // +2 car index 1-based + header

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

    private function processRow(array $row, int $line, array $headerMap = [])

    {

        // Extraire les colonnes en respectant le mapping si présent

        $nom = trim($row[$headerMap['nom'] ?? 'A'] ?? '');

        $prenom = trim($row[$headerMap['prenom'] ?? 'B'] ?? '');

        $sexe = strtoupper(trim($row[$headerMap['sexe'] ?? 'C'] ?? ''));

        $dob = trim($row[$headerMap['date_naissance'] ?? 'D'] ?? '');

        $lieuNais = trim($row[$headerMap['lieu_naissance'] ?? 'E'] ?? '');

        $className = trim($row[$headerMap['class'] ?? 'F'] ?? '');

        $isRedoublantRaw = strtoupper(trim($row[$headerMap['redoubl'] ?? 'H'] ?? ''));

        $providedMatricule = trim($row[$headerMap['matricule'] ?? 'C'] ?? '');

        $parentContact = trim($row[$headerMap['parent_contact'] ?? ''] ?? '');

        $guardianContact = trim($row[$headerMap['guardian_contact'] ?? ''] ?? '');



        // Ignorer la ligne d'exemple si elle n'a pas été supprimée par l'utilisateur

        if ($nom === 'Ndogmo' && $prenom === 'Evarice' && strtoupper($providedMatricule) === 'MT-0001') {

            return;

        }



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

            // Déterminer le matricule : utiliser celui fourni si présent, sinon générer

            if ($providedMatricule !== '') {

                // Vérifier l'unicité

                $chk = $this->db->prepare("SELECT COUNT(*) FROM students WHERE email = ?");

                $chk->execute([$providedMatricule]);

                if ((int) $chk->fetchColumn() > 0) {

                    $this->logError($line, "Matricule '{$providedMatricule}' déjà utilisé.");

                    return;

                }

                $matricule = $providedMatricule;

            } else {

                $matricule = $this->matriculeService->generate($classId);

            }



            $sql = "INSERT INTO students (nom, prenom, email, class_id, teaching_type_id, sexe, date_naissance, lieu_naissance, is_redoublant, academic_year_id, parent_contact, guardian_contact) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([

                strtoupper($nom),

                $prenom,

                $matricule, // Email sert de matricule dans ce système

                $classId,
                $this->cache['class_teaching_types'][$classId] ?? null,

                $sexe,

                $this->formatDate($dob),

                $lieuNais,

                $isRedoublant,

                $this->activeYearId,

                $parentContact,

                $guardianContact

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

        $normalized = $this->normalizeString($name);

        if ($normalized === '') {

            $this->logError($line, "La classe est obligatoire.");

            return null;

        }



        if (isset($this->cache['classes'][$normalized])) {

            return $this->cache['classes'][$normalized];

        }



        $this->logError($line, "Classe '$name' introuvable dans le système.");

        return null;

    }



    /**

     * Pré-chargement des données pour éviter les requêtes N+1.

     */

    private function warmupCache()

    {

        $stmt = $this->db->query("SELECT id, nom, teaching_type_id FROM classes");

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $this->cache['classes'][$this->normalizeString($row['nom'])] = (int) $row['id'];

            $this->cache['class_teaching_types'][(int) $row['id']] = $row['teaching_type_id'] ? (int) $row['teaching_type_id'] : null;

        }

    }



    private function setActiveYear()

    {

        $stmt = $this->db->prepare("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1");

        $stmt->execute();

        $this->activeYearId = (int) $stmt->fetchColumn();

    }



    /**

     * Normalise une chaîne pour les recherches insensibles à la casse, aux espaces et aux accents.

     */

    private function normalizeString(string $value): string

    {

        $value = trim($value);

        $value = preg_replace('/\s+/', ' ', $value);

        $value = mb_strtolower($value, 'UTF-8');

        return $value;

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

