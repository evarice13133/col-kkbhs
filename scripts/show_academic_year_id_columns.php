<?php
$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');
$tables = [
    'grades', 'students', 'classes', 'subjects', 'enrollments', 'discipline', 'teacher_assignments', 'user_departments', 'users', 'sequences', 'sections', 'departments'
];
foreach ($tables as $table) {
    $q = $pdo->query("SHOW COLUMNS FROM $table LIKE '%academic_year_id%'");
    if ($q && $q->rowCount() > 0) {
        echo "Table $table :\n";
        foreach($q as $row) echo "  - ".$row['Field']."\n";
    }
}
