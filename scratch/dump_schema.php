<?php
$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

$output = "=== DATABASE SCHEMA DUMP ===\n\n";

foreach ($tables as $table) {
    $output .= "Table: $table\n";
    $output .= str_repeat("=", 40) . "\n";
    $q = $pdo->query("SHOW CREATE TABLE `$table`");
    $create = $q->fetch(PDO::FETCH_NUM);
    $output .= $create[1] . "\n\n";
}

file_put_contents(__DIR__ . '/db_schema.txt', $output);
echo "Schema dumped to scratch/db_schema.txt\n";
