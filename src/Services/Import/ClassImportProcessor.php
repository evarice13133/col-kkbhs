<?php

namespace App\Services\Import;

use Exception;
use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ClassImportProcessor
{
    private PDO $db;
    private array $errors = [];
    private int $successCount = 0;
    private array $cyclesByName = [];
    private array $sectionsByName = [];
    private array $departmentsByName = [];
    private array $teachingTypesByName = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->warmupLookups();
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
        if ($first === '' || (!str_contains($first, 'class') && !str_contains($first, 'classe'))) {
            throw new Exception('Format d\'en-tete invalide. Utilisez le modele officiel.');
        }
    }

    private function processRow(array $row, int $line): void
    {
        $className = trim((string) ($row['A'] ?? ''));
        $cycleName = trim((string) ($row['B'] ?? ''));
        $sectionName = trim((string) ($row['C'] ?? ''));
        $deptName = trim((string) ($row['D'] ?? ''));
        $teachingTypeName = trim((string) ($row['E'] ?? ''));

        if ($className === '') {
            $this->logError($line, 'Le nom de classe est obligatoire.');
            return;
        }

        $cycleId = $this->resolveCycleId($cycleName, $line);
        if ($cycleName !== '' && $cycleId === false) {
            return;
        }
        $sectionId = $this->resolveSectionId($sectionName, $line);
        if ($sectionName !== '' && $sectionId === false) {
            return;
        }

        $deptId = $this->resolveDepartmentId($deptName, $line);
        if ($deptName !== '' && $deptId === false) {
            return;
        }

        $teachingTypeId = $this->resolveTeachingTypeId($teachingTypeName, $line);
        if ($teachingTypeName !== '' && $teachingTypeId === false) {
            return;
        }
        if (!$teachingTypeId && $cycleId) {
            $stmtCycle = $this->db->prepare("SELECT teaching_type_id FROM cycles WHERE id = ?");
            $stmtCycle->execute([$cycleId]);
            $teachingTypeId = $stmtCycle->fetchColumn();
        }
        if (!$teachingTypeId) {
            $stmtTT = $this->db->query("SELECT id FROM teaching_types WHERE code = 'ESG' OR LOWER(nom) LIKE '%secondaire%' LIMIT 1");
            $teachingTypeId = $stmtTT ? $stmtTT->fetchColumn() : null;
            if (!$teachingTypeId) {
                $teachingTypeId = $this->db->query("SELECT id FROM teaching_types ORDER BY id ASC LIMIT 1")->fetchColumn();
            }
        }

        try {
            $stmt = $this->db->prepare("SELECT id FROM classes WHERE LOWER(TRIM(nom)) = LOWER(TRIM(?)) LIMIT 1");
            $stmt->execute([$className]);
            if ($stmt->fetchColumn()) {
                $this->logError($line, "Classe deja existante : {$className}");
                return;
            }

            $ins = $this->db->prepare("INSERT INTO classes (nom, cycle_id, section_id, department_id, teaching_type_id) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$className, $cycleId ?: null, $sectionId ?: null, $deptId ?: null, $teachingTypeId ?: null]);
            $this->successCount++;
        } catch (\Throwable $e) {
            $this->logError($line, 'Erreur base de donnees : ' . $e->getMessage());
        }
    }

    private function resolveCycleId(string $name, int $line)
    {
        if ($name === '') {
            return null;
        }
        $key = mb_strtolower($name);
        if (!isset($this->cyclesByName[$key])) {
            $this->logError($line, "Cycle introuvable : {$name}");
            return false;
        }
        return (int) $this->cyclesByName[$key];
    }

    private function resolveSectionId(string $name, int $line)
    {
        if ($name === '') {
            return null;
        }
        $key = mb_strtolower($name);
        if (!isset($this->sectionsByName[$key])) {
            $this->logError($line, "Section introuvable : {$name}");
            return false;
        }
        return (int) $this->sectionsByName[$key];
    }

    private function resolveDepartmentId(string $name, int $line)
    {
        if ($name === '') {
            return null;
        }
        $key = mb_strtolower($name);
        if (!isset($this->departmentsByName[$key])) {
            $this->logError($line, "Departement introuvable : {$name}");
            return false;
        }
        return (int) $this->departmentsByName[$key];
    }

    private function resolveTeachingTypeId(string $name, int $line)
    {
        if ($name === '') {
            return null;
        }
        $key = mb_strtolower($name);
        if (!isset($this->teachingTypesByName[$key])) {
            $this->logError($line, "Type d'enseignement introuvable : {$name}");
            return false;
        }
        return (int) $this->teachingTypesByName[$key];
    }

    private function warmupLookups(): void
    {
        $cycles = $this->db->query("SELECT id, nom FROM cycles")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cycles as $row) {
            $this->cyclesByName[mb_strtolower((string) $row['nom'])] = (int) $row['id'];
        }

        $sections = $this->db->query("SELECT id, nom FROM sections")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($sections as $row) {
            $this->sectionsByName[mb_strtolower((string) $row['nom'])] = (int) $row['id'];
        }

        $departments = $this->db->query("SELECT id, nom FROM departments")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($departments as $row) {
            $this->departmentsByName[mb_strtolower((string) $row['nom'])] = (int) $row['id'];
        }

        $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($teachingTypes as $row) {
            $this->teachingTypesByName[mb_strtolower((string) $row['nom'])] = (int) $row['id'];
            $this->teachingTypesByName[mb_strtolower((string) $row['code'])] = (int) $row['id'];
        }
    }

    private function logError(int $line, string $message): void
    {
        $this->errors[] = "Ligne {$line} : {$message}";
    }
}
