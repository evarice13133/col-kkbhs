<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';

$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);

echo "=== FINDING ADMIN USER ===\n\n";

// Find admin user
$stmt = $db->query("SELECT id, email, role FROM users WHERE role = 'admin' LIMIT 1");
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo "✓ Admin user found:\n";
    echo "  ID: " . $admin['id'] . "\n";
    echo "  Email: " . $admin['email'] . "\n";
    echo "  Role: " . $admin['role'] . "\n";
} else {
    echo "✗ No admin found, looking for any user...\n";
    $stmt = $db->query("SELECT id, email FROM users LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - ID=" . $row['id'] . ": " . $row['email'] . "\n";
    }
}

?>
