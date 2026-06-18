<?php
require_once 'c:/laragon/www/Nouveau dossier/copobimat.camertech/config/config.php';
require_once 'c:/laragon/www/Nouveau dossier/copobimat.camertech/vendor/autoload.php';

use App\Core\Database;
$db = Database::getInstance()->getConnection();

try {
    echo "=== Columns for student_installments ===\n";
    $stmt = $db->query("DESCRIBE `student_installments`");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
