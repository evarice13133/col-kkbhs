<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
$db = Database::getInstance()->getConnection();

$q = $db->query("DESCRIBE student_payments");
print_r($q->fetchAll(PDO::FETCH_ASSOC));
