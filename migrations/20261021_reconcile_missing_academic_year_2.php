<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();
$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

$sourceYearId = 2;
$targetYearId = 3;
$targetYearStmt = $db->prepare('SELECT nom FROM academic_years WHERE id = ?');
$targetYearStmt->execute([$targetYearId]);
$targetYearName = $targetYearStmt->fetchColumn();
if ($targetYearName !== '2025-2026') {
    throw new RuntimeException('L’année cible ID 3 ne correspond plus au libellé confirmé.');
}
if ($db->query('SELECT 1 FROM academic_years WHERE id = 2')->fetchColumn()) {
    throw new RuntimeException('L’année ID 2 existe maintenant; la réconciliation doit être réévaluée.');
}

function countMissingYearReferences(PDO $db, string $table, int $yearId): int
{
    return (int) $db->query(
        "SELECT COUNT(*) FROM `{$table}` WHERE academic_year_id = {$yearId}"
    )->fetchColumn();
}

function ensureYearReferenceBackup(
    PDO $db,
    string $table,
    string $backupTable,
    int $yearId
): int {
    $exists = $db->query("SHOW TABLES LIKE '{$backupTable}'")->fetchColumn();
    if (!$exists) {
        $db->exec(
            "CREATE TABLE `{$backupTable}` (
                id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
                original_academic_year_id INT NOT NULL
            ) ENGINE=InnoDB"
        );
        $db->exec(
            "INSERT INTO `{$backupTable}` (id, original_academic_year_id)
             SELECT id, academic_year_id FROM `{$table}`
             WHERE academic_year_id = {$yearId}"
        );
    }

    $count = (int) $db->query("SELECT COUNT(*) FROM `{$backupTable}`")->fetchColumn();
    if ($count === 0 || $count !== countMissingYearReferences($db, $table, $yearId)
        && countMissingYearReferences($db, $table, $yearId) > 0) {
        throw new RuntimeException("La sauvegarde {$backupTable} ne correspond pas aux références attendues.");
    }

    $missingRows = (int) $db->query(
        "SELECT COUNT(*) FROM `{$backupTable}` b
         LEFT JOIN `{$table}` t ON t.id = b.id
         WHERE t.id IS NULL OR t.academic_year_id NOT IN ({$yearId}, {$GLOBALS['targetYearId']})"
    )->fetchColumn();
    if ($missingRows > 0) {
        throw new RuntimeException(
            "La sauvegarde {$backupTable} contient {$missingRows} ligne(s) modifiée(s) ou absente(s)."
        );
    }

    return $count;
}

foreach (['sequences', 'activity_logs'] as $table) {
    $otherOrphans = (int) $db->query(
        "SELECT COUNT(*) FROM `{$table}` r
         WHERE r.academic_year_id IS NOT NULL
           AND r.academic_year_id <> {$sourceYearId}
           AND NOT EXISTS (
               SELECT 1 FROM academic_years ay WHERE ay.id = r.academic_year_id
           )"
    )->fetchColumn();
    if ($otherOrphans > 0) {
        throw new RuntimeException(
            "{$table} contient {$otherOrphans} autre(s) année(s) absente(s); aucune référence ne sera déplacée."
        );
    }
}

$sequenceBackupRows = ensureYearReferenceBackup(
    $db,
    'sequences',
    'sequences_year_2_repair_backup_20261021',
    $sourceYearId
);
$activityLogBackupRows = ensureYearReferenceBackup(
    $db,
    'activity_logs',
    'activity_logs_year_2_repair_backup_20261021',
    $sourceYearId
);
$db->beginTransaction();
try {
    $updateSequences = $db->prepare(
        'UPDATE sequences SET academic_year_id = ? WHERE academic_year_id = ?'
    );
    $updateSequences->execute([$targetYearId, $sourceYearId]);
    $updatedSequences = $updateSequences->rowCount();

    $updateActivityLogs = $db->prepare(
        'UPDATE activity_logs SET academic_year_id = ? WHERE academic_year_id = ?'
    );
    $updateActivityLogs->execute([$targetYearId, $sourceYearId]);
    $updatedActivityLogs = $updateActivityLogs->rowCount();

    foreach ([
        ['sequences', 'sequences_year_2_repair_backup_20261021'],
        ['activity_logs', 'activity_logs_year_2_repair_backup_20261021'],
    ] as [$table, $backupTable]) {
        $unreconciled = (int) $db->query(
            "SELECT COUNT(*) FROM `{$backupTable}` b
             LEFT JOIN `{$table}` t ON t.id = b.id
             WHERE t.id IS NULL OR t.academic_year_id <> {$targetYearId}"
        )->fetchColumn();
        if ($unreconciled > 0) {
            throw new RuntimeException(
                "{$table}: {$unreconciled} référence(s) n’ont pas été réconciliées."
            );
        }
    }

    $db->commit();
} catch (Throwable $error) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    throw $error;
}

foreach ([
    ['sequences', 'academic_year_id', 'idx_sequences_academic_year'],
    ['activity_logs', 'academic_year_id', 'idx_activity_logs_academic_year'],
] as [$table, $column, $index]) {
    $columnInfoStmt = $db->prepare(
        'SELECT COLUMN_TYPE, IS_NULLABLE FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $columnInfoStmt->execute([$table, $column]);
    $columnInfo = $columnInfoStmt->fetch(\PDO::FETCH_ASSOC);
    if (!$columnInfo) {
        throw new RuntimeException("Colonne {$table}.{$column} introuvable.");
    }
    if (strtolower($columnInfo['COLUMN_TYPE']) !== 'int(11)') {
        $nullability = $columnInfo['IS_NULLABLE'] === 'YES' ? 'NULL' : 'NOT NULL';
        $db->exec(
            "ALTER TABLE `{$table}` MODIFY `{$column}` INT(11) {$nullability}"
        );
    }

    $indexExists = $db->query(
        "SHOW INDEX FROM `{$table}` WHERE Key_name = '{$index}'"
    )->fetchColumn();
    if (!$indexExists) {
        $db->exec("ALTER TABLE `{$table}` ADD INDEX `{$index}` (`{$column}`)");
    }
}

foreach ([
    ['sequences', 'fk_sequences_academic_year'],
    ['activity_logs', 'fk_activity_logs_academic_year'],
] as [$table, $constraint]) {
    $constraintStmt = $db->prepare(
        'SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
         WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ?
           AND COLUMN_NAME = \'academic_year_id\'
           AND REFERENCED_TABLE_NAME = \'academic_years\''
    );
    $constraintStmt->execute([$table]);
    if ((int) $constraintStmt->fetchColumn() === 0) {
        $db->exec(
            "ALTER TABLE `{$table}`
             ADD CONSTRAINT `{$constraint}` FOREIGN KEY (academic_year_id)
             REFERENCES academic_years(id) ON DELETE RESTRICT ON UPDATE CASCADE"
        );
    }
}

$remainingYearTwoReferences =
    countMissingYearReferences($db, 'sequences', $sourceYearId)
    + countMissingYearReferences($db, 'activity_logs', $sourceYearId);
if ($remainingYearTwoReferences > 0) {
    throw new RuntimeException(
        "{$remainingYearTwoReferences} référence(s) vers l’année ID 2 subsistent."
    );
}

echo "Séquences mises à jour: {$updatedSequences}; sauvegarde: {$sequenceBackupRows} identifiants.\n";
echo "Journaux mis à jour: {$updatedActivityLogs}; sauvegarde: {$activityLogBackupRows} identifiants.\n";
echo "Les deux tables ont maintenant une clé étrangère restrictive vers academic_years.\n";
