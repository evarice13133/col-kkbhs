<?php







namespace App\Controllers;







use App\Core\Database;



use App\Core\Session;



use App\Services\ActivityTracker;



use App\Services\AcademicYearService;



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



    private AcademicYearService $academicYearService;







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



        $this->academicYearService = new AcademicYearService($this->db);







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



        $filters = $this->getAssignmentFilters();

        $classes = $this->extractAccessibleClasses($assignments, (int) $filters['teaching_type_id']);
        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        $subjects = $this->extractAccessibleSubjects($assignments, (int) $filters['class_id'], (int) $filters['teaching_type_id']);



        $filteredAssignments = $this->filterAssignments($assignments, $filters);



        $dashboard = [];



        $evaluationTypes = $this->getAvailableEvaluationTypes((int) ($filters['teaching_type_id'] ?? 0));



        // Par défaut, la vue de saisie choisit la 1ère évaluation active (si aucun paramètre 'periode' n'est passé)

        $defaultPeriode = $evaluationTypes[0] ?? '';



        $_completedAssignments = $this->getAssignmentGradesProgressStatus(

            $filteredAssignments,

            $defaultPeriode

        );







        // Organisation des données pour un affichage intelligent



        // Les enseignants voient toujours leurs matières tant qu'une évaluation est active



        // Les administrateurs gardent le filtrage (uniquement ce qui reste à faire) pour la surveillance



        $activeSequencesCount = (int) $this->db->query("SELECT COUNT(*) FROM sequences s LEFT JOIN teaching_types tt ON s.teaching_type_id = tt.id WHERE s.is_active = 1 AND (tt.actif = 1 OR s.teaching_type_id IS NULL)")->fetchColumn();



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

            //    MAIS: une matière NON affectée à un enseignant doit TOUJOURS rester visible (même si les notes sont déjà complètes)

            $isUnassigned = empty($assignment['teacher_nom']);



            if ((!$isAdmin && $hasActiveEval) || !$isComplete || ($isAdmin && $isUnassigned)) {



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



        $evaluationTypes = $this->getAvailableEvaluationTypes((int) ($filters['teaching_type_id'] ?? 0));







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



        $format = trim((string) ($_GET['format'] ?? 'html'));







        // Mode : Liste des notes déjà saisies



        if ($exportMode !== 'report') {



            [$recentGrades] = $this->getAccessibleGrades();



            $filters = $this->getAssignmentFilters();



            $classId = (int) $filters['class_id'];







            if ($classId <= 0 || !$this->classHasFilledGrades($classId)) {



                die(__('choose_class_with_grades_before_export'));



            }







            // Classes are now shared across years, no year filtering

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







            include __DIR__ . '/../Views/grades/export_list.php';



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







        // Classes are now shared across years, no year filtering

        $classInfo = $this->fetchOne("SELECT id, nom, teaching_type_id FROM classes WHERE id = ?", [$classId]);



        $subjectInfo = $this->fetchOne("SELECT id, nom, coefficient FROM subjects WHERE id = ?", [$subjectId]);



        $activeYear = $this->getActiveAcademicYear();



        $activeEvaluations = $this->getAvailableEvaluationTypes((int) ($classInfo['teaching_type_id'] ?? 0));



        $students = $this->getStudentsForClass($classId);



        $teacherName = trim((string) Session::get('user_prenom') . ' ' . (string) Session::get('user_nom'));







        if ($format === 'pdf') {



            $settingsStore = new \App\Services\SettingsStore($this->db);



            $logoManager = \App\Core\LogoManager::getInstance($this->db);



            



            $school_name = $settingsStore->get('school_name', 'NotesMaster');



            $logo_base64 = $logoManager->hasLogo() ? $logoManager->getLogoBase64() : '';



            $title = "Fiche de collecte des notes";







            ob_start();



            include __DIR__ . '/../Views/grades/templates/export_pdf_file_report.php';



            $html = ob_get_clean();







            $this->streamPdf($html, "Fiche_Collecte_" . ($classInfo['nom'] ?? 'Classe') . "_" . date('Y-m-d') . ".pdf");



            return;



        }







        include __DIR__ . '/../Views/grades/export_report.php';



    }







    protected function streamPdf(string $html, string $filename)



    {



        while (ob_get_level()) {



            ob_end_clean();



        }







        $options = new \Dompdf\Options();



        $options->set('isHtml5ParserEnabled', true);



        $options->set('isRemoteEnabled', true);



        $options->set('defaultFont', 'Helvetica');







        $dompdf = new \Dompdf\Dompdf($options);



        $dompdf->loadHtml($html);



        $dompdf->setPaper('A4', 'portrait');







        try {



            $dompdf->render();



            $dompdf->stream($filename, ["Attachment" => true]);



        } catch (\Throwable $e) {



            echo "Erreur lors de la génération du PDF : " . $e->getMessage();



        }



        exit;



    }







    /**



     * Interface de saisie interactive des notes.



     * Garantit que seul l'enseignant affecté (ou un admin) peut modifier les notes.



     */



    public function saisie()



    {



        $class_id = (int) ($_GET['class_id'] ?? 0);



        $subject_id = (int) ($_GET['subject_id'] ?? 0);



        // Classes are now shared across years, no year filtering
        $classInfo = $this->fetchOne("SELECT id, nom, teaching_type_id FROM classes WHERE id = ?", [$class_id]);
        $subjectInfo = $this->fetchOne("SELECT id, nom, coefficient FROM subjects WHERE id = ?", [$subject_id]);

        if (!$classInfo || !$subjectInfo) {
            header("Location: /notes");
            exit;
        }

        // Vérification stricte des autorisations de saisie
        if (!$this->canManageAssignment($subject_id, $class_id)) {
            die(__('unauthorized_gradebook_access'));
        }

        // Récupérer uniquement les évaluations correspondant au type d'enseignement de la classe
        $periodes = $this->getAvailableEvaluationTypes((int) ($classInfo['teaching_type_id'] ?? 0));
        $periode = $_GET['periode'] ?? ($periodes[0] ?? '');

        // Validation de la période demandée pour éviter les injections de labels fantaisistes
        if (!$this->isAllowedEvaluationType($periode, $periodes)) {
            $periode = $periodes[0] ?? '';
        }

        $activeYear = $this->getActiveAcademicYear();
        if (!$activeYear) {
            die(__('no_active_year_defined'));
        }

        if (empty($periodes)) {
            die(__('no_active_evaluation_available'));
        }

        // Récupération enrichie des élèves de la classe avec leurs notes existantes si saisies
        // Exclut les élèves supprimés (actif = 0) et démissionnaires/abondon
        $sql = "SELECT st.id as student_id, st.nom, st.prenom,
                       g.valeur, g.appreciation
                FROM students st
                LEFT JOIN grades g
                    ON st.id = g.student_id
                    AND g.subject_id = ?
                    AND g.periode = ?
                    AND g.academic_year_id = ?
                WHERE st.class_id = ? 
                  AND st.academic_year_id = ? 
                  AND st.is_withdrawn = 0 
                  AND st.actif = 1 
                  AND st.status NOT IN ('Démission', 'Démissionnaire', 'Abandon')
                ORDER BY st.nom ASC, st.prenom ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$subject_id, $periode, $activeYear['id'], $class_id, $activeYear['id']]);

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



        // Vérification de la cohérence Type Enseignement vs Département
        $stmtCheck = $this->db->prepare("
            SELECT c.teaching_type_id as class_type, s.teaching_type_id as subject_type
            FROM classes c, subjects s
            WHERE c.id = ? AND s.id = ?
        ");
        $stmtCheck->execute([$class_id, $subject_id]);
        $typeCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($typeCheck && $typeCheck['class_type'] != $typeCheck['subject_type']) {
            Session::setFlash('error', __('incoherent_teaching_type_grade') ?? 'Erreur : Incohérence de Type d\'Enseignement entre la classe et la matière.');
            header("Location: /notes/saisie?class_id=$class_id&subject_id=$subject_id");
            exit;
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



            // Récupérer les informations de l'enseignant et de la matière pour les snapshots

            $teacherInfo = $this->db->prepare("SELECT nom, prenom FROM users WHERE id = ?")->execute([$teacher_id]);

            $teacherData = $this->db->query("SELECT nom, prenom FROM users WHERE id = $teacher_id LIMIT 1")->fetch(PDO::FETCH_ASSOC);

            $teacherNom = $teacherData['nom'] ?? 'Enseignant Supprimé';

            $teacherPrenom = $teacherData['prenom'] ?? '';

            

            $subjectData = $this->db->query("SELECT nom FROM subjects WHERE id = $subject_id LIMIT 1")->fetch(PDO::FETCH_ASSOC);

            $subjectNom = $subjectData['nom'] ?? 'Matière Supprimée';

            

            $userRole = Session::get('user_role');

            $createdByType = in_array($userRole, ['admin', 'superadmin']) ? 'admin' : 'enseignant';



            $this->db->beginTransaction();







            // Requête optimisée avec ON DUPLICATE KEY UPDATE pour gérer l'Upsert

            // Inclut les snapshots pour archiver les données historiques



            $stmt = $this->db->prepare("



                INSERT INTO grades (student_id, subject_id, teacher_id, academic_year_id, sequence_id, periode, valeur, appreciation, teacher_nom_snapshot, teacher_prenom_snapshot, subject_nom_snapshot, created_by_type)



                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)



                ON DUPLICATE KEY UPDATE



                    valeur = VALUES(valeur),



                    appreciation = VALUES(appreciation),



                    teacher_id = VALUES(teacher_id),



                    sequence_id = VALUES(sequence_id),



                    teacher_nom_snapshot = VALUES(teacher_nom_snapshot),



                    teacher_prenom_snapshot = VALUES(teacher_prenom_snapshot),



                    subject_nom_snapshot = VALUES(subject_nom_snapshot),



                    created_by_type = VALUES(created_by_type)



            ");







            foreach ($notes as $student_id => $valeur) {



                if ($valeur === '' || $valeur === null)



                    continue;







                $student_id = (int) $student_id;



                $valFloat = (float) str_replace(',', '.', (string) $valeur);







                // Validation de la plage de note (0-20)



                if ($student_id <= 0 || $valFloat < 0)



                    continue;



                



                if ($valFloat > 20) {



                    throw new \Exception("La note ne doit pas être supérieure à 20.");



                }







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



                    $appr,



                    $teacherNom,



                    $teacherPrenom,



                    $subjectNom,



                    $createdByType



                ]);



            }







            $this->db->commit();







            // Suivi d'activité pour les audits administratifs



            (new ActivityTracker($this->db))->recordGradesSaved($teacher_id, $periode, $class_id, $subject_id, $createdCount, $updatedCount);







            Session::setFlash('success', __('grades_saved_success'));



        } catch (\PDOException $e) {



            $this->db->rollBack();



            Session::setFlash('error', __('grade_save_failed', ['message' => $e->getMessage()]));



        } catch (\Exception $e) {



            if ($this->db->inTransaction()) $this->db->rollBack();



            Session::setFlash('error', $e->getMessage());



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

                                            COALESCE(s.teaching_type_id, c.teaching_type_id) as teaching_type_id,

                                            u.nom as teacher_nom, u.prenom as teacher_prenom

                                     FROM subject_classes sc

                                     JOIN subjects s ON sc.subject_id = s.id

                                     JOIN classes c ON sc.class_id = c.id

                                     LEFT JOIN teaching_types tt ON COALESCE(s.teaching_type_id, c.teaching_type_id) = tt.id

                                     LEFT JOIN teacher_assignments ta ON sc.subject_id = ta.subject_id AND sc.class_id = ta.class_id

                                     LEFT JOIN users u ON ta.user_id = u.id

                                     WHERE s.status = 1 AND (tt.actif = 1 OR COALESCE(s.teaching_type_id, c.teaching_type_id) IS NULL)

                                     ORDER BY c.nom ASC, s.nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        }



        $stmt = $this->db->prepare("SELECT ta.subject_id, ta.class_id, s.nom as subject_nom, c.nom as class_nom,

                                           COALESCE(s.teaching_type_id, c.teaching_type_id) as teaching_type_id,

                                           u.nom as teacher_nom, u.prenom as teacher_prenom

                                    FROM teacher_assignments ta

                                    JOIN subjects s ON ta.subject_id = s.id

                                    JOIN classes c ON ta.class_id = c.id

                                    LEFT JOIN teaching_types tt ON COALESCE(s.teaching_type_id, c.teaching_type_id) = tt.id

                                    JOIN users u ON ta.user_id = u.id

                                    WHERE ta.user_id = ? AND s.status = 1 AND (tt.actif = 1 OR COALESCE(s.teaching_type_id, c.teaching_type_id) IS NULL)

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







        // Utilise LEFT JOIN pour gérer les cas où teacher_id est NULL (notes orphelines)

        // Affiche les snapshots si l'enseignant a été supprimé



        $sql = "SELECT g.id, g.valeur, g.appreciation, g.periode, g.updated_at,



                       s.nom as student_nom, s.prenom as student_prenom,



                       sub.nom as subject_nom, sub.coefficient,



                       c.id as class_id, c.nom as class_nom,



                       sub.id as subject_id,



                       ay.nom as academic_year_nom,



                       COALESCE(u.nom, g.teacher_nom_snapshot) as teacher_nom, 

                       COALESCE(u.prenom, g.teacher_prenom_snapshot) as teacher_prenom,



                       g.teacher_id,



                       g.teacher_nom_snapshot,



                       g.teacher_prenom_snapshot,



                       g.subject_nom_snapshot,



                       g.created_by_type



                FROM grades g



                JOIN students s ON g.student_id = s.id



                JOIN classes c ON s.class_id = c.id



                JOIN subjects sub ON g.subject_id = sub.id



                LEFT JOIN users u ON g.teacher_id = u.id

                LEFT JOIN academic_years ay ON g.academic_year_id = ay.id

                LEFT JOIN teaching_types tt ON COALESCE(sub.teaching_type_id, c.teaching_type_id) = tt.id";

        $params = [];

        if (!in_array($role, ['superadmin', 'admin'], true)) {

            $sql .= " JOIN teacher_assignments ta ON ta.subject_id = g.subject_id AND ta.class_id = c.id AND ta.user_id = ?";

            $params[] = $user_id;

        }

        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = $activeYear['id'] ?? 0;

        $sql .= " WHERE g.academic_year_id = ? AND s.academic_year_id = ?";
        $params[] = $academicYearId;
        $params[] = $academicYearId;

        $teachingTypeId = (int) ($_GET['teaching_type_id'] ?? 0);
        if ($teachingTypeId > 0) {
            $sql .= " AND COALESCE(sub.teaching_type_id, c.teaching_type_id) = ?";
            $params[] = $teachingTypeId;
        }

        if ($classId > 0) {
            $sql .= " AND c.id = ?";
            $params[] = $classId;
        }

        if ($subjectId > 0) {
            $sql .= " AND sub.id = ?";
            $params[] = $subjectId;
        }

        $sql .= " AND s.is_withdrawn = 0 AND s.actif = 1 AND sub.status = 1 AND (tt.actif = 1 OR COALESCE(sub.teaching_type_id, c.teaching_type_id) IS NULL)";







        $sql .= " ORDER BY g.updated_at DESC, c.nom ASC, sub.nom ASC";



        $stmt = $this->db->prepare($sql);



        $stmt->execute($params);







        return [$stmt->fetchAll(PDO::FETCH_ASSOC), ['class_id' => $classId, 'subject_id' => $subjectId]];



    }







    private function extractAccessibleClasses(array $assignments, int $teachingTypeId = 0): array

    {

        $classes = [];

        foreach ($assignments as $a) {

            if ($teachingTypeId > 0 && (int) ($a['teaching_type_id'] ?? 0) !== $teachingTypeId) {
                continue;
            }

            $classes[(int) $a['class_id']] = [
                'id' => (int) $a['class_id'],
                'nom' => $a['class_nom'],
                'teaching_type_id' => (int) ($a['teaching_type_id'] ?? 0)
            ];

        }

        uasort($classes, fn($a, $b) => strcmp($a['nom'], $b['nom']));

        return array_values($classes);

    }







    private function extractAccessibleSubjects(array $assignments, int $classId = 0, int $teachingTypeId = 0): array

    {

        $subjects = [];

        foreach ($assignments as $a) {

            if ($classId > 0 && (int) $a['class_id'] !== $classId) {
                continue;
            }

            if ($teachingTypeId > 0 && (int) ($a['teaching_type_id'] ?? 0) !== $teachingTypeId) {
                continue;
            }

            $subjects[(int) $a['subject_id']] = [
                'id' => (int) $a['subject_id'],
                'nom' => $a['subject_nom'],
                'teaching_type_id' => (int) ($a['teaching_type_id'] ?? 0)
            ];

        }

        uasort($subjects, fn($a, $b) => strcmp($a['nom'], $b['nom']));

        return array_values($subjects);

    }







    private function getAssignmentFilters(): array



    {



        return [
            'class_id' => (int) ($_GET['class_id'] ?? 0), 
            'subject_id' => (int) ($_GET['subject_id'] ?? 0),
            'teaching_type_id' => (int) ($_GET['teaching_type_id'] ?? 0)
        ];



    }







    private function filterAssignments(array $assignments, array $filters): array



    {



        $cI = (int) ($filters['class_id'] ?? 0);



        $sI = (int) ($filters['subject_id'] ?? 0);



        $tI = (int) ($filters['teaching_type_id'] ?? 0);

        return array_values(array_filter(

            $assignments,

            fn($a) =>

            ($cI <= 0 || (int) $a['class_id'] === $cI) && ($sI <= 0 || (int) $a['subject_id'] === $sI) && ($tI <= 0 || (int) ($a['teaching_type_id'] ?? 0) === $tI)



        ));



    }







    private function getAvailableEvaluationTypes(?int $teachingTypeId = null): array
    {
        try {
            $sql = "SELECT s.label 
                    FROM sequences s 
                    LEFT JOIN teaching_types tt ON s.teaching_type_id = tt.id 
                    WHERE s.is_active = 1 AND (tt.actif = 1 OR s.teaching_type_id IS NULL)";
            $params = [];
            if ($teachingTypeId) {
                $sql .= " AND s.teaching_type_id = ?";
                $params[] = $teachingTypeId;
            }
            $sql .= " ORDER BY s.position ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
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
        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = $activeYear['id'] ?? 0;

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM students WHERE class_id = ? AND academic_year_id = ? AND actif = 1 AND is_withdrawn = 0 AND status NOT IN ('Démission', 'Démissionnaire', 'Abandon')");
        $stmt->execute([$classId, $academicYearId]);
        return (int) $stmt->fetchColumn();
    }







    /** Surpasse l'erreur du prepare execute précédent */



    private function fetchColumn()



    {



        return 0;



    } // Hack for simplicity in countStudentsInClass rewrite below







    private function countStudentsInClassFixed(int $classId): int
    {
        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = $activeYear['id'] ?? 0;

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM students WHERE class_id = ? AND academic_year_id = ? AND is_withdrawn = 0 AND actif = 1 AND status NOT IN ('Démission', 'Démissionnaire', 'Abandon')");
        $stmt->execute([$classId, $academicYearId]);



        return (int) $stmt->fetchColumn();



    }







    private function getStudentsForClass(int $classId): array
    {
        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = $activeYear['id'] ?? 0;

        $stmt = $this->db->prepare("SELECT id, nom, prenom FROM students WHERE class_id = ? AND academic_year_id = ? AND is_withdrawn = 0 AND actif = 1 AND status NOT IN ('Démission', 'Démissionnaire', 'Abandon') ORDER BY nom ASC, prenom ASC");
        $stmt->execute([$classId, $academicYearId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }







    private function classHasFilledGrades(int $classId): bool
    {
        if ($classId <= 0)
            return false;

        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = $activeYear['id'] ?? 0;

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM grades g JOIN students st ON st.id = g.student_id WHERE st.class_id = ? AND g.academic_year_id = ?");
        $stmt->execute([$classId, $academicYearId]);



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



        $stmt = $this->db->query("SELECT class_id, COUNT(*) as count FROM students WHERE is_withdrawn = 0 AND actif = 1 AND status NOT IN ('Démission', 'Démissionnaire', 'Abandon') GROUP BY class_id");



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



    /**

     * Progression (numérateur/dénominateur) par matière pour une SEULE période (séquence active par défaut).

     *

     * - Numérateur (X) = nombre d'élèves ayant au moins une note pour (classe + matière + période + année active)

     * - Dénominateur (Y) = nombre total d'élèves concernés dans la classe

     */

    private function getAssignmentGradesProgressStatus(array $assignments, string $periode): array

    {

        if (empty($assignments) || $periode === '') {

            return [];

        }



        $activeYear = $this->getActiveAcademicYear();

        if (!$activeYear) {

            return [];

        }

        $academicYearId = $activeYear['id'];

        // Récupérer toutes les séquences actives
        $allSequences = $this->db->query("
            SELECT s.label, s.teaching_type_id 
            FROM sequences s 
            LEFT JOIN teaching_types tt ON s.teaching_type_id = tt.id 
            WHERE s.is_active = 1 AND (tt.actif = 1 OR s.teaching_type_id IS NULL)
            ORDER BY s.position ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $evalTypesByTT = [];
        $allUniqueActiveLabels = [];
        foreach ($allSequences as $seq) {
            $ttId = (int)($seq['teaching_type_id'] ?? 0);
            $evalTypesByTT[$ttId][] = $seq['label'];
            $allUniqueActiveLabels[] = $seq['label'];
        }
        $allUniqueActiveLabels = array_values(array_unique($allUniqueActiveLabels));

        if (empty($allUniqueActiveLabels)) {
            return [];
        }

        // 1) Total élèves par classe (dénominateur) * nombre d'évaluations actives

        $studentCounts = [];

        $stmt = $this->db->query("SELECT class_id, COUNT(*) as count FROM students WHERE academic_year_id = {$academicYearId} AND is_withdrawn = 0 AND actif = 1 AND status NOT IN ('Démission', 'Démissionnaire', 'Abandon') GROUP BY class_id");

        while ($row = $stmt->fetch()) {

            $studentCounts[(int) $row['class_id']] = (int) $row['count'];

        }



        // 2) Nombre de notes distinctes saisies sur TOUTES les périodes actives

        // => on compte des paires (student_id, periode) pour coller au "nombre de notes"

        $classIds = array_values(array_unique(array_map('intval', array_column($assignments, 'class_id'))));

        $subjectIds = array_values(array_unique(array_map('intval', array_column($assignments, 'subject_id'))));



        if (empty($classIds) || empty($subjectIds)) {

            return [];

        }



        $classPlaceholders = implode(',', array_fill(0, count($classIds), '?'));

        $subjectPlaceholders = implode(',', array_fill(0, count($subjectIds), '?'));



        $role = Session::get('user_role');

        $teacherId = (int) Session::get('user_id');



        $teacherFilterSql = '';

        $teacherParams = [];



        // Pour un enseignant, afficher la progression basée sur SES saisies uniquement

        if (!in_array($role, ['superadmin', 'admin'], true)) {

            $teacherFilterSql = " AND g.teacher_id = ? ";

            $teacherParams[] = $teacherId;

        }



        $periodePlaceholders = implode(',', array_fill(0, count($allUniqueActiveLabels), '?'));



        $sql = "SELECT s.class_id,

                       g.subject_id,

                       COUNT(DISTINCT CONCAT(g.student_id, ':', g.periode)) as filled_count

                FROM grades g

                JOIN students s ON s.id = g.student_id

                WHERE g.academic_year_id = ?

                  AND s.academic_year_id = ?

                  AND g.periode IN ($periodePlaceholders)

                  AND s.is_withdrawn = 0

                  AND s.class_id IN ($classPlaceholders)

                  AND g.subject_id IN ($subjectPlaceholders)

                  $teacherFilterSql

                GROUP BY s.class_id, g.subject_id";



        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge([$academicYearId, $academicYearId], $allUniqueActiveLabels, $classIds, $subjectIds, $teacherParams));



        $gradeCounts = [];

        while ($row = $stmt->fetch()) {

            $gradeCounts[(int) $row['class_id'] . '_' . (int) $row['subject_id']] = (int) $row['filled_count'];

        }



        // 3) Mapper sur les affectations (matière/classe)

        $results = [];

        foreach ($assignments as $a) {

            $classId = (int) $a['class_id'];

            $subjectId = (int) $a['subject_id'];
            $ttId = (int) ($a['teaching_type_id'] ?? 0);



            $key = $classId . '_' . $subjectId;



            $studentsInClass = $studentCounts[$classId] ?? 0;

            // Récupérer le nombre exact d'évaluations attendues pour ce type d'enseignement
            $classEvalTypes = $evalTypesByTT[$ttId] ?? $evalTypesByTT[0] ?? [];
            $classEvalCount = count($classEvalTypes);

            $total = $studentsInClass * $classEvalCount;

            $filled = $gradeCounts[$key] ?? 0;



            $results[$key] = [

                'is_complete' => ($total > 0 && $filled >= $total),

                'filled' => $filled,

                'total' => $total

            ];

        }



        return $results;

    }



    /**

     * Affiche la page d'importation des notes.

     */

    public function import(): void

    {

        $class_id = (int) ($_GET['class_id'] ?? 0);

        $subject_id = (int) ($_GET['subject_id'] ?? 0);



        // Vérifier les autorisations

        $userRole = Session::get('user_role');

        $isAdmin = in_array($userRole, ['admin', 'superadmin'], true);



        // Pour les admins, permettre l'accès même sans matière spécifique

        if (!$isAdmin && !$this->canManageAssignment($subject_id, $class_id)) {

            die(__('unauthorized_gradebook_access'));

        }



        // Récupérer les informations de classe

        // Classes are now shared across years, no year filtering

        $classInfo = $this->fetchOne("SELECT id, nom FROM classes WHERE id = ?", [$class_id]);

        if (!$classInfo) {

            header("Location: /notes");

            exit;

        }



        // Récupérer les informations de matière si spécifiée

        $subjectInfo = null;

        if ($subject_id > 0) {

            $subjectInfo = $this->fetchOne("SELECT id, nom FROM subjects WHERE id = ?", [$subject_id]);

            if (!$subjectInfo) {

                header("Location: /notes");

                exit;

            }

        }



        include __DIR__ . '/../Views/grades/import.php';

    }



    /**

     * Télécharge le modèle Excel pour l'import des notes.

     */

    public function downloadTemplate(): void

    {

        while (ob_get_level()) {

            ob_end_clean();

        }

        ini_set('memory_limit', '512M');

        $lang = Session::get('app_lang', 'fr') === 'en' ? 'en' : 'fr';

        $class_id = (int) ($_GET['class_id'] ?? 0);

        $subject_id = (int) ($_GET['subject_id'] ?? 0);



        try {

            // Récupérer le nom de la classe pour le nom du fichier

            $className = '';

            if ($class_id > 0) {

                // Classes are now shared across years, no year filtering

                $classInfo = $this->fetchOne("SELECT nom FROM classes WHERE id = ?", [$class_id]);

                $className = $classInfo ? $classInfo['nom'] : '';

            }



            $svc = new \App\Services\Import\ExcelTemplateService($this->db);

            $content = $svc->generateGradeTemplate($lang, $class_id, $subject_id);



            // Construire le nom du fichier avec le nom de la classe

            $classNameClean = $className ? preg_replace('/[^a-zA-Z0-9]/', '_', $className) : 'Classe';

            if ($lang === 'fr') {

                $filename = "Modele_Import_Notes_{$classNameClean}_FR.xlsx";

            } else {

                $filename = "Grade_Import_Template_{$classNameClean}_EN.xlsx";

            }



            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

            header('Content-Disposition: attachment;filename="' . $filename . '"');

            header('Cache-Control: max-age=0');

            echo $content;

            exit;

        } catch (\Throwable $e) {

            Session::setFlash('error', $e->getMessage());

            header('Location: /notes/import?class_id=' . $class_id . '&subject_id=' . $subject_id);

            exit;

        }

    }



    /**

     * Traite le fichier Excel uploadé pour l'import des notes.

     */

    public function upload(): void

    {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['import_file'])) {

            header('Location: /notes');

            exit;

        }



        $class_id = (int) ($_POST['class_id'] ?? 0);

        $subject_id = (int) ($_POST['subject_id'] ?? 0);



        // Vérifier les autorisations

        $userRole = Session::get('user_role');

        $isAdmin = in_array($userRole, ['admin', 'superadmin'], true);



        // Pour les admins, permettre l'import même sans matière spécifique

        if (!$isAdmin && !$this->canManageAssignment($subject_id, $class_id)) {

            die(__('unauthorized_action'));

        }



        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {

            Session::setFlash('error', __('session_expired_retry') ?? 'Session expirée ou requête invalide.');

            header('Location: /notes/import?class_id=' . $class_id . '&subject_id=' . $subject_id);

            exit;

        }



        $file = $_FILES['import_file'];

        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));

        if ($ext !== 'xlsx') {

            Session::setFlash('error', __('invalid_file_format_excel'));

            header('Location: /notes/import?class_id=' . $class_id . '&subject_id=' . $subject_id);

            exit;

        }



        $teacherId = (int) Session::get('user_id');

        $userRole = Session::get('user_role');



        $processor = new \App\Services\Import\GradeImportProcessor($this->db, $teacherId, $userRole);

        $result = $processor->process((string) $file['tmp_name'], $class_id, $subject_id);



        if ($result['success']) {

            Session::setFlash('success', __('grades_imported_success', ['count' => $result['count']]));

            if ($subject_id > 0) {

                header('Location: /notes/saisie?class_id=' . $class_id . '&subject_id=' . $subject_id);

            } else {

                header('Location: /notes');

            }

            exit;

        }



        $errors = $result['errors'];

        // Classes are now shared across years, no year filtering

        $classInfo = $this->fetchOne("SELECT id, nom FROM classes WHERE id = ?", [$class_id]);

        $subjectInfo = null;

        if ($subject_id > 0) {

            $subjectInfo = $this->fetchOne("SELECT id, nom FROM subjects WHERE id = ?", [$subject_id]);

        }

        include __DIR__ . '/../Views/grades/import.php';

    }



    /**

     * Affiche l'historique complet des notes saisies.

     */

    public function history(): void

    {

        $userId = Session::get('user_id');

        $userRole = Session::get('user_role');

        $isAdmin = in_array($userRole, ['admin', 'superadmin'], true);



        // Récupérer les filtres

        $filters = [

            'q' => $_GET['q'] ?? '',

            'class_id' => (int) ($_GET['class_id'] ?? 0),

            'subject_id' => (int) ($_GET['subject_id'] ?? 0),

            'teaching_type_id' => (int) ($_GET['teaching_type_id'] ?? 0),

            'periode' => $_GET['periode'] ?? '',

        ];

        // Récupérer les données pour les filtres intelligemment selon le type d'enseignement actif
        $assignments = $this->getAccessibleAssignments();
        $classes = $this->extractAccessibleClasses($assignments, (int) $filters['teaching_type_id']);
        $subjects = $this->extractAccessibleSubjects($assignments, (int) $filters['class_id'], (int) $filters['teaching_type_id']);
        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = $activeYear['id'] ?? 0;
        $periods = $this->db->query("SELECT DISTINCT label FROM sequences WHERE is_active = 1 AND academic_year_id = {$academicYearId} ORDER BY position ASC")->fetchAll(PDO::FETCH_COLUMN);

        // Récupérer les notes récentes avec pagination

        $page = (int) ($_GET['page'] ?? 1);

        $perPage = 50;

        $offset = ($page - 1) * $perPage;

        // Construire les conditions WHERE

        $whereConditions = [
            'g.academic_year_id = (SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1)',
            's.academic_year_id = (SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1)',
            '(tt.actif = 1 OR COALESCE(sub.teaching_type_id, c.teaching_type_id) IS NULL)'
        ];

        $params = [];

        $countParams = [];

        if (!$isAdmin) {

            $whereConditions[] = 'g.teacher_id = ?';

            $params[] = $userId;

            $countParams[] = $userId;

        }

        if ($filters['teaching_type_id'] > 0) {

            $whereConditions[] = 'COALESCE(sub.teaching_type_id, c.teaching_type_id) = ?';

            $params[] = $filters['teaching_type_id'];

            $countParams[] = $filters['teaching_type_id'];

        }

        if (!empty($filters['q'])) {

            $whereConditions[] = '(s.nom LIKE ? OR s.prenom LIKE ? OR sub.nom LIKE ?)';

            $searchTerm = '%' . $filters['q'] . '%';

            $params[] = $searchTerm;

            $params[] = $searchTerm;

            $params[] = $searchTerm;

            $countParams[] = $searchTerm;

            $countParams[] = $searchTerm;

            $countParams[] = $searchTerm;

        }

        if ($filters['class_id'] > 0) {

            $whereConditions[] = 'c.id = ?';

            $params[] = $filters['class_id'];

            $countParams[] = $filters['class_id'];

        }

        if ($filters['subject_id'] > 0) {

            $whereConditions[] = 'sub.id = ?';

            $params[] = $filters['subject_id'];

            $countParams[] = $filters['subject_id'];

        }

        if (!empty($filters['periode'])) {

            $whereConditions[] = 'g.periode = ?';

            $params[] = $filters['periode'];

            $countParams[] = $filters['periode'];

        }

        $whereClause = implode(' AND ', $whereConditions);

        // Construire la requête

        $sql = "

            SELECT g.*, s.nom as student_nom, s.prenom as student_prenom,

                   sub.nom as subject_nom, c.nom as class_nom, c.id as class_id,

                   u.nom as teacher_nom, u.prenom as teacher_prenom

            FROM grades g

            JOIN students s ON g.student_id = s.id

            JOIN subjects sub ON g.subject_id = sub.id

            JOIN classes c ON s.class_id = c.id

            LEFT JOIN teaching_types tt ON COALESCE(sub.teaching_type_id, c.teaching_type_id) = tt.id

            LEFT JOIN users u ON g.teacher_id = u.id

            WHERE {$whereClause}

            ORDER BY g.created_at DESC

            LIMIT ? OFFSET ?

        ";

        $params[] = $perPage;

        $params[] = $offset;



        $countSql = "

            SELECT COUNT(*)

            FROM grades g

            JOIN students s ON g.student_id = s.id

            JOIN subjects sub ON g.subject_id = sub.id

            JOIN classes c ON s.class_id = c.id

            LEFT JOIN teaching_types tt ON COALESCE(sub.teaching_type_id, c.teaching_type_id) = tt.id

            WHERE {$whereClause}

        ";



        $grades = $this->db->prepare($sql);

        $grades->execute($params);

        $grades = $grades->fetchAll(PDO::FETCH_ASSOC);



        $countStmt = $this->db->prepare($countSql);

        $countStmt->execute($countParams);

        $total = (int) $countStmt->fetchColumn();

        $totalPages = (int) ceil($total / $perPage);



        include __DIR__ . '/../Views/grades/history.php';

    }



}





