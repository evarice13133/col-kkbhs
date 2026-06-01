<?php
$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');

echo "=== YEAR FILTERING ANALYSIS ===\n\n";

// Check which tables have academic_year_id
$tables = [
    'students', 'classes', 'teacher_assignments', 'subject_classes', 
    'sequences', 'activity_logs', 'system_job_runs', 'grades', 'discipline'
];

echo "Tables with academic_year_id:\n";
foreach ($tables as $table) {
    $q = $pdo->query("SHOW COLUMNS FROM $table LIKE 'academic_year_id'");
    if ($q && $q->rowCount() > 0) {
        echo "  ✓ $table\n";
    } else {
        echo "  ✗ $table (MISSING)\n";
    }
}

echo "\n=== CURRENT DATA DISTRIBUTION ===\n\n";

// Check current data distribution
echo "Grades by academic year:\n";
$stmt = $pdo->query("SELECT academic_year_id, COUNT(*) as count FROM grades GROUP BY academic_year_id");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $yearId = $row['academic_year_id'] ?? 'NULL';
    echo "  Year $yearId: {$row['count']} grades\n";
}

echo "\nDiscipline by academic year:\n";
$stmt = $pdo->query("SELECT academic_year_id, COUNT(*) as count FROM discipline GROUP BY academic_year_id");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $yearId = $row['academic_year_id'] ?? 'NULL';
    echo "  Year $yearId: {$row['count']} records\n";
}

echo "\nStudents (no year filtering):\n";
$count = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
echo "  Total students: $count\n";

echo "\nClasses (no year filtering):\n";
$count = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
echo "  Total classes: $count\n";

echo "\nTeacher assignments (no year filtering):\n";
$count = $pdo->query("SELECT COUNT(*) FROM teacher_assignments")->fetchColumn();
echo "  Total assignments: $count\n";

echo "\n=== RISK ANALYSIS ===\n\n";
echo "HIGH RISK - Data mixing between years:\n";
echo "  - Students table: No academic_year_id, same student can't be in different years\n";
echo "  - Classes table: No academic_year_id, class names reused across years\n";
echo "  - Teacher assignments: No academic_year_id, assignments persist across years\n";
echo "  - Subject classes: No academic_year_id, associations persist across years\n";
