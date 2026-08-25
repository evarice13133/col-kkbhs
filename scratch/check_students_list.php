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

    echo "=== STUDENTS COUNT & SAMPLE ===\n";
    $count = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    echo "Total students: $count\n";
    $students = $pdo->query("SELECT id, nom, prenom FROM students ORDER BY id LIMIT 20")->fetchAll();
    print_r($students);

    echo "=== CHECK STUDENT ID = 2 IN STUDENTS ===\n";
    $s2 = $pdo->query("SELECT * FROM students WHERE id = 2")->fetch();
    print_r($s2);

    echo "=== CHECK ACADEMIC YEARS ===\n";
    $years = $pdo->query("SELECT * FROM academic_years")->fetchAll();
    print_r($years);

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
