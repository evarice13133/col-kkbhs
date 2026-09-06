<?php
require __DIR__ . '/../config/config.php';
$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

foreach (['teaching_types', 'teaching_forms', 'subject_groups', 'classes'] as $table) {
    echo "TABLE={$table}\n";
    $rows = $pdo->query('SELECT * FROM ' . $table . ' ORDER BY id DESC LIMIT 10')->fetchAll();
    echo json_encode($rows, JSON_UNESCAPED_UNICODE) . "\n\n";
}
