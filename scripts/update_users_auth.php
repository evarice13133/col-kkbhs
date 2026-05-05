<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Updating 'users' table structure...\n";
    
    // 1. Add username column if not exists
    $db->exec("ALTER TABLE users ADD COLUMN username VARCHAR(50) UNIQUE AFTER prenom");
    
    // 2. Make email nullable
    $db->exec("ALTER TABLE users MODIFY email VARCHAR(100) NULL");
    
    // 3. Initialize username with email prefix for existing users
    $stmt = $db->query("SELECT id, email FROM users WHERE username IS NULL");
    $updateStmt = $db->prepare("UPDATE users SET username = ? WHERE id = ?");
    
    while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($user['email']) {
            $username = explode('@', $user['email'])[0];
            // Handle duplicates
            $tempUsername = $username;
            $counter = 1;
            while (true) {
                $check = $db->prepare("SELECT id FROM users WHERE username = ?");
                $check->execute([$tempUsername]);
                if (!$check->fetch()) break;
                $tempUsername = $username . $counter++;
            }
            $updateStmt->execute([$tempUsername, $user['id']]);
        }
    }
    
    echo "Successfully updated 'users' table. Email is now optional, and login uses 'username'.\n";
} catch (PDOException $e) {
    die("Error during migration: " . $e->getMessage() . "\n");
}
