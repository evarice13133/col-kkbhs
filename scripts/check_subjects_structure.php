<?php
/**
 * Check subjects table structure
 */

require __DIR__ . '/../config/config.php';

$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');

echo "=== CHECKING SUBJECTS TABLE STRUCTURE ===\n\n";

// Check table structure
$stmt = $pdo->query("DESCRIBE subjects");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Columns in subjects table:\n";
foreach ($columns as $col) {
    echo "  {$col['Field']} - {$col['Type']} - Null: {$col['Null']} - Default: " . ($col['Default'] ?? 'NULL') . "\n";
}

echo "\n=== DONE ===\n";
