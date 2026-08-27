<?php
/**
 * Exécute la restauration préparée :
 * - sauvegarde les subjects concernés
 * - vérifie compatibilité group <-> teaching_form <-> subject.teaching_type_id
 * - applique les UPDATE dans une transaction
 * - produit un rollback SQL et un rapport before/after
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

$db = Database::getInstance()->getConnection();
$timestamp = date('Ymd_His');
$outDir = __DIR__ . '/../tmp';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);

// 1) Collect candidates using the same logic as prepare script
$subjects = $db->query("SELECT id, nom, groupe, teaching_type_id FROM subjects WHERE subject_group_id IS NULL ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$groups = $db->query("SELECT id, libelle, teaching_type_id, teaching_form_id FROM subject_groups")->fetchAll(PDO::FETCH_ASSOC);
$groupsByLib = [];
foreach ($groups as $g) {
    $groupsByLib[strtolower($g['libelle'])][] = $g;
}

$candidates = [];
$ambiguous = [];
$noMatch = [];

$likeStmt = $db->prepare("SELECT id, libelle, teaching_type_id, teaching_form_id FROM subject_groups WHERE LOWER(libelle) LIKE :like LIMIT 5");
$findTfStmt = $db->prepare("SELECT id, teaching_type_id, status FROM teaching_forms WHERE id = :id LIMIT 1");

foreach ($subjects as $s) {
    $gText = trim((string)$s['groupe']);
    $key = strtolower($gText);
    if ($gText === '') { $noMatch[] = $s; continue; }
    if (isset($groupsByLib[$key]) && count($groupsByLib[$key]) === 1) {
        $grp = $groupsByLib[$key][0];
        $candidates[] = array_merge($s, ['matched_group'=>$grp]);
        continue;
    }
    $likeStmt->execute([':like' => '%' . $key . '%']);
    $cands = $likeStmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($cands) === 1) {
        $grp = $cands[0];
        $candidates[] = array_merge($s, ['matched_group'=>$grp]);
        continue;
    }
    if (count($cands) > 1) { $ambiguous[] = array_merge($s, ['candidates'=>$cands]); continue; }
    $noMatch[] = $s;
}

// 2) Backup current rows (CSV)
$backupCsv = $outDir . "/restore_backup_{$timestamp}.csv";
$fh = fopen($backupCsv, 'w');
fputcsv($fh, ['id','nom','groupe','subject_group_id','teaching_type_id']);
$ids = [];
foreach ($subjects as $r) {
    fputcsv($fh, [$r['id'],$r['nom'],$r['groupe'], null, $r['teaching_type_id']]);
    $ids[] = (int)$r['id'];
}
fclose($fh);

// 3) Prepare rollback SQL
$rollbackSqlFile = $outDir . "/restore_rollback_{$timestamp}.sql";
$rb = fopen($rollbackSqlFile, 'w');
fwrite($rb, "-- Rollback to set subject_group_id = NULL for restored subjects\nBEGIN;\n");
if (!empty($ids)) {
    $chunks = array_chunk($ids, 500);
    foreach ($chunks as $chunk) {
        fwrite($rb, "UPDATE subjects SET subject_group_id = NULL WHERE id IN (" . implode(',', $chunk) . ");\n");
    }
}
fwrite($rb, "COMMIT;\n");
fclose($rb);

// 4) Determine compatible updates and conflicts
$toUpdate = [];
$conflicts = [];
foreach ($candidates as $c) {
    $sid = (int)$c['id'];
    $grp = $c['matched_group'];
    $groupId = (int)$grp['id'];
    $groupTeachingType = isset($grp['teaching_type_id']) ? (int)$grp['teaching_type_id'] : null;
    $groupTeachingFormId = isset($grp['teaching_form_id']) ? (int)$grp['teaching_form_id'] : null;
    $subjectTeachingType = isset($c['teaching_type_id']) ? (int)$c['teaching_type_id'] : null;

    $compatible = true;
    $reason = '';
    // If group has teaching_type and it differs from subject -> conflict
    if ($groupTeachingType !== null && $subjectTeachingType !== null && $groupTeachingType !== $subjectTeachingType) {
        $compatible = false;
        $reason = 'group_teaching_type_mismatch';
    }
    // If group has teaching_form, check teaching_forms.teaching_type
    if ($compatible && $groupTeachingFormId) {
        $findTfStmt->execute([':id'=>$groupTeachingFormId]);
        $tf = $findTfStmt->fetch(PDO::FETCH_ASSOC);
        if ($tf) {
            $tfTeachingType = isset($tf['teaching_type_id']) ? (int)$tf['teaching_type_id'] : null;
            $tfStatus = isset($tf['status']) ? $tf['status'] : 1;
            if ($tfStatus != 1) {
                $compatible = false; $reason = 'teaching_form_inactive';
            } elseif ($tfTeachingType !== null && $subjectTeachingType !== null && $tfTeachingType !== $subjectTeachingType) {
                $compatible = false; $reason = 'teaching_form_type_mismatch';
            }
        }
    }

    if ($compatible) {
        $toUpdate[] = ['subject_id'=>$sid, 'group_id'=>$groupId];
    } else {
        $conflicts[] = ['subject_id'=>$sid, 'group_id'=>$groupId, 'reason'=>$reason, 'subject_teaching_type'=>$subjectTeachingType, 'group_teaching_type'=>$groupTeachingType, 'group_teaching_form_id'=>$groupTeachingFormId];
    }
}

// 5) Apply updates in transaction
$changesCsv = $outDir . "/restore_changes_{$timestamp}.csv";
$fhChanges = fopen($changesCsv, 'w');
fputcsv($fhChanges, ['subject_id','before_subject_group_id','after_subject_group_id']);

$applied = 0;
try {
    $db->beginTransaction();
    // get before values
    $getBefore = $db->prepare("SELECT subject_group_id FROM subjects WHERE id = :id LIMIT 1");
    $upd = $db->prepare("UPDATE subjects SET subject_group_id = :gid WHERE id = :id");
    foreach ($toUpdate as $u) {
        $getBefore->execute([':id'=>$u['subject_id']]);
        $before = $getBefore->fetchColumn();
        $upd->execute([':gid'=>$u['group_id'], ':id'=>$u['subject_id']]);
        $after = $u['group_id'];
        fputcsv($fhChanges, [$u['subject_id'], $before === null ? '' : $before, $after]);
        $applied++;
    }
    $db->commit();
    fclose($fhChanges);
} catch (Exception $e) {
    $db->rollBack();
    fclose($fhChanges);
    echo "Erreur lors de la restauration: " . $e->getMessage() . "\n";
    exit(1);
}

// 6) Save conflicts to file
$confFile = $outDir . "/restore_conflicts_{$timestamp}.json";
file_put_contents($confFile, json_encode($conflicts, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

// 7) Summary
echo "Restauration exécutée.\n";
echo "Candidates total: " . count($candidates) . "\n";
echo "Applied updates: {$applied}\n";
echo "Conflicts skipped: " . count($conflicts) . " (see {$confFile})\n";
echo "Backup CSV: {$backupCsv}\n";
echo "Rollback SQL: {$rollbackSqlFile}\n";
echo "Changes CSV: {$changesCsv}\n";

exit(0);
