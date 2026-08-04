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

        $timetables = $this->timetableModel->getAllFiltered(
            $selectedYear ?: null,
            $selectedClass ?: null,
            $selectedWeek ?: null
        );

        // Mettre à jour les statuts de verrouillage 168h
        foreach ($timetables as &$t) {
            $t['is_locked_calc'] = $this->lockService->checkAutoLock((int)$t['id']);
            $t['can_edit'] = $this->lockService->canModify($t);
        }

        $years = $this->db->query("SELECT id, nom as libelle FROM academic_years ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        $classes = $this->db->query("SELECT id, nom FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
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

        if ($step === 'cycles') {
            $typeId = (int)($_GET['teaching_type_id'] ?? 0);
            $cycles = $this->wizardService->getCyclesByTeachingType($typeId);
            echo json_encode(['success' => true, 'cycles' => $cycles]);
            exit;
        } elseif ($step === 'classes') {
            $cycleId = (int)($_GET['cycle_id'] ?? 0);
            $classes = $this->wizardService->getClassesByCycle($cycleId);
            echo json_encode(['success' => true, 'classes' => $classes]);
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

        $typeId = (int)($_POST['teaching_type_id'] ?? 0);
        $cycleId = (int)($_POST['cycle_id'] ?? 0);
        $classId = (int)($_POST['class_id'] ?? 0);
        $weekId = (int)($_POST['week_id'] ?? 0);

        if (!$activeYearId || !$typeId || !$cycleId || !$classId || !$weekId) {
            Session::setFlash('error', 'Veuillez compléter toutes les étapes de l\'assistant.');
            header('Location: /timetables/wizard');
            exit;
        }

        // Vérifier si un emploi existe déjà pour cette classe et cette semaine
        $existing = $this->timetableModel->findByClassAndWeek($classId, $weekId);
        if ($existing) {
            header('Location: /timetables/grid?id=' . $existing['id']);
            exit;
        }

        // Récupérer les noms pour former le titre
        $classRow = $this->db->query("SELECT nom FROM classes WHERE id = $classId")->fetch(PDO::FETCH_ASSOC);
        $weekRow = $this->db->query("SELECT libelle FROM timetable_weeks WHERE id = $weekId")->fetch(PDO::FETCH_ASSOC);

        $titre = "Emploi du Temps - " . ($classRow['nom'] ?? 'Classe') . " (" . ($weekRow['libelle'] ?? 'Semaine') . ")";

        $timetableId = $this->timetableModel->create([
            'academic_year_id' => $activeYearId,
            'teaching_type_id' => $typeId,
            'cycle_id' => $cycleId,
            'class_id' => $classId,
            'week_id' => $weekId,
            'titre' => $titre,
            'statut' => 'brouillon',
            'created_by' => Session::get('user_id')
        ]);

        header('Location: /timetables/grid?id=' . $timetableId);
        exit;
    }

    /**
     * Grille de planification interactive (Étape 5 / Mode Édition).
     */
    public function grid()
    {
        $id = (int)($_GET['id'] ?? 0);
        $timetable = $this->timetableModel->find($id);

        if (!$timetable) {
            Session::setFlash('error', 'Emploi du temps introuvable.');
            header('Location: /timetables');
            exit;
        }

        // Vérification automatique du verrouillage 168h
        $isLocked = $this->lockService->checkAutoLock($id);
        $canEdit = $this->lockService->canModify($timetable);

        $entries = $this->entryModel->getByTimetable($id);
        $gridData = $this->wizardService->getGridDataForClass((int)$timetable['class_id']);

        // Indexer les entrées par slot_id et day_of_week
        $matrix = [];
        foreach ($entries as $e) {
            $matrix[$e['slot_id']][$e['day_of_week']] = $e;
        }

        $auditLogs = $this->auditLogModel->getLogsByTimetable($id);

        require __DIR__ . '/../Views/timetables/grid.php';
    }

    /**
     * API AJAX : Enregistrement ou mise à jour d'un créneau dans la grille.
     */
    public function saveGridEntry()
    {
        header('Content-Type: application/json');
        if (!Session::isLogged()) {
            echo json_encode(['success' => false, 'message' => 'Non authentifié']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $timetableId = (int)($input['timetable_id'] ?? 0);
        $slotId = (int)($input['slot_id'] ?? 0);
        $dayOfWeek = trim($input['day_of_week'] ?? '');
        $subjectId = (int)($input['subject_id'] ?? 0);
        $teacherId = (int)($input['teacher_id'] ?? 0);
        $roomId = (int)($input['room_id'] ?? 0);
        $color = trim($input['couleur_hex'] ?? '#3b82f6');

        $timetable = $this->timetableModel->find($timetableId);
        if (!$timetable) {
            echo json_encode(['success' => false, 'message' => 'Emploi du temps inexistant']);
            exit;
        }

        if (!$this->lockService->canModify($timetable)) {
            echo json_encode(['success' => false, 'message' => 'Cet emploi du temps est verrouillé (délai de 168h dépassé). Seul un Super Administrateur peut le modifier.']);
            exit;
        }

        // Vérification des conflits en temps réel
        $check = $this->conflictService->checkConflict(
            $timetableId,
            (int)$timetable['week_id'],
            $slotId,
            $dayOfWeek,
            (int)$timetable['class_id'],
            $teacherId,
            $roomId
        );

        if ($check['has_conflict']) {
            echo json_encode([
                'success' => false,
                'message' => implode(' | ', $check['messages']),
                'conflicts' => $check['messages']
            ]);
            exit;
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
            echo json_encode(['success' => true, 'message' => 'Créneau affecté avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement.']);
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
        $slotId = (int)($_GET['slot_id'] ?? 0);
        $dayOfWeek = trim($_GET['day_of_week'] ?? '');
        $teacherId = (int)($_GET['teacher_id'] ?? 0);
        $roomId = (int)($_GET['room_id'] ?? 0);

        $timetable = $this->timetableModel->find($timetableId);
        if (!$timetable) {
            echo json_encode(['has_conflict' => false]);
            exit;
        }

        $check = $this->conflictService->checkConflict(
            $timetableId,
            (int)$timetable['week_id'],
            $slotId,
            $dayOfWeek,
            (int)$timetable['class_id'],
            $teacherId,
            $roomId
        );

        echo json_encode($check);
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
            Session::setFlash('success', 'L\'emploi du temps a été déverrouillé avec succès. L\'action a été consignée dans le journal d\'audit.');
        } else {
            Session::setFlash('error', 'Échec du déverrouillage.');
        }

        header('Location: /timetables/grid?id=' . $id);
        exit;
    }

    /**
     * Export PDF / Vue Impression d'un emploi du temps.
     */
    public function exportPdf()
    {
        $id = (int)($_GET['id'] ?? 0);
        $mode = $_GET['mode'] ?? 'download'; // 'download' ou 'print'

        $timetable = $this->timetableModel->find($id);
        if (!$timetable) {
            Session::setFlash('error', 'Emploi du temps introuvable.');
            header('Location: /timetables');
            exit;
        }

        $entries = $this->entryModel->getByTimetable($id);
        $gridData = $this->wizardService->getGridDataForClass((int)$timetable['class_id']);

        $matrix = [];
        foreach ($entries as $e) {
            $matrix[$e['slot_id']][$e['day_of_week']] = $e;
        }

        $school_name = $this->settingsStore->get('school_name', 'NoteMaster School');
        $school_code = $this->settingsStore->get('school_code', 'NMS');

        if ($mode === 'print') {
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

        $filename = 'Emploi_du_Temps_' . str_replace(' ', '_', $timetable['class_name']) . '_' . str_replace(' ', '_', $timetable['week_libelle']) . '.pdf';
        $dompdf->stream($filename, ['Attachment' => 1]);
        exit;
    }
}
