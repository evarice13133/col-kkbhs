<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

$pdo = App\Core\Database::getInstance()->getConnection();

echo "=== ACTIVITY LOGS SAMPLES ===\n";
$logs = $pdo->query("SELECT * FROM activity_logs LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
print_r($logs);

echo "\n=== FINANCIAL HISTORY SAMPLES ===\n";
$fh = $pdo->query("SELECT * FROM financial_history LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
print_r($fh);

echo "\n=== USERS ===\n";
$users = $pdo->query("SELECT id, username, email, role_id FROM users")->fetchAll(PDO::FETCH_ASSOC);
print_r($users);
