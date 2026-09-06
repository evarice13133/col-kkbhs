<?php
require __DIR__ . '/../config/config.php';
$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
foreach (['subjects','subject_classes','competencies','classes','teaching_types','teaching_forms','subject_groups','academic_years'] as $table) {
    echo "TABLE $table\n";
    foreach ($pdo->query('SHOW COLUMNS FROM `' . $table . '`') as $row) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE), PHP_EOL;
    }
    echo "\n";
}

foreach (['SELECT id, nom FROM classes ORDER BY id DESC LIMIT 20', 'SELECT id, nom FROM subject_groups ORDER BY id DESC LIMIT 20', 'SELECT id, nom FROM teaching_types ORDER BY id DESC LIMIT 20', 'SELECT id, nom, teaching_type_id FROM teaching_forms ORDER BY id DESC LIMIT 20'] as $sql) {
    echo "QUERY: $sql\n";
    foreach ($pdo->query($sql) as $row) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE), PHP_EOL;
    }
    echo "\n";
}
