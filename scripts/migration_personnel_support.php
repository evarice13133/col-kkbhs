<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    $staffColumns = [
        'is_staff' => "TINYINT(1) NOT NULL DEFAULT 0",
        'cni_number' => "VARCHAR(64) NULL",
        'job_title' => "VARCHAR(150) NULL",
        'base_salary' => "DECIMAL(12,2) NULL",
        'nui_number' => "VARCHAR(64) NULL",
    ];

    foreach ($staffColumns as $column => $definition) {
        $check = $db->prepare("
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'users'
              AND COLUMN_NAME = ?
        ");
        $check->execute([$column]);

        if ((int)$check->fetchColumn() === 0) {
            $db->exec("ALTER TABLE users ADD COLUMN {$column} {$definition}");
            echo "Added users.{$column}.\n";
        }
    }

    $uniqueIndexes = [
        'uq_users_cni_number' => 'cni_number',
        'uq_users_nui_number' => 'nui_number',
    ];

    foreach ($uniqueIndexes as $index => $column) {
        $check = $db->prepare("
            SELECT COUNT(*)
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'users'
              AND INDEX_NAME = ?
        ");
        $check->execute([$index]);

        if ((int)$check->fetchColumn() === 0) {
            $db->exec("ALTER TABLE users ADD UNIQUE INDEX {$index} ({$column})");
            echo "Added unique index {$index}.\n";
        }
    }

    $db->exec("UPDATE users SET is_staff = 1 WHERE role IN ('enseignant', 'personnel')");

    $db->exec("
        INSERT INTO roles (role_code, role_name, description)
        VALUES ('personnel', 'Personnel', 'Compte du personnel de l’établissement sans autorisations applicatives par défaut.')
        ON DUPLICATE KEY UPDATE
            role_name = VALUES(role_name),
            description = VALUES(description)
    ");
    $db->exec("
        DELETE rp
        FROM role_permissions rp
        JOIN roles r ON rp.role_id = r.id
        WHERE r.role_code = 'personnel'
    ");

    echo "Personnel role and user fields are ready.\n";
} catch (\Throwable $e) {
    echo "Personnel migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
