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
    private array $seenMatricules = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->warmupCache();
    }

    public function validate(string $filePath, string $lang, int $teachingTypeId): array
    {
        $this->errors = [];
        $this->validRows = [];
        $this->seenMatricules = [];

        try {
            $spreadsheet = IOFactory::load($filePath);
            $allSheets = $spreadsheet->getAllSheets();

            foreach ($allSheets as $sheet) {
                $sheetTitle = $sheet->getTitle();
                if ($sheetTitle === 'DATASOURCES') continue;

                $data = $sheet->toArray(null, true, true, true);
                if (count($data) < 1) {
                    $this->errors[] = "Feuille '{$sheetTitle}' : le fichier ne contient pas de ligne d'en-tête.";
                    continue;
                }

                // Validate headers
                $headers = array_shift($data);
                $headerMap = $this->parseHeaders($headers, $lang);

                $missingHeaders = [];
                foreach (['nom' => 'Nom', 'prenom' => 'Prénom', 'class' => 'Classe'] as $key => $label) {
                    if (empty($headerMap[$key])) {
                        $missingHeaders[] = $label;
                    }
                }
                if ($missingHeaders !== []) {
                    $presentHeaders = array_values(array_filter(array_map(static fn($value): string => trim((string) $value), $headers)));
                    $this->errors[] = "Feuille '{$sheetTitle}' : colonnes obligatoires absentes : " . implode(', ', $missingHeaders) . ". Colonnes détectées : " . ($presentHeaders ? implode(' | ', $presentHeaders) : '[aucune]') . ".";
                    continue;
                }

                if (count($data) < 2) {
                    $this->errors[] = "Feuille '{$sheetTitle}' : aucune ligne élève à importer sous les en-têtes.";
                    continue;
                }

                // Process rows
                foreach ($data as $rowIndex => $row) {
                    $line = $rowIndex + 2; // +1 for 0-index, +1 for header
                    $hasContent = count(array_filter($row, static fn($value): bool => trim((string) $value) !== '')) > 0;
                    if (!$hasContent) continue;

                    $this->validateRow($row, $line, $headerMap, $teachingTypeId, $sheetTitle);
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

    private function validateRow(array $row, int $line, array $headerMap, int $teachingTypeId, string $sheetTitle = '')
    {
        $context = $sheetTitle !== '' ? "Feuille '{$sheetTitle}', ligne {$line}" : "Ligne {$line}";
        $nom = trim($row[$headerMap['nom'] ?? 'A'] ?? '');
        $prenom = trim($row[$headerMap['prenom'] ?? 'B'] ?? '');
        $sexe = strtoupper(trim((string) ($row[$headerMap['sexe'] ?? ''] ?? '')));
        $dob = trim((string) ($row[$headerMap['date_naissance'] ?? ''] ?? ''));
        $lieuNais = trim((string) ($row[$headerMap['lieu_naissance'] ?? ''] ?? ''));
        $className = trim((string) ($row[$headerMap['class'] ?? ''] ?? ''));
        $isRedoublantRaw = strtoupper(trim((string) ($row[$headerMap['redoubl'] ?? ''] ?? '')));
        $providedMatricule = trim((string) ($row[$headerMap['matricule'] ?? ''] ?? ''));
        $parentContact = trim((string) ($row[$headerMap['parent_contact'] ?? ''] ?? ''));
        $guardianContact = trim((string) ($row[$headerMap['guardian_contact'] ?? ''] ?? ''));

        // Ignore sample row
        if ($nom === 'Ndogmo' && $prenom === 'Evarice' && strtoupper($providedMatricule) === 'MT-0001') {
            return;
        }

        if (empty($nom) || empty($prenom)) {
            $this->errors[] = "{$context} : les champs Nom et Prénom sont obligatoires (Nom='" . ($nom !== '' ? $nom : '[vide]') . "', Prénom='" . ($prenom !== '' ? $prenom : '[vide]') . "').";
            return;
        }

        if (empty($className)) {
            $this->errors[] = "{$context} : le champ Classe est obligatoire (Nom='{$nom}', Prénom='{$prenom}').";
            return;
        }

        if ($sexe !== '' && !in_array($sexe, ['M', 'F'], true)) {
            $this->errors[] = "{$context} : valeur Sexe invalide '{$sexe}'. Valeurs autorisées : M ou F (Nom='{$nom}', Prénom='{$prenom}').";
            return;
        }

        if ($dob !== '' && $this->formatDate($dob) === null) {
            $this->errors[] = "{$context} : date de naissance invalide '{$dob}'. Formats acceptés : JJ/MM/AAAA ou AAAA/MM/JJ (Nom='{$nom}', Prénom='{$prenom}').";
            return;
        }

        if ($isRedoublantRaw !== '' && !in_array($isRedoublantRaw, ['OUI', 'NON', 'YES', 'NO'], true)) {
            $this->errors[] = "{$context} : valeur Redoublant invalide '{$isRedoublantRaw}'. Valeurs acceptées : OUI/NON ou YES/NO (Nom='{$nom}', Prénom='{$prenom}').";
            return;
        }

        // Validate Class
        $normalizedClass = $this->normalizeString($className);
        if (!isset($this->cache['classes'][$normalizedClass])) {
            $this->errors[] = "{$context} : classe '{$className}' introuvable (Nom='{$nom}', Prénom='{$prenom}').";
            return;
        }

        $classId = $this->cache['classes'][$normalizedClass];
        $classTeachingTypeId = $this->cache['class_teaching_types'][$classId] ?? null;

        if ($classTeachingTypeId !== $teachingTypeId) {
            $this->errors[] = "{$context} : la classe '{$className}' (classe_id={$classId}, teaching_type_id={$classTeachingTypeId}) ne correspond pas au type d'enseignement sélectionné (teaching_type_id={$teachingTypeId}).";
            return;
        }

        // Validate Matricule
        if ($providedMatricule !== '') {
            $matriculeKey = mb_strtolower($providedMatricule, 'UTF-8');
            if (isset($this->seenMatricules[$matriculeKey])) {
                $this->errors[] = "{$context} : matricule '{$providedMatricule}' dupliqué dans le fichier; il apparaît déjà à la ligne {$this->seenMatricules[$matriculeKey]}.";
                return;
            }
            $this->seenMatricules[$matriculeKey] = $line;

            $chk = $this->db->prepare("SELECT COUNT(*) FROM students WHERE email = ?");
            $chk->execute([$providedMatricule]);
            if ((int) $chk->fetchColumn() > 0) {
                $this->errors[] = "{$context} : matricule '{$providedMatricule}' déjà utilisé pour Nom='{$nom}', Prénom='{$prenom}'.";
                return;
            }
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
