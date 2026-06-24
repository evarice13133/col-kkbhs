<?php
require_once 'config/config.php';
require_once 'vendor/autoload.php';

use App\Core\Database;
$db = Database::getInstance()->getConnection();

echo "=== TABLES ===\n";
$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    echo $table . "\n";
}
