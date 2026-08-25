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

    echo "=== ENROLLMENTS WITH CLASS_ID 106 ===\n";
    $e = $pdo->query("
        SELECT e.*, s.nom as student_nom, s.prenom as student_prenom, s.class_id as student_class_id
        FROM enrollments e 
        LEFT JOIN students s ON e.student_id = s.id 
        WHERE e.class_id = 106
    ")->fetchAll();
    print_r($e);

    echo "\n=== ALL REFS TO 104, 105, 106, 107 ACROSS ALL TABLES ===\n";
    $cids = [104, 105, 106, 107];
    foreach ($cids as $cid) {
        echo "--- CLASS_ID $cid ---\n";
        echo "class_installments:\n";
        print_r($pdo->query("SELECT * FROM class_installments WHERE class_id = $cid")->fetchAll());
        echo "fee_installments:\n";
        print_r($pdo->query("SELECT * FROM fee_installments WHERE class_id = $cid")->fetchAll());
        echo "installment_deadlines:\n";
        print_r($pdo->query("SELECT * FROM installment_deadlines WHERE class_id = $cid")->fetchAll());
        echo "school_fees:\n";
        print_r($pdo->query("SELECT * FROM school_fees WHERE class_id = $cid")->fetchAll());
        echo "enrollments:\n";
        print_r($pdo->query("SELECT * FROM enrollments WHERE class_id = $cid")->fetchAll());
        echo "students:\n";
        print_r($pdo->query("SELECT * FROM students WHERE class_id = $cid")->fetchAll());
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
