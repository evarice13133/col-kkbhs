<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

$column = $db->query("SHOW COLUMNS FROM classes LIKE 'teaching_form_id'")->fetchColumn();
if (!$column) {
    $db->exec("ALTER TABLE classes ADD COLUMN teaching_form_id INT NULL AFTER teaching_type_id");
}

$index = $db->query("SHOW INDEX FROM classes WHERE Key_name = 'idx_classes_teaching_form'")->fetchColumn();
if (!$index) {
    $db->exec("ALTER TABLE classes ADD INDEX idx_classes_teaching_form (teaching_form_id)");
}

$constraint = $db->query("SELECT CONSTRAINT_NAME
                          FROM information_schema.TABLE_CONSTRAINTS
                          WHERE CONSTRAINT_SCHEMA = DATABASE()
                            AND TABLE_NAME = 'classes'
                            AND CONSTRAINT_NAME = 'fk_classes_teaching_form'
                            AND CONSTRAINT_TYPE = 'FOREIGN KEY'")->fetchColumn();
if (!$constraint) {
    $db->exec("ALTER TABLE classes
               ADD CONSTRAINT fk_classes_teaching_form
               FOREIGN KEY (teaching_form_id) REFERENCES teaching_forms(id)
               ON DELETE SET NULL ON UPDATE CASCADE");
}

if (!$db->query("SHOW COLUMNS FROM classes LIKE 'teaching_form_id'")->fetchColumn()) {
    throw new RuntimeException('La colonne classes.teaching_form_id est introuvable après migration.');
}

echo "Colonne classes.teaching_form_id prête.\n";
