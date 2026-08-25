<?php
require_once __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "=== INSPECTING SUBJECT_CLASSES TABLE ===\n";
    $scCount = $pdo->query("SELECT COUNT(*) FROM subject_classes")->fetchColumn();
    echo "Total rows in subject_classes: $scCount\n";

    if ($scCount > 0) {
        $sample = $pdo->query("SELECT * FROM subject_classes LIMIT 20")->fetchAll();
        print_r($sample);
    }

    echo "\n=== INSPECTING TEACHER_ASSIGNMENTS TABLE ===\n";
    $taCount = $pdo->query("SELECT COUNT(*) FROM teacher_assignments")->fetchColumn();
    echo "Total rows in teacher_assignments: $taCount\n";

    if ($taCount > 0) {
        $sampleTa = $pdo->query("SELECT * FROM teacher_assignments LIMIT 20")->fetchAll();
        print_r($sampleTa);
    }

    echo "\n=== STATS BY CLASS ===\n";
    $classesWithSubjects = $pdo->query("
        SELECT c.id, c.nom, COUNT(sc.subject_id) as subjects_count
        FROM classes c
        LEFT JOIN subject_classes sc ON c.id = sc.class_id
        GROUP BY c.id, c.nom
        ORDER BY subjects_count ASC, c.nom ASC
    ")->fetchAll();
    
    foreach ($classesWithSubjects as $c) {
        echo "Class #{$c['id']} ({$c['nom']}): {$c['subjects_count']} subjects linked.\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
