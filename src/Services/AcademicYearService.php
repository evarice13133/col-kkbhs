<?php

namespace App\Services;

use PDO;

/**
 * AcademicYearService
 * 
 * Centralized service for managing academic year operations.
 * Provides helper methods to get the active year and filter data by year.
 */
class AcademicYearService
{
    private $db;
    private $activeYearCache = null;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get the currently active academic year
     * 
     * @return array|null Returns the active year array or null if none exists
     */
    public function getActiveYear(): ?array
    {
        if ($this->activeYearCache !== null) {
            return $this->activeYearCache;
        }

        $stmt = $this->db->query("SELECT id, nom, start_date, end_date, is_active, status FROM academic_years WHERE is_active = 1 LIMIT 1");
        $year = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->activeYearCache = $year ?: null;
        return $this->activeYearCache;
    }

    /**
     * Get the ID of the currently active academic year
     * 
     * @return int Returns the active year ID or 0 if none exists
     */
    public function getActiveYearId(): int
    {
        $year = $this->getActiveYear();
        return (int) ($year['id'] ?? 0);
    }

    /**
     * Get all academic years ordered by ID descending
     * 
     * @return array List of all academic years
     */
    public function getAllYears(): array
    {
        $stmt = $this->db->query("SELECT id, nom, start_date, end_date, is_active, status FROM academic_years ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a specific academic year by ID
     * 
     * @param int $yearId The academic year ID
     * @return array|null The year data or null if not found
     */
    public function getYearById(int $yearId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM academic_years WHERE id = ?");
        $stmt->execute([$yearId]);
        $year = $stmt->fetch(PDO::FETCH_ASSOC);
        return $year ?: null;
    }

    /**
     * Activate a specific academic year (deactivates all others)
     * 
     * @param int $yearId The academic year ID to activate
     * @return bool True on success, false on failure
     */
    public function activateYear(int $yearId): bool
    {
        try {
            $this->db->beginTransaction();
            
            // Deactivate all years
            $this->db->query("UPDATE academic_years SET is_active = FALSE");
            
            // Activate the specified year
            $stmt = $this->db->prepare("UPDATE academic_years SET is_active = TRUE WHERE id = ? AND status != 'archived'");
            $stmt->execute([$yearId]);
            
            $this->db->commit();
            
            // Clear cache
            $this->activeYearCache = null;
            
            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    /**
     * Create a new academic year
     * 
     * @param string $nom The year name (e.g., "2026-2027")
     * @param string|null $startDate Optional start date
     * @param string|null $endDate Optional end date
     * @return int|false The new year ID or false on failure
     */
    public function createYear(string $nom, ?string $startDate = null, ?string $endDate = null)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO academic_years (nom, start_date, end_date, status) VALUES (?, ?, ?, 'active')");
            $stmt->execute([$nom, $startDate, $endDate]);
            return (int) $this->db->lastInsertId();
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Check if a year can be safely deleted (no associated data)
     * 
     * @param int $yearId The academic year ID
     * @return array Returns ['can_delete' => bool, 'reason' => string]
     */
    public function canDeleteYear(int $yearId): array
    {
        $tables = [
            'students' => 'students',
            'grades' => 'grades',
            'classes' => 'classes',
            'teacher_assignments' => 'teacher assignments',
            'discipline' => 'discipline records'
        ];

        foreach ($tables as $table => $label) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM $table WHERE academic_year_id = ?");
            $stmt->execute([$yearId]);
            $count = (int) $stmt->fetchColumn();
            
            if ($count > 0) {
                return [
                    'can_delete' => false,
                    'reason' => "Cannot delete: year has $count $label"
                ];
            }
        }

        return ['can_delete' => true, 'reason' => ''];
    }

    /**
     * Add a WHERE clause to filter by academic year
     * 
     * @param string $sql The base SQL query
     * @param array $params The query parameters
     * @param int|null $yearId The academic year ID (uses active if null)
     * @param string $tableAlias The table alias to use (default: empty string)
     * @return array Returns ['sql' => modified SQL, 'params' => modified params]
     */
    public function addYearFilter(string $sql, array $params, ?int $yearId = null, string $tableAlias = ''): array
    {
        $yearId = $yearId ?? $this->getActiveYearId();
        
        if ($yearId <= 0) {
            return ['sql' => $sql, 'params' => $params];
        }

        $prefix = $tableAlias ? $tableAlias . '.' : '';
        $sql .= " AND {$prefix}academic_year_id = ?";
        $params[] = $yearId;

        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Clone data from one year to another (for year rollover)
     * 
     * @param int $fromYearId Source year ID
     * @param int $toYearId Target year ID
     * @param array $tables Tables to clone (default: ['classes', 'subjects', 'subject_classes'])
     * @return array Returns ['success' => bool, 'message' => string, 'details' => array]
     */
    public function cloneYearData(int $fromYearId, int $toYearId, array $tables = ['classes', 'subjects', 'subject_classes']): array
    {
        $results = [];
        
        try {
            $this->db->beginTransaction();

            foreach ($tables as $table) {
                $count = 0;
                
                switch ($table) {
                    case 'classes':
                        // Clone classes with new IDs but same structure
                        $stmt = $this->db->prepare("
                            INSERT INTO classes (nom, cycle_id, section_id, department_id, main_teacher_id, academic_year_id)
                            SELECT nom, cycle_id, section_id, department_id, main_teacher_id, ? 
                            FROM classes WHERE academic_year_id = ?
                        ");
                        $stmt->execute([$toYearId, $fromYearId]);
                        $count = $stmt->rowCount();
                        break;
                        
                    case 'subjects':
                        // Subjects are structural, don't clone by year
                        $count = 0;
                        break;
                        
                    case 'subject_classes':
                        // Clone subject-class associations for the new year
                        $stmt = $this->db->prepare("
                            INSERT INTO subject_classes (subject_id, class_id, academic_year_id)
                            SELECT sc.subject_id, c.id, ?
                            FROM subject_classes sc
                            JOIN classes c ON c.nom = (
                                SELECT c2.nom FROM classes c2 WHERE c2.id = sc.class_id AND c2.academic_year_id = ?
                            )
                            WHERE sc.academic_year_id = ?
                        ");
                        $stmt->execute([$toYearId, $fromYearId, $fromYearId]);
                        $count = $stmt->rowCount();
                        break;
                }
                
                $results[$table] = $count;
            }

            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Data cloned successfully',
                'details' => $results
            ];
            
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            
            return [
                'success' => false,
                'message' => 'Clone failed: ' . $e->getMessage(),
                'details' => []
            ];
        }
    }
}
