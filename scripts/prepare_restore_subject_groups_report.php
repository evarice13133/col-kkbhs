<?php
// Analyse les subjects avec subject_group_id NULL et propose une restauration
// basée sur la colonne textuelle `groupe` et les libellés de `subject_groups`.

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

$db = Database::getInstance()->getConnection();

echo "Analyse démarrée\n";

$total = (int)$db->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
$nullCount = (int)$db->query("SELECT COUNT(*) FROM subjects WHERE subject_group_id IS NULL")->fetchColumn();
echo "Total subjects: {$total}\n";
echo "Subjects with subject_group_id IS NULL: {$nullCount}\n";

$stmt = $db->query("SELECT id, nom, groupe, teaching_type_id FROM subjects WHERE subject_group_id IS NULL ORDER BY groupe, id");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$groups = $db->query("SELECT id, libelle, teaching_type_id FROM subject_groups")->fetchAll(PDO::FETCH_ASSOC);
$groupsByLib = [];
foreach ($groups as $g) {
    $groupsByLib[strtolower($g['libelle'])][] = $g;
}

$canRestore = [];
$ambiguous = [];
$noMatch = [];

foreach ($rows as $r) {
    $gText = trim((string)$r['groupe']);
    $key = strtolower($gText);
    if ($gText === '') {
        $noMatch[] = $r;
        continue;
    }
    if (isset($groupsByLib[$key]) && count($groupsByLib[$key]) === 1) {
        $grp = $groupsByLib[$key][0];
        $canRestore[] = array_merge($r, ['matched_group_id'=>$grp['id'], 'matched_group_libelle'=>$grp['libelle'], 'match_type'=>'exact']);
        continue;
    }

    // try LIKE match
    $likeStmt = $db->prepare("SELECT id, libelle FROM subject_groups WHERE LOWER(libelle) LIKE :like LIMIT 5");
    $likeStmt->execute([':like' => '%' . $key . '%']);
    $cands = $likeStmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($cands) === 1) {
        $grp = $cands[0];
        $canRestore[] = array_merge($r, ['matched_group_id'=>$grp['id'], 'matched_group_libelle'=>$grp['libelle'], 'match_type'=>'like']);
        continue;
    }
    if (count($cands) > 1) {
        $ambiguous[] = array_merge($r, ['candidates'=>$cands]);
        continue;
    }

    $noMatch[] = $r;
}

echo "Possible restorations (auto): " . count($canRestore) . "\n";
echo "Ambiguous mappings: " . count($ambiguous) . "\n";
echo "No match: " . count($noMatch) . "\n";

$outDir = __DIR__ . '/../tmp';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);

$csvFile = $outDir . '/restore_candidates_' . date('Ymd_His') . '.csv';
$fh = fopen($csvFile, 'w');
fputcsv($fh, ['subject_id','nom','groupe','matched_group_id','matched_group_libelle','match_type']);
foreach ($canRestore as $c) {
    fputcsv($fh, [$c['id'],$c['nom'],$c['groupe'],$c['matched_group_id'],$c['matched_group_libelle'],$c['match_type']]);
}
fclose($fh);

$sqlFile = $outDir . '/restore_updates_' . date('Ymd_His') . '.sql';
$fhSql = fopen($sqlFile, 'w');
fwrite($fhSql, "-- SQL statements to restore subject_group_id based on groupe text\nBEGIN;\n");
foreach ($canRestore as $c) {
    $s = sprintf("UPDATE subjects SET subject_group_id = %d WHERE id = %d;\n", $c['matched_group_id'], $c['id']);
    fwrite($fhSql, $s);
}
fwrite($fhSql, "-- Ambiguous mappings and no-match entries are listed below as comments\n");
foreach ($ambiguous as $a) {
    fwrite($fhSql, "-- AMBIGUOUS subject={$a['id']} groupe='{$a['groupe']}' candidates=\"" . json_encode($a['candidates']) . "\"\n");
}
foreach ($noMatch as $n) {
    fwrite($fhSql, "-- NO_MATCH subject={$n['id']} groupe='{$n['groupe']}'\n");
}
fwrite($fhSql, "COMMIT;\n");
fclose($fhSql);

echo "Generated files: \n - $csvFile\n - $sqlFile\n";
echo "Analyse terminée. Vérifiez les fichiers et validez l'exécution contrôlée si OK.\n";

exit(0);
