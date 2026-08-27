<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';
$db = \App\Core\Database::getInstance()->getConnection();
$col = $db->query("SHOW COLUMNS FROM subject_groups LIKE 'teaching_form_id'")->fetchColumn();
if ($col) {
    echo "Column teaching_form_id exists in subject_groups\n";
    $fk = $db->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subject_groups' AND CONSTRAINT_TYPE = 'FOREIGN KEY'")->fetchAll(PDO::FETCH_COLUMN);
    echo "Foreign keys on subject_groups: \n";
    foreach ($fk as $f) echo " - $f\n";
} else {
    echo "Column teaching_form_id NOT found in subject_groups\n";
}
