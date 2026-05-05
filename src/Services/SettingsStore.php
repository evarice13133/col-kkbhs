<?php

namespace App\Services;

use PDO;

class SettingsStore
{
    private PDO $db;

    private const DEFAULTS = [
        'school_name' => 'Mon Etablissement',
        'school_republic' => 'Republique du Cameroun',
        'school_republic_en' => 'Republic of Cameroon',
        'school_ministry' => 'Ministere des Enseignements Secondaires',
        'school_ministry_en' => 'Ministry of Secondary Education',
        'school_slogan' => 'Discipline - Travail - Succes',
        'school_slogan_en' => 'Discipline - Work - Success',
        'school_motto' => 'Paix - Travail - Patrie',
        'school_motto_en' => 'Peace - Work - Fatherland',
        'school_logo' => '',
        'school_code' => 'CMR-COL',
        'school_city' => 'BWADIBO',
        'school_phone' => '',
        'school_po_box' => '',
        'school_fax' => '',
        'school_email' => '',
        'school_website' => '',
        'display_school_year' => '',
        'principal_name' => '',
        'principal_title' => 'Chef d\'etablissement',
        'principal_signature' => '',
        'school_stamp' => '',
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
        'matricule_format' => '{SCHOOL_CODE}-{CLASS}-MT{COUNTER}',
        'matricule_counter' => '1',
        'backup_enabled' => '1',
        'backup_push_enabled' => '1',
        'backup_storage_path' => 'storage/backups',
        'backup_git_worktree' => 'storage/backup-repository',
        'backup_retention_count' => '12',
        'backup_schedule_day' => 'Sunday',
        'backup_schedule_time' => '02:00',
        'backup_github_owner' => 'evarice13133',
        'backup_github_repository' => 'notesmaster-backups',
        'backup_github_branch' => 'main',
        'backup_github_auth' => 'ssh',
        'backup_git_user_name' => 'NotesMaster Backup Bot',
        'backup_git_user_email' => 'backup-bot@notesmaster.local',
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->ensureTable();
        $this->ensureDefaults(self::DEFAULTS);
    }

    public static function defaults(): array
    {
        return self::DEFAULTS;
    }

    public function ensureTable(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS settings (setting_key VARCHAR(80) PRIMARY KEY, setting_value TEXT)");
    }

    public function ensureDefaults(array $defaults): void
    {
        $stmt = $this->db->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");

        foreach ($defaults as $key => $value) {
            $stmt->execute([$key, (string) $value]);
        }
    }

    public function all(): array
    {
        $settings = self::DEFAULTS;
        $stmt = $this->db->query("SELECT setting_key, setting_value FROM settings");

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $settings[$row['setting_key']] = (string) $row['setting_value'];
        }

        return $settings;
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();

        if ($value === false) {
            return self::DEFAULTS[$key] ?? $default;
        }

        return (string) $value;
    }

    public function getInt(string $key, int $default = 0): int
    {
        return (int) ($this->get($key, (string) $default) ?? $default);
    }

    public function getBool(string $key, bool $default = false): bool
    {
        $value = strtolower(trim((string) ($this->get($key, $default ? '1' : '0') ?? '0')));

        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    public function set(string $key, ?string $value): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->execute([$key, (string) ($value ?? '')]);
    }

    public function setMany(array $values): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");

        foreach ($values as $key => $value) {
            $stmt->execute([$key, (string) ($value ?? '')]);
        }
    }
}
