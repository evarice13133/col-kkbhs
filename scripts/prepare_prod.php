<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

const PRESERVED_ROLES = ['admin', 'superadmin', 'caissier'];
const PRESERVED_TABLES = [
    'academic_years',
    'activity_logs',
    'classes',
    'class_discounts',
    'class_installments',
    'class_rooms',
    'class_scholarships',
    'competencies',
    'cycle_levels',
    'cycles',
    'departments',
    'discount_types',
    'evaluation_competencies',
    'fee_installments',
    'installment_deadlines',
    'levels',
    'migrations',
    'permission_audit_logs',
    'permission_backups',
    'permissions',
    'role_permissions',
    'school_fees',
    'sections',
    'sequences',
    'settings',
    'subject_classes',
    'subject_group_assignments',
    'subject_groups',
    'subjects',
    'system_job_runs',
    'teaching_forms',
    'teaching_types',
    'user_permissions',
];

$purgeTables = [
    'teacher_assignments',
    'teacher_class_competencies',
    'teacher_contracts',
    'timetable_entries',
    'timetable_audit_logs',
    'timetable_time_slots',
    'timetable_weeks',
    'timetables',
    'receipt_verifications_log',
    'student_payment_allocations',
    'payment_receipts',
    'student_payments',
    'payments',
    'student_installments',
    'student_discounts',
    'student_scholarships',
    'insolvent_students',
    'expense_logs',
    'expenses',
    'financial_history',
    'grades',
    'discipline',
    'conseils_classe',
    'decisions_fin_annee',
    'historique_modifications_conseil',
    'historique_passages',
    'enrollments',
    'students',
];

$options = getopt('', ['execute', 'confirm:']);
$execute = array_key_exists('execute', $options);
$confirmation = $options['confirm'] ?? '';
$pdo = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
    DB_USER,
    DB_PASS,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);

function quoteIdentifier(string $identifier): string
{
    return '`' . str_replace('`', '``', $identifier) . '`';
}

function tableCount(PDO $pdo, string $table): int
{
    return (int) $pdo->query('SELECT COUNT(*) FROM ' . quoteIdentifier($table))->fetchColumn();
}

function writeBackup(PDO $pdo, string $database): string
{
    $views = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.VIEWS WHERE TABLE_SCHEMA = DATABASE()")->fetchColumn();
    $triggers = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE()")->fetchColumn();
    $routines = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()")->fetchColumn();
    $events = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.EVENTS WHERE EVENT_SCHEMA = DATABASE()")->fetchColumn();
    if ($views + $triggers + $routines + $events > 0) {
        throw new RuntimeException('La sauvegarde logique doit inclure des vues, déclencheurs, routines ou événements non pris en charge; aucune purge effectuée.');
    }

    $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'notemaster_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $database) . '_' . date('Ymd_His') . '.sql';
    $file = fopen($path, 'xb');
    if ($file === false) {
        throw new RuntimeException('Impossible de créer le fichier de sauvegarde: ' . $path);
    }

    try {
        fwrite($file, "-- Sauvegarde logique complète de {$database}, " . date(DATE_ATOM) . "\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");
        $tables = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $quotedTable = quoteIdentifier((string) $table);
            $create = $pdo->query('SHOW CREATE TABLE ' . $quotedTable)->fetch(PDO::FETCH_NUM);
            if (!$create || !isset($create[1])) {
                throw new RuntimeException('Impossible de lire le DDL de ' . $table);
            }

            fwrite($file, "\nDROP TABLE IF EXISTS {$quotedTable};\n{$create[1]};\n");
            $columns = $pdo->query('SHOW FULL COLUMNS FROM ' . $quotedTable)->fetchAll();
            $columnNames = array_column($columns, 'Field');
            $binaryColumns = [];
            foreach ($columns as $column) {
                if (in_array(strtolower((string) strtok((string) $column['Type'], '(')), ['binary', 'varbinary', 'tinyblob', 'blob', 'mediumblob', 'longblob'], true)) {
                    $binaryColumns[$column['Field']] = true;
                }
            }
            $quotedColumns = implode(', ', array_map('quoteIdentifier', $columnNames));
            $statement = $pdo->query('SELECT * FROM ' . $quotedTable);
            while ($row = $statement->fetch()) {
                $values = [];
                foreach ($columnNames as $columnName) {
                    $value = $row[$columnName];
                    if ($value === null) {
                        $values[] = 'NULL';
                    } elseif (isset($binaryColumns[$columnName])) {
                        $values[] = '0x' . bin2hex((string) $value);
                    } else {
                        $values[] = $pdo->quote((string) $value);
                    }
                }
                fwrite($file, 'INSERT INTO ' . $quotedTable . ' (' . $quotedColumns . ') VALUES (' . implode(', ', $values) . ");\n");
            }
            fwrite($file, "\n");
        }

        fwrite($file, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($file);
        return $path;
    } catch (Throwable $exception) {
        fclose($file);
        @unlink($path);
        throw $exception;
    }
}

function enableAutoIncrement(PDO $pdo, string $table): void
{
    $statement = $pdo->prepare("SELECT COLUMN_TYPE, IS_NULLABLE, EXTRA, COLUMN_KEY FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'id'");
    $statement->execute([$table]);
    $column = $statement->fetch();
    if (!$column || stripos((string) $column['EXTRA'], 'auto_increment') !== false) {
        return;
    }

    if ($column['IS_NULLABLE'] !== 'NO') {
        throw new RuntimeException("{$table}.id est nullable; correction automatique refusée.");
    }

    $count = tableCount($pdo, $table);
    $quotedTable = quoteIdentifier($table);
    $primaryClause = '';
    if ($column['COLUMN_KEY'] !== 'PRI') {
        if ($count !== 0) {
            throw new RuntimeException("{$table}.id n'est pas clé primaire et la table contient des lignes; correction automatique refusée.");
        }
        $primaryClause = ', ADD PRIMARY KEY (`id`)';
    }

    $type = preg_replace('/[^a-zA-Z0-9_(), ]/', '', (string) $column['COLUMN_TYPE']);
    $pdo->exec("ALTER TABLE {$quotedTable} MODIFY COLUMN `id` {$type} NOT NULL AUTO_INCREMENT{$primaryClause}");
}

try {
    $existingTables = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);
    $missingTables = array_values(array_diff($purgeTables, $existingTables));
    if ($missingTables) {
        throw new RuntimeException('Tables prévues absentes: ' . implode(', ', $missingTables));
    }
    $missingPreservedTables = array_values(array_diff(PRESERVED_TABLES, $existingTables));
    if ($missingPreservedTables) {
        throw new RuntimeException('Tables protégées absentes: ' . implode(', ', $missingPreservedTables));
    }

    $engines = $pdo->query("SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE'")->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($purgeTables as $table) {
        if (($engines[$table] ?? null) !== 'InnoDB') {
            throw new RuntimeException("{$table} n'est pas InnoDB; purge transactionnelle impossible.");
        }
    }

    echo 'Base ciblée: ' . DB_NAME . ' (' . APP_ENV . ")\n";
    echo "Données conservées: classes, matières, groupes, évaluations de référence, grille tarifaire, aides de classe, types d'aides, configuration/pilotage, journaux d'activité et RBAC.\n";
    echo "Utilisateurs conservés: admin, superadmin, caissier.\n\n";
    echo "Tables vidées (compteurs actuels):\n";
    foreach ($purgeTables as $table) {
        echo '  ' . $table . ': ' . tableCount($pdo, $table) . "\n";
    }
    foreach (['user_departments', 'user_teaching_types'] as $table) {
        if (in_array($table, $existingTables, true)) {
            echo '  ' . $table . ': affectations des comptes non conservés uniquement (' . tableCount($pdo, $table) . " lignes actuellement)\n";
        }
    }
    echo '  users: ' . (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role NOT IN ('admin', 'superadmin', 'caissier')")->fetchColumn() . " comptes supprimés\n";

    $preservedCounts = [];
    foreach (PRESERVED_TABLES as $table) {
        $preservedCounts[$table] = tableCount($pdo, $table);
    }
    $preservedRoleCounts = $pdo->query("SELECT role, COUNT(*) AS total FROM users WHERE role IN ('admin', 'superadmin', 'caissier') GROUP BY role ORDER BY role")->fetchAll(PDO::FETCH_KEY_PAIR);

    if (!$execute) {
        echo "\nSimulation uniquement. Aucune donnée ni structure modifiée.\n";
        echo 'Exécution explicite requise: php scripts/prepare_prod.php --execute --confirm=' . DB_NAME . "\n";
        exit(0);
    }
    if ($confirmation !== DB_NAME) {
        throw new RuntimeException('Confirmation incorrecte; la base n’a pas été modifiée.');
    }

    echo "\nCréation de la sauvegarde complète...\n";
    $backupPath = writeBackup($pdo, DB_NAME);
    echo 'Sauvegarde: ' . $backupPath . ' (' . filesize($backupPath) . " octets)\n";

    foreach ($purgeTables as $table) {
        enableAutoIncrement($pdo, $table);
    }

    $pdo->beginTransaction();
    foreach ($purgeTables as $table) {
        $pdo->exec('DELETE FROM ' . quoteIdentifier($table));
    }
    foreach (['user_departments', 'user_teaching_types'] as $table) {
        if (in_array($table, $existingTables, true)) {
            $pdo->exec("DELETE FROM " . quoteIdentifier($table) . " WHERE user_id NOT IN (SELECT id FROM users WHERE role IN ('admin', 'superadmin', 'caissier'))");
        }
    }
    $pdo->exec("DELETE FROM users WHERE role NOT IN ('admin', 'superadmin', 'caissier')");

    foreach ($purgeTables as $table) {
        if (tableCount($pdo, $table) !== 0) {
            throw new RuntimeException("Vérification avant validation échouée: {$table} n'est pas vide.");
        }
    }
    foreach ($preservedCounts as $table => $count) {
        if (tableCount($pdo, $table) !== $count) {
            throw new RuntimeException("Vérification avant validation échouée: la table protégée {$table} a changé de volume.");
        }
    }
    $currentRoleCounts = $pdo->query("SELECT role, COUNT(*) AS total FROM users WHERE role IN ('admin', 'superadmin', 'caissier') GROUP BY role ORDER BY role")->fetchAll(PDO::FETCH_KEY_PAIR);
    if ($currentRoleCounts !== $preservedRoleCounts) {
        throw new RuntimeException('Vérification avant validation échouée: les comptes protégés ont changé.');
    }
    $pdo->commit();

    foreach ($purgeTables as $table) {
        $column = $pdo->query("SELECT EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = " . $pdo->quote($table) . " AND COLUMN_NAME = 'id'")->fetchColumn();
        if ($column !== false && stripos((string) $column, 'auto_increment') === false) {
            throw new RuntimeException("Vérification après purge échouée: {$table}.id n'est pas AUTO_INCREMENT.");
        }
    }

    echo "\nPurge terminée et validée. La sauvegarde SQL complète est conservée dans le dossier temporaire système.\n";
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'ERREUR: ' . $exception->getMessage() . "\n");
    exit(1);
}