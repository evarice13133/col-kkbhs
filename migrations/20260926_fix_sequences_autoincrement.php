<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();
$column = $db->query("SHOW COLUMNS FROM sequences WHERE Field = 'id'")->fetch(PDO::FETCH_ASSOC);

if (!$column) {
    throw new RuntimeException('La colonne sequences.id est absente.');
}
if ($column['Key'] !== 'PRI' || $column['Null'] !== 'NO') {
    throw new RuntimeException('sequences.id doit rester une clé primaire NOT NULL; migration interrompue sans modification.');
}

$zeroIds = $db->query('SELECT id FROM sequences WHERE id = 0')->fetchAll(PDO::FETCH_COLUMN);
if ($zeroIds) {
    $nextId = (int) $db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM sequences')->fetchColumn();
    $update = $db->prepare('UPDATE sequences SET id = ? WHERE id = 0');
    foreach ($zeroIds as $_) {
        $update->execute([$nextId++]);
    }
}

if (stripos((string) $column['Extra'], 'auto_increment') === false) {
    $columnType = preg_replace('/[^a-zA-Z0-9_(), ]/', '', (string) $column['Type']);
    $db->exec("ALTER TABLE sequences MODIFY COLUMN id {$columnType} NOT NULL AUTO_INCREMENT");
}

$nextId = (int) $db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM sequences')->fetchColumn();
$db->exec('ALTER TABLE sequences AUTO_INCREMENT = ' . max(1, $nextId));

echo "sequences.id est AUTO_INCREMENT; prochain identifiant: {$nextId}.\n";