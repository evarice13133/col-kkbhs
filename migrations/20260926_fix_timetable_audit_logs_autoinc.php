<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

if (!$db->query("SHOW TABLES LIKE 'timetable_audit_logs'")->fetchColumn()) {
    echo "Table timetable_audit_logs absente, migration ignoree.\n";
    exit(0);
}

$zeroIds = $db->query('SELECT id FROM timetable_audit_logs WHERE id = 0')->fetchAll(PDO::FETCH_COLUMN);
if ($zeroIds) {
    $nextId = (int) $db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM timetable_audit_logs')->fetchColumn();
    $update = $db->prepare('UPDATE timetable_audit_logs SET id = ? WHERE id = 0');
    foreach ($zeroIds as $_) {
        $update->execute([$nextId++]);
    }
}

$db->exec('ALTER TABLE timetable_audit_logs MODIFY id INT(11) NOT NULL AUTO_INCREMENT');
$nextId = (int) $db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM timetable_audit_logs')->fetchColumn();
$db->exec('ALTER TABLE timetable_audit_logs AUTO_INCREMENT = ' . max(1, $nextId));

echo "AUTO_INCREMENT de timetable_audit_logs synchronise a " . max(1, $nextId) . ".\n";
exit(0);