<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

$db = \App\Core\Database::getInstance()->getConnection();
echo "DEPARTMENTS:\n";
print_r($db->query("DESCRIBE departments")->fetchAll(PDO::FETCH_ASSOC));
echo "\nCYCLES:\n";
print_r($db->query("DESCRIBE cycles")->fetchAll(PDO::FETCH_ASSOC));
