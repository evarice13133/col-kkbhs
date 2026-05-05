<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

// Define DB constants if not already defined (sometimes they are in config.php)
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'notesmasterdb');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    
    // Add short_label to sequences if not exists
    $result = $db->query("SHOW COLUMNS FROM sequences LIKE 'short_label'");
    if ($result->rowCount() == 0) {
        $db->exec("ALTER TABLE sequences ADD COLUMN short_label VARCHAR(20) AFTER label");
        echo "Added short_label column to sequences table.\n";
    } else {
        echo "short_label column already exists.\n";
    }

    // Initialize short_label for existing sequences
    $db->exec("UPDATE sequences SET short_label = label WHERE short_label IS NULL OR short_label = ''");
    echo "Initialized short_label for existing sequences.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
