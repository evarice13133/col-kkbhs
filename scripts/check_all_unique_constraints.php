<?php
/**
 * Check all tables with academic_year_id for unique constraints
 */

require __DIR__ . '/../config/config.php';

$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');

echo "=== CHECKING ALL TABLES WITH ACADEMIC_YEAR_ID ===\n\n";

// Tables that have academic_year_id
$tables = ['students', 'classes', 'teacher_assignments', 'subject_classes', 'sequences'];

foreach ($tables as $table) {
    echo "--- TABLE: $table ---\n";
    
    // Check unique constraints
    $stmt = $pdo->query("SHOW INDEX FROM $table WHERE Non_unique = 0");
    $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($indexes) {
        echo "Unique indexes:\n";
        foreach ($indexes as $idx) {
            echo "  Key: {$idx['Key_name']}, Column: {$idx['Column_name']}\n";
        }
    } else {
        echo "No unique indexes found.\n";
    }
    
    // Check default value for academic_year_id
    $stmt = $pdo->query("DESCRIBE $table");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        if ($col['Field'] === 'academic_year_id') {
            echo "Default value for academic_year_id: " . ($col['Default'] ?? 'NULL') . "\n";
            break;
        }
    }
    
    echo "\n";
}

echo "=== DONE ===\n";
