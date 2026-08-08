<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\PermissionManager;
use App\Core\Security;
use App\Core\Session;
use App\Models\ClassRoom;
use App\Models\CourseWeek;
use App\Models\Timetable;
use App\Models\TimetableAuditLog;
use App\Models\TimetableEntry;
use App\Models\TimetableSlot;
use App\Services\AcademicYearService;
use App\Services\SettingsStore;
use App\Services\Timetable\BulkSchedulingService;
use App\Services\Timetable\TimetableConflictService;
use App\Services\Timetable\TimetableLockService;
use App\Services\Timetable\TimetableWizardService;
use Dompdf\Dompdf;
use Dompdf\Options;
use PDO;

class TimetableController
{
    private PDO $db;
    private SettingsStore $settingsStore;
    private AcademicYearService $academicYearService;
    private Timetable $timetableModel;
    private TimetableEntry $entryModel;
    private TimetableSlot $slotModel;
    private ClassRoom $roomModel;
    private CourseWeek $weekModel;
    private TimetableAuditLog $auditLogModel;
    private TimetableConflictService $conflictService;
    private TimetableLockService $lockService;
    private TimetableWizardService $wizardService;
    private BulkSchedulingService $bulkService;

    public function __construct()
    {
        PermissionManager::requirePermission('view_timetables');

        $this->db = Database::getInstance()->getConnection();
        $this->settingsStore = new SettingsStore($this->db);
        $this->academicYearService = new AcademicYearService($this->db);
        $this->timetableModel = new Timetable();
        $this->entryModel = new TimetableEntry();
        $this->slotModel = new TimetableSlot();
        $this->roomModel = new ClassRoom();
        $this->weekModel = new CourseWeek();
        $this->auditLogModel = new TimetableAuditLog();
        $this->conflictService = new TimetableConflictService($this->db);
        $this->lockService = new TimetableLockService($this->db);
        $this->wizardService = new TimetableWizardService($this->db);
        $this->bulkService = new BulkSchedulingService($this->db);
    }

    /**
     * Dashboard principal des emplois du temps avec filtres.
     */
    public function index()
    {
        $activeYear = $this->academicYearService->getActiveYear();
        $activeYearId = $activeYear ? (int)$activeYear['id'] : 0;

        $selectedYear = (int)($_GET['year_id'] ?? $activeYearId);
        $selectedClass = (int)($_GET['class_id'] ?? 0);
        $selectedWeek = (int)($_GET['week_id'] ?? 0);

        $timetables = $this->timetableModel->getAllGrouped(
            $selectedYear ?: null,
            $selectedClass ?: null,
            $selectedWeek ?: null
        );

        // Mettre à jour les statuts de verrouillage et la conciliation des états
        foreach ($timetables as &$t) {
            $ids = !empty($t['timetable_ids']) ? explode(',', $t['timetable_ids']) : [$t['primary_id']];
            $isLocked = false;
            $canEdit = true;
            
            foreach ($ids as $ttId) {
                if ($this->lockService->checkAutoLock((int)$ttId)) {
                    $isLocked = true;
                }
                $ttRow = $this->timetableModel->find((int)$ttId);
                if ($ttRow && !$this->lockService->canModify($ttRow)) {
                    $canEdit = false;
                }
            }

            $statuts = !empty($t['statuts']) ? explode(',', $t['statuts']) : ['brouillon'];
            if (in_array('publie', $statuts, true) && count(array_unique($statuts)) === 1) {
                $t['statut'] = 'publie';
            } elseif (in_array('verrouille', $statuts, true) || $isLocked) {
                $t['statut'] = 'verrouille';
            } else {
                $t['statut'] = 'brouillon';
            }

            $t['is_locked_calc'] = $isLocked;
            $t['can_edit'] = $canEdit;
            $t['id'] = $t['primary_id'];
        }
        unset($t);

        $years = $this->db->query("SELECT id, nom as libelle FROM academic_years ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        $classes = $this->db->query("
            SELECT c.id, c.nom 
            FROM classes c
            LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id
            WHERE tt.code = 'LMD' OR tt.nom LIKE '%Supérieur%' OR tt.nom LIKE '%LMD%' OR c.teaching_type_id = 9
            ORDER BY c.nom ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
        $weeks = $selectedYear ? $this->weekModel->getByAcademicYear($selectedYear) : $this->weekModel->getAll();

        require __DIR__ . '/../Views/timetables/index.php';
    }

    /**
     * Sous-module 1 : Gestion des Créneaux Horaires.
     */
    public function slots()
    {
        PermissionManager::requirePermission('manage_timetables');
        $slots = $this->slotModel->getAll();
        require __DIR__ . '/../Views/timetables/slots.php';
    }

    public function storeSlot()
    {
        PermissionManager::requirePermission('manage_timetables');
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Jeton de sécurité CSRF invalide.');
            header('Location: /timetables/slots');
            exit;
        }

        $debut = trim($_POST['heure_debut'] ?? '');
        $fin = trim($_POST['heure_fin'] ?? '');
        $type = $_POST['type_creneau'] ?? 'cours';
        $ordre = (int)($_POST['ordre_affichage'] ?? 1);

        if (empty($debut) || empty($fin)) {
            Session::setFlash('error', 'Les heures de début et de fin sont obligatoires.');
            header('Location: /timetables/slots');
            exit;
        }

        if (strtotime("1970-01-01 $fin") <= strtotime("1970-01-01 $debut")) {
            Session::setFlash('error', 'L\'heure de fin doit être supérieure à l\'heure de début.');
            header('Location: /timetables/slots');
            exit;
        }

        if ($this->slotModel->hasOverlap($debut, $fin)) {
            Session::setFlash('error', 'Conflit détecté : Ce créneau chevauche un créneau horaire existant.');
            header('Location: /timetables/slots');
            exit;
        }

        $this->slotModel->create([
            'heure_debut' => $debut,
            'heure_fin' => $fin,
            'type_creneau' => $type,
            'ordre_affichage' => $ordre
        ]);

        Session::setFlash('success', 'Créneau horaire ajouté avec succès.');
        header('Location: /timetables/slots');
        exit;
    }

    public function updateSlot()
    {
        PermissionManager::requirePermission('manage_timetables');
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Jeton CSRF invalide.');
            header('Location: /timetables/slots');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $debut = trim($_POST['heure_debut'] ?? '');
        $fin = trim($_POST['heure_fin'] ?? '');
        $type = $_POST['type_creneau'] ?? 'cours';
        $ordre = (int)($_POST['ordre_affichage'] ?? 1);

        if ($this->slotModel->hasOverlap($debut, $fin, $id)) {
            Session::setFlash('error', 'Conflit de chevauchement détecté pour la modification de ce créneau.');
            header('Location: /timetables/slots');
            exit;
        }

        $this->slotModel->update($id, [
            'heure_debut' => $debut,
            'heure_fin' => $fin,
            'type_creneau' => $type,
            'ordre_affichage' => $ordre
        ]);

        Session::setFlash('success', 'Créneau horaire mis à jour.');
        header('Location: /timetables/slots');
        exit;
    }

    public function deleteSlot()
    {
        PermissionManager::requirePermission('manage_timetables');
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Jeton CSRF invalide.');
            header('Location: /timetables/slots');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $this->slotModel->delete($id);

        Session::setFlash('success', 'Créneau horaire supprimé.');
        header('Location: /timetables/slots');
        exit;
    }

    /**
     * Sous-module 2 : Gestion des Salles de Classe.
     */
    public function rooms()
    {
        PermissionManager::requirePermission('manage_timetables');
        $rooms = $this->roomModel->getAll();
        require __DIR__ . '/../Views/timetables/rooms.php';
    }

    public function storeRoom()
    {
        PermissionManager::requirePermission('manage_timetables');
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Jeton CSRF invalide.');
            header('Location: /timetables/rooms');
            exit;
        }

        $nom = trim($_POST['nom'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $capacite = (int)($_POST['capacite'] ?? 30);
        $desc = trim($_POST['description'] ?? '');

        if (empty($nom) || empty($code)) {
            Session::setFlash('error', 'Le nom et le code de la salle sont obligatoires.');
            header('Location: /timetables/rooms');
            exit;
        }

        try {
            $this->roomModel->create([
                'nom' => $nom,
                'code' => $code,
                'capacite' => $capacite,
                'description' => $desc,
                'status' => 1
            ]);
            Session::setFlash('success', 'Salle de classe ajoutée avec succès.');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Erreur : Le code de la salle existe déjà.');
        }

        header('Location: /timetables/rooms');
        exit;
    }

    public function updateRoom()
    {
        PermissionManager::requirePermission('manage_timetables');
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Jeton CSRF invalide.');
            header('Location: /timetables/rooms');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $nom = trim($_POST['nom'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $capacite = (int)($_POST['capacite'] ?? 30);
        $desc = trim($_POST['description'] ?? '');
        $status = (int)($_POST['status'] ?? 1);

        $this->roomModel->update($id, [
            'nom' => $nom,
            'code' => $code,
            'capacite' => $capacite,
            'description' => $desc,
            'status' => $status
        ]);

        Session::setFlash('success', 'Salle de classe mise à jour.');
        header('Location: /timetables/rooms');
        exit;
    }

    public function deleteRoom()
    {
        PermissionManager::requirePermission('manage_timetables');
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Jeton CSRF invalide.');
            header('Location: /timetables/rooms');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $this->roomModel->delete($id);

        Session::setFlash('success', 'Salle de classe supprimée.');
        header('Location: /timetables/rooms');
        exit;
    }

    /**
     * Sous-module 3 : Gestion des Semaines de Cours.
     */
    public function weeks()
    {
        PermissionManager::requirePermission('manage_timetables');

        $activeYear = $this->academicYearService->getActiveYear();
        $activeYearId = $activeYear ? (int)$activeYear['id'] : 0;

        $weeks = $this->weekModel->getAll();
        $years = $this->db->query("SELECT id, nom as libelle FROM academic_years ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

        $suggestion = $activeYearId ? $this->weekModel->suggestNextWeek($activeYearId) : null;

        require __DIR__ . '/../Views/timetables/weeks.php';
    }

    public function storeWeek()
    {
        PermissionManager::requirePermission('manage_timetables');
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Jeton CSRF invalide.');
            header('Location: /timetables/weeks');
            exit;
        }

        $yearId = (int)($_POST['academic_year_id'] ?? 0);
        $libelle = trim($_POST['libelle'] ?? '');
        $start = trim($_POST['date_debut'] ?? '');
        $end = trim($_POST['date_fin'] ?? '');

        if (empty($libelle) || empty($start) || empty($end) || !$yearId) {
            Session::setFlash('error', 'Tous les champs sont requis pour créer une semaine.');
            header('Location: /timetables/weeks');
            exit;
        }

        if (strtotime($end) < strtotime($start)) {
            Session::setFlash('error', 'La date de fin ne peut pas être antérieure à la date de début.');
            header('Location: /timetables/weeks');
            exit;
        }

        if ($this->weekModel->hasOverlap($yearId, $start, $end)) {
            Session::setFlash('error', 'Chevauchement de périodes : Une autre semaine existe déjà dans cette plage de dates pour cette année académique.');
            header('Location: /timetables/weeks');
            exit;
        }

        try {
            $this->weekModel->create([
                'academic_year_id' => $yearId,
                'libelle' => $libelle,
                'date_debut' => $start,
                'date_fin' => $end
            ]);
            Session::setFlash('success', 'Semaine de cours enregistrée.');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Une semaine avec la même date de début existe déjà pour cette année académique.');
        }

        header('Location: /timetables/weeks');
        exit;
    }

    public function updateWeek()
    {
        PermissionManager::requirePermission('manage_timetables');
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Jeton CSRF invalide.');
            header('Location: /timetables/weeks');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $yearId = (int)($_POST['academic_year_id'] ?? 0);
        $libelle = trim($_POST['libelle'] ?? '');
        $start = trim($_POST['date_debut'] ?? '');
        $end = trim($_POST['date_fin'] ?? '');

        if ($this->weekModel->hasOverlap($yearId, $start, $end, $id)) {
            Session::setFlash('error', 'Chevauchement de dates détecté lors de la modification de la semaine.');
            header('Location: /timetables/weeks');
            exit;
        }

        $this->weekModel->update($id, [
            'academic_year_id' => $yearId,
            'libelle' => $libelle,
            'date_debut' => $start,
            'date_fin' => $end
        ]);

        Session::setFlash('success', 'Semaine de cours mise à jour.');
        header('Location: /timetables/weeks');
        exit;
    }

    public function deleteWeek()
    {
        PermissionManager::requirePermission('manage_timetables');
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Jeton CSRF invalide.');
            header('Location: /timetables/weeks');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $this->weekModel->delete($id);

        Session::setFlash('success', 'Semaine de cours supprimée.');
        header('Location: /timetables/weeks');
        exit;
    }

    /**
     * Sous-module 4 : Assistant de Création Step-by-Step (Wizard).
     */
    public function wizard()
    {
        PermissionManager::requirePermission('manage_timetables');

        $activeYear = $this->academicYearService->getActiveYear();
        $activeYearId = $activeYear ? (int)$activeYear['id'] : 0;

        $teachingTypes = $this->wizardService->getTeachingTypes();
        $defaultType = null;
        foreach ($teachingTypes as $tt) {
            if ($tt['is_default']) {
                $defaultType = $tt;
                break;
            }
        }
        if (!$defaultType && !empty($teachingTypes)) {
            $defaultType = $teachingTypes[0];
        }

        $cycles = $defaultType ? $this->wizardService->getCyclesByTeachingType((int)$defaultType['id']) : [];
        $weeks = $activeYearId ? $this->wizardService->getWeeksByAcademicYear($activeYearId) : [];

        require __DIR__ . '/../Views/timetables/wizard.php';
    }

    /**
     * API AJAX pour charger dynamiquement les données du Wizard.
     */
    public function wizardStepData()
    {
        header('Content-Type: application/json');
        $step = $_GET['step'] ?? '';

        $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (empty($step) && strpos($requestPath, '/timetables/api/wizard/') === 0) {
            $step = basename($requestPath);
        }


        if ($step === 'cycles') {
            $typeId = (int)($_GET['teaching_type_id'] ?? 9);
            $cycles = $this->wizardService->getCyclesByTeachingType($typeId);
            echo json_encode(['success' => true, 'cycles' => $cycles]);
            exit;
        } elseif ($step === 'levels') {
            $cycleId = (int)($_GET['cycle_id'] ?? 0);
            $levels = $this->wizardService->getLevelsByCycle($cycleId);
            echo json_encode(['success' => true, 'levels' => $levels]);
            exit;
        } elseif ($step === 'classes') {
            $cycleId = (int)($_GET['cycle_id'] ?? 0);
            $levelId = (int)($_GET['level_id'] ?? 0);
            $classes = $this->wizardService->getClassesByLevel($cycleId, $levelId);
            echo json_encode(['success' => true, 'classes' => $classes]);
            exit;
        } elseif ($step === 'weeks') {
            $activeYear = $this->academicYearService->getActiveYear();
            $activeYearId = $activeYear ? (int)$activeYear['id'] : 0;
            $weeks = $this->wizardService->getWeeksByAcademicYear($activeYearId);
            echo json_encode(['success' => true, 'weeks' => $weeks]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Étape inconnue']);
        exit;
    }

    /**
     * Création initiale de la grille (par l'assistant Wizard).
     */
    public function createTimetable()
    {
        PermissionManager::requirePermission('manage_timetables');
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Jeton CSRF invalide.');
            header('Location: /timetables/wizard');
            exit;
        }

        $activeYear = $this->academicYearService->getActiveYear();
        $activeYearId = $activeYear ? (int)$activeYear['id'] : 0;

        $typeId = (int)($_POST['teaching_type_id'] ?? 9);
        $cycleId = (int)($_POST['cycle_id'] ?? 0);
        $levelId = (int)($_POST['level_id'] ?? 0);
        $weekId = (int)($_POST['week_id'] ?? 0);

        if (!$activeYearId || !$cycleId || !$weekId) {
            Session::setFlash('error', 'Veuillez sélectionner au moins le cycle et la semaine.');
            header('Location: /timetables/wizard');
            exit;
        }

        // Validation backend stricte anti-injection de cycle secondaire
        $validCycleStmt = $this->db->prepare("
            SELECT c.id 
            FROM cycles c 
            LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id 
            WHERE c.id = :cycle_id 
              AND (c.teaching_type_id = 9 OR tt.code = 'LMD' OR LOWER(tt.nom) LIKE '%lmd%' OR LOWER(tt.nom) LIKE '%supérieur%' OR c.teaching_type_id IS NULL)
              AND (tt.code IS NULL OR (tt.code != 'SEC00' AND LOWER(tt.nom) NOT LIKE '%secondaire%'))
              AND LOWER(c.nom) NOT LIKE '%1ere cycle%' 
              AND LOWER(c.nom) NOT LIKE '%2nd cycle%' 
              AND LOWER(c.nom) NOT LIKE '%secondaire%'
        ");
        $validCycleStmt->execute(['cycle_id' => $cycleId]);
        if (!$validCycleStmt->fetch()) {
            Session::setFlash('error', 'Le cycle sélectionné n\'est pas valide pour le Supérieur LMD.');
            header('Location: /timetables/wizard');
            exit;
        }

        // Récupérer les classes du niveau
        $classes = $this->wizardService->getClassesByLevel($cycleId, $levelId);

        // Assurer l'existence d'une entrée dans 'timetables' pour chaque classe du niveau
        foreach ($classes as $cls) {
            $classId = (int)$cls['id'];
            $existing = $this->timetableModel->findByClassAndWeek($classId, $weekId);
            if (!$existing) {
                $weekRow = $this->db->query("SELECT libelle FROM timetable_weeks WHERE id = $weekId")->fetch(PDO::FETCH_ASSOC);
                $titre = "Emploi du Temps - " . $cls['nom'] . " (" . ($weekRow['libelle'] ?? 'Semaine') . ")";
                $this->timetableModel->create([
                    'academic_year_id' => $activeYearId,
                    'teaching_type_id' => $typeId,
                    'cycle_id' => $cycleId,
                    'class_id' => $classId,
                    'week_id' => $weekId,
                    'titre' => $titre,
                    'statut' => 'brouillon',
                    'created_by' => Session::get('user_id')
                ]);
            }
        }

        header("Location: /timetables/grid?cycle_id={$cycleId}&level_id={$levelId}&week_id={$weekId}");
        exit;
    }

    /**
     * Grille de planification interactive (Étape 5 / Vue Multi-classes par Niveau).
     */
    public function grid()
    {
        $cycleId = (int)($_GET['cycle_id'] ?? 0);
        $levelId = (int)($_GET['level_id'] ?? 0);
        $weekId = (int)($_GET['week_id'] ?? 0);
        $id = (int)($_GET['id'] ?? 0);

        if ($id > 0 && (!$cycleId || !$weekId)) {
            $timetable = $this->timetableModel->find($id);
            if ($timetable) {
                $cycleId = (int)$timetable['cycle_id'];
                $weekId = (int)$timetable['week_id'];
                $clsRow = $this->db->query("SELECT level_id FROM classes WHERE id = " . (int)$timetable['class_id'])->fetch(PDO::FETCH_ASSOC);
                $levelId = $clsRow['level_id'] ? (int)$clsRow['level_id'] : $levelId;
            }
        }

        if (!$cycleId || !$weekId) {
            Session::setFlash('error', 'Semaine et cycle obligatoires pour afficher la grille.');
            header('Location: /timetables');
            exit;
        }

        // Charger les métadonnées de la semaine
        $weekRow = $this->db->query("SELECT * FROM timetable_weeks WHERE id = $weekId")->fetch(PDO::FETCH_ASSOC);
        $cycleRow = $this->db->query("SELECT * FROM cycles WHERE id = $cycleId")->fetch(PDO::FETCH_ASSOC);
        $levelRow = $levelId ? $this->db->query("SELECT * FROM levels WHERE id = $levelId")->fetch(PDO::FETCH_ASSOC) : null;
        $activeYear = $this->academicYearService->getActiveYear();

        // Récupérer la grille multi-classes
        $gridData = $this->wizardService->getMultiClassGridData($cycleId, $levelId, $weekId);
        $classIds = array_column($gridData['classes'], 'id');

        // Récupérer les conflits visuels sur la grille
        $gridConflicts = $this->conflictService->getGridConflictsForWeek($weekId, $classIds);

        // Déterminer le statut de verrouillage global
        $isLocked = false;
        $canEdit = true;
        foreach ($gridData['timetablesByClass'] as $tt) {
            if ($this->lockService->checkAutoLock((int)$tt['id'])) {
                $isLocked = true;
            }
            if (!$this->lockService->canModify($tt)) {
                $canEdit = false;
            }
        }

        $school_name = $this->settingsStore->get('school_name', 'NoteMaster School');
        $school_code = $this->settingsStore->get('school_code', 'NMS');

        require __DIR__ . '/../Views/timetables/grid.php';
    }

    /**
     * API AJAX : Enregistrement ou mise à jour d'un créneau dans la grille.
     */
    public function saveGridEntry()
    {
        header('Content-Type: application/json');
        PermissionManager::requirePermission('manage_timetables');

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $classIds = !empty($input['class_ids']) ? array_map('intval', (array)$input['class_ids']) : [];
        if (empty($classIds) && !empty($input['class_id'])) {
            $classIds = [(int)$input['class_id']];
        }
        $removedClassIds = !empty($input['removed_class_ids']) ? array_map('intval', (array)$input['removed_class_ids']) : [];

        $weekId = (int)($input['week_id'] ?? 0);
        $slotId = (int)($input['slot_id'] ?? 0);
        $dayOfWeek = trim($input['day_of_week'] ?? '');
        $subjectId = (int)($input['subject_id'] ?? 0);
        $teacherId = (int)($input['teacher_id'] ?? 0);
        $roomId = (int)($input['room_id'] ?? 0);
        $color = trim($input['couleur_hex'] ?? '#3b82f6');
        $userId = (int)Session::get('user_id');

        if (empty($classIds) || !$weekId || !$slotId || !$subjectId || !$teacherId || !$roomId) {
            echo json_encode(['success' => false, 'message' => 'Paramètres invalides. Veuillez vous assurer d\'avoir sélectionné au moins une classe.']);
            exit;
        }

        // 1. Libérer le créneau uniquement pour les classes expressément retirées du modal
        foreach ($removedClassIds as $remClassId) {
            $remTt = $this->timetableModel->findByClassAndWeek($remClassId, $weekId);
            if ($remTt && $this->lockService->canModify($remTt)) {
                $this->entryModel->deleteEntry((int)$remTt['id'], $slotId, $dayOfWeek);
            }
        }

        $savedCount = 0;
        $errorMessages = [];

        // 2. Traiter l'enregistrement de l'affectation pour chaque classe conservée/sélectionnée
        foreach ($classIds as $classId) {
            $timetable = $this->timetableModel->findByClassAndWeek($classId, $weekId);
            $timetableId = $timetable ? (int)$timetable['id'] : 0;

            if (!$timetableId) {
                $activeYear = $this->academicYearService->getActiveYear();
                $activeYearId = $activeYear ? (int)$activeYear['id'] : 0;
                $classRow = $this->db->query("SELECT nom, cycle_id, teaching_type_id FROM classes WHERE id = $classId")->fetch(PDO::FETCH_ASSOC);
                $weekRow = $this->db->query("SELECT libelle FROM timetable_weeks WHERE id = $weekId")->fetch(PDO::FETCH_ASSOC);

                $timetableId = $this->timetableModel->create([
                    'academic_year_id' => $activeYearId,
                    'teaching_type_id' => $classRow['teaching_type_id'] ?? 9,
                    'cycle_id' => $classRow['cycle_id'] ?? 1,
                    'class_id' => $classId,
                    'week_id' => $weekId,
                    'titre' => "Emploi du Temps - " . ($classRow['nom'] ?? 'Classe') . " (" . ($weekRow['libelle'] ?? 'Semaine') . ")",
                    'statut' => 'brouillon',
                    'created_by' => $userId
                ]);
                $timetable = $this->timetableModel->find($timetableId);
            }

            if (!$timetable || !$this->lockService->canModify($timetable)) {
                $errorMessages[] = "L'emploi du temps de la classe #{$classId} est verrouillé.";
                continue;
            }

            // Validation des conflits
            $check = $this->conflictService->checkConflict(
                $timetableId,
                $weekId,
                $slotId,
                $dayOfWeek,
                $classId,
                $teacherId,
                $roomId,
                null,
                $subjectId
            );

            if ($check['has_conflict']) {
                $errorMessages[] = implode(' | ', $check['messages']);
                continue;
            }

            $saved = $this->entryModel->upsertEntry([
                'timetable_id' => $timetableId,
                'slot_id' => $slotId,
                'day_of_week' => $dayOfWeek,
                'subject_id' => $subjectId,
                'teacher_id' => $teacherId,
                'room_id' => $roomId,
                'couleur_hex' => $color
            ]);

            if ($saved) {
                $savedCount++;
                $activeYear = $this->academicYearService->getActiveYear();
                $activeYearId = $activeYear ? (int)$activeYear['id'] : 0;

                // Association Matière-Classe si non existante (avec journalisation)
                try {
                    $stmtCheckSubClass = $this->db->prepare("SELECT 1 FROM subject_classes WHERE class_id = ? AND subject_id = ?");
                    $stmtCheckSubClass->execute([$classId, $subjectId]);
                    if (!$stmtCheckSubClass->fetchColumn()) {
                        $stmtInsSubClass = $this->db->prepare("
                            INSERT INTO subject_classes (subject_id, class_id, academic_year_id)
                            VALUES (?, ?, ?)
                            ON DUPLICATE KEY UPDATE subject_id = VALUES(subject_id)
                        ");
                        $stmtInsSubClass->execute([$subjectId, $classId, $activeYearId]);

                        $subRow = $this->db->query("SELECT nom FROM subjects WHERE id = $subjectId")->fetch(PDO::FETCH_ASSOC);
                        $clsRow = $this->db->query("SELECT nom FROM classes WHERE id = $classId")->fetch(PDO::FETCH_ASSOC);
                        $subName = $subRow['nom'] ?? "Matière #$subjectId";
                        $clsName = $clsRow['nom'] ?? "Classe #$classId";

                        Security::log("Association automatique Classe '{$clsName}' <-> Matière '{$subName}' créée lors de la planification par l'utilisateur #{$userId}.");
                        $this->auditLogModel->logAction(
                            $timetableId,
                            $userId,
                            'ATTACH_SUBJECT',
                            "Rattachement automatique de la matière '{$subName}' à la classe '{$clsName}'.",
                            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                        );
                    }
                } catch (\Throwable $e) {}

                // Affectation Enseignant-Matière
                try {
                    $stmtAssig = $this->db->prepare("
                        INSERT INTO teacher_assignments (user_id, subject_id, class_id, academic_year_id)
                        VALUES (?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE user_id = VALUES(user_id)
                    ");
                    $stmtAssig->execute([$teacherId, $subjectId, $classId ?: null, $activeYearId]);
                } catch (\Throwable $e) {}
            }
        }

        if ($savedCount > 0) {
            echo json_encode(['success' => true, 'message' => "Créneau affecté avec succès pour $savedCount classe(s)."]);
        } else {
            echo json_encode(['success' => false, 'message' => !empty($errorMessages) ? implode(' | ', $errorMessages) : 'Échec de l\'enregistrement.']);
        }
        exit;
    }


    /**
     * API AJAX : Récupérer les matières associées à une classe.
     */
    public function apiGetClassSubjects()
    {
        header('Content-Type: application/json');
        $classId = (int)($_GET['class_id'] ?? 0);
        $subjects = $this->wizardService->getSubjectsByClass($classId);
        echo json_encode(['success' => true, 'subjects' => $subjects]);
        exit;
    }

    /**
     * API AJAX : Récupérer les enseignants affectés à une matière (ou fallback LMD).
     */
    public function apiGetSubjectTeachers()
    {
        header('Content-Type: application/json');
        $subjectId = (int)($_GET['subject_id'] ?? 0);
        $classId = (int)($_GET['class_id'] ?? 0);

        $teachers = $this->wizardService->getTeachersBySubject($subjectId, $classId);
        echo json_encode(['success' => true, 'teachers' => $teachers]);
        exit;
    }

    /**
     * API AJAX : Création rapide d'un nouvel enseignant et affectation immédiate de la matière.
     */
    public function apiQuickCreateTeacher()
    {
        header('Content-Type: application/json');
        PermissionManager::requirePermission('manage_timetables');

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $name = trim($input['nom_complet'] ?? '');
        $subjectId = (int)($input['subject_id'] ?? 0);
        $classId = (int)($input['class_id'] ?? 0);

        if (empty($name) || !$subjectId) {
            echo json_encode(['success' => false, 'message' => 'Veuillez saisir le nom de l\'enseignant et sélectionner une matière.']);
            exit;
        }

        // Séparer nom et prénom
        $parts = explode(' ', $name, 2);
        $nom = $parts[0];
        $prenom = $parts[1] ?? '';

        // Générer un email factice / fonctionnel unique
        $emailSlug = strtolower(preg_replace('/[^a-z0-9]/', '', $nom . $prenom)) . '_' . time() . '@institution.local';
        $username = strtolower(preg_replace('/[^a-z0-9]/', '', $nom . $prenom)) . '_' . rand(100, 999);
        $passwordHash = password_hash('Enseignant' . rand(1000, 9999), PASSWORD_DEFAULT);

        try {
            // 1. Création de l'utilisateur enseignant
            $stmt = $this->db->prepare("
                INSERT INTO users (nom, prenom, email, username, password, role, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'enseignant', 1, NOW())
            ");
            $stmt->execute([$nom, $prenom, $emailSlug, $username, $passwordHash]);
            $teacherId = (int)$this->db->lastInsertId();


            // Rattaché au Supérieur LMD via user_teaching_types
            $this->db->exec("INSERT INTO user_teaching_types (user_id, teaching_type_id) VALUES ($teacherId, 9) ON DUPLICATE KEY UPDATE teaching_type_id = 9");


            // 2. Affectation à la matière
            $activeYear = $this->academicYearService->getActiveYear();
            $activeYearId = $activeYear ? (int)$activeYear['id'] : 0;

            $stmtAssig = $this->db->prepare("
                INSERT INTO teacher_assignments (user_id, subject_id, class_id, academic_year_id)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE user_id = VALUES(user_id)
            ");
            $stmtAssig->execute([$teacherId, $subjectId, $classId ?: null, $activeYearId]);

            $fullName = trim($nom . ' ' . $prenom);

            echo json_encode([
                'success' => true,
                'message' => "L'enseignant $fullName a été créé et affecté avec succès à la matière.",
                'teacher' => [
                    'id' => $teacherId,
                    'nom_complet' => $fullName,
                    'role' => 'enseignant',
                    'is_unassigned_fallback' => 0
                ]
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la création : ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * API AJAX : Suppression d'un créneau dans la grille.
     */
    public function deleteGridEntry()
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $timetableId = (int)($input['timetable_id'] ?? 0);
        $slotId = (int)($input['slot_id'] ?? 0);
        $dayOfWeek = trim($input['day_of_week'] ?? '');

        $timetable = $this->timetableModel->find($timetableId);
        if (!$timetable || !$this->lockService->canModify($timetable)) {
            echo json_encode(['success' => false, 'message' => 'Action non autorisée ou emploi du temps verrouillé.']);
            exit;
        }

        $deleted = $this->entryModel->deleteEntry($timetableId, $slotId, $dayOfWeek);
        echo json_encode(['success' => $deleted, 'message' => $deleted ? 'Créneau libéré.' : 'Échec de suppression.']);
        exit;
    }

    /**
     * API AJAX : Validation préalable de conflit en temps réel.
     */
    public function apiValidateConflict()
    {
        header('Content-Type: application/json');
        $timetableId = (int)($_GET['timetable_id'] ?? 0);
        $weekId = (int)($_GET['week_id'] ?? 0);
        $classId = (int)($_GET['class_id'] ?? 0);
        $slotId = (int)($_GET['slot_id'] ?? 0);
        $dayOfWeek = trim($_GET['day_of_week'] ?? '');
        $subjectId = (int)($_GET['subject_id'] ?? 0);
        $teacherId = (int)($_GET['teacher_id'] ?? 0);
        $roomId = (int)($_GET['room_id'] ?? 0);

        if (!$weekId && $timetableId) {
            $tt = $this->timetableModel->find($timetableId);
            $weekId = $tt ? (int)$tt['week_id'] : 0;
            $classId = $tt ? (int)$tt['class_id'] : $classId;
        }

        $check = $this->conflictService->checkConflict(
            $timetableId,
            $weekId,
            $slotId,
            $dayOfWeek,
            $classId,
            $teacherId,
            $roomId,
            null,
            $subjectId
        );

        echo json_encode($check);
        exit;
    }

    /**
     * API AJAX : Pré-validation de la planification en masse des cours.
     */
    public function apiBulkValidate()
    {
        header('Content-Type: application/json');
        PermissionManager::requirePermission('manage_timetables');

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $result = $this->bulkService->validateBulkSchedule($input);

        echo json_encode($result);
        exit;
    }

    /**
     * API AJAX : Enregistrement transactionnel en masse des cours.
     */
    public function apiBulkSave()
    {
        header('Content-Type: application/json');
        PermissionManager::requirePermission('manage_timetables');

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $schedules = $input['schedules'] ?? [];
        $userId = (int)Session::get('user_id');

        $result = $this->bulkService->saveBulkSchedule($schedules, $userId);

        echo json_encode($result);
        exit;
    }


    /**
     * Déverrouillage manuel par le Super Administrateur.
     */
    public function unlock()
    {
        PermissionManager::requireRole('superadmin');
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Jeton CSRF invalide.');
            header('Location: /timetables');
            exit;
        }

        $id = (int)($_POST['timetable_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? 'Déverrouillage manuel exceptionnel');

        $unlocked = $this->lockService->unlockBySuperAdmin($id, $reason);

        if ($unlocked) {
            Session::setFlash('success', 'L\'emploi du temps a été déverrouillé avec succès.');
        } else {
            Session::setFlash('error', 'Échec du déverrouillage.');
        }

        header('Location: /timetables/grid?id=' . $id);
        exit;
    }

    /**
     * Suppression définitive d'un emploi du temps (Réservé au Super Administrateur).
     */
    public function deleteTimetable()
    {
        PermissionManager::requireRole('superadmin');
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Jeton CSRF invalide.');
            header('Location: /timetables');
            exit;
        }

        $rawIds = $_POST['timetable_ids'] ?? $_POST['timetable_id'] ?? $_POST['id'] ?? '';
        $ids = array_filter(array_map('intval', explode(',', (string)$rawIds)));

        if (empty($ids)) {
            Session::setFlash('error', 'Emploi du temps introuvable.');
            header('Location: /timetables');
            exit;
        }

        $deletedCount = 0;
        foreach ($ids as $id) {
            if ($this->timetableModel->delete($id)) {
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            Security::log("Suppression de {$deletedCount} grille(s) d'emploi du temps par Super Administrateur.");
            Session::setFlash('success', 'L\'emploi du temps regroupé a été supprimé avec succès.');
        } else {
            Session::setFlash('error', 'Échec de la suppression de l\'emploi du temps.');
        }

        header('Location: /timetables');
        exit;
    }

    /**
     * Export PDF / Vue Impression d'un emploi du temps multi-classes par niveau.
     */
    public function exportPdf()
    {
        $cycleId = (int)($_GET['cycle_id'] ?? 0);
        $levelId = (int)($_GET['level_id'] ?? 0);
        $weekId = (int)($_GET['week_id'] ?? 0);
        $id = (int)($_GET['id'] ?? 0);
        $mode = $_GET['mode'] ?? 'download'; // 'download' ou 'print'

        if ($id > 0 && (!$cycleId || !$weekId)) {
            $timetable = $this->timetableModel->find($id);
            if ($timetable) {
                $cycleId = (int)$timetable['cycle_id'];
                $weekId = (int)$timetable['week_id'];
                $clsRow = $this->db->query("SELECT level_id FROM classes WHERE id = " . (int)$timetable['class_id'])->fetch(PDO::FETCH_ASSOC);
                $levelId = $clsRow['level_id'] ? (int)$clsRow['level_id'] : $levelId;
            }
        }

        if (!$cycleId) {
            $firstCycle = $this->db->query("SELECT id FROM cycles ORDER BY nom ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            $cycleId = $firstCycle ? (int)$firstCycle['id'] : 0;
        }

        if (!$weekId) {
            $firstWeek = $this->db->query("SELECT id FROM timetable_weeks ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            $weekId = $firstWeek ? (int)$firstWeek['id'] : 0;
        }

        if (!$cycleId || !$weekId) {
            Session::setFlash('error', 'Aucun emploi du temps disponible pour la prévisualisation.');
            header('Location: /timetables');
            exit;
        }

        // Charger les métadonnées de la semaine
        $weekRow = $this->db->query("SELECT * FROM timetable_weeks WHERE id = $weekId")->fetch(PDO::FETCH_ASSOC);
        $cycleRow = $this->db->query("SELECT * FROM cycles WHERE id = $cycleId")->fetch(PDO::FETCH_ASSOC);
        $levelRow = $levelId ? $this->db->query("SELECT * FROM levels WHERE id = $levelId")->fetch(PDO::FETCH_ASSOC) : null;
        $activeYear = $this->academicYearService->getActiveYear();

        $gridData = $this->wizardService->getMultiClassGridData($cycleId, $levelId, $weekId);

        if (!empty($cycleRow['teaching_type_id'])) {
            $this->settingsStore->setTeachingTypeId((int)$cycleRow['teaching_type_id']);
        }

        $school_name = $this->settingsStore->get('school_name', 'NoteMaster School');
        $school_code = $this->settingsStore->get('school_code', 'NMS');
        $school_logo = $this->settingsStore->get('school_logo', '/public/assets/images/logo.png');
        $creation_decree = $this->settingsStore->get('creation_decree', '');
        if (empty($creation_decree)) {
            $stmtDecree = $this->db->query("SELECT setting_value FROM settings WHERE setting_key = 'creation_decree' AND setting_value IS NOT NULL AND TRIM(setting_value) != '' ORDER BY id DESC LIMIT 1");
            $creation_decree = $stmtDecree ? (string)$stmtDecree->fetchColumn() : '';
        }
        $partner_logo = $this->settingsStore->get('partner_logo', $this->settingsStore->get('academic_partner_logo', ''));
        if (empty($partner_logo) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/public/uploads/1785328229_logo-camertech.png')) {
            $partner_logo = '/public/uploads/1785328229_logo-camertech.png';
        }

        if ($mode === 'print' || $mode === 'preview') {
            require __DIR__ . '/../Views/timetables/pdf.php';
            exit;
        }

        // Dompdf Export
        ob_start();
        require __DIR__ . '/../Views/timetables/pdf.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'Emploi_du_Temps_' . str_replace(' ', '_', $cycleRow['nom'] ?? 'Cycle') . '_' . str_replace(' ', '_', $weekRow['libelle'] ?? 'Semaine') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => 1]);
    }

    /**
     * Page d'impression dédiée des emplois du temps (Menu Imprimer).
     * Reprend le design d'île flottante et le parcours unifié NoteMaster.
     */
    public function printIndex()
    {
        PermissionManager::requirePermission('view_timetables');

        $activeYear = $this->academicYearService->getActiveYear();
        $activeYearId = $activeYear ? (int)$activeYear['id'] : 0;

        $academicYears = $this->academicYearService->getAllYears();
        $selectedYearId = (int)($_GET['academic_year_id'] ?? $activeYearId);

        // Récupération des cycles (Supérieur LMD uniquement)
        $cycles = $this->wizardService->getCyclesByTeachingType(9);
        $selectedCycleId = (int)($_GET['cycle_id'] ?? ($cycles[0]['id'] ?? 0));

        // Récupération des niveaux pour le cycle sélectionné via la table pivot ou le wizard service
        $levels = [];
        if ($selectedCycleId > 0) {
            $levels = $this->wizardService->getLevelsByCycle($selectedCycleId);
        }
        $selectedLevelId = (int)($_GET['level_id'] ?? 0);

        // Récupération des semaines de cours disponibles
        $weeks = $this->weekModel->getAll();
        $selectedWeekId = (int)($_GET['week_id'] ?? ($weeks[0]['id'] ?? 0));

        require __DIR__ . '/../Views/timetables/print.php';
    }
}

