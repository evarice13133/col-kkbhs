<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

$db = \App\Core\Database::getInstance()->getConnection();

echo "--- CLEANING DUPLICATE SEQUENCES ---\n";
// Supprimer les doublons stricts (même code ET même teaching_type_id)
$db->exec("
    DELETE s1 FROM sequences s1
    INNER JOIN sequences s2 
    ON s1.code = s2.code 
    AND (s1.teaching_type_id = s2.teaching_type_id OR (s1.teaching_type_id IS NULL AND s2.teaching_type_id IS NULL))
    AND s1.id > s2.id
");

echo "Cleaned duplicates successfully!\n";
$rows = $db->query("SELECT id, teaching_type_id, code, label FROM sequences")->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
