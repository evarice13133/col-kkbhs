<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();
$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

function tableRows(PDO $db, string $table): int
{
    return (int) $db->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
}

function indexColumns(PDO $db, string $table, string $index): array
{
    $stmt = $db->prepare(
        'SELECT COLUMN_NAME FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?
         ORDER BY SEQ_IN_INDEX'
    );
    $stmt->execute([$table, $index]);
    return $stmt->fetchAll(\PDO::FETCH_COLUMN);
}

function hasForeignKey(PDO $db, string $table, string $column, string $parent): bool
{
    $stmt = $db->prepare(
        'SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
         WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ?
           AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME = ?'
    );
    $stmt->execute([$table, $column, $parent]);
    return (int) $stmt->fetchColumn() > 0;
}

function ensureBackup(PDO $db, string $table, string $backup): int
{
    $exists = $db->query("SHOW TABLES LIKE '{$backup}'")->fetchColumn();
    if (!$exists) {
        $db->exec("CREATE TABLE `{$backup}` LIKE `{$table}`");
        $db->exec("INSERT INTO `{$backup}` SELECT * FROM `{$table}`");
    }

    $backupRows = tableRows($db, $backup);
    if ($backupRows !== tableRows($db, $table)) {
        throw new RuntimeException(
            "La sauvegarde {$backup} ne correspond pas au nombre de lignes de {$table}."
        );
    }

    return $backupRows;
}

function assertReferencesExist(PDO $db, string $table, string $column, string $parent): void
{
    $orphans = (int) $db->query(
        "SELECT COUNT(*) FROM `{$table}` child
         WHERE child.`{$column}` IS NULL
            OR NOT EXISTS (
                SELECT 1 FROM `{$parent}` parent
                WHERE parent.id = child.`{$column}`
            )"
    )->fetchColumn();
    if ($orphans > 0) {
        throw new RuntimeException(
            "{$table}.{$column} contient {$orphans} référence(s) nulle(s) ou absente(s) vers {$parent}.id."
        );
    }
}

foreach ([
    ['subject_classes', 'subject_id', 'subjects'],
    ['subject_classes', 'class_id', 'classes'],
    ['subject_classes', 'academic_year_id', 'academic_years'],
    ['teacher_assignments', 'user_id', 'users'],
    ['teacher_assignments', 'subject_id', 'subjects'],
    ['teacher_assignments', 'class_id', 'classes'],
    ['teacher_assignments', 'academic_year_id', 'academic_years'],
] as [$table, $column, $parent]) {
    assertReferencesExist($db, $table, $column, $parent);
}

$duplicateSubjectClasses = (int) $db->query(
    'SELECT COUNT(*) FROM (
         SELECT subject_id, class_id, academic_year_id
         FROM subject_classes
         GROUP BY subject_id, class_id, academic_year_id
         HAVING COUNT(*) > 1
     ) duplicates'
)->fetchColumn();
if ($duplicateSubjectClasses > 0) {
    throw new RuntimeException('Des affectations matière-classe-année sont dupliquées.');
}

$duplicateTeacherAssignments = (int) $db->query(
    'SELECT COUNT(*) FROM (
         SELECT class_id, subject_id, academic_year_id
         FROM teacher_assignments
         GROUP BY class_id, subject_id, academic_year_id
         HAVING COUNT(*) > 1
     ) duplicates'
)->fetchColumn();
if ($duplicateTeacherAssignments > 0) {
    throw new RuntimeException(
        'Plusieurs enseignants sont affectés à une même matière, classe et année; aucune contrainte ne sera remplacée.'
    );
}

$subjectClassBackupRows = ensureBackup(
    $db,
    'subject_classes',
    'subject_classes_backup_20261021'
);
$teacherAssignmentBackupRows = ensureBackup(
    $db,
    'teacher_assignments',
    'teacher_assignments_backup_20261021'
);

$subjectClassPrimaryKey = indexColumns($db, 'subject_classes', 'PRIMARY');
$subjectClassYear = $db->query(
    "SELECT COLUMN_TYPE, IS_NULLABLE FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subject_classes'
       AND COLUMN_NAME = 'academic_year_id'"
)->fetch(PDO::FETCH_ASSOC);
$subjectClassAlterations = [];
if (
    strtolower((string) ($subjectClassYear['COLUMN_TYPE'] ?? '')) !== 'int(11)'
    || ($subjectClassYear['IS_NULLABLE'] ?? 'YES') !== 'NO'
) {
    $subjectClassAlterations[] = 'MODIFY academic_year_id INT(11) NOT NULL';
}
if ($subjectClassPrimaryKey !== ['subject_id', 'class_id', 'academic_year_id']) {
    if ($subjectClassPrimaryKey !== []) {
        $subjectClassAlterations[] = 'DROP PRIMARY KEY';
    }
    $subjectClassAlterations[] = 'ADD PRIMARY KEY (subject_id, class_id, academic_year_id)';
}
if (indexColumns($db, 'subject_classes', 'idx_subject_classes_academic_year') === []) {
    $subjectClassAlterations[] = 'ADD KEY idx_subject_classes_academic_year (academic_year_id)';
}
if ($subjectClassAlterations !== []) {
    $db->exec('ALTER TABLE subject_classes ' . implode(', ', $subjectClassAlterations));
}

$teacherPrimaryKey = indexColumns($db, 'teacher_assignments', 'PRIMARY');
$teacherYear = $db->query(
    "SELECT COLUMN_TYPE, IS_NULLABLE FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'teacher_assignments'
       AND COLUMN_NAME = 'academic_year_id'"
)->fetch(PDO::FETCH_ASSOC);
$teacherAlterations = [];
if (
    strtolower((string) ($teacherYear['COLUMN_TYPE'] ?? '')) !== 'int(11)'
    || ($teacherYear['IS_NULLABLE'] ?? 'YES') !== 'NO'
) {
    $teacherAlterations[] = 'MODIFY academic_year_id INT(11) NOT NULL';
}
if ($teacherPrimaryKey !== ['user_id', 'subject_id', 'class_id', 'academic_year_id']) {
    if ($teacherPrimaryKey !== []) {
        $teacherAlterations[] = 'DROP PRIMARY KEY';
    }
    $teacherAlterations[] = 'ADD PRIMARY KEY (user_id, subject_id, class_id, academic_year_id)';
}

$teacherUniqueColumns = indexColumns($db, 'teacher_assignments', 'idx_teacher_unique_assignment');
if ($teacherUniqueColumns !== ['class_id', 'subject_id', 'academic_year_id']) {
    if ($teacherUniqueColumns !== []) {
        $teacherAlterations[] = 'DROP INDEX idx_teacher_unique_assignment';
    }
    $teacherAlterations[] =
        'ADD UNIQUE KEY idx_teacher_unique_assignment (class_id, subject_id, academic_year_id)';
}
if (indexColumns($db, 'teacher_assignments', 'idx_teacher_assignments_academic_year') === []) {
    $teacherAlterations[] =
        'ADD KEY idx_teacher_assignments_academic_year (academic_year_id)';
}
if ($teacherAlterations !== []) {
    $db->exec('ALTER TABLE teacher_assignments ' . implode(', ', $teacherAlterations));
}

foreach ([
    ['subject_classes', 'subject_id', 'subjects', 'fk_subject_classes_subject',
        'ON DELETE RESTRICT ON UPDATE CASCADE'],
    ['subject_classes', 'class_id', 'classes', 'fk_subject_classes_class',
        'ON DELETE RESTRICT ON UPDATE CASCADE'],
    ['subject_classes', 'academic_year_id', 'academic_years', 'fk_subject_classes_academic_year',
        'ON UPDATE CASCADE'],
    ['teacher_assignments', 'user_id', 'users', 'fk_teacher_assignments_user',
        'ON DELETE RESTRICT ON UPDATE CASCADE'],
    ['teacher_assignments', 'subject_id', 'subjects', 'fk_teacher_assignments_subject',
        'ON DELETE RESTRICT ON UPDATE CASCADE'],
    ['teacher_assignments', 'class_id', 'classes', 'fk_teacher_assignments_class',
        'ON DELETE RESTRICT ON UPDATE CASCADE'],
    ['teacher_assignments', 'academic_year_id', 'academic_years', 'fk_teacher_assignments_academic_year',
        'ON UPDATE CASCADE'],
] as [$table, $column, $parent, $constraint, $actions]) {
    if (!hasForeignKey($db, $table, $column, $parent)) {
        $db->exec(
            "ALTER TABLE `{$table}`
             ADD CONSTRAINT `{$constraint}` FOREIGN KEY (`{$column}`)
             REFERENCES `{$parent}` (id) {$actions}"
        );
    }
}

foreach ([
    ['subject_classes', 'subject_classes_backup_20261021',
        ['subject_id', 'class_id', 'academic_year_id']],
    ['teacher_assignments', 'teacher_assignments_backup_20261021',
        ['user_id', 'subject_id', 'class_id', 'academic_year_id']],
] as [$table, $backup, $columns]) {
    if (tableRows($db, $table) !== tableRows($db, $backup)) {
        throw new RuntimeException("Le nombre de lignes de {$table} diffère de sa sauvegarde.");
    }
    $join = [];
    foreach ($columns as $column) {
        $join[] = "current.`{$column}` = backup.`{$column}`";
    }
    $missing = (int) $db->query(
        "SELECT COUNT(*) FROM `{$backup}` backup
         WHERE NOT EXISTS (
             SELECT 1 FROM `{$table}` current WHERE " . implode(' AND ', $join) . '
         )'
    )->fetchColumn();
    if ($missing > 0) {
        throw new RuntimeException(
            "{$table}: {$missing} ligne(s) de la sauvegarde ne sont plus présentes."
        );
    }
}

echo "subject_classes: {$subjectClassBackupRows} lignes conservées; clé primaire multi-années et références vérifiées.\n";
echo "teacher_assignments: {$teacherAssignmentBackupRows} lignes conservées; clés multi-années et références vérifiées.\n";
