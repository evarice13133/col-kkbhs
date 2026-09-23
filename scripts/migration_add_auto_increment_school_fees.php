<?php
// scripts/migration_add_auto_increment_school_fees.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    $sql = "ALTER TABLE school_fees MODIFY COLUMN id INT NOT NULL AUTO_INCREMENT;";
    $db->exec($sql);
    echo "Added AUTO_INCREMENT to school_fees table.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        // If there are duplicate 0s, we might need to delete or renumber them first.
        echo "Could not add AUTO_INCREMENT due to duplicate IDs. Cleaning up...\n";
        
        // Remove the entries with id = 0 or renumber them
        $db->exec("SET @count = 0;");
        $db->exec("UPDATE school_fees SET id = (@count:= @count + 1) WHERE id = 0;");
        
        // Try again
        $db->exec("ALTER TABLE school_fees MODIFY COLUMN id INT NOT NULL AUTO_INCREMENT;");
        echo "Added AUTO_INCREMENT to school_fees table after cleanup.\n";
    } else {
        throw $e;
    }
}
