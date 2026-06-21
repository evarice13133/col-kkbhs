<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();
$res = $db->query("SELECT DISTINCT entity_type, event_type FROM activity_logs WHERE entity_type IS NOT NULL AND entity_type != ''")->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
