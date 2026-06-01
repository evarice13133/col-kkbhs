<?php
/**
 * Fix all unique constraints to include academic_year_id
 * Update default values for academic_year_id to current active year
 */

require __DIR__ . '/../config/config.php';

$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');

echo "=== FIXING ALL UNIQUE CONSTRAINTS AND DEFAULT VALUES ===\n\n";

// Get current active year
$stmt = $pdo->query("SELECT id, nom FROM academic_years WHERE is_active = 1 LIMIT 1");
$activeYear = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$activeYear) {
    die("ERROR: No active academic year found.\n");
}

$activeYearId = (int) $activeYear['id'];
echo "Active academic year: {$activeYear['nom']} (ID: $activeYearId)\n\n";

// Fix teacher_assignments
echo "--- TABLE: teacher_assignments ---\n";
echo "Dropping old unique constraint idx_teacher_unique_assignment...\n";
$pdo->query("ALTER TABLE teacher_assignments DROP INDEX idx_teacher_unique_assignment");
echo "Adding new unique constraint on (class_id, subject_id, academic_year_id)...\n";
$pdo->query("ALTER TABLE teacher_assignments ADD UNIQUE KEY idx_teacher_unique_assignment (class_id, subject_id, academic_year_id)");
echo "Updating default value for academic_year_id to $activeYearId...\n";
$pdo->query("ALTER TABLE teacher_assignments ALTER COLUMN academic_year_id SET DEFAULT $activeYearId");
echo "✓ Done.\n\n";

// Fix subject_classes
echo "--- TABLE: subject_classes ---\n";
echo "Dropping old primary key...\n";
$pdo->query("ALTER TABLE subject_classes DROP PRIMARY KEY");
echo "Adding new primary key on (subject_id, class_id, academic_year_id)...\n";
$pdo->query("ALTER TABLE subject_classes ADD PRIMARY KEY (subject_id, class_id, academic_year_id)");
echo "Updating default value for academic_year_id to $activeYearId...\n";
$pdo->query("ALTER TABLE subject_classes ALTER COLUMN academic_year_id SET DEFAULT $activeYearId");
echo "✓ Done.\n\n";

// Fix sequences
echo "--- TABLE: sequences ---\n";
echo "Dropping old unique constraint on code...\n";
$pdo->query("ALTER TABLE sequences DROP INDEX code");
echo "Adding new unique constraint on (code, academic_year_id)...\n";
$pdo->query("ALTER TABLE sequences ADD UNIQUE KEY code (code, academic_year_id)");
echo "Dropping old unique constraint on label...\n";
$pdo->query("ALTER TABLE sequences DROP INDEX label");
echo "Adding new unique constraint on (label, academic_year_id)...\n";
$pdo->query("ALTER TABLE sequences ADD UNIQUE KEY label (label, academic_year_id)");
echo "Updating default value for academic_year_id to $activeYearId...\n";
$pdo->query("ALTER TABLE sequences ALTER COLUMN academic_year_id SET DEFAULT $activeYearId");
echo "✓ Done.\n\n";

echo "=== ALL FIXES COMPLETED ===\n";
