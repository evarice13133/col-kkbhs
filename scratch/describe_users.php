<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
$db = Database::getInstance()->getConnection();

$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);
foreach ($tables as $t) {
    if (in_array($t, ['payments', 'enrollments', 'students', 'classes', 'student_payments', 'settings', 'sequences'])) {
        echo "=== $t columns ===\n";
        $cols = $db->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            echo "  " . $c['Field'] . " (" . $c['Type'] . ")\n";
        }
    }
}
