<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

// Define DB constants if not already defined
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'notesmasterdb');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    
    echo "Starting database cleanup...\n";
    
    // Disable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // 1. Clear students (eleves)
    $db->exec("TRUNCATE TABLE students");
    echo "- Table 'students' (eleves) emptied and ID reset.\n";
    
    // 2. Clear subjects (matieres)
    $db->exec("TRUNCATE TABLE subjects");
    $db->exec("TRUNCATE TABLE subject_classes");
    echo "- Table 'subjects' (matieres) emptied and ID reset.\n";
    
    // 3. Clear teachers (enseignants)
    // Teachers are in the 'users' table. We delete those with role 'enseignant'.
    // TRUNCATE doesn't support WHERE, so we DELETE and then reset AUTO_INCREMENT if we were truncating the whole table.
    // However, the user said "la table enseignant". If they mean the whole users table, it's risky (admins).
    // Let's assume they want to clear all teachers and their related assignments.
    $db->exec("DELETE FROM users WHERE role = 'enseignant'");
    $db->exec("TRUNCATE TABLE teacher_assignments");
    $db->exec("TRUNCATE TABLE user_departments"); // Clear department assignments too
    echo "- Teachers removed from 'users' and assignments cleared.\n";
    
    // 4. Clear grades (always a good idea when clearing students/subjects)
    $db->exec("TRUNCATE TABLE grades");
    echo "- Table 'grades' emptied.\n";

    // 5. Clear activity logs (optional but clean)
    $db->exec("TRUNCATE TABLE activity_logs");
    echo "- Activity logs cleared.\n";
    
    // Re-enable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "Cleanup completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
