<?php

namespace App\Services\Import;

use Exception;
use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SubjectImportProcessor
{
    private PDO $db;
    private array $errors = [];
    private int $successCount = 0;
    private array $classesByName = [];
    private int $activeYearId;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->setActiveYear();
        $this->warmupClasses();
    }

    /**
     * @return array{success: bool, count: int, errors: list<string>}
     */
    public function process(string $filePath): array
    {
        try {
            $sheet = IOFactory::load($filePath)->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
            if (count($rows) < 2) {
                throw new Exception('Document vide ou sans donnees.');
            }

            $headers = array_shift($rows);
            $this->validateHeaders($headers);

            $this->db->beginTransaction();

            foreach ($rows as $idx => $row) {
                $line = $idx + 2;
                if (!$this->rowHasData($row)) {
                    continue;
                }
                $this->processRow($row, $line);
            }

            if (empty($this->errors)) {
                $this->db->commit();
            } else {
                $this->db->rollBack();
            }

            return [
                'success' => count($this->errors) === 0,
                'count' => $this->successCount,
                'errors' => $this->errors,
            ];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return [
                'success' => false,
                'count' => 0,
                'errors' => ['Erreur fatale : ' . $e->getMessage()],
            ];
        }
    }

    private function rowHasData(array $row): bool
    {
        foreach (['A', 'B', 'C'] as $col) {
            if (trim((string) ($row[$col] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    private function validateHeaders(array $headers): void
    {
        $first = strtolower(trim((string) ($headers['A'] ?? '')));
        if ($first === '' || (!str_contains($first, 'mati') && !str_contains($first, 'subject'))) {
            throw new Exception('Format d\'en-tete invalide. Utilisez le modele officiel.');
        }
    }

    private function processRow(array $row, int $line): void
    {
        $subjectName = trim((string) ($row['A'] ?? ''));
        $coefficientRaw = trim((string) ($row['B'] ?? ''));
        $groupRaw = trim((string) ($row['C'] ?? 'Groupe 1'));
        $classNames = $this->extractClassNames($row);

        if ($subjectName === '' || empty($classNames)) {
            $this->logError($line, 'Matiere et classes sont obligatoires.');
            return;
        }

        $coefficient = (int) ($coefficientRaw !== '' ? $coefficientRaw : 1);
        if ($coefficient < 1) {
            $coefficient = 1;
        }

        // Nettoyage du groupe (ex: "Groupe 1 - ..." -> "Groupe 1")
        $groupe = 'Groupe 1';
        if (preg_match('/(Groupe\s*\d+)/i', $groupRaw, $matches)) {
            $groupe = ucwords(strtolower($matches[1]));
        }

        $classIds = $this->resolveClassIds($classNames, $line);
        if (empty($classIds)) {
            return;
        }

        $duplicateClassNames = $this->findDuplicateClassesForSubjectName($subjectName, $classIds);
        if (!empty($duplicateClassNames)) {
            $this->logError($line, "La matiere existe deja dans: " . implode(', ', $duplicateClassNames));
            return;
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO subjects (nom, coefficient, groupe, status) VALUES (?, ?, ?, 1)");
            $stmt->execute([$subjectName, $coefficient, $groupe]);
            $subjectId = (int) $this->db->lastInsertId();

            $ins = $this->db->prepare("INSERT INTO subject_classes (subject_id, class_id, academic_year_id) VALUES (?, ?, ?)");
            foreach ($classIds as $classId) {
                $ins->execute([$subjectId, $classId, $this->activeYearId]);
            }

            $this->successCount++;
        } catch (\Throwable $e) {
            $this->logError($line, 'Erreur base de donnees : ' . $e->getMessage());
        }
    }

    private function resolveClassIds(array $names, int $line): array
    {
        $ids = [];
        $missing = [];

        foreach ($names as $name) {
            $key = mb_strtolower($name);
            if (isset($this->classesByName[$key])) {
                $ids[] = (int) $this->classesByName[$key];
            } else {
                $missing[] = $name;
            }
        }

        if (!empty($missing)) {
            $this->logError($line, "Classes introuvables: " . implode(', ', $missing));
            return [];
        }

        return array_values(array_unique($ids));
    }

    private function extractClassNames(array $row): array
    {
        $names = [];

        // Nouveau format template : plusieurs colonnes de classes (D à M).
        foreach (['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M'] as $col) {
            $value = trim((string) ($row[$col] ?? ''));
            if ($value !== '') {
                $names[] = $value;
            }
        }

        // Pas de compatibilité virgule pour C car c'est maintenant le Groupe.

        return array_values(array_unique($names));
    }

    private function warmupClasses(): void
    {
        $rows = $this->db->query("SELECT id, nom FROM classes")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $this->classesByName[mb_strtolower((string) $row['nom'])] = (int) $row['id'];
        }
    }

    private function setActiveYear(): void
    {
        $stmt = $this->db->prepare("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1");
        $stmt->execute();
        $this->activeYearId = (int) $stmt->fetchColumn();
    }

    private function findDuplicateClassesForSubjectName(string $name, array $classIds): array
    {
        if ($name === '' || empty($classIds)) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($classIds), '?'));
        $sql = "SELECT DISTINCT c.nom
                FROM subject_classes sc
                JOIN subjects s ON s.id = sc.subject_id
                JOIN classes c ON c.id = sc.class_id
                WHERE sc.class_id IN ($placeholders)
                  AND LOWER(TRIM(s.nom)) = LOWER(TRIM(?))
                ORDER BY c.nom ASC";
        $params = array_merge($classIds, [$name]);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private function logError(int $line, string $message): void
    {
        $this->errors[] = "Ligne {$line} : {$message}";
    }
}
