<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/vendor/autoload.php';

$db = App\Core\Database::getInstance()->getConnection();

function describeTable($db, $tableName) {
    echo "=== DESCRIBE $tableName ===\n";
    try {
        $stmt = $db->query("DESCRIBE $tableName");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo "  {$col['Field']} - {$col['Type']} - Null: {$col['Null']} - Default: " . ($col['Default'] ?? 'NULL') . "\n";
        }
    } catch (Exception $e) {
        echo "Error describing $tableName: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

describeTable($db, 'students');
describeTable($db, 'enrollments');
describeTable($db, 'payments');
?>
