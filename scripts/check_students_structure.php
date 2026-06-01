<?php
/**
 * Check students table structure and constraints
 */

require __DIR__ . '/../config/config.php';

$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');

echo "=== CHECKING STUDENTS TABLE STRUCTURE ===\n\n";

// Check table structure
$stmt = $pdo->query("DESCRIBE students");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Columns in students table:\n";
foreach ($columns as $col) {
    echo "  {$col['Field']} - {$col['Type']} - Null: {$col['Null']} - Default: " . ($col['Default'] ?? 'NULL') . "\n";
}

echo "\n=== CHECKING UNIQUE CONSTRAINTS ===\n\n";

// Check unique constraints
$stmt = $pdo->query("SHOW INDEX FROM students WHERE Non_unique = 0");
$indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Unique indexes:\n";
foreach ($indexes as $idx) {
    echo "  Key: {$idx['Key_name']}, Column: {$idx['Column_name']}\n";
}

echo "\n=== CHECKING EXISTING STUDENTS ===\n\n";

// Check existing students
$stmt = $pdo->query("SELECT id, nom, prenom, email, academic_year_id FROM students LIMIT 10");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Sample students in database:\n";
foreach ($students as $student) {
    echo "  ID: {$student['id']}, Nom: {$student['nom']}, Prenom: {$student['prenom']}, Email: " . ($student['email'] ?? 'NULL') . ", Academic Year ID: " . ($student['academic_year_id'] ?? 'NULL') . "\n";
}

echo "\n=== DONE ===\n";
