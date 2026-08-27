<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
$pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$nom = 'TEST_AUTO_' . date('Ymd_His');
$stmt = $pdo->prepare("INSERT INTO subjects (nom, coefficient, groupe, status, created_at) VALUES (?, 1, 'Groupe 1', 1, NOW())");
$stmt->execute([$nom]);
$last = (int)$pdo->lastInsertId();
echo "Inserted subject '$nom' with lastInsertId = $last\n";
$sel = $pdo->prepare("SELECT id, nom, created_at FROM subjects WHERE nom = ? ORDER BY id DESC LIMIT 1");
$sel->execute([$nom]);
$row = $sel->fetch(PDO::FETCH_ASSOC);
if ($row) {
    echo "DB select found id={$row['id']} nom={$row['nom']} created_at={$row['created_at']}\n";
} else {
    echo "No row found by select.\n";
}
