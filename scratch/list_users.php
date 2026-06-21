<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();
$users = $db->query('SELECT id, username, nom, prenom, role FROM users')->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    echo "ID: {$u['id']} | Username: {$u['username']} | Name: {$u['nom']} {$u['prenom']} | Role: {$u['role']}\n";
}
