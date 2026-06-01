<?php
/**
 * MIGRATION: Update academic_years table structure
 * 
 * This script adds start_date and end_date columns to the academic_years table
 * to better define year boundaries and enable proper year management.
 */

$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');

echo "=== MIGRATION: Updating academic_years table structure ===\n\n";

// Check current structure
$check = $pdo->query("SHOW COLUMNS FROM academic_years LIKE 'start_date'");
if ($check->rowCount() > 0) {
    echo "✓ start_date column already exists\n";
} else {
    try {
        $sql = "ALTER TABLE academic_years ADD COLUMN start_date DATE NULL AFTER nom";
        $pdo->exec($sql);
        echo "✓ start_date column added\n";
    } catch (PDOException $e) {
        echo "✗ ERROR adding start_date: " . $e->getMessage() . "\n";
    }
}

$check = $pdo->query("SHOW COLUMNS FROM academic_years LIKE 'end_date'");
if ($check->rowCount() > 0) {
    echo "✓ end_date column already exists\n";
} else {
    try {
        $sql = "ALTER TABLE academic_years ADD COLUMN end_date DATE NULL AFTER start_date";
        $pdo->exec($sql);
        echo "✓ end_date column added\n";
    } catch (PDOException $e) {
        echo "✗ ERROR adding end_date: " . $e->getMessage() . "\n";
    }
}

// Update existing years with reasonable dates if they're NULL
echo "\nUpdating existing academic years with dates...\n";

$stmt = $pdo->query("SELECT id, nom FROM academic_years WHERE start_date IS NULL OR end_date IS NULL");
$yearsToUpdate = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($yearsToUpdate as $year) {
    $nom = $year['nom'];
    $id = $year['id'];
    
    // Parse year from nom (e.g., "2025-2026")
    if (preg_match('/(\d{4})-(\d{4})/', $nom, $matches)) {
        $startYear = (int)$matches[1];
        $endYear = (int)$matches[2];
        
        $startDate = "{$startYear}-09-01"; // September 1st
        $endDate = "{$endYear}-06-30"; // June 30th
        
        $sql = "UPDATE academic_years SET start_date = ?, end_date = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$startDate, $endDate, $id]);
        
        echo "  ✓ Updated year '$nom': $startDate to $endDate\n";
    }
}

echo "\n=== MIGRATION COMPLETED SUCCESSFULLY ===\n";
