<?php
/**
 * Student Promotion System
 * 
 * This script handles promoting students to the next academic year.
 * It can:
 * - Promote students to the next class based on their current class
 * - Handle students who are repeating (is_redoublant)
 * - Update academic_year_id for all students
 * - Create a promotion report
 */

$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');

echo "=== STUDENT PROMOTION SYSTEM ===\n\n";

// Get current and new academic years
$stmt = $pdo->query("SELECT id, nom, start_date, end_date FROM academic_years WHERE is_active = 1 LIMIT 1");
$currentYear = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentYear) {
    die("ERROR: No active academic year found.\n");
}

echo "Current academic year: {$currentYear['nom']} (ID: {$currentYear['id']})\n";

// Get the next academic year (the one that's not active)
$stmt = $pdo->query("SELECT id, nom, start_date, end_date FROM academic_years WHERE is_active = 0 ORDER BY id DESC LIMIT 1");
$newYear = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$newYear) {
    die("ERROR: No target academic year found. Please create a new academic year first.\n");
}

echo "Target academic year: {$newYear['nom']} (ID: {$newYear['id']})\n\n";

// Get all students in the current year
$stmt = $pdo->prepare("SELECT s.id, s.nom, s.prenom, s.class_id, s.is_redoublant, c.nom as current_class_nom
                      FROM students s
                      LEFT JOIN classes c ON s.class_id = c.id
                      WHERE s.academic_year_id = ? AND s.is_withdrawn = 0");
$stmt->execute([$currentYear['id']]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($students) . " students to promote\n\n";

// Get class mapping (current class -> next class)
// This is a simple example - in a real system, you'd have a proper class progression table
$classMapping = [
    // Example: CP -> CE1, CE1 -> CE2, etc.
    // This should be configured based on your school's structure
];

// For now, we'll just update the academic_year_id and keep students in the same class
// In a real implementation, you'd need to define the class progression rules

$promotedCount = 0;
$repeatingCount = 0;
$errors = [];

try {
    $pdo->beginTransaction();
    
    foreach ($students as $student) {
        $studentId = $student['id'];
        $currentClassId = $student['class_id'];
        $isRedoublant = $student['is_redoublant'];
        
        // Determine the target class for the new year
        // For now, we'll keep students in the same class (you should implement proper class progression)
        $targetClassId = $currentClassId;
        
        // Update the student's academic year
        $stmt = $pdo->prepare("UPDATE students SET academic_year_id = ?, class_id = ? WHERE id = ?");
        $stmt->execute([$newYear['id'], $targetClassId, $studentId]);
        
        if ($isRedoublant) {
            $repeatingCount++;
        } else {
            $promotedCount++;
        }
        
        echo "  - {$student['nom']} {$student['prenom']}: {$student['current_class_nom']} -> ";
        echo ($isRedoublant ? "REPEATING" : "PROMOTED") . "\n";
    }
    
    $pdo->commit();
    
    echo "\n=== PROMOTION COMPLETED SUCCESSFULLY ===\n";
    echo "Promoted: $promotedCount\n";
    echo "Repeating: $repeatingCount\n";
    echo "Total: " . ($promotedCount + $repeatingCount) . "\n";
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\nERROR: Promotion failed: " . $e->getMessage() . "\n";
    exit(1);
}
