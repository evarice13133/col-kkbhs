<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

$pdo = new PDO('mysql:host=localhost;dbname=u290233073_col_futura_db2;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$activeYearId = (int)$pdo->query("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1")->fetchColumn();
if (!$activeYearId) {
    $activeYearId = 3;
}
echo "Active Year ID: $activeYearId\n";

$classes = $pdo->query("SELECT id, nom, teaching_type_id FROM classes")->fetchAll(PDO::FETCH_ASSOC);
$subjects = $pdo->query("SELECT id, nom, teaching_type_id FROM subjects")->fetchAll(PDO::FETCH_ASSOC);

echo "Subjects count: " . count($subjects) . "\n";
echo "Classes count: " . count($classes) . "\n";

$insertSc = $pdo->prepare("
    INSERT INTO subject_classes (subject_id, class_id, academic_year_id) 
    VALUES (?, ?, ?) 
    ON DUPLICATE KEY UPDATE subject_id = subject_id
");

$linked = 0;
foreach ($subjects as $s) {
    $sId = (int)$s['id'];
    $sTt = $s['teaching_type_id'] !== null ? (int)$s['teaching_type_id'] : null;

    foreach ($classes as $c) {
        $cId = (int)$c['id'];
        $cTt = $c['teaching_type_id'] !== null ? (int)$c['teaching_type_id'] : null;

        if ($sTt === null || $cTt === null || $sTt === $cTt) {
            $insertSc->execute([$sId, $cId, $activeYearId]);
            $linked++;
        }
    }
}

$scCount = $pdo->query("SELECT COUNT(*) FROM subject_classes")->fetchColumn();
echo "-> Total subject_classes created: $scCount\n";
