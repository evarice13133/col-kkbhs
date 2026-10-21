<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;
use App\Services\BackupService;

$db = Database::getInstance()->getConnection();
$execute = in_array('--execute', $argv ?? [], true);

$sectionStmt = $db->query("SELECT id, nom FROM sections WHERE status = 1 AND (LOWER(nom) LIKE '%anglophone%' OR LOWER(nom) LIKE '%english%')");
$englishSections = $sectionStmt->fetchAll(PDO::FETCH_ASSOC);
if (count($englishSections) !== 1) {
    fwrite(STDERR, "Arrêt : une seule section active Anglophone/English doit exister.\n");
    exit(1);
}
$englishSectionId = (int) $englishSections[0]['id'];

$candidateStmt = $db->prepare("SELECT DISTINCT s.id, s.nom, s.subject_group_id
    FROM subjects s
    WHERE s.subject_group_id IN (
        SELECT id FROM subject_groups WHERE section_id = ?
    )
    OR EXISTS (
        SELECT 1 FROM subject_group_assignments sga
        JOIN subject_groups sg ON sg.id = sga.subject_group_id
        WHERE sga.subject_id = s.id AND sg.section_id = ?
    )
    OR EXISTS (
        SELECT 1 FROM subject_classes sc
        JOIN classes c ON c.id = sc.class_id
        WHERE sc.subject_id = s.id AND c.section_id = ?
    )
    ORDER BY s.id");
$candidateStmt->execute([$englishSectionId, $englishSectionId, $englishSectionId]);
$possibleSubjects = $candidateStmt->fetchAll(PDO::FETCH_ASSOC);

$scopeStmt = $db->prepare("SELECT section_id FROM subject_classes sc
                            JOIN classes c ON c.id = sc.class_id WHERE sc.subject_id = ?
                            UNION
                            SELECT c.section_id FROM teacher_assignments ta
                            LEFT JOIN classes c ON c.id = ta.class_id WHERE ta.subject_id = ?
                            UNION
                            SELECT c.section_id FROM timetable_entries te
                            JOIN timetables t ON t.id = te.timetable_id
                            LEFT JOIN classes c ON c.id = t.class_id WHERE te.subject_id = ?");
$groupScopeStmt = $db->prepare("SELECT section_id FROM subject_groups WHERE id = ?
                                UNION
                                SELECT sg.section_id FROM subject_group_assignments sga
                                JOIN subject_groups sg ON sg.id = sga.subject_group_id WHERE sga.subject_id = ?");
$gradeCountStmt = $db->prepare("SELECT COUNT(*) FROM grades WHERE subject_id = ?");

$safeSubjects = [];
$excludedSubjects = [];
foreach ($possibleSubjects as $subject) {
    $subjectId = (int) $subject['id'];

    $scopeStmt->execute([$subjectId, $subjectId, $subjectId]);
    $classSections = $scopeStmt->fetchAll(PDO::FETCH_COLUMN);
    $groupScopeStmt->execute([(int) ($subject['subject_group_id'] ?? 0), $subjectId]);
    $groupSections = $groupScopeStmt->fetchAll(PDO::FETCH_COLUMN);

    $hasEnglishScope = in_array($englishSectionId, array_map('intval', $classSections), true)
        || in_array($englishSectionId, array_map('intval', $groupSections), true);
    $hasOtherOrUnknownScope = false;
    foreach (array_merge($classSections, $groupSections) as $scope) {
        if ($scope === null || (int) $scope !== $englishSectionId) {
            $hasOtherOrUnknownScope = true;
            break;
        }
    }

    $gradeCountStmt->execute([$subjectId]);
    $gradeCount = (int) $gradeCountStmt->fetchColumn();
    $entry = [
        'id' => $subjectId,
        'name' => (string) $subject['nom'],
        'grade_rows' => $gradeCount,
    ];

    if ($hasEnglishScope && !$hasOtherOrUnknownScope) {
        $safeSubjects[] = $entry;
    } else {
        $entry['reason'] = !$hasEnglishScope ? 'no_english_reference' : 'shared_or_unknown_section';
        $excludedSubjects[] = $entry;
    }
}

$summary = [
    'mode' => $execute ? 'execute' : 'dry-run',
    'english_section' => $englishSections[0]['nom'],
    'safe_subject_count' => count($safeSubjects),
    'safe_subjects' => $safeSubjects,
    'excluded_count' => count($excludedSubjects),
    'excluded_subjects' => $excludedSubjects,
    'grade_rows_to_delete' => array_sum(array_column($safeSubjects, 'grade_rows')),
    'audit_logs_retained' => true,
];

if (!$execute) {
    echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    echo "Aucune donnée modifiée. Relancer avec --execute après vérification et sauvegarde.\n";
    exit(0);
}

try {
    $backup = (new BackupService($db))->createArchive('before_english_subject_wipe');
} catch (Throwable $e) {
    fwrite(STDERR, "Arrêt : sauvegarde impossible; aucune donnée n’a été supprimée. {$e->getMessage()}\n");
    exit(1);
}

$subjectIds = array_column($safeSubjects, 'id');
if ($subjectIds === []) {
    echo json_encode(['success' => true, 'deleted_subjects' => 0, 'backup' => $backup['archive_path']], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit(0);
}

$referenceStmt = $db->prepare("SELECT c.TABLE_NAME
    FROM information_schema.COLUMNS c
    JOIN information_schema.TABLES t
      ON t.TABLE_SCHEMA = c.TABLE_SCHEMA AND t.TABLE_NAME = c.TABLE_NAME
    WHERE c.TABLE_SCHEMA = DATABASE()
      AND c.COLUMN_NAME = 'subject_id'
      AND c.TABLE_NAME <> 'subjects'
      AND t.TABLE_TYPE = 'BASE TABLE'
    ORDER BY c.TABLE_NAME");
$referenceStmt->execute();
$referenceTables = $referenceStmt->fetchAll(PDO::FETCH_COLUMN);

$db->beginTransaction();
try {
    $placeholders = implode(',', array_fill(0, count($subjectIds), '?'));
    $lockStmt = $db->prepare("SELECT id FROM subjects WHERE id IN ($placeholders) FOR UPDATE");
    $lockStmt->execute($subjectIds);
    if (count($lockStmt->fetchAll(PDO::FETCH_COLUMN)) !== count($subjectIds)) {
        throw new RuntimeException('La liste des matières a changé depuis le dry-run. Relancez le script sans --execute.');
    }

    foreach ($referenceTables as $table) {
        $deleteStmt = $db->prepare("DELETE FROM `" . str_replace('`', '``', (string) $table) . "` WHERE subject_id IN ($placeholders)");
        $deleteStmt->execute($subjectIds);
    }

    $deleteSubjectsStmt = $db->prepare("DELETE FROM subjects WHERE id IN ($placeholders)");
    $deleteSubjectsStmt->execute($subjectIds);
    $deletedSubjectCount = $deleteSubjectsStmt->rowCount();

    $deleteEmptyGroupsStmt = $db->prepare("DELETE FROM subject_groups
        WHERE section_id = ?
          AND NOT EXISTS (SELECT 1 FROM subjects s WHERE s.subject_group_id = subject_groups.id)
          AND NOT EXISTS (SELECT 1 FROM subject_group_assignments sga WHERE sga.subject_group_id = subject_groups.id)");
    $deleteEmptyGroupsStmt->execute([$englishSectionId]);
    $deletedGroupCount = $deleteEmptyGroupsStmt->rowCount();

    $db->commit();
    echo json_encode([
        'success' => true,
        'deleted_subjects' => $deletedSubjectCount,
        'deleted_empty_english_groups' => $deletedGroupCount,
        'deleted_grade_rows' => $summary['grade_rows_to_delete'],
        'backup' => $backup['archive_path'],
        'audit_logs_retained' => true,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, "Nettoyage annulé; transaction restaurée. {$e->getMessage()}\nSauvegarde : {$backup['archive_path']}\n");
    exit(1);
}