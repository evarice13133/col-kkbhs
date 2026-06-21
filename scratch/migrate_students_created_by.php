<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if column already exists
    $check = $db->query("SHOW COLUMNS FROM students LIKE 'created_by'")->fetch();
    if (!$check) {
        echo "Adding 'created_by' column to 'students' table...\n";
        $db->exec("ALTER TABLE students ADD COLUMN created_by INT NULL");
        $db->exec("ALTER TABLE students ADD CONSTRAINT fk_students_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL");
        echo "Successfully added 'created_by' column and constraint.\n";
    } else {
        echo "'created_by' column already exists in 'students' table.\n";
    }
} catch (PDOException $e) {
    die("Database migration error: " . $e->getMessage() . "\n");
}
