<?php
$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== STRUCTURE DE subject_classes ===\n\n";

$structure = $pdo->query("SHOW CREATE TABLE subject_classes")->fetch(PDO::FETCH_ASSOC);
echo $structure['Create Table'] . "\n\n";

echo "=== FIN ===\n";
