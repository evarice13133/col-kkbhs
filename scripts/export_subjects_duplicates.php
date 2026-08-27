<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo "Erreur connexion DB: " . $e->getMessage() . "\n";
    exit(1);
}

$dupStmt = $pdo->query("SELECT id FROM subjects GROUP BY id HAVING COUNT(*) > 1");
$dups = $dupStmt->fetchAll(PDO::FETCH_COLUMN);
if (empty($dups)) {
    echo "Aucun doublon d'id détecté dans subjects.\n";
    exit(0);
}

$outDir = __DIR__ . '/../logs';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);
$outFile = $outDir . '/subjects_duplicates_' . date('Ymd_His') . '.csv';
$fh = fopen($outFile, 'w');

fputcsv($fh, ['duplicate_id','tmp_rowid','id','nom','coefficient','subject_group_id','teaching_type_id','department_id','created_at','status']);

foreach ($dups as $dupId) {
    $stmt = $pdo->prepare("SELECT @rownum := @rownum + 1 AS tmp_rowid, s.* FROM (SELECT @rownum := 0) r, subjects s WHERE s.id = ? ORDER BY s.created_at ASC, s.nom ASC");
    $stmt->execute([$dupId]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($fh, [$dupId, $row['tmp_rowid'], $row['id'], $row['nom'], $row['coefficient'], $row['subject_group_id'], $row['teaching_type_id'], $row['department_id'], $row['created_at'], $row['status']]);
    }
}

fclose($fh);
echo "Export des doublons terminé dans : $outFile\n";
