<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Services\Import\ClassImportProcessor;
use App\Services\Import\ExcelTemplateService;
use App\Services\SettingsStore;
use App\Services\AcademicYearService;
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
    private AcademicYearService $academicYearService;

    /**
     * Initialise le contrôleur et vérifie les autorisations d'administration.
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->settingsStore = new SettingsStore($this->db);
        $this->academicYearService = new AcademicYearService($this->db);
        
        if (Session::get('user_role') === 'enseignant') {
            Session::setFlash('error', __('action_forbidden') ?? 'Accès non autorisé.');
            header("Location: /");
            exit;
        }

        \App\Core\PermissionManager::requirePermission('view_classes');
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
        $cycles = $this->db->query("SELECT c.id, c.nom, c.teaching_type_id FROM cycles c LEFT JOIN teaching_types t ON c.teaching_type_id = t.id WHERE c.status = 1 AND (t.actif = 1 OR c.teaching_type_id IS NULL) ORDER BY c.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $sections = $this->db->query("SELECT id, nom FROM sections WHERE status = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $levels = $this->db->query("SELECT l.id, l.code, l.libelle_fr, l.libelle_en, l.teaching_type_id FROM levels l LEFT JOIN teaching_types tt ON l.teaching_type_id = tt.id WHERE l.status = 1 AND (tt.actif = 1 OR l.teaching_type_id IS NULL) ORDER BY l.code ASC, l.libelle_fr ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        // Seuls les départements actifs sont visibles pour le filtrage usuel
        $departments = $this->db->query("SELECT d.id, d.nom, d.teaching_type_id FROM departments d LEFT JOIN teaching_types t ON d.teaching_type_id = t.id WHERE d.status = 1 AND (t.actif = 1 OR d.teaching_type_id IS NULL) ORDER BY d.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        
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
        // Chargement des dépendances structurelles (Cycles, Sections, Types d'enseignement, Départements, Niveaux)
        $cycles = $this->db->query("SELECT c.id, c.nom, c.teaching_type_id FROM cycles c LEFT JOIN teaching_types t ON c.teaching_type_id = t.id WHERE c.status = 1 AND (t.actif = 1 OR c.teaching_type_id IS NULL) ORDER BY c.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $sections = $this->db->query("SELECT id, nom FROM sections WHERE status = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $levels = $this->db->query("SELECT l.id, l.code, l.libelle_fr, l.libelle_en, l.teaching_type_id FROM levels l LEFT JOIN teaching_types tt ON l.teaching_type_id = tt.id WHERE l.status = 1 AND (tt.actif = 1 OR l.teaching_type_id IS NULL) ORDER BY l.code ASC, l.libelle_fr ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        // Pour la création, on ne propose que les départements actifs rattachés à un type d'enseignement actif (ou sans type)
        $departments = $this->db->query("SELECT d.id, d.nom, d.teaching_type_id FROM departments d LEFT JOIN teaching_types t ON d.teaching_type_id = t.id WHERE d.status = 1 AND (t.actif = 1 OR d.teaching_type_id IS NULL) ORDER BY d.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        
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
            $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;
            $level_id = !empty($_POST['level_id']) ? (int) $_POST['level_id'] : null;

            $frais_inscription = !empty($_POST['frais_inscription']) ? (float)$_POST['frais_inscription'] : 0.0;
            $frais_inscription_reinscription = !empty($_POST['frais_inscription_reinscription']) ? (float)$_POST['frais_inscription_reinscription'] : 0.0;
            $frais_scolarite_brut = !empty($_POST['frais_scolarite_brut']) ? (float)$_POST['frais_scolarite_brut'] : 0.0;
            $nbr_tranches = !empty($_POST['nbr_tranches']) ? (int)$_POST['nbr_tranches'] : 0;
            $tranches = $_POST['tranches'] ?? [];

            $sumTranches = 0.0;
            if ($nbr_tranches > 0) {
                for ($i = 1; $i <= $nbr_tranches; $i++) {
                    $sumTranches += isset($tranches[$i]['amount']) ? (float)$tranches[$i]['amount'] : 0.0;
                }
            }

            $hasError = false;
            if ($nom === '' || !$level_id) {
                $error = $nom === '' ? __('required') : (__('level_required') ?? 'Le niveau est obligatoire.');
                $hasError = true;
            } elseif ($frais_scolarite_brut > 0) {
                if ($nbr_tranches <= 0) {
                    $error = "Le nombre de tranches est obligatoire si les frais de scolarité sont renseignés.";
                    $hasError = true;
                } elseif (abs($sumTranches - $frais_scolarite_brut) > 0.01) {
                    $error = "La somme des tranches (" . number_format($sumTranches, 0, '.', ' ') . " FCFA) doit être égale aux frais de scolarité brut (" . number_format($frais_scolarite_brut, 0, '.', ' ') . " FCFA).";
                    $hasError = true;
                } else {
                    // Vérifier si toutes les échéances (dates) sont saisies
                    for ($i = 1; $i <= $nbr_tranches; $i++) {
                        if (empty($tranches[$i]['deadline'])) {
                            $error = "La date d'échéance de la tranche " . $i . " est requise.";
                            $hasError = true;
                            break;
                        }
                    }
                }
            }

            if ($hasError) {
                $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $sections = $this->db->query("SELECT id, nom FROM sections WHERE status = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $levels = $this->db->query("SELECT l.id, l.code, l.libelle_fr, l.libelle_en, l.teaching_type_id FROM levels l LEFT JOIN teaching_types tt ON l.teaching_type_id = tt.id WHERE l.status = 1 AND (tt.actif = 1 OR l.teaching_type_id IS NULL) ORDER BY l.code ASC, l.libelle_fr ASC")->fetchAll(PDO::FETCH_ASSOC);
                $departments = $this->db->query("SELECT id, nom FROM departments ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $classe = [
                    'nom' => $nom,
                    'cycle_id' => $cycle_id,
                    'section_id' => $section_id,
                    'department_id' => $department_id,
                    'teaching_type_id' => $teaching_type_id,
                    'level_id' => $level_id,
                    'frais_inscription' => $frais_inscription,
                    'frais_inscription_reinscription' => $frais_inscription_reinscription,
                    'frais_scolarite_brut' => $frais_scolarite_brut,
                    'nbr_tranches' => $nbr_tranches,
                    'tranches' => $tranches,
                    'honor_roll_threshold' => $_POST['honor_roll_threshold'] ?? ''
                ];
                include __DIR__ . '/../Views/classes/create.php';
                return;
            }

            // Validation: teaching_type_id doit correspondre à celui du département si un département est sélectionné
            if ($department_id) {
                $deptStmt = $this->db->prepare("SELECT teaching_type_id FROM departments WHERE id = ?");
                $deptStmt->execute([$department_id]);
                $deptTeachingTypeId = $deptStmt->fetchColumn();
                if ($deptTeachingTypeId && $deptTeachingTypeId != $teaching_type_id) {
                    $error = __('department_teaching_type_mismatch') ?? 'Le type d\'enseignement de la classe doit correspondre à celui du département.';
                    $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                    $sections = $this->db->query("SELECT id, nom FROM sections WHERE status = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                    $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                    $levels = $this->db->query("SELECT l.id, l.code, l.libelle_fr, l.libelle_en, l.teaching_type_id FROM levels l LEFT JOIN teaching_types tt ON l.teaching_type_id = tt.id WHERE l.status = 1 AND (tt.actif = 1 OR l.teaching_type_id IS NULL) ORDER BY l.code ASC, l.libelle_fr ASC")->fetchAll(PDO::FETCH_ASSOC);
                    $departments = $this->db->query("SELECT id, nom FROM departments ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                    $classe = [
                        'nom' => $nom,
                        'cycle_id' => $cycle_id,
                        'section_id' => $section_id,
                        'department_id' => $department_id,
                        'teaching_type_id' => $teaching_type_id,
                        'level_id' => $level_id,
                        'frais_inscription' => $frais_inscription,
                        'frais_inscription_reinscription' => $frais_inscription_reinscription,
                        'frais_scolarite_brut' => $frais_scolarite_brut,
                        'nbr_tranches' => $nbr_tranches,
                        'tranches' => $tranches,
                        'honor_roll_threshold' => $_POST['honor_roll_threshold'] ?? ''
                    ];
                    include __DIR__ . '/../Views/classes/create.php';
                    return;
                }
            }

            try {
                $this->db->beginTransaction();

                $stmt = $this->db->prepare("INSERT INTO classes (nom, cycle_id, section_id, department_id, teaching_type_id, level_id, frais_inscription, frais_inscription_reinscription, frais_scolarite_brut, nbr_tranches) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nom, $cycle_id, $section_id, $department_id, $teaching_type_id, $level_id, $frais_inscription, $frais_inscription_reinscription, $frais_scolarite_brut, $nbr_tranches]);
                $newClassId = (int) $this->db->lastInsertId();

                $activeYearId = $this->academicYearService->getActiveYearId();

                if ($nbr_tranches > 0) {
                    $ins = $this->db->prepare("INSERT INTO class_installments (class_id, installment_number, amount) VALUES (?, ?, ?)");
                    $insFeeInst = $this->db->prepare("INSERT INTO fee_installments (academic_year_id, name, installment_order, amount, deadline_date, class_id) VALUES (?, ?, ?, ?, ?, ?)");
                    $insDeadlines = $this->db->prepare("INSERT INTO installment_deadlines (academic_year_id, class_id, installment_number, deadline_date) VALUES (?, ?, ?, ?)");

                    for ($i = 1; $i <= $nbr_tranches; $i++) {
                        $amt = isset($tranches[$i]['amount']) ? (float)$tranches[$i]['amount'] : 0.0;
                        $rawDeadline = !empty($tranches[$i]['deadline']) ? trim((string)$tranches[$i]['deadline']) : null;
                        $deadlineDate = ($rawDeadline !== null && $rawDeadline !== '') ? $rawDeadline : date('Y-12-31');

                        $ins->execute([$newClassId, $i, $amt]);
                        $insFeeInst->execute([$activeYearId, "Tranche " . $i, $i, $amt, $deadlineDate, $newClassId]);
                        if ($rawDeadline !== null && $rawDeadline !== '') {
                            $insDeadlines->execute([$activeYearId, $newClassId, $i, $rawDeadline]);
                        }
                    }
                }

                $threshold = trim((string) ($_POST['honor_roll_threshold'] ?? ''));
                if ($threshold !== '') {
                    $this->settingsStore->set('honor_roll_threshold_class_' . $newClassId, $threshold);
                }

                // Enregistrement de l'historique
                $financialService = new \App\Services\FinancialService($this->db);
                $financialService->logHistory(
                    Session::get('user_id'),
                    'class_finance',
                    $newClassId,
                    'create',
                    null,
                    [
                        'nom' => $nom,
                        'frais_inscription' => $frais_inscription,
                        'frais_scolarite_brut' => $frais_scolarite_brut,
                        'nbr_tranches' => $nbr_tranches,
                        'tranches' => $tranches
                    ]
                );

                $this->db->commit();
                Session::setFlash('success', __('created_success'));
                header("Location: /classes");
                exit;
            } catch (\PDOException $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                $error = __('error_generic') . " : " . $e->getMessage();
                $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $sections = $this->db->query("SELECT id, nom FROM sections WHERE status = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $departments = $this->db->query("SELECT id, nom FROM departments ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $classe = [
                    'nom' => $nom,
                    'cycle_id' => $cycle_id,
                    'section_id' => $section_id,
                    'department_id' => $department_id,
                    'teaching_type_id' => $teaching_type_id,
                    'frais_inscription' => $frais_inscription,
                    'frais_scolarite_brut' => $frais_scolarite_brut,
                    'nbr_tranches' => $nbr_tranches,
                    'tranches' => $tranches,
                    'honor_roll_threshold' => $_POST['honor_roll_threshold'] ?? ''
                ];
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

        // Récupérer les tranches existantes et leurs échéances
        $stmtTranches = $this->db->prepare("SELECT installment_number, amount FROM class_installments WHERE class_id = ? ORDER BY installment_number ASC");
        $stmtTranches->execute([(int)$id]);
        $rawTranches = $stmtTranches->fetchAll(PDO::FETCH_KEY_PAIR);

        $activeYearId = $this->academicYearService->getActiveYearId();
        $stmtDeadlines = $this->db->prepare("SELECT installment_number, deadline_date FROM installment_deadlines WHERE class_id = ? AND academic_year_id = ? ORDER BY installment_number ASC");
        $stmtDeadlines->execute([(int)$id, $activeYearId]);
        $deadlines = $stmtDeadlines->fetchAll(PDO::FETCH_KEY_PAIR);

        $classe['tranches'] = [];
        foreach ($rawTranches as $instNum => $amount) {
            $classe['tranches'][$instNum] = [
                'amount' => $amount,
                'deadline' => $deadlines[$instNum] ?? ''
            ];
        }

        $cycles = $this->db->query("SELECT c.id, c.nom, c.teaching_type_id FROM cycles c LEFT JOIN teaching_types t ON c.teaching_type_id = t.id WHERE c.status = 1 AND (t.actif = 1 OR c.teaching_type_id IS NULL) ORDER BY c.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $sections = $this->db->query("SELECT id, nom FROM sections WHERE status = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $levels = $this->db->query("SELECT l.id, l.code, l.libelle_fr, l.libelle_en, l.teaching_type_id FROM levels l LEFT JOIN teaching_types tt ON l.teaching_type_id = tt.id WHERE l.status = 1 AND (tt.actif = 1 OR l.teaching_type_id IS NULL) ORDER BY l.code ASC, l.libelle_fr ASC")->fetchAll(PDO::FETCH_ASSOC);
        $deptQuery = Session::get('user_role') === 'superadmin' ? "SELECT id, nom, teaching_type_id FROM departments ORDER BY nom ASC" : "SELECT id, nom, teaching_type_id FROM departments WHERE status = 1 ORDER BY nom ASC";
        $departments = $this->db->query($deptQuery)->fetchAll(PDO::FETCH_ASSOC);
        
        include __DIR__ . '/../Views/classes/edit.php';
    }

    /**
     * Met à jour les affiliations et la configuration financière d'une classe.
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim((string)($_POST['nom'] ?? ''));
            $cycle_id = !empty($_POST['cycle_id']) ? (int) $_POST['cycle_id'] : null;
            $section_id = !empty($_POST['section_id']) ? (int) $_POST['section_id'] : null;
            $department_id = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;
            $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;
            $level_id = !empty($_POST['level_id']) ? (int) $_POST['level_id'] : null;

            $frais_inscription = !empty($_POST['frais_inscription']) ? (float)$_POST['frais_inscription'] : 0.0;
            $frais_inscription_reinscription = !empty($_POST['frais_inscription_reinscription']) ? (float)$_POST['frais_inscription_reinscription'] : 0.0;
            $frais_scolarite_brut = !empty($_POST['frais_scolarite_brut']) ? (float)$_POST['frais_scolarite_brut'] : 0.0;
            $nbr_tranches = !empty($_POST['nbr_tranches']) ? (int)$_POST['nbr_tranches'] : 0;
            $tranches = $_POST['tranches'] ?? [];

            $sumTranches = 0.0;
            if ($nbr_tranches > 0) {
                for ($i = 1; $i <= $nbr_tranches; $i++) {
                    $sumTranches += isset($tranches[$i]['amount']) ? (float)$tranches[$i]['amount'] : 0.0;
                }
            }

            $hasError = false;
            if ($nom === '' || !$level_id) {
                $error = $nom === '' ? __('required') : (__('level_required') ?? 'Le niveau est obligatoire.');
                $hasError = true;
            } elseif ($frais_scolarite_brut > 0) {
                if ($nbr_tranches <= 0) {
                    $error = "Le nombre de tranches est obligatoire si les frais de scolarité sont renseignés.";
                    $hasError = true;
                } elseif (abs($sumTranches - $frais_scolarite_brut) > 0.01) {
                    $error = "La somme des tranches (" . number_format($sumTranches, 0, '.', ' ') . " FCFA) doit être égale aux frais de scolarité brut (" . number_format($frais_scolarite_brut, 0, '.', ' ') . " FCFA).";
                    $hasError = true;
                } else {
                    // Vérifier si toutes les échéances (dates) sont saisies
                    for ($i = 1; $i <= $nbr_tranches; $i++) {
                        if (empty($tranches[$i]['deadline'])) {
                            $error = "La date d'échéance de la tranche " . $i . " est requise.";
                            $hasError = true;
                            break;
                        }
                    }
                }
            }

            if ($hasError) {
                $classe = [
                    'id' => $id,
                    'nom' => $nom,
                    'cycle_id' => $cycle_id,
                    'section_id' => $section_id,
                    'department_id' => $department_id,
                    'teaching_type_id' => $teaching_type_id,
                    'level_id' => $level_id,
                    'frais_inscription' => $frais_inscription,
                    'frais_inscription_reinscription' => $frais_inscription_reinscription,
                    'frais_scolarite_brut' => $frais_scolarite_brut,
                    'nbr_tranches' => $nbr_tranches,
                    'tranches' => $tranches,
                    'honor_roll_threshold' => $_POST['honor_roll_threshold'] ?? ''
                ];
                $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $levels = $this->db->query("SELECT l.id, l.code, l.libelle_fr, l.libelle_en, l.teaching_type_id FROM levels l LEFT JOIN teaching_types tt ON l.teaching_type_id = tt.id WHERE l.status = 1 AND (tt.actif = 1 OR l.teaching_type_id IS NULL) ORDER BY l.code ASC, l.libelle_fr ASC")->fetchAll(PDO::FETCH_ASSOC);
                $departments = $this->db->query("SELECT id, nom FROM departments ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/classes/edit.php';
                return;
            }

            // Validation: teaching_type_id doit correspondre à celui du département si un département est sélectionné
            if ($department_id) {
                $deptStmt = $this->db->prepare("SELECT teaching_type_id FROM departments WHERE id = ?");
                $deptStmt->execute([$department_id]);
                $deptTeachingTypeId = $deptStmt->fetchColumn();
                if ($deptTeachingTypeId && $deptTeachingTypeId != $teaching_type_id) {
                    $error = __('department_teaching_type_mismatch') ?? 'Le type d\'enseignement de la classe doit correspondre à celui du département.';
                    $classe = [
                        'id' => $id,
                        'nom' => $nom,
                        'cycle_id' => $cycle_id,
                        'section_id' => $section_id,
                        'department_id' => $department_id,
                        'teaching_type_id' => $teaching_type_id,
                        'level_id' => $level_id,
                        'frais_inscription' => $frais_inscription,
                        'frais_inscription_reinscription' => $frais_inscription_reinscription,
                        'frais_scolarite_brut' => $frais_scolarite_brut,
                        'nbr_tranches' => $nbr_tranches,
                        'tranches' => $tranches,
                        'honor_roll_threshold' => $_POST['honor_roll_threshold'] ?? ''
                    ];
                    $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                    $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                    $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                    $levels = $this->db->query("SELECT l.id, l.code, l.libelle_fr, l.libelle_en, l.teaching_type_id FROM levels l LEFT JOIN teaching_types tt ON l.teaching_type_id = tt.id WHERE l.status = 1 AND (tt.actif = 1 OR l.teaching_type_id IS NULL) ORDER BY l.code ASC, l.libelle_fr ASC")->fetchAll(PDO::FETCH_ASSOC);
                    $departments = $this->db->query("SELECT id, nom FROM departments WHERE status = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                    include __DIR__ . '/../Views/classes/edit.php';
                    return;
                }
            }

            try {
                $this->db->beginTransaction();

                // Récupérer les anciennes valeurs pour l'historique
                $stmtOld = $this->db->prepare("SELECT nom, frais_inscription, frais_inscription_reinscription, frais_scolarite_brut, nbr_tranches FROM classes WHERE id = ?");
                $stmtOld->execute([(int)$id]);
                $oldClass = $stmtOld->fetch(PDO::FETCH_ASSOC);

                $stmtOldTr = $this->db->prepare("SELECT installment_number, amount FROM class_installments WHERE class_id = ? ORDER BY installment_number ASC");
                $stmtOldTr->execute([(int)$id]);
                $oldClass['tranches'] = $stmtOldTr->fetchAll(PDO::FETCH_KEY_PAIR);

                // Mettre à jour la classe
                $stmt = $this->db->prepare("UPDATE classes SET nom = ?, cycle_id = ?, section_id = ?, department_id = ?, teaching_type_id = ?, level_id = ?, frais_inscription = ?, frais_inscription_reinscription = ?, frais_scolarite_brut = ?, nbr_tranches = ? WHERE id = ?");
                $stmt->execute([$nom, $cycle_id, $section_id, $department_id, $teaching_type_id, $level_id, $frais_inscription, $frais_inscription_reinscription, $frais_scolarite_brut, $nbr_tranches, (int)$id]);
                
                // Mettre à jour les tranches et échéances
                $del = $this->db->prepare("DELETE FROM class_installments WHERE class_id = ?");
                $del->execute([(int)$id]);

                $activeYearId = $this->academicYearService->getActiveYearId();
                $this->db->prepare("DELETE FROM fee_installments WHERE class_id = ? AND academic_year_id = ?")->execute([(int)$id, $activeYearId]);
                $this->db->prepare("DELETE FROM installment_deadlines WHERE class_id = ? AND academic_year_id = ?")->execute([(int)$id, $activeYearId]);

                if ($nbr_tranches > 0) {
                    $ins = $this->db->prepare("INSERT INTO class_installments (class_id, installment_number, amount) VALUES (?, ?, ?)");
                    $insFeeInst = $this->db->prepare("INSERT INTO fee_installments (academic_year_id, name, installment_order, amount, deadline_date, class_id) VALUES (?, ?, ?, ?, ?, ?)");
                    $insDeadlines = $this->db->prepare("INSERT INTO installment_deadlines (academic_year_id, class_id, installment_number, deadline_date) VALUES (?, ?, ?, ?)");

                    for ($i = 1; $i <= $nbr_tranches; $i++) {
                        $amt = isset($tranches[$i]['amount']) ? (float)$tranches[$i]['amount'] : 0.0;
                        $rawDeadline = !empty($tranches[$i]['deadline']) ? trim((string)$tranches[$i]['deadline']) : null;
                        $deadlineDate = ($rawDeadline !== null && $rawDeadline !== '') ? $rawDeadline : date('Y-12-31');

                        $ins->execute([(int)$id, $i, $amt]);
                        $insFeeInst->execute([$activeYearId, "Tranche " . $i, $i, $amt, $deadlineDate, (int)$id]);
                        if ($rawDeadline !== null && $rawDeadline !== '') {
                            $insDeadlines->execute([$activeYearId, (int)$id, $i, $rawDeadline]);
                        }
                    }
                }

                $threshold = trim((string) ($_POST['honor_roll_threshold'] ?? ''));
                $this->settingsStore->set('honor_roll_threshold_class_' . $id, $threshold);

                // Log d'historique
                $fs = new \App\Services\FinancialService($this->db);
                $fs->logHistory(Session::get('user_id'), 'class_finance', (int)$id, 'update', $oldClass, [
                    'nom' => $nom,
                    'frais_inscription' => $frais_inscription,
                    'frais_inscription_reinscription' => $frais_inscription_reinscription,
                    'frais_scolarite_brut' => $frais_scolarite_brut,
                    'nbr_tranches' => $nbr_tranches,
                    'tranches' => $tranches
                ]);

                // Synchroniser tous les élèves inscrits dans cette classe
                $fs->syncClassFinancials((int)$id, $activeYearId);

                $this->db->commit();
                Session::setFlash('success', __('updated_success'));
                header("Location: /classes");
                exit;
            } catch (\PDOException $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                $error = __('error_generic') . " : " . $e->getMessage();
                $classe = [
                    'id' => $id,
                    'nom' => $nom,
                    'cycle_id' => $cycle_id,
                    'section_id' => $section_id,
                    'department_id' => $department_id,
                    'teaching_type_id' => $teaching_type_id,
                    'frais_inscription' => $frais_inscription,
                    'frais_inscription_reinscription' => $frais_inscription_reinscription,
                    'frais_scolarite_brut' => $frais_scolarite_brut,
                    'nbr_tranches' => $nbr_tranches,
                    'tranches' => $tranches,
                    'honor_roll_threshold' => $_POST['honor_roll_threshold'] ?? ''
                ];
                $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $departments = $this->db->query("SELECT id, nom FROM departments WHERE status = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
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
        $academicYearId = $this->academicYearService->getActiveYearId();
        
        // Liste de toutes les classes pour le sélecteur
        // Classes are now shared across years, no year filtering
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
                    WHERE ta.class_id = ? AND ta.academic_year_id = ?
                    ORDER BY u.nom ASC, u.prenom ASC
                ");
                $stmt->execute([$id, $id, $academicYearId]);
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
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['import_file'])) {
            if ($isAjax) {
                header('Content-Type: application/json', true, 400);
                echo json_encode(['success' => false, 'message' => __('invalid_request')]);
                exit;
            }
            header('Location: /classes/import');
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
            header('Location: /classes/import');
            exit;
        }

        $ext = strtolower(pathinfo((string) ($_FILES['import_file']['name'] ?? ''), PATHINFO_EXTENSION));
        if ($ext !== 'xlsx') {
            $errMsg = __('invalid_file_format_excel');
            if ($isAjax) {
                header('Content-Type: application/json', true, 400);
                echo json_encode(['success' => false, 'message' => $errMsg]);
                exit;
            }
            Session::setFlash('error', $errMsg);
            header('Location: /classes/import');
            exit;
        }

        $processor = new ClassImportProcessor($this->db);
        $result = $processor->process((string) $_FILES['import_file']['tmp_name']);

        if ($result['success']) {
            $successMsg = __('classes_imported_success', ['count' => $result['count']]);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $successMsg, 'count' => $result['count']]);
                exit;
            }
            Session::setFlash('success', $successMsg);
            header('Location: /classes');
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
        $teachingTypeId = (int) ($_GET['teaching_type_id'] ?? 0);
        $levelId = (int) ($_GET['level_id'] ?? 0);
        // Classes are now shared across years, no year filtering needed

        // 1. Count total
        $countSql = "SELECT COUNT(*) FROM classes c 
                     LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id
                     LEFT JOIN cycles cy ON c.cycle_id = cy.id
                     LEFT JOIN sections s ON c.section_id = s.id
                     LEFT JOIN departments d ON c.department_id = d.id 
                     LEFT JOIN levels lvl ON c.level_id = lvl.id
                     WHERE (c.teaching_type_id IS NULL OR tt.actif = 1)
                       AND (c.cycle_id IS NULL OR cy.status = 1)
                       AND (c.section_id IS NULL OR s.status = 1)
                       AND (c.department_id IS NULL OR d.status = 1)
                       AND (c.level_id IS NULL OR lvl.status = 1)";
        $countParams = [];
        
        // No academic year filtering for classes
        
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
        if ($teachingTypeId > 0) {
            $countSql .= " AND c.teaching_type_id = ?";
            $countParams[] = $teachingTypeId;
        }
        if ($levelId > 0) {
            $countSql .= " AND c.level_id = ?";
            $countParams[] = $levelId;
        }
        $stmtCount = $this->db->prepare($countSql);
        $stmtCount->execute($countParams);
        $totalCount = (int) $stmtCount->fetchColumn();

        // 2. Fetch data
        // Jointures pour récupérer les noms des cycles, sections, départements, niveaux et du professeur principal
        $academicYearId = $this->academicYearService->getActiveYearId();
        $sql = "SELECT c.id, c.nom, c.level_id, cy.nom as cycle_nom, s.nom as section_nom, d.nom as department_nom, tt.nom as teaching_type_nom,
                       lvl.code as level_code, lvl.libelle_fr as level_libelle_fr, lvl.libelle_en as level_libelle_en,
                       u.nom as main_teacher_nom, u.prenom as main_teacher_prenom,
                       (SELECT COUNT(*) FROM students WHERE class_id = c.id AND academic_year_id = {$academicYearId} AND is_withdrawn = 0 AND actif = 1) as student_count
                FROM classes c
                LEFT JOIN cycles cy ON c.cycle_id = cy.id
                LEFT JOIN sections s ON c.section_id = s.id
                LEFT JOIN departments d ON c.department_id = d.id
                LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id
                LEFT JOIN levels lvl ON c.level_id = lvl.id
                LEFT JOIN users u ON c.main_teacher_id = u.id
                WHERE (c.teaching_type_id IS NULL OR tt.actif = 1)
                  AND (c.cycle_id IS NULL OR cy.status = 1)
                  AND (c.section_id IS NULL OR s.status = 1)
                  AND (c.department_id IS NULL OR d.status = 1)
                  AND (c.level_id IS NULL OR lvl.status = 1)";
        $params = [];

        // No academic year filtering for classes

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
        if ($teachingTypeId > 0) {
            $sql .= " AND c.teaching_type_id = ?";
            $params[] = $teachingTypeId;
        }
        if ($levelId > 0) {
            $sql .= " AND c.level_id = ?";
            $params[] = $levelId;
        }

        $sql .= " ORDER BY c.nom ASC";

        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [$stmt->fetchAll(PDO::FETCH_ASSOC), ['q' => $search, 'cycle_id' => $cycleId, 'section_id' => $sectionId, 'department_id' => $departmentId, 'teaching_type_id' => $teachingTypeId, 'level_id' => $levelId], $totalCount];
    }
}
