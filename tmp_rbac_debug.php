<?php
require __DIR__ . '/config/config.php';
require __DIR__ . '/vendor/autoload.php';

$db = App\Core\Database::getInstance()->getConnection();
$perm = $db->query("SELECT id, perm_code, status FROM permissions WHERE perm_code='manage_teaching_forms' ORDER BY id LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$admin = $db->query("SELECT id, role_code FROM roles WHERE role_code='admin' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$grant = $admin ? $db->query("SELECT p.id, p.perm_code FROM role_permissions rp JOIN permissions p ON p.id = rp.permission_id WHERE rp.role_id = " . (int)$admin['id'] . " AND p.perm_code='manage_teaching_forms'")->fetchAll(PDO::FETCH_ASSOC) : [];
$showPerm = $db->query("SHOW CREATE TABLE permissions")->fetch(PDO::FETCH_ASSOC);
$showRoles = $db->query("SHOW CREATE TABLE roles")->fetch(PDO::FETCH_ASSOC);

var_dump($perm);
var_dump($admin);
var_dump($grant);
var_dump($showPerm['Create Table'] ?? null);
var_dump($showRoles['Create Table'] ?? null);
