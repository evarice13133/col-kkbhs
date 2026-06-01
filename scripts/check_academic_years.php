<?php
$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');

echo "=== ACADEMIC YEARS TABLE ANALYSIS ===\n\n";

$q = $pdo->query("DESCRIBE academic_years");
$columns = $q->fetchAll();

echo "Columns:\n";
foreach ($columns as $col) {
    echo "  - {$col['Field']} ({$col['Type']})";
    if ($col['Key'] !== '') {
        echo " [KEY: {$col['Key']}]";
    }
    echo "\n";
}

echo "\nData:\n";
$q = $pdo->query("SELECT * FROM academic_years");
$years = $q->fetchAll();
foreach ($years as $year) {
    echo "  ID: {$year['id']}, Nom: {$year['nom']}, Is Active: " . ($year['is_active'] ? 'YES' : 'NO') . ", Status: {$year['status']}, Created: {$year['created_at']}\n";
}

echo "\nTotal academic years: " . count($years) . "\n";
