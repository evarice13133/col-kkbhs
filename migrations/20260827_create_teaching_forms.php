<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

$exists = $db->query("SHOW TABLES LIKE 'teaching_forms'")->fetchColumn();
if (!$exists) {
    $db->exec("
        CREATE TABLE teaching_forms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(150) NOT NULL,
            code VARCHAR(50) NOT NULL,
            teaching_type_id INT NOT NULL,
            status TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_teaching_forms_code_type (code, teaching_type_id),
            KEY idx_teaching_forms_type (teaching_type_id),
            CONSTRAINT fk_teaching_forms_teaching_type FOREIGN KEY (teaching_type_id) REFERENCES teaching_types(id) ON DELETE RESTRICT ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
}

foreach (['roles', 'permissions'] as $table) {
    $autoIncrement = $db->query("SHOW COLUMNS FROM `$table` WHERE Field = 'id' AND Extra LIKE '%auto_increment%' ")->fetchColumn();
    if (!$autoIncrement) {
        $db->exec("ALTER TABLE `$table` MODIFY COLUMN id INT NOT NULL AUTO_INCREMENT");
    }
}

$db->exec("DELETE FROM role_permissions WHERE permission_id = 0");
$db->exec("DELETE FROM permissions WHERE id = 0");

$deptColumn = $db->query("SHOW COLUMNS FROM departments LIKE 'teaching_form_id'")->fetchColumn();
if (!$deptColumn) {
    $db->exec("ALTER TABLE departments ADD COLUMN teaching_form_id INT NULL AFTER teaching_type_id");
}

$checkFk = $db->query("SHOW CREATE TABLE departments")->fetchColumn(1);
if (stripos((string) $checkFk, 'fk_departments_teaching_form') === false) {
    try {
        $db->exec("ALTER TABLE departments ADD CONSTRAINT fk_departments_teaching_form FOREIGN KEY (teaching_form_id) REFERENCES teaching_forms(id) ON DELETE SET NULL ON UPDATE CASCADE");
    } catch (PDOException $e) {
        echo "Warning: FK teaching_form_id already exists or could not be added: " . $e->getMessage() . "\n";
    }
}

$permStmt = $db->prepare("SELECT id FROM permissions WHERE perm_code = 'manage_teaching_forms' ORDER BY id LIMIT 1");
$permStmt->execute();
$permId = $permStmt->fetchColumn();

if ($permId === false || (int) $permId === 0) {
    $db->exec("DELETE FROM role_permissions WHERE permission_id IN (SELECT id FROM permissions WHERE perm_code = 'manage_teaching_forms' AND id = 0)");
    $db->exec("DELETE FROM permissions WHERE perm_code = 'manage_teaching_forms' AND id = 0");
    $permId = null;
}

if (!$permId) {
    $db->exec("INSERT INTO permissions (perm_code, perm_name, module, submodule, action, description, criticality, status, is_system) VALUES ('manage_teaching_forms', 'Gérer les formes d\'enseignement', 'pedagogy', 'structure', 'manage', 'Créer et modifier les formes d\'enseignement.', 'medium', 'active', 1) ON DUPLICATE KEY UPDATE perm_name = VALUES(perm_name), description = VALUES(description)");
}

$permIdStmt = $db->prepare("SELECT id FROM permissions WHERE perm_code = 'manage_teaching_forms' ORDER BY id LIMIT 1");
$permIdStmt->execute();
$finalPermId = $permIdStmt->fetchColumn();

if ($finalPermId !== false && (int) $finalPermId > 0) {
    foreach (['admin', 'superadmin', 'direction_academique', 'it_manager'] as $roleCode) {
        $roleIdStmt = $db->prepare("SELECT id FROM roles WHERE role_code = ? LIMIT 1");
        $roleIdStmt->execute([$roleCode]);
        $roleId = $roleIdStmt->fetchColumn();
        if (!$roleId) {
            continue;
        }

        $db->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)")
          ->execute([(int) $roleId, (int) $finalPermId]);
    }
}

echo "Migration teaching_forms ready.\n";
exit(0);
