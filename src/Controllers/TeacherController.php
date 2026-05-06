<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Services\Import\ExcelTemplateService;
use App\Services\Import\TeacherImportProcessor;
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

    /**
     * Initialise le contrôleur et verrouille l'accès aux administrateurs uniquement.
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        if (!Session::isLogged() || !in_array(Session::get('user_role'), ['superadmin', 'admin'])) {
            header("Location: /");
            exit;
        }
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

        // Sécurité : si la page demandée est vide suite au changement de limite, on redirige
        if ($page > $totalPages && $totalCount > 0) {
            header("Location: /teachers?page=1");
            exit;
        }

        // Mode affectation rapide depuis le dashboard
        $assignContext = null;
        if (isset($_GET['assign_subject']) && isset($_GET['assign_class'])) {
            $stmt = $this->db->prepare("
                SELECT s.nom as subject_name, c.nom as class_name 
                FROM subjects s, classes c 
                WHERE s.id = ? AND c.id = ?
            ");
            $stmt->execute([(int) $_GET['assign_subject'], (int) $_GET['assign_class']]);
            $assignContext = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($assignContext) {
                $assignContext['subject_id'] = (int) $_GET['assign_subject'];
                $assignContext['class_id'] = (int) $_GET['assign_class'];
            }
        }

        include __DIR__ . '/../Views/teachers/index.php';
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
        include __DIR__ . '/../Views/teachers/create.php';
    }

    /**
     * Traite l'enregistrement d'un enseignant et son compte utilisateur.
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($nom) || empty($username) || empty($password)) {
                $error = __('teacher_required_fields');
                include __DIR__ . '/../Views/teachers/create.php';
                return;
            }

            try {
                $pwdHash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $this->db->prepare("INSERT INTO users (nom, prenom, username, email, password, role) VALUES (?, ?, ?, ?, ?, 'enseignant')");
                $stmt->execute([$nom, $prenom, $username, $email ?: null, $pwdHash]);
                Session::setFlash('success', __('teacher_created_success'));
                header("Location: /teachers");
                exit;
            } catch (\PDOException $e) {
                $error = strpos($e->getMessage(), 'Duplicate') !== false ? __('teacher_username_taken') : __('teacher_db_error');
                include __DIR__ . '/../Views/teachers/create.php';
            }
        }
    }

    /**
     * Supprime un profil enseignant (Attention aux contraintes d'intégrité si des notes existent).
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ? AND role = 'enseignant'");
        $stmt->execute([$id]);
        Session::setFlash('success', __('teacher_deleted_success'));
        header("Location: /teachers");
        exit;
    }

    /**
     * Interface de pilotage des affectations pour un enseignant spécifique.
     * Cette vue permet de distribuer la charge de travail (Matières/Classes).
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


        // Analyse croisée : On cherche toutes les paires Matière-Classe définies, 
        // et on identifie celles déjà occupées par d'autres collègues.
        $subjectsRaw = $this->db->query("
            SELECT s.id as subject_id, s.nom as subject_nom, c.id as class_id, c.nom as class_nom,
                   u.id as teacher_id, u.nom as teacher_nom, u.prenom as teacher_prenom
            FROM subjects s
            JOIN subject_classes sc ON s.id = sc.subject_id
            JOIN classes c ON sc.class_id = c.id
            LEFT JOIN teacher_assignments ta ON (s.id = ta.subject_id AND c.id = ta.class_id)
            LEFT JOIN users u ON ta.user_id = u.id
            WHERE s.status = 1
            ORDER BY s.nom ASC, c.nom ASC
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
                'other_teacher' => ($tid !== 0 && $tid !== (int) $id) ? $row['teacher_nom'] . ' ' . $row['teacher_prenom'] : null
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

        try {
            // Vérifier s'il y a déjà une affectation pour ce couple Matière-Classe
            $stmtCheck = $this->db->prepare("SELECT user_id FROM teacher_assignments WHERE subject_id = ? AND class_id = ?");
            $stmtCheck->execute([$subject_id, $class_id]);
            if ($stmtCheck->fetch()) {
                $this->db->prepare("DELETE FROM teacher_assignments WHERE subject_id = ? AND class_id = ?")->execute([$subject_id, $class_id]);
            }

            // Créer la nouvelle affectation
            $stmt = $this->db->prepare("INSERT INTO teacher_assignments (user_id, subject_id, class_id) VALUES (?, ?, ?)");
            $stmt->execute([$teacher_id, $subject_id, $class_id]);

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

            try {
                $this->db->beginTransaction();

                // 2. Contrôle de Conflit (Crucial) : On vérifie que chaque case (Matière, Classe) est libre.
                foreach ($assignments as $pair) {
                    [$subj_id, $cls_id] = explode('_', $pair);

                    $stmtCheck = $this->db->prepare("
                        SELECT u.nom, u.prenom, s.nom as subject_name, c.nom as class_name 
                        FROM teacher_assignments ta
                        JOIN users u ON ta.user_id = u.id
                        JOIN subjects s ON ta.subject_id = s.id
                        JOIN classes c ON ta.class_id = c.id
                        WHERE ta.subject_id = ? AND ta.class_id = ? AND ta.user_id != ?
                        LIMIT 1
                    ");
                    $stmtCheck->execute([(int) $subj_id, (int) $cls_id, (int) $id]);
                    $conflict = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                    if ($conflict) {
                        // Un collègue a été plus rapide ? On bloque et on informe.
                        $this->db->rollBack();
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
                $stmtDelAssig = $this->db->prepare("DELETE FROM teacher_assignments WHERE user_id = ?");
                $stmtDelAssig->execute([$id]);

                $stmtInsAssig = $this->db->prepare("INSERT INTO teacher_assignments (user_id, subject_id, class_id) VALUES (?, ?, ?)");
                foreach ($assignments as $pair) {
                    [$subj_id, $cls_id] = explode('_', $pair);
                    $stmtInsAssig->execute([$id, (int) $subj_id, (int) $cls_id]);
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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['import_file'])) {
            header('Location: /teachers/import');
            exit;
        }
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', __('session_expired_error'));
            header('Location: /teachers/import');
            exit;
        }
        $file = $_FILES['import_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'xlsx') {
            Session::setFlash('error', __('invalid_file_format_excel'));
            header('Location: /teachers/import');
            exit;
        }
        $processor = new TeacherImportProcessor($this->db);
        $lang = Session::get('app_lang', 'fr') === 'en' ? 'en' : 'fr';
        $result = $processor->process($file['tmp_name'], $lang);
        if ($result['success']) {
            Session::setFlash('success', __('teacher_import_success_count', ['count' => $result['count']]));
            header('Location: /teachers');
            exit;
        }
        $errors = $result['errors'];
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

        if ($nom === '' || $prenom === '' || $username === '') {
            $error = __('teacher_required_fields');
            $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? AND role = ?');
            $stmt->execute([$id, 'enseignant']);
            $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
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

            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $this->db->prepare('UPDATE users SET nom = ?, prenom = ?, username = ?, email = ?, password = ? WHERE id = ? AND role = ?')
                    ->execute([$nom, $prenom, $username, $email ?: null, $hash, $id, 'enseignant']);
            } else {
                $this->db->prepare('UPDATE users SET nom = ?, prenom = ?, username = ?, email = ? WHERE id = ? AND role = ?')
                    ->execute([$nom, $prenom, $username, $email ?: null, $id, 'enseignant']);
            }

            Session::setFlash('success', __('teacher_updated_success'));
            header('Location: /teachers');
            exit;
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? AND role = ?');
            $stmt->execute([$id, 'enseignant']);
            $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
            include __DIR__ . '/../Views/teachers/edit.php';
        }
    }

    /**
     * Moteur de recherche interne pour les enseignants.
     */
    private function fetchTeachersFromFilters($limit = null, $offset = null)
    {
        $search = trim($_GET['q'] ?? '');

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
                    FROM teacher_assignments ta
                    JOIN subjects s ON ta.subject_id = s.id
                    WHERE ta.user_id = u.id) as subjects_list,
                (SELECT GROUP_CONCAT(DISTINCT c.nom ORDER BY c.nom SEPARATOR ', ')
                    FROM teacher_assignments ta
                    JOIN classes c ON ta.class_id = c.id
                    WHERE ta.user_id = u.id) as classes_list,
                (SELECT COUNT(DISTINCT ta.subject_id)
                    FROM teacher_assignments ta
                    WHERE ta.user_id = u.id) as subjects_count
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
