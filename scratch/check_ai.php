<?php
require_once 'config/config.php';
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $tables = ['school_fees', 'class_installments', 'fee_installments', 'installment_deadlines'];
    foreach($tables as $t) {
        $stmt = $pdo->query("SHOW CREATE TABLE " . $t);
        if ($row = $stmt->fetch()) {
            if (strpos($row[1], 'AUTO_INCREMENT') === false) {
                echo "Table $t is MISSING AUTO_INCREMENT!\n";
            } else {
                echo "Table $t HAS AUTO_INCREMENT.\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
