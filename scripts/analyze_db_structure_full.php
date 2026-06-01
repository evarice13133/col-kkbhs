<?php
$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');

$tables = [
    'academic_years',
    'activity_logs',
    'classes',
    'cycles',
    'departments',
    'discipline',
    'grades',
    'sections',
    'sequences',
    'settings',
    'students',
    'subject_classes',
    'subjects',
    'system_job_runs',
    'teacher_assignments',
    'user_departments',
    'users'
];

echo "=== DATABASE STRUCTURE ANALYSIS ===\n\n";

foreach ($tables as $table) {
    echo "Table: $table\n";
    echo str_repeat("-", 50) . "\n";
    
    // Get columns
    $q = $pdo->query("DESCRIBE $table");
    $columns = $q->fetchAll();
    
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})";
        if ($col['Key'] !== '') {
            echo " [KEY: {$col['Key']}]";
        }
        if ($col['Null'] === 'NO') {
            echo " [NOT NULL]";
        }
        echo "\n";
    }
    
    // Check for academic_year_id
    $has_academic_year = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'academic_year_id') {
            $has_academic_year = true;
            break;
        }
    }
    echo "  Has academic_year_id: " . ($has_academic_year ? "YES" : "NO") . "\n";
    
    // Get row count
    $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    echo "  Row count: $count\n";
    
    echo "\n";
}

echo "=== FOREIGN KEY ANALYSIS ===\n\n";
foreach ($tables as $table) {
    $q = $pdo->query("SHOW CREATE TABLE $table");
    $create = $q->fetch();
    if (strpos($create[1], 'FOREIGN KEY') !== false) {
        echo "Table: $table\n";
        echo $create[1] . "\n\n";
    }
}
