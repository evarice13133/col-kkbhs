<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();
$column = $db->query("SHOW COLUMNS FROM users WHERE Field = 'id'")->fetch(PDO::FETCH_ASSOC);

if (!$column) {
    throw new RuntimeException('La colonne users.id est absente.');
}
if ($column['Key'] !== 'PRI' || $column['Null'] !== 'NO') {
    throw new RuntimeException('users.id doit rester une clé primaire NOT NULL; migration interrompue sans modification.');
}

if (stripos((string) $column['Extra'], 'auto_increment') === false) {
    $columnType = preg_replace('/[^a-zA-Z0-9_(), ]/', '', (string) $column['Type']);
    $db->exec("ALTER TABLE users MODIFY COLUMN id {$columnType} NOT NULL AUTO_INCREMENT");
}

$nextId = (int) $db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM users')->fetchColumn();
$db->exec('ALTER TABLE users AUTO_INCREMENT = ' . max(1, $nextId));

echo "users.id est AUTO_INCREMENT; prochain identifiant: {$nextId}.\n";