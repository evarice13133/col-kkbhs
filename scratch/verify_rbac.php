<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

$db = App\Core\Database::getInstance()->getConnection();

echo "=== ROLES ===\n";
$roles = $db->query('SELECT role_code, role_name FROM roles ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
foreach ($roles as $r) {
    echo "  " . $r['role_code'] . " => " . $r['role_name'] . "\n";
}

echo "\n=== PERMISSIONS COUNT ===\n";
$perms = $db->query('SELECT COUNT(*) FROM permissions')->fetchColumn();
echo "  Total permissions: " . $perms . "\n";

echo "\n=== ROLE PERMISSIONS SUMMARY ===\n";
$rp = $db->query('SELECT r.role_code, COUNT(rp.permission_id) as cnt FROM roles r LEFT JOIN role_permissions rp ON rp.role_id = r.id GROUP BY r.role_code ORDER BY r.id')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rp as $r) {
    echo "  " . $r['role_code'] . ": " . $r['cnt'] . " permissions\n";
}
