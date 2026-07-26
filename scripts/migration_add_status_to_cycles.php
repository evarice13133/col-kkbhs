<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    echo "Starting Migration: Adding status column to cycles...\n";

    // 1. Ensure status exists in cycles
    $stmt = $db->query("DESCRIBE cycles");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('status', $columns)) {
        echo "Adding status to cycles...\n";
        $db->exec("ALTER TABLE cycles ADD COLUMN status TINYINT(1) NOT NULL DEFAULT 1");
        echo "Column status added to cycles successfully.\n";
    } else {
        echo "status already exists in cycles.\n";
    }

    echo "Migration for cycles status completed successfully!\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
