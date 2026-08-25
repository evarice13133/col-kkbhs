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
            extract($data);
            include __DIR__ . '/../Views/dashboard/teacher.php';
            return;
        }

        if (in_array($role, ['caissier', 'comptable'], true)) {
            $data = $this->buildFinancialDashboardData();
            extract($data);
            include __DIR__ . '/../Views/dashboard/financial.php';
            return;
        }

        if ($role === 'it_manager') {
            $data = $this->buildItManagerDashboardData();
            extract($data);
            include __DIR__ . '/../Views/dashboard/it_manager.php';
            return;
        }

        // Pour superadmin / admin
        $data = $this->buildAdminDashboardData();
        extract($data);
        include __DIR__ . '/../Views/dashboard/admin.php';
    }

    /**
     * Obtenir les types d'enseignement actifs de l'établissement.
     */
    private function getActiveTeachingTypes(): array
    {
        return $this->db->query("SELECT id, nom, code, position FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Construit les données pour le tableau de bord financier (caissier / comptable).
     * Filtré dynamiquement selon le ou les types d'enseignement actifs.
     */
    private function buildFinancialDashboardData($filterTeachingTypeIds = null): array
    {
        $activeYearId = $this->getActiveAcademicYearId();

        $andClass = "";
        if (is_array($filterTeachingTypeIds)) {
            if (!empty($filterTeachingTypeIds)) {
                $idsStr = implode(',', array_map('intval', $filterTeachingTypeIds));
                $andClass = " AND c.teaching_type_id IN ($idsStr) ";
            } else {
                $andClass = " AND 1=0 ";
            }
        } elseif ($filterTeachingTypeIds !== null) {
            $id = (int)$filterTeachingTypeIds;
            $andClass = " AND c.teaching_type_id = {$id} ";
        } else {
            $activeTypes = $this->getActiveTeachingTypes();
            $activeIds = array_column($activeTypes, 'id');
            if (!empty($activeIds)) {
                $idsStr = implode(',', array_map('intval', $activeIds));
                $andClass = " AND c.teaching_type_id IN ($idsStr) ";
            }
        }

        $totalStudents = (int) $this->db->query(
            "SELECT COUNT(*) FROM students s JOIN classes c ON s.class_id = c.id WHERE s.is_withdrawn = 0 AND s.actif = 1 AND s.academic_year_id = {$activeYearId} {$andClass}"
        )->fetchColumn();

        // Scolarité encaissée
        $totalTuitionCollected = (float) $this->db->query(
            "SELECT COALESCE(SUM(p.amount), 0) FROM payments p JOIN students s ON p.student_id = s.id JOIN classes c ON s.class_id = c.id WHERE p.type = 'scolarite' AND p.academic_year_id = {$activeYearId} {$andClass}"
        )->fetchColumn();

        // Frais d'inscription encaissés
        $totalRegistrationCollected = (float) $this->db->query(
            "SELECT COALESCE(SUM(p.amount), 0) FROM payments p JOIN students s ON p.student_id = s.id JOIN classes c ON s.class_id = c.id WHERE p.type = 'inscription' AND p.academic_year_id = {$activeYearId} {$andClass}"
        )->fetchColumn();

        // Recettes globales de la caisse (scolarité + inscription)
        $totalGeneralCollected = $totalTuitionCollected + $totalRegistrationCollected;

        // Total attendu scolarité brut
        $totalExpectedGross = (float) $this->db->query(
            "SELECT COALESCE(SUM(c.frais_scolarite_brut), 0)
             FROM students s JOIN classes c ON s.class_id = c.id
             WHERE s.is_withdrawn = 0 AND s.actif = 1 AND s.academic_year_id = {$activeYearId} {$andClass}"
        )->fetchColumn();

        // Réductions accordées sur la scolarité
        $totalReductions = (float) $this->db->query(
            "SELECT COALESCE(SUM(e.total_reductions), 0)
             FROM enrollments e
             JOIN students s ON e.student_id = s.id
             JOIN classes c ON s.class_id = c.id
             WHERE s.is_withdrawn = 0 AND s.actif = 1 AND e.academic_year_id = {$activeYearId} {$andClass}"
        )->fetchColumn();

        $totalExpected = max(0.0, $totalExpectedGross - $totalReductions);

        $totalInsolvent = (int) $this->db->query(
            "SELECT COUNT(DISTINCT ins.student_id) 
             FROM insolvent_students ins
             JOIN students s ON ins.student_id = s.id
             JOIN classes c ON s.class_id = c.id
             WHERE ins.academic_year_id = {$activeYearId} {$andClass}"
        )->fetchColumn();

        $collectionRate = $totalExpected > 0 ? round(($totalTuitionCollected / $totalExpected) * 100, 1) : 0;

        // Récupérer la politique des frais d'inscription
        $settingsStore = new \App\Services\SettingsStore($this->db);
        $policy = $settingsStore->get('registration_fee_policy', 'all');

        $isNewOnly = ($policy === 'new_only') ? 1 : 0;
        $isByStatus = ($policy === 'by_status') ? 1 : 0;

        // Récupérer les statistiques de rentrée/inscription par classe
        $stmtClassStats = $this->db->prepare("
            SELECT 
                c.id as class_id,
                c.nom as class_name,
                COUNT(s.id) as total_students,
                SUM(CASE WHEN COALESCE(p.paid_amount, 0) >= 
                    CASE 
                        WHEN :isNewOnly1 = 1 THEN 
                            CASE WHEN e.student_status = 'nouveau' THEN c.frais_inscription ELSE 0 END
                        WHEN :isByStatus1 = 1 THEN 
                            CASE WHEN e.student_status = 'nouveau' THEN c.frais_inscription ELSE c.frais_inscription_reinscription END
                        ELSE 
                            c.frais_inscription
                    END
                THEN 1 ELSE 0 END) as enrolled_count,
                SUM(CASE WHEN COALESCE(p.paid_amount, 0) < 
                    CASE 
                        WHEN :isNewOnly2 = 1 THEN 
                            CASE WHEN e.student_status = 'nouveau' THEN c.frais_inscription ELSE 0 END
                        WHEN :isByStatus2 = 1 THEN 
                            CASE WHEN e.student_status = 'nouveau' THEN c.frais_inscription ELSE c.frais_inscription_reinscription END
                        ELSE 
                            c.frais_inscription
                    END
                THEN 1 ELSE 0 END) as non_enrolled_count,
                SUM(COALESCE(p.paid_amount, 0)) as total_registration_collected
            FROM students s
            JOIN enrollments e ON s.id = e.student_id AND e.academic_year_id = s.academic_year_id
            JOIN classes c ON s.class_id = c.id
            LEFT JOIN (
                SELECT student_id, SUM(amount) as paid_amount 
                FROM payments 
                WHERE type = 'inscription' AND academic_year_id = :academic_year_id1
                GROUP BY student_id
            ) p ON s.id = p.student_id
            WHERE s.is_withdrawn = 0 AND s.actif = 1 AND s.academic_year_id = :academic_year_id2 {$andClass}
            GROUP BY c.id, c.nom
            ORDER BY c.nom ASC
        ");
        $stmtClassStats->execute([
            ':isNewOnly1' => $isNewOnly,
            ':isByStatus1' => $isByStatus,
            ':isNewOnly2' => $isNewOnly,
            ':isByStatus2' => $isByStatus,
            ':academic_year_id1' => $activeYearId,
            ':academic_year_id2' => $activeYearId
        ]);
        $classRegistrationStats = $stmtClassStats->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Calculer les totaux d'inscription depuis les statistiques par classe
        $totalEnrolled = 0;
        $totalNonEnrolled = 0;
        foreach ($classRegistrationStats as $row) {
            $totalEnrolled += (int)$row['enrolled_count'];
            $totalNonEnrolled += (int)$row['non_enrolled_count'];
        }

        // Évolution mensuelle des paiements (6 derniers mois)
        $monthlyPayments = $this->db->query(
            "SELECT DATE_FORMAT(p.payment_date, '%Y-%m') as month,
                    SUM(p.amount) as total
             FROM payments p
             JOIN students s ON p.student_id = s.id
             JOIN classes c ON s.class_id = c.id
             WHERE p.academic_year_id = {$activeYearId} {$andClass}
               AND p.payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY month ORDER BY month ASC"
        )->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Derniers paiements reçus
        $recentPayments = $this->db->query(
            "SELECT p.payment_date, p.amount, p.payment_method, p.type,
                    CONCAT(s.nom, ' ', s.prenom) as student_name,
                    c.nom as class_nom
             FROM payments p
             JOIN students s ON p.student_id = s.id
             JOIN classes c ON s.class_id = c.id
             WHERE p.academic_year_id = {$activeYearId} {$andClass}
             ORDER BY p.payment_date DESC, p.id DESC LIMIT 10"
        )->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Dépenses globales (établissement)
        $totalExpenses = (float) $this->db->query(
            "SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE status = 'active' AND academic_year_id = {$activeYearId}"
        )->fetchColumn();

        $dailyExpenses = (float) $this->db->query(
            "SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE status = 'active' AND expense_date = CURDATE() AND academic_year_id = {$activeYearId}"
        )->fetchColumn();

        $monthlyExpenses = (float) $this->db->query(
            "SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE status = 'active' AND MONTH(expense_date) = MONTH(CURDATE()) AND YEAR(expense_date) = YEAR(CURDATE()) AND academic_year_id = {$activeYearId}"
        )->fetchColumn();

        $annualExpenses = $totalExpenses;
        $netBalance = $totalGeneralCollected - $totalExpenses;

        $expensesByCategory = $this->db->query("
            SELECT ec.name as category_name, COALESCE(SUM(e.amount), 0) as total 
            FROM expenses e 
            JOIN expense_categories ec ON e.category_id = ec.id 
            WHERE e.status = 'active' AND e.academic_year_id = {$activeYearId} 
            GROUP BY ec.id, ec.name
        ")->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $monthlyExpensesHist = $this->db->query("
            SELECT DATE_FORMAT(expense_date, '%Y-%m') as month,
                   SUM(amount) as total
            FROM expenses
            WHERE status = 'active' AND academic_year_id = {$activeYearId}
              AND expense_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY month ORDER BY month ASC
        ")->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $totalCollected = $totalTuitionCollected;

        $extraFinData = $this->getExtraFinancialCenterData($activeYearId, $filterTeachingTypeIds);

        return array_merge(compact(
            'totalStudents', 'totalCollected', 'totalExpected', 'totalExpectedGross', 'totalReductions',
            'totalInsolvent', 'collectionRate', 'monthlyPayments', 'recentPayments',
            'totalRegistrationCollected', 'totalTuitionCollected', 'totalGeneralCollected',
            'totalEnrolled', 'totalNonEnrolled', 'classRegistrationStats', 'policy',
            'totalExpenses', 'dailyExpenses', 'monthlyExpenses', 'annualExpenses', 'netBalance', 
            'expensesByCategory', 'monthlyExpensesHist'
        ), $extraFinData);
    }

    /**
     * Construit les données pour le tableau de bord IT Manager.
     * NE CONTIENT AUCUN montant financier.
     */
    private function buildItManagerDashboardData(): array
    {
        $totalUsers = (int) $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalTeachers = (int) $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'enseignant'")->fetchColumn();
        $totalClasses = (int) $this->db->query("SELECT COUNT(*) FROM classes")->fetchColumn();
        $totalStudents = (int) $this->db->query(
            "SELECT COUNT(*) FROM students s JOIN academic_years ay ON s.academic_year_id = ay.id WHERE ay.is_active = 1 AND s.is_withdrawn = 0 AND s.actif = 1"
        )->fetchColumn();

        // Dernières activités
        $recentActivity = [];
        try {
            $recentActivity = $this->db->query(
                "SELECT action, details, created_at FROM activity_log ORDER BY created_at DESC LIMIT 15"
            )->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            // Table may not exist
        }

        // Répartition des rôles
        $roleDistribution = $this->db->query(
            "SELECT role, COUNT(*) as count FROM users GROUP BY role ORDER BY count DESC"
        )->fetchAll(\PDO::FETCH_ASSOC);

        return compact('totalUsers', 'totalTeachers', 'totalClasses', 'totalStudents', 'recentActivity', 'roleDistribution');
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

        // Vérifier si l'enseignant intervient dans des classes du type d'enseignement "Supérieur LMD"
        $has_lmd_classes = false;
        if (!empty($classIds)) {
            $placeholders = implode(',', array_fill(0, count($classIds), '?'));
            $stmtLmd = $this->db->prepare("
                SELECT COUNT(*) 
                FROM classes c
                JOIN teaching_types tt ON c.teaching_type_id = tt.id
                WHERE c.id IN ($placeholders)
                  AND (tt.id = 9 OR tt.nom LIKE '%LMD%' OR tt.nom LIKE '%Supérieur%')
            ");
            $stmtLmd->execute($classIds);
            $has_lmd_classes = ((int) $stmtLmd->fetchColumn() > 0);
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
            'activeEvaluations' => $activeEvaluations,
            'has_lmd_classes' => $has_lmd_classes,
            'teacherAssignments' => $assignments
        ];
    }

    /**
     * Construit les données pour l'administrateur de manière optimisée et contextualisée par type d'enseignement actif.
     */
    private function buildAdminDashboardData()
    {
        $activeYearId = $this->getActiveAcademicYearId();
        $activeEvaluations = $this->getActiveEvaluations();
        $activeTeachingTypes = $this->getActiveTeachingTypes();
        $activeTypeIds = array_column($activeTeachingTypes, 'id');

        $statsByTeachingType = [];
        $no_active_teaching_types = empty($activeTeachingTypes);

        if (!$no_active_teaching_types) {
            foreach ($activeTeachingTypes as $tt) {
                $ttId = (int)$tt['id'];
                $statsByTeachingType[$ttId] = $this->buildTeachingTypeMetrics($ttId, $activeYearId, $activeEvaluations);
                $statsByTeachingType[$ttId]['teaching_type'] = $tt;
                $statsByTeachingType[$ttId]['financial_data'] = $this->buildFinancialDashboardData($ttId);
            }
        }

        // Métriques combinées / consolidées pour les vues principales
        $primaryData = $this->buildTeachingTypeMetrics($activeTypeIds, $activeYearId, $activeEvaluations);

        // Données globales à l'établissement (utilisateurs, logs, backups, vitrine)
        $userRole = Session::get('user_role');
        if ($userRole === 'admin') {
            $stats_users = (int) $this->db->query("SELECT COUNT(*) FROM users WHERE role <> 'superadmin'")->fetchColumn();
            $roleDistribution = $this->db->query("
                SELECT role, COUNT(*) as count FROM users WHERE role <> 'superadmin' GROUP BY role ORDER BY count DESC
            ")->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $adminsCount = (int)$this->db->query("
                SELECT COUNT(*) FROM users WHERE role IN ('admin', 'caissier', 'comptable', 'it_manager')
            ")->fetchColumn();
        } else {
            $stats_users = (int) $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
            $roleDistribution = $this->db->query("
                SELECT role, COUNT(*) as count FROM users GROUP BY role ORDER BY count DESC
            ")->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $adminsCount = (int)$this->db->query("
                SELECT COUNT(*) FROM users WHERE role IN ('superadmin', 'admin', 'caissier', 'comptable', 'it_manager')
            ")->fetchColumn();
        }

        $inactive_subjects_list = $this->db->query("SELECT id, nom FROM subjects WHERE status = 0 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $teachersWithoutAssignment = (int) $this->db->query("
            SELECT COUNT(*) FROM users u 
            WHERE u.role = 'enseignant' 
            AND NOT EXISTS (SELECT 1 FROM teacher_assignments ta WHERE ta.user_id = u.id)
        ")->fetchColumn();

        $usageMetrics = $this->getUsageMetrics();
        $teacherActivitySummary = $this->getTeacherActivitySummary();
        $backupOverview = $this->getBackupOverview();
        
        $notifications = [];
        $logPath = __DIR__ . '/../../logs/notifications.json';
        if (file_exists($logPath)) {
            $notifications = json_decode(file_get_contents($logPath), true) ?: [];
        }

        $totalRemaining = (float)$this->db->query("
            SELECT COALESCE(SUM(reste_a_payer), 0)
            FROM enrollments e
            JOIN students s ON e.student_id = s.id
            WHERE s.is_withdrawn = 0 AND s.actif = 1 AND e.academic_year_id = {$activeYearId}
        ")->fetchColumn();

        $totalReductions = (float)$this->db->query("
            SELECT COALESCE(SUM(total_reductions), 0)
            FROM enrollments e
            JOIN students s ON e.student_id = s.id
            WHERE s.is_withdrawn = 0 AND s.actif = 1 AND e.academic_year_id = {$activeYearId}
        ")->fetchColumn();

        $totalScholarships = (float)$this->db->query("
            SELECT COALESCE(SUM(total_bourses), 0)
            FROM enrollments e
            JOIN students s ON e.student_id = s.id
            WHERE s.is_withdrawn = 0 AND s.actif = 1 AND e.academic_year_id = {$activeYearId}
        ")->fetchColumn();

        $stmtPayMethod = $this->db->prepare("
            SELECT payment_method, SUM(amount) as total
            FROM payments
            WHERE status = 'valide' AND academic_year_id = ?
            GROUP BY payment_method
        ");
        $stmtPayMethod->execute([$activeYearId]);
        $paymentMethodRepartition = $stmtPayMethod->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $financialData = $this->buildFinancialDashboardData($activeTypeIds);
        $extraFinData = $this->getExtraFinancialCenterData($activeYearId, $activeTypeIds);
        $extraAcadData = $this->getExtraExecutiveAcademicData($activeYearId, $activeTypeIds);

        return array_merge([
            'activeTeachingTypes' => $activeTeachingTypes,
            'statsByTeachingType' => $statsByTeachingType,
            'no_active_teaching_types' => $no_active_teaching_types,
            'inactive_subjects_list' => $inactive_subjects_list,
            'stats_users' => $stats_users,
            'teachers_without_assignment' => $teachersWithoutAssignment,
            'usageMetrics' => $usageMetrics,
            'teacherActivitySummary' => $teacherActivitySummary,
            'backupOverview' => $backupOverview,
            'landing_notifications' => $notifications,
            'activeEvaluations' => $activeEvaluations,
            'bulletin_printing_enabled' => $this->settingsStore->getBool('bulletin_printing_enabled', true),
            'totalRemaining' => $totalRemaining,
            'totalReductions' => $totalReductions,
            'totalScholarships' => $totalScholarships,
            'paymentMethodRepartition' => $paymentMethodRepartition,
            'roleDistribution' => $roleDistribution,
            'adminsCount' => $adminsCount,
        ], $primaryData, $financialData, $extraFinData, $extraAcadData);
    }

    /**
     * Génère les métriques pour un ou plusieurs types d'enseignement donnés.
     */
    private function buildTeachingTypeMetrics($ttIdOrIds, int $activeYearId, array $activeEvaluations): array
    {
        $numEvals = count($activeEvaluations);

        if (is_array($ttIdOrIds)) {
            if (empty($ttIdOrIds)) {
                return $this->getEmptyTeachingTypeMetrics($activeEvaluations);
            }
            $idsStr = implode(',', array_map('intval', $ttIdOrIds));
            $whereClass = " WHERE c.teaching_type_id IN ($idsStr) ";
            $andClass = " AND c.teaching_type_id IN ($idsStr) ";
            $andSubject = " AND s.teaching_type_id IN ($idsStr) ";
        } elseif ($ttIdOrIds !== null) {
            $id = (int)$ttIdOrIds;
            $whereClass = " WHERE c.teaching_type_id = {$id} ";
            $andClass = " AND c.teaching_type_id = {$id} ";
            $andSubject = " AND s.teaching_type_id = {$id} ";
        } else {
            $whereClass = "";
            $andClass = "";
            $andSubject = "";
        }

        // 1. Students stats
        $stats_students = (int) $this->db->query("
            SELECT COUNT(*) FROM students s 
            JOIN classes c ON s.class_id = c.id 
            WHERE s.status = 'Inscrit' AND s.actif = 1 AND s.academic_year_id = {$activeYearId} {$andClass}
        ")->fetchColumn();

        $stats_students_inscrits = $stats_students;

        $stats_students_non_inscrits = (int) $this->db->query("
            SELECT COUNT(*) FROM students s 
            JOIN classes c ON s.class_id = c.id 
            WHERE s.status = 'Non inscrit' AND s.actif = 1 AND s.academic_year_id = {$activeYearId} {$andClass}
        ")->fetchColumn();

        $stats_students_demissionnaires = (int) $this->db->query("
            SELECT COUNT(*) FROM students s 
            JOIN classes c ON s.class_id = c.id 
            WHERE s.status = 'Démissionnaire' AND s.actif = 1 AND s.academic_year_id = {$activeYearId} {$andClass}
        ")->fetchColumn();

        $stats_total_importes = $stats_students_inscrits + $stats_students_non_inscrits + $stats_students_demissionnaires;
        $conversion_rate = $stats_total_importes > 0 ? round(($stats_students_inscrits / $stats_total_importes) * 100, 1) : 0;

        // 2. Classes
        $stats_classes = (int) $this->db->query("
            SELECT COUNT(*) FROM classes c {$whereClass}
        ")->fetchColumn();

        // 3. Subjects
        $whereSubjectStatus = $ttIdOrIds !== null ? "WHERE status = 1 {$andSubject}" : "WHERE status = 1";
        $stats_subjects = (int) $this->db->query("
            SELECT COUNT(*) FROM subjects s {$whereSubjectStatus}
        ")->fetchColumn();

        $whereSubjectInactive = $ttIdOrIds !== null ? "WHERE status = 0 {$andSubject}" : "WHERE status = 0";
        $stats_subjects_inactive = (int) $this->db->query("
            SELECT COUNT(*) FROM subjects s {$whereSubjectInactive}
        ")->fetchColumn();

        // 4. Teachers count
        $stats_teachers_count = (int) $this->db->query("
            SELECT COUNT(DISTINCT ta.user_id) 
            FROM teacher_assignments ta 
            JOIN classes c ON ta.class_id = c.id 
            WHERE ta.academic_year_id = {$activeYearId} {$andClass}
        ")->fetchColumn();

        // 5. Class student counts
        $classCountsStmt = $this->db->query("
            SELECT s.class_id, COUNT(*) 
            FROM students s 
            JOIN classes c ON s.class_id = c.id 
            WHERE s.is_withdrawn = 0 AND s.actif = 1 AND s.academic_year_id = {$activeYearId} {$andClass}
            GROUP BY s.class_id
        ");
        $allClassCounts = $classCountsStmt ? $classCountsStmt->fetchAll(PDO::FETCH_KEY_PAIR) : [];

        // Assignments
        $assignmentsQuery = "
            SELECT ta.user_id, ta.class_id, ta.subject_id, c.nom as class_nom 
            FROM teacher_assignments ta 
            JOIN classes c ON c.id = ta.class_id AND ta.academic_year_id = {$activeYearId} {$andClass}
        ";
        $allAssignments = $this->db->query($assignmentsQuery)->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC) ?: [];

        // Global filled counts for active evaluations
        $allFilledCounts = $this->getBulkGlobalFilledCounts($activeYearId, $activeEvaluations, $ttIdOrIds);

        // Progression
        $globalExpected = 0;
        $globalFilled = 0;

        $subjectClassesQuery = "
            SELECT sc.class_id, sc.subject_id
            FROM subject_classes sc
            JOIN subjects s ON s.id = sc.subject_id
            JOIN classes c ON c.id = sc.class_id
            WHERE sc.academic_year_id = {$activeYearId} AND s.status = 1 {$andClass}
        ";
        $allSubjectClasses = $this->db->query($subjectClassesQuery)->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($allSubjectClasses as $sc) {
            $cId = (int) $sc['class_id'];
            $sId = (int) $sc['subject_id'];
            $studentCount = $allClassCounts[$cId] ?? 0;
            $key = "{$cId}_{$sId}";
            $filledCount = $allFilledCounts[$key] ?? 0;

            $globalExpected += ($studentCount * $numEvals);
            $globalFilled += $filledCount;
        }

        $globalProgress = $globalExpected > 0 ? round(($globalFilled / $globalExpected) * 100) : 0;

        // Teacher metrics
        $teacherMetrics = [];
        $teachersUnder50 = 0;
        
        $teachersQuery = "
            SELECT DISTINCT u.id, u.nom, u.prenom 
            FROM users u 
            JOIN teacher_assignments ta ON ta.user_id = u.id 
            JOIN classes c ON c.id = ta.class_id 
            WHERE u.role = 'enseignant' AND ta.academic_year_id = {$activeYearId} {$andClass}
            ORDER BY u.nom ASC
        ";
        $teachers = $this->db->query($teachersQuery)->fetchAll(PDO::FETCH_ASSOC) ?: [];

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
                $filled += ($allFilledCounts["{$cId}_{$sId}"] ?? 0);
                $classes[$a['class_nom']] = true;
            }

            $progress = $expected > 0 ? round(($filled / $expected) * 100) : 0;

            if ($progress < 50 && $expected > 0) {
                $teachersUnder50++;
            }

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
        }

        usort($teacherMetrics, fn($a, $b) => $b['progress_percent'] <=> $a['progress_percent']);

        // Unassigned subjects
        $unassignedSubjectsRaw = $this->db->query("
            SELECT s.nom as subject_name, c.nom as class_name, c.id as class_id, s.id as subject_id
            FROM subject_classes sc
            JOIN subjects s ON s.id = sc.subject_id
            JOIN classes c ON c.id = sc.class_id
            LEFT JOIN teacher_assignments ta ON ta.class_id = sc.class_id AND ta.subject_id = sc.subject_id AND ta.academic_year_id = {$activeYearId}
            WHERE ta.user_id IS NULL AND s.status = 1 {$andClass}
            ORDER BY c.nom ASC, s.nom ASC
        ")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Top & struggling students
        $stmtTop = $this->db->prepare("
            SELECT st.id as student_id, st.nom, st.prenom, c.nom as classe_nom,
                   SUM(g.valeur * s.coefficient) / SUM(s.coefficient) as moyenne
            FROM grades g
            JOIN students st ON st.id = g.student_id
            JOIN classes c ON c.id = st.class_id
            JOIN subjects s ON s.id = g.subject_id
            WHERE g.academic_year_id = ? AND st.is_withdrawn = 0 {$andClass}
            GROUP BY st.id, st.nom, st.prenom, c.nom
            ORDER BY moyenne DESC
            LIMIT 5
        ");
        $stmtTop->execute([$activeYearId]);
        $topStudents = $stmtTop->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $stmtStrug = $this->db->prepare("
            SELECT st.id as student_id, st.nom, st.prenom, c.nom as classe_nom,
                   SUM(g.valeur * s.coefficient) / SUM(s.coefficient) as moyenne
            FROM grades g
            JOIN students st ON st.id = g.student_id
            JOIN classes c ON c.id = st.class_id
            JOIN subjects s ON s.id = g.subject_id
            WHERE g.academic_year_id = ? AND st.is_withdrawn = 0 {$andClass}
            GROUP BY st.id, st.nom, st.prenom, c.nom
            HAVING moyenne < 10
            ORDER BY moyenne ASC
            LIMIT 5
        ");
        $stmtStrug->execute([$activeYearId]);
        $strugglingStudents = $stmtStrug->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Class avgs
        $stmtClassAvgs = $this->db->prepare("
            SELECT st.class_id, c.nom as class_name, st.id as student_id,
                   SUM(g.valeur * s.coefficient) / SUM(s.coefficient) as moyenne
            FROM grades g
            JOIN students st ON st.id = g.student_id
            JOIN classes c ON c.id = st.class_id
            JOIN subjects s ON s.id = g.subject_id
            WHERE g.academic_year_id = ? AND st.is_withdrawn = 0 {$andClass}
            GROUP BY st.class_id, c.nom, st.id
        ");
        $stmtClassAvgs->execute([$activeYearId]);
        $avgs = $stmtClassAvgs->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $classStats = [];
        $distribution = ['elite' => 0, 'satisfait' => 0, 'passable' => 0, 'soutien' => 0];

        foreach ($avgs as $row) {
            $cId = $row['class_id'];
            if (!isset($classStats[$cId])) {
                $classStats[$cId] = [
                    'class_name' => $row['class_name'],
                    'total_students' => 0,
                    'passing_students' => 0,
                    'sum_averages' => 0
                ];
            }
            $classStats[$cId]['total_students']++;
            if ($row['moyenne'] >= 10) {
                $classStats[$cId]['passing_students']++;
            }
            $classStats[$cId]['sum_averages'] += (float)$row['moyenne'];

            $val = (float)$row['moyenne'];
            if ($val >= 16) $distribution['elite']++;
            elseif ($val >= 12) $distribution['satisfait']++;
            elseif ($val >= 10) $distribution['passable']++;
            else $distribution['soutien']++;
        }
        foreach ($classStats as &$cs) {
            $cs['class_avg'] = $cs['total_students'] > 0 ? ($cs['sum_averages'] / $cs['total_students']) : 0;
            $cs['success_rate'] = $cs['total_students'] > 0 ? round(($cs['passing_students'] / $cs['total_students']) * 100) : 0;
        }
        unset($cs);
        uasort($classStats, fn($a, $b) => $b['class_avg'] <=> $a['class_avg']);

        // Period averages
        $seqAverages = [];
        if (!empty($activeEvaluations)) {
            $placeholders = implode(',', array_fill(0, count($activeEvaluations), '?'));
            $stmtSeq = $this->db->prepare("
                SELECT g.periode, AVG(g.valeur) as moyenne
                FROM grades g
                JOIN students st ON st.id = g.student_id
                JOIN classes c ON c.id = st.class_id
                WHERE g.academic_year_id = ? AND g.periode IN ($placeholders) {$andClass}
                GROUP BY g.periode
            ");
            $stmtSeq->execute(array_merge([$activeYearId], $activeEvaluations));
            $seqAvgsRaw = $stmtSeq->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

            foreach ($activeEvaluations as $seq) {
                $seqAverages[] = [
                    'periode' => $seq,
                    'moyenne' => isset($seqAvgsRaw[$seq]) ? (float)$seqAvgsRaw[$seq] : 0
                ];
            }
        }

        // Demographics: Gender
        $stmtGender = $this->db->prepare("
            SELECT st.sexe, COUNT(*) as count 
            FROM students st 
            JOIN classes c ON st.class_id = c.id 
            WHERE st.is_withdrawn = 0 AND st.actif = 1 AND st.academic_year_id = ? {$andClass}
            GROUP BY st.sexe
        ");
        $stmtGender->execute([$activeYearId]);
        $genders = $stmtGender->fetchAll(PDO::FETCH_KEY_PAIR);
        $maleCount = (int)($genders['M'] ?? 0);
        $femaleCount = (int)($genders['F'] ?? 0);

        // Demographics: Cycles
        $stmtCycles = $this->db->prepare("
            SELECT cy.nom as cycle_nom, COUNT(st.id) as count
            FROM students st
            JOIN classes c ON st.class_id = c.id
            JOIN cycles cy ON c.cycle_id = cy.id
            WHERE st.is_withdrawn = 0 AND st.actif = 1 AND st.academic_year_id = ? {$andClass}
            GROUP BY cy.id, cy.nom
        ");
        $stmtCycles->execute([$activeYearId]);
        $cycleRepartition = $stmtCycles->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Success rate
        $totalWithAverage = count($avgs);
        $passingCount = 0;
        foreach ($avgs as $row) {
            if ((float)$row['moyenne'] >= 10.0) {
                $passingCount++;
            }
        }
        $successRate = $totalWithAverage > 0 ? round(($passingCount / $totalWithAverage) * 100, 1) : 0;

        // Subjects stats
        $stmtSubjectStatsClean = $this->db->prepare("
            SELECT s.id, s.nom, AVG(g.valeur) as moyenne,
                   GROUP_CONCAT(DISTINCT c.nom SEPARATOR ', ') as classes
            FROM grades g
            JOIN students st ON st.id = g.student_id
            JOIN classes c ON c.id = st.class_id
            JOIN subjects s ON s.id = g.subject_id
            WHERE g.academic_year_id = ? AND s.status = 1 {$andClass}
            GROUP BY s.id, s.nom
            ORDER BY moyenne DESC
        ");
        $stmtSubjectStatsClean->execute([$activeYearId]);
        $subjectStats = $stmtSubjectStatsClean->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $bestSubject = !empty($subjectStats) ? $subjectStats[0] : null;
        $worstSubject = !empty($subjectStats) && count($subjectStats) > 1 ? end($subjectStats) : null;
        $top5Subjects = array_slice($subjectStats, 0, 5);
        $bottom5Subjects = array_slice(array_reverse($subjectStats), 0, 5);

        // Subject by evaluation
        $subjectByEval = [];
        if (!empty($activeEvaluations)) {
            foreach ($activeEvaluations as $eval) {
                $stmtEval = $this->db->prepare("
                    SELECT s.id, s.nom, AVG(g.valeur) as moyenne,
                           GROUP_CONCAT(DISTINCT c.nom SEPARATOR ', ') as classes
                    FROM grades g
                    JOIN students st ON st.id = g.student_id
                    JOIN classes c ON c.id = st.class_id
                    JOIN subjects s ON s.id = g.subject_id
                    WHERE g.academic_year_id = ? AND g.periode = ? AND s.status = 1 {$andClass}
                    GROUP BY s.id, s.nom
                    ORDER BY moyenne DESC
                    LIMIT 5
                ");
                $stmtEval->execute([$activeYearId, $eval]);
                $subjectByEval[$eval] = $stmtEval->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }
        }

        return [
            'stats_students' => $stats_students,
            'stats_students_inscrits' => $stats_students_inscrits,
            'stats_students_non_inscrits' => $stats_students_non_inscrits,
            'stats_students_demissionnaires' => $stats_students_demissionnaires,
            'stats_total_importes' => $stats_total_importes,
            'conversion_rate' => $conversion_rate,
            'stats_classes' => $stats_classes,
            'stats_subjects' => $stats_subjects,
            'stats_subjects_inactive' => $stats_subjects_inactive,
            'stats_teachers' => $stats_teachers_count,
            'globalExpected' => $globalExpected,
            'globalFilled' => $globalFilled,
            'globalPending' => max(0, $globalExpected - $globalFilled),
            'globalProgress' => $globalProgress,
            'teachersUnder50' => $teachersUnder50,
            'teacherMetrics' => $teacherMetrics,
            'unassignedSubjects' => $unassignedSubjectsRaw,
            'topStudents' => $topStudents,
            'strugglingStudents' => $strugglingStudents,
            'classStats' => array_values($classStats),
            'seqAverages' => $seqAverages,
            'distribution' => $distribution,
            'bestSubject' => $bestSubject,
            'worstSubject' => $worstSubject,
            'top5Subjects' => $top5Subjects,
            'bottom5Subjects' => $bottom5Subjects,
            'subjectByEval' => $subjectByEval,
            'maleCount' => $maleCount,
            'femaleCount' => $femaleCount,
            'cycleRepartition' => $cycleRepartition,
            'successRate' => $successRate,
        ];
    }

    private function getEmptyTeachingTypeMetrics(array $evals): array
    {
        return [
            'stats_students' => 0,
            'stats_students_inscrits' => 0,
            'stats_students_non_inscrits' => 0,
            'stats_students_demissionnaires' => 0,
            'stats_total_importes' => 0,
            'conversion_rate' => 0,
            'stats_classes' => 0,
            'stats_subjects' => 0,
            'stats_subjects_inactive' => 0,
            'stats_teachers' => 0,
            'globalExpected' => 0,
            'globalFilled' => 0,
            'globalPending' => 0,
            'globalProgress' => 0,
            'teachersUnder50' => 0,
            'teacherMetrics' => [],
            'unassignedSubjects' => [],
            'topStudents' => [],
            'strugglingStudents' => [],
            'classStats' => [],
            'seqAverages' => array_map(fn($e) => ['periode' => $e, 'moyenne' => 0], $evals),
            'distribution' => ['elite' => 0, 'satisfait' => 0, 'passable' => 0, 'soutien' => 0],
            'bestSubject' => null,
            'worstSubject' => null,
            'top5Subjects' => [],
            'bottom5Subjects' => [],
            'subjectByEval' => [],
            'maleCount' => 0,
            'femaleCount' => 0,
            'cycleRepartition' => [],
            'successRate' => 0,
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
        $activeYearId = $this->getActiveAcademicYearId();
        $where = !empty($classIds) ? " AND class_id IN (" . implode(',', array_map('intval', $classIds)) . ")" : "";
        $stmt = $this->db->query("SELECT class_id, COUNT(*) FROM students WHERE is_withdrawn = 0 AND actif = 1 AND academic_year_id = {$activeYearId} $where GROUP BY class_id");
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
        // Compte les notes par classe/matière (sans distinction de l'enseignant qui a saisi)
        $sql = "SELECT CONCAT(st.class_id, '_', g.subject_id), COUNT(*)
                FROM grades g
                JOIN students st ON st.id = g.student_id
                WHERE g.academic_year_id = ? AND g.periode IN ($placeholders) AND st.is_withdrawn = 0
                GROUP BY st.class_id, g.subject_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge([$yearId], $evals));
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    private function getTeacherAssignments($teacherId)
    {
        $activeYearId = $this->getActiveAcademicYearId();
        $stmt = $this->db->prepare("SELECT ta.class_id, ta.subject_id, c.nom AS class_nom, s.nom AS subject_nom 
                                    FROM teacher_assignments ta 
                                    JOIN classes c ON c.id = ta.class_id
                                    JOIN subjects s ON s.id = ta.subject_id
                                    WHERE ta.user_id = ? AND ta.academic_year_id = ? AND s.status = 1 ORDER BY c.nom ASC, s.nom ASC");
        $stmt->execute([$teacherId, $activeYearId]);
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
            'activeEvaluations' => $evals,
            'has_lmd_classes' => false
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

    private function getExtraFinancialCenterData($activeYearId): array
    {
        // 1. Encaissements par période
        $dailyCollections = (float)$this->db->query("
            SELECT COALESCE(SUM(amount), 0) FROM payments
            WHERE DATE(payment_date) = CURDATE() AND status = 'valide' AND academic_year_id = {$activeYearId}
        ")->fetchColumn();

        $weeklyCollections = (float)$this->db->query("
            SELECT COALESCE(SUM(amount), 0) FROM payments
            WHERE YEARWEEK(payment_date, 1) = YEARWEEK(CURDATE(), 1) AND status = 'valide' AND academic_year_id = {$activeYearId}
        ")->fetchColumn();

        $monthlyCollections = (float)$this->db->query("
            SELECT COALESCE(SUM(amount), 0) FROM payments
            WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE()) AND status = 'valide' AND academic_year_id = {$activeYearId}
        ")->fetchColumn();

        // 2. Dépenses par période
        $dailyExpenses = (float)$this->db->query("
            SELECT COALESCE(SUM(amount), 0) FROM expenses
            WHERE DATE(expense_date) = CURDATE() AND status = 'active' AND academic_year_id = {$activeYearId}
        ")->fetchColumn();

        $weeklyExpenses = (float)$this->db->query("
            SELECT COALESCE(SUM(amount), 0) FROM expenses
            WHERE YEARWEEK(expense_date, 1) = YEARWEEK(CURDATE(), 1) AND status = 'active' AND academic_year_id = {$activeYearId}
        ")->fetchColumn();

        $monthlyExpenses = (float)$this->db->query("
            SELECT COALESCE(SUM(amount), 0) FROM expenses
            WHERE MONTH(expense_date) = MONTH(CURDATE()) AND YEAR(expense_date) = YEAR(CURDATE()) AND status = 'active' AND academic_year_id = {$activeYearId}
        ")->fetchColumn();

        // 3. Répartition des réductions
        $studentDiscs = $this->db->query("
            SELECT COALESCE(dt.name, sd.motive) as name,
                   SUM(CASE WHEN sd.amount_type = 'percentage' THEN (c.frais_scolarite_brut * sd.amount / 100) ELSE sd.amount END) as total
            FROM student_discounts sd
            JOIN students s ON sd.student_id = s.id
            JOIN classes c ON s.class_id = c.id
            LEFT JOIN discount_types dt ON sd.discount_type_id = dt.id
            WHERE sd.status = 'active' AND s.is_withdrawn = 0 AND s.actif = 1 AND s.academic_year_id = {$activeYearId}
            GROUP BY name
        ")->fetchAll(PDO::FETCH_ASSOC);

        $classDiscs = $this->db->query("
            SELECT COALESCE(dt.name, cd.motive) as name,
                   SUM(CASE WHEN cd.amount_type = 'percentage' THEN (c.frais_scolarite_brut * cd.amount / 100) ELSE cd.amount END) as total
            FROM class_discounts cd
            JOIN classes c ON cd.class_id = c.id
            LEFT JOIN discount_types dt ON cd.discount_type_id = dt.id
            JOIN students s ON s.class_id = c.id
            WHERE cd.status = 'active' AND s.is_withdrawn = 0 AND s.actif = 1 AND s.academic_year_id = {$activeYearId}
            GROUP BY name
        ")->fetchAll(PDO::FETCH_ASSOC);

        $reductionsRepartition = [];
        foreach (array_merge($studentDiscs, $classDiscs) as $d) {
            $name = $d['name'] ?: 'Autre';
            $reductionsRepartition[$name] = ($reductionsRepartition[$name] ?? 0.0) + (float)$d['total'];
        }

        // 4. Répartition des bourses
        $studentSchols = $this->db->query("
            SELECT COALESCE(dt.name, ss.motive) as name,
                   SUM(CASE WHEN ss.amount_type = 'percentage' THEN (c.frais_scolarite_brut * ss.amount / 100) ELSE ss.amount END) as total
            FROM student_scholarships ss
            JOIN students s ON ss.student_id = s.id
            JOIN classes c ON s.class_id = c.id
            LEFT JOIN discount_types dt ON ss.discount_type_id = dt.id
            WHERE ss.status = 'active' AND s.is_withdrawn = 0 AND s.actif = 1 AND s.academic_year_id = {$activeYearId}
            GROUP BY name
        ")->fetchAll(PDO::FETCH_ASSOC);

        $classSchols = $this->db->query("
            SELECT COALESCE(dt.name, cs.motive) as name,
                   SUM(CASE WHEN cs.amount_type = 'percentage' THEN (c.frais_scolarite_brut * cs.amount / 100) ELSE cs.amount END) as total
            FROM class_scholarships cs
            JOIN classes c ON cs.class_id = c.id
            LEFT JOIN discount_types dt ON cs.discount_type_id = dt.id
            JOIN students s ON s.class_id = c.id
            WHERE cs.status = 'active' AND s.is_withdrawn = 0 AND s.actif = 1 AND s.academic_year_id = {$activeYearId}
            GROUP BY name
        ")->fetchAll(PDO::FETCH_ASSOC);

        $scholarshipsRepartition = [];
        foreach (array_merge($studentSchols, $classSchols) as $s) {
            $name = $s['name'] ?: 'Autre';
            $scholarshipsRepartition[$name] = ($scholarshipsRepartition[$name] ?? 0.0) + (float)$s['total'];
        }

        // 5. Situation des tranches
        $tranchesSituation = $this->db->query("
            SELECT installment_number, SUM(amount_planned) as total_planned, SUM(amount_paid) as total_paid
            FROM student_installments
            WHERE academic_year_id = {$activeYearId}
            GROUP BY installment_number
            ORDER BY installment_number ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // 6. Analyse des insolvables
        $insolventModel = new \App\Models\InsolventStudent();
        $insolventList = $insolventModel->getAll($activeYearId);
        $totalInsolventAmount = array_sum(array_column($insolventList, 'amount_due'));
        $totalInsolventCount = count($insolventList);

        $insolventsByClass = $this->db->query("
            SELECT c.nom as class_name, COUNT(ins.student_id) as count, SUM(ins.amount_due) as total_due
            FROM insolvent_students ins
            JOIN students s ON ins.student_id = s.id
            JOIN classes c ON s.class_id = c.id
            WHERE ins.academic_year_id = {$activeYearId}
            GROUP BY c.id, c.nom
            ORDER BY total_due DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $topInsolvents = array_slice($insolventList, 0, 10);

        return [
            'dailyCollections' => $dailyCollections,
            'weeklyCollections' => $weeklyCollections,
            'monthlyCollections' => $monthlyCollections,
            'dailyExpenses' => $dailyExpenses,
            'weeklyExpenses' => $weeklyExpenses,
            'monthlyExpenses' => $monthlyExpenses,
            'reductionsRepartition' => $reductionsRepartition,
            'scholarshipsRepartition' => $scholarshipsRepartition,
            'tranchesSituation' => $tranchesSituation,
            'totalInsolventAmount' => $totalInsolventAmount,
            'totalInsolventCount' => $totalInsolventCount,
            'insolventsByClass' => $insolventsByClass,
            'topInsolvents' => $topInsolvents
        ];
    }

    private function getExtraExecutiveAcademicData($activeYearId): array
    {
        $stmtGender = $this->db->prepare("SELECT sexe, COUNT(*) as count FROM students WHERE is_withdrawn = 0 AND actif = 1 AND academic_year_id = ? GROUP BY sexe");
        $stmtGender->execute([$activeYearId]);
        $genders = $stmtGender->fetchAll(PDO::FETCH_KEY_PAIR);
        $maleCount = (int)($genders['M'] ?? 0);
        $femaleCount = (int)($genders['F'] ?? 0);

        $stmtCycles = $this->db->prepare("
            SELECT cy.nom as cycle_nom, COUNT(s.id) as count
            FROM students s
            JOIN classes c ON s.class_id = c.id
            JOIN cycles cy ON c.cycle_id = cy.id
            WHERE s.is_withdrawn = 0 AND s.actif = 1 AND s.academic_year_id = ?
            GROUP BY cy.id, cy.nom
        ");
        $stmtCycles->execute([$activeYearId]);
        $cycleRepartition = $stmtCycles->fetchAll(PDO::FETCH_ASSOC);

        // overall success rate
        $stmtClassAvgs = $this->db->prepare("
            SELECT st.id as student_id,
                   SUM(g.valeur * s.coefficient) / SUM(s.coefficient) as moyenne
            FROM grades g
            JOIN students st ON st.id = g.student_id
            JOIN subjects s ON s.id = g.subject_id
            WHERE g.academic_year_id = ? AND st.is_withdrawn = 0 AND st.actif = 1
            GROUP BY st.id
        ");
        $stmtClassAvgs->execute([$activeYearId]);
        $avgs = $stmtClassAvgs->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $totalWithAverage = count($avgs);
        $passingCount = 0;
        foreach ($avgs as $row) {
            if ((float)$row['moyenne'] >= 10.0) {
                $passingCount++;
            }
        }
        $successRate = $totalWithAverage > 0 ? round(($passingCount / $totalWithAverage) * 100, 1) : 0;

        return [
            'maleCount' => $maleCount,
            'femaleCount' => $femaleCount,
            'cycleRepartition' => $cycleRepartition,
            'successRate' => $successRate
        ];
    }

    public function executiveDashboard()
    {
        $activeYearId = $this->getActiveAcademicYearId();

        // --- 1. Académique ---
        $stats_students = (int) $this->db->query("SELECT COUNT(*) FROM students WHERE is_withdrawn = 0 AND actif = 1 AND academic_year_id = {$activeYearId}")->fetchColumn();

        $stmtGender = $this->db->prepare("SELECT sexe, COUNT(*) as count FROM students WHERE is_withdrawn = 0 AND actif = 1 AND academic_year_id = ? GROUP BY sexe");
        $stmtGender->execute([$activeYearId]);
        $genders = $stmtGender->fetchAll(PDO::FETCH_KEY_PAIR);
        $maleCount = (int)($genders['M'] ?? 0);
        $femaleCount = (int)($genders['F'] ?? 0);

        $stmtCycles = $this->db->prepare("
            SELECT cy.nom as cycle_nom, COUNT(s.id) as count
            FROM students s
            JOIN classes c ON s.class_id = c.id
            JOIN cycles cy ON c.cycle_id = cy.id
            WHERE s.is_withdrawn = 0 AND s.actif = 1 AND s.academic_year_id = ?
            GROUP BY cy.id, cy.nom
        ");
        $stmtCycles->execute([$activeYearId]);
        $cycleRepartition = $stmtCycles->fetchAll(PDO::FETCH_ASSOC);

        $stmtClasses = $this->db->prepare("
            SELECT c.nom as class_nom, COUNT(s.id) as count
            FROM students s
            JOIN classes c ON s.class_id = c.id
            WHERE s.is_withdrawn = 0 AND s.actif = 1 AND s.academic_year_id = ?
            GROUP BY c.id, c.nom
            ORDER BY c.nom ASC
        ");
        $stmtClasses->execute([$activeYearId]);
        $classRepartition = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        // overall success rate
        $stmtClassAvgs = $this->db->prepare("
            SELECT st.id as student_id,
                   SUM(g.valeur * s.coefficient) / SUM(s.coefficient) as moyenne
            FROM grades g
            JOIN students st ON st.id = g.student_id
            JOIN subjects s ON s.id = g.subject_id
            WHERE g.academic_year_id = ? AND st.is_withdrawn = 0 AND st.actif = 1
            GROUP BY st.id
        ");
        $stmtClassAvgs->execute([$activeYearId]);
        $avgs = $stmtClassAvgs->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $totalWithAverage = count($avgs);
        $passingCount = 0;
        foreach ($avgs as $row) {
            if ((float)$row['moyenne'] >= 10.0) {
                $passingCount++;
            }
        }
        $successRate = $totalWithAverage > 0 ? round(($passingCount / $totalWithAverage) * 100, 1) : 0;

        // --- 2. Financier ---
        $financialData = $this->buildFinancialDashboardData();
        extract($financialData);

        // total remaining, reductions, bourses
        $totalRemaining = (float)$this->db->query("
            SELECT COALESCE(SUM(reste_a_payer), 0)
            FROM enrollments e
            JOIN students s ON e.student_id = s.id
            WHERE s.is_withdrawn = 0 AND s.actif = 1 AND e.academic_year_id = {$activeYearId}
        ")->fetchColumn();

        $totalReductions = (float)$this->db->query("
            SELECT COALESCE(SUM(total_reductions), 0)
            FROM enrollments e
            JOIN students s ON e.student_id = s.id
            WHERE s.is_withdrawn = 0 AND s.actif = 1 AND e.academic_year_id = {$activeYearId}
        ")->fetchColumn();

        $totalScholarships = (float)$this->db->query("
            SELECT COALESCE(SUM(total_bourses), 0)
            FROM enrollments e
            JOIN students s ON e.student_id = s.id
            WHERE s.is_withdrawn = 0 AND s.actif = 1 AND e.academic_year_id = {$activeYearId}
        ")->fetchColumn();

        // --- 3. Ressources Humaines ---
        $userRole = Session::get('user_role');
        if ($userRole === 'admin') {
            $personnelTotal = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role <> 'superadmin'")->fetchColumn();
            $teachersCount = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role = 'enseignant'")->fetchColumn();
            $adminsCount = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role IN ('admin', 'caissier', 'comptable', 'it_manager')")->fetchColumn();
        } else {
            $personnelTotal = (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
            $teachersCount = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role = 'enseignant'")->fetchColumn();
            $adminsCount = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role IN ('superadmin', 'admin', 'caissier', 'comptable', 'it_manager')")->fetchColumn();
        }

        include __DIR__ . '/../Views/pilotage/dashboard.php';
    }

    public function financialCenter()
    {
        $activeYearId = $this->getActiveAcademicYearId();

        // 1. Encaissements du jour, semaine, mois, année
        $dailyCollections = (float)$this->db->query("
            SELECT COALESCE(SUM(amount), 0) FROM payments
            WHERE DATE(payment_date) = CURDATE() AND status = 'valide' AND academic_year_id = {$activeYearId}
        ")->fetchColumn();

        $weeklyCollections = (float)$this->db->query("
            SELECT COALESCE(SUM(amount), 0) FROM payments
            WHERE YEARWEEK(payment_date, 1) = YEARWEEK(CURDATE(), 1) AND status = 'valide' AND academic_year_id = {$activeYearId}
        ")->fetchColumn();

        $monthlyCollections = (float)$this->db->query("
            SELECT COALESCE(SUM(amount), 0) FROM payments
            WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE()) AND status = 'valide' AND academic_year_id = {$activeYearId}
        ")->fetchColumn();

        $annualCollections = (float)$this->db->query("
            SELECT COALESCE(SUM(amount), 0) FROM payments
            WHERE status = 'valide' AND academic_year_id = {$activeYearId}
        ")->fetchColumn();

        // 2. Dépenses du jour, semaine, mois, année
        $dailyExpenses = (float)$this->db->query("
            SELECT COALESCE(SUM(amount), 0) FROM expenses
            WHERE DATE(expense_date) = CURDATE() AND status = 'active' AND academic_year_id = {$activeYearId}
        ")->fetchColumn();

        $weeklyExpenses = (float)$this->db->query("
            SELECT COALESCE(SUM(amount), 0) FROM expenses
            WHERE YEARWEEK(expense_date, 1) = YEARWEEK(CURDATE(), 1) AND status = 'active' AND academic_year_id = {$activeYearId}
        ")->fetchColumn();

        $monthlyExpenses = (float)$this->db->query("
            SELECT COALESCE(SUM(amount), 0) FROM expenses
            WHERE MONTH(expense_date) = MONTH(CURDATE()) AND YEAR(expense_date) = YEAR(CURDATE()) AND status = 'active' AND academic_year_id = {$activeYearId}
        ")->fetchColumn();

        $annualExpenses = (float)$this->db->query("
            SELECT COALESCE(SUM(amount), 0) FROM expenses
            WHERE status = 'active' AND academic_year_id = {$activeYearId}
        ")->fetchColumn();

        $netBalance = $annualCollections - $annualExpenses;

        // 2. Répartition des paiements (méthode de paiement)
        $stmtPayMethod = $this->db->prepare("
            SELECT payment_method, SUM(amount) as total
            FROM payments
            WHERE status = 'valide' AND academic_year_id = ?
            GROUP BY payment_method
        ");
        $stmtPayMethod->execute([$activeYearId]);
        $paymentMethodRepartition = $stmtPayMethod->fetchAll(PDO::FETCH_ASSOC);

        // 3. Répartition des réductions (motive/type)
        $studentDiscs = $this->db->query("
            SELECT COALESCE(dt.name, sd.motive) as name,
                   SUM(CASE WHEN sd.amount_type = 'percentage' THEN (c.frais_scolarite_brut * sd.amount / 100) ELSE sd.amount END) as total
            FROM student_discounts sd
            JOIN students s ON sd.student_id = s.id
            JOIN classes c ON s.class_id = c.id
            LEFT JOIN discount_types dt ON sd.discount_type_id = dt.id
            WHERE sd.status = 'active' AND s.is_withdrawn = 0 AND s.actif = 1 AND s.academic_year_id = {$activeYearId}
            GROUP BY name
        ")->fetchAll(PDO::FETCH_ASSOC);

        $classDiscs = $this->db->query("
            SELECT COALESCE(dt.name, cd.motive) as name,
                   SUM(CASE WHEN cd.amount_type = 'percentage' THEN (c.frais_scolarite_brut * cd.amount / 100) ELSE cd.amount END) as total
            FROM class_discounts cd
            JOIN classes c ON cd.class_id = c.id
            LEFT JOIN discount_types dt ON cd.discount_type_id = dt.id
            JOIN students s ON s.class_id = c.id
            WHERE cd.status = 'active' AND s.is_withdrawn = 0 AND s.actif = 1 AND s.academic_year_id = {$activeYearId}
            GROUP BY name
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Consolidation
        $reductionsRepartition = [];
        foreach (array_merge($studentDiscs, $classDiscs) as $d) {
            $name = $d['name'] ?: 'Autre';
            $reductionsRepartition[$name] = ($reductionsRepartition[$name] ?? 0.0) + (float)$d['total'];
        }

        // 4. Répartition des bourses (motive/type)
        $studentSchols = $this->db->query("
            SELECT COALESCE(dt.name, ss.motive) as name,
                   SUM(CASE WHEN ss.amount_type = 'percentage' THEN (c.frais_scolarite_brut * ss.amount / 100) ELSE ss.amount END) as total
            FROM student_scholarships ss
            JOIN students s ON ss.student_id = s.id
            JOIN classes c ON s.class_id = c.id
            LEFT JOIN discount_types dt ON ss.discount_type_id = dt.id
            WHERE ss.status = 'active' AND s.is_withdrawn = 0 AND s.actif = 1 AND s.academic_year_id = {$activeYearId}
            GROUP BY name
        ")->fetchAll(PDO::FETCH_ASSOC);

        $classSchols = $this->db->query("
            SELECT COALESCE(dt.name, cs.motive) as name,
                   SUM(CASE WHEN cs.amount_type = 'percentage' THEN (c.frais_scolarite_brut * cs.amount / 100) ELSE cs.amount END) as total
            FROM class_scholarships cs
            JOIN classes c ON cs.class_id = c.id
            LEFT JOIN discount_types dt ON cs.discount_type_id = dt.id
            JOIN students s ON s.class_id = c.id
            WHERE cs.status = 'active' AND s.is_withdrawn = 0 AND s.actif = 1 AND s.academic_year_id = {$activeYearId}
            GROUP BY name
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Consolidation
        $scholarshipsRepartition = [];
        foreach (array_merge($studentSchols, $classSchols) as $s) {
            $name = $s['name'] ?: 'Autre';
            $scholarshipsRepartition[$name] = ($scholarshipsRepartition[$name] ?? 0.0) + (float)$s['total'];
        }

        // 5. Situation des tranches
        $tranchesSituation = $this->db->query("
            SELECT installment_number, SUM(amount_planned) as total_planned, SUM(amount_paid) as total_paid
            FROM student_installments
            WHERE academic_year_id = {$activeYearId}
            GROUP BY installment_number
            ORDER BY installment_number ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // 6. Analyse des insolvables
        $insolventModel = new \App\Models\InsolventStudent();
        $insolventList = $insolventModel->getAll($activeYearId);
        $totalInsolventAmount = array_sum(array_column($insolventList, 'amount_due'));
        $totalInsolventCount = count($insolventList);

        $insolventsByClass = $this->db->query("
            SELECT c.nom as class_name, COUNT(ins.student_id) as count, SUM(ins.amount_due) as total_due
            FROM insolvent_students ins
            JOIN students s ON ins.student_id = s.id
            JOIN classes c ON s.class_id = c.id
            WHERE ins.academic_year_id = {$activeYearId}
            GROUP BY c.id, c.nom
            ORDER BY total_due DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // 7. Dépenses par catégorie & historique mensuel pour le pilotage financier
        $expensesByCategory = $this->db->query("
            SELECT ec.name as category_name, COALESCE(SUM(e.amount), 0) as total 
            FROM expenses e 
            JOIN expense_categories ec ON e.category_id = ec.id 
            WHERE e.status = 'active' AND e.academic_year_id = {$activeYearId} 
            GROUP BY ec.id, ec.name
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $monthlyExpensesHist = $this->db->query("
            SELECT DATE_FORMAT(expense_date, '%Y-%m') as month,
                   SUM(amount) as total
            FROM expenses
            WHERE status = 'active' AND academic_year_id = {$activeYearId}
              AND expense_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY month ORDER BY month ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $monthlyPayments = $this->db->query("
            SELECT DATE_FORMAT(payment_date, '%Y-%m') as month,
                   SUM(amount) as total
            FROM payments
            WHERE status = 'valide' AND academic_year_id = {$activeYearId}
              AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY month ORDER BY month ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/pilotage/financial.php';
    }
}
