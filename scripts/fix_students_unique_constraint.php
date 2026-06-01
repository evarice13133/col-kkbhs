<?php
/**
 * Fix student unique constraint to allow same email in different academic years
 * Changes UNIQUE KEY on (email) to UNIQUE KEY on (email, academic_year_id)
 * Also updates default value for academic_year_id to current active year
 */

require __DIR__ . '/../config/config.php';

$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');

echo "=== FIXING STUDENT UNIQUE CONSTRAINT ===\n\n";

// Get current active year
$stmt = $pdo->query("SELECT id, nom FROM academic_years WHERE is_active = 1 LIMIT 1");
$activeYear = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$activeYear) {
    die("ERROR: No active academic year found.\n");
}

$activeYearId = (int) $activeYear['id'];
echo "Active academic year: {$activeYear['nom']} (ID: $activeYearId)\n\n";

// Check current unique constraints
$stmt = $pdo->query("SHOW INDEX FROM students WHERE Key_name = 'uniq_students_email'");
$index = $stmt->fetch(PDO::FETCH_ASSOC);

if ($index) {
    echo "Current unique constraint on 'email' found.\n";
    echo "Dropping old unique constraint...\n";
    
    // Drop the old unique constraint
    $pdo->query("ALTER TABLE students DROP INDEX uniq_students_email");
    echo "✓ Old unique constraint dropped.\n";
    
    // Add new unique constraint on (email, academic_year_id)
    echo "Adding new unique constraint on (email, academic_year_id)...\n";
    $pdo->query("ALTER TABLE students ADD UNIQUE KEY uniq_students_email_year (email, academic_year_id)");
    echo "✓ New unique constraint added.\n";
} else {
    echo "No unique constraint on 'email' found.\n";
    echo "Adding unique constraint on (email, academic_year_id)...\n";
    $pdo->query("ALTER TABLE students ADD UNIQUE KEY uniq_students_email_year (email, academic_year_id)");
    echo "✓ Unique constraint added.\n";
}

// Update default value for academic_year_id
echo "\nUpdating default value for academic_year_id to $activeYearId...\n";
$pdo->query("ALTER TABLE students ALTER COLUMN academic_year_id SET DEFAULT $activeYearId");
echo "✓ Default value updated.\n";

echo "\n=== DONE ===\n";
