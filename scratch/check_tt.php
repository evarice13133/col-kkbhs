<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

$rows = $db->query("SELECT * FROM teaching_types")->fetchAll(PDO::FETCH_ASSOC);
echo "=== TEACHING TYPES IN DB ===\n";
print_r($rows);
