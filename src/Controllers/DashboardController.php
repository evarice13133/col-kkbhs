<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Services\ActivityTracker;
use App\Services\BackupService;
use App\Services\JobRunLogger;
use App\Services\SettingsStore;
use PDO;

/**
 * Classe DashboardController
 * 
 * Centralise les statistiques pour les administrateurs et les enseignants.
 * Refactorisée pour la performance : utilise des requêtes groupées au lieu de boucles SQL.
 */
class DashboardController
{
    /** @var PDO Instance de connexion à la base de données */
    private $db;
    private SettingsStore $settingsStore;
    private JobRunLogger $jobLogger;
    private BackupService $backupService;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->settingsStore = new SettingsStore($this->db);
        new ActivityTracker($this->db);
        $this->jobLogger = new JobRunLogger($this->db);
        $this->backupService = new BackupService($this->db, $this->settingsStore, $this->jobLogger);

        if (!Session::isLogged()) {
            header("Location: /login");
            exit;
        }
    }

    /**
     * Affiche la vue du tableau de bord. 
     * Charge une structure vide initialement, les données arrivent via AJAX pour ne pas bloquer le rendu.
     */
    /**
     * Affiche la vue du tableau de bord.
     * Charge toutes les statistiques de manière optimisée dès le premier rendu.
     */
    public function index()
    {
        $role = Session::get('user_role');
        $user_id = (int) Session::get('user_id');

        if ($role === 'enseignant') {
            $data = $this->buildTeacherDashboardData($user_id);
            // Extraction des variables pour la vue teacher
            extract($data);
            include __DIR__ . '/../Views/dashboard/teacher.php';
            return;
        }

        // Pour l'administrateur
        $data = $this->buildAdminDashboardData();
        // Extraction des variables pour la vue admin
        extract($data);
        include __DIR__ . '/../Views/dashboard/admin.php';
    }

    /**
     * Construit les données pour l'enseignant de manière optimisée.
     */
    private function buildTeacherDashboardData($teacherId)
    {
        $activeYearId = $this->getActiveAcademicYearId();
        $activeEvaluations = $this->getActiveEvaluations();

        // 1. Récupérer toutes les affectations de l'enseignant en une fois
        $assignments = $this->getTeacherAssignments($teacherId);
        if (empty($assignments)) {
            return $this->getEmptyTeacherData($activeEvaluations);
        }

        // 2. Récupérer les effectifs de TOUTES les classes de l'enseignant (1 seule requête)
        $classIds = array_values(array_unique(array_column($assignments, 'class_id')));
        $classCounts = $this->getBulkClassStudentCounts($classIds);

        // 3. Récupérer TOUTES les notes déjà saisies par ce prof pour l'année active (1 seule requête)
        $filledCounts = $this->getBulkTeacherFilledCounts($teacherId, $activeYearId, $activeEvaluations);

        $stats_classes = count($classIds);
        $stats_subjects = count(array_unique(array_column($assignments, 'subject_id')));
        $stats_students = array_sum($classCounts);

        $stats_expected = 0;
        $stats_filled = 0;
        $classProgress = [];
        $evaluationStats = [];

        // Initialiser l'état global des évaluations
        foreach ($activeEvaluations as $evaluation) {
            $evaluationStats[$evaluation] = ['label' => $evaluation, 'expected_count' => 0, 'filled_count' => 0];
        }

        foreach ($assignments as $assignment) {
            $cId = (int) $assignment['class_id'];
            $sId = (int) $assignment['subject_id'];
            $studentCount = $classCounts[$cId] ?? 0;
            $numEvals = count($activeEvaluations);

            $expectedForAssign = $studentCount * $numEvals;
            $stats_expected += $expectedForAssign;

            if (!isset($classProgress[$cId])) {
                $classProgress[$cId] = [
                    'class_nom' => $assignment['class_nom'],
                    'student_count' => $studentCount,
                    'expected_count' => 0,
                    'filled_count' => 0,
                    'evaluations' => []
                ];
                foreach ($activeEvaluations as $eval) {
                    $classProgress[$cId]['evaluations'][$eval] = ['label' => $eval, 'expected_count' => 0, 'filled_count' => 0];
                }
            }

            $classProgress[$cId]['expected_count'] += $expectedForAssign;

            // Ventiler les notes remplies
            foreach ($activeEvaluations as $eval) {
                // Clé de groupement utilisée dans getBulkTeacherFilledCounts
                $key = "{$cId}_{$sId}_{$eval}";
                $count = $filledCounts[$key] ?? 0;

                $stats_filled += $count;
                $classProgress[$cId]['filled_count'] += $count;
                $classProgress[$cId]['evaluations'][$eval]['expected_count'] += $studentCount;
                $classProgress[$cId]['evaluations'][$eval]['filled_count'] += $count;

                $evaluationStats[$eval]['expected_count'] += $studentCount;
                $evaluationStats[$eval]['filled_count'] += $count;
            }
        }

        // Calculs finaux pour la vue
        foreach ($classProgress as &$cp) {
            $cp['progress_percent'] = $cp['expected_count'] > 0 ? round(($cp['filled_count'] / $cp['expected_count']) * 100) : 0;
            $cp['level_label'] = $this->getLevelLabel($cp['progress_percent']);
            foreach ($cp['evaluations'] as &$ev) {
                $ev['progress_percent'] = $ev['expected_count'] > 0 ? round(($ev['filled_count'] / $ev['expected_count']) * 100) : 0;
            }
        }
        foreach ($evaluationStats as &$es) {
            $es['progress_percent'] = $es['expected_count'] > 0 ? round(($es['filled_count'] / $es['expected_count']) * 100) : 0;
            $es['level_label'] = $this->getLevelLabel($es['progress_percent']);
        }

        return [
            'stats_classes' => $stats_classes,
            'stats_subjects' => $stats_subjects,
            'stats_students' => $stats_students,
            'stats_expected' => $stats_expected,
            'stats_filled' => $stats_filled,
            'stats_pending' => max(0, $stats_expected - $stats_filled),
            'stats_progress' => $stats_expected > 0 ? round(($stats_filled / $stats_expected) * 100) : 0,
            'classProgress' => array_values($classProgress),
            'evaluationStats' => array_values($evaluationStats),
            'activeEvaluations' => $activeEvaluations
        ];
    }

    /**
     * Construit les données pour l'administrateur de manière optimisée.
     */
    private function buildAdminDashboardData()
    {
        $activeYearId = $this->getActiveAcademicYearId();
        $activeEvaluations = $this->getActiveEvaluations();
        $numEvals = count($activeEvaluations);

        // 1. Stats de base
        $stats_students = (int) $this->db->query("SELECT COUNT(*) FROM students WHERE is_withdrawn = 0")->fetchColumn();
        $stats_classes = (int) $this->db->query("SELECT COUNT(*) FROM classes")->fetchColumn();
        $stats_subjects = (int) $this->db->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
        $stats_users = (int) $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats_teachers_count = (int) $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'enseignant'")->fetchColumn();

        // 2. Récupérer TOUTES les affectations et TOUTES les classes avec effectifs (1 seule requête chacune)
        $allClassCounts = $this->getBulkClassStudentCounts([]);
        $allAssignments = $this->db->query("SELECT user_id, class_id, subject_id, c.nom as class_nom 
                                            FROM teacher_assignments ta 
                                            JOIN classes c ON c.id = ta.class_id")->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        // 3. Récupérer TOUTES les notes saisies pour l'année active par TOUS les profs (1 seule requête)
        $allFilledCounts = $this->getBulkGlobalFilledCounts($activeYearId, $activeEvaluations);

        $teacherMetrics = [];
        $globalExpected = 0;
        $globalFilled = 0;

        $teachers = $this->db->query("SELECT id, nom, prenom FROM users WHERE role = 'enseignant' ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($teachers as $t) {
            $tId = (int) $t['id'];
            $assignments = $allAssignments[$tId] ?? [];
            $expected = 0;
            $filled = 0;
            $classes = [];

            foreach ($assignments as $a) {
                $cId = (int) $a['class_id'];
                $sId = (int) $a['subject_id'];
                $studentCount = $allClassCounts[$cId] ?? 0;

                $expected += ($studentCount * $numEvals);
                $filled += ($allFilledCounts["{$tId}_{$cId}_{$sId}"] ?? 0);
                $classes[$a['class_nom']] = true;
            }

            $progress = $expected > 0 ? round(($filled / $expected) * 100) : 0;
            $teacherMetrics[] = [
                'teacher_name' => trim($t['prenom'] . ' ' . $t['nom']),
                'classes_count' => count($classes),
                'assignments_count' => count($assignments),
                'expected_count' => $expected,
                'filled_count' => $filled,
                'pending_count' => max(0, $expected - $filled),
                'progress_percent' => $progress,
                'level_label' => $this->getLevelLabel($progress),
            ];

            $globalExpected += $expected;
            $globalFilled += $filled;
        }

        // Tri par performance
        usort($teacherMetrics, fn($a, $b) => $b['progress_percent'] <=> $a['progress_percent']);

        // 4. Matières non affectées (Sujet lié à une classe mais sans prof assigné)
        $unassignedSubjectsRaw = $this->db->query("
            SELECT s.nom as subject_name, c.nom as class_name, c.id as class_id, s.id as subject_id
            FROM subject_classes sc
            JOIN subjects s ON s.id = sc.subject_id
            JOIN classes c ON c.id = sc.class_id
            LEFT JOIN teacher_assignments ta ON ta.class_id = sc.class_id AND ta.subject_id = sc.subject_id
            WHERE ta.user_id IS NULL
            ORDER BY c.nom ASC, s.nom ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // 5. Enseignants sans aucune affectation
        $teachersWithoutAssignment = (int) $this->db->query("
            SELECT COUNT(*) FROM users u 
            WHERE u.role = 'enseignant' 
            AND NOT EXISTS (SELECT 1 FROM teacher_assignments ta WHERE ta.user_id = u.id)
        ")->fetchColumn();

        $usageMetrics = $this->getUsageMetrics();
        $teacherActivitySummary = $this->getTeacherActivitySummary();
        $backupOverview = $this->getBackupOverview();

        return [
            'stats_students' => $stats_students,
            'stats_classes' => $stats_classes,
            'stats_subjects' => $stats_subjects,
            'stats_users' => $stats_users,
            'stats_teachers' => $stats_teachers_count,
            'teachers_without_assignment' => $teachersWithoutAssignment,
            'globalExpected' => $globalExpected,
            'globalFilled' => $globalFilled,
            'globalPending' => max(0, $globalExpected - $globalFilled),
            'globalProgress' => $globalExpected > 0 ? round(($globalFilled / $globalExpected) * 100) : 0,
            'teacherMetrics' => $teacherMetrics,
            'unassignedSubjects' => $unassignedSubjectsRaw,
            'usageMetrics' => $usageMetrics,
            'teacherActivitySummary' => $teacherActivitySummary,
            'backupOverview' => $backupOverview,
        ];
    }

    private function getUsageMetrics(): array
    {
        $row = $this->db->query("
            SELECT
                COUNT(DISTINCT CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND user_id IS NOT NULL THEN user_id END) AS weekly_active_users,
                COUNT(DISTINCT CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND user_id IS NOT NULL THEN user_id END) AS monthly_active_users,
                SUM(CASE WHEN event_type = 'page_view' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS weekly_visits,
                SUM(CASE WHEN event_type = 'page_view' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS monthly_visits,
                SUM(CASE WHEN event_category <> 'usage' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN event_count ELSE 0 END) AS weekly_activity,
                SUM(CASE WHEN event_category <> 'usage' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN event_count ELSE 0 END) AS monthly_activity
            FROM activity_logs
        ")->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'weekly_active_users' => (int) ($row['weekly_active_users'] ?? 0),
            'monthly_active_users' => (int) ($row['monthly_active_users'] ?? 0),
            'weekly_visits' => (int) ($row['weekly_visits'] ?? 0),
            'monthly_visits' => (int) ($row['monthly_visits'] ?? 0),
            'weekly_activity' => (int) ($row['weekly_activity'] ?? 0),
            'monthly_activity' => (int) ($row['monthly_activity'] ?? 0),
        ];
    }

    private function getTeacherActivitySummary(): array
    {
        $rows = $this->db->query("
            SELECT
                u.id,
                u.nom,
                u.prenom,
                COALESCE(SUM(CASE WHEN al.event_category = 'teacher_activity' AND al.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN al.event_count ELSE 0 END), 0) AS weekly_actions,
                COALESCE(SUM(CASE WHEN al.event_category = 'teacher_activity' AND al.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN al.event_count ELSE 0 END), 0) AS monthly_actions,
                COUNT(DISTINCT CASE WHEN al.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN DATE(al.created_at) END) AS active_days,
                MAX(al.created_at) AS last_activity_at
            FROM users u
            LEFT JOIN activity_logs al ON al.user_id = u.id
            WHERE u.role = 'enseignant'
            GROUP BY u.id, u.nom, u.prenom
            ORDER BY monthly_actions DESC, active_days DESC, last_activity_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $maxMonthly = 1;
        foreach ($rows as $row) {
            $maxMonthly = max($maxMonthly, (int) ($row['monthly_actions'] ?? 0));
        }

        foreach ($rows as &$row) {
            $monthlyActions = (int) ($row['monthly_actions'] ?? 0);
            $activeDays = (int) ($row['active_days'] ?? 0);
            $row['teacher_name'] = trim((string) $row['prenom'] . ' ' . (string) $row['nom']);
            $row['weekly_actions'] = (int) ($row['weekly_actions'] ?? 0);
            $row['monthly_actions'] = $monthlyActions;
            $row['active_days'] = $activeDays;
            $row['frequency_label'] = $this->getFrequencyLabel($activeDays);
            $row['activity_percent'] = (int) round(($monthlyActions / $maxMonthly) * 100);
        }
        unset($row);

        return $rows;
    }

    private function getBackupOverview(): array
    {
        $settings = $this->settingsStore->all();
        $recentArchives = $this->backupService->listArchives();
        $latestRun = $this->jobLogger->latest('weekly_database_backup');
        $lastCompleted = $this->db->query("
            SELECT *
            FROM system_job_runs
            WHERE job_name = 'weekly_database_backup' AND status IN ('success', 'warning')
            ORDER BY started_at DESC
            LIMIT 1
        ")->fetch(PDO::FETCH_ASSOC) ?: null;

        $latestDetails = $this->decodeJson($latestRun['details'] ?? null);
        $archiveMeta = $latestDetails['archive'] ?? [];
        $owner = trim((string) ($settings['backup_github_owner'] ?? ''));
        $repository = trim((string) ($settings['backup_github_repository'] ?? ''));
        $lastCompletedAt = $lastCompleted['started_at'] ?? null;
        $isStale = $lastCompletedAt === null || strtotime((string) $lastCompletedAt) < strtotime('-8 days');
        $latestStatus = (string) ($latestRun['status'] ?? 'unknown');

        if ($latestRun === null) {
            $freshnessState = 'unknown';
        } elseif ($latestStatus === 'failed') {
            $freshnessState = $isStale ? 'stale' : 'failed';
        } elseif ($isStale) {
            $freshnessState = 'stale';
        } elseif ($latestStatus === 'warning') {
            $freshnessState = 'warning';
        } else {
            $freshnessState = 'success';
        }

        return [
            'latest_run_status' => $latestStatus,
            'latest_run_status_label' => $this->getStatusLabel($latestStatus),
            'latest_message' => $latestRun['message'] ?? '',
            'last_run_at' => $latestRun['started_at'] ?? null,
            'last_success_at' => $lastCompletedAt,
            'latest_archive_name' => $archiveMeta['filename'] ?? ($recentArchives[0]['filename'] ?? null),
            'recent_archives' => array_slice($recentArchives, 0, 3),
            'archive_count' => count($recentArchives),
            'repository_label' => ($owner !== '' && $repository !== '') ? $owner . '/' . $repository : null,
            'schedule_label' => $this->formatWeeklyScheduleLabel($settings),
            'freshness_state' => $freshnessState,
            'push_enabled' => $this->settingsStore->getBool('backup_push_enabled', true),
            'backup_enabled' => $this->settingsStore->getBool('backup_enabled', true),
        ];
    }

    // --- Méthodes d'accès aux données Bulk ---

    private function getBulkClassStudentCounts(array $classIds): array
    {
        $where = !empty($classIds) ? " AND class_id IN (" . implode(',', array_map('intval', $classIds)) . ")" : "";
        $stmt = $this->db->query("SELECT class_id, COUNT(*) FROM students WHERE is_withdrawn = 0 $where GROUP BY class_id");
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    private function getBulkTeacherFilledCounts($teacherId, $yearId, $evals): array
    {
        if (!$yearId || empty($evals))
            return [];
        $placeholders = implode(',', array_fill(0, count($evals), '?'));
        $sql = "SELECT CONCAT(st.class_id, '_', g.subject_id, '_', g.periode), COUNT(*)
                FROM grades g
                JOIN students st ON st.id = g.student_id
                WHERE g.teacher_id = ? AND g.academic_year_id = ? AND g.periode IN ($placeholders) AND st.is_withdrawn = 0
                GROUP BY st.class_id, g.subject_id, g.periode";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge([$teacherId, $yearId], $evals));
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    private function getBulkGlobalFilledCounts($yearId, $evals): array
    {
        if (!$yearId || empty($evals))
            return [];
        $placeholders = implode(',', array_fill(0, count($evals), '?'));
        $sql = "SELECT CONCAT(g.teacher_id, '_', st.class_id, '_', g.subject_id), COUNT(*)
                FROM grades g
                JOIN students st ON st.id = g.student_id
                WHERE g.academic_year_id = ? AND g.periode IN ($placeholders) AND st.is_withdrawn = 0
                GROUP BY g.teacher_id, st.class_id, g.subject_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge([$yearId], $evals));
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    private function getTeacherAssignments($teacherId)
    {
        $stmt = $this->db->prepare("SELECT ta.class_id, ta.subject_id, c.nom AS class_nom 
                                    FROM teacher_assignments ta JOIN classes c ON c.id = ta.class_id
                                    WHERE ta.user_id = ? ORDER BY c.nom ASC");
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getActiveAcademicYearId()
    {
        return (int) $this->db->query("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1")->fetchColumn();
    }

    private function getActiveEvaluations(): array
    {
        $stmt = $this->db->query("SELECT label FROM sequences WHERE is_active = 1 ORDER BY position ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    private function getLevelLabel($progress)
    {
        if ($progress >= 90)
            return 'Excellent';
        if ($progress >= 70)
            return 'Bon';
        if ($progress >= 40)
            return 'Moyen';
        if ($progress > 0)
            return 'Faible';
        return 'A demarrer';
    }

    private function getEmptyTeacherData($evals)
    {
        return [
            'stats_classes' => 0,
            'stats_subjects' => 0,
            'stats_students' => 0,
            'stats_expected' => 0,
            'stats_filled' => 0,
            'stats_pending' => 0,
            'stats_progress' => 0,
            'classProgress' => [],
            'evaluationStats' => array_map(fn($e) => ['label' => $e, 'expected_count' => 0, 'filled_count' => 0, 'progress_percent' => 0], $evals),
            'activeEvaluations' => $evals
        ];
    }

    private function formatWeeklyScheduleLabel(array $settings): string
    {
        $day = strtolower(trim((string) ($settings['backup_schedule_day'] ?? 'sunday')));
        $time = trim((string) ($settings['backup_schedule_time'] ?? '02:00')) ?: '02:00';

        return \__($day) . ' - ' . $time;
    }

    private function getFrequencyLabel(int $activeDays): string
    {
        if ($activeDays >= 20) {
            return \__('frequency_high');
        }

        if ($activeDays >= 10) {
            return \__('frequency_medium');
        }

        if ($activeDays > 0) {
            return \__('frequency_low');
        }

        return \__('frequency_idle');
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'success' => \__('status_success'),
            'warning' => \__('status_warning'),
            'failed' => \__('status_failed'),
            'running' => \__('status_running'),
            default => \__('status_unknown'),
        };
    }

    private function decodeJson($value): array
    {
        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
