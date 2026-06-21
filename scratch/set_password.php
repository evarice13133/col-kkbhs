<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

// Hash using BCRYPT or ARGON2ID. AuthController uses password_hash with BCRYPT, User model uses PASSWORD_ARGON2ID. Both are verified via password_verify. Let's use BCRYPT for simplicity and compatibility.
$hash = password_hash('password', PASSWORD_BCRYPT);

$usersToUpdate = ['sup', 'admin', 'mira'];

foreach ($usersToUpdate as $username) {
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = ?");
    $stmt->execute([$hash, $username]);
    echo "Password updated for username: $username\n";
}
