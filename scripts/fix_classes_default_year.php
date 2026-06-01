<?php
/**
 * Update default value for academic_year_id in classes table
 */

require __DIR__ . '/../config/config.php';

$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');

echo "=== UPDATING CLASS DEFAULT ACADEMIC YEAR ===\n\n";

// Get current active year
$stmt = $pdo->query("SELECT id, nom FROM academic_years WHERE is_active = 1 LIMIT 1");
$activeYear = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$activeYear) {
    die("ERROR: No active academic year found.\n");
}

$activeYearId = (int) $activeYear['id'];
echo "Active academic year: {$activeYear['nom']} (ID: $activeYearId)\n\n";

// Update default value for academic_year_id
echo "Updating default value for academic_year_id to $activeYearId...\n";
$pdo->query("ALTER TABLE classes ALTER COLUMN academic_year_id SET DEFAULT $activeYearId");
echo "✓ Default value updated.\n";

echo "\n=== DONE ===\n";
