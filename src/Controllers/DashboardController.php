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
     * Construit les données pour le tableau de bord financier (caissier / comptable).
     * NB : Ne contient AUCUNE donnée pédagogique. 
     */
    private function buildFinancialDashboardData(): array
    {
        $activeYearId = $this->getActiveAcademicYearId();

        $totalStudents = (int) $this->db->query(
            "SELECT COUNT(*) FROM students WHERE is_withdrawn = 0 AND actif = 1 AND academic_year_id = {$activeYearId}"
        )->fetchColumn();

        // Scolarité encaissée
        $totalTuitionCollected = (float) $this->db->query(
            "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE type = 'scolarite' AND academic_year_id = {$activeYearId}"
        )->fetchColumn();

        // Frais d'inscription encaissés
        $totalRegistrationCollected = (float) $this->db->query(
            "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE type = 'inscription' AND academic_year_id = {$activeYearId}"
        )->fetchColumn();

        // Recettes globales de la caisse (scolarité + inscription)
        $totalGeneralCollected = $totalTuitionCollected + $totalRegistrationCollected;

        // Total attendu scolarité
        $totalExpected = (float) $this->db->query(
            "SELECT COALESCE(SUM(c.frais_scolarite_brut), 0)
             FROM students s JOIN classes c ON s.class_id = c.id
             WHERE s.is_withdrawn = 0 AND s.actif = 1 AND s.academic_year_id = {$activeYearId}"
        )->fetchColumn();

        $totalInsolvent = (int) $this->db->query(
            "SELECT COUNT(DISTINCT student_id) FROM insolvent_students WHERE academic_year_id = {$activeYearId}"
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
            WHERE s.is_withdrawn = 0 AND s.actif = 1 AND s.academic_year_id = :academic_year_id2
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
        $classRegistrationStats = $stmtClassStats->fetchAll(\PDO::FETCH_ASSOC);

        // Calculer les totaux d'inscription depuis les statistiques par classe
        $totalEnrolled = 0;
        $totalNonEnrolled = 0;
        foreach ($classRegistrationStats as $row) {
            $totalEnrolled += (int)$row['enrolled_count'];
            $totalNonEnrolled += (int)$row['non_enrolled_count'];
        }

        // Évolution mensuelle des paiements (6 derniers mois) - comprend tous les paiements (frais scolarité et inscription)
        $monthlyPayments = $this->db->query(
            "SELECT DATE_FORMAT(payment_date, '%Y-%m') as month,
                    SUM(amount) as total
             FROM payments
             WHERE academic_year_id = {$activeYearId}
               AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY month ORDER BY month ASC"
        )->fetchAll(\PDO::FETCH_ASSOC);

        // Derniers paiements reçus (tous types confondus : inscription et scolarité)
        $recentPayments = $this->db->query(
            "SELECT p.payment_date, p.amount, p.payment_method, p.type,
                    CONCAT(s.nom, ' ', s.prenom) as student_name,
                    c.nom as class_nom
             FROM payments p
             JOIN students s ON p.student_id = s.id
             JOIN classes c ON s.class_id = c.id
             WHERE p.academic_year_id = {$activeYearId}
             ORDER BY p.payment_date DESC, p.id DESC LIMIT 10"
        )->fetchAll(\PDO::FETCH_ASSOC);

        // Assurer la rétrocompatibilité pour la vue existante
        $totalCollected = $totalTuitionCollected;

        return compact(
            'totalStudents', 'totalCollected', 'totalExpected',
            'totalInsolvent', 'collectionRate', 'monthlyPayments', 'recentPayments',
            'totalRegistrationCollected', 'totalTuitionCollected', 'totalGeneralCollected',
            'totalEnrolled', 'totalNonEnrolled', 'classRegistrationStats', 'policy'
        );
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
        $activeYearId = $this->getActiveAcademicYearId();
        $stats_students = (int) $this->db->query("SELECT COUNT(*) FROM students WHERE is_withdrawn = 0 AND actif = 1 AND academic_year_id = {$activeYearId}")->fetchColumn();
        // Classes are now shared across years, no year filtering
        $stats_classes = (int) $this->db->query("SELECT COUNT(*) FROM classes")->fetchColumn();
        $stats_subjects = (int) $this->db->query("SELECT COUNT(*) FROM subjects WHERE status = 1")->fetchColumn();
        $stats_subjects_inactive = (int) $this->db->query("SELECT COUNT(*) FROM subjects WHERE status = 0")->fetchColumn();
        $inactive_subjects_list = $this->db->query("SELECT id, nom FROM subjects WHERE status = 0 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $stats_users = (int) $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats_teachers_count = (int) $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'enseignant'")->fetchColumn();

        // 2. Récupérer TOUTES les affectations et TOUTES les classes avec effectifs (1 seule requête chacune)
        $allClassCounts = $this->getBulkClassStudentCounts([]);
        // Classes are now shared across years, no year filtering on classes
        $allAssignments = $this->db->query("SELECT user_id, class_id, subject_id, c.nom as class_nom 
                                            FROM teacher_assignments ta 
                                            JOIN classes c ON c.id = ta.class_id AND ta.academic_year_id = {$activeYearId}")->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        // 3. Récupérer TOUTES les notes saisies pour l'année active par classe/matière (sans distinction de l'enseignant)
        $allFilledCounts = $this->getBulkGlobalFilledCounts($activeYearId, $activeEvaluations);

        // 4. Calculer la progression globale basée sur toutes les notes saisies
        $globalExpected = 0;
        $globalFilled = 0;

        // Récupérer toutes les combinaisons classe/matière actives
        // Classes are now shared across years, but subject_classes are still year-specific
        $allSubjectClasses = $this->db->query("
            SELECT sc.class_id, sc.subject_id
            FROM subject_classes sc
            JOIN subjects s ON s.id = sc.subject_id
            WHERE sc.academic_year_id = {$activeYearId} AND s.status = 1
        ")->fetchAll(PDO::FETCH_ASSOC);

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

        // 5. Calculer la progression par enseignant basée sur les notes saisies dans leurs matières assignées
        $teacherMetrics = [];
        $teachersUnder50 = 0;

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
                // Utiliser les notes saisies globalement pour cette classe/matière (sans distinction de l'enseignant)
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

        // Tri par performance
        usort($teacherMetrics, fn($a, $b) => $b['progress_percent'] <=> $a['progress_percent']);

        // 4. Matières non affectées (Sujet lié à une classe mais sans prof assigné)
        $unassignedSubjectsRaw = $this->db->query("
            SELECT s.nom as subject_name, c.nom as class_name, c.id as class_id, s.id as subject_id
            FROM subject_classes sc
            JOIN subjects s ON s.id = sc.subject_id
            JOIN classes c ON c.id = sc.class_id
            LEFT JOIN teacher_assignments ta ON ta.class_id = sc.class_id AND ta.subject_id = sc.subject_id
            WHERE ta.user_id IS NULL AND s.status = 1
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
        
        // 6. Notifications de la vitrine (Landing Page)
        $notifications = [];
        $logPath = __DIR__ . '/../../logs/notifications.json';
        if (file_exists($logPath)) {
            $notifications = json_decode(file_get_contents($logPath), true) ?: [];
        }

        // 7. Statistiques Intelligentes du Tableau de Bord (Demande Utilisateur)
        // A. Meilleurs élèves de l'établissement
        $stmtTop = $this->db->prepare("
            SELECT st.id as student_id, st.nom, st.prenom, c.nom as classe_nom,
                   SUM(g.valeur * s.coefficient) / SUM(s.coefficient) as moyenne
            FROM grades g
            JOIN students st ON st.id = g.student_id
            JOIN classes c ON c.id = st.class_id
            JOIN subjects s ON s.id = g.subject_id
            WHERE g.academic_year_id = ? AND st.is_withdrawn = 0
            GROUP BY st.id, st.nom, st.prenom, c.nom
            ORDER BY moyenne DESC
            LIMIT 5
        ");
        $stmtTop->execute([$activeYearId]);
        $topStudents = $stmtTop->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // B. Élèves en difficulté
        $stmtStrug = $this->db->prepare("
            SELECT st.id as student_id, st.nom, st.prenom, c.nom as classe_nom,
                   SUM(g.valeur * s.coefficient) / SUM(s.coefficient) as moyenne
            FROM grades g
            JOIN students st ON st.id = g.student_id
            JOIN classes c ON c.id = st.class_id
            JOIN subjects s ON s.id = g.subject_id
            WHERE g.academic_year_id = ? AND st.is_withdrawn = 0
            GROUP BY st.id, st.nom, st.prenom, c.nom
            HAVING moyenne < 10
            ORDER BY moyenne ASC
            LIMIT 5
        ");
        $stmtStrug->execute([$activeYearId]);
        $strugglingStudents = $stmtStrug->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // C. Statistiques par classe
        $stmtClassAvgs = $this->db->prepare("
            SELECT st.class_id, c.nom as class_name, st.id as student_id,
                   SUM(g.valeur * s.coefficient) / SUM(s.coefficient) as moyenne
            FROM grades g
            JOIN students st ON st.id = g.student_id
            JOIN classes c ON c.id = st.class_id
            JOIN subjects s ON s.id = g.subject_id
            WHERE g.academic_year_id = ? AND st.is_withdrawn = 0
            GROUP BY st.class_id, c.nom, st.id
        ");
        $stmtClassAvgs->execute([$activeYearId]);
        $avgs = $stmtClassAvgs->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $classStats = [];
        $distribution = [
            'elite' => 0,       // >= 16
            'satisfait' => 0,   // 12 to 15.99
            'passable' => 0,    // 10 to 11.99
            'soutien' => 0      // < 10
        ];

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

            // Distribution
            $val = (float)$row['moyenne'];
            if ($val >= 16) {
                $distribution['elite']++;
            } elseif ($val >= 12) {
                $distribution['satisfait']++;
            } elseif ($val >= 10) {
                $distribution['passable']++;
            } else {
                $distribution['soutien']++;
            }
        }
        foreach ($classStats as &$cs) {
            $cs['class_avg'] = $cs['total_students'] > 0 ? ($cs['sum_averages'] / $cs['total_students']) : 0;
            $cs['success_rate'] = $cs['total_students'] > 0 ? round(($cs['passing_students'] / $cs['total_students']) * 100) : 0;
        }
        unset($cs);
        uasort($classStats, fn($a, $b) => $b['class_avg'] <=> $a['class_avg']);

        // D. Évolution des moyennes par période (Séquences actives)
        $activeSeqs = $this->getActiveEvaluations();
        $seqAverages = [];
        if (!empty($activeSeqs)) {
            $placeholders = implode(',', array_fill(0, count($activeSeqs), '?'));
            $stmtSeq = $this->db->prepare("
                SELECT g.periode, AVG(g.valeur) as moyenne
                FROM grades g
                WHERE g.academic_year_id = ? AND g.periode IN ($placeholders)
                GROUP BY g.periode
            ");
            $stmtSeq->execute(array_merge([$activeYearId], $activeSeqs));
            $seqAvgsRaw = $stmtSeq->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
            
            foreach ($activeSeqs as $seq) {
                $seqAverages[] = [
                    'periode' => $seq,
                    'moyenne' => isset($seqAvgsRaw[$seq]) ? (float)$seqAvgsRaw[$seq] : 0
                ];
            }
        }

        // E. Points forts & Faibles par matière avec enseignants affectés et classes
        $stmtSubjectStats = $this->db->prepare("
            SELECT s.id, s.nom, AVG(g.valeur) as moyenne,
                   GROUP_CONCAT(DISTINCT CONCAT(u.nom, ' ', u.prenom) SEPARATOR ', ') as teachers,
                   GROUP_CONCAT(DISTINCT c.nom SEPARATOR ', ') as classes
            FROM grades g
            JOIN subjects s ON s.id = g.subject_id
            JOIN teacher_assignments ta ON ta.subject_id = s.id
            JOIN users u ON ta.user_id = u.id
            JOIN classes c ON ta.class_id = c.id
            WHERE g.academic_year_id = ? AND s.status = 1
            GROUP BY s.id, s.nom
            ORDER BY moyenne DESC
        ");
        $stmtSubjectStats->execute([$activeYearId]);
        $subjectStats = $stmtSubjectStats->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $bestSubject = !empty($subjectStats) ? $subjectStats[0] : null;
        $worstSubject = !empty($subjectStats) && count($subjectStats) > 1 ? end($subjectStats) : null;

        // 5 meilleures disciplines avec enseignants affectés
        $top5Subjects = array_slice($subjectStats, 0, 5);

        // 5 pires disciplines avec enseignants affectés
        $bottom5Subjects = array_slice(array_reverse($subjectStats), 0, 5);

        // F. Disciplines moyennes par évaluation active
        $subjectByEval = [];
        if (!empty($activeEvaluations)) {
            foreach ($activeEvaluations as $eval) {
                $stmtEval = $this->db->prepare("
                    SELECT s.id, s.nom, AVG(g.valeur) as moyenne,
                           GROUP_CONCAT(DISTINCT CONCAT(u.nom, ' ', u.prenom) SEPARATOR ', ') as teachers,
                           GROUP_CONCAT(DISTINCT c.nom SEPARATOR ', ') as classes
                    FROM grades g
                    JOIN subjects s ON s.id = g.subject_id
                    JOIN teacher_assignments ta ON ta.subject_id = s.id
                    JOIN users u ON ta.user_id = u.id
                    JOIN classes c ON ta.class_id = c.id
                    WHERE g.academic_year_id = ? AND g.periode = ? AND s.status = 1
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
            'stats_classes' => $stats_classes,
            'stats_subjects' => $stats_subjects,
            'stats_subjects_inactive' => $stats_subjects_inactive,
            'inactive_subjects_list' => $inactive_subjects_list,
            'stats_users' => $stats_users,
            'stats_teachers' => $stats_teachers_count,
            'teachers_without_assignment' => $teachersWithoutAssignment,
            'globalExpected' => $globalExpected,
            'globalFilled' => $globalFilled,
            'globalPending' => max(0, $globalExpected - $globalFilled),
            'globalProgress' => $globalProgress,
            'teachersUnder50' => $teachersUnder50,
            'teacherMetrics' => $teacherMetrics,
            'unassignedSubjects' => $unassignedSubjectsRaw,
            'usageMetrics' => $usageMetrics,
            'teacherActivitySummary' => $teacherActivitySummary,
            'backupOverview' => $backupOverview,
            'landing_notifications' => $notifications,
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
            'activeEvaluations' => $activeEvaluations,
            'bulletin_printing_enabled' => $this->settingsStore->getBool('bulletin_printing_enabled', true),
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
        $stmt = $this->db->prepare("SELECT ta.class_id, ta.subject_id, c.nom AS class_nom 
                                    FROM teacher_assignments ta 
                                    JOIN classes c ON c.id = ta.class_id
                                    JOIN subjects s ON s.id = ta.subject_id
                                    WHERE ta.user_id = ? AND s.status = 1 ORDER BY c.nom ASC");
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
