<?php
/**
 * Fix class unique constraint to allow same class names in different academic years
 * Changes UNIQUE KEY on (nom) to UNIQUE KEY on (nom, academic_year_id)
 */

require __DIR__ . '/../config/config.php';

$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');

echo "=== FIXING CLASS UNIQUE CONSTRAINT ===\n\n";

// Check current unique constraints
$stmt = $pdo->query("SHOW INDEX FROM classes WHERE Key_name = 'nom'");
$index = $stmt->fetch(PDO::FETCH_ASSOC);

if ($index) {
    echo "Current unique constraint on 'nom' found.\n";
    echo "Dropping old unique constraint...\n";
    
    // Drop the old unique constraint
    $pdo->query("ALTER TABLE classes DROP INDEX nom");
    echo "✓ Old unique constraint dropped.\n";
    
    // Add new unique constraint on (nom, academic_year_id)
    echo "Adding new unique constraint on (nom, academic_year_id)...\n";
    $pdo->query("ALTER TABLE classes ADD UNIQUE KEY unique_class_year (nom, academic_year_id)");
    echo "✓ New unique constraint added.\n";
} else {
    echo "No unique constraint on 'nom' found.\n";
    echo "Adding unique constraint on (nom, academic_year_id)...\n";
    $pdo->query("ALTER TABLE classes ADD UNIQUE KEY unique_class_year (nom, academic_year_id)");
    echo "✓ Unique constraint added.\n";
}

echo "\n=== DONE ===\n";
