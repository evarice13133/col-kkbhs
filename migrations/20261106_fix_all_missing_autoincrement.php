<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;
use PDO;

$db = Database::getInstance()->getConnection();
$tables = [
    'discount_types',
    'fee_installments',
    'grades',
    'insolvent_students',
    'installment_deadlines',
    'payment_receipts',
    'roles',
    'sections',
    'student_installments',
    'student_payment_allocations',
    'system_job_runs',
    'teacher_contracts',
    'teaching_types',
    'timetable_weeks',
    'whatsapp_consents',
    'whatsapp_logs',
    'whatsapp_settings',
];

foreach ($tables as $table) {
    $exists = $db->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
    );
    $exists->execute([$table]);
    if ((int) $exists->fetchColumn() === 0) {
        continue;
    }

    $column = $db->prepare(
        'SELECT COLUMN_TYPE, COLUMN_KEY, IS_NULLABLE, EXTRA
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = \'id\''
    );
    $column->execute([$table]);
    $definition = $column->fetch(PDO::FETCH_ASSOC);
    if (!$definition || $definition['COLUMN_KEY'] !== 'PRI' || $definition['IS_NULLABLE'] !== 'NO') {
        throw new RuntimeException("{$table}.id doit être une clé primaire NOT NULL.");
    }

    $zeroIds = $db->query("SELECT id FROM `{$table}` WHERE id = 0")->fetchAll(PDO::FETCH_COLUMN);
    if ($zeroIds !== []) {
        $nextId = (int) $db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM `{$table}`")->fetchColumn();
        $update = $db->prepare("UPDATE `{$table}` SET id = ? WHERE id = 0");
        foreach ($zeroIds as $_) {
            $update->execute([$nextId++]);
        }
    }

    $columnType = preg_replace('/[^a-zA-Z0-9_(), ]/', '', (string) $definition['COLUMN_TYPE']);
    if (stripos((string) $definition['EXTRA'], 'auto_increment') === false) {
        $db->exec("ALTER TABLE `{$table}` MODIFY id {$columnType} NOT NULL AUTO_INCREMENT");
    }

    $nextId = (int) $db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM `{$table}`")->fetchColumn();
    $db->exec("ALTER TABLE `{$table}` AUTO_INCREMENT = " . max(1, $nextId));
}

echo "Les clés AUTO_INCREMENT des tables métier sont synchronisées.\n";
