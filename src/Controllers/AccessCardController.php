<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\LogoManager;
use App\Core\PermissionManager;
use App\Core\Session;
use App\Services\AccessCardPalette;
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

    public function index(bool $identityCard = false): void
    {
        $routePrefix = $identityCard ? '/identity-cards' : '/access-cards';
        $orientation = in_array($_GET['orientation'] ?? '', ['portrait', 'landscape'], true)
            ? $_GET['orientation']
            : 'landscape';
        $cardsPerPage = $identityCard
            ? ($orientation === 'portrait' ? 10 : 9)
            : ($orientation === 'portrait' ? 8 : 4);
        $palette = AccessCardPalette::fromArray($_GET['palette'] ?? []);
        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = (int) ($activeYear['id'] ?? 0);
        $classId = (int) ($_GET['class_id'] ?? 0);
        $sections = $this->getSections($academicYearId);
        $sectionId = (int) ($_GET['section_id'] ?? 0);
        if (!in_array($sectionId, array_map('intval', array_column($sections, 'id')), true)) {
            $sectionId = 0;
        }
        $classes = $sectionId > 0 ? $this->getClassesForSection($academicYearId, $sectionId) : [];
        if (!in_array($classId, array_map('intval', array_column($classes, 'id')), true)) {
            $classId = 0;
        }
        $students = $sectionId > 0
            ? $this->getEnrolledStudentsForSection($sectionId, $academicYearId, $classId)
            : [];
        $teachingTypeId = (int) ($classes[0]['teaching_type_id'] ?? 0);

        include __DIR__ . ($identityCard
            ? '/../Views/id_card/identity_card/index.php'
            : '/../Views/id_card/access_card/index.php');
    }

    public function print(bool $identityCard = false): void
    {
        $routePrefix = $identityCard ? '/identity-cards' : '/access-cards';
        $orientation = ($_GET['orientation'] ?? '') === 'portrait' ? 'portrait' : 'landscape';
        $cardsPerPage = $identityCard
            ? ($orientation === 'portrait' ? 10 : 9)
            : ($orientation === 'portrait' ? 8 : 4);
        $classId = (int) ($_GET['class_id'] ?? 0);
        $studentId = (int) ($_GET['student_id'] ?? 0);

        if ($identityCard && !isset($_GET['section_id'])) {
            $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
            $teachingTypeId = (int) ($_GET['teaching_type_id'] ?? 0);
            $yearStmt = $this->db->prepare('SELECT id, nom FROM academic_years WHERE id = ? LIMIT 1');
            $yearStmt->execute([$academicYearId]);
            $activeYear = $yearStmt->fetch(PDO::FETCH_ASSOC);
            $classStmt = $this->db->prepare("SELECT c.id, c.nom, c.teaching_type_id, cy.nom AS cycle_name, tt.nom AS teaching_type_name
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
                header('Location: ' . $routePrefix);
                exit;
            }
            $students = $this->getEnrolledStudents($classId, $academicYearId, $studentId);
            $sectionId = 0;
            $sectionName = '';
        } else {
            $activeYear = $this->getActiveAcademicYear();
            $academicYearId = (int) ($activeYear['id'] ?? 0);
            $sectionId = (int) ($_GET['section_id'] ?? 0);
            $sections = $this->getSections($academicYearId);
            $selectedSection = null;
            foreach ($sections as $availableSection) {
                if ((int) $availableSection['id'] === $sectionId) {
                    $selectedSection = $availableSection;
                    break;
                }
            }
            $classes = $sectionId > 0 ? $this->getClassesForSection($academicYearId, $sectionId) : [];
            $class = null;
            if ($classId > 0) {
                foreach ($classes as $availableClass) {
                    if ((int) $availableClass['id'] === $classId) {
                        $class = $availableClass;
                        break;
                    }
                }
            } elseif (!empty($classes)) {
                $class = $classes[0];
            }
            if (!$selectedSection || !$class || $academicYearId <= 0) {
                header('Location: ' . $routePrefix);
                exit;
            }
            $sectionName = (string) $selectedSection['nom'];
            $teachingTypeId = (int) $class['teaching_type_id'];
            $students = $this->getEnrolledStudentsForSection($sectionId, $academicYearId, $classId, $studentId);
            $class['id'] = $classId;
            $class['nom'] = $classId > 0 ? (string) $class['nom'] : $sectionName;
        }

        if (empty($students)) {
            $params = $identityCard
                ? ['academic_year_id' => $academicYearId, 'teaching_type_id' => $teachingTypeId, 'class_id' => $classId]
                : ['section_id' => $sectionId, 'class_id' => $classId];
            header('Location: ' . $routePrefix . '?' . http_build_query($params));
            exit;
        }

        $settingsStore = new SettingsStore($this->db, $teachingTypeId);
        $institution = $settingsStore->all($teachingTypeId);
        $palette = AccessCardPalette::fromArray($_GET['palette'] ?? []);
        $pages = array_chunk($students, $cardsPerPage);

        include __DIR__ . ($identityCard
            ? '/../Views/id_card/identity_card/print.php'
            : '/../Views/id_card/access_card/print.php');
    }

    public function passes(): void
    {
        $orientation = in_array($_GET['orientation'] ?? '', ['portrait', 'landscape'], true)
            ? $_GET['orientation']
            : 'landscape';
        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = (int) ($activeYear['id'] ?? 0);
        $sections = $this->getSections($academicYearId);
        $sectionId = (int) ($_GET['section_id'] ?? 0);
        if (!in_array($sectionId, array_map('intval', array_column($sections, 'id')), true)) {
            $sectionId = 0;
        }

        $cycles = $this->getPassCycles($academicYearId, $sectionId);
        $cycleId = (int) ($_GET['cycle_id'] ?? 0);
        if (!in_array($cycleId, array_map('intval', array_column($cycles, 'id')), true)) {
            $cycleId = 0;
        }

        $classes = $this->getPassClasses($academicYearId, $sectionId, $cycleId);
        $classId = (int) ($_GET['class_id'] ?? 0);
        if ($classId > 0 && !in_array($classId, array_map('intval', array_column($classes, 'id')), true)) {
            $classId = 0;
        }
        $palette = AccessCardPalette::fromArray($_GET['palette'] ?? []);

        include __DIR__ . '/../Views/id_card/passes/index.php';
    }

    public function printPasses(): void
    {
        $orientation = in_array($_GET['orientation'] ?? '', ['portrait', 'landscape'], true)
            ? $_GET['orientation']
            : 'landscape';
        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = (int) ($activeYear['id'] ?? 0);
        $sectionId = (int) ($_GET['section_id'] ?? 0);
        $classId = (int) ($_GET['class_id'] ?? 0);
        $sections = $this->getSections($academicYearId);
        if (!in_array($sectionId, array_map('intval', array_column($sections, 'id')), true)) {
            if ($sectionId > 0) {
                header('Location: /student-passes');
                exit;
            }
        }

        $cycles = $this->getPassCycles($academicYearId, $sectionId);
        $cycleId = (int) ($_GET['cycle_id'] ?? 0);
        if (!in_array($cycleId, array_map('intval', array_column($cycles, 'id')), true)) {
            if ($cycleId > 0) {
                header('Location: /student-passes');
                exit;
            }
        }

        $classes = $this->getPassClasses($academicYearId, $sectionId, $cycleId);
        if ($classId > 0) {
            $matchingClass = false;
            foreach ($classes as $availableClass) {
                if ((int) $availableClass['id'] === $classId) {
                    $matchingClass = true;
                    break;
                }
            }
            if (!$matchingClass) {
                header('Location: /student-passes');
                exit;
            }
            $classes = array_values(array_filter(
                $classes,
                static fn(array $availableClass): bool => (int) $availableClass['id'] === $classId
            ));
        }

        if ($academicYearId <= 0 || ($sectionId <= 0 && $cycleId <= 0 && $classId <= 0) || empty($classes)) {
            header('Location: /student-passes');
            exit;
        }

        $cardsPerPage = $orientation === 'portrait' ? 8 : 4;
        $palette = AccessCardPalette::fromArray($_GET['palette'] ?? []);
        $printCards = [];
        foreach ($classes as $class) {
            $teachingTypeId = (int) $class['teaching_type_id'];
            $settingsStore = new SettingsStore($this->db, $teachingTypeId);
            $institution = $settingsStore->all($teachingTypeId);
            $logoBase64 = LogoManager::getInstance($this->db, $teachingTypeId)->getLogoBase64();
            $printCards[] = [
                'class' => $class,
                'institution' => $institution,
                'logo_base64' => $logoBase64,
            ];
        }

        $printPages = array_chunk($printCards, $cardsPerPage);

        include __DIR__ . '/../Views/id_card/passes/print.php';
    }

    private function getActiveAcademicYear(): array
    {
        $stmt = $this->db->query('SELECT id, nom FROM academic_years WHERE is_active = 1 LIMIT 1');
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['id' => 0, 'nom' => ''];
    }

    private function getSections(int $academicYearId): array
    {
        $stmt = $this->db->prepare(
            "SELECT sec.id, sec.nom,
                    COUNT(DISTINCT e.student_id) AS enrolled_count
             FROM sections sec
             JOIN classes c ON c.section_id = sec.id
             JOIN teaching_types tt ON tt.id = c.teaching_type_id AND tt.actif = 1
             LEFT JOIN cycles cy ON cy.id = c.cycle_id
             LEFT JOIN departments d ON d.id = c.department_id
             JOIN enrollments e ON e.class_id = c.id AND e.academic_year_id = ?
             JOIN students s ON s.id = e.student_id
             WHERE sec.status = 1
               AND c.status = 1
               AND (c.cycle_id IS NULL OR cy.status = 1)
               AND (c.department_id IS NULL OR d.status = 1)
               AND s.status = 'Inscrit'
               AND s.actif = 1
               AND s.is_withdrawn = 0
             GROUP BY sec.id, sec.nom
             ORDER BY sec.nom ASC"
        );
        $stmt->execute([$academicYearId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getClassesForSection(int $academicYearId, int $sectionId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.id, c.nom, c.teaching_type_id, cy.nom AS cycle_name,
                    tt.nom AS teaching_type_name,
                    COUNT(DISTINCT e.student_id) AS enrolled_count
             FROM classes c
             JOIN teaching_types tt ON tt.id = c.teaching_type_id AND tt.actif = 1
             LEFT JOIN cycles cy ON cy.id = c.cycle_id
             LEFT JOIN departments d ON d.id = c.department_id
             JOIN enrollments e ON e.class_id = c.id AND e.academic_year_id = ?
             JOIN students s ON s.id = e.student_id
             WHERE c.section_id = ?
               AND c.status = 1
               AND (c.cycle_id IS NULL OR cy.status = 1)
               AND (c.department_id IS NULL OR d.status = 1)
               AND s.status = 'Inscrit'
               AND s.actif = 1
               AND s.is_withdrawn = 0
             GROUP BY c.id, c.nom, c.teaching_type_id, cy.nom, tt.nom
             ORDER BY c.nom ASC"
        );
        $stmt->execute([$academicYearId, $sectionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getPassCycles(int $academicYearId, int $sectionId = 0): array
    {
        $sql = "SELECT DISTINCT cy.id, cy.nom
                FROM cycles cy
                JOIN classes c ON c.cycle_id = cy.id
                JOIN teaching_types tt ON tt.id = c.teaching_type_id AND tt.actif = 1
                LEFT JOIN sections sec ON sec.id = c.section_id
                LEFT JOIN departments d ON d.id = c.department_id
                JOIN enrollments e ON e.class_id = c.id AND e.academic_year_id = ?
                JOIN students s ON s.id = e.student_id
                WHERE cy.status = 1
                  AND c.status = 1
                  AND (c.section_id IS NULL OR sec.status = 1)
                  AND (c.department_id IS NULL OR d.status = 1)
                  AND s.status = 'Inscrit'
                  AND s.actif = 1
                  AND s.is_withdrawn = 0";
        $params = [$academicYearId];
        if ($sectionId > 0) {
            $sql .= ' AND c.section_id = ?';
            $params[] = $sectionId;
        }
        $sql .= ' ORDER BY cy.nom ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getPassClasses(int $academicYearId, int $sectionId = 0, int $cycleId = 0): array
    {
        $sql = "SELECT c.id, c.nom, c.teaching_type_id,
                       sec.nom AS section_name, cy.nom AS cycle_name,
                       COUNT(DISTINCT e.student_id) AS enrolled_count
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
                  AND s.is_withdrawn = 0";
        $params = [$academicYearId];
        if ($sectionId > 0) {
            $sql .= ' AND c.section_id = ?';
            $params[] = $sectionId;
        }
        if ($cycleId > 0) {
            $sql .= ' AND c.cycle_id = ?';
            $params[] = $cycleId;
        }
        $sql .= ' GROUP BY c.id, c.nom, c.teaching_type_id, sec.nom, cy.nom ORDER BY c.nom ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getTeachingTypes(int $academicYearId): array
    {
        $stmt = $this->db->prepare(
            "SELECT tt.id, tt.code, tt.nom
             FROM teaching_types tt
             WHERE tt.actif = 1
               AND EXISTS (
                   SELECT 1
                   FROM classes c
                   LEFT JOIN cycles cy ON cy.id = c.cycle_id
                   LEFT JOIN sections sec ON sec.id = c.section_id
                   LEFT JOIN departments d ON d.id = c.department_id
                   WHERE c.teaching_type_id = tt.id
                     AND c.status = 1
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
                     )
               )
             ORDER BY tt.position ASC, tt.nom ASC"
        );
        $stmt->execute([$academicYearId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getClasses(int $academicYearId, int $teachingTypeId): array
    {
        $sql = "SELECT DISTINCT c.id, c.nom, c.teaching_type_id,
                       (SELECT COUNT(DISTINCT enrolled_student.id)
                        FROM enrollments enrolled
                        JOIN students enrolled_student ON enrolled_student.id = enrolled.student_id
                        WHERE enrolled.class_id = c.id
                          AND enrolled.academic_year_id = ?
                          AND enrolled_student.status = 'Inscrit'
                          AND enrolled_student.actif = 1
                          AND enrolled_student.is_withdrawn = 0) AS enrolled_count
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
        $params = [$academicYearId, $academicYearId];
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

    private function getEnrolledStudentsForSection(
        int $sectionId,
        int $academicYearId,
        int $classId = 0,
        int $studentId = 0
    ): array {
        $sql = "SELECT s.*, c.nom AS class_name, cy.nom AS cycle_name,
                       tt.nom AS teaching_type_name
                FROM enrollments e
                JOIN students s ON s.id = e.student_id
                JOIN classes c ON c.id = e.class_id
                JOIN teaching_types tt ON tt.id = c.teaching_type_id AND tt.actif = 1
                LEFT JOIN cycles cy ON cy.id = c.cycle_id
                LEFT JOIN departments d ON d.id = c.department_id
                JOIN sections sec ON sec.id = c.section_id
                WHERE sec.id = ?
                  AND sec.status = 1
                  AND c.status = 1
                  AND (c.cycle_id IS NULL OR cy.status = 1)
                  AND (c.department_id IS NULL OR d.status = 1)
                  AND e.academic_year_id = ?
                  AND s.status = 'Inscrit'
                  AND s.actif = 1
                  AND s.is_withdrawn = 0";
        $params = [$sectionId, $academicYearId];
        if ($classId > 0) {
            $sql .= ' AND c.id = ?';
            $params[] = $classId;
        }
        if ($studentId > 0) {
            $sql .= ' AND s.id = ?';
            $params[] = $studentId;
        }
        $sql .= ' ORDER BY c.nom ASC, s.nom ASC, s.prenom ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}