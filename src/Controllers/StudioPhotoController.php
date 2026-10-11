<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\PermissionManager;
use App\Core\Session;
use App\Services\PhotoStudioService;
use PDO;

class StudioPhotoController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();

        if (!Session::isLogged()) {
            header('Location: /login');
            exit;
        }

        if (!PermissionManager::hasPermission('manage_students') && !PermissionManager::hasPermission('view_students')) {
            PermissionManager::requirePermission('manage_students');
        }
    }

    public function index(): void
    {
        $activeYearId = $this->getActiveAcademicYearId();
        $classes = $activeYearId > 0 ? $this->getEligibleClasses($activeYearId) : [];

        include __DIR__ . '/../Views/studio_photo/index.php';
    }

    public function students(): void
    {
        header('Content-Type: application/json');
        $classId = filter_var($_GET['class_id'] ?? null, FILTER_VALIDATE_INT);
        $activeYearId = $this->getActiveAcademicYearId();

        if ($classId === false || $classId <= 0 || $activeYearId <= 0 || !$this->isEligibleClass($classId, $activeYearId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => __('studio_photo_invalid_class')]);
            exit;
        }

        $stmt = $this->db->prepare(
            "SELECT s.id, s.nom, s.prenom, s.photo_eleve
             FROM enrollments e
             JOIN students s ON s.id = e.student_id
             WHERE e.class_id = ?
               AND e.academic_year_id = ?
               AND s.status = 'Inscrit'
               AND s.actif = 1
               AND s.is_withdrawn = 0
             ORDER BY s.nom ASC, s.prenom ASC"
        );
        $stmt->execute([$classId, $activeYearId]);

        echo json_encode([
            'success' => true,
            'students' => array_map(static function (array $student): array {
                return [
                    'id' => (int) $student['id'],
                    'name' => trim((string) $student['nom'] . ' ' . (string) $student['prenom']),
                    'has_photo' => !empty($student['photo_eleve']),
                ];
            }, $stmt->fetchAll(PDO::FETCH_ASSOC)),
        ]);
        exit;
    }

    public function savePhoto(): void
    {
        header('Content-Type: application/json');
        if (!$this->verifyAjaxCsrfToken()) {
            exit;
        }

        if (!PermissionManager::hasPermission('manage_students')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => __('studio_photo_unauthorized')]);
            exit;
        }

        $studentId = filter_var($_POST['student_id'] ?? null, FILTER_VALIDATE_INT);
        $classId = filter_var($_POST['class_id'] ?? null, FILTER_VALIDATE_INT);
        $imageData = trim((string) ($_POST['image_data'] ?? ''));
        if ($studentId === false || $studentId <= 0 || $classId === false || $classId <= 0 || $imageData === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => __('studio_photo_missing_data')]);
            exit;
        }

        $activeYearId = $this->getActiveAcademicYearId();
        if ($activeYearId <= 0 || !$this->isEligibleStudent($studentId, $classId, $activeYearId)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => __('studio_photo_student_not_found')]);
            exit;
        }

        try {
            $service = new PhotoStudioService();
            $photo = $service->saveStudentPhoto($imageData, $studentId);
        } catch (\Throwable $e) {
            error_log('Studio photo save failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => __('studio_photo_save_failed')]);
            exit;
        }
        if (!$photo['success']) {
            http_response_code((int) ($photo['http_status'] ?? 400));
            echo json_encode(['success' => false, 'error' => __($photo['error'] ?? 'studio_photo_save_failed')]);
            exit;
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE students s
                 JOIN enrollments e ON e.student_id = s.id
                 SET s.photo_eleve = ?
                 WHERE s.id = ?
                   AND e.class_id = ?
                   AND e.academic_year_id = ?
                   AND s.status = 'Inscrit'
                   AND s.actif = 1
                   AND s.is_withdrawn = 0"
            );
            $stmt->execute([$photo['path'], $studentId, $classId, $activeYearId]);

            if ($stmt->rowCount() !== 1) {
                if (!$service->removeSavedStudentPhoto($photo['path'])) {
                    error_log('Studio photo could not remove an unassigned photo: ' . basename($photo['path']));
                }
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => __('studio_photo_student_not_found')]);
                exit;
            }
        } catch (\Throwable $e) {
            if (!$service->removeSavedStudentPhoto($photo['path'])) {
                error_log('Studio photo could not remove an unassigned photo: ' . basename($photo['path']));
            }
            error_log('Studio photo database update failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => __('studio_photo_assignment_failed')]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'message' => __('studio_photo_saved'),
            'path' => $photo['path'],
        ]);
        exit;
    }

    private function getActiveAcademicYearId(): int
    {
        return (int) $this->db->query(
            'SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1'
        )->fetchColumn();
    }

    private function getEligibleClasses(int $activeYearId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.id, c.nom, tt.nom AS teaching_type_name, COUNT(DISTINCT s.id) AS student_count
             FROM classes c
             JOIN teaching_types tt ON tt.id = c.teaching_type_id AND tt.actif = 1
             LEFT JOIN cycles cy ON cy.id = c.cycle_id
             LEFT JOIN sections sec ON sec.id = c.section_id
             LEFT JOIN departments d ON d.id = c.department_id
             JOIN enrollments e ON e.class_id = c.id AND e.academic_year_id = ?
             JOIN students s ON s.id = e.student_id
             WHERE c.status = 1
               AND (c.cycle_id IS NULL OR cy.status = 1)
               AND (c.section_id IS NULL OR sec.status = 1)
               AND (c.department_id IS NULL OR d.status = 1)
               AND s.status = 'Inscrit'
               AND s.actif = 1
               AND s.is_withdrawn = 0
             GROUP BY c.id, c.nom, tt.nom, tt.position
             ORDER BY tt.position ASC, tt.nom ASC, c.nom ASC"
        );
        $stmt->execute([$activeYearId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function isEligibleClass(int $classId, int $activeYearId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1
             FROM classes c
             JOIN teaching_types tt ON tt.id = c.teaching_type_id AND tt.actif = 1
             LEFT JOIN cycles cy ON cy.id = c.cycle_id
             LEFT JOIN sections sec ON sec.id = c.section_id
             LEFT JOIN departments d ON d.id = c.department_id
             JOIN enrollments e ON e.class_id = c.id AND e.academic_year_id = ?
             JOIN students s ON s.id = e.student_id
             WHERE c.id = ?
               AND c.status = 1
               AND (c.cycle_id IS NULL OR cy.status = 1)
               AND (c.section_id IS NULL OR sec.status = 1)
               AND (c.department_id IS NULL OR d.status = 1)
               AND s.status = 'Inscrit'
               AND s.actif = 1
               AND s.is_withdrawn = 0
             LIMIT 1"
        );
        $stmt->execute([$activeYearId, $classId]);
        return (bool) $stmt->fetchColumn();
    }

    private function isEligibleStudent(int $studentId, int $classId, int $activeYearId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1
             FROM enrollments e
             JOIN students s ON s.id = e.student_id
             WHERE e.student_id = ?
               AND e.class_id = ?
               AND e.academic_year_id = ?
               AND s.status = 'Inscrit'
               AND s.actif = 1
               AND s.is_withdrawn = 0
             LIMIT 1"
        );
        $stmt->execute([$studentId, $classId, $activeYearId]);
        return (bool) $stmt->fetchColumn() && $this->isEligibleClass($classId, $activeYearId);
    }

    private function verifyAjaxCsrfToken(): bool
    {
        if (Session::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            return true;
        }

        http_response_code(403);
        echo json_encode(['success' => false, 'error' => __('studio_photo_session_expired')]);
        return false;
    }
}
