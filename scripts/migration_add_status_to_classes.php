<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    echo "Starting Migration: Adding status column to classes...\n";

    $stmt = $db->query("DESCRIBE classes");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('status', $columns)) {
        echo "Adding status to classes...\n";
        $db->exec("ALTER TABLE classes ADD COLUMN status TINYINT(1) NOT NULL DEFAULT 1");
        echo "Column status added to classes successfully.\n";
    } else {
        echo "status already exists in classes.\n";
    }

    echo "Migration for classes status completed successfully!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
