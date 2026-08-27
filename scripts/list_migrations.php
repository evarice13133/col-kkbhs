<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT * FROM migrations ORDER BY id DESC LIMIT 200");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo sprintf("%s | %s | %s\n", $r['id'], $r['migration'], $r['applied_at']);
}

exit(0);
