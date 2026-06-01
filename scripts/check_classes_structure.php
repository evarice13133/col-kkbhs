<?php
/**
 * Check classes table structure
 */

require __DIR__ . '/../config/config.php';

$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');

echo "=== CHECKING CLASSES TABLE STRUCTURE ===\n\n";

// Check table structure
$stmt = $pdo->query("DESCRIBE classes");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Columns in classes table:\n";
foreach ($columns as $col) {
    echo "  {$col['Field']} - {$col['Type']} - Null: {$col['Null']} - Default: " . ($col['Default'] ?? 'NULL') . "\n";
}

echo "\n=== CHECKING EXISTING CLASSES ===\n\n";

// Check existing classes
$stmt = $pdo->query("SELECT id, nom, academic_year_id FROM classes");
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Classes in database:\n";
foreach ($classes as $class) {
    echo "  ID: {$class['id']}, Nom: {$class['nom']}, Academic Year ID: " . ($class['academic_year_id'] ?? 'NULL') . "\n";
}

echo "\n=== DONE ===\n";
