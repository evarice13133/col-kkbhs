<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

$tableExists = $db->query("SHOW TABLES LIKE 'subject_groups'")->fetchColumn();
if (!$tableExists) {
    echo "Table subject_groups absente, migration ignoree.\n";
    exit(0);
}

$zeroIds = $db->query("SELECT id FROM subject_groups WHERE id = 0")->fetchAll(PDO::FETCH_COLUMN);
if ($zeroIds) {
    $nextId = (int) $db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM subject_groups")->fetchColumn();
    $update = $db->prepare("UPDATE subject_groups SET id = ? WHERE id = 0");
    foreach ($zeroIds as $_) {
        $update->execute([$nextId++]);
    }
}

$db->exec("ALTER TABLE subject_groups MODIFY id INT(11) NOT NULL AUTO_INCREMENT");
$nextId = (int) $db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM subject_groups")->fetchColumn();
$db->exec("ALTER TABLE subject_groups AUTO_INCREMENT = " . max(1, $nextId));

echo "AUTO_INCREMENT de subject_groups synchronise a $nextId.\n";