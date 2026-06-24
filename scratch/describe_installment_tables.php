<?php
require_once 'c:/laragon/www/Nouveau dossier/copobimat.camertech/config/config.php';
require_once 'c:/laragon/www/Nouveau dossier/copobimat.camertech/vendor/autoload.php';

use App\Core\Database;
$db = Database::getInstance()->getConnection();

function describeTable($db, $tableName) {
    try {
        echo "=== Columns for $tableName ===\n";
        $stmt = $db->query("DESCRIBE `$tableName`");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
    } catch (\Exception $e) {
        echo "Error: $tableName - " . $e->getMessage() . "\n";
    }
}

describeTable($db, 'fee_installments');
describeTable($db, 'installment_deadlines');
