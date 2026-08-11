<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Services\Import\ExcelTemplateService;
use App\Services\Import\TeacherImportProcessor;
use App\Services\AcademicYearService;
use PDO;

/**
 * TeacherController
 * 
 * Ce contrôleur orchestre la gestion des ressources humaines enseignantes.
 * Sa mission principale est de piloter les affectations (Enseignants <-> Matières <-> Classes)
 * tout en garantissant l'unicité des attributions pédagogiques.
 */
class TeacherController
{
    /** @var PDO Instance de connexion à la base de données */
    private $db;
    private AcademicYearService $academicYearService;

    /**
     * Initialise le contrôleur et verrouille l'accès aux administrateurs uniquement.
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->academicYearService = new AcademicYearService($this->db);
        \App\Core\PermissionManager::requirePermission('manage_teachers');
    }

    /**
     * Liste et filtre tous les enseignants de l'établissement.
     * Fournit une vue d'ensemble de la charge de travail (classes et matières).
     */
    public function index()
    {
        $page = (int) ($_GET['page'] ?? 1);
        $limit = 16;
        $offset = ($page - 1) * $limit;

        [$teachers, $filters, $totalCount] = $this->fetchTeachersFromFilters($limit, $offset);
        $totalPages = (int) ceil($totalCount / $limit);

        // Récupérer le paramètre d'affichage des noms d'enseignants sur les bulletins
        $settingsStore = new \App\Services\SettingsStore($this->db);
        $showTeacherNamesOnBulletins = (bool) $settingsStore->get('show_teacher_names_on_bulletins', '1');

        // Sécurité : si la page demandée est vide suite au changement de limite, on redirige
        if ($page > $totalPages && $totalCount > 0) {
            header("Location: /teachers?page=1");
            exit;
        }

        // Mode affectation rapide depuis le dashboard
        $assignContext = null;
        if (isset($_GET['assign_subject']) && isset($_GET['assign_class'])) {
            $stmt = $this->db->prepare("
                SELECT s.nom as subject_name, c.nom as class_name, tt.actif
                FROM subjects s
                JOIN classes c
                JOIN teaching_types tt ON s.teaching_type_id = tt.id
                WHERE s.id = ? AND c.id = ?
            ");
            $stmt->execute([(int) $_GET['assign_subject'], (int) $_GET['assign_class']]);
            $assignContext = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($assignContext && (int) $assignContext['actif'] === 1) {
                $assignContext['subject_id'] = (int) $_GET['assign_subject'];
                $assignContext['class_id'] = (int) $_GET['assign_class'];
            } else {
                $assignContext = null;
                Session::setFlash('error', __('subject_not_active_teaching_type') ?? 'La matière doit être rattachée à un type d\'enseignement actif.');
            }
        }

        // Récupérer les types d'enseignement actifs pour le formulaire (modale/page)
        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/teachers/index.php';
    }

    /**
     * Bascule l'affichage des noms d'enseignants sur les bulletins.
     */
    public function toggleTeacherNames()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $show = isset($_POST['show']) ? (int) $_POST['show'] : 0;
            
            $settingsStore = new \App\Services\SettingsStore($this->db);
            $settingsStore->set('show_teacher_names_on_bulletins', (string) $show);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => $show ? __('teacher_names_enabled') : __('teacher_names_disabled')
            ]);
            exit;
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => __('invalid_request')]);
        exit;
    }

    /**
     * Exporte le registre des enseignants en PDF (via template HTML).
     */
    public function export()
    {
        [$teachers] = $this->fetchTeachersFromFilters();

        $settingsStore = new \App\Services\SettingsStore($this->db);
        $logoManager   = \App\Core\LogoManager::getInstance($this->db);

        $school_name = $settingsStore->get('school_name', 'NotesMaster');
        $logo_base64 = $logoManager->hasLogo() ? $logoManager->getLogoBase64() : '';

        $ayRow = $this->db->query("SELECT nom FROM academic_years WHERE is_active = 1 LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
        $academic_year_nom = $ayRow['nom'] ?? date('Y');

        $title = __("teacher_register") ?: "Registre des Enseignants";

        ob_start();
        include __DIR__ . '/../Views/teachers/templates/export_pdf_teachers.php';
        $html = ob_get_clean();

        $this->streamPdf($html, 'Registre_Enseignants_' . date('Y-m-d') . '.pdf');
    }

    protected function streamPdf(string $html, string $filename): void
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
            $dompdf->stream($filename, ['Attachment' => true]);
        } catch (\Throwable $e) {
            echo 'Erreur lors de la génération du PDF : ' . $e->getMessage();
        }
        exit;
    }

    /**
     * Affiche le formulaire de création d'un nouveau professeur.
     */
    public function create()
    {
        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        include __DIR__ . '/../Views/teachers/create.php';
    }

    /**
     * Traite l'enregistrement d'un enseignant et son compte utilisateur.
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $errMsg = __('session_expired_retry') ?? 'Session expirée, veuillez réessayer.';
                if ($isAjax) {
                    header('Content-Type: application/json', true, 400);
                    echo json_encode(['success' => false, 'message' => $errMsg]);
                    exit;
                }
                Session::setFlash('error', $errMsg);
                header('Location: /teachers/create');
                exit;
            }

            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $teaching_type_ids = $_POST['teaching_type_ids'] ?? [];

            if (empty($nom) || empty($prenom) || empty($username) || empty($password) || empty($teaching_type_ids)) {
                $error = empty($teaching_type_ids)
                    ? __('select_at_least_one_teaching_type')
                    : __('teacher_required_fields');
                if ($isAjax) {
                    header('Content-Type: application/json', true, 400);
                    echo json_encode(['success' => false, 'message' => $error]);
                    exit;
                }
                $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/teachers/create.php';
                return;
            }

            try {
                // Vérifier l'unicité du nom d'utilisateur
                $chk = $this->db->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
                $chk->execute([$username]);
                if ($chk->fetch()) {
                    throw new \RuntimeException(__('teacher_username_taken') ?? 'Nom d\'utilisateur déjà pris.');
                }

                // Vérifier l'unicité de l'adresse e-mail
                if ($email !== '') {
                    $chkE = $this->db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
                    $chkE->execute([$email]);
                    if ($chkE->fetch()) {
                        throw new \RuntimeException(__('email_already_used') ?? 'Adresse e-mail déjà utilisée.');
                    }
                }

                $this->db->beginTransaction();

                $pwdHash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $this->db->prepare("INSERT INTO users (nom, prenom, username, email, password, role) VALUES (?, ?, ?, ?, ?, 'enseignant')");
                $stmt->execute([$nom, $prenom, $username, $email ?: null, $pwdHash]);
                $teacherId = (int) $this->db->lastInsertId();

                if (!empty($teaching_type_ids)) {
                    $stmtPivot = $this->db->prepare("INSERT INTO user_teaching_types (user_id, teaching_type_id) VALUES (?, ?)");
                    foreach ($teaching_type_ids as $tt_id) {
                        $stmtPivot->execute([$teacherId, (int) $tt_id]);
                    }
                }

                $this->db->commit();
                $successMsg = __('teacher_created_success');

                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => $successMsg]);
                    exit;
                }

                Session::setFlash('success', $successMsg);
                header("Location: /teachers");
                exit;
            } catch (\Throwable $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                $error = $e->getMessage();

                if ($isAjax) {
                    header('Content-Type: application/json', true, 400);
                    echo json_encode(['success' => false, 'message' => $error]);
                    exit;
                }

                $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/teachers/create.php';
            }
        }
    }

    /**
     * Supprime un profil enseignant.
     * IMPORTANT: Les notes saisies par l'enseignant ne sont JAMAIS supprimées.
     * Si l'enseignant a des notes, la suppression est refusée pour préserver l'historique.
     */
    public function delete($id)
    {
        // Vérifier si l'enseignant a des notes (grades)
        $stmt = $this->db->prepare("SELECT COUNT(*) as grade_count FROM grades WHERE teacher_id = ?");
        $stmt->execute([$id]);
        $history = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $hasGrades = ($history['grade_count'] > 0);
        
        if ($hasGrades) {
            // Refuser la suppression si l'enseignant a des notes
            Session::setFlash('error', __('teacher_has_grades_cannot_delete'));
            header("Location: /teachers");
            exit;
        }
        
        // Suppression autorisée uniquement si pas de notes
        // Les assignments seront supprimés en cascade par la contrainte FK
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ? AND role = 'enseignant'");
        $stmt->execute([$id]);
        Session::setFlash('success', __('teacher_deleted_success'));
        
        header("Location: /teachers");
        exit;
    }

    /**
     * Interface de pilotage des affectations pour un enseignant spécifique.
     * Cette vue permet de distribuer la charge de travail (Matières/Classes).
     * Ajout: Sélecteur d'année scolaire pour consultation historique.
     */
    public function assign($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND role = 'enseignant'");
        $stmt->execute([$id]);
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$teacher) {
            header("Location: /teachers");
            exit;
        }

        // Récupérer l'année scolaire sélectionnée pour consultation historique
        $selectedYearId = (int) ($_GET['academic_year_id'] ?? $this->academicYearService->getActiveYearId());
        $activeYearId = $this->academicYearService->getActiveYearId();
        
        // Récupérer toutes les années scolaires pour le sélecteur
        $academicYears = $this->db->query("SELECT id, nom, is_active FROM academic_years ORDER BY nom DESC")->fetchAll(PDO::FETCH_ASSOC);

        // Vérifier si l'année sélectionnée est différente de l'année active (mode historique)
        $isHistoricalView = ($selectedYearId !== $activeYearId);

        // Récupérer les types d'enseignement de l'enseignant
        $stmtTt = $this->db->prepare("SELECT teaching_type_id FROM user_teaching_types WHERE user_id = ?");
        $stmtTt->execute([$id]);
        $teacherTeachingTypes = $stmtTt->fetchAll(PDO::FETCH_COLUMN);

        $ttCondition = "";
        if (!empty($teacherTeachingTypes)) {
            $inTypes = implode(',', array_map('intval', $teacherTeachingTypes));
            $ttCondition = " AND (s.teaching_type_id IN ($inTypes) OR s.teaching_type_id IS NULL OR EXISTS (SELECT 1 FROM teacher_assignments ta_chk WHERE ta_chk.subject_id = s.id AND ta_chk.user_id = {$id}) OR EXISTS (SELECT 1 FROM timetable_entries te_chk WHERE te_chk.subject_id = s.id AND te_chk.teacher_id = {$id}))";
        }

        // Pour l'interface d'affectation, on utilise toujours l'année active
        // Le sélecteur sert uniquement à consulter l'historique
        $subjectsRaw = $this->db->query("
            SELECT s.id as subject_id, s.nom as subject_nom, c.id as class_id, c.nom as class_nom,
                   COALESCE(
                       (SELECT ta.user_id FROM teacher_assignments ta WHERE ta.subject_id = s.id AND (ta.class_id = c.id OR ta.class_id IS NULL) AND ta.user_id = {$id} AND (ta.academic_year_id = {$activeYearId} OR ta.academic_year_id IS NULL) LIMIT 1),
                       (SELECT te.teacher_id FROM timetable_entries te JOIN timetables t ON te.timetable_id = t.id WHERE te.subject_id = s.id AND t.class_id = c.id AND te.teacher_id = {$id} AND (t.academic_year_id = {$activeYearId} OR t.academic_year_id IS NULL) LIMIT 1),
                       (SELECT ta.user_id FROM teacher_assignments ta WHERE ta.subject_id = s.id AND (ta.class_id = c.id OR ta.class_id IS NULL) AND (ta.academic_year_id = {$activeYearId} OR ta.academic_year_id IS NULL) LIMIT 1),
                       (SELECT te.teacher_id FROM timetable_entries te JOIN timetables t ON te.timetable_id = t.id WHERE te.subject_id = s.id AND t.class_id = c.id AND (t.academic_year_id = {$activeYearId} OR t.academic_year_id IS NULL) LIMIT 1)
                   ) as teacher_id,
                   u.nom as teacher_nom, u.prenom as teacher_prenom
            FROM subjects s
            LEFT JOIN teaching_types tt ON s.teaching_type_id = tt.id
            JOIN subject_classes sc ON s.id = sc.subject_id
            JOIN classes c ON sc.class_id = c.id
            LEFT JOIN users u ON u.id = COALESCE(
                       (SELECT ta.user_id FROM teacher_assignments ta WHERE ta.subject_id = s.id AND (ta.class_id = c.id OR ta.class_id IS NULL) AND ta.user_id = {$id} AND (ta.academic_year_id = {$activeYearId} OR ta.academic_year_id IS NULL) LIMIT 1),
                       (SELECT te.teacher_id FROM timetable_entries te JOIN timetables t ON te.timetable_id = t.id WHERE te.subject_id = s.id AND t.class_id = c.id AND te.teacher_id = {$id} AND (t.academic_year_id = {$activeYearId} OR t.academic_year_id IS NULL) LIMIT 1),
                       (SELECT ta.user_id FROM teacher_assignments ta WHERE ta.subject_id = s.id AND (ta.class_id = c.id OR ta.class_id IS NULL) AND (ta.academic_year_id = {$activeYearId} OR ta.academic_year_id IS NULL) LIMIT 1),
                       (SELECT te.teacher_id FROM timetable_entries te JOIN timetables t ON te.timetable_id = t.id WHERE te.subject_id = s.id AND t.class_id = c.id AND (t.academic_year_id = {$activeYearId} OR t.academic_year_id IS NULL) LIMIT 1)
                   )
            WHERE s.status = 1 AND (tt.actif = 1 OR s.teaching_type_id IS NULL) {$ttCondition}
            ORDER BY s.nom ASC, c.nom ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer tous les enseignants pour le modal de transfert
        $allTeachers = $this->db->query("
            SELECT id, nom, prenom, username 
            FROM users 
            WHERE role = 'enseignant' AND status = 1 
            ORDER BY nom ASC, prenom ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $assignedSubjectsMap = [];
        $availableSubjectsMap = [];

        foreach ($subjectsRaw as $row) {
            $sid = (int) $row['subject_id'];
            $cid = (int) $row['class_id'];
            $tid = (int) $row['teacher_id'];

            $classData = [
                'id' => $cid,
                'nom' => $row['class_nom'],
                'other_teacher' => ($tid !== 0 && $tid !== (int) $id) ? $row['teacher_nom'] . ' ' . $row['teacher_prenom'] : null,
                'other_teacher_id' => ($tid !== 0 && $tid !== (int) $id) ? $tid : null
            ];

            if ($tid === (int) $id) {
                $assignedSubjectsMap[$sid]['nom'] = $row['subject_nom'];
                $assignedSubjectsMap[$sid]['classes'][] = $classData;
            } else {
                $availableSubjectsMap[$sid]['nom'] = $row['subject_nom'];
                $availableSubjectsMap[$sid]['classes'][] = $classData;
            }
        }

        include __DIR__ . '/../Views/teachers/assign.php';
    }

    public function directAssign()
    {
        $teacher_id = (int) ($_GET['teacher_id'] ?? 0);
        $subject_id = (int) ($_GET['subject_id'] ?? 0);
        $class_id = (int) ($_GET['class_id'] ?? 0);

        if (!$teacher_id || !$subject_id || !$class_id) {
            Session::setFlash('error', __('incomplete_assignment_data'));
            header("Location: /teachers");
            exit;
        }

        $academicYearId = $this->academicYearService->getActiveYearId();

        try {
            // Vérifier que la matière appartient à un type d'enseignement actif
            $stmtTtCheck = $this->db->prepare("
                SELECT COUNT(*) 
                FROM subjects s
                JOIN teaching_types tt ON s.teaching_type_id = tt.id
                WHERE s.id = ? AND tt.actif = 1
            ");
            $stmtTtCheck->execute([$subject_id]);
            if ((int) $stmtTtCheck->fetchColumn() === 0) {
                Session::setFlash('error', __('subject_not_active_teaching_type') ?? 'La matière doit être rattachée à un type d\'enseignement actif.');
                header("Location: /teachers");
                exit;
            }

            // Vérifier s'il y a déjà une affectation pour ce couple Matière-Classe
            $stmtCheck = $this->db->prepare("SELECT user_id FROM teacher_assignments WHERE subject_id = ? AND class_id = ? AND academic_year_id = ?");
            $stmtCheck->execute([$subject_id, $class_id, $academicYearId]);
            if ($stmtCheck->fetch()) {
                $this->db->prepare("DELETE FROM teacher_assignments WHERE subject_id = ? AND class_id = ? AND academic_year_id = ?")->execute([$subject_id, $class_id, $academicYearId]);
            }

            // Créer la nouvelle affectation
            $stmt = $this->db->prepare("INSERT INTO teacher_assignments (user_id, subject_id, class_id, academic_year_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$teacher_id, $subject_id, $class_id, $academicYearId]);

            Session::setFlash('success', __('teacher_assigned_success'));
            header("Location: /"); // Retour au dashboard
            exit;
        } catch (\Exception $e) {
            Session::setFlash('error', __('assignment_error') . " : " . $e->getMessage());
            header("Location: /teachers");
            exit;
        }
    }

    /**
     * Valide et enregistre la nouvelle charge de travail de l'enseignant.
     * Intègre un 'Barrage de Conflit' : Interdiction d'affecter une matière déjà prise dans une classe.
     * Ajout: Blocage des modifications sur les années fermées.
     */
    public function storeAssignment($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // VERIFICATION CSRF : Protection contre les requêtes contrefaites
            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', \__('session_expired_retry') ?? 'Session expirée, veuillez réessayer.');
                header("Location: /teachers/assign?id=" . $id);
                exit;
            }

            $assignments = $_POST['assignments'] ?? [];
            $academicYearId = $this->academicYearService->getActiveYearId();

            try {
                $this->db->beginTransaction();

                // 2. Contrôle de Conflit (Crucial) : On vérifie que chaque case (Matière, Classe) est libre.
                foreach ($assignments as $pair) {
                    [$subj_id, $cls_id] = explode('_', $pair);

                    // Valider que la matière appartient à un type d'enseignement actif
                    $stmtTtCheck = $this->db->prepare("
                        SELECT COUNT(*) 
                        FROM subjects s
                        JOIN teaching_types tt ON s.teaching_type_id = tt.id
                        WHERE s.id = ? AND tt.actif = 1
                    ");
                    $stmtTtCheck->execute([(int) $subj_id]);
                    if ((int) $stmtTtCheck->fetchColumn() === 0) {
                        $this->db->rollBack();
                        Session::setFlash('error', __('subject_not_active_teaching_type') ?? 'La matière affectée doit être rattachée à un type d\'enseignement actif.');
                        header("Location: /teachers/assign?id=" . $id);
                        exit;
                    }

                    $stmtCheck = $this->db->prepare("
                        SELECT u.id as user_id, u.nom, u.prenom, s.nom as subject_name, c.nom as class_name 
                        FROM teacher_assignments ta
                        JOIN users u ON ta.user_id = u.id
                        JOIN subjects s ON ta.subject_id = s.id
                        JOIN classes c ON ta.class_id = c.id
                        WHERE ta.subject_id = ? AND ta.class_id = ? AND ta.user_id != ? AND ta.academic_year_id = ?
                        LIMIT 1
                    ");
                    $stmtCheck->execute([(int) $subj_id, (int) $cls_id, (int) $id, $academicYearId]);
                    $conflict = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                    if ($conflict) {
                        $this->db->rollBack();
                        Session::setFlash('assignment_conflict', [
                            'subject_id' => (int) $subj_id,
                            'class_id' => (int) $cls_id,
                            'subject_name' => $conflict['subject_name'],
                            'class_name' => $conflict['class_name'],
                            'source_teacher_id' => (int) $conflict['user_id'],
                            'source_teacher_name' => $conflict['nom'] . ' ' . $conflict['prenom'],
                            'target_teacher_id' => (int) $id
                        ]);
                        Session::setFlash('error', __('assignment_already_taken', [
                            'teacher' => $conflict['nom'] . ' ' . $conflict['prenom'],
                            'subject' => $conflict['subject_name'],
                            'class' => $conflict['class_name']
                        ]));
                        header("Location: /teachers/assign?id=" . $id);
                        exit;
                    }
                }

                // 3. Purge et ré-affectation propre
                $stmtDelAssig = $this->db->prepare("
                    DELETE FROM teacher_assignments 
                    WHERE user_id = ? AND academic_year_id = ? 
                      AND subject_id IN (
                          SELECT s.id 
                          FROM subjects s 
                          JOIN teaching_types tt ON s.teaching_type_id = tt.id 
                          WHERE tt.actif = 1
                      )
                ");
                $stmtDelAssig->execute([$id, $academicYearId]);

                $stmtInsAssig = $this->db->prepare("INSERT INTO teacher_assignments (user_id, subject_id, class_id, academic_year_id) VALUES (?, ?, ?, ?)");
                foreach ($assignments as $pair) {
                    [$subj_id, $cls_id] = explode('_', $pair);
                    $stmtInsAssig->execute([$id, (int) $subj_id, (int) $cls_id, $academicYearId]);
                }

                $this->db->commit();
                Session::setFlash('success', __('workload_saved_success'));
                header("Location: /teachers");
                exit;
            } catch (\PDOException $e) {
                if ($this->db->inTransaction())
                    $this->db->rollBack();

                // Doublon au niveau SQL (protection ultime)
                if (str_contains($e->getMessage(), 'Duplicate')) {
                    Session::setFlash('error', __('integrity_error_already_assigned'));
                    header("Location: /teachers/assign?id=" . $id);
                    exit;
                }
                die("Erreur critique d'affectation : " . $e->getMessage());
            }
        }
    }

    /**
     * Annule/Retire une affectation spécifique (Matière - Classe) pour un enseignant.
     */
    public function removeAssignment()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $teacherId = (int)($_POST['teacher_id'] ?? 0);
            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', __('session_expired_retry') ?? 'Session expirée, veuillez réessayer.');
                header("Location: /teachers/assign?id=" . $teacherId);
                exit;
            }

            $subjectId = (int)($_POST['subject_id'] ?? 0);
            $classId = (int)($_POST['class_id'] ?? 0);
            $academicYearId = $this->academicYearService->getActiveYearId();

            if (!$teacherId || !$subjectId || !$classId) {
                Session::setFlash('error', __('incomplete_assignment_data') ?? 'Données d\'affectation incomplètes.');
                header("Location: /teachers/assign?id=" . $teacherId);
                exit;
            }

            try {
                // Supprimer l'affectation dans teacher_assignments
                $stmtDel = $this->db->prepare("
                    DELETE FROM teacher_assignments 
                    WHERE user_id = ? AND subject_id = ? AND (class_id = ? OR class_id IS NULL) 
                      AND (academic_year_id = ? OR academic_year_id IS NULL)
                ");
                $stmtDel->execute([$teacherId, $subjectId, $classId, $academicYearId]);

                Session::setFlash('success', __('assignment_removed_success') ?? 'L\'affectation a été annulée avec succès.');
            } catch (\Exception $e) {
                Session::setFlash('error', __('assignment_error') . " : " . $e->getMessage());
            }

            header("Location: /teachers/assign?id=" . $teacherId);
            exit;
        }
    }

    /**
     * Effectue le transfert d'un cours (Matière - Classe) d'un enseignant source vers un nouvel enseignant (cible).
     * Peut également créer à la volée un nouvel enseignant pour recevoir ce cours.
     */
    public function transferCourse()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $redirectTeacherId = (int)($_POST['redirect_teacher_id'] ?? $_POST['target_teacher_id'] ?? 0);
            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', __('session_expired_retry') ?? 'Session expirée, veuillez réessayer.');
                header("Location: /teachers/assign?id=" . $redirectTeacherId);
                exit;
            }

            $subjectId = (int)($_POST['subject_id'] ?? 0);
            $classId = (int)($_POST['class_id'] ?? 0);
            $sourceTeacherId = (int)($_POST['source_teacher_id'] ?? 0);
            $targetTeacherId = (int)($_POST['target_teacher_id'] ?? 0);
            $createNewTeacher = !empty($_POST['create_new_teacher']);
            $newTeacherName = trim($_POST['new_teacher_name'] ?? '');

            $academicYearId = $this->academicYearService->getActiveYearId();

            if (!$subjectId || !$classId) {
                Session::setFlash('error', __('incomplete_assignment_data') ?? 'Données d\'affectation incomplètes.');
                header("Location: /teachers/assign?id=" . $redirectTeacherId);
                exit;
            }

            try {
                $this->db->beginTransaction();

                // 1. Création à la volée du nouvel enseignant si demandé
                if ($createNewTeacher || $targetTeacherId === -1) {
                    if (empty($newTeacherName)) {
                        throw new \Exception("Veuillez saisir le nom et prénom du nouvel enseignant à créer.");
                    }

                    $parts = explode(' ', $newTeacherName, 2);
                    $nom = trim($parts[0]);
                    $prenom = trim($parts[1] ?? '');

                    $emailSlug = strtolower(preg_replace('/[^a-z0-9]/', '', $nom . $prenom)) . '_' . time() . '@institution.local';
                    $username = strtolower(preg_replace('/[^a-z0-9]/', '', $nom . $prenom)) . '_' . rand(100, 999);
                    $passwordHash = password_hash('Enseignant' . rand(1000, 9999), PASSWORD_DEFAULT);

                    $stmtIns = $this->db->prepare("
                        INSERT INTO users (nom, prenom, email, username, password, role, status, created_at)
                        VALUES (?, ?, ?, ?, ?, 'enseignant', 1, NOW())
                    ");
                    $stmtIns->execute([$nom, $prenom, $emailSlug, $username, $passwordHash]);
                    $targetTeacherId = (int)$this->db->lastInsertId();

                    // Raccorder le type d'enseignement de la matière
                    $stmtSubTt = $this->db->prepare("SELECT teaching_type_id FROM subjects WHERE id = ?");
                    $stmtSubTt->execute([$subjectId]);
                    $subTtId = (int)$stmtSubTt->fetchColumn();
                    if ($subTtId > 0) {
                        $this->db->prepare("
                            INSERT INTO user_teaching_types (user_id, teaching_type_id) 
                            VALUES (?, ?) 
                            ON DUPLICATE KEY UPDATE teaching_type_id = VALUES(teaching_type_id)
                        ")->execute([$targetTeacherId, $subTtId]);
                    }
                }

                if (!$targetTeacherId) {
                    throw new \Exception("Veuillez sélectionner un enseignant destinataire ou en créer un nouveau.");
                }

                // 2. Transférer / Réaffecter l'entrée dans teacher_assignments
                $stmtDelOld = $this->db->prepare("
                    DELETE FROM teacher_assignments 
                    WHERE subject_id = ? AND (class_id = ? OR class_id IS NULL) 
                      AND (academic_year_id = ? OR academic_year_id IS NULL)
                ");
                $stmtDelOld->execute([$subjectId, $classId, $academicYearId]);

                $stmtInsAssig = $this->db->prepare("
                    INSERT INTO teacher_assignments (user_id, subject_id, class_id, academic_year_id)
                    VALUES (?, ?, ?, ?)
                ");
                $stmtInsAssig->execute([$targetTeacherId, $subjectId, $classId, $academicYearId]);

                // 3. Transférer les créneaux dans emplois du temps publiés (timetable_entries)
                if ($sourceTeacherId > 0) {
                    $stmtTT = $this->db->prepare("
                        UPDATE timetable_entries te
                        JOIN timetables t ON te.timetable_id = t.id
                        SET te.teacher_id = ?
                        WHERE te.teacher_id = ? AND te.subject_id = ? AND t.class_id = ?
                          AND t.statut = 'publie'
                          AND (t.academic_year_id = ? OR t.academic_year_id IS NULL)
                    ");
                    $stmtTT->execute([$targetTeacherId, $sourceTeacherId, $subjectId, $classId, $academicYearId]);


                    // 4. Transférer les notes éventuellement déjà saisies par l'ancien enseignant
                    $stmtGrades = $this->db->prepare("
                        UPDATE grades 
                        SET teacher_id = ? 
                        WHERE teacher_id = ? AND subject_id = ? AND class_id = ?
                    ");
                    $stmtGrades->execute([$targetTeacherId, $sourceTeacherId, $subjectId, $classId]);
                }

                $this->db->commit();

                $stmtTargetUser = $this->db->prepare("SELECT nom, prenom FROM users WHERE id = ?");
                $stmtTargetUser->execute([$targetTeacherId]);
                $targetUser = $stmtTargetUser->fetch(PDO::FETCH_ASSOC);
                $targetName = $targetUser ? trim($targetUser['nom'] . ' ' . $targetUser['prenom']) : "Enseignant #$targetTeacherId";

                Session::setFlash('success', "Le cours et ses données ont été transférés avec succès à l'enseignant $targetName.");
                header("Location: /teachers/assign?id=" . $targetTeacherId);
                exit;

            } catch (\Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                Session::setFlash('error', "Échec du transfert de cours : " . $e->getMessage());
                header("Location: /teachers/assign?id=" . $redirectTeacherId);
                exit;
            }
        }
    }

    public function import(): void
    {
        include __DIR__ . '/../Views/teachers/import.php';
    }

    public function downloadTemplate(): void
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        ini_set('memory_limit', '512M');
        $lang = Session::get('app_lang', 'fr') === 'en' ? 'en' : 'fr';
        try {
            $svc = new ExcelTemplateService($this->db);
            $content = $svc->generateTeacherTemplate($lang);
            $filename = $lang === 'fr' ? 'Modele_Import_Enseignants_FR.xlsx' : 'Teacher_Import_Template_EN.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            echo $content;
            exit;
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            header('Location: /teachers/import');
            exit;
        }
    }

    public function upload(): void
    {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['import_file'])) {
            if ($isAjax) {
                header('Content-Type: application/json', true, 400);
                echo json_encode(['success' => false, 'message' => __('invalid_request')]);
                exit;
            }
            header('Location: /teachers/import');
            exit;
        }

        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $errMsg = __('session_expired_error');
            if ($isAjax) {
                header('Content-Type: application/json', true, 400);
                echo json_encode(['success' => false, 'message' => $errMsg]);
                exit;
            }
            Session::setFlash('error', $errMsg);
            header('Location: /teachers/import');
            exit;
        }

        $file = $_FILES['import_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'xlsx') {
            $errMsg = __('invalid_file_format_excel');
            if ($isAjax) {
                header('Content-Type: application/json', true, 400);
                echo json_encode(['success' => false, 'message' => $errMsg]);
                exit;
            }
            Session::setFlash('error', $errMsg);
            header('Location: /teachers/import');
            exit;
        }

        $processor = new TeacherImportProcessor($this->db);
        $lang = Session::get('app_lang', 'fr') === 'en' ? 'en' : 'fr';
        $result = $processor->process($file['tmp_name'], $lang);

        if ($result['success']) {
            $successMsg = __('teacher_import_success_count', ['count' => $result['count']]);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $successMsg, 'count' => $result['count']]);
                exit;
            }
            Session::setFlash('success', $successMsg);
            header('Location: /teachers');
            exit;
        }

        $errors = $result['errors'];
        if ($isAjax) {
            header('Content-Type: application/json', true, 400);
            echo json_encode([
                'success' => false,
                'message' => implode("<br>", array_map('htmlspecialchars', $errors)),
                'errors' => $errors
            ]);
            exit;
        }

        include __DIR__ . '/../Views/teachers/import.php';
    }

    public function edit(int $id): void
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND role = 'enseignant'");
        $stmt->execute([$id]);
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$teacher) {
            header('Location: /teachers');
            exit;
        }

        $teacherTeachingTypes = $this->db->prepare("SELECT teaching_type_id FROM user_teaching_types WHERE user_id = ?");
        $teacherTeachingTypes->execute([$id]);
        $teacher['teaching_type_ids'] = $teacherTeachingTypes->fetchAll(PDO::FETCH_COLUMN);

        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/teachers/edit.php';
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /teachers');
            exit;
        }
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', __('session_expired_retry') ?? 'Session expirée.');
            header('Location: /teachers/edit?id=' . $id);
            exit;
        }
        $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ? AND role = 'enseignant'");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            header('Location: /teachers');
            exit;
        }

        $nom = trim((string) ($_POST['nom'] ?? ''));
        $prenom = trim((string) ($_POST['prenom'] ?? ''));
        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = trim((string) ($_POST['password'] ?? ''));
        $teaching_type_ids = $_POST['teaching_type_ids'] ?? [];

        if ($nom === '' || $prenom === '' || $username === '') {
            $error = __('teacher_required_fields');
            $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? AND role = ?');
            $stmt->execute([$id, 'enseignant']);
            $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
            $teacher['teaching_type_ids'] = $teaching_type_ids;
            $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
            include __DIR__ . '/../Views/teachers/edit.php';
            return;
        }

        try {
            $chk = $this->db->prepare('SELECT id FROM users WHERE username = ? AND id != ? LIMIT 1');
            $chk->execute([$username, $id]);
            if ($chk->fetch()) {
                throw new \RuntimeException(__('teacher_username_taken'));
            }
            if ($email !== '') {
                $chkE = $this->db->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
                $chkE->execute([$email, $id]);
                if ($chkE->fetch()) {
                    throw new \RuntimeException(__('email_already_used'));
                }
            }

            $this->db->beginTransaction();

            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $this->db->prepare('UPDATE users SET nom = ?, prenom = ?, username = ?, email = ?, password = ? WHERE id = ? AND role = ?')
                    ->execute([$nom, $prenom, $username, $email ?: null, $hash, $id, 'enseignant']);
            } else {
                $this->db->prepare('UPDATE users SET nom = ?, prenom = ?, username = ?, email = ? WHERE id = ? AND role = ?')
                    ->execute([$nom, $prenom, $username, $email ?: null, $id, 'enseignant']);
            }

            // Mettre à jour les types d'enseignement
            $this->db->prepare("DELETE FROM user_teaching_types WHERE user_id = ?")->execute([$id]);
            if (!empty($teaching_type_ids)) {
                $stmtPivot = $this->db->prepare("INSERT INTO user_teaching_types (user_id, teaching_type_id) VALUES (?, ?)");
                foreach ($teaching_type_ids as $tt_id) {
                    $stmtPivot->execute([$id, (int) $tt_id]);
                }
            }

            $this->db->commit();

            Session::setFlash('success', __('teacher_updated_success'));
            header('Location: /teachers');
            exit;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $error = $e->getMessage();
            $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? AND role = ?');
            $stmt->execute([$id, 'enseignant']);
            $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
            $teacher['teaching_type_ids'] = $teaching_type_ids;
            $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
            include __DIR__ . '/../Views/teachers/edit.php';
        }
    }

    /**
     * Moteur de recherche interne pour les enseignants.
     */
    private function fetchTeachersFromFilters($limit = null, $offset = null)
    {
        $search = trim($_GET['q'] ?? '');
        $academicYearId = $this->academicYearService->getActiveYearId();

        // 1. Compter le total sans limite pour la pagination
        $countSql = "SELECT COUNT(*) FROM users u WHERE u.role = 'enseignant'";
        $countParams = [];
        if ($search !== '') {
            $like = '%' . $search . '%';
            $countSql .= " AND (u.nom LIKE ? OR u.prenom LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
            array_push($countParams, $like, $like, $like, $like);
        }
        $stmtCount = $this->db->prepare($countSql);
        $stmtCount->execute($countParams);
        $totalCount = (int) $stmtCount->fetchColumn();

        // 2. Récupérer les données avec limite
        $sql = "SELECT u.*,
                (SELECT GROUP_CONCAT(DISTINCT s.nom ORDER BY s.nom SEPARATOR ', ')
                 FROM subjects s
                 LEFT JOIN teaching_types tt ON s.teaching_type_id = tt.id
                 WHERE (tt.actif = 1 OR s.teaching_type_id IS NULL)
                   AND (
                       EXISTS (
                           SELECT 1 FROM teacher_assignments ta 
                           WHERE ta.subject_id = s.id AND ta.user_id = u.id AND (ta.academic_year_id = {$academicYearId} OR ta.academic_year_id IS NULL)
                       )
                       OR EXISTS (
                           SELECT 1 FROM timetable_entries te 
                           JOIN timetables t ON te.timetable_id = t.id 
                           WHERE te.subject_id = s.id AND te.teacher_id = u.id AND (t.academic_year_id = {$academicYearId} OR t.academic_year_id IS NULL)
                       )
                   )
                ) as subjects_list,
                (SELECT GROUP_CONCAT(DISTINCT c.nom ORDER BY c.nom SEPARATOR ', ')
                 FROM classes c
                 WHERE EXISTS (
                     SELECT 1 FROM teacher_assignments ta
                     JOIN subjects s ON ta.subject_id = s.id
                     LEFT JOIN teaching_types tt ON s.teaching_type_id = tt.id
                     WHERE ta.user_id = u.id 
                       AND (ta.class_id = c.id OR (ta.class_id IS NULL AND EXISTS (SELECT 1 FROM subject_classes sc WHERE sc.subject_id = ta.subject_id AND sc.class_id = c.id)))
                       AND (ta.academic_year_id = {$academicYearId} OR ta.academic_year_id IS NULL) 
                       AND (tt.actif = 1 OR s.teaching_type_id IS NULL)
                 ) OR EXISTS (
                     SELECT 1 FROM timetable_entries te
                     JOIN timetables t ON te.timetable_id = t.id
                     JOIN subjects s ON te.subject_id = s.id
                     LEFT JOIN teaching_types tt ON s.teaching_type_id = tt.id
                     WHERE te.teacher_id = u.id 
                       AND t.class_id = c.id 
                       AND (t.academic_year_id = {$academicYearId} OR t.academic_year_id IS NULL) 
                       AND (tt.actif = 1 OR s.teaching_type_id IS NULL)
                 )
                ) as classes_list,
                (SELECT COUNT(DISTINCT s.id)
                 FROM subjects s
                 LEFT JOIN teaching_types tt ON s.teaching_type_id = tt.id
                 WHERE (tt.actif = 1 OR s.teaching_type_id IS NULL)
                   AND (
                       EXISTS (
                           SELECT 1 FROM teacher_assignments ta 
                           WHERE ta.subject_id = s.id AND ta.user_id = u.id AND (ta.academic_year_id = {$academicYearId} OR ta.academic_year_id IS NULL)
                       )
                       OR EXISTS (
                           SELECT 1 FROM timetable_entries te 
                           JOIN timetables t ON te.timetable_id = t.id 
                           WHERE te.subject_id = s.id AND te.teacher_id = u.id AND (t.academic_year_id = {$academicYearId} OR t.academic_year_id IS NULL)
                       )
                   )
                ) as subjects_count
                FROM users u
                WHERE u.role = 'enseignant'";

        $params = [];
        if ($search !== '') {
            $like = '%' . $search . '%';
            $sql .= " AND (u.nom LIKE ? OR u.prenom LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
            array_push($params, $like, $like, $like, $like);
        }

        $sql .= " ORDER BY u.nom ASC, u.prenom ASC";

        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [$stmt->fetchAll(PDO::FETCH_ASSOC), ['q' => $search], $totalCount];
    }
}
