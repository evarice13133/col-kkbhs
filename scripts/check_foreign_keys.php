<?php
/**
 * Check foreign key constraints on classes table
 */

require __DIR__ . '/../config/config.php';

$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');

echo "=== CHECKING FOREIGN KEY CONSTRAINTS ===\n\n";

// Check classes table
echo "Foreign keys on classes table:\n";
$stmt = $pdo->query("SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = 'notemaster_imt' 
                    AND TABLE_NAME = 'classes' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL");
$fks = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($fks) {
    foreach ($fks as $fk) {
        echo "  Constraint: {$fk['CONSTRAINT_NAME']}, Column: {$fk['COLUMN_NAME']}, References: {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
    }
} else {
    echo "  No foreign keys found\n";
}

echo "\n";

// Check subjects table
echo "Foreign keys on subjects table:\n";
$stmt = $pdo->query("SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = 'notemaster_imt' 
                    AND TABLE_NAME = 'subjects' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL");
$fks = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($fks) {
    foreach ($fks as $fk) {
        echo "  Constraint: {$fk['CONSTRAINT_NAME']}, Column: {$fk['COLUMN_NAME']}, References: {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
    }
} else {
    echo "  No foreign keys found\n";
}

echo "\n=== DONE ===\n";
