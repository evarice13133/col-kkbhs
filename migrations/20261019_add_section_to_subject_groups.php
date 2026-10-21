<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

$sectionColumn = $db->query("SHOW COLUMNS FROM subject_groups LIKE 'section_id'")->fetchColumn();
if (!$sectionColumn) {
    $db->exec("ALTER TABLE subject_groups ADD COLUMN section_id INT NULL AFTER teaching_form_id");
}

$sectionIndex = $db->query("SHOW INDEX FROM subject_groups WHERE Key_name = 'idx_subject_groups_section'")->fetchColumn();
if (!$sectionIndex) {
    $db->exec("ALTER TABLE subject_groups ADD INDEX idx_subject_groups_section (section_id)");
}

$sectionConstraint = $db->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'subject_groups' AND CONSTRAINT_NAME = 'fk_subject_groups_section' AND CONSTRAINT_TYPE = 'FOREIGN KEY'")->fetchColumn();
if (!$sectionConstraint) {
    $db->exec("ALTER TABLE subject_groups ADD CONSTRAINT fk_subject_groups_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE RESTRICT ON UPDATE CASCADE");
}

$legacyGroups = $db->query("SELECT id FROM subject_groups WHERE section_id IS NULL")->fetchAll(PDO::FETCH_COLUMN);
$sectionsStmt = $db->prepare("SELECT DISTINCT c.section_id
                              FROM subjects s
                              JOIN subject_classes sc ON sc.subject_id = s.id
                              JOIN classes c ON c.id = sc.class_id
                              WHERE (s.subject_group_id = ? OR EXISTS (
                                  SELECT 1 FROM subject_group_assignments sga
                                  WHERE sga.subject_id = s.id AND sga.subject_group_id = ?
                              ))");
$assignSectionStmt = $db->prepare("UPDATE subject_groups SET section_id = ? WHERE id = ? AND section_id IS NULL");
foreach ($legacyGroups as $groupId) {
    $sectionsStmt->execute([(int) $groupId, (int) $groupId]);
    $sectionIds = $sectionsStmt->fetchAll(PDO::FETCH_COLUMN);
    if (count($sectionIds) === 1 && (int) $sectionIds[0] > 0) {
        $assignSectionStmt->execute([(int) $sectionIds[0], (int) $groupId]);
    }
}

$legacyPositionIndex = $db->query("SHOW INDEX FROM subject_groups WHERE Key_name = 'uq_subject_groups_form_position'")->fetchColumn();
if ($legacyPositionIndex) {
    $db->exec("ALTER TABLE subject_groups DROP INDEX uq_subject_groups_form_position");
}

$scopedPositionIndex = $db->query("SHOW INDEX FROM subject_groups WHERE Key_name = 'uq_subject_groups_section_form_position'")->fetchColumn();
if (!$scopedPositionIndex) {
    $db->exec("ALTER TABLE subject_groups ADD UNIQUE KEY uq_subject_groups_section_form_position (section_id, teaching_form_id, position)");
}

$classYearIndex = $db->query("SHOW INDEX FROM subject_classes WHERE Key_name = 'idx_subject_classes_class_year_subject'")->fetchColumn();
if (!$classYearIndex) {
    $db->exec("ALTER TABLE subject_classes ADD INDEX idx_subject_classes_class_year_subject (class_id, academic_year_id, subject_id)");
}

echo "Portée des groupes de matières par section ajoutée. Les groupes existants restent à affecter explicitement.\n";