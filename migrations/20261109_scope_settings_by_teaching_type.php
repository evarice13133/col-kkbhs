<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Services\SettingsStore;

$db = Database::getInstance()->getConnection();

try {
    $column = $db->query("SHOW COLUMNS FROM settings LIKE 'teaching_type_id'")->fetch(PDO::FETCH_ASSOC);
    if (!$column) {
        $db->exec('ALTER TABLE settings ADD COLUMN teaching_type_id INT NOT NULL DEFAULT 0');
    }

    $primaryKeyColumns = $db->query("
        SELECT COLUMN_NAME
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'settings'
          AND INDEX_NAME = 'PRIMARY'
        ORDER BY SEQ_IN_INDEX
    ")->fetchAll(PDO::FETCH_COLUMN);

    if ($primaryKeyColumns !== ['setting_key', 'teaching_type_id']) {
        $dropPrimaryKey = $primaryKeyColumns !== [];
        $db->exec(
            ($dropPrimaryKey ? 'ALTER TABLE settings DROP PRIMARY KEY, ' : 'ALTER TABLE settings ')
            . 'ADD PRIMARY KEY (setting_key, teaching_type_id)'
        );
    }

    $teachingTypes = $db->query(
        'SELECT id, code, actif, position FROM teaching_types WHERE id > 0 ORDER BY position, id'
    )->fetchAll(PDO::FETCH_ASSOC);
    $teachingTypeIds = array_map(static fn(array $type): int => (int) $type['id'], $teachingTypes);
    if ($teachingTypeIds === []) {
        throw new \RuntimeException('Aucun type d’enseignement valide n’est disponible pour initialiser les paramètres.');
    }

    $defaultTeachingTypeId = 0;
    foreach ($teachingTypes as $type) {
        if ((string) $type['code'] === 'SEC00' && (int) $type['actif'] === 1) {
            $defaultTeachingTypeId = (int) $type['id'];
            break;
        }
    }
    if ($defaultTeachingTypeId < 1) {
        foreach ($teachingTypes as $type) {
            if ((int) $type['actif'] === 1) {
                $defaultTeachingTypeId = (int) $type['id'];
                break;
            }
        }
    }
    if ($defaultTeachingTypeId < 1) {
        throw new \RuntimeException('Aucun type d’enseignement par défaut valide n’est disponible.');
    }

    $legacyRows = $db->query("
        SELECT setting_key, setting_value
        FROM settings
        WHERE teaching_type_id = 0
    ")->fetchAll(PDO::FETCH_ASSOC);

    $insertSetting = $db->prepare("
        INSERT IGNORE INTO settings (setting_key, setting_value, teaching_type_id)
        VALUES (?, ?, ?)
    ");
    $migratedLegacyKeys = [];
    $dynamicTypeLookups = [
        'class' => ['classes', 'teaching_type_id'],
        'cycle' => ['cycles', 'teaching_type_id'],
        'section' => ['sections', 'teaching_type_id'],
    ];

    foreach ($legacyRows as $row) {
        $key = (string) $row['setting_key'];
        if (!SettingsStore::isScopedKey($key)) {
            continue;
        }

        if (preg_match('/^honor_roll_threshold_(class|cycle|section)_(\d+)$/', $key, $matches) === 1) {
            [$table, $columnName] = $dynamicTypeLookups[$matches[1]];
            $columnExists = $db->query("SHOW COLUMNS FROM `$table` LIKE '$columnName'")->fetchColumn();
            if ($columnExists) {
                $ownerTypeStmt = $db->prepare("SELECT `$columnName` FROM `$table` WHERE id = ?");
                $ownerTypeStmt->execute([(int) $matches[2]]);
                $ownerTypeId = (int) $ownerTypeStmt->fetchColumn();
                if ($ownerTypeId > 0) {
                    $insertSetting->execute([$key, (string) $row['setting_value'], $ownerTypeId]);
                } else {
                    foreach ($teachingTypeIds as $teachingTypeId) {
                        $insertSetting->execute([$key, (string) $row['setting_value'], $teachingTypeId]);
                    }
                }
            } else {
                foreach ($teachingTypeIds as $teachingTypeId) {
                    $insertSetting->execute([$key, (string) $row['setting_value'], $teachingTypeId]);
                }
            }
            $migratedLegacyKeys[] = $key;
            continue;
        }

        foreach ($teachingTypeIds as $teachingTypeId) {
            $insertSetting->execute([$key, (string) $row['setting_value'], $teachingTypeId]);
        }
        $migratedLegacyKeys[] = $key;
    }

    foreach ($teachingTypeIds as $teachingTypeId) {
        foreach (SettingsStore::defaults() as $key => $value) {
            if (SettingsStore::isScopedKey((string) $key)) {
                $insertSetting->execute([(string) $key, (string) $value, $teachingTypeId]);
            }
        }
    }

    if ($migratedLegacyKeys !== []) {
        $deleteSetting = $db->prepare('DELETE FROM settings WHERE setting_key = ? AND teaching_type_id = 0');
        foreach (array_unique($migratedLegacyKeys) as $key) {
            $deleteSetting->execute([$key]);
        }
    }

    echo "Settings now have an independent row per teaching type.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Settings scope migration failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
