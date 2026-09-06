<?php
require __DIR__ . '/../config/config.php';

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
$db = new PDO($dsn, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$subjectCount = (int) $db->query('SELECT COUNT(*) FROM subjects')->fetchColumn();
$lastSubject = $db->query('SELECT id, nom, subject_group_id, teaching_type_id, teaching_form_id FROM subjects ORDER BY id DESC LIMIT 5')->fetchAll();
$lastComp = $db->query('SELECT * FROM competencies ORDER BY id DESC LIMIT 5')->fetchAll();

echo "subjects_count={$subjectCount}\n";
if ($lastSubject) {
    echo "last_subjects=" . json_encode($lastSubject, JSON_UNESCAPED_UNICODE) . "\n";
} else {
    echo "last_subjects=[]\n";
}
if ($lastComp) {
    echo "last_competencies=" . json_encode($lastComp, JSON_UNESCAPED_UNICODE) . "\n";
} else {
    echo "last_competencies=[]\n";
}
