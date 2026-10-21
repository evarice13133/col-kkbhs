<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();
$dryRun = in_array('--dry-run', $argv ?? [], true);
$legacyGroups = $db->query("SELECT id, libelle, teaching_type_id, position
                            FROM subject_groups
                            WHERE section_id IS NULL
                              AND teaching_type_id = 3
                              AND libelle IN ('Matières Littéraires', 'Matières Scientifiques', 'Développement Personnel')
                            ORDER BY position, id")->fetchAll(PDO::FETCH_ASSOC);

if ($legacyGroups === []) {
    echo "Aucun groupe secondaire historique à répartir.\n";
    exit(0);
}

$legacyGroupIds = array_map(static fn(array $group): int => (int) $group['id'], $legacyGroups);
$placeholders = implode(',', array_fill(0, count($legacyGroupIds), '?'));
$linksSql = "SELECT s.id AS subject_id, s.subject_group_id AS group_id
             FROM subjects s
             WHERE s.subject_group_id IN ($placeholders)";
$hasAssignmentTable = (bool) $db->query("SHOW TABLES LIKE 'subject_group_assignments'")->fetchColumn();
if ($hasAssignmentTable) {
    $linksSql .= " UNION SELECT sga.subject_id, sga.subject_group_id AS group_id
                   FROM subject_group_assignments sga
                   WHERE sga.subject_group_id IN ($placeholders)";
}
$linksStmt = $db->prepare($linksSql);
$linkParams = $legacyGroupIds;
if ($hasAssignmentTable) {
    $linkParams = array_merge($linkParams, $legacyGroupIds);
}
$linksStmt->execute($linkParams);
$links = $linksStmt->fetchAll(PDO::FETCH_ASSOC);

$groupsById = [];
foreach ($legacyGroups as $group) {
    $groupsById[(int) $group['id']] = $group;
}

$classScopesStmt = $db->prepare("SELECT DISTINCT c.section_id,
                                        COALESCE(d.teaching_form_id, c.teaching_form_id) AS teaching_form_id,
                                        COALESCE(c.teaching_type_id, s.teaching_type_id) AS teaching_type_id,
                                        sec.status AS section_status,
                                        tf.status AS form_status,
                                        tf.teaching_type_id AS form_teaching_type_id
                                 FROM subject_classes sc
                                 JOIN classes c ON c.id = sc.class_id
                                 LEFT JOIN departments d ON d.id = c.department_id
                                 LEFT JOIN sections sec ON sec.id = c.section_id
                                 LEFT JOIN teaching_forms tf ON tf.id = COALESCE(d.teaching_form_id, c.teaching_form_id)
                                 JOIN subjects s ON s.id = sc.subject_id
                                 WHERE sc.subject_id = ?");

$plan = [];
$unresolved = [];
foreach ($links as $link) {
    $subjectId = (int) $link['subject_id'];
    $groupId = (int) $link['group_id'];
    if (!isset($groupsById[$groupId])) {
        continue;
    }

    $classScopesStmt->execute([$subjectId]);
    $scopes = $classScopesStmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($scopes) !== 1) {
        $unresolved[] = ['subject_id' => $subjectId, 'group_id' => $groupId, 'reason' => $scopes === [] ? 'no_class_assignment' : 'multiple_section_or_form_scopes'];
        continue;
    }

    $scope = $scopes[0];
    $sectionId = (int) ($scope['section_id'] ?? 0);
    $formId = (int) ($scope['teaching_form_id'] ?? 0);
    $teachingTypeId = (int) ($scope['teaching_type_id'] ?? 0);
    if ($sectionId <= 0 || (int) ($scope['section_status'] ?? 0) !== 1
        || $formId <= 0 || (int) ($scope['form_status'] ?? 0) !== 1
        || $teachingTypeId !== (int) $groupsById[$groupId]['teaching_type_id']
        || (int) ($scope['form_teaching_type_id'] ?? 0) !== $teachingTypeId) {
        $unresolved[] = ['subject_id' => $subjectId, 'group_id' => $groupId, 'reason' => 'inactive_or_inconsistent_class_structure'];
        continue;
    }

    $scopeKey = $groupId . ':' . $sectionId . ':' . $formId;
    $plan[$scopeKey] ??= [
        'source_group_id' => $groupId,
        'libelle' => (string) $groupsById[$groupId]['libelle'],
        'teaching_type_id' => $teachingTypeId,
        'section_id' => $sectionId,
        'teaching_form_id' => $formId,
        'subject_ids' => [],
    ];
    $plan[$scopeKey]['subject_ids'][$subjectId] = $subjectId;
}

$summary = array_values(array_map(static function (array $item): array {
    $item['subject_count'] = count($item['subject_ids']);
    unset($item['subject_ids']);
    return $item;
}, $plan));

if ($dryRun) {
    echo json_encode([
        'mode' => 'dry-run',
        'target_groups' => $summary,
        'unresolved_count' => count($unresolved),
        'unresolved_examples' => array_slice($unresolved, 0, 25),
        'notes_and_grade_rows_changed' => 0,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit(0);
}

$findTargetGroup = $db->prepare("SELECT id FROM subject_groups
                                 WHERE libelle = ? AND teaching_type_id = ? AND teaching_form_id = ? AND section_id = ? AND status = 1
                                 ORDER BY position, id LIMIT 1");
$maxPosition = $db->prepare("SELECT COALESCE(MAX(position), 0) FROM subject_groups WHERE section_id = ? AND teaching_form_id = ?");
$insertGroup = $db->prepare("INSERT INTO subject_groups (libelle, teaching_type_id, teaching_form_id, section_id, position, status)
                             VALUES (?, ?, ?, ?, ?, 1)");
$updateSubject = $db->prepare("UPDATE subjects SET subject_group_id = ? WHERE id = ? AND subject_group_id = ?");
$deleteOldAssignment = $hasAssignmentTable
    ? $db->prepare("DELETE FROM subject_group_assignments WHERE subject_id = ? AND subject_group_id = ?")
    : null;
$insertNewAssignment = $hasAssignmentTable
    ? $db->prepare("INSERT IGNORE INTO subject_group_assignments (subject_id, subject_group_id) VALUES (?, ?)")
    : null;

$db->beginTransaction();
try {
    $moved = 0;
    $createdGroups = 0;
    foreach ($plan as $item) {
        $findTargetGroup->execute([$item['libelle'], $item['teaching_type_id'], $item['teaching_form_id'], $item['section_id']]);
        $targetGroupId = (int) $findTargetGroup->fetchColumn();
        if ($targetGroupId <= 0) {
            $maxPosition->execute([$item['section_id'], $item['teaching_form_id']]);
            $position = (int) $maxPosition->fetchColumn() + 1;
            $insertGroup->execute([$item['libelle'], $item['teaching_type_id'], $item['teaching_form_id'], $item['section_id'], $position]);
            $targetGroupId = (int) $db->lastInsertId();
            $createdGroups++;
        }

        foreach ($item['subject_ids'] as $subjectId) {
            $updateSubject->execute([$targetGroupId, $subjectId, $item['source_group_id']]);
            if ($updateSubject->rowCount() === 1) {
                $moved++;
            }
            if ($hasAssignmentTable) {
                $deleteOldAssignment->execute([$subjectId, $item['source_group_id']]);
                $insertNewAssignment->execute([$subjectId, $targetGroupId]);
            }
        }
    }

    $db->commit();
    echo json_encode([
        'success' => true,
        'created_groups' => $createdGroups,
        'subject_instances_reassigned' => $moved,
        'unresolved_count' => count($unresolved),
        'unresolved_examples' => array_slice($unresolved, 0, 25),
        'grade_rows_changed' => 0,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, "Legacy group reassignment failed: {$e->getMessage()}\n");
    exit(1);
}