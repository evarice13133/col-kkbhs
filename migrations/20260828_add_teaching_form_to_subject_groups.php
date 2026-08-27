<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

// Ajouter la colonne teaching_form_id si elle n'existe pas
$col = $db->query("SHOW COLUMNS FROM subject_groups LIKE 'teaching_form_id'")->fetchColumn();
if (!$col) {
    $db->exec("ALTER TABLE subject_groups ADD COLUMN teaching_form_id INT NULL AFTER teaching_type_id");
    $db->exec("ALTER TABLE subject_groups ADD INDEX idx_subject_groups_teaching_form (teaching_form_id)");

    // Tenter d'ajouter la contrainte FK (silencieusement si déjà présente)
    try {
        $db->exec("ALTER TABLE subject_groups ADD CONSTRAINT fk_subject_groups_teaching_form FOREIGN KEY (teaching_form_id) REFERENCES teaching_forms(id) ON DELETE RESTRICT ON UPDATE CASCADE");
    } catch (PDOException $e) {
        echo "Warning: FK fk_subject_groups_teaching_form already exists or could not be added: " . $e->getMessage() . "\n";
    }

    // Pour les groupes existants, tenter d'inférer une forme d'enseignement
    $stmt = $db->query("SELECT id, teaching_type_id FROM subject_groups WHERE teaching_form_id IS NULL AND teaching_type_id IS NOT NULL");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $update = $db->prepare("UPDATE subject_groups SET teaching_form_id = ? WHERE id = ?");
    $findForm = $db->prepare("SELECT id FROM teaching_forms WHERE teaching_type_id = ? AND status = 1 ORDER BY id LIMIT 1");
    foreach ($rows as $r) {
        $findForm->execute([(int)$r['teaching_type_id']]);
        $tf = $findForm->fetchColumn();
        if ($tf) {
            $update->execute([(int)$tf, (int)$r['id']]);
        }
    }

    echo "Migration add teaching_form_id to subject_groups executed.\n";
} else {
    echo "Column teaching_form_id already exists on subject_groups.\n";
}

exit(0);
