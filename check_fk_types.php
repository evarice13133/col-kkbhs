<?php
$db = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "Types de colonnes:\n";
$col1 = $db->query('SHOW COLUMNS FROM grades WHERE Field="teacher_id"')->fetch(PDO::FETCH_ASSOC);
echo "  teacher_id dans grades: " . $col1['Type'] . "\n";

$col2 = $db->query('SHOW COLUMNS FROM users WHERE Field="id"')->fetch(PDO::FETCH_ASSOC);
echo "  id dans users: " . $col2['Type'] . "\n";

// Vérifier le charset de la table grades
$tableInfo = $db->query("SHOW CREATE TABLE grades")->fetch(PDO::FETCH_ASSOC);
echo "\nSHOW CREATE TABLE grades:\n";
echo $tableInfo['Create Table'] . "\n";
?>
