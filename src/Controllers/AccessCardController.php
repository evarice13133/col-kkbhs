<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\PermissionManager;
use App\Core\Session;
use App\Services\SettingsStore;
use PDO;

class AccessCardController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();

        if (!Session::isLogged()) {
            header('Location: /login');
            exit;
        }

        PermissionManager::requirePermission('manage_bulletins');
    }

    public function index(): void
    {
        $academicYears = $this->db->query("SELECT id, nom, is_active FROM academic_years ORDER BY id DESC")
            ->fetchAll(PDO::FETCH_ASSOC);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        if ($academicYearId <= 0) {
            $activeYear = $this->getActiveAcademicYear();
            $academicYearId = (int) ($activeYear['id'] ?? 0);
        }

        $teachingTypes = $this->db->query("SELECT id, code, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")
            ->fetchAll(PDO::FETCH_ASSOC);
        $teachingTypeId = (int) ($_GET['teaching_type_id'] ?? 0);
        $classId = (int) ($_GET['class_id'] ?? 0);

        if ($classId > 0 && $teachingTypeId <= 0) {
            $stmt = $this->db->prepare('SELECT teaching_type_id FROM classes WHERE id = ?');
            $stmt->execute([$classId]);
            $teachingTypeId = (int) ($stmt->fetchColumn() ?: 0);
        }
        if ($teachingTypeId <= 0 && !empty($teachingTypes)) {
            $teachingTypeId = (int) $teachingTypes[0]['id'];
        }

        $classes = $this->getClasses($academicYearId, $teachingTypeId);
        if (!in_array($classId, array_map('intval', array_column($classes, 'id')), true)) {
            $classId = 0;
        }
        $students = $classId > 0 ? $this->getEnrolledStudents($classId, $academicYearId) : [];

        include __DIR__ . '/../Views/id_card/access_carte/index.php';
    }

    public function print(): void
    {
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        $teachingTypeId = (int) ($_GET['teaching_type_id'] ?? 0);
        $classId = (int) ($_GET['class_id'] ?? 0);
        $studentId = (int) ($_GET['student_id'] ?? 0);

        $yearStmt = $this->db->prepare('SELECT id, nom FROM academic_years WHERE id = ? LIMIT 1');
        $yearStmt->execute([$academicYearId]);
        $activeYear = $yearStmt->fetch(PDO::FETCH_ASSOC);

                $classStmt = $this->db->prepare("SELECT c.id, c.nom, c.teaching_type_id
                                                                                 FROM classes c
                                                                                 LEFT JOIN teaching_types tt ON tt.id = c.teaching_type_id
                                                                                 LEFT JOIN cycles cy ON cy.id = c.cycle_id
                                                                                 LEFT JOIN sections sec ON sec.id = c.section_id
                                                                                 LEFT JOIN departments d ON d.id = c.department_id
                                                                                 WHERE c.id = ? AND c.status = 1
                                                                                     AND (c.teaching_type_id IS NULL OR tt.actif = 1)
                                                                                     AND (c.cycle_id IS NULL OR cy.status = 1)
                                                                                     AND (c.section_id IS NULL OR sec.status = 1)
                                                                                     AND (c.department_id IS NULL OR d.status = 1)");
        $classStmt->execute([$classId]);
        $class = $classStmt->fetch(PDO::FETCH_ASSOC);

        if (!$activeYear || !$class || $academicYearId <= 0 || $classId <= 0
            || ($teachingTypeId > 0 && (int) $class['teaching_type_id'] !== $teachingTypeId)) {
            header('Location: /access-cards');
            exit;
        }

        $students = $this->getEnrolledStudents($classId, $academicYearId, $studentId);
        if (empty($students)) {
            header('Location: /access-cards?' . http_build_query([
                'academic_year_id' => $academicYearId,
                'teaching_type_id' => $teachingTypeId,
                'class_id' => $classId,
            ]));
            exit;
        }

        $settingsStore = new SettingsStore($this->db, $teachingTypeId);
        $institution = $settingsStore->all($teachingTypeId);
        $pages = array_chunk($students, 9);

        include __DIR__ . '/../Views/id_card/access_carte/print.php';
    }

    private function getActiveAcademicYear(): array
    {
        $stmt = $this->db->query('SELECT id, nom FROM academic_years WHERE is_active = 1 LIMIT 1');
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['id' => 0, 'nom' => ''];
    }

    private function getClasses(int $academicYearId, int $teachingTypeId): array
    {
        $sql = "SELECT DISTINCT c.id, c.nom, c.teaching_type_id
                FROM classes c
                LEFT JOIN teaching_types tt ON tt.id = c.teaching_type_id
                                LEFT JOIN cycles cy ON cy.id = c.cycle_id
                                LEFT JOIN sections sec ON sec.id = c.section_id
                                LEFT JOIN departments d ON d.id = c.department_id
                WHERE c.status = 1
                  AND (c.teaching_type_id IS NULL OR tt.actif = 1)
                                    AND (c.cycle_id IS NULL OR cy.status = 1)
                                    AND (c.section_id IS NULL OR sec.status = 1)
                                    AND (c.department_id IS NULL OR d.status = 1)
                  AND EXISTS (
                      SELECT 1
                      FROM enrollments e
                      JOIN students s ON s.id = e.student_id
                      WHERE e.class_id = c.id
                        AND e.academic_year_id = ?
                        AND s.status = 'Inscrit'
                        AND s.actif = 1
                        AND s.is_withdrawn = 0
                  )";
        $params = [$academicYearId];
        if ($teachingTypeId > 0) {
            $sql .= ' AND c.teaching_type_id = ?';
            $params[] = $teachingTypeId;
        }
        $sql .= ' ORDER BY c.nom ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getEnrolledStudents(int $classId, int $academicYearId, int $studentId = 0): array
    {
        $sql = "SELECT s.*, c.nom AS class_name
                FROM enrollments e
                JOIN students s ON s.id = e.student_id
                JOIN classes c ON c.id = e.class_id
                WHERE e.class_id = ?
                  AND e.academic_year_id = ?
                  AND s.status = 'Inscrit'
                  AND s.actif = 1
                  AND s.is_withdrawn = 0";
        $params = [$classId, $academicYearId];
        if ($studentId > 0) {
            $sql .= ' AND s.id = ?';
            $params[] = $studentId;
        }
        $sql .= ' ORDER BY s.nom ASC, s.prenom ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}