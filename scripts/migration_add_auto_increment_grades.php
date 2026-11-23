<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT COLUMN_TYPE, COLUMN_KEY, EXTRA
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'grades'
          AND COLUMN_NAME = 'id'
    ");
    $stmt->execute();
    $column = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$column || $column['COLUMN_KEY'] !== 'PRI') {
        throw new RuntimeException('The grades.id primary key column was not found.');
    }

    if (stripos((string) $column['EXTRA'], 'auto_increment') !== false) {
        echo "grades.id already has AUTO_INCREMENT.\n";
        exit(0);
    }

    $columnType = preg_replace('/[^a-zA-Z0-9_(), ]/', '', (string) $column['COLUMN_TYPE']);
    if ($columnType === '') {
        throw new RuntimeException('Unable to determine the grades.id column type.');
    }

    $db->exec("ALTER TABLE grades MODIFY COLUMN id {$columnType} NOT NULL AUTO_INCREMENT");

    $extra = $db->query("
        SELECT EXTRA
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'grades'
          AND COLUMN_NAME = 'id'
    ")->fetchColumn();

    if (stripos((string) $extra, 'auto_increment') === false) {
        throw new RuntimeException('AUTO_INCREMENT was not applied to grades.id.');
    }

    echo "Added AUTO_INCREMENT to grades.id.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Migration failed: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
