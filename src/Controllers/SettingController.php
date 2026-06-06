<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\LogoManager;
use App\Services\ActivityTracker;
use App\Services\BackupService;
use App\Services\SettingsStore;
use PDO;

class SettingController
{
    private $db;
    private SettingsStore $settingsStore;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        if (Session::get('user_role') !== 'superadmin') {
            header("Location: /");
            exit;
        }

        $this->settingsStore = new SettingsStore($this->db);
    }

    public function index()
    {
        $settings = $this->settingsStore->all();

        include __DIR__ . '/../Views/settings/index.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $allowed_keys = [
                'school_name',
                'school_code',
                'school_republic',
                'school_republic_en',
                'school_ministry',
                'school_ministry_en',
                'school_slogan',
                'school_slogan_en',
                'school_motto',
                'school_motto_en',
                'school_city',
                'school_phone',
                'school_po_box',
                'school_fax',
                'school_email',
                'school_website',
                'display_school_year',
                'principal_name',
                'principal_title',
                'theme_navbar_bg',
                'theme_navbar_hover',
                'theme_button_bg',
                'theme_button_text',
                'theme_glow_bg',
                'theme_glow_text',
                'theme_admin_hero_start',
                'theme_admin_hero_end',
                'theme_admin_hero_glow',
                'theme_admin_hero_card',
                'theme_teacher_hero_start',
                'theme_teacher_hero_end',
                'theme_teacher_hero_glow',
                'theme_teacher_hero_card',
                'theme_login_bg_start',
                'theme_login_bg_mid',
                'theme_login_bg_end',
                'theme_login_bubble',
                'theme_login_button',
                'theme_login_showcase_start',
                'theme_login_showcase_end',
                'theme_login_showcase_glow',
                'theme_login_panel_bg',
                'theme_login_panel_badge_bg',
                'theme_login_panel_badge_text',
                'allow_teacher_registration',
                'matricule_format',
                'matricule_counter',
                'backup_enabled',
                'backup_push_enabled',
                'backup_storage_path',
                'backup_git_worktree',
                'backup_retention_count',
                'backup_schedule_day',
                'backup_schedule_time',
                'honor_roll_default_threshold',
                'backup_github_owner',
                'backup_github_repository',
                'backup_github_branch',
                'backup_github_auth',
                'backup_git_user_name',
                'backup_git_user_email',
                'bulletin_printing_enabled',
            ];

            $updates = [];
            foreach ($allowed_keys as $key) {
                if (isset($_POST[$key])) {
                    $updates[$key] = trim((string) $_POST[$key]);
                }
            }

            // Gérer explicitement les checkboxes (elles ne sont pas envoyées si décochées)
            $checkbox_keys = ['bulletin_printing_enabled', 'backup_enabled', 'backup_push_enabled'];
            foreach ($checkbox_keys as $key) {
                if (in_array($key, $allowed_keys)) {
                    $updates[$key] = isset($_POST[$key]) ? '1' : '0';
                }
            }

            $this->settingsStore->setMany($updates);
            $this->handleImageUpload('school_logo');
            $this->handleImageUpload('principal_signature');
            $this->handleImageUpload('school_stamp');
            (new ActivityTracker($this->db))->recordSettingsUpdate();

            Session::set('success_msg', \__('settings_saved_success'));
            header("Location: /settings");
            exit;
        }
    }

    public function reset()
    {
        // On définit uniquement les thèmes par défaut pour ne pas toucher aux entêtes (Logo, Nom, etc.)
        $defaults = [
            'theme_navbar_bg' => '#0a1726',
            'theme_navbar_hover' => '#ffffff14',
            'theme_button_bg' => '#1f5fbf',
            'theme_button_text' => '#ffffff',
            'theme_glow_bg' => '#eef4fb',
            'theme_glow_text' => '#1c4169',
            'theme_admin_hero_start' => '#16324f',
            'theme_admin_hero_end' => '#2f6fed',
            'theme_admin_hero_glow' => '#f4b942',
            'theme_admin_hero_card' => '#5d7894',
            'theme_teacher_hero_start' => '#16324f',
            'theme_teacher_hero_end' => '#2f6fed',
            'theme_teacher_hero_glow' => '#f4b942',
            'theme_teacher_hero_card' => '#5d7894',
            'theme_login_bg_start' => '#0a1726',
            'theme_login_bg_mid' => '#16324f',
            'theme_login_bg_end' => '#2f6fed',
            'theme_login_bubble' => '#f4b942',
            'theme_login_button' => '#1f5fbf',
            'theme_login_showcase_start' => '#102033',
            'theme_login_showcase_end' => '#143961',
            'theme_login_showcase_glow' => '#f4b942',
            'theme_login_panel_bg' => '#ffffff',
            'theme_login_panel_badge_bg' => '#e8f0ff',
            'theme_login_panel_badge_text' => '#1f5fbf',
        ];

        foreach ($defaults as $key => $value) {
            $this->settingsStore->set($key, $value);
        }

        Session::set('success_msg', \__('theme_reset_success'));
        header("Location: /settings");
        exit;
    }

    public function runBackup()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /settings");
            exit;
        }

        $backupService = new BackupService($this->db, $this->settingsStore);
        $result = $backupService->runAutomatedBackup([
            'trigger' => 'manual_settings',
            'job_name' => 'weekly_database_backup',
            'push' => $this->settingsStore->getBool('backup_push_enabled', true),
        ]);

        Session::remove('success_msg');
        Session::remove('warning_msg');
        Session::remove('error_msg');

        if ($result['status'] === 'success') {
            Session::set('success_msg', \__('backup_run_success'));
        } elseif ($result['status'] === 'warning') {
            Session::set('warning_msg', \__('backup_run_warning'));
        } else {
            Session::set('error_msg', \__('backup_run_failed', ['message' => $result['message']]));
        }

        header("Location: /settings#tab-automation");
        exit;
    }

    private function handleImageUpload($fieldName)
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileTmp = $_FILES[$fieldName]['tmp_name'];
        $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.\-_]/", "", basename($_FILES[$fieldName]['name']));
        $destPath = $uploadDir . $fileName;
        $fileType = mime_content_type($fileTmp);

        if ($fileType && strpos($fileType, 'image/') === 0 && move_uploaded_file($fileTmp, $destPath)) {
            $webPath = '/public/uploads/' . $fileName;
            
            // Utiliser le LogoManager pour le logo
            if ($fieldName === 'school_logo') {
                $logoManager = LogoManager::getInstance($this->db);
                $logoManager->updateLogo($webPath);
            } else {
                // Pour les autres images (signature, tampon)
                $this->settingsStore->set($fieldName, $webPath);
            }
        }
    }
}
