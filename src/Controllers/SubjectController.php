<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\PermissionManager;
use App\Services\Import\ExcelTemplateService;
use App\Services\Import\SubjectImportProcessor;
use App\Services\AcademicYearService;
use PDO;

class SubjectController
{
    private $db;
    private AcademicYearService $academicYearService;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->academicYearService = new AcademicYearService($this->db);
        if (!Session::isLogged()) {
            header("Location: /login");
            exit;
        }

        $this->db->exec("CREATE TABLE IF NOT EXISTS subject_classes (
            subject_id INT NOT NULL, class_id INT NOT NULL, academic_year_id INT NOT NULL,
            PRIMARY KEY (subject_id, class_id, academic_year_id),
            FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
            FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
            FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON UPDATE CASCADE
        )");

        // Ensure 'groupe' column exists in subjects table
        try {
            $this->db->exec("ALTER TABLE subjects ADD COLUMN groupe VARCHAR(50) DEFAULT 'Groupe 1'");
        } catch (\PDOException $e) {
            // Column probably already exists, ignore
        }
    }

    public function index()
    {
        $page = (int) ($_GET['page'] ?? 1);
        $limit = 16;
        $offset = ($page - 1) * $limit;

        [$subjects, $filters, $totalCount] = $this->fetchSubjectsFromFilters($limit, $offset);
        $totalPages = (int) ceil($totalCount / $limit);

        if ($page > $totalPages && $totalCount > 0) {
            header("Location: /subjects?page=1");
            exit;
        }

        $classes = $this->db->query("SELECT c.id, c.nom, c.teaching_type_id FROM classes c LEFT JOIN departments d ON c.department_id = d.id LEFT JOIN cycles cy ON c.cycle_id = cy.id LEFT JOIN sections sec ON c.section_id = sec.id LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id WHERE (c.department_id IS NULL OR d.status = 1) AND (c.cycle_id IS NULL OR cy.status = 1) AND (c.section_id IS NULL OR sec.status = 1) AND (c.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY c.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $departments = $this->db->query("SELECT d.id, d.nom, d.teaching_type_id FROM departments d LEFT JOIN teaching_types tt ON d.teaching_type_id = tt.id WHERE d.status = 1 AND (d.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY d.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        include __DIR__ . '/../Views/subjects/index.php';
    }

    public function export()
    {
        [$subjects] = $this->fetchSubjectsFromFilters();

        $settingsStore = new \App\Services\SettingsStore($this->db);
        $logoManager = \App\Core\LogoManager::getInstance($this->db);
        
        $school_name = $settingsStore->get('school_name', 'NotesMaster');
        $logo_base64 = $logoManager->hasLogo() ? $logoManager->getLogoBase64() : '';
        $title = "Registre des matières";

        ob_start();
        include __DIR__ . '/../Views/subjects/templates/export_pdf_subject.php';
        $html = ob_get_clean();

        $this->streamPdf($html, "Registre_Matieres_" . date('Y-m-d') . ".pdf");
    }

    protected function streamPdf(string $html, string $filename)
    {
        // Nettoyage complet des tampons de sortie
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

    public function create()
    {
        // Sécurité RBAC : Accès réservé aux administrateurs
        PermissionManager::requirePermission('manage_subjects');
        
        $classes = $this->db->query("SELECT c.id, c.nom, c.teaching_type_id FROM classes c LEFT JOIN departments d ON c.department_id = d.id LEFT JOIN cycles cy ON c.cycle_id = cy.id LEFT JOIN sections sec ON c.section_id = sec.id LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id WHERE (c.department_id IS NULL OR d.status = 1) AND (c.cycle_id IS NULL OR cy.status = 1) AND (c.section_id IS NULL OR sec.status = 1) AND (c.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY c.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $deptQuery = "SELECT d.id, d.nom, d.teaching_type_id FROM departments d LEFT JOIN teaching_types tt ON d.teaching_type_id = tt.id WHERE d.status = 1 AND (d.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY d.nom ASC";
        $departments = $this->db->query($deptQuery)->fetchAll(PDO::FETCH_ASSOC);
        $subjectGroups = $this->db->query("SELECT id, libelle, teaching_type_id FROM subject_groups WHERE status = 1 ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);
        include __DIR__ . '/../Views/subjects/create.php';
    }

    public function store()
    {
        // Sécurité RBAC : Accès réservé aux administrateurs
        PermissionManager::requirePermission('manage_subjects');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $coeff = (int) ($_POST['coefficient'] ?? 1);
            $subject_group_id = !empty($_POST['subject_group_id']) ? (int) $_POST['subject_group_id'] : null;
            $groupe = trim($_POST['groupe'] ?? 'Groupe 1');
            $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;
            $department_id = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;
            $classes_ids = array_values(array_unique(array_map('intval', $_POST['classes'] ?? [])));

            $code_uv = !empty($_POST['code_uv']) ? trim($_POST['code_uv']) : null;
            $code_ue = !empty($_POST['code_ue']) ? trim($_POST['code_ue']) : null;
            if ($teaching_type_id) {
                $stmtTt = $this->db->prepare("SELECT code FROM teaching_types WHERE id = ?");
                $stmtTt->execute([$teaching_type_id]);
                $ttCode = $stmtTt->fetchColumn();
                if ($ttCode !== 'LMD') {
                    $code_uv = null;
                    $code_ue = null;
                }
            } else {
                $code_uv = null;
                $code_ue = null;
            }

            // Si un groupe de modules est sélectionné, récupérer son libellé pour assurer la rétrocompatibilité du champ 'groupe'
            if ($subject_group_id) {
                $grpStmt = $this->db->prepare("SELECT libelle FROM subject_groups WHERE id = ?");
                $grpStmt->execute([$subject_group_id]);
                $grpLib = $grpStmt->fetchColumn();
                if ($grpLib) $groupe = $grpLib;
            }

            if (empty($nom) || empty($classes_ids)) {
                $error = \__('subject_name_and_class_required');
                $classes = $this->db->query("SELECT c.id, c.nom, c.teaching_type_id FROM classes c LEFT JOIN departments d ON c.department_id = d.id LEFT JOIN cycles cy ON c.cycle_id = cy.id LEFT JOIN sections sec ON c.section_id = sec.id LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id WHERE (c.department_id IS NULL OR d.status = 1) AND (c.cycle_id IS NULL OR cy.status = 1) AND (c.section_id IS NULL OR sec.status = 1) AND (c.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY c.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $deptQuery = "SELECT d.id, d.nom, d.teaching_type_id FROM departments d LEFT JOIN teaching_types tt ON d.teaching_type_id = tt.id WHERE d.status = 1 AND (d.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY d.nom ASC";
                $departments = $this->db->query($deptQuery)->fetchAll(PDO::FETCH_ASSOC);
                $subjectGroups = $this->db->query("SELECT id, libelle, teaching_type_id FROM subject_groups WHERE status = 1 ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/subjects/create.php';
                return;
            }

            // Validation: teaching_type_id doit correspondre à celui du département si un département est sélectionné
            if ($department_id) {
                $deptStmt = $this->db->prepare("SELECT teaching_type_id FROM departments WHERE id = ?");
                $deptStmt->execute([$department_id]);
                $deptTeachingTypeId = $deptStmt->fetchColumn();
                if ($deptTeachingTypeId && $deptTeachingTypeId != $teaching_type_id) {
                    $error = __('department_teaching_type_mismatch') ?? 'Le type d\'enseignement de la matière doit correspondre à celui du département.';
                    $classes = $this->db->query("SELECT c.id, c.nom, c.teaching_type_id FROM classes c LEFT JOIN departments d ON c.department_id = d.id LEFT JOIN cycles cy ON c.cycle_id = cy.id LEFT JOIN sections sec ON c.section_id = sec.id LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id WHERE (c.department_id IS NULL OR d.status = 1) AND (c.cycle_id IS NULL OR cy.status = 1) AND (c.section_id IS NULL OR sec.status = 1) AND (c.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY c.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                    $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                    $deptQuery = "SELECT d.id, d.nom, d.teaching_type_id FROM departments d LEFT JOIN teaching_types tt ON d.teaching_type_id = tt.id WHERE d.status = 1 AND (d.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY d.nom ASC";
                    $departments = $this->db->query($deptQuery)->fetchAll(PDO::FETCH_ASSOC);
                    $subjectGroups = $this->db->query("SELECT id, libelle, teaching_type_id FROM subject_groups WHERE status = 1 ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);
                    include __DIR__ . '/../Views/subjects/create.php';
                    return;
                }
            }

            $duplicateClasses = $this->findDuplicateClassesForSubjectName($nom, $classes_ids);
            if (!empty($duplicateClasses)) {
                $error = \__('subject_already_exists_in_classes', ['classes' => implode(', ', $duplicateClasses)]);
                $classes = $this->db->query("SELECT c.id, c.nom, c.teaching_type_id FROM classes c LEFT JOIN departments d ON c.department_id = d.id LEFT JOIN cycles cy ON c.cycle_id = cy.id LEFT JOIN sections sec ON c.section_id = sec.id LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id WHERE (c.department_id IS NULL OR d.status = 1) AND (c.cycle_id IS NULL OR cy.status = 1) AND (c.section_id IS NULL OR sec.status = 1) AND (c.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY c.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $deptQuery = "SELECT d.id, d.nom, d.teaching_type_id FROM departments d LEFT JOIN teaching_types tt ON d.teaching_type_id = tt.id WHERE d.status = 1 AND (d.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY d.nom ASC";
                $departments = $this->db->query($deptQuery)->fetchAll(PDO::FETCH_ASSOC);
                $subjectGroups = $this->db->query("SELECT id, libelle, teaching_type_id FROM subject_groups WHERE status = 1 ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/subjects/create.php';
                return;
            }

            try {
                $this->db->beginTransaction();

                $stmt = $this->db->prepare("INSERT INTO subjects (nom, coefficient, groupe, subject_group_id, teaching_type_id, department_id, code_uv, code_ue) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nom, $coeff, $groupe, $subject_group_id, $teaching_type_id, $department_id, $code_uv, $code_ue]);
                $subject_id = $this->db->lastInsertId();

                $academicYearId = $this->academicYearService->getActiveYearId();
                $stmt = $this->db->prepare("INSERT INTO subject_classes (subject_id, class_id, academic_year_id) VALUES (?, ?, ?)");
                foreach ($classes_ids as $cid) {
                    $stmt->execute([$subject_id, (int) $cid, $academicYearId]);
                }

                $this->db->commit();
                Session::setFlash('success', __('subject_created_success'));
                header("Location: /subjects");
                exit;
            } catch (\PDOException $e) {
                $this->db->rollBack();
                $error = \__('server_error_subject_creation');
                $classes = $this->db->query("SELECT id, nom FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $deptQuery = Session::get('user_role') === 'superadmin' ? "SELECT id, nom, teaching_type_id FROM departments ORDER BY nom ASC" : "SELECT id, nom, teaching_type_id FROM departments WHERE status = 1 ORDER BY nom ASC";
                $departments = $this->db->query($deptQuery)->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/subjects/create.php';
            }
        }
    }

    public function edit($id)
    {
        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'])) {
            header("Location: /subjects");
            exit;
        }

        $stmt = $this->db->prepare("SELECT * FROM subjects WHERE id = ?");
        $stmt->execute([$id]);
        $subject = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$subject) {
            header("Location: /subjects");
            exit;
        }

        // Récupérer l'année académique sélectionnée ou utiliser l'année active par défaut
        $selectedYearId = (int) ($_GET['academic_year_id'] ?? 0);
        if ($selectedYearId <= 0) {
            $selectedYearId = $this->academicYearService->getActiveYearId();
        }

        // Récupérer toutes les années académiques pour le sélecteur
        $academicYears = $this->db->query("SELECT id, nom, is_active FROM academic_years ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les classes assignées pour l'année sélectionnée
        $stmt_assoc = $this->db->prepare("SELECT class_id FROM subject_classes WHERE subject_id = ? AND academic_year_id = ?");
        $stmt_assoc->execute([$id, $selectedYearId]);
        $assigned_classes = $stmt_assoc->fetchAll(PDO::FETCH_COLUMN);

        $classes = $this->db->query("SELECT c.id, c.nom, c.teaching_type_id FROM classes c LEFT JOIN departments d ON c.department_id = d.id LEFT JOIN cycles cy ON c.cycle_id = cy.id LEFT JOIN sections sec ON c.section_id = sec.id LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id WHERE (c.department_id IS NULL OR d.status = 1) AND (c.cycle_id IS NULL OR cy.status = 1) AND (c.section_id IS NULL OR sec.status = 1) AND (c.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY c.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $deptQuery = "SELECT d.id, d.nom, d.teaching_type_id FROM departments d LEFT JOIN teaching_types tt ON d.teaching_type_id = tt.id WHERE d.status = 1 AND (d.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY d.nom ASC";
        $departments = $this->db->query($deptQuery)->fetchAll(PDO::FETCH_ASSOC);
        $subjectGroups = $this->db->query("SELECT id, libelle, teaching_type_id FROM subject_groups WHERE status = 1 ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);
        include __DIR__ . '/../Views/subjects/edit.php';
    }

    public function update($id)
    {
        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'])) {
            header("Location: /subjects");
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $coeff = (int) ($_POST['coefficient'] ?? 1);
            $subject_group_id = !empty($_POST['subject_group_id']) ? (int) $_POST['subject_group_id'] : null;
            $groupe = trim($_POST['groupe'] ?? 'Groupe 1');
            $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;
            $department_id = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;
            $classes_ids = array_values(array_unique(array_map('intval', $_POST['classes'] ?? [])));
            $academicYearId = (int) ($_POST['academic_year_id'] ?? $this->academicYearService->getActiveYearId());

            $code_uv = !empty($_POST['code_uv']) ? trim($_POST['code_uv']) : null;
            $code_ue = !empty($_POST['code_ue']) ? trim($_POST['code_ue']) : null;
            if ($teaching_type_id) {
                $stmtTt = $this->db->prepare("SELECT code FROM teaching_types WHERE id = ?");
                $stmtTt->execute([$teaching_type_id]);
                $ttCode = $stmtTt->fetchColumn();
                if ($ttCode !== 'LMD') {
                    $code_uv = null;
                    $code_ue = null;
                }
            } else {
                $code_uv = null;
                $code_ue = null;
            }

            if ($subject_group_id) {
                $grpStmt = $this->db->prepare("SELECT libelle FROM subject_groups WHERE id = ?");
                $grpStmt->execute([$subject_group_id]);
                $grpLib = $grpStmt->fetchColumn();
                if ($grpLib) $groupe = $grpLib;
            }

            if (empty($nom) || empty($classes_ids)) {
                $error = \__('subject_name_and_one_class_required');
                $subject = ['id' => $id, 'nom' => $nom, 'coefficient' => $coeff, 'groupe' => $groupe, 'subject_group_id' => $subject_group_id, 'teaching_type_id' => $teaching_type_id, 'department_id' => $department_id, 'code_uv' => $code_uv, 'code_ue' => $code_ue];
                $assigned_classes = $classes_ids;
                $classes = $this->db->query("SELECT c.id, c.nom, c.teaching_type_id FROM classes c LEFT JOIN departments d ON c.department_id = d.id LEFT JOIN cycles cy ON c.cycle_id = cy.id LEFT JOIN sections sec ON c.section_id = sec.id LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id WHERE (c.department_id IS NULL OR d.status = 1) AND (c.cycle_id IS NULL OR cy.status = 1) AND (c.section_id IS NULL OR sec.status = 1) AND (c.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY c.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $academicYears = $this->db->query("SELECT id, nom, is_active FROM academic_years ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
                $deptQuery = "SELECT d.id, d.nom, d.teaching_type_id FROM departments d LEFT JOIN teaching_types tt ON d.teaching_type_id = tt.id WHERE d.status = 1 AND (d.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY d.nom ASC";
                $departments = $this->db->query($deptQuery)->fetchAll(PDO::FETCH_ASSOC);
                $subjectGroups = $this->db->query("SELECT id, libelle, teaching_type_id FROM subject_groups WHERE status = 1 ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/subjects/edit.php';
                return;
            }

            // Validation: teaching_type_id doit correspondre à celui du département si un département est sélectionné
            if ($department_id) {
                $deptStmt = $this->db->prepare("SELECT teaching_type_id FROM departments WHERE id = ?");
                $deptStmt->execute([$department_id]);
                $deptTeachingTypeId = $deptStmt->fetchColumn();
                if ($deptTeachingTypeId && $deptTeachingTypeId != $teaching_type_id) {
                    $error = __('department_teaching_type_mismatch') ?? 'Le type d\'enseignement de la matière doit correspondre à celui du département.';
                    $subject = ['id' => $id, 'nom' => $nom, 'coefficient' => $coeff, 'groupe' => $groupe, 'subject_group_id' => $subject_group_id, 'teaching_type_id' => $teaching_type_id, 'department_id' => $department_id, 'code_uv' => $code_uv, 'code_ue' => $code_ue];
                    $assigned_classes = $classes_ids;
                    $classes = $this->db->query("SELECT c.id, c.nom, c.teaching_type_id FROM classes c LEFT JOIN departments d ON c.department_id = d.id LEFT JOIN cycles cy ON c.cycle_id = cy.id LEFT JOIN sections sec ON c.section_id = sec.id LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id WHERE (c.department_id IS NULL OR d.status = 1) AND (c.cycle_id IS NULL OR cy.status = 1) AND (c.section_id IS NULL OR sec.status = 1) AND (c.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY c.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                    $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                    $academicYears = $this->db->query("SELECT id, nom, is_active FROM academic_years ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
                    $deptQuery = "SELECT d.id, d.nom, d.teaching_type_id FROM departments d LEFT JOIN teaching_types tt ON d.teaching_type_id = tt.id WHERE d.status = 1 AND (d.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY d.nom ASC";
                    $departments = $this->db->query($deptQuery)->fetchAll(PDO::FETCH_ASSOC);
                    $subjectGroups = $this->db->query("SELECT id, libelle, teaching_type_id FROM subject_groups WHERE status = 1 ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);
                    include __DIR__ . '/../Views/subjects/edit.php';
                    return;
                }
            }

            $duplicateClasses = $this->findDuplicateClassesForSubjectName($nom, $classes_ids, (int) $id);
            if (!empty($duplicateClasses)) {
                $error = \__('subject_already_exists_in_classes', ['classes' => implode(', ', $duplicateClasses)]);
                $subject = ['id' => $id, 'nom' => $nom, 'coefficient' => $coeff, 'groupe' => $groupe, 'subject_group_id' => $subject_group_id, 'teaching_type_id' => $teaching_type_id, 'department_id' => $department_id, 'code_uv' => $code_uv, 'code_ue' => $code_ue];
                $assigned_classes = $classes_ids;
                $classes = $this->db->query("SELECT c.id, c.nom, c.teaching_type_id FROM classes c LEFT JOIN departments d ON c.department_id = d.id LEFT JOIN cycles cy ON c.cycle_id = cy.id LEFT JOIN sections sec ON c.section_id = sec.id LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id WHERE (c.department_id IS NULL OR d.status = 1) AND (c.cycle_id IS NULL OR cy.status = 1) AND (c.section_id IS NULL OR sec.status = 1) AND (c.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY c.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $academicYears = $this->db->query("SELECT id, nom, is_active FROM academic_years ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
                $deptQuery = "SELECT d.id, d.nom, d.teaching_type_id FROM departments d LEFT JOIN teaching_types tt ON d.teaching_type_id = tt.id WHERE d.status = 1 AND (d.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY d.nom ASC";
                $departments = $this->db->query($deptQuery)->fetchAll(PDO::FETCH_ASSOC);
                $subjectGroups = $this->db->query("SELECT id, libelle, teaching_type_id FROM subject_groups WHERE status = 1 ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/subjects/edit.php';
                return;
            }

            try {
                $this->db->beginTransaction();

                $stmt = $this->db->prepare("UPDATE subjects SET nom = ?, coefficient = ?, groupe = ?, subject_group_id = ?, teaching_type_id = ?, department_id = ?, code_uv = ?, code_ue = ? WHERE id = ?");
                $stmt->execute([$nom, $coeff, $groupe, $subject_group_id, $teaching_type_id, $department_id, $code_uv, $code_ue, $id]);

                $stmt_del = $this->db->prepare("DELETE FROM subject_classes WHERE subject_id = ? AND academic_year_id = ?");
                $stmt_del->execute([$id, $academicYearId]);

                $stmt_ins = $this->db->prepare("INSERT INTO subject_classes (subject_id, class_id, academic_year_id) VALUES (?, ?, ?)");
                foreach ($classes_ids as $cid) {
                    $stmt_ins->execute([$id, (int) $cid, $academicYearId]);
                }

                $this->db->commit();
                Session::setFlash('success', __('subject_updated_success'));
                header("Location: /subjects");
                exit;
            } catch (\PDOException $e) {
                $this->db->rollBack();
                $error = \__('server_error_subject_update');
                $subject = ['id' => $id, 'nom' => $nom, 'coefficient' => $coeff, 'groupe' => $groupe, 'teaching_type_id' => $teaching_type_id];
                $assigned_classes = $classes_ids;
                $classes = $this->db->query("SELECT id, nom FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $academicYears = $this->db->query("SELECT id, nom, is_active FROM academic_years ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/subjects/edit.php';
            }
        }
    }

    public function toggleStatus($id)
    {
        if (Session::get('user_role') !== 'superadmin') {
            Session::setFlash('error', __('access_denied_superadmin_only'));
            header("Location: /subjects");
            exit;
        }

        $stmt = $this->db->prepare("UPDATE subjects SET status = 1 - status WHERE id = ?");
        if ($stmt->execute([$id])) {
            Session::setFlash('success', __('status_updated_success'));
        } else {
            Session::setFlash('error', __('status_update_failed'));
        }
        header("Location: /subjects");
        exit;
    }

    public function delete($id)
    {
        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'])) {
            header("Location: /subjects");
            exit;
        }
        $stmt = $this->db->prepare("DELETE FROM subjects WHERE id = ?");
        if ($stmt->execute([$id])) {
            Session::setFlash('success', __('subject_deleted_success'));
        } else {
            Session::setFlash('error', __('subject_delete_failed'));
        }
        header("Location: /subjects");
        exit;
    }

    public function import(): void
    {
        include __DIR__ . '/../Views/subjects/import.php';
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
            $content = $svc->generateSubjectTemplate($lang);
            $filename = $lang === 'fr' ? 'Modele_Import_Matieres_FR.xlsx' : 'Subject_Import_Template_EN.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            echo $content;
            exit;
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            header('Location: /subjects/import');
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
            header('Location: /subjects/import');
            exit;
        }

        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $errMsg = __('session_expired_retry') ?? 'Session expirée ou requête invalide.';
            if ($isAjax) {
                header('Content-Type: application/json', true, 400);
                echo json_encode(['success' => false, 'message' => $errMsg]);
                exit;
            }
            Session::setFlash('error', $errMsg);
            header('Location: /subjects/import');
            exit;
        }

        $file = $_FILES['import_file'];
        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'xlsx') {
            $errMsg = __('invalid_file_format_excel');
            if ($isAjax) {
                header('Content-Type: application/json', true, 400);
                echo json_encode(['success' => false, 'message' => $errMsg]);
                exit;
            }
            Session::setFlash('error', $errMsg);
            header('Location: /subjects/import');
            exit;
        }

        $processor = new SubjectImportProcessor($this->db);
        $result = $processor->process((string) $file['tmp_name']);

        if ($result['success']) {
            $successMsg = __('subjects_imported_success', ['count' => $result['count']]);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $successMsg, 'count' => $result['count']]);
                exit;
            }
            Session::setFlash('success', $successMsg);
            header('Location: /subjects');
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

        include __DIR__ . '/../Views/subjects/import.php';
    }

    private function fetchSubjectsFromFilters($limit = null, $offset = null)
    {
        $search = trim($_GET['q'] ?? '');
        $classId = (int) ($_GET['class_id'] ?? 0);
        $teachingTypeId = (int) ($_GET['teaching_type_id'] ?? 0);
        $departmentId = (int) ($_GET['department_id'] ?? 0);

        // 1. Count total
        $countSql = "SELECT COUNT(*) FROM subjects s LEFT JOIN teaching_types tt ON s.teaching_type_id = tt.id WHERE (tt.actif = 1 OR s.teaching_type_id IS NULL)";
        $countParams = [];
        if ($search !== '') {
            $countSql .= " AND s.nom LIKE ?";
            $countParams[] = '%' . $search . '%';
        }
        if ($classId > 0) {
            $countSql .= " AND EXISTS (SELECT 1 FROM subject_classes sc2 WHERE sc2.subject_id = s.id AND sc2.class_id = ?)";
            $countParams[] = $classId;
        }
        if ($teachingTypeId > 0) {
            $countSql .= " AND s.teaching_type_id = ?";
            $countParams[] = $teachingTypeId;
        }
        if ($departmentId > 0) {
            $countSql .= " AND s.department_id = ?";
            $countParams[] = $departmentId;
        }

        if (Session::get('user_role') !== 'superadmin') {
            $countSql .= " AND s.status = 1";
        }
        $stmtCount = $this->db->prepare($countSql);
        $stmtCount->execute($countParams);
        $totalCount = (int) $stmtCount->fetchColumn();

        // 2. Fetch data
        $sql = "SELECT s.*, tt.nom as teaching_type_nom, sg.libelle as subject_group_libelle, GROUP_CONCAT(c.nom SEPARATOR ', ') as classes_list
                FROM subjects s
                LEFT JOIN subject_classes sc ON s.id = sc.subject_id
                LEFT JOIN classes c ON sc.class_id = c.id
                LEFT JOIN teaching_types tt ON s.teaching_type_id = tt.id
                LEFT JOIN subject_groups sg ON s.subject_group_id = sg.id
                WHERE (tt.actif = 1 OR s.teaching_type_id IS NULL)";
        $params = [];

        if ($search !== '') {
            $sql .= " AND s.nom LIKE ?";
            $params[] = '%' . $search . '%';
        }

        if ($classId > 0) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM subject_classes sc2
                WHERE sc2.subject_id = s.id AND sc2.class_id = ?
            )";
            $params[] = $classId;
        }

        if ($teachingTypeId > 0) {
            $sql .= " AND s.teaching_type_id = ?";
            $params[] = $teachingTypeId;
        }

        if ($departmentId > 0) {
            $sql .= " AND s.department_id = ?";
            $params[] = $departmentId;
        }

        if (Session::get('user_role') !== 'superadmin') {
            $sql .= " AND s.status = 1";
        }

        $sql .= " GROUP BY s.id ORDER BY s.nom ASC";

        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [$data, ['q' => $search, 'class_id' => $classId, 'teaching_type_id' => $teachingTypeId, 'department_id' => $departmentId], $totalCount];
    }

    private function findDuplicateClassesForSubjectName(string $nom, array $classIds, ?int $excludeSubjectId = null): array
    {
        $classIds = array_values(array_unique(array_filter(array_map('intval', $classIds))));
        if ($nom === '' || empty($classIds)) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($classIds), '?'));
        $sql = "SELECT DISTINCT c.nom
                FROM subject_classes sc
                JOIN subjects s ON s.id = sc.subject_id
                JOIN classes c ON c.id = sc.class_id
                WHERE sc.class_id IN ($placeholders)
                  AND LOWER(TRIM(s.nom)) = LOWER(TRIM(?))";
        $params = array_merge($classIds, [$nom]);

        if ($excludeSubjectId !== null) {
            $sql .= " AND s.id <> ?";
            $params[] = $excludeSubjectId;
        }

        $sql .= " ORDER BY c.nom ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }
}
