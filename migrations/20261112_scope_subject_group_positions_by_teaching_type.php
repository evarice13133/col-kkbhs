<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    foreach ([
        'uq_subject_groups_form_position',
        'uq_subject_groups_section_form_position',
    ] as $legacyIndex) {
        $indexStmt = $db->prepare('SHOW INDEX FROM subject_groups WHERE Key_name = ?');
        $indexStmt->execute([$legacyIndex]);
        if ($indexStmt->fetch(PDO::FETCH_ASSOC)) {
            $db->exec("ALTER TABLE subject_groups DROP INDEX `{$legacyIndex}`");
        }
    }

    $scopedIndex = $db->query(
        "SHOW INDEX FROM subject_groups WHERE Key_name = 'uq_subject_groups_type_section_form_position'"
    )->fetch(PDO::FETCH_ASSOC);
    if (!$scopedIndex) {
        $db->exec(
            'ALTER TABLE subject_groups
             ADD UNIQUE KEY uq_subject_groups_type_section_form_position
             (teaching_type_id, section_id, teaching_form_id, position)'
        );
    }

    echo "Subject-group positions are now unique within teaching type, section, and form.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Subject-group position migration failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
