<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

$pdo = new PDO('mysql:host=localhost;dbname=u290233073_col_futura_db2;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sqlFile = __DIR__ . '/../db_prod.sql';
if (!file_exists($sqlFile)) {
    die("File db_prod.sql not found\n");
}

echo "1. Restoring database tables from db_prod.sql...\n";
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

$content = file_get_contents($sqlFile);

// Split SQL queries
$statements = explode(";\n", $content);
$execCount = 0;

foreach ($statements as $statement) {
    $trimmed = trim($statement);
    if ($trimmed !== '' && strpos($trimmed, '--') !== 0) {
        try {
            $pdo->exec($trimmed);
            $execCount++;
        } catch (Exception $e) {
            // Continuation en cas de petite erreur d'index
        }
    }
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

$subCount = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
echo "-> Subjects restored: $subCount\n";

$activeYearId = (int)$pdo->query("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1")->fetchColumn();
if (!$activeYearId) {
    $activeYearId = 3;
}
echo "-> Active Year ID: $activeYearId\n";

echo "2. Re-building subject_classes associations...\n";

// Attribuer chaque matière aux classes qui lui sont destinées
$classes = $pdo->query("SELECT id, nom, teaching_type_id FROM classes")->fetchAll(PDO::FETCH_ASSOC);
$subjects = $pdo->query("SELECT id, nom, teaching_type_id FROM subjects")->fetchAll(PDO::FETCH_ASSOC);

$insertSc = $pdo->prepare("
    INSERT INTO subject_classes (subject_id, class_id, academic_year_id) 
    VALUES (?, ?, ?) 
    ON DUPLICATE KEY UPDATE subject_id = subject_id
");

$linkedCount = 0;
foreach ($subjects as $s) {
    $sId = (int)$s['id'];
    $sTt = $s['teaching_type_id'] !== null ? (int)$s['teaching_type_id'] : null;

    foreach ($classes as $c) {
        $cId = (int)$c['id'];
        $cTt = $c['teaching_type_id'] !== null ? (int)$c['teaching_type_id'] : null;

        // Liaison si même type d'enseignement ou non restreint
        if ($sTt === null || $cTt === null || $sTt === $cTt) {
            $insertSc->execute([$sId, $cId, $activeYearId]);
            $linkedCount++;
        }
    }
}

$scCount = $pdo->query("SELECT COUNT(*) FROM subject_classes")->fetchColumn();
echo "-> subject_classes created: $scCount\n";
echo "=== RESTORATION AND ASSIGNMENTS COMPLETE ===\n";
