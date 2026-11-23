<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    $column = $db->query("SHOW COLUMNS FROM expenses LIKE 'teaching_type_id'")->fetch(PDO::FETCH_ASSOC);
    if (!$column) {
        $db->exec("ALTER TABLE expenses ADD COLUMN teaching_type_id INT NULL DEFAULT NULL AFTER academic_year_id");
    }

    $index = $db->query("SHOW INDEX FROM expenses WHERE Key_name = 'idx_expenses_year_teaching_type'")->fetch(PDO::FETCH_ASSOC);
    if (!$index) {
        $db->exec("CREATE INDEX idx_expenses_year_teaching_type ON expenses (academic_year_id, teaching_type_id)");
    }

    echo "Expenses now support teaching-type scope; existing expenses remain shared.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Expense teaching-type migration failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
