<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
use App\Core\Database;
$db = Database::getInstance()->getConnection();
$c = $db->query('SELECT COUNT(*) FROM subjects WHERE subject_group_id = 0')->fetchColumn();
echo $c.PHP_EOL;
