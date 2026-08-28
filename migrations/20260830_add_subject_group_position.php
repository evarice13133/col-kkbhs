<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

$column = $db->query("SHOW COLUMNS FROM subject_groups LIKE 'position'")->fetchColumn();
if (!$column) {
    $db->exec("ALTER TABLE subject_groups ADD COLUMN position INT NOT NULL DEFAULT 1 AFTER teaching_form_id");
}

// Initialiser l’ordre existant sans modifier les rattachements ni les matières.
$groups = $db->query("SELECT id, teaching_form_id FROM subject_groups ORDER BY teaching_form_id IS NULL, teaching_form_id, id")->fetchAll(PDO::FETCH_ASSOC);
$nextPositions = [];
$update = $db->prepare("UPDATE subject_groups SET position = ? WHERE id = ?");
foreach ($groups as $group) {
    $formKey = $group['teaching_form_id'] === null ? 'legacy_' . (int) $group['id'] : (string) (int) $group['teaching_form_id'];
    $nextPositions[$formKey] = ($nextPositions[$formKey] ?? 0) + 1;
    $update->execute([$nextPositions[$formKey], (int) $group['id']]);
}

$indexExists = $db->query("SHOW INDEX FROM subject_groups WHERE Key_name = 'uq_subject_groups_form_position'")->fetchColumn();
if (!$indexExists) {
    $db->exec("ALTER TABLE subject_groups ADD UNIQUE KEY uq_subject_groups_form_position (teaching_form_id, position)");
}

echo "Position des groupes de modules synchronisee.\n";