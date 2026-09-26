<?php
require 'config/config.php';
$pdo = App\Core\Database::getInstance()->getConnection();
$stmt = $pdo->query("SHOW COLUMNS FROM classes");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo $col['Field'] . "\n";
}
