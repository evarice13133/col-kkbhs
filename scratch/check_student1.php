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

    echo "=== STUDENT 1 INFO ===\n";
    print_r($pdo->query("SELECT * FROM students WHERE id = 1")->fetch());

    echo "=== PAYMENTS FOR STUDENT 1 OR OTHER STUDENTS ===\n";
    print_r($pdo->query("SELECT * FROM payments WHERE student_id = 1")->fetchAll());

    echo "=== ALL ENROLLMENTS ===\n";
    print_r($pdo->query("SELECT * FROM enrollments LIMIT 20")->fetchAll());

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
