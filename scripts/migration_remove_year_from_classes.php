<?php
/**
 * MIGRATION: Remove academic_year_id from classes table
 * 
 * This script removes academic_year_id from classes table to make it
 * shared across years, while keeping students, grades, and other data year-specific.
 * 
 * IMPORTANT: Run this script during a maintenance window with a backup available.
 */

$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');

echo "=== MIGRATION: Removing academic_year_id from classes table ===\n\n";

// Step 1: Drop foreign key constraint
echo "Step 1: Dropping foreign key constraint fk_classes_academic_year...\n";
try {
    $pdo->query("ALTER TABLE classes DROP FOREIGN KEY fk_classes_academic_year");
    echo "  ✓ Dropped foreign key constraint\n";
} catch (PDOException $e) {
    echo "  ! Foreign key constraint not found or already dropped: " . $e->getMessage() . "\n";
}

// Step 2: Drop index
echo "\nStep 2: Dropping index on academic_year_id...\n";
try {
    $pdo->query("ALTER TABLE classes DROP INDEX academic_year_id");
    echo "  ✓ Dropped index\n";
} catch (PDOException $e) {
    echo "  ! Index not found or already dropped: " . $e->getMessage() . "\n";
}

// Step 3: Drop column
echo "\nStep 3: Dropping academic_year_id column...\n";
try {
    $pdo->query("ALTER TABLE classes DROP COLUMN academic_year_id");
    echo "  ✓ Dropped academic_year_id column\n";
} catch (PDOException $e) {
    echo "  ! Column not found or already dropped: " . $e->getMessage() . "\n";
}

// Step 4: Update unique constraint
echo "\nStep 4: Updating unique constraint on nom...\n";
try {
    $pdo->query("ALTER TABLE classes DROP INDEX unique_class_year");
    echo "  ✓ Dropped unique_class_year index\n";
} catch (PDOException $e) {
    echo "  ! unique_class_year index not found or already dropped: " . $e->getMessage() . "\n";
}

try {
    $pdo->query("ALTER TABLE classes ADD UNIQUE KEY nom (nom)");
    echo "  ✓ Added unique constraint on nom\n";
} catch (PDOException $e) {
    echo "  ! Unique constraint on nom already exists: " . $e->getMessage() . "\n";
}

// Step 5: Verify the changes
echo "\nStep 5: Verifying changes...\n";
$stmt = $pdo->query("DESCRIBE classes");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
$hasYearId = false;
foreach ($columns as $col) {
    if ($col['Field'] === 'academic_year_id') {
        $hasYearId = true;
        break;
    }
}

if ($hasYearId) {
    echo "  ✗ ERROR: classes table still has academic_year_id\n";
    exit(1);
} else {
    echo "  ✓ classes table no longer has academic_year_id\n";
}

echo "\n=== MIGRATION COMPLETED SUCCESSFULLY ===\n";
