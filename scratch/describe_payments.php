<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    echo "\nCOLUMNS FOR students:\n";
    $stmt = $db->query("DESCRIBE students");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
