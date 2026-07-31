<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

$pdo = App\Core\Database::getInstance()->getConnection();

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$rowCounts = [];

foreach ($tables as $t) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    $rowCounts[$t] = $count;
}

echo "=== ROW COUNTS PER TABLE ===\n";
foreach ($rowCounts as $tbl => $cnt) {
    printf("%-35s : %d lines\n", $tbl, $cnt);
}
