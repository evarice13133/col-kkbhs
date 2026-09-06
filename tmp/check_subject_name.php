<?php
require __DIR__ . '/../config/config.php';
$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$patterns = [
    'REAL_VALIDATION_%',
    'AUTO_TEST_%',
    'SUBJECT_FIX_%',
    'VALIDATION_ADMIN_%',
    'VALIDATION BROWSER FINAL %',
    '%BROWSER_VALIDATION_%'
];
foreach ($patterns as $pattern) {
    $sql = "SELECT id, nom, subject_group_id, teaching_type_id, teaching_form_id, created_at FROM subjects WHERE nom LIKE :p ORDER BY id DESC LIMIT 20";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':p' => $pattern]);
    $rows = $stmt->fetchAll();
    echo "PATTERN={$pattern}\n";
    if ($rows) {
        echo json_encode($rows, JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "[]\n";
    }
}
$recent = $pdo->query("SELECT id, nom, subject_group_id, teaching_type_id, teaching_form_id FROM subjects ORDER BY id DESC LIMIT 10")->fetchAll();
echo "RECENT\n";
echo json_encode($recent, JSON_UNESCAPED_UNICODE) . "\n";
