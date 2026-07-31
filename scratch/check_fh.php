<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

$pdo = App\Core\Database::getInstance()->getConnection();

echo "=== FINANCIAL HISTORY ENTITY TYPES ===\n";
$types = $pdo->query("SELECT DISTINCT entity_type FROM financial_history")->fetchAll(PDO::FETCH_COLUMN);
print_r($types);

echo "\n=== FINANCIAL HISTORY ENTITY TYPES AND ACTIONS ===\n";
$actions = $pdo->query("SELECT entity_type, action, COUNT(*) as cnt FROM financial_history GROUP BY entity_type, action")->fetchAll(PDO::FETCH_ASSOC);
print_r($actions);
