<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Session;
use App\Core\Security;
use App\Core\Database;
use App\Core\Translator;
use App\Core\Locale;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\StudentController;
use App\Controllers\SubjectController;
use App\Controllers\AcademicYearController;
use App\Controllers\CycleController;
use App\Controllers\SectionController;
use App\Controllers\ClassController;
use App\Controllers\BulletinController;
use App\Controllers\ProcesVerbalController;
use App\Controllers\SequenceController;
use App\Controllers\SettingController;
use App\Controllers\TeacherController;
use App\Controllers\ProfileController;
use App\Controllers\GradeController;
use App\Controllers\DashboardController;
use App\Services\ActivityTracker;
use App\Controllers\DocumentationController;
use App\Controllers\HonorRollController;
use App\Controllers\DepartmentController;
use App\Controllers\LandingController;
use App\Controllers\TeachingTypeController;
use App\Controllers\PaymentController;
use App\Controllers\DiscountController;
use App\Controllers\ScholarshipController;
use App\Controllers\FinancialHistoryController;
use App\Controllers\DiscountTypeController;
use App\Controllers\SchoolFeeController;
use App\Controllers\AIAssistantController;
use App\Controllers\SubjectGroupController;
use App\Controllers\LevelController;
use App\Controllers\TranscriptController;
use App\Controllers\TimetableController;



use App\Controllers\RbacController;

Security::applyHeaders();

// 1. Initialise la session via le module Core
Session::start();

// 2. Langue interface (FR)
Locale::bootstrapFromRequest();

// 3. Helpers globaux (utilisés partout)
if (!function_exists('__')) {
    function __($key, $replacements = [], $count = null)
    {
        return Translator::translate($key, $replacements, $count);
    }
}

if (!function_exists('h')) {
    function h($string)
    {
        return htmlspecialchars((string) $string, ENT_QUOTES, 'UTF-8');
    }
}

// Récupération de l'URL pour le routage
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// BARRAGE DE SÉCURITÉ GLOBAL (Middleware)
// Redirige ABSOLUMENT tout visiteur non-authentifié ou session expirée vers l'écran de connexion.
if (!in_array($path, ['/', '/login', '/logout', '/register-teacher', '/sitemap.xml', '/contact/send', '/payments/verify']) && strpos($path, '/verify-receipt') !== 0 && !Security::validateSession()) {
    header('Location: /login');
    exit;
}

if (Session::isLogged()) {
    try {
        (new ActivityTracker(Database::getInstance()->getConnection()))->trackRequest($path, $method);
    } catch (\Throwable $e) {
    }
}

// 4. Route spéciale pour le changement de langue (Locale Switcher)
if ($path === '/locale') {
    $lang = $_GET['lang'] ?? 'fr';
    $redirect = $_GET['redirect'] ?? '/';
    Locale::set($lang);
    header('Location: ' . $redirect);
    exit;
}

// 5. SEO & Public Actions
if ($path === '/sitemap.xml') {
    header('Content-Type: application/xml');
    include __DIR__ . '/sitemap.xml.php';
    exit;
}

if ($path === '/contact/send' && $method === 'POST') {
    $c = new LandingController();
    $c->sendContact();
    exit;
}

if ($path === '/notifications/toggle-archive') {
    $c = new LandingController();
    $c->toggleArchiveNotification();
    exit;
}

if ($path === '/notifications/delete') {
    $c = new LandingController();
    $c->deleteNotification();
    exit;
}

// ====== ROUTES: RADIOGRAPHIE D'IMPACT & SMART DELETE ======
if ($path === '/api/impact-analysis' && $method === 'GET') {
    (new \App\Controllers\ImpactAnalysisController())->getAnalysis();
    exit;
}
if ($path === '/api/smart-delete' && $method === 'POST') {
    (new \App\Controllers\ImpactAnalysisController())->executeDelete();
    exit;
}

// ====== ROUTES: GESTION DES PERMISSIONS (RBAC) ======
if (strpos($path, '/pilotage/rbac') === 0 || strpos($path, '/api/rbac') === 0) {
    if (!Session::isLogged()) {
        header('Location: /login');
        exit;
    }
    $c = new RbacController();

    if ($path === '/pilotage/rbac') {
        $c->index();
    } elseif ($path === '/api/rbac/permissions' && $method === 'GET') {
        $c->getPermissions();
    } elseif ($path === '/api/rbac/roles' && $method === 'GET') {
        $c->getRoles();
    } elseif ($path === '/api/rbac/roles/permissions' && $method === 'GET') {
        $c->getRolePermissions();
    } elseif ($path === '/api/rbac/roles/permissions' && $method === 'POST') {
        $c->saveRolePermissions();
    } elseif ($path === '/api/rbac/roles/copy' && $method === 'POST') {
        $c->copyRolePermissions();
    } elseif ($path === '/api/rbac/roles/compare' && $method === 'GET') {
        $c->compareRoles();
    } elseif ($path === '/api/rbac/roles/reset' && $method === 'POST') {
        $c->resetRolePermissions();
    } elseif ($path === '/api/rbac/users' && $method === 'GET') {
        $c->searchUsers();
    } elseif ($path === '/api/rbac/users/permissions' && $method === 'GET') {
        $c->getUserPermissions();
    } elseif ($path === '/api/rbac/users/permissions' && $method === 'POST') {
        $c->saveUserPermissions();
    } elseif ($path === '/api/rbac/scan' && $method === 'POST') {
        $c->runScan();
    } elseif ($path === '/api/rbac/audit' && $method === 'GET') {
        $c->getAuditLogs();
    } elseif ($path === '/api/rbac/backups' && $method === 'GET') {
        $c->getBackups();
    } elseif ($path === '/api/rbac/backups/create' && $method === 'POST') {
        $c->createBackup();
    } elseif ($path === '/api/rbac/backups/restore' && $method === 'POST') {
        $c->restoreBackup();
    }
    exit;
}

// Route principale (Tableau de Bord ou Landing Page)
if ($path === '/' || $path === '/index.php') {
    if (Session::isLogged()) {
        $c = new DashboardController();
        $c->index();
    } else {
        $c = new LandingController();
        $c->index();
    }
}

// ====== ROUTES: EMPLOIS DU TEMPS ======
elseif (strpos($path, '/timetables') === 0) {
    if (!Session::isLogged()) {
        header('Location: /login');
        exit;
    }
    $c = new TimetableController();

    if ($path === '/timetables') {
        $c->index();
    } elseif ($path === '/timetables/slots') {
        $c->slots();
    } elseif ($path === '/timetables/slots/store' && $method === 'POST') {
        $c->storeSlot();
    } elseif ($path === '/timetables/slots/update' && $method === 'POST') {
        $c->updateSlot();
    } elseif ($path === '/timetables/slots/delete' && $method === 'POST') {
        $c->deleteSlot();
    } elseif ($path === '/timetables/rooms') {
        $c->rooms();
    } elseif ($path === '/timetables/rooms/store' && $method === 'POST') {
        $c->storeRoom();
    } elseif ($path === '/timetables/rooms/update' && $method === 'POST') {
        $c->updateRoom();
    } elseif ($path === '/timetables/rooms/delete' && $method === 'POST') {
        $c->deleteRoom();
    } elseif ($path === '/timetables/weeks') {
        $c->weeks();
    } elseif ($path === '/timetables/weeks/store' && $method === 'POST') {
        $c->storeWeek();
    } elseif ($path === '/timetables/weeks/update' && $method === 'POST') {
        $c->updateWeek();
    } elseif ($path === '/timetables/weeks/delete' && $method === 'POST') {
        $c->deleteWeek();
    } elseif ($path === '/timetables/wizard') {
        $c->wizard();
    } elseif (($path === '/timetables/wizard/generate' || $path === '/timetables/create') && $method === 'POST') {
        $c->createTimetable();
    } elseif ($path === '/timetables/grid') {
        $c->grid();
    } elseif ($path === '/timetables/unlock' && $method === 'POST') {
        $c->unlock();
    } elseif ($path === '/timetables/publish' && $method === 'POST') {
        $c->publish();
    } elseif ($path === '/timetables/unpublish' && $method === 'POST') {
        $c->unpublish();
    } elseif ($path === '/timetables/delete' && $method === 'POST') {
        $c->deleteTimetable();

    } elseif ($path === '/timetables/print') {
        $c->printIndex();
    } elseif ($path === '/timetables/pdf') {
        $c->exportPdf();
    } elseif ($path === '/timetables/api/wizard-data' || strpos($path, '/timetables/api/wizard/') === 0) {
        $c->wizardStepData();
    } elseif (($path === '/timetables/api/save-entry' || $path === '/timetables/api/grid/save') && $method === 'POST') {
        $c->saveGridEntry();
    } elseif (($path === '/timetables/api/delete-entry' || $path === '/timetables/api/grid/delete') && $method === 'POST') {
        $c->deleteGridEntry();
    } elseif ($path === '/timetables/api/validate-conflict') {
        $c->apiValidateConflict();
    } elseif ($path === '/timetables/api/class-subjects') {
        $c->apiGetClassSubjects();
    } elseif ($path === '/timetables/api/subject-teachers') {
        $c->apiGetSubjectTeachers();
    } elseif ($path === '/timetables/api/quick-create-teacher' && $method === 'POST') {
        $c->apiQuickCreateTeacher();
    } elseif ($path === '/timetables/api/bulk-validate' && $method === 'POST') {
        $c->apiBulkValidate();
    } elseif ($path === '/timetables/api/bulk-save' && $method === 'POST') {
        $c->apiBulkSave();
    }


}

// ====== ROUTES: UTILISATEURS ======
elseif (strpos($path, '/users') === 0) {
    if (!Session::isLogged()) {
        header('Location: /login');
        exit;
    }
    $c = new UserController();
    if ($path === '/users')
        $c->index();
    elseif ($path === '/users/export')
        $c->export();
    elseif ($path === '/users/create')
        $c->create();
    elseif ($path === '/users/store' && $method === 'POST')
        $c->store();
    elseif ($path === '/users/create-caissier')
        $c->createCaissier();
    elseif ($path === '/users/store-caissier' && $method === 'POST')
        $c->storeCaissier();
    elseif ($path === '/users/caissiers')
        $c->caissiers();
    elseif ($path === '/users/toggle-status')
        $c->toggleStatus($_GET['id'] ?? 0);
    elseif ($path === '/users/edit')
        $c->edit($_GET['id'] ?? 0);
    elseif ($path === '/users/update' && $method === 'POST')
        $c->update($_GET['id'] ?? 0);
    elseif ($path === '/users/delete')
        $c->delete($_GET['id'] ?? 0);
}

// ====== ROUTES: ETUDIANTS ======
elseif (strpos($path, '/students') === 0) {
    if (!Session::isLogged()) {
        header('Location: /login');
        exit;
    }

    $c = new StudentController();
    $role = Session::get('user_role');

    if ($path === '/students') {
        $c->index();
    } elseif ($path === '/students/non-inscrits') {
        $c->nonInscrits();
    } elseif ($path === '/students/create' && in_array($role, ['superadmin', 'admin', 'caissier', 'comptable'])) {
        $c->create();
    } elseif ($path === '/students/store' && $method === 'POST' && in_array($role, ['superadmin', 'admin', 'caissier', 'comptable'])) {
        $c->store();
    } elseif ($path === '/students/import' && in_array($role, ['superadmin', 'admin', 'caissier', 'comptable'])) {
        $c->import();
    } elseif ($path === '/students/download_template') {
        $c->downloadTemplate();
    } elseif ($path === '/students/upload' && $method === 'POST' && in_array($role, ['superadmin', 'admin', 'caissier', 'comptable'])) {
        $c->upload();
    } elseif ($path === '/students/edit' && in_array($role, ['superadmin', 'admin', 'caissier', 'comptable'])) {
        $c->edit($_GET['id'] ?? 0);
    } elseif ($path === '/students/update' && $method === 'POST' && in_array($role, ['superadmin', 'admin', 'caissier', 'comptable'])) {
        $c->update($_GET['id'] ?? 0);
    } elseif ($path === '/students/export') {
        $c->export();
    } elseif ($path === '/students/exportExcel') {
        $c->exportExcel();
    } elseif ($path === '/students/delete' && in_array($role, ['superadmin', 'admin', 'caissier', 'comptable'])) {
        $c->delete($_GET['id'] ?? 0);
    } elseif ($path === '/students/withdraw' && in_array($role, ['superadmin', 'admin', 'caissier', 'comptable'])) {
        $c->withdraw($_GET['id'] ?? 0);
    } elseif ($path === '/students/restore' && in_array($role, ['superadmin', 'admin', 'caissier', 'comptable'])) {
        $c->restore($_GET['id'] ?? 0);
    } else {
        header('Location: /students');
        exit;
    }
}

// ====== ROUTES: AUTRES PARAMETRES ======
// Vérifie si l'URL commence par "/cycles"
elseif (strpos($path, '/cycles') === 0) {
    if (!Session::isLogged() || (!in_array(Session::get('user_role'), ['superadmin', 'admin', 'direction_academique'], true) && !\App\Core\PermissionManager::hasPermission('manage_cycles'))) {
        header('Location: /');
        exit;
    }

    // Instancie le contrôleur des Cycles
    $c = new CycleController();

    // Aiguillage vers les méthodes du contrôleur selon le chemin de l'URL 
    if ($path === '/cycles')
        $c->index(); // Liste des cycles
    elseif ($path === '/cycles/create')
        $c->create(); // Formulaire de création
    elseif ($path === '/cycles/store' && $method === 'POST')
        $c->store(); // Traitement de l'ajout (POST)
    elseif ($path === '/cycles/edit')
        $c->edit($_GET['id'] ?? 0); // Formulaire de modification
    elseif ($path === '/cycles/update' && $method === 'POST')
        $c->update($_GET['id'] ?? 0); // Traitement de la modification (POST)
    elseif ($path === '/cycles/toggle')
        $c->toggleStatus($_GET['id'] ?? 0);
    elseif ($path === '/cycles/delete')
        $c->delete($_GET['id'] ?? 0); // Suppression
} elseif (strpos($path, '/sections') === 0) {
    if (!Session::isLogged() || (!in_array(Session::get('user_role'), ['superadmin', 'admin', 'direction_academique'], true) && !\App\Core\PermissionManager::hasPermission('manage_sections'))) {
        header('Location: /');
        exit;
    }
    $c = new SectionController();
    if ($path === '/sections')
        $c->index();
    elseif ($path === '/sections/create')
        $c->create();
    elseif ($path === '/sections/store' && $method === 'POST')
        $c->store();
    elseif ($path === '/sections/edit')
        $c->edit($_GET['id'] ?? 0);
    elseif ($path === '/sections/update' && $method === 'POST')
        $c->update($_GET['id'] ?? 0);
    elseif ($path === '/sections/toggle')
        $c->toggleStatus($_GET['id'] ?? 0);
    elseif ($path === '/sections/delete')
        $c->delete($_GET['id'] ?? 0);
} elseif (strpos($path, '/departments') === 0) {
    \App\Core\PermissionManager::requirePermission('manage_departments');
    $c = new DepartmentController();
    if ($path === '/departments')
        $c->index();
    elseif ($path === '/departments/create')
        $c->create();
    elseif ($path === '/departments/store' && $method === 'POST')
        $c->store();
    elseif ($path === '/departments/edit')
        $c->edit($_GET['id'] ?? 0);
    elseif ($path === '/departments/update' && $method === 'POST')
        $c->update($_GET['id'] ?? 0);
    elseif ($path === '/departments/toggle')
        $c->toggleStatus($_GET['id'] ?? 0);
    elseif ($path === '/departments/delete')
        $c->delete($_GET['id'] ?? 0);
} elseif (strpos($path, '/classes') === 0) {
    if (!Session::isLogged() || (!in_array(Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'direction_academique'], true) && !\App\Core\PermissionManager::hasPermission('view_classes'))) {
        header('Location: /');
        exit;
    }
    $c = new ClassController();
    if ($path === '/classes')
        $c->index();
    elseif ($path === '/classes/export')
        $c->export();
    elseif ($path === '/classes/import')
        $c->import();
    elseif ($path === '/classes/download_template')
        $c->downloadTemplate();
    elseif ($path === '/classes/upload' && $method === 'POST')
        $c->upload();
    elseif ($path === '/classes/create')
        $c->create();
    elseif ($path === '/classes/store' && $method === 'POST')
        $c->store();
    elseif ($path === '/classes/edit')
        $c->edit($_GET['id'] ?? 0);
    elseif ($path === '/classes/update' && $method === 'POST')
        $c->update($_GET['id'] ?? 0);
    elseif ($path === '/classes/delete')
        $c->delete($_GET['id'] ?? 0);
    elseif ($path === '/classes/manage-team')
        $c->manageTeam($_GET['id'] ?? 0);
    elseif ($path === '/classes/set-main-teacher' && $method === 'POST')
        $c->setMainTeacher($_GET['id'] ?? 0);
} elseif (strpos($path, '/sequences') === 0) {
    if (!Session::isLogged() || (!in_array(Session::get('user_role'), ['superadmin', 'admin', 'direction_academique'], true) && !\App\Core\PermissionManager::hasPermission('manage_sequences'))) {
        header('Location: /');
        exit;
    }
    $c = new SequenceController();
    if ($path === '/sequences')
        $c->index();
    elseif ($path === '/sequences/create')
        $c->create();
    elseif ($path === '/sequences/store' && $method === 'POST')
        $c->store();
    elseif ($path === '/sequences/edit')
        $c->edit($_GET['id'] ?? 0);
    elseif ($path === '/sequences/update' && $method === 'POST')
        $c->update($_GET['id'] ?? 0);
    elseif ($path === '/sequences/delete')
        $c->delete($_GET['id'] ?? 0);
    elseif ($path === '/sequences/toggle')
        $c->toggle($_GET['id'] ?? 0);
} elseif (strpos($path, '/teaching_types') === 0) {
    if (!Session::isLogged() || (!in_array(Session::get('user_role'), ['superadmin', 'admin', 'direction_academique'], true) && !\App\Core\PermissionManager::hasPermission('manage_teaching_types'))) {
        header('Location: /');
        exit;
    }
    $c = new TeachingTypeController();
    if ($path === '/teaching_types' || $path === '/teaching_types/' || $path === '/teaching_types/index.php')
        $c->index();
    elseif ($path === '/teaching_types/create')
        $c->create();
    elseif ($path === '/teaching_types/store' && $method === 'POST')
        $c->store();
    elseif ($path === '/teaching_types/edit')
        $c->edit($_GET['id'] ?? 0);
    elseif ($path === '/teaching_types/update' && $method === 'POST')
        $c->update($_GET['id'] ?? 0);
    elseif ($path === '/teaching_types/delete')
        $c->delete($_GET['id'] ?? 0);
    else {
        header('Location: /teaching_types');
        exit;
    }
} elseif (strpos($path, '/subject-groups') === 0) {
    if (!Session::isLogged() || (!in_array(Session::get('user_role'), ['superadmin', 'admin', 'direction_academique'], true) && !\App\Core\PermissionManager::hasPermission('manage_subjects'))) {
        header('Location: /');
        exit;
    }
    $c = new SubjectGroupController();
    if ($path === '/subject-groups' || $path === '/subject-groups/')
        $c->index();
    elseif ($path === '/subject-groups/store' && $method === 'POST')
        $c->store();
    elseif ($path === '/subject-groups/update' && $method === 'POST')
        $c->update($_GET['id'] ?? 0);
    elseif ($path === '/subject-groups/toggle')
        $c->toggle($_GET['id'] ?? 0);
} elseif (strpos($path, '/levels') === 0) {
    if (!Session::isLogged() || (!in_array(Session::get('user_role'), ['superadmin', 'admin', 'direction_academique'], true) && !\App\Core\PermissionManager::hasPermission('manage_classes_structure'))) {
        header('Location: /');
        exit;
    }
    $c = new LevelController();
    if ($path === '/levels' || $path === '/levels/' || $path === '/levels/index.php')
        $c->index();
    elseif ($path === '/levels/create')
        $c->create();
    elseif ($path === '/levels/store' && $method === 'POST')
        $c->store();
    elseif ($path === '/levels/edit')
        $c->edit($_GET['id'] ?? 0);
    elseif ($path === '/levels/update' && $method === 'POST')
        $c->update($_GET['id'] ?? 0);
    elseif ($path === '/levels/toggle')
        $c->toggleStatus($_GET['id'] ?? 0);
    elseif ($path === '/levels/delete')
        $c->delete($_GET['id'] ?? 0);
    else {
        header('Location: /levels');
        exit;
    }
}

// ====== ROUTES: CORPS ENSEIGNANT ======
elseif (strpos($path, '/teachers') === 0) {
    if (!Session::isLogged() || (!in_array(Session::get('user_role'), ['superadmin', 'admin', 'direction_academique'], true) && !\App\Core\PermissionManager::hasPermission('manage_teachers'))) {
        header('Location: /');
        exit;
    }
    $c = new TeacherController();
    if ($path === '/teachers')
        $c->index();
    elseif ($path === '/teachers/export')
        $c->export();
    elseif ($path === '/teachers/create')
        $c->create();
    elseif ($path === '/teachers/import')
        $c->import();
    elseif ($path === '/teachers/download_template')
        $c->downloadTemplate();
    elseif ($path === '/teachers/upload' && $method === 'POST')
        $c->upload();
    elseif ($path === '/teachers/edit')
        $c->edit((int) ($_GET['id'] ?? 0));
    elseif ($path === '/teachers/update' && $method === 'POST')
        $c->update((int) ($_GET['id'] ?? 0));
    elseif ($path === '/teachers/store' && $method === 'POST')
        $c->store();
    elseif ($path === '/teachers/delete')
        $c->delete($_GET['id'] ?? 0);
    elseif ($path === '/teachers/assign')
        $c->assign($_GET['id'] ?? 0);
    elseif ($path === '/teachers/direct_assign')
        $c->directAssign();
    elseif ($path === '/teachers/store_assignment' && $method === 'POST')
        $c->storeAssignment($_GET['id'] ?? 0);
    elseif ($path === '/teachers/remove_assignment' && $method === 'POST')
        $c->removeAssignment();
    elseif ($path === '/teachers/transfer_course' && $method === 'POST')
        $c->transferCourse();
    elseif ($path === '/teachers/toggle-teacher-names' && $method === 'POST')
        $c->toggleTeacherNames();
} elseif (strpos($path, '/bulletins') === 0) {
    if (!Session::isLogged()) {
        header('Location: /login');
        exit;
    }
    $c = new BulletinController();
    if ($path === '/bulletins')
        $c->index();
    elseif ($path === '/bulletins/classes-json')
        $c->getClassesByTeachingTypeJson();
    elseif ($path === '/bulletins/sequence')
        $c->sequence();
    elseif ($path === '/bulletins/sequence/class')
        $c->sequenceClass();
    elseif ($path === '/bulletins/trimestre')
        $c->trimestre();
    elseif ($path === '/bulletins/trimestre/class')
        $c->trimestreClass();
    elseif ($path === '/bulletins/annuel')
        $c->annuel();
    elseif ($path === '/bulletins/annuel/class')
        $c->annuelClass();
    elseif ($path === '/bulletins/discipline')
        $c->discipline();
    elseif ($path === '/bulletins/discipline/save' && $method === 'POST')
        $c->saveDiscipline();
} elseif (strpos($path, '/honors') === 0) {
    if (!Session::isLogged()) {
        header('Location: /login');
        exit;
    }
    $c = new HonorRollController();
    if ($path === '/honors')
        $c->index();
    elseif ($path === '/honors/trimestre')
        $c->trimestre();
    elseif ($path === '/honors/trimestre/bulk')
        $c->trimesterBulk();
    elseif ($path === '/honors/annuel')
        $c->annuel();
    elseif ($path === '/honors/annuel/bulk')
        $c->annuelBulk();
}

// ====== ROUTES: PROCÈS-VERBAUX ======
elseif (strpos($path, '/proces-verbal') === 0) {
    if (!Session::isLogged()) {
        header('Location: /login');
        exit;
    }
    $c = new ProcesVerbalController();
    if ($path === '/proces-verbal')
        $c->index();
    elseif ($path === '/proces-verbal/sequence')
        $c->sequence();
    elseif ($path === '/proces-verbal/trimestre')
        $c->trimestre();
    elseif ($path === '/proces-verbal/annuel')
        $c->annuel();
    elseif ($path === '/proces-verbal/evaluation')
        $c->evaluation();
}

// ====== ROUTES: RELEVÉS DE NOTES ======
elseif (strpos($path, '/transcripts') === 0) {
    if (!Session::isLogged()) {
        header('Location: /login');
        exit;
    }
    $c = new TranscriptController();
    if ($path === '/transcripts')
        $c->index();
    elseif ($path === '/transcripts/generate')
        $c->generate();
}


// ====== ROUTES: PROFIL UTILISATEUR ======
elseif (strpos($path, '/profile') === 0) {
    if (!Session::isLogged()) {
        header('Location: /login');
        exit;
    }
    $c = new ProfileController();
    if ($path === '/profile')
        $c->index();
    elseif ($path === '/profile/update' && $method === 'POST')
        $c->update();
}

// ====== ROUTES: MATIERES ======
elseif (strpos($path, '/subjects') === 0) {
    if (!Session::isLogged()) {
        header('Location: /login');
        exit;
    }
    $c = new SubjectController();
    if ($path === '/subjects')
        $c->index();
    elseif ($path === '/subjects/export')
        $c->export();
    elseif ($path === '/subjects/exportExcel' || $path === '/subjects/export-excel')
        $c->exportExcel();
    elseif ($path === '/subjects/create')
        $c->create();
    elseif ($path === '/subjects/import')
        $c->import();
    elseif ($path === '/subjects/download_template')
        $c->downloadTemplate();
    elseif ($path === '/subjects/upload' && $method === 'POST')
        $c->upload();
    elseif ($path === '/subjects/store' && $method === 'POST')
        $c->store();
    elseif ($path === '/subjects/edit')
        $c->edit($_GET['id'] ?? 0);
    elseif ($path === '/subjects/update' && $method === 'POST')
        $c->update($_GET['id'] ?? 0);
    elseif ($path === '/subjects/delete')
        $c->delete($_GET['id'] ?? 0);
    elseif ($path === '/subjects/toggleStatus')
        $c->toggleStatus($_GET['id'] ?? 0);
}

// ====== ROUTES: ANNEES ACADEMIQUES ======
elseif (strpos($path, '/academic_years') === 0) {
    if (!Session::isLogged() || !in_array(Session::get('user_role'), ['superadmin', 'it_manager'])) {
        header('Location: /');
        exit;
    }
    // On load le controlleur avec le namespace importé
    $c = new AcademicYearController();
    if ($path === '/academic_years')
        $c->index();
    elseif ($path === '/academic_years/create')
        $c->create();
    elseif ($path === '/academic_years/store' && $method === 'POST')
        $c->store();
    elseif ($path === '/academic_years/activate')
        $c->activate($_GET['id'] ?? 0);
    elseif ($path === '/academic_years/archive_wizard')
        $c->archiveWizard($_GET['id'] ?? 0);
    elseif ($path === '/academic_years/do_archive' && $method === 'POST')
        $c->doArchive();
    elseif ($path === '/academic_years/restore')
        $c->restore($_GET['file'] ?? '');
    elseif ($path === '/academic_years/edit')
        $c->edit($_GET['id'] ?? 0);
    elseif ($path === '/academic_years/update' && $method === 'POST')
        $c->update($_GET['id'] ?? 0);
    elseif ($path === '/academic_years/delete')
        $c->delete($_GET['id'] ?? 0);
    elseif ($path === '/academic_years/unarchive')
        $c->unarchive($_GET['id'] ?? 0);
    elseif ($path === '/academic_years/do_unarchive' && $method === 'POST')
        $c->doUnarchive();
}

// ====== ROUTES: GESTION FINANCIÈRE ======
elseif ($path === '/payments/verify') {
    // Rétrocompatibilité pour les anciens QR Codes
    $c = new \App\Controllers\PublicVerificationController();
    $c->verifyPublic();
}
elseif (strpos($path, '/verify-receipt') === 0) {
    // Nouvelle route publique de vérification
    $c = new \App\Controllers\PublicVerificationController();
    $c->verifyPublic();
}
elseif ($path === '/admin/run-migrations') {
    // Exécuter les migrations depuis le navigateur (alternative au SSH)
    if (!Security::validateSession() || !in_array(Session::get('user_role'), ['superadmin', 'admin'])) {
        header('Location: /');
        exit;
    }
    echo "<pre style='background:#1e293b; color:#10b981; padding:20px; font-family:monospace;'>";
    echo "=== DÉMARRAGE DU RUNNER DE MIGRATION ===\n\n";
    try {
        require __DIR__ . '/../scratch/MigrationRunner.php';
        echo "\n\n=== MIGRATIONS TERMINÉES AVEC SUCCÈS ===\n";
    } catch (\Exception $e) {
        echo "\n\nERREUR: " . $e->getMessage() . "\n";
    }
    echo "\n<a href='/' style='color:#3b82f6;'>Retour à l'accueil</a></pre>";
    exit;
}
elseif (strpos($path, '/admin/verifications') === 0) {
    if (!Session::isLogged() || !in_array(Session::get('user_role'), ['superadmin', 'admin', 'comptable'])) {
        header('Location: /');
        exit;
    }
    $c = new \App\Controllers\VerificationAdminController();
    if ($path === '/admin/verifications') {
        $c->index();
    }
}
elseif (strpos($path, '/payments') === 0) {
    if (!Session::isLogged() || !in_array(Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable'])) {
        header('Location: /');
        exit;
    }
    $c = new PaymentController();
    if ($path === '/payments')
        $c->index();
    elseif ($path === '/payments/student')
        $c->studentDetails($_GET['id'] ?? 0);
    elseif ($path === '/payments/store' && $method === 'POST')
        $c->store();
    elseif ($path === '/payments/delete')
        $c->delete($_GET['id'] ?? 0);
    elseif ($path === '/payments/receipt')
        $c->receipt($_GET['id'] ?? 0);
    elseif ($path === '/payments/full-history')
        $c->fullHistory();
}

elseif (strpos($path, '/discounts') === 0) {
    if (!Session::isLogged() || !in_array(Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable'])) {
        header('Location: /');
        exit;
    }
    $c = new DiscountController();
    if ($path === '/discounts')
        $c->index();
    elseif ($path === '/discounts/store' && $method === 'POST')
        $c->store();
    elseif ($path === '/discounts/toggle')
        $c->toggleStatus($_GET['id'] ?? 0);
    elseif ($path === '/discounts/delete')
        $c->delete($_GET['id'] ?? 0);
}

elseif (strpos($path, '/discount_types') === 0) {
    if (!Session::isLogged() || !in_array(Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable'])) {
        header('Location: /');
        exit;
    }
    $c = new DiscountTypeController();
    if ($path === '/discount_types')
        $c->index();
    elseif ($path === '/discount_types/store' && $method === 'POST')
        $c->store();
    elseif ($path === '/discount_types/toggle')
        $c->toggleStatus($_GET['id'] ?? 0);
    elseif ($path === '/discount_types/delete')
        $c->delete($_GET['id'] ?? 0);
}

elseif (strpos($path, '/school_fees') === 0) {
    if (!Session::isLogged() || !in_array(Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable'])) {
        header('Location: /');
        exit;
    }
    $c = new SchoolFeeController();
    if ($path === '/school_fees/grille')
        $c->grille();
    elseif ($path === '/school_fees/grille/print')
        $c->printGrille();
    elseif ($path === '/school_fees/grille/template')
        $c->templateGrille();
    elseif ($path === '/school_fees/grille/import' && $method === 'POST')
        $c->importGrille();
    elseif ($path === '/school_fees/tranches')
        $c->tranches();
    elseif ($path === '/school_fees/versements')
        $c->versements();
    elseif ($path === '/school_fees/versements/store' && $method === 'POST')
        $c->storeVersement();
    elseif ($path === '/school_fees/versements/delete')
        $c->deleteVersement();
    elseif ($path === '/school_fees/insolvables')
        $c->insolvables();
    elseif ($path === '/school_fees/insolvables/print')
        $c->printInsolvables();
    elseif ($path === '/school_fees/receipt')
        $c->receipt();
}

elseif (strpos($path, '/scholarships') === 0) {
    if (!Session::isLogged() || !in_array(Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable'])) {
        header('Location: /');
        exit;
    }
    $c = new ScholarshipController();
    if ($path === '/scholarships')
        $c->index();
    elseif ($path === '/scholarships/store' && $method === 'POST')
        $c->store();
    elseif ($path === '/scholarships/toggle')
        $c->toggleStatus($_GET['id'] ?? 0);
    elseif ($path === '/scholarships/delete')
        $c->delete($_GET['id'] ?? 0);
}

elseif (strpos($path, '/financial-history') === 0) {
    if (!Session::isLogged() || !in_array(Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable'])) {
        header('Location: /');
        exit;
    }
    $c = new FinancialHistoryController();
    if ($path === '/financial-history')
        $c->index();
    elseif ($path === '/financial-history/print')
        $c->print();
}

// ====== ROUTES: GESTION DES DÉPENSES ======
elseif (strpos($path, '/expenses') === 0) {
    if (!Session::isLogged() || !in_array(Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable'])) {
        header('Location: /');
        exit;
    }
    $c = new \App\Controllers\ExpenseController();
    if ($path === '/expenses')
        $c->index();
    elseif ($path === '/expenses/store' && $method === 'POST')
        $c->store();
    elseif ($path === '/expenses/update' && $method === 'POST')
        $c->update();
    elseif ($path === '/expenses/cancel' && $method === 'POST')
        $c->cancel();
    elseif ($path === '/expenses/print')
        $c->printReport();
    elseif ($path === '/expenses/categories')
        $c->categories();
    elseif ($path === '/expenses/categories/store' && $method === 'POST')
        $c->storeCategory();
    elseif ($path === '/expenses/categories/update' && $method === 'POST')
        $c->updateCategory();
    elseif ($path === '/expenses/categories/toggle')
        $c->toggleCategoryStatus();
    elseif ($path === '/expenses/audit')
        $c->auditLogs();
}

// ====== ROUTES: CENTRE DE PILOTAGE ======
elseif (strpos($path, '/pilotage') === 0) {
    if (!Session::isLogged() || (!in_array(Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable', 'direction_academique'], true) && !\App\Core\PermissionManager::hasPermission('view_pilotage'))) {
        header('Location: /');
        exit;
    }
    $c = new DashboardController();
    if ($path === '/pilotage/dashboard') {
        $c->executiveDashboard();
    } elseif ($path === '/pilotage/financial') {
        $c->financialCenter();
    } else {
        header('Location: /');
        exit;
    }
}

// ====== ROUTES: CONFIGURATIONS GLOBALES ======
elseif (strpos($path, '/settings') === 0) {
    if (!Session::isLogged() || (!in_array(Session::get('user_role'), ['superadmin', 'admin', 'direction_academique'], true) && !\App\Core\PermissionManager::hasPermission('manage_settings'))) {
        header('Location: /');
        exit;
    }
    $c = new SettingController();
    if ($path === '/settings')
        $c->index();
    elseif ($path === '/settings/store' && $method === 'POST')
        $c->store();
    elseif ($path === '/settings/run_backup' && $method === 'POST') {
        if (Session::get('user_role') !== 'superadmin') {
            header('Location: /');
            exit;
        }
        $c->runBackup();
    }
    elseif ($path === '/settings/reset')
        $c->reset();
}

// ====== ROUTES: NOTES ======
elseif (strpos($path, '/notes') === 0) {
    if (!Session::isLogged()) {
        header('Location: /login');
        exit;
    }
    $c = new GradeController();
    if ($path === '/notes')
        $c->index();
    elseif ($path === '/notes/export')
        $c->export();
    elseif ($path === '/notes/saisie')
        $c->saisie();
    elseif ($path === '/notes/store' && $method === 'POST')
        $c->store();
    elseif ($path === '/notes/import')
        $c->import();
    elseif ($path === '/notes/downloadTemplate')
        $c->downloadTemplate();
    elseif ($path === '/notes/upload' && $method === 'POST')
        $c->upload();
    elseif ($path === '/notes/history')
        $c->history();
}



// ====== ROUTES: DOCUMENTATION ======
elseif (strpos($path, '/documentation') === 0) {
    if (!Session::isLogged()) {
        header('Location: /login');
        exit;
    }
    $c = new DocumentationController();
    if ($path === '/documentation')
        $c->index();
    elseif ($path === '/documentation/download')
        $c->download();
}

// ====== ROUTES: AUTHENTIFICATION ======
elseif ($path === '/login') {
    $auth = new AuthController();
    if ($method === 'GET')
        $auth->loginView();
    elseif ($method === 'POST')
        $auth->loginPost();
} elseif ($path === '/logout') {
    $auth = new AuthController();
    $auth->logout();
} elseif ($path === '/register-teacher') {
    $auth = new AuthController();
    if ($method === 'GET')
        $auth->registerTeacherView();
    elseif ($method === 'POST')
        $auth->registerTeacherPost();
}


// ====== ROUTE: 404 ======
else {
    http_response_code(404);
    echo "<div style='text-align:center;font-family:sans-serif;margin-top:50px;'>";
    echo "<h1>Erreur 404 - Page Introuvable</h1>";
    echo "<a href='/' style='padding:10px 20px; background:#007BFF; color:white; text-decoration:none; border-radius:5px;'>Retour</a>";
    echo "</div>";
}
