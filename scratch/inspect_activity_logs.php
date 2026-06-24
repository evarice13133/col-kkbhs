<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();
$t = 'activity_logs';
echo "\n=== COLUMNS OF '$t' ===\n";
$stmt = $db->query("SHOW COLUMNS FROM `$t`");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  " . $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . " | " . $row['Default'] . "\n";
}

echo "\n=== LATEST ENTRIES ===\n";
$entries = $db->query("SELECT * FROM `$t` ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
print_r($entries);
