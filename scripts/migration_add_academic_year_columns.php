<?php
/**
 * MIGRATION: Add academic_year_id to all year-dependent tables
 * 
 * This script adds academic_year_id foreign key to tables that need year-based data isolation.
 * It preserves existing data by associating it with the currently active academic year.
 * 
 * IMPORTANT: Run this script during a maintenance window with a backup available.
 */

$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');

echo "=== MIGRATION: Adding academic_year_id to year-dependent tables ===\n\n";

// Get the currently active academic year
$stmt = $pdo->query("SELECT id, nom FROM academic_years WHERE is_active = 1 LIMIT 1");
$activeYear = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$activeYear) {
    die("ERROR: No active academic year found. Please activate an academic year first.\n");
}

$activeYearId = (int) $activeYear['id'];
echo "Active academic year: {$activeYear['nom']} (ID: $activeYearId)\n\n";

// Tables that need academic_year_id
$migrations = [
    'students' => [
        'description' => 'Student enrollments are year-specific',
        'default_value' => $activeYearId,
        'nullable' => false,
        'after' => 'class_id'
    ],
    'classes' => [
        'description' => 'Classes are year-specific (same class names can exist in different years)',
        'default_value' => $activeYearId,
        'nullable' => false,
        'after' => 'department_id'
    ],
    'teacher_assignments' => [
        'description' => 'Teacher assignments change yearly',
        'default_value' => $activeYearId,
        'nullable' => false,
        'after' => 'class_id'
    ],
    'subject_classes' => [
        'description' => 'Subject-class associations are year-specific',
        'default_value' => $activeYearId,
        'nullable' => false,
        'after' => 'class_id'
    ],
    'sequences' => [
        'description' => 'Evaluation sequences are year-specific',
        'default_value' => $activeYearId,
        'nullable' => false,
        'after' => 'position'
    ],
    'activity_logs' => [
        'description' => 'Activity logs should be filterable by year',
        'default_value' => $activeYearId,
        'nullable' => true,
        'after' => 'created_at'
    ],
    'system_job_runs' => [
        'description' => 'System jobs should be trackable by year',
        'default_value' => $activeYearId,
        'nullable' => true,
        'after' => 'created_at'
    ]
];

foreach ($migrations as $table => $config) {
    echo "Processing table: $table\n";
    echo "  Description: {$config['description']}\n";
    
    // Check if column already exists
    $check = $pdo->query("SHOW COLUMNS FROM $table LIKE 'academic_year_id'");
    if ($check->rowCount() > 0) {
        echo "  ✓ Column already exists, skipping...\n\n";
        continue;
    }
    
    try {
        // Add the column
        $nullable = $config['nullable'] ? 'NULL' : 'NOT NULL';
        $default = $config['nullable'] ? 'NULL' : $config['default_value'];
        
        $sql = "ALTER TABLE $table ADD COLUMN academic_year_id INT(11) $nullable DEFAULT $default AFTER {$config['after']}";
        $pdo->exec($sql);
        echo "  ✓ Column added\n";
        
        // Add foreign key constraint
        $fkName = "fk_{$table}_academic_year";
        $sql = "ALTER TABLE $table ADD CONSTRAINT $fkName FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE RESTRICT ON UPDATE CASCADE";
        $pdo->exec($sql);
        echo "  ✓ Foreign key constraint added\n";
        
        // Add index for performance
        $indexName = "idx_{$table}_academic_year";
        $sql = "CREATE INDEX $indexName ON $table(academic_year_id)";
        $pdo->exec($sql);
        echo "  ✓ Index added\n";
        
        echo "  ✓ Migration completed successfully\n\n";
        
    } catch (PDOException $e) {
        echo "  ✗ ERROR: " . $e->getMessage() . "\n\n";
        die("Migration failed. Please check the error and fix manually.\n");
    }
}

echo "=== MIGRATION COMPLETED SUCCESSFULLY ===\n";
echo "All year-dependent tables now have academic_year_id column.\n";
echo "Existing data has been associated with academic year ID: $activeYearId\n";
