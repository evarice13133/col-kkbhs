<?php
/**
 * MIGRATION: Remove academic_year_id from classes and subjects tables
 * 
 * This script removes academic_year_id from classes and subjects tables to make them
 * shared across years, while keeping students, grades, and other data year-specific.
 * 
 * IMPORTANT: Run this script during a maintenance window with a backup available.
 */

$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');

echo "=== MIGRATION: Removing academic_year_id from classes and subjects ===\n\n";

// 1. Remove academic_year_id from classes table
echo "Step 1: Removing academic_year_id from classes table...\n";

// Drop foreign key constraint
try {
    $pdo->query("ALTER TABLE classes DROP FOREIGN KEY classes_ibfk_academic_year");
    echo "  ✓ Dropped foreign key constraint\n";
} catch (PDOException $e) {
    echo "  ! Foreign key constraint not found or already dropped\n";
}

// Drop index
try {
    $pdo->query("ALTER TABLE classes DROP INDEX academic_year_id");
    echo "  ✓ Dropped index\n";
} catch (PDOException $e) {
    echo "  ! Index not found or already dropped\n";
}

// Drop column
try {
    $pdo->query("ALTER TABLE classes DROP COLUMN academic_year_id");
    echo "  ✓ Dropped academic_year_id column\n";
} catch (PDOException $e) {
    echo "  ! Column not found or already dropped\n";
}

// Update unique constraint back to just nom
try {
    $pdo->query("ALTER TABLE classes DROP INDEX unique_class_year");
    echo "  ✓ Dropped unique_class_year index\n";
} catch (PDOException $e) {
    echo "  ! unique_class_year index not found or already dropped\n";
}

try {
    $pdo->query("ALTER TABLE classes ADD UNIQUE KEY nom (nom)");
    echo "  ✓ Added unique constraint on nom\n";
} catch (PDOException $e) {
    echo "  ! Unique constraint on nom already exists\n";
}

echo "\n";

// 2. Remove academic_year_id from subjects table
echo "Step 2: Removing academic_year_id from subjects table...\n";

// Check if subjects table has academic_year_id
$stmt = $pdo->query("SHOW COLUMNS FROM subjects LIKE 'academic_year_id'");
$hasColumn = $stmt->fetch();

if ($hasColumn) {
    // Drop foreign key constraint
    try {
        $pdo->query("ALTER TABLE subjects DROP FOREIGN KEY subjects_ibfk_academic_year");
        echo "  ✓ Dropped foreign key constraint\n";
    } catch (PDOException $e) {
        echo "  ! Foreign key constraint not found or already dropped\n";
    }

    // Drop index
    try {
        $pdo->query("ALTER TABLE subjects DROP INDEX academic_year_id");
        echo "  ✓ Dropped index\n";
    } catch (PDOException $e) {
        echo "  ! Index not found or already dropped\n";
    }

    // Drop column
    try {
        $pdo->query("ALTER TABLE subjects DROP COLUMN academic_year_id");
        echo "  ✓ Dropped academic_year_id column\n";
    } catch (PDOException $e) {
        echo "  ! Column not found or already dropped\n";
    }
} else {
    echo "  ! subjects table does not have academic_year_id column\n";
}

echo "\n";

// 3. Verify the changes
echo "Step 3: Verifying changes...\n";

// Check classes table
$stmt = $pdo->query("DESCRIBE classes");
$classesColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
$classesHasYearId = false;
foreach ($classesColumns as $col) {
    if ($col['Field'] === 'academic_year_id') {
        $classesHasYearId = true;
        break;
    }
}

if ($classesHasYearId) {
    echo "  ✗ ERROR: classes table still has academic_year_id\n";
} else {
    echo "  ✓ classes table no longer has academic_year_id\n";
}

// Check subjects table
$stmt = $pdo->query("DESCRIBE subjects");
$subjectsColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
$subjectsHasYearId = false;
foreach ($subjectsColumns as $col) {
    if ($col['Field'] === 'academic_year_id') {
        $subjectsHasYearId = true;
        break;
    }
}

if ($subjectsHasYearId) {
    echo "  ✗ ERROR: subjects table still has academic_year_id\n";
} else {
    echo "  ✓ subjects table no longer has academic_year_id\n";
}

echo "\n=== MIGRATION COMPLETED ===\n";
