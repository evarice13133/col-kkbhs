<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();
$column = $db->query("SHOW COLUMNS FROM subjects LIKE 'groupe'")->fetch(PDO::FETCH_ASSOC);

if (!$column) {
    throw new RuntimeException("La colonne subjects.groupe est introuvable.");
}

$currentLength = 0;
if (preg_match('/^varchar\((\d+)\)$/i', (string) $column['Type'], $matches)) {
    $currentLength = (int) $matches[1];
}

if ($currentLength < 100) {
    $db->exec("ALTER TABLE subjects MODIFY groupe VARCHAR(100) NOT NULL DEFAULT 'Groupe 1'");
}

$db->exec(
    "UPDATE subjects s
     JOIN subject_groups sg ON sg.id = s.subject_group_id
     SET s.groupe = sg.libelle
     WHERE s.subject_group_id IS NOT NULL"
);

echo "Libellés de groupes des matières stockés sans troncature.\n";
