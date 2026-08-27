<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
use App\Core\Database;
$db = Database::getInstance()->getConnection();
$q = $db->query('SELECT id, libelle, status, teaching_type_id FROM subject_groups ORDER BY id');
foreach($q as $r) echo $r['id'].' | '.$r['libelle'].' | status='.$r['status'].' | tt='.$r['teaching_type_id'].PHP_EOL;
