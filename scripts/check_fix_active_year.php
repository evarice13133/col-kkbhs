<?php
/**
 * Check and fix active academic year
 * This script ensures there's an active academic year
 */

require __DIR__ . '/../config/config.php';

$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');

echo "=== CHECKING ACTIVE ACADEMIC YEAR ===\n\n";

// Check current active year
$stmt = $pdo->query("SELECT id, nom, is_active, status FROM academic_years");
$years = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Academic years in database:\n";
foreach ($years as $year) {
    echo "  ID: {$year['id']}, Nom: {$year['nom']}, Active: " . ($year['is_active'] ? 'YES' : 'NO') . ", Status: {$year['status']}\n";
}

// Check if there's an active year
$stmt = $pdo->query("SELECT id, nom FROM academic_years WHERE is_active = 1 LIMIT 1");
$activeYear = $stmt->fetch(PDO::FETCH_ASSOC);

if ($activeYear) {
    echo "\n✓ Active year found: {$activeYear['nom']} (ID: {$activeYear['id']})\n";
} else {
    echo "\n✗ NO ACTIVE YEAR FOUND!\n";
    echo "Activating the most recent year...\n";
    
    // Activate the most recent year
    $stmt = $pdo->query("SELECT id, nom FROM academic_years ORDER BY id DESC LIMIT 1");
    $latestYear = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($latestYear) {
        $pdo->query("UPDATE academic_years SET is_active = FALSE");
        $stmt = $pdo->prepare("UPDATE academic_years SET is_active = TRUE WHERE id = ?");
        $stmt->execute([$latestYear['id']]);
        echo "✓ Activated: {$latestYear['nom']} (ID: {$latestYear['id']})\n";
    } else {
        echo "✗ ERROR: No academic years found in database!\n";
        echo "Please create an academic year first.\n";
        exit(1);
    }
}

echo "\n=== DONE ===\n";
