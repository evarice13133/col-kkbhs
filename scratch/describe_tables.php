<?php
require_once 'config/config.php';
require_once 'vendor/autoload.php';

use App\Core\Database;
$db = Database::getInstance()->getConnection();

$tables = ['students', 'enrollments', 'payments', 'financial_history', 'settings'];
foreach ($tables as $tableName) {
    try {
        echo "=== Columns for $tableName ===\n";
        $stmt = $db->query("DESCRIBE `$tableName`");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  {$row['Field']} - {$row['Type']} - Null: {$row['Null']} - Key: {$row['Key']} - Default: {$row['Default']}\n";
        }
    } catch (\Exception $e) {
        echo "Error displaying $tableName: " . $e->getMessage() . "\n";
    }
}
