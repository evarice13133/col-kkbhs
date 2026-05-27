<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Services\Import\ClassImportProcessor;
use App\Services\Import\ExcelTemplateService;
use App\Services\SettingsStore;
use PDO;

/**
 * ClassController
 * 
 * Ce contrôleur assure la gestion du registre des classes et leur affiliation structurelle.
 * Chaque classe est désormais rattachée à un Cycle et une Section pour une meilleure organisation académique.
 */
class ClassController
{
    /** @var PDO Instance de connexion à la base de données */
    private $db;
    private SettingsStore $settingsStore;

    /**
     * Initialise le contrôleur et vérifie les autorisations d'administration.
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->settingsStore = new SettingsStore($this->db);
        
        // Accès restreint aux rôles administratifs (Superadmin et Admin)
        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'])) {
            header("Location: /");
            exit;
        }
    }

    /**
     * Affiche la liste filtrée des classes de l'établissement.
     */
    public function index()
    {
        $page = (int) ($_GET['page'] ?? 1);
        $limit = 16;
        $offset = ($page - 1) * $limit;

        // Récupération des données selon les filtres actifs (Recherche, Cycle, Section)
        [$classes, $filters, $totalCount] = $this->fetchClassesFromFilters($limit, $offset);
        $totalPages = (int) ceil($totalCount / $limit);

        if ($page > $totalPages && $totalCount > 0) {
            header("Location: /classes?page=1");
            exit;
        }
        
        // Listes pour alimenter les menus déroulants de filtrage dans la vue
        $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        // Seuls les départements actifs sont visibles pour le filtrage usuel
        $deptQuery = "SELECT id, nom FROM departments WHERE status = 1 ORDER BY nom ASC";
        if (Session::get('user_role') === 'superadmin') {
            $deptQuery = "SELECT id, nom FROM departments ORDER BY nom ASC";
        }
        $departments = $this->db->query($deptQuery)->fetchAll(PDO::FETCH_ASSOC);
        
        include __DIR__ . '/../Views/classes/index.php';
    }

    /**
     * Exporte le registre des classes au format PDF (via template HTML).
     */
    public function export()
    {
        // On récupère les données filtrées pour l'export
        [$classes] = $this->fetchClassesFromFilters();

        $exportTitle = __('classes_cohorts');
        $exportSubtitle = "Registre officiel des structures pédagogiques";
        $exportColumns = [__('class'), __('cycle'), __('section'), __('department')];
        
        $exportRows = array_map(function ($class) {
            return [
                $class['nom'],
                $class['cycle_nom'] ?: '-',
                $class['section_nom'] ?: '-',
                $class['department_nom'] ?: '-',
            ];
        }, $classes);

        include __DIR__ . '/../Views/templates/export.php';
    }

    /**
     * Affiche le formulaire de création d'une nouvelle classe.
     */
    public function create()
    {
        // Chargement des dépendances structurelles (Cycles et Sections)
        $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        // Pour la création, on ne propose que les départements actifs (sauf SuperAdmin)
        $deptQuery = Session::get('user_role') === 'superadmin' ? "SELECT id, nom FROM departments ORDER BY nom ASC" : "SELECT id, nom FROM departments WHERE status = 1 ORDER BY nom ASC";
        $departments = $this->db->query($deptQuery)->fetchAll(PDO::FETCH_ASSOC);
        
        include __DIR__ . '/../Views/classes/create.php';
    }

    /**
     * Traite l'enregistrement d'une nouvelle classe.
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim((string)($_POST['nom'] ?? ''));
            $cycle_id = !empty($_POST['cycle_id']) ? (int) $_POST['cycle_id'] : null;
            $section_id = !empty($_POST['section_id']) ? (int) $_POST['section_id'] : null;
            $department_id = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;

            // Le nom de la classe est l'identifiant minimal requis
            if ($nom === '') {
                $error = __('required');
                $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $departments = $this->db->query("SELECT id, nom FROM departments ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/classes/create.php';
                return;
            }

            try {
                // Insertion avec gestion des relations optionnelles
                $stmt = $this->db->prepare("INSERT INTO classes (nom, cycle_id, section_id, department_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nom, $cycle_id, $section_id, $department_id]);
                $newClassId = (int) $this->db->lastInsertId();
                $threshold = trim((string) ($_POST['honor_roll_threshold'] ?? ''));
                if ($threshold !== '') {
                    $this->settingsStore->set('honor_roll_threshold_class_' . $newClassId, $threshold);
                }
                
                Session::setFlash('success', __('created_success'));
                header("Location: /classes");
                exit;
            } catch (\PDOException $e) {
                // Gestion des doublons de noms de classes
                $error = __('error_generic');
                $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $departments = $this->db->query("SELECT id, nom FROM departments ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/classes/create.php';
            }
        }
    }

    /**
     * Affiche l'interface de modification d'une classe existante.
     */
    public function edit($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM classes WHERE id = ?");
        $stmt->execute([(int)$id]);
        $classe = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$classe) {
            header("Location: /classes");
            exit;
        }

        $classe['honor_roll_threshold'] = $this->settingsStore->get('honor_roll_threshold_class_' . $classe['id'], '');

        $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $departments = $this->db->query("SELECT id, nom FROM departments ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        include __DIR__ . '/../Views/classes/edit.php';
    }

    /**
     * Met à jour les affiliations d'une classe.
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim((string)($_POST['nom'] ?? ''));
            $cycle_id = !empty($_POST['cycle_id']) ? (int) $_POST['cycle_id'] : null;
            $section_id = !empty($_POST['section_id']) ? (int) $_POST['section_id'] : null;
            $department_id = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;

            if ($nom === '') {
                $error = __('required');
                $classe = ['id' => $id, 'nom' => $nom, 'cycle_id' => $cycle_id, 'section_id' => $section_id, 'department_id' => $department_id];
                $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $departments = $this->db->query("SELECT id, nom FROM departments ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/classes/edit.php';
                return;
            }

            try {
                $stmt = $this->db->prepare("UPDATE classes SET nom = ?, cycle_id = ?, section_id = ?, department_id = ? WHERE id = ?");
                $stmt->execute([$nom, $cycle_id, $section_id, $department_id, (int)$id]);
                
                $threshold = trim((string) ($_POST['honor_roll_threshold'] ?? ''));
                $this->settingsStore->set('honor_roll_threshold_class_' . $id, $threshold);
                
                Session::setFlash('success', __('updated_success'));
                header("Location: /classes");
                exit;
            } catch (\PDOException $e) {
                $error = __('error_generic');
                $classe = ['id' => $id, 'nom' => $nom, 'cycle_id' => $cycle_id, 'section_id' => $section_id, 'department_id' => $department_id];
                $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $departments = $this->db->query("SELECT id, nom FROM departments ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/classes/edit.php';
            }
        }
    }

    /**
     * Archive ou supprime une classe (si elle n'a pas d'élèves).
     */
    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM classes WHERE id = ?");
            $stmt->execute([(int)$id]);
            Session::setFlash('success', __('deleted_success'));
        } catch (\PDOException $e) {
            // Échec si des élèves ou des notes sont rattachés à cette classe
            Session::setFlash('error', __('error_generic'));
        }
        header("Location: /classes");
        exit;
    }

    /**
     * Interface de gestion de l'équipe pédagogique d'une classe.
     * Permet notamment de désigner le Professeur Principal.
     */
    public function manageTeam($id = 0)
    {
        $id = (int)($id ?: ($_GET['id'] ?? 0));
        
        // Liste de toutes les classes pour le sélecteur
        $allClasses = $this->db->query("SELECT id, nom FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        $class = null;
        $teachers = [];
        
        if ($id > 0) {
            $stmt = $this->db->prepare("SELECT c.*, u.nom as mt_nom, u.prenom as mt_prenom 
                                       FROM classes c 
                                       LEFT JOIN users u ON c.main_teacher_id = u.id 
                                       WHERE c.id = ?");
            $stmt->execute([$id]);
            $class = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($class) {
                // Récupérer UNIQUEMENT les enseignants qui interviennent dans cette classe
                $stmt = $this->db->prepare("
                    SELECT DISTINCT u.id, u.nom, u.prenom,
                           (SELECT GROUP_CONCAT(cl.nom SEPARATOR ', ') FROM classes cl WHERE cl.main_teacher_id = u.id AND cl.id != ?) as other_classes
                    FROM users u
                    JOIN teacher_assignments ta ON u.id = ta.user_id
                    WHERE ta.class_id = ?
                    ORDER BY u.nom ASC, u.prenom ASC
                ");
                $stmt->execute([$id, $id]);
                $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        include __DIR__ . '/../Views/classes/manage_team.php';
    }

    /**
     * Enregistre le choix du professeur principal pour une classe.
     */
    public function setMainTeacher($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', __('session_expired_retry'));
                header("Location: /classes/manage-team?id=" . $id);
                exit;
            }

            $teacher_id = !empty($_POST['teacher_id']) ? (int) $_POST['teacher_id'] : null;
            
            $stmt = $this->db->prepare("UPDATE classes SET main_teacher_id = ? WHERE id = ?");
            $stmt->execute([$teacher_id, (int)$id]);
            
            Session::setFlash('success', __('main_teacher_updated_success'));
            header("Location: /classes/manage-team?id=" . $id);
            exit;
        }
    }

    public function import(): void
    {
        include __DIR__ . '/../Views/classes/import.php';
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
            $content = $svc->generateClassTemplate($lang);
            $filename = $lang === 'fr' ? 'Modele_Import_Classes_FR.xlsx' : 'Class_Import_Template_EN.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            echo $content;
            exit;
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            header('Location: /classes/import');
            exit;
        }
    }

    public function upload(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['import_file'])) {
            header('Location: /classes/import');
            exit;
        }
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', __('session_expired_retry') ?? 'Session expirée ou requête invalide.');
            header('Location: /classes/import');
            exit;
        }
        $ext = strtolower(pathinfo((string) ($_FILES['import_file']['name'] ?? ''), PATHINFO_EXTENSION));
        if ($ext !== 'xlsx') {
            Session::setFlash('error', __('invalid_file_format_excel'));
            header('Location: /classes/import');
            exit;
        }

        $processor = new ClassImportProcessor($this->db);
        $result = $processor->process((string) $_FILES['import_file']['tmp_name']);
        if ($result['success']) {
            Session::setFlash('success', __('classes_imported_success', ['count' => $result['count']]));
            header('Location: /classes');
            exit;
        }

        $errors = $result['errors'];
        include __DIR__ . '/../Views/classes/import.php';
    }

    /**
     * Méthode utilitaire interne pour centraliser la logique de filtrage multicritères.
     * 
     * @return array [Données des classes, États des filtres]
     */
    private function fetchClassesFromFilters($limit = null, $offset = null)
    {
        $search = trim((string)($_GET['q'] ?? ''));
        $cycleId = (int) ($_GET['cycle_id'] ?? 0);
        $sectionId = (int) ($_GET['section_id'] ?? 0);
        $departmentId = (int) ($_GET['department_id'] ?? 0);

        // 1. Count total
        $countSql = "SELECT COUNT(*) FROM classes c WHERE 1=1";
        $countParams = [];
        if ($search !== '') {
            $countSql .= " AND c.nom LIKE ?";
            $countParams[] = '%' . $search . '%';
        }
        if ($cycleId > 0) {
            $countSql .= " AND c.cycle_id = ?";
            $countParams[] = $cycleId;
        }
        if ($sectionId > 0) {
            $countSql .= " AND c.section_id = ?";
            $countParams[] = $sectionId;
        }
        if ($departmentId > 0) {
            $countSql .= " AND c.department_id = ?";
            $countParams[] = $departmentId;
        }
        $stmtCount = $this->db->prepare($countSql);
        $stmtCount->execute($countParams);
        $totalCount = (int) $stmtCount->fetchColumn();

        // 2. Fetch data
        // Jointures pour récupérer les noms des cycles, sections, départements et du professeur principal
        $sql = "SELECT c.id, c.nom, cy.nom as cycle_nom, s.nom as section_nom, d.nom as department_nom,
                       u.nom as main_teacher_nom, u.prenom as main_teacher_prenom,
                       (SELECT COUNT(*) FROM students WHERE class_id = c.id AND is_withdrawn = 0) as student_count
                FROM classes c
                LEFT JOIN cycles cy ON c.cycle_id = cy.id
                LEFT JOIN sections s ON c.section_id = s.id
                LEFT JOIN departments d ON c.department_id = d.id
                LEFT JOIN users u ON c.main_teacher_id = u.id
                WHERE 1=1";
        $params = [];

        // Application dynamique de la clause WHERE selon les entrées utilisateur
        if ($search !== '') {
            $sql .= " AND c.nom LIKE ?";
            $params[] = '%' . $search . '%';
        }

        if ($cycleId > 0) {
            $sql .= " AND c.cycle_id = ?";
            $params[] = $cycleId;
        }

        if ($sectionId > 0) {
            $sql .= " AND c.section_id = ?";
            $params[] = $sectionId;
        }
        if ($departmentId > 0) {
            $sql .= " AND c.department_id = ?";
            $params[] = $departmentId;
        }

        $sql .= " ORDER BY c.nom ASC";

        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [$stmt->fetchAll(PDO::FETCH_ASSOC), ['q' => $search, 'cycle_id' => $cycleId, 'section_id' => $sectionId, 'department_id' => $departmentId], $totalCount];
    }
}
