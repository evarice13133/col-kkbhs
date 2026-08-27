<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

foreach (['student_discounts', 'student_scholarships'] as $table) {
    $exists = $db->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
    $exists->execute([$table]);
    if ((int) $exists->fetchColumn() === 0) {
        continue;
    }

    $zeroIds = $db->query("SELECT id FROM `{$table}` WHERE id = 0")->fetchAll(PDO::FETCH_COLUMN);
    if ($zeroIds) {
        $nextId = (int) $db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM `{$table}`")->fetchColumn();
        $fixZeroId = $db->prepare("UPDATE `{$table}` SET id = ? WHERE id = 0");
        foreach ($zeroIds as $_) {
            $fixZeroId->execute([$nextId++]);
        }
    }

    $db->exec("ALTER TABLE `{$table}` MODIFY id INT(11) NOT NULL AUTO_INCREMENT");
    $nextId = (int) $db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM `{$table}`")->fetchColumn();
    $db->exec("ALTER TABLE `{$table}` AUTO_INCREMENT = " . max(1, $nextId));
}

echo "Migration financial tables AUTO_INCREMENT executed.\n";
exit(0);