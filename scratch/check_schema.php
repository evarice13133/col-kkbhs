<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

$db = App\Core\Database::getInstance()->getConnection();

echo "=== payments columns ===\n";
$cols = $db->query("DESCRIBE payments")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "  " . $c['Field'] . " (" . $c['Type'] . ")\n";
}

echo "\n=== student_payments columns ===\n";
$cols2 = $db->query("DESCRIBE student_payments")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols2 as $c) {
    echo "  " . $c['Field'] . " (" . $c['Type'] . ")\n";
}


