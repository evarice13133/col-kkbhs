<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    echo "Starting Migration: Adding status column to sections...\n";

    // 1. Ensure status exists in sections
    $stmt = $db->query("DESCRIBE sections");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('status', $columns)) {
        echo "Adding status to sections...\n";
        $db->exec("ALTER TABLE sections ADD COLUMN status TINYINT(1) NOT NULL DEFAULT 1");
        echo "Column status added to sections successfully.\n";
    } else {
        echo "status already exists in sections.\n";
    }

    echo "Migration for sections status completed successfully!\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
