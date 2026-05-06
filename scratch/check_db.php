<?php
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    $tables = ['subjects', 'subject_groups'];
    foreach ($tables as $table) {
        $stmt = $db->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Columns in '$table':\n";
        foreach ($columns as $col) {
            echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
