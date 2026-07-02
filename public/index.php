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
if (!in_array($path, ['/', '/login', '/logout', '/register-teacher', '/sitemap.xml', '/contact/send', '/payments/verify']) && !Security::validateSession()) {
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

// Route API Assistant IA
if ($path === '/api/ai-assistant' && $method === 'POST') {
    $c = new AIAssistantController();
    $c->handleRequest();
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
    // Sécurité : Vérifie si l'utilisateur est connecté et possède le rôle superadmin uniquement
    if (!Session::isLogged() || Session::get('user_role') !== 'superadmin') {
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
    elseif ($path === '/cycles/delete')
        $c->delete($_GET['id'] ?? 0); // Suppression
} elseif (strpos($path, '/sections') === 0) {
    if (!Session::isLogged() || Session::get('user_role') !== 'superadmin') {
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
    elseif ($path === '/sections/delete')
        $c->delete($_GET['id'] ?? 0);
} elseif (strpos($path, '/departments') === 0) {
    if (!Session::isLogged() || !in_array(Session::get('user_role'), ['superadmin', 'admin'])) {
        header('Location: /');
        exit;
    }
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
    if (!Session::isLogged() || !in_array(Session::get('user_role'), ['superadmin', 'admin', 'caissier'])) {
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
    if (!Session::isLogged() || !in_array(Session::get('user_role'), ['superadmin', 'admin'])) {
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
    if (!Session::isLogged() || !in_array(Session::get('user_role'), ['superadmin', 'admin'])) {
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
}

// ====== ROUTES: CORPS ENSEIGNANT ======
elseif (strpos($path, '/teachers') === 0) {
    if (!Session::isLogged() || !in_array(Session::get('user_role'), ['superadmin', 'admin'])) {
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
        $c->getClassesBySectionJson();
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
    if (!Session::isLogged() || !in_array(Session::get('user_role'), ['superadmin', 'admin'])) {
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
    $c = new PaymentController();
    $c->verify();
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

// ====== ROUTES: CENTRE DE PILOTAGE ======
elseif (strpos($path, '/pilotage') === 0) {
    if (!Session::isLogged() || !in_array(Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable'])) {
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
    if (!Session::isLogged() || Session::get('user_role') !== 'superadmin') {
        header('Location: /');
        exit;
    }
    $c = new SettingController();
    if ($path === '/settings')
        $c->index();
    elseif ($path === '/settings/store' && $method === 'POST')
        $c->store();
    elseif ($path === '/settings/run_backup' && $method === 'POST')
        $c->runBackup();
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
