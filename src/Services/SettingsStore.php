<?php

namespace App\Services;

use PDO;

class SettingsStore
{
    private PDO $db;
    private ?int $teachingTypeId = null;

    private const SCOPED_KEYS = [
        'school_name', 'school_code', 'school_republic', 'school_republic_en',
        'school_ministry', 'school_ministry_en', 'school_slogan', 'school_slogan_en',
        'school_motto', 'school_motto_en', 'school_logo', 'tutelage_logo', 'creation_decree', 'school_city',
        'school_phone', 'school_po_box', 'school_fax', 'school_email', 'school_website',
        'display_school_year', 'principal_name', 'principal_title', 'principal_signature',
        'school_stamp', 'honor_roll_default_threshold', 'bulletin_printing_enabled',
        'registration_fee_policy', 'payment_methods'
    ];

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
        'tutelage_logo' => '',
        'creation_decree' => '',
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
        'honor_roll_default_threshold' => '12',
        'bulletin_printing_enabled' => '1',
    ];

    public function __construct(PDO $db, ?int $teachingTypeId = null)
    {
        $this->db = $db;
        $this->ensureTable();
        $this->setTeachingTypeId($teachingTypeId);
        $this->ensureDefaults(self::DEFAULTS);
    }

    public function setTeachingTypeId(?int $teachingTypeId): self
    {
        if ($teachingTypeId !== null && $teachingTypeId > 0) {
            $this->teachingTypeId = $teachingTypeId;
        } else {
            $this->teachingTypeId = $this->getDefaultTeachingTypeId();
        }
        return $this;
    }

    public function getDbConnection(): PDO
    {
        return $this->db;
    }

    public function getTeachingTypeId(): int
    {
        if ($this->teachingTypeId === null || $this->teachingTypeId <= 0) {
            $this->teachingTypeId = $this->getDefaultTeachingTypeId();
        }
        return $this->teachingTypeId;
    }

    public function getDefaultTeachingTypeId(): int
    {
        try {
            $stmt = $this->db->query("SELECT id FROM teaching_types WHERE code = 'SEC00' LIMIT 1");
            $id = $stmt->fetchColumn();
            if ($id) {
                return (int) $id;
            }
            $id = $this->db->query("SELECT id FROM teaching_types WHERE actif = 1 ORDER BY position ASC, id ASC LIMIT 1")->fetchColumn();
            return $id ? (int) $id : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public static function defaults(): array
    {
        return self::DEFAULTS;
    }

    public function ensureTable(): void
    {
        // Table déjà préparée par la migration si la colonne teaching_type_id existe
        $colCheck = $this->db->query("SHOW COLUMNS FROM settings LIKE 'teaching_type_id'")->fetch();
        if (!$colCheck) {
            $this->db->exec("ALTER TABLE settings ADD COLUMN teaching_type_id INT NOT NULL DEFAULT 0");
        }
    }

    public function ensureDefaults(array $defaults): void
    {
        $defaultTtId = $this->getDefaultTeachingTypeId();
        $stmt = $this->db->prepare("INSERT IGNORE INTO settings (setting_key, setting_value, teaching_type_id) VALUES (?, ?, ?)");

        foreach ($defaults as $key => $value) {
            $ttId = in_array($key, self::SCOPED_KEYS, true) ? $defaultTtId : 0;
            $stmt->execute([$key, (string) $value, $ttId]);
        }
    }

    public function all(?int $teachingTypeId = null): array
    {
        $settings = self::DEFAULTS;
        $ttId = $teachingTypeId ?? $this->teachingTypeId ?? $this->getDefaultTeachingTypeId();
        $defaultTtId = $this->getDefaultTeachingTypeId();

        // 1. Paramètres globaux (teaching_type_id = 0)
        $stmt0 = $this->db->query("SELECT setting_key, setting_value FROM settings WHERE teaching_type_id = 0");
        foreach ($stmt0->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $settings[$row['setting_key']] = (string) $row['setting_value'];
        }

        // 2. Paramètres par défaut SEC00
        if ($defaultTtId > 0) {
            $stmtDef = $this->db->prepare("SELECT setting_key, setting_value FROM settings WHERE teaching_type_id = ?");
            $stmtDef->execute([$defaultTtId]);
            foreach ($stmtDef->fetchAll(PDO::FETCH_ASSOC) as $row) {
                if (in_array($row['setting_key'], self::SCOPED_KEYS, true)) {
                    $settings[$row['setting_key']] = (string) $row['setting_value'];
                }
            }
        }

        // 3. Paramètres spécifiques au type demandé (si différent de SEC00)
        if ($ttId > 0 && $ttId !== $defaultTtId) {
            $stmtCur = $this->db->prepare("SELECT setting_key, setting_value FROM settings WHERE teaching_type_id = ?");
            $stmtCur->execute([$ttId]);
            foreach ($stmtCur->fetchAll(PDO::FETCH_ASSOC) as $row) {
                if (in_array($row['setting_key'], self::SCOPED_KEYS, true)) {
                    $settings[$row['setting_key']] = (string) $row['setting_value'];
                }
            }
        }

        return $settings;
    }

    public function get(string $key, ?string $default = null, ?int $teachingTypeId = null): ?string
    {
        $ttId = $teachingTypeId ?? $this->teachingTypeId ?? $this->getDefaultTeachingTypeId();
        if (!in_array($key, self::SCOPED_KEYS, true)) {
            $ttId = 0;
        }

        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = ? AND teaching_type_id = ? LIMIT 1");
        $stmt->execute([$key, $ttId]);
        $value = $stmt->fetchColumn();

        if ($value !== false && $value !== null) {
            return (string) $value;
        }

        // Fallback vers SEC00 si ce n'était pas SEC00
        $defaultTtId = $this->getDefaultTeachingTypeId();
        if ($ttId !== $defaultTtId && in_array($key, self::SCOPED_KEYS, true)) {
            $stmt->execute([$key, $defaultTtId]);
            $value = $stmt->fetchColumn();
            if ($value !== false && $value !== null) {
                return (string) $value;
            }
        }

        // Fallback vers teaching_type_id = 0
        if ($ttId !== 0) {
            $stmt->execute([$key, 0]);
            $value = $stmt->fetchColumn();
            if ($value !== false && $value !== null) {
                return (string) $value;
            }
        }

        return self::DEFAULTS[$key] ?? $default;
    }

    public function getInt(string $key, int $default = 0, ?int $teachingTypeId = null): int
    {
        return (int) ($this->get($key, (string) $default, $teachingTypeId) ?? $default);
    }

    public function getBool(string $key, bool $default = false, ?int $teachingTypeId = null): bool
    {
        $value = strtolower(trim((string) ($this->get($key, $default ? '1' : '0', $teachingTypeId) ?? '0')));

        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    public function set(string $key, ?string $value, ?int $teachingTypeId = null): void
    {
        $ttId = $teachingTypeId ?? $this->teachingTypeId ?? $this->getDefaultTeachingTypeId();
        if (!in_array($key, self::SCOPED_KEYS, true)) {
            $ttId = 0;
        }

        $stmt = $this->db->prepare("
            INSERT INTO settings (setting_key, setting_value, teaching_type_id)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->execute([$key, (string) ($value ?? ''), $ttId]);
    }

    public function setMany(array $values, ?int $teachingTypeId = null): void
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $teachingTypeId);
        }
    }
}
