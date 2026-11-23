<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

$tables = [
    'enrollments',
    'payments',
    'student_payments',
    'financial_history',
];

try {
    foreach ($tables as $table) {
        $tableCheck = $db->prepare(
            'SELECT COUNT(*)
             FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = ?'
        );
        $tableCheck->execute([$table]);
        if ((int) $tableCheck->fetchColumn() === 0) {
            continue;
        }

        $zeroIds = $db->query("SELECT id FROM `{$table}` WHERE id = 0")->fetchAll(PDO::FETCH_COLUMN);
        if ($zeroIds !== []) {
            $nextId = (int) $db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM `{$table}`")->fetchColumn();
            $update = $db->prepare("UPDATE `{$table}` SET id = ? WHERE id = 0");
            foreach ($zeroIds as $_) {
                $update->execute([$nextId++]);
            }
        }

        $column = $db->prepare(
            'SELECT column_type, extra
             FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND column_name = \'id\''
        );
        $column->execute([$table]);
        $idDefinition = $column->fetch(PDO::FETCH_ASSOC);
        if (!$idDefinition) {
            throw new RuntimeException("La table {$table} ne possède pas de colonne id.");
        }

        if (stripos((string) $idDefinition['extra'], 'auto_increment') === false) {
            $columnType = (string) $idDefinition['column_type'];
            $db->exec("ALTER TABLE `{$table}` MODIFY id {$columnType} NOT NULL AUTO_INCREMENT");
        }

        $nextId = (int) $db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM `{$table}`")->fetchColumn();
        $db->exec("ALTER TABLE `{$table}` AUTO_INCREMENT = " . max(1, $nextId));
    }

    echo "Les clés primaires des tables d'inscription ont été réparées.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Échec de la réparation des clés primaires : {$e->getMessage()}\n");
    exit(1);
}
