<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

$pdo = App\Core\Database::getInstance()->getConnection();

$sql = file_get_contents(__DIR__ . '/../scripts/prepare_prod_purge.sql');
try {
    $pdo->exec($sql);
    echo "SUCCESS_EXECUTION\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
