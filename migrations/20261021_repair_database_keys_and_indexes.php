<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

$duplicateGroups = (int) $db->query(
    "SELECT COUNT(*) FROM (
         SELECT section_id, teaching_form_id, position
         FROM subject_groups
         WHERE section_id IS NOT NULL AND teaching_form_id IS NOT NULL AND position IS NOT NULL
         GROUP BY section_id, teaching_form_id, position
         HAVING COUNT(*) > 1
     ) duplicates"
)->fetchColumn();
if ($duplicateGroups > 0) {
    throw new RuntimeException('Des positions de groupes sont dupliquées dans une même section et forme.');
}

$scopedGroupIndex = $db->query(
    "SHOW INDEX FROM subject_groups WHERE Key_name = 'uq_subject_groups_section_form_position'"
)->fetchColumn();
if (!$scopedGroupIndex) {
    $db->exec(
        'ALTER TABLE subject_groups
         ADD UNIQUE KEY uq_subject_groups_section_form_position (section_id, teaching_form_id, position)'
    );
}

$legacyGroupIndex = $db->query(
    "SHOW INDEX FROM subject_groups WHERE Key_name = 'uq_subject_groups_form_position'"
)->fetchColumn();
if ($legacyGroupIndex) {
    $db->exec('ALTER TABLE subject_groups DROP INDEX uq_subject_groups_form_position');
}

$subjectClassIndex = $db->query(
    "SHOW INDEX FROM subject_classes WHERE Key_name = 'idx_subject_classes_class_year_subject'"
)->fetchColumn();
if (!$subjectClassIndex) {
    $db->exec(
        'ALTER TABLE subject_classes
         ADD INDEX idx_subject_classes_class_year_subject (class_id, academic_year_id, subject_id)'
    );
}

foreach ([
    'conseils_classe',
    'decisions_fin_annee',
    'historique_modifications_conseil',
    'historique_passages',
] as $table) {
    $primaryKey = $db->query(
        'SHOW INDEX FROM `' . $table . "` WHERE Key_name = 'PRIMARY'"
    )->fetchColumn();
    if ($primaryKey) {
        continue;
    }

    $rowCount = (int) $db->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
    if ($rowCount !== 0) {
        throw new RuntimeException(
            "La table {$table} contient des données et n'a pas de clé primaire; correction manuelle requise."
        );
    }

    $db->exec(
        'ALTER TABLE `' . $table . '` MODIFY id INT NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)'
    );
}

echo "Index et clés primaires réparés sans modification de données métier.\n";
