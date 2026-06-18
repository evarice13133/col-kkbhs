<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PDO;

class StudentImportValidator
{
    private PDO $db;
    private array $errors = [];
    private array $validRows = [];
    private array $cache = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->warmupCache();
    }

    public function validate(string $filePath, string $lang, int $teachingTypeId): array
    {
        $this->errors = [];
        $this->validRows = [];

        try {
            $spreadsheet = IOFactory::load($filePath);
            $allSheets = $spreadsheet->getAllSheets();

            foreach ($allSheets as $sheet) {
                $sheetTitle = $sheet->getTitle();
                if ($sheetTitle === 'DATASOURCES') continue;

                $data = $sheet->toArray(null, true, true, true);
                if (count($data) < 2) continue;

                // Validate headers
                $headers = array_shift($data);
                $headerMap = $this->parseHeaders($headers, $lang);

                if (empty($headerMap['nom']) || empty($headerMap['prenom'])) {
                    $this->errors[] = "Feuille '{$sheetTitle}' : Format d'en-tête invalide.";
                    continue;
                }

                // Process rows
                foreach ($data as $rowIndex => $row) {
                    $line = $rowIndex + 2; // +1 for 0-index, +1 for header
                    if (empty(trim($row['A'] ?? ''))) continue;

                    $this->validateRow($row, $line, $headerMap, $teachingTypeId);
                }
            }

        } catch (\Exception $e) {
            $this->errors[] = "Erreur de lecture du fichier : " . $e->getMessage();
        }

        return [
            'isValid' => count($this->errors) === 0,
            'errors' => $this->errors,
            'validRows' => $this->validRows
        ];
    }

    private function parseHeaders(array $headers, string $lang): array
    {
        $headerMap = [];
        foreach ($headers as $col => $text) {
            $norm = mb_strtolower(trim((string) $text), 'UTF-8');
            if ($norm === '') continue;

            if (str_contains($norm, 'matric') || str_contains($norm, 'student id') || str_contains($norm, 'id')) {
                $headerMap['matricule'] = $col;
            } elseif (str_contains($norm, 'prénom') || str_contains($norm, 'prenom') || str_contains($norm, 'first')) {
                $headerMap['prenom'] = $col;
            } elseif (str_contains($norm, 'nom') || str_contains($norm, 'last')) {
                $headerMap['nom'] = $col;
            } elseif (str_contains($norm, 'sexe') || str_contains($norm, 'gender')) {
                $headerMap['sexe'] = $col;
            } elseif (str_contains($norm, 'date')) {
                $headerMap['date_naissance'] = $col;
            } elseif (str_contains($norm, 'lieu') || str_contains($norm, 'place')) {
                $headerMap['lieu_naissance'] = $col;
            } elseif (str_contains($norm, 'classe') || str_contains($norm, 'class')) {
                $headerMap['class'] = $col;
            } elseif (str_contains($norm, 'redoubl') || str_contains($norm, 'repeating')) {
                $headerMap['redoubl'] = $col;
            } elseif (str_contains($norm, 'père') || str_contains($norm, 'pere') || str_contains($norm, 'parent') || str_contains($norm, 'mère') || str_contains($norm, 'mere')) {
                $headerMap['parent_contact'] = $col;
            } elseif (str_contains($norm, 'tuteur') || str_contains($norm, 'guardian')) {
                $headerMap['guardian_contact'] = $col;
            }
        }
        return $headerMap;
    }

    private function validateRow(array $row, int $line, array $headerMap, int $teachingTypeId)
    {
        $nom = trim($row[$headerMap['nom'] ?? 'A'] ?? '');
        $prenom = trim($row[$headerMap['prenom'] ?? 'B'] ?? '');
        $sexe = strtoupper(trim($row[$headerMap['sexe'] ?? 'C'] ?? ''));
        $dob = trim($row[$headerMap['date_naissance'] ?? 'D'] ?? '');
        $lieuNais = trim($row[$headerMap['lieu_naissance'] ?? 'E'] ?? '');
        $className = trim($row[$headerMap['class'] ?? 'F'] ?? '');
        $isRedoublantRaw = strtoupper(trim($row[$headerMap['redoubl'] ?? 'H'] ?? ''));
        $providedMatricule = trim($row[$headerMap['matricule'] ?? 'C'] ?? '');
        $parentContact = trim($row[$headerMap['parent_contact'] ?? 'I'] ?? '');
        $guardianContact = trim($row[$headerMap['guardian_contact'] ?? 'J'] ?? '');

        // Ignore sample row
        if ($nom === 'Ndogmo' && $prenom === 'Evarice' && strtoupper($providedMatricule) === 'MT-0001') {
            return;
        }

        if (empty($nom) || empty($prenom)) {
            $this->errors[] = "Ligne {$line} : Nom et Prénom sont requis.";
            return;
        }

        if (empty($className)) {
            $this->errors[] = "Ligne {$line} : La classe est obligatoire.";
            return;
        }

        // Validate Class
        $normalizedClass = $this->normalizeString($className);
        if (!isset($this->cache['classes'][$normalizedClass])) {
            $this->errors[] = "Ligne {$line} : Classe '{$className}' introuvable dans le système.";
            return;
        }

        $classId = $this->cache['classes'][$normalizedClass];
        $classTeachingTypeId = $this->cache['class_teaching_types'][$classId] ?? null;

        if ($classTeachingTypeId !== $teachingTypeId) {
            $this->errors[] = "Ligne {$line} : La classe '{$className}' n'appartient pas au type d'enseignement sélectionné.";
            return;
        }

        // Validate Matricule
        if ($providedMatricule !== '') {
            $chk = $this->db->prepare("SELECT COUNT(*) FROM students WHERE email = ?");
            $chk->execute([$providedMatricule]);
            if ((int) $chk->fetchColumn() > 0) {
                $this->errors[] = "Ligne {$line} : Matricule '{$providedMatricule}' déjà utilisé.";
                return;
            }
        }

        if (!in_array($sexe, ['M', 'F'])) {
            $sexe = null;
        }

        $isRedoublant = in_array($isRedoublantRaw, ['OUI', 'YES']) ? 1 : 0;

        // Add to valid rows
        $this->validRows[] = [
            'line' => $line,
            'nom' => strtoupper($nom),
            'prenom' => $prenom,
            'matricule' => $providedMatricule,
            'class_id' => $classId,
            'teaching_type_id' => $teachingTypeId,
            'sexe' => $sexe,
            'date_naissance' => $this->formatDate($dob),
            'lieu_naissance' => $lieuNais,
            'is_redoublant' => $isRedoublant,
            'parent_contact' => $parentContact,
            'guardian_contact' => $guardianContact
        ];
    }

    private function warmupCache()
    {
        $stmt = $this->db->query("SELECT id, nom, teaching_type_id FROM classes");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->cache['classes'][$this->normalizeString($row['nom'])] = (int) $row['id'];
            $this->cache['class_teaching_types'][(int) $row['id']] = $row['teaching_type_id'] ? (int) $row['teaching_type_id'] : null;
        }
    }

    private function normalizeString(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/\s+/', ' ', $value);
        $value = mb_strtolower($value, 'UTF-8');
        return $value;
    }

    private function formatDate($value): ?string
    {
        if (empty($value)) return null;

        if (is_numeric($value)) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$value);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        $value = trim((string)$value);
        $value = str_replace('-', '/', $value);

        $dateISO = \DateTime::createFromFormat('Y/m/d', $value);
        if ($dateISO && $dateISO->format('Y/m/d') === $value) {
            return $dateISO->format('Y-m-d');
        }

        $dateFR = \DateTime::createFromFormat('d/m/Y', $value);
        if ($dateFR) {
            return $dateFR->format('Y-m-d');
        }

        return null;
    }
}
