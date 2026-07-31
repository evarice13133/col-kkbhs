<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

$pdo = App\Core\Database::getInstance()->getConnection();

$sql = "SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL";
$fks = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$tablesWithFKsReferencingUs = [];
foreach ($fks as $fk) {
    $ref = $fk['REFERENCED_TABLE_NAME'];
    $child = $fk['TABLE_NAME'];
    $tablesWithFKsReferencingUs[$ref][] = $child . '.' . $fk['COLUMN_NAME'];
}

echo "=== TABLES AND WHAT REFERENCES THEM ===\n";
ksort($tablesWithFKsReferencingUs);
print_r($tablesWithFKsReferencingUs);
