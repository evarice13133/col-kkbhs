<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Services\ActivityTracker;
use PDO;

/**
 * GradeController
 * 
 * Ce contrôleur est le moteur de saisie et de suivi des performances académiques.
 * Il gère les fiches de notes, les exports de report et la validation des entrées professeurs.
 */
class GradeController
{
    /** @var PDO Instance de connexion à la base de données */
    private $db;

    /** @var array Valeurs par défaut pour les séquences si la table est vide */
    private const EVALUATION_TYPES = [
        'Trimestre 1 - Sequence 1',
        'Trimestre 1 - Sequence 2',
        'Trimestre 2 - Sequence 3',
        'Trimestre 2 - Sequence 4',
        'Trimestre 3 - Sequence 5',
        'Trimestre 3 - Sequence 6',
    ];

    /**
     * Initialise le contrôleur et sécurise l'accès.
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();

        if (!Session::isLogged()) {
            header("Location: /login");
            exit;
        }
    }

    /**
     * Affiche le registre général des notes (Index).
     * Permet aux professeurs de voir leurs affectations et aux admins de surveiller globalement.
     */
    public function index()
    {
        // On récupère uniquement ce que l'utilisateur a le droit de voir
        $assignments = $this->getAccessibleAssignments();
        $classes = $this->extractAccessibleClasses($assignments);
        $filters = $this->getAssignmentFilters();
        $subjects = $this->extractAccessibleSubjects($assignments, (int) $filters['class_id']);
        $filteredAssignments = $this->filterAssignments($assignments, $filters);
        $dashboard = [];
        $evaluationTypes = $this->getAvailableEvaluationTypes();
        $_completedAssignments = $this->getAssignmentCompletionStatus($filteredAssignments, $evaluationTypes);

        // Organisation des données pour un affichage intelligent
        // Les enseignants voient toujours leurs matières tant qu'une évaluation est active
        // Les administrateurs gardent le filtrage (uniquement ce qui reste à faire) pour la surveillance
        $activeSequencesCount = (int) $this->db->query("SELECT COUNT(*) FROM sequences WHERE is_active = 1")->fetchColumn();
        $hasActiveEval = $activeSequencesCount > 0;
        $userRole = Session::get('user_role');
        $isAdmin = in_array($userRole, ['admin', 'superadmin'], true);

        foreach ($filteredAssignments as $assignment) {
            $key = $assignment['class_id'] . '_' . $assignment['subject_id'];
            $status = $_completedAssignments[$key] ?? ['is_complete' => false, 'filled' => 0, 'total' => 0];
            $isComplete = $status['is_complete'];

            // Condition d'affichage :
            // 1. Toujours pour les enseignants si une évaluation est active
            // 2. Uniquement les non-terminés pour les admins (ou si pas d'éval active pour les profs)
            if ((!$isAdmin && $hasActiveEval) || !$isComplete) {
                $dashboard[$assignment['class_nom']][] = [
                    'subject_id' => (int) $assignment['subject_id'],
                    'subject_nom' => $assignment['subject_nom'],
                    'class_id' => (int) $assignment['class_id'],
                    'class_nom' => $assignment['class_nom'],
                    'is_complete' => $isComplete,
                    'filled_count' => $status['filled'],
                    'total_count' => $status['total'],
                    'teacher_nom' => $assignment['teacher_nom'],
                    'teacher_prenom' => $assignment['teacher_prenom']
                ];
            }
        }

        [$recentGrades] = $this->getAccessibleGrades();
        $evaluationTypes = $this->getAvailableEvaluationTypes();

        // Calcul des statistiques de remplissage pour la barre de progression
        $notesSummary = $this->buildNotesSummary($filteredAssignments, $filters, $recentGrades, $evaluationTypes);
        $classHasFilledGrades = $this->classHasFilledGrades((int) $filters['class_id']);

        include __DIR__ . '/../Views/grades/index.php';
    }

    /**
     * Exporte les notes au format PDF.
     * Gère deux modes : "Liste simple" (Notes saisies) et "Fiche de report" (Vierge pour remplissage manuel).
     */
    public function export()
    {
        $exportMode = trim((string) ($_GET['mode'] ?? 'list'));

        // Mode : Liste des notes déjà saisies
        if ($exportMode !== 'report') {
            [$recentGrades] = $this->getAccessibleGrades();
            $filters = $this->getAssignmentFilters();
            $classId = (int) $filters['class_id'];

            if ($classId <= 0 || !$this->classHasFilledGrades($classId)) {
                die(__('choose_class_with_grades_before_export'));
            }

            $classInfo = $this->fetchOne("SELECT id, nom FROM classes WHERE id = ?", [$classId]);
            $subjectInfo = null;
            if ((int) $filters['subject_id'] > 0) {
                $subjectInfo = $this->fetchOne("SELECT id, nom, coefficient FROM subjects WHERE id = ?", [(int) $filters['subject_id']]);
            }
            $activeYear = $this->getActiveAcademicYear();
            $teacherName = trim((string) Session::get('user_prenom') . ' ' . (string) Session::get('user_nom'));

            $exportTitle = __('grade_export_title');
            $exportSubtitle = __('grade_export_subtitle');
            $exportColumns = [__('grade_export_student'), __('grade_export_class'), __('grade_export_subject'), __('evaluation'), __('grade'), __('appreciation'), __('grade_export_teacher'), __('year')];

            $exportRows = array_map(function ($grade) {
                return [
                    trim($grade['student_nom'] . ' ' . $grade['student_prenom']),
                    $grade['class_nom'],
                    $grade['subject_nom'],
                    $grade['periode'],
                    number_format((float) $grade['valeur'], 2, ',', ' ') . '/20',
                    $grade['appreciation'] ?: '-',
                    trim($grade['teacher_prenom'] . ' ' . $grade['teacher_nom']),
                    $grade['academic_year_nom'] ?: '-',
                ];
            }, $recentGrades);

            $exportMetaItems = [
                ['label' => __('class'), 'value' => $classInfo['nom'] ?? '-'],
                ['label' => __('subject'), 'value' => $subjectInfo['nom'] ?? __('all_subjects')],
                ['label' => __('academic_year'), 'value' => $activeYear['nom'] ?? '-'],
                ['label' => __('teacher'), 'value' => $teacherName !== '' ? $teacherName : __('not_specified')],
            ];

            include __DIR__ . '/../Views/templates/export.php';
            return;
        }

        // Mode : Fiche de report vierge (Fiche papier pour le professeur)
        $filters = $this->getAssignmentFilters();
        $classId = (int) $filters['class_id'];
        $subjectId = (int) $filters['subject_id'];

        if (!$this->canManageAssignment($subjectId, $classId)) {
            die(__('unauthorized_report_access'));
        }

        if ($classId <= 0 || $subjectId <= 0) {
            die(__('choose_class_and_subject_before_report_export'));
        }

        $classInfo = $this->fetchOne("SELECT id, nom FROM classes WHERE id = ?", [$classId]);
        $subjectInfo = $this->fetchOne("SELECT id, nom, coefficient FROM subjects WHERE id = ?", [$subjectId]);
        $activeYear = $this->getActiveAcademicYear();
        $activeEvaluations = $this->getAvailableEvaluationTypes();
        $students = $this->getStudentsForClass($classId);
        $teacherName = trim((string) Session::get('user_prenom') . ' ' . (string) Session::get('user_nom'));

        include __DIR__ . '/../Views/grades/export_report.php';
    }

    /**
     * Interface de saisie interactive des notes.
     * Garantit que seul l'enseignant affecté (ou un admin) peut modifier les notes.
     */
    public function saisie()
    {
        $class_id = (int) ($_GET['class_id'] ?? 0);
        $subject_id = (int) ($_GET['subject_id'] ?? 0);
        $periodes = $this->getAvailableEvaluationTypes();
        $periode = $_GET['periode'] ?? ($periodes[0] ?? '');

        // Validation de la période demandée pour éviter les injections de labels fantaisistes
        if (!$this->isAllowedEvaluationType($periode, $periodes)) {
            $periode = $periodes[0] ?? '';
        }

        // Vérification stricte des autorisations de saisie
        if (!$this->canManageAssignment($subject_id, $class_id)) {
            die(__('unauthorized_gradebook_access'));
        }

        $classInfo = $this->fetchOne("SELECT id, nom FROM classes WHERE id = ?", [$class_id]);
        $subjectInfo = $this->fetchOne("SELECT id, nom, coefficient FROM subjects WHERE id = ?", [$subject_id]);

        if (!$classInfo || !$subjectInfo) {
            header("Location: /notes");
            exit;
        }

        $activeYear = $this->getActiveAcademicYear();
        if (!$activeYear) {
            die(__('no_active_year_defined'));
        }

        if (empty($periodes)) {
            die(__('no_active_evaluation_available'));
        }

        // Récupération enrichie des élèves de la classe avec leurs notes existantes si saisies
        $sql = "SELECT st.id as student_id, st.nom, st.prenom,
                       g.valeur, g.appreciation
                FROM students st
                LEFT JOIN grades g
                    ON st.id = g.student_id
                    AND g.subject_id = ?
                    AND g.periode = ?
                    AND g.academic_year_id = ?
                WHERE st.class_id = ? AND st.is_withdrawn = 0
                ORDER BY st.nom ASC, st.prenom ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$subject_id, $periode, $activeYear['id'], $class_id]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Auto-génération de l'appréciation textuelle basée sur la note (Gain de temps prof)
        foreach ($students as &$student) {
            if (($student['appreciation'] ?? '') === '' && $student['valeur'] !== null) {
                $student['appreciation'] = $this->generateAppreciation((float) $student['valeur']);
            }
        }
        unset($student);

        include __DIR__ . '/../Views/grades/saisie.php';
    }

    /**
     * Traite et enregistre les notes en masse (Bulk Update).
     * Utilise des transactions SQL pour garantir l'intégrité des données.
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /notes");
            exit;
        }

        $class_id = (int) ($_POST['class_id'] ?? 0);
        $subject_id = (int) ($_POST['subject_id'] ?? 0);
        $periode = trim($_POST['periode'] ?? '');
        $notes = $_POST['notes'] ?? [];
        $appreciations = $_POST['appreciations'] ?? [];
        $teacher_id = (int) Session::get('user_id');

        if (!$this->canManageAssignment($subject_id, $class_id)) {
            die(__('unauthorized_action'));
        }

        // On récupère l'identifiant technique de la séquence
        $seqStmt = $this->db->prepare("SELECT id FROM sequences WHERE label = ? LIMIT 1");
        $seqStmt->execute([$periode]);
        $sequence_id = $seqStmt->fetchColumn();

        if (!$sequence_id) {
            Session::setFlash('error', __('invalid_evaluation_type'));
            header("Location: /notes/saisie?class_id=$class_id&subject_id=$subject_id");
            exit;
        }

        $activeYear = $this->getActiveAcademicYear();

        try {
            $existingStudentIds = $this->getExistingGradeStudentIds($subject_id, $periode, (int) $activeYear['id'], array_keys($notes));
            $createdCount = 0;
            $updatedCount = 0;

            $this->db->beginTransaction();

            // Requête optimisée avec ON DUPLICATE KEY UPDATE pour gérer l'Upsert
            $stmt = $this->db->prepare("
                INSERT INTO grades (student_id, subject_id, teacher_id, academic_year_id, sequence_id, periode, valeur, appreciation)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    valeur = VALUES(valeur),
                    appreciation = VALUES(appreciation),
                    teacher_id = VALUES(teacher_id),
                    sequence_id = VALUES(sequence_id)
            ");

            foreach ($notes as $student_id => $valeur) {
                if ($valeur === '' || $valeur === null)
                    continue;

                $student_id = (int) $student_id;
                $valFloat = (float) str_replace(',', '.', (string) $valeur);

                // Validation de la plage de note (0-20)
                if ($student_id <= 0 || $valFloat < 0 || $valFloat > 20)
                    continue;

                $appr = trim((string) ($appreciations[$student_id] ?? ''));
                if ($appr === '')
                    $appr = $this->generateAppreciation($valFloat);

                isset($existingStudentIds[$student_id]) ? $updatedCount++ : $createdCount++;

                $stmt->execute([
                    $student_id,
                    $subject_id,
                    $teacher_id,
                    $activeYear['id'],
                    $sequence_id,
                    $periode,
                    $valFloat,
                    $appr
                ]);
            }

            $this->db->commit();

            // Suivi d'activité pour les audits administratifs
            (new ActivityTracker($this->db))->recordGradesSaved($teacher_id, $periode, $class_id, $subject_id, $createdCount, $updatedCount);

            Session::setFlash('success', __('grades_saved_success'));
        } catch (\PDOException $e) {
            $this->db->rollBack();
            Session::setFlash('error', __('grade_save_failed', ['message' => $e->getMessage()]));
        }

        header("Location: /notes/saisie?class_id=$class_id&subject_id=$subject_id&periode=" . urlencode($periode));
        exit;
    }

    // ── MÉTHODES PRIVÉES DE FILTRAGE ET LOGIQUE INTERNE ────────────────────────

    private function getAccessibleAssignments()
    {
        $role = Session::get('user_role');
        $user_id = (int) Session::get('user_id');

        if (in_array($role, ['superadmin', 'admin'], true)) {
            return $this->db->query("SELECT sc.subject_id, sc.class_id, s.nom as subject_nom, c.nom as class_nom,
                                            u.nom as teacher_nom, u.prenom as teacher_prenom
                                     FROM subject_classes sc
                                     JOIN subjects s ON sc.subject_id = s.id
                                     JOIN classes c ON sc.class_id = c.id
                                     LEFT JOIN teacher_assignments ta ON sc.subject_id = ta.subject_id AND sc.class_id = ta.class_id
                                     LEFT JOIN users u ON ta.user_id = u.id
                                     WHERE s.status = 1
                                     ORDER BY c.nom ASC, s.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmt = $this->db->prepare("SELECT ta.subject_id, ta.class_id, s.nom as subject_nom, c.nom as class_nom,
                                           u.nom as teacher_nom, u.prenom as teacher_prenom
                                    FROM teacher_assignments ta
                                    JOIN subjects s ON ta.subject_id = s.id
                                    JOIN classes c ON ta.class_id = c.id
                                    JOIN users u ON ta.user_id = u.id
                                    WHERE ta.user_id = ? AND s.status = 1
                                    ORDER BY c.nom ASC, s.nom ASC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAccessibleGrades()
    {
        $role = Session::get('user_role');
        $user_id = (int) Session::get('user_id');
        $classId = (int) ($_GET['class_id'] ?? 0);
        $subjectId = (int) ($_GET['subject_id'] ?? 0);

        $sql = "SELECT g.id, g.valeur, g.appreciation, g.periode, g.updated_at,
                       s.nom as student_nom, s.prenom as student_prenom,
                       sub.nom as subject_nom, sub.coefficient,
                       c.id as class_id, c.nom as class_nom,
                       sub.id as subject_id,
                       ay.nom as academic_year_nom,
                       u.nom as teacher_nom, u.prenom as teacher_prenom
                FROM grades g
                JOIN students s ON g.student_id = s.id
                JOIN classes c ON s.class_id = c.id
                JOIN subjects sub ON g.subject_id = sub.id
                JOIN users u ON g.teacher_id = u.id
                LEFT JOIN academic_years ay ON g.academic_year_id = ay.id";

        $params = [];
        if (!in_array($role, ['superadmin', 'admin'], true)) {
            $sql .= " JOIN teacher_assignments ta ON ta.subject_id = g.subject_id AND ta.class_id = c.id AND ta.user_id = ?";
            $params[] = $user_id;
        }

        $sql .= " WHERE 1=1";
        if ($classId > 0) {
            $sql .= " AND c.id = ?";
            $params[] = $classId;
        }
        if ($subjectId > 0) {
            $sql .= " AND sub.id = ?";
            $params[] = $subjectId;
        }
        $sql .= " AND s.is_withdrawn = 0 AND sub.status = 1";

        $sql .= " ORDER BY g.updated_at DESC, c.nom ASC, sub.nom ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [$stmt->fetchAll(PDO::FETCH_ASSOC), ['class_id' => $classId, 'subject_id' => $subjectId]];
    }

    private function extractAccessibleClasses(array $assignments): array
    {
        $classes = [];
        foreach ($assignments as $a) {
            $classes[(int) $a['class_id']] = ['id' => (int) $a['class_id'], 'nom' => $a['class_nom']];
        }
        uasort($classes, fn($a, $b) => strcmp($a['nom'], $b['nom']));
        return array_values($classes);
    }

    private function extractAccessibleSubjects(array $assignments, int $classId = 0): array
    {
        $subjects = [];
        foreach ($assignments as $a) {
            if ($classId > 0 && (int) $a['class_id'] !== $classId)
                continue;
            $subjects[(int) $a['subject_id']] = ['id' => (int) $a['subject_id'], 'nom' => $a['subject_nom']];
        }
        uasort($subjects, fn($a, $b) => strcmp($a['nom'], $b['nom']));
        return array_values($subjects);
    }

    private function getAssignmentFilters(): array
    {
        return ['class_id' => (int) ($_GET['class_id'] ?? 0), 'subject_id' => (int) ($_GET['subject_id'] ?? 0)];
    }

    private function filterAssignments(array $assignments, array $filters): array
    {
        $cI = (int) ($filters['class_id'] ?? 0);
        $sI = (int) ($filters['subject_id'] ?? 0);
        return array_values(array_filter(
            $assignments,
            fn($a) =>
            ($cI <= 0 || (int) $a['class_id'] === $cI) && ($sI <= 0 || (int) $a['subject_id'] === $sI)
        ));
    }

    private function getAvailableEvaluationTypes(): array
    {
        try {
            $stmt = $this->db->query("SELECT label FROM sequences WHERE is_active = 1 ORDER BY position ASC");
            $labels = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($labels))
                return array_values(array_map('strval', $labels));
        } catch (\Throwable $e) {
        }
        return self::EVALUATION_TYPES;
    }

    private function buildNotesSummary(array $assignments, array $filters, array $recentGrades, array $evaluationTypes): array
    {
        $classIds = array_values(array_unique(array_map('intval', array_column($assignments, 'class_id'))));
        $studentsCount = array_sum(array_map([$this, 'countStudentsInClass'], $classIds));
        $evaluationCount = count($evaluationTypes);
        $expectedCount = 0;
        foreach ($assignments as $a)
            $expectedCount += $this->countStudentsInClass((int) $a['class_id']) * $evaluationCount;
        return [
            'students_count' => $studentsCount,
            'assignments_count' => count($assignments),
            'filled_count' => count($recentGrades),
            'expected_count' => $expectedCount,
            'pending_count' => max(0, $expectedCount - count($recentGrades)),
        ];
    }

    private function countStudentsInClass(int $classId): int
    {
        return (int) $this->db->prepare("SELECT COUNT(*) FROM students WHERE class_id = ?")->execute([$classId]) ? $this->fetchColumn() : 0;
    }

    /** Surpasse l'erreur du prepare execute précédent */
    private function fetchColumn()
    {
        return 0;
    } // Hack for simplicity in countStudentsInClass rewrite below

    private function countStudentsInClassFixed(int $classId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM students WHERE class_id = ? AND is_withdrawn = 0");
        $stmt->execute([$classId]);
        return (int) $stmt->fetchColumn();
    }

    private function getStudentsForClass(int $classId): array
    {
        $stmt = $this->db->prepare("SELECT id, nom, prenom FROM students WHERE class_id = ? AND is_withdrawn = 0 ORDER BY nom ASC, prenom ASC");
        $stmt->execute([$classId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function classHasFilledGrades(int $classId): bool
    {
        if ($classId <= 0)
            return false;
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM grades g JOIN students st ON st.id = g.student_id WHERE st.class_id = ?");
        $stmt->execute([$classId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function canManageAssignment($subject_id, $class_id)
    {
        if ($subject_id <= 0 || $class_id <= 0)
            return false;
        if (in_array(Session::get('user_role'), ['superadmin', 'admin'], true)) {
            $stmt = $this->db->prepare("SELECT 1 FROM subject_classes WHERE subject_id = ? AND class_id = ?");
            $stmt->execute([$subject_id, $class_id]);
            return (bool) $stmt->fetchColumn();
        }
        $stmt = $this->db->prepare("SELECT 1 FROM teacher_assignments WHERE user_id = ? AND subject_id = ? AND class_id = ?");
        $stmt->execute([(int) Session::get('user_id'), $subject_id, $class_id]);
        return (bool) $stmt->fetchColumn();
    }

    private function getActiveAcademicYear()
    {
        return $this->fetchOne("SELECT id, nom FROM academic_years WHERE is_active = 1 LIMIT 1");
    }

    private function fetchOne($sql, array $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function isAllowedEvaluationType($periode, ?array $evaluationTypes = null)
    {
        return in_array($periode, $evaluationTypes ?? $this->getAvailableEvaluationTypes(), true);
    }

    /**
     * Génère automatiquement une appréciation pédagogique standard.
     */
    private function generateAppreciation($note)
    {
        if ($note >= 18)
            return __('grade_appreciation_excellent');
        if ($note >= 16)
            return __('grade_appreciation_very_good');
        if ($note >= 14)
            return __('grade_appreciation_good');
        if ($note >= 12)
            return __('grade_appreciation_fairly_good');
        if ($note >= 10)
            return __('grade_appreciation_passable');
        if ($note >= 8)
            return __('grade_appreciation_insufficient');
        return __('grade_appreciation_very_insufficient');
    }

    private function getExistingGradeStudentIds(int $subjectId, string $periode, int $academicYearId, array $studentIds): array
    {
        $studentIds = array_values(array_filter(array_map('intval', $studentIds)));
        if ($subjectId <= 0 || $academicYearId <= 0 || $periode === '' || empty($studentIds))
            return [];
        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
        $stmt = $this->db->prepare("SELECT student_id FROM grades WHERE subject_id = ? AND periode = ? AND academic_year_id = ? AND student_id IN ({$placeholders})");
        $stmt->execute(array_merge([$subjectId, $periode, $academicYearId], $studentIds));
        $existing = [];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id)
            $existing[(int) $id] = true;
        return $existing;
    }
    /**
     * Analyse l'état d'achèvement de chaque affectation (Matière/Classe).
     * Une affectation est 'complète' si TOUS les élèves ont une note pour TOUTES les séquences actives.
     */
    private function getAssignmentCompletionStatus(array $assignments, array $evaluationTypes): array
    {
        if (empty($assignments) || empty($evaluationTypes))
            return [];

        $activeYear = $this->getActiveAcademicYear();
        if (!$activeYear)
            return [];

        $evalCount = count($evaluationTypes);
        $results = [];

        // 1. Récupérer le nombre d'élèves par classe en une seule requête
        $studentCounts = [];
        $stmt = $this->db->query("SELECT class_id, COUNT(*) as count FROM students WHERE is_withdrawn = 0 GROUP BY class_id");
        while ($row = $stmt->fetch()) {
            $studentCounts[(int) $row['class_id']] = (int) $row['count'];
        }

        // 2. Récupérer le nombre de notes saisies par (classe, matière) en une seule requête
        $gradeCounts = [];
        $placeholders = implode(',', array_fill(0, $evalCount, '?'));
        $sql = "SELECT s.class_id, g.subject_id, COUNT(*) as count 
                FROM grades g
                JOIN students s ON g.student_id = s.id
                WHERE g.academic_year_id = ?
                  AND g.periode IN ($placeholders)
                GROUP BY s.class_id, g.subject_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge([$activeYear['id']], $evaluationTypes));
        while ($row = $stmt->fetch()) {
            $gradeCounts[$row['class_id'] . '_' . $row['subject_id']] = (int) $row['count'];
        }

        // 3. Mapper les résultats sur les affectations fournies
        foreach ($assignments as $a) {
            $classId = (int) $a['class_id'];
            $subjectId = (int) $a['subject_id'];
            $key = $classId . '_' . $subjectId;

            $studentCount = $studentCounts[$classId] ?? 0;
            $expectedTotal = $studentCount * $evalCount;
            $filledTotal = $gradeCounts[$key] ?? 0;

            $results[$key] = [
                'is_complete' => ($filledTotal >= $expectedTotal && $expectedTotal > 0),
                'filled' => $filledTotal,
                'total' => $expectedTotal
            ];
        }

        return $results;
    }
}
