<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

$tables = ['students', 'enrollments', 'payments', 'student_payments', 'student_discounts', 'student_scholarships'];
$issues = [];

foreach ($tables as $table) {
    $exists = $db->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}'")->fetchColumn();
    if ((int) $exists === 0) {
        continue;
    }

    $zeroCount = (int) $db->query("SELECT COUNT(*) FROM `{$table}` WHERE id = 0")->fetchColumn();
    if ($zeroCount > 0) {
        $issues[] = "{$table}: id=0 present ({$zeroCount})";
    }

    $duplicateCount = (int) $db->query("SELECT COUNT(*) FROM (SELECT id FROM `{$table}` GROUP BY id HAVING COUNT(*) > 1) t")->fetchColumn();
    if ($duplicateCount > 0) {
        $issues[] = "{$table}: duplicate ids present ({$duplicateCount})";
    }

    $ai = $db->query("SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}'")->fetchColumn();
    if ($ai === false || (string) $ai === '0') {
        $issues[] = "{$table}: AUTO_INCREMENT invalid or 0";
    }
}

if ($issues) {
    fwrite(STDERR, "Student registration integrity issue detected:\n" . implode("\n", $issues) . "\n");
    exit(1);
}

echo "Student registration integrity OK.\n";
exit(0);
