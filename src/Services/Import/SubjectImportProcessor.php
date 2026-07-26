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
    private array $teachingTypesByName = [];
    private array $departmentsByName = [];
    private array $cyclesByName = [];
    private array $subjectGroupsByName = [];
    private int $activeYearId;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->setActiveYear();
        $this->warmupData();
    }

    /**
     * @return array{success: bool, count: int, errors: list<string>}
     */
    public function process(string $filePath): array
    {
        try {
            $workbook = IOFactory::load($filePath);
            $this->db->beginTransaction();

            $hasData = false;
            foreach ($workbook->getAllSheets() as $sheet) {
                // Ignorer les feuilles masquées comme SUBJECT_DATASOURCES
                if ($sheet->getSheetState() !== \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VISIBLE) {
                    continue;
                }

                $sheetName = $sheet->getTitle();
                $teachingTypeId = $this->resolveTeachingTypeIdBySheetName($sheetName);
                if (!$teachingTypeId) {
                    continue; // Ignorer les feuilles qui ne correspondent pas à un type d'enseignement actif
                }

                $rows = $sheet->toArray(null, true, true, true);
                if (count($rows) < 2) {
                    continue; // Sauter les feuilles vides ou avec seulement des en-têtes
                }

                $headers = array_shift($rows);
                try {
                    $this->validateHeaders($headers);
                } catch (Exception $e) {
                    $this->errors[] = "Feuille '{$sheetName}' : " . $e->getMessage();
                    continue;
                }

                foreach ($rows as $idx => $row) {
                    $line = $idx + 2;
                    if (!$this->rowHasData($row)) {
                        continue;
                    }
                    $hasData = true;
                    $this->processRow($row, $line, $teachingTypeId, $sheetName);
                }
            }

            if (!$hasData) {
                throw new Exception('Document vide ou sans données.');
            }

            if (empty($this->errors)) {
                $this->db->commit();
            } else {
                $this->db->rollBack();
            }

            return [
                'success' => count($this->errors) === 0,
                'count' => count($this->errors) === 0 ? $this->successCount : 0,
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
        foreach (['A', 'B', 'C', 'D', 'E'] as $col) {
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
            throw new Exception('Format d\'en-tête invalide. Utilisez le modèle officiel.');
        }
    }

    private function processRow(array $row, int $line, int $teachingTypeId, string $sheetName): void
    {
        $subjectName = trim((string) ($row['A'] ?? ''));
        $cycleRaw = trim((string) ($row['B'] ?? ''));
        $departmentRaw = trim((string) ($row['C'] ?? ''));
        $coefficientRaw = trim((string) ($row['D'] ?? ''));
        $groupRaw = trim((string) ($row['E'] ?? ''));
        $classNames = $this->extractClassNames($row);

        if ($subjectName === '' || empty($classNames)) {
            $this->logError($line, "Feuille '{$sheetName}' : Matière et classes sont obligatoires.");
            return;
        }

        $coefficient = (int) ($coefficientRaw !== '' ? $coefficientRaw : 1);
        if ($coefficient < 1) {
            $coefficient = 1;
        }

        if ($cycleRaw === '') {
            $this->logError($line, "Feuille '{$sheetName}' : Le cycle est obligatoire.");
            return;
        }

        $cycleId = $this->resolveCycleId($cycleRaw, $line, $teachingTypeId, $sheetName);
        if ($cycleId === null) {
            return;
        }

        $departmentId = null;
        if ($departmentRaw !== '') {
            $departmentId = $this->resolveDepartmentId($departmentRaw, $line, $teachingTypeId, $sheetName);
            if ($departmentId === null) {
                return;
            }
        }

        $subjectGroupId = null;
        $groupe = 'Groupe 1';
        if ($groupRaw !== '') {
            $subjectGroupId = $this->resolveSubjectGroupId($groupRaw, $line, $teachingTypeId, $sheetName);
            if ($subjectGroupId === null) {
                return;
            }
            $groupe = $groupRaw;
        }

        $classIds = $this->resolveClassIds($classNames, $line, $teachingTypeId, $sheetName);
        if (empty($classIds)) {
            return;
        }

        // Valider la cohérence de cycle pour chaque classe
        $mismatchedClasses = [];
        foreach ($classNames as $className) {
            $key = mb_strtolower($className);
            if (isset($this->classesByName[$key])) {
                $classCycleId = $this->classesByName[$key]['cycle_id'];
                if ($classCycleId !== $cycleId) {
                    $mismatchedClasses[] = $className;
                }
            }
        }
        if (!empty($mismatchedClasses)) {
            $this->logError($line, "Feuille '{$sheetName}' : Les classes suivantes ne correspondent pas au cycle '{$cycleRaw}' : " . implode(', ', $mismatchedClasses));
            return;
        }

        $duplicateClassNames = $this->findDuplicateClassesForSubjectName($subjectName, $classIds);
        if (!empty($duplicateClassNames)) {
            $this->logError($line, "Feuille '{$sheetName}' : La matière '{$subjectName}' existe déjà dans les classes : " . implode(', ', $duplicateClassNames));
            return;
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO subjects (nom, coefficient, groupe, subject_group_id, teaching_type_id, department_id, status) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$subjectName, $coefficient, $groupe, $subjectGroupId, $teachingTypeId, $departmentId]);
            $subjectId = (int) $this->db->lastInsertId();

            $ins = $this->db->prepare("INSERT INTO subject_classes (subject_id, class_id, academic_year_id) VALUES (?, ?, ?)");
            foreach ($classIds as $classId) {
                $ins->execute([$subjectId, $classId, $this->activeYearId]);
            }

            $this->successCount++;
        } catch (\Throwable $e) {
            $this->logError($line, "Feuille '{$sheetName}' : Erreur base de données : " . $e->getMessage());
        }
    }

    private function resolveClassIds(array $names, int $line, int $teachingTypeId, string $sheetName): array
    {
        $ids = [];
        $missing = [];
        $mismatched = [];

        foreach ($names as $name) {
            $key = mb_strtolower($name);
            if (isset($this->classesByName[$key])) {
                $classInfo = $this->classesByName[$key];
                if ($classInfo['teaching_type_id'] === $teachingTypeId) {
                    $ids[] = $classInfo['id'];
                } else {
                    $mismatched[] = $name;
                }
            } else {
                $missing[] = $name;
            }
        }

        if (!empty($missing)) {
            $this->logError($line, "Feuille '{$sheetName}' : Classes introuvables : " . implode(', ', $missing));
            return [];
        }

        if (!empty($mismatched)) {
            $this->logError($line, "Feuille '{$sheetName}' : Les classes suivantes ne correspondent pas au Type d'Enseignement : " . implode(', ', $mismatched));
            return [];
        }

        return array_values(array_unique($ids));
    }

    private function extractClassNames(array $row): array
    {
        $names = [];
        // Colonnes F à O (Classe 1 à Classe 10)
        foreach (['F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O'] as $col) {
            $value = trim((string) ($row[$col] ?? ''));
            if ($value !== '') {
                $names[] = $value;
            }
        }

        return array_values(array_unique($names));
    }

    private function resolveTeachingTypeIdBySheetName(string $sheetName): ?int
    {
        $key = mb_strtolower(trim($sheetName));
        foreach ($this->teachingTypesByName as $name => $id) {
            // Comparaison avec troncature à 31 caractères (limite des onglets Excel)
            $truncatedName = mb_substr($name, 0, 31);
            if ($truncatedName === $key) {
                return $id;
            }
        }
        return null;
    }

    private function resolveCycleId(string $name, int $line, int $teachingTypeId, string $sheetName): ?int
    {
        $key = mb_strtolower(trim($name));
        if (isset($this->cyclesByName[$key])) {
            $cycleInfo = $this->cyclesByName[$key];
            if ($cycleInfo['teaching_type_id'] === null || $cycleInfo['teaching_type_id'] === $teachingTypeId) {
                return $cycleInfo['id'];
            } else {
                $this->logError($line, "Feuille '{$sheetName}' : Le cycle '{$name}' ne correspond pas au Type d'Enseignement de la feuille.");
                return null;
            }
        }
        $this->logError($line, "Feuille '{$sheetName}' : Cycle introuvable : " . $name);
        return null;
    }

    private function resolveDepartmentId(string $name, int $line, int $teachingTypeId, string $sheetName): ?int
    {
        $key = mb_strtolower(trim($name));
        if (isset($this->departmentsByName[$key])) {
            $deptInfo = $this->departmentsByName[$key];
            if ($deptInfo['teaching_type_id'] === null || $deptInfo['teaching_type_id'] === $teachingTypeId) {
                return $deptInfo['id'];
            } else {
                $this->logError($line, "Feuille '{$sheetName}' : Le département '{$name}' ne correspond pas au Type d'Enseignement de la feuille.");
                return null;
            }
        }
        $this->logError($line, "Feuille '{$sheetName}' : Département introuvable : " . $name);
        return null;
    }

    private function resolveSubjectGroupId(string $name, int $line, int $teachingTypeId, string $sheetName): ?int
    {
        $key = mb_strtolower(trim($name));
        if (isset($this->subjectGroupsByName[$key])) {
            $groupInfo = $this->subjectGroupsByName[$key];
            if ($groupInfo['teaching_type_id'] === null || $groupInfo['teaching_type_id'] === $teachingTypeId) {
                return $groupInfo['id'];
            } else {
                $this->logError($line, "Feuille '{$sheetName}' : Le groupe de module '{$name}' ne correspond pas au Type d'Enseignement de la feuille.");
                return null;
            }
        }
        $this->logError($line, "Feuille '{$sheetName}' : Groupe de module introuvable : " . $name);
        return null;
    }

    private function warmupData(): void
    {
        // Récupérer les classes actives et leurs attributs
        $rows = $this->db->query("
            SELECT c.id, c.nom, c.teaching_type_id, c.cycle_id 
            FROM classes c
            LEFT JOIN departments d ON c.department_id = d.id
            LEFT JOIN cycles cy ON c.cycle_id = cy.id
            LEFT JOIN sections sec ON c.section_id = sec.id
            LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id
            WHERE (c.department_id IS NULL OR d.status = 1)
              AND (c.cycle_id IS NULL OR cy.status = 1)
              AND (c.section_id IS NULL OR sec.status = 1)
              AND (c.teaching_type_id IS NULL OR tt.actif = 1)
        ")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $this->classesByName[mb_strtolower((string) $row['nom'])] = [
                'id' => (int) $row['id'],
                'teaching_type_id' => $row['teaching_type_id'] !== null ? (int) $row['teaching_type_id'] : null,
                'cycle_id' => $row['cycle_id'] !== null ? (int) $row['cycle_id'] : null
            ];
        }

        // Récupérer les types d'enseignement actifs
        $tts = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($tts as $tt) {
            $this->teachingTypesByName[mb_strtolower((string) $tt['nom'])] = (int) $tt['id'];
        }

        // Récupérer les départements actifs
        $depts = $this->db->query("
            SELECT d.id, d.nom, d.teaching_type_id 
            FROM departments d
            LEFT JOIN teaching_types tt ON d.teaching_type_id = tt.id
            WHERE d.status = 1 AND (d.teaching_type_id IS NULL OR tt.actif = 1)
        ")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($depts as $dept) {
            $this->departmentsByName[mb_strtolower((string) $dept['nom'])] = [
                'id' => (int) $dept['id'],
                'teaching_type_id' => $dept['teaching_type_id'] !== null ? (int) $dept['teaching_type_id'] : null
            ];
        }

        // Récupérer les cycles actifs
        $cycles = $this->db->query("
            SELECT cy.id, cy.nom, cy.teaching_type_id 
            FROM cycles cy
            LEFT JOIN teaching_types tt ON cy.teaching_type_id = tt.id
            WHERE cy.status = 1 AND (cy.teaching_type_id IS NULL OR tt.actif = 1)
        ")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cycles as $cy) {
            $this->cyclesByName[mb_strtolower((string) $cy['nom'])] = [
                'id' => (int) $cy['id'],
                'teaching_type_id' => $cy['teaching_type_id'] !== null ? (int) $cy['teaching_type_id'] : null
            ];
        }

        // Récupérer les groupes de modules actifs
        $groups = $this->db->query("
            SELECT sg.id, sg.libelle, sg.teaching_type_id 
            FROM subject_groups sg
            LEFT JOIN teaching_types tt ON sg.teaching_type_id = tt.id
            WHERE sg.status = 1 AND (sg.teaching_type_id IS NULL OR tt.actif = 1)
        ")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($groups as $g) {
            $this->subjectGroupsByName[mb_strtolower(trim((string) $g['libelle']))] = [
                'id' => (int) $g['id'],
                'teaching_type_id' => $g['teaching_type_id'] !== null ? (int) $g['teaching_type_id'] : null
            ];
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
