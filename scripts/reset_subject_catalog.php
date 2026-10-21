<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;
use App\Services\BackupService;

$db = Database::getInstance()->getConnection();
$execute = in_array('--execute', $argv ?? [], true);
$confirmed = in_array('--confirm-all-sections', $argv ?? [], true);

$subjectIds = array_map('intval', $db->query('SELECT id FROM subjects ORDER BY id')->fetchAll(PDO::FETCH_COLUMN));
$groupIds = array_map('intval', $db->query('SELECT id FROM subject_groups ORDER BY id')->fetchAll(PDO::FETCH_COLUMN));

$referenceColumns = $db->query(
    "SELECT c.TABLE_NAME, c.COLUMN_NAME
     FROM information_schema.COLUMNS c
     JOIN information_schema.TABLES t
       ON t.TABLE_SCHEMA = c.TABLE_SCHEMA AND t.TABLE_NAME = c.TABLE_NAME
     WHERE c.TABLE_SCHEMA = DATABASE()
       AND COLUMN_NAME IN ('subject_id', 'subject_group_id')
       AND c.TABLE_NAME NOT IN ('subjects', 'subject_groups')
       AND t.TABLE_TYPE = 'BASE TABLE'
     ORDER BY c.TABLE_NAME, c.COLUMN_NAME"
)->fetchAll(PDO::FETCH_ASSOC);

$columnsByTable = [];
foreach ($referenceColumns as $reference) {
    $columnsByTable[(string) $reference['TABLE_NAME']][] = (string) $reference['COLUMN_NAME'];
}

$references = [];
foreach ($columnsByTable as $table => $columns) {
    $quotedTable = '`' . str_replace('`', '``', $table) . '`';
    $conditions = [];
    if (in_array('subject_id', $columns, true) && $subjectIds !== []) {
        $quotedColumn = '`subject_id`';
        $conditions[] = "{$quotedColumn} IS NOT NULL";
    } elseif (in_array('subject_id', $columns, true)) {
        $conditions[] = '`subject_id` IS NOT NULL';
    }
    if (in_array('subject_group_id', $columns, true) && $groupIds !== []) {
        $quotedColumn = '`subject_group_id`';
        $conditions[] = "{$quotedColumn} IS NOT NULL";
    } elseif (in_array('subject_group_id', $columns, true)) {
        $conditions[] = '`subject_group_id` IS NOT NULL';
    }
    if ($conditions === []) {
        continue;
    }
    $stmt = $db->prepare('SELECT COUNT(*) FROM ' . $quotedTable . ' WHERE ' . implode(' OR ', $conditions));
    $stmt->execute();
    $count = (int) $stmt->fetchColumn();
    if ($count > 0) {
        $references[] = ['table' => $table, 'rows' => $count];
    }
}

$summary = [
    'mode' => $execute ? 'execute' : 'dry-run',
    'subjects_to_delete' => count($subjectIds),
    'groups_to_delete' => count($groupIds),
    'linked_rows_to_delete' => $references,
    'linked_row_count' => array_sum(array_column($references, 'rows')),
    'audit_logs_retained' => true,
];

if (!$execute) {
    echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    echo "Aucune donnée modifiée. Sauvegarde et confirmation requises : --execute --confirm-all-sections\n";
    exit(0);
}

if (!$confirmed) {
    fwrite(STDERR, "Arrêt : confirmez explicitement la purge de toutes les sections avec --confirm-all-sections.\n");
    exit(1);
}

try {
    $backup = (new BackupService($db))->createArchive('before_all_subject_catalog_reset', false);
} catch (Throwable $e) {
    fwrite(STDERR, "Arrêt : sauvegarde impossible; aucune donnée supprimée. {$e->getMessage()}\n");
    exit(1);
}

try {
    $db->beginTransaction();
    $lockedSubjectIds = array_map('intval', $db->query('SELECT id FROM subjects ORDER BY id FOR UPDATE')->fetchAll(PDO::FETCH_COLUMN));
    $lockedGroupIds = array_map('intval', $db->query('SELECT id FROM subject_groups ORDER BY id FOR UPDATE')->fetchAll(PDO::FETCH_COLUMN));
    if ($lockedSubjectIds !== $subjectIds || $lockedGroupIds !== $groupIds) {
        throw new RuntimeException('Le catalogue a changé depuis le rapport à blanc. Relancez le script sans --execute.');
    }

    $deletedLinkedRows = 0;
    foreach (['subject_id', 'subject_group_id'] as $column) {
        foreach ($referenceColumns as $reference) {
            if ($reference['COLUMN_NAME'] !== $column) {
                continue;
            }
            $table = '`' . str_replace('`', '``', (string) $reference['TABLE_NAME']) . '`';
            $quotedColumn = '`' . str_replace('`', '``', $column) . '`';
            $delete = $db->prepare("DELETE FROM {$table} WHERE {$quotedColumn} IS NOT NULL");
            $delete->execute();
            $deletedLinkedRows += $delete->rowCount();
        }
    }

    $deletedSubjects = $db->exec('DELETE FROM subjects');
    $deletedGroups = $db->exec('DELETE FROM subject_groups');
    $remainingSubjects = (int) $db->query('SELECT COUNT(*) FROM subjects')->fetchColumn();
    $remainingGroups = (int) $db->query('SELECT COUNT(*) FROM subject_groups')->fetchColumn();
    if ($remainingSubjects !== 0 || $remainingGroups !== 0) {
        throw new RuntimeException('La vérification après suppression a détecté des matières ou groupes restants.');
    }

    $db->commit();
    echo json_encode([
        'success' => true,
        'deleted_subjects' => (int) $deletedSubjects,
        'deleted_groups' => (int) $deletedGroups,
        'linked_rows_deleted' => $deletedLinkedRows,
        'backup' => $backup['archive_path'],
        'audit_logs_retained' => true,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, "Purge annulée; transaction restaurée. {$e->getMessage()}\nSauvegarde : {$backup['archive_path']}\n");
    exit(1);
}
