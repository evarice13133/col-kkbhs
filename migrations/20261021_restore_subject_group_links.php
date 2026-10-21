<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();
$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

$teachingTypeStmt = $db->prepare(
    "SELECT id FROM teaching_types WHERE code = 'SEC' AND actif = 1 ORDER BY id"
);
$teachingTypeStmt->execute();
$teachingTypeIds = array_map('intval', $teachingTypeStmt->fetchAll(PDO::FETCH_COLUMN));
if (count($teachingTypeIds) !== 1) {
    throw new RuntimeException('Le type d’enseignement secondaire actif doit être unique.');
}
$secondaryTeachingTypeId = $teachingTypeIds[0];

$db->exec(
    "CREATE TABLE IF NOT EXISTS subject_group_assignments (
        subject_id INT NOT NULL,
        subject_group_id INT NOT NULL,
        PRIMARY KEY (subject_id, subject_group_id),
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
        FOREIGN KEY (subject_group_id) REFERENCES subject_groups(id) ON DELETE CASCADE
    ) ENGINE=InnoDB"
);

$subjects = [];
$groupDefinitions = [];
$nullSectionLinks = [];
$scopeStmt = $db->query(
    "SELECT s.id AS subject_id, s.groupe, s.teaching_type_id AS subject_type_id,
            c.id AS class_id, c.section_id,
            COALESCE(d.teaching_form_id, c.teaching_form_id) AS teaching_form_id,
            COALESCE(c.teaching_type_id, s.teaching_type_id) AS scope_type_id
     FROM subjects s
     LEFT JOIN subject_classes sc ON sc.subject_id = s.id
     LEFT JOIN classes c ON c.id = sc.class_id
     LEFT JOIN departments d ON d.id = c.department_id
     ORDER BY s.id, c.section_id, c.id"
);

foreach ($scopeStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $subjectId = (int) $row['subject_id'];
    if (!isset($subjects[$subjectId])) {
        $label = trim((string) ($row['groupe'] ?? ''));
        if (!preg_match('/^Groupe ([1-3])$/', $label, $matches)) {
            throw new RuntimeException("Libellé de groupe non reconnu pour la matière {$subjectId}.");
        }
        if ((int) ($row['subject_type_id'] ?? 0) !== $secondaryTeachingTypeId) {
            throw new RuntimeException("Type d’enseignement inattendu pour la matière {$subjectId}.");
        }
        $subjects[$subjectId] = [
            'label' => $label,
            'position' => (int) $matches[1],
            'sections' => [],
        ];
    }

    if ($row['class_id'] === null) {
        throw new RuntimeException("La matière {$subjectId} n’a pas d’affectation de classe.");
    }
    if ($row['section_id'] === null) {
        $nullSectionLinks[$subjectId] = true;
        continue;
    }
    if ($row['teaching_form_id'] !== null) {
        throw new RuntimeException(
            "Une forme d’enseignement est renseignée pour la matière {$subjectId}; le plan doit être réévalué."
        );
    }
    if ((int) ($row['scope_type_id'] ?? 0) !== $secondaryTeachingTypeId) {
        throw new RuntimeException("Type de portée incohérent pour la matière {$subjectId}.");
    }

    $sectionId = (int) $row['section_id'];
    $subjects[$subjectId]['sections'][$sectionId] = true;
    $key = $subjects[$subjectId]['label'] . '|' . $sectionId;
    $groupDefinitions[$key] = [
        'label' => $subjects[$subjectId]['label'],
        'position' => $subjects[$subjectId]['position'],
        'section_id' => $sectionId,
    ];
}

if ($subjects === []) {
    throw new RuntimeException('Aucune matière à rattacher.');
}

foreach ($subjects as $subjectId => $subject) {
    if (count($subject['sections']) !== 1) {
        throw new RuntimeException(
            "La matière {$subjectId} n’a pas exactement une section non nulle."
        );
    }
}

$sectionStmt = $db->prepare('SELECT id FROM sections WHERE id = ? AND status = 1');
foreach ($groupDefinitions as $definition) {
    $sectionStmt->execute([$definition['section_id']]);
    if (!$sectionStmt->fetchColumn()) {
        throw new RuntimeException(
            "La section {$definition['section_id']} est introuvable ou inactive."
        );
    }
}

$backupTable = 'subject_group_repair_backup_20261021';
$backupExists = $db->query("SHOW TABLES LIKE '{$backupTable}'")->fetchColumn();
if (!$backupExists) {
    $db->exec(
        "CREATE TABLE `{$backupTable}` (
            subject_id INT NOT NULL PRIMARY KEY,
            original_subject_group_id INT NULL,
            legacy_group_label VARCHAR(100) NULL,
            backed_up_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB"
    );
}

$backupCount = (int) $db->query("SELECT COUNT(*) FROM `{$backupTable}`")->fetchColumn();
if ($backupCount === 0) {
    $db->exec(
        "INSERT INTO `{$backupTable}` (subject_id, original_subject_group_id, legacy_group_label)
         SELECT id, subject_group_id, groupe FROM subjects"
    );
    $backupCount = (int) $db->query("SELECT COUNT(*) FROM `{$backupTable}`")->fetchColumn();
}

$missingSnapshots = (int) $db->query(
    "SELECT COUNT(*) FROM subjects s
     LEFT JOIN `{$backupTable}` b ON b.subject_id = s.id
     WHERE b.subject_id IS NULL"
)->fetchColumn();
$extraSnapshots = (int) $db->query(
    "SELECT COUNT(*) FROM `{$backupTable}` b
     LEFT JOIN subjects s ON s.id = b.subject_id
     WHERE s.id IS NULL"
)->fetchColumn();
if ($backupCount !== count($subjects) || $missingSnapshots > 0 || $extraSnapshots > 0) {
    throw new RuntimeException(
        'La copie préalable ne correspond pas exactement aux matières actuelles; aucune relation ne sera modifiée.'
    );
}

$assignmentBackupTable = 'subject_group_assignment_repair_backup_20261021';
$assignmentBackupExists = $db->query("SHOW TABLES LIKE '{$assignmentBackupTable}'")->fetchColumn();
if (!$assignmentBackupExists) {
    $db->exec(
        "CREATE TABLE `{$assignmentBackupTable}` (
            subject_id INT NOT NULL,
            subject_group_id INT NOT NULL,
            backed_up_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (subject_id, subject_group_id)
        ) ENGINE=InnoDB"
    );
    $db->exec(
        "INSERT INTO `{$assignmentBackupTable}` (subject_id, subject_group_id)
         SELECT subject_id, subject_group_id FROM subject_group_assignments"
    );
}

$groupLookup = $db->prepare(
    'SELECT id, position FROM subject_groups
     WHERE libelle = ? AND teaching_type_id = ? AND teaching_form_id IS NULL
       AND section_id = ? AND status = 1'
);
$insertGroup = $db->prepare(
    'INSERT INTO subject_groups
     (libelle, teaching_type_id, teaching_form_id, section_id, position, status)
     VALUES (?, ?, NULL, ?, ?, 1)'
);
$updateSubject = $db->prepare(
    'UPDATE subjects SET subject_group_id = ? WHERE id = ?
     AND (subject_group_id IS NULL OR subject_group_id <> ?)'
);
$existingSubjectGroup = $db->prepare(
    'SELECT subject_group_id FROM subjects WHERE id = ?'
);
$existingAssignments = $db->prepare(
    'SELECT subject_group_id FROM subject_group_assignments WHERE subject_id = ?'
);
$insertAssignment = $db->prepare(
    'INSERT IGNORE INTO subject_group_assignments (subject_id, subject_group_id) VALUES (?, ?)'
);
$groupIds = [];
$updatedSubjects = 0;
$insertedAssignments = 0;

$db->beginTransaction();
try {
    foreach ($groupDefinitions as $key => $definition) {
        $groupLookup->execute([
            $definition['label'],
            $secondaryTeachingTypeId,
            $definition['section_id'],
        ]);
        $matches = $groupLookup->fetchAll(PDO::FETCH_ASSOC);
        if (count($matches) > 1) {
            throw new RuntimeException("Plusieurs groupes actifs correspondent à la portée {$key}.");
        }
        if ($matches !== []) {
            if ((int) $matches[0]['position'] !== $definition['position']) {
                throw new RuntimeException("La position du groupe existant {$key} est incohérente.");
            }
            $groupIds[$key] = (int) $matches[0]['id'];
            continue;
        }

        $insertGroup->execute([
            $definition['label'],
            $secondaryTeachingTypeId,
            $definition['section_id'],
            $definition['position'],
        ]);
        $groupIds[$key] = (int) $db->lastInsertId();
    }

    foreach ($subjects as $subjectId => $subject) {
        $sectionId = (int) array_key_first($subject['sections']);
        $key = $subject['label'] . '|' . $sectionId;
        if (!isset($groupIds[$key])) {
            throw new RuntimeException("Aucun groupe cible défini pour la matière {$subjectId}.");
        }
        $groupId = $groupIds[$key];

        $existingSubjectGroup->execute([$subjectId]);
        $currentGroupId = $existingSubjectGroup->fetchColumn();
        if ($currentGroupId !== false && !in_array((int) $currentGroupId, [0, $groupId], true)) {
            throw new RuntimeException(
                "La matière {$subjectId} possède déjà un autre groupe principal; rattachement conservé."
            );
        }

        $existingAssignments->execute([$subjectId]);
        foreach ($existingAssignments->fetchAll(PDO::FETCH_COLUMN) as $assignedGroupId) {
            if ((int) $assignedGroupId !== $groupId) {
                throw new RuntimeException(
                    "La matière {$subjectId} possède déjà un autre rattachement secondaire; aucune ligne ne sera remplacée."
                );
            }
        }

        $updateSubject->execute([$groupId, $subjectId, $groupId]);
        $updatedSubjects += $updateSubject->rowCount();
        $insertAssignment->execute([$subjectId, $groupId]);
        $insertedAssignments += $insertAssignment->rowCount();
    }

    $invalidLinks = (int) $db->query(
        "SELECT COUNT(*) FROM subjects s
         LEFT JOIN subject_groups sg ON sg.id = s.subject_group_id
         WHERE sg.id IS NULL OR sg.libelle <> s.groupe
            OR sg.teaching_type_id <> {$secondaryTeachingTypeId}
            OR sg.teaching_form_id IS NOT NULL
            OR NOT EXISTS (
                SELECT 1 FROM subject_classes sc
                JOIN classes c ON c.id = sc.class_id
                WHERE sc.subject_id = s.id AND c.section_id = sg.section_id
            )"
    )->fetchColumn();
    if ($invalidLinks > 0) {
        throw new RuntimeException("La vérification transactionnelle a détecté {$invalidLinks} lien(s) incohérent(s).");
    }

    $db->commit();
} catch (Throwable $error) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    throw $error;
}

$groupIndex = $db->query(
    "SHOW INDEX FROM subjects WHERE Key_name = 'idx_subjects_subject_group'"
)->fetchColumn();
if (!$groupIndex) {
    $db->exec('ALTER TABLE subjects ADD INDEX idx_subjects_subject_group (subject_group_id)');
}

$groupConstraint = $db->query(
    "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
     WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'subjects'
       AND CONSTRAINT_NAME = 'fk_subject_group' AND CONSTRAINT_TYPE = 'FOREIGN KEY'"
)->fetchColumn();
if (!$groupConstraint) {
    $db->exec(
        'ALTER TABLE subjects
         ADD CONSTRAINT fk_subject_group
         FOREIGN KEY (subject_group_id) REFERENCES subject_groups(id)
         ON DELETE SET NULL ON UPDATE CASCADE'
    );
}

$verifiedLinks = (int) $db->query(
    "SELECT COUNT(*) FROM subjects s
     JOIN subject_groups sg ON sg.id = s.subject_group_id
     WHERE sg.libelle = s.groupe
       AND sg.teaching_type_id = {$secondaryTeachingTypeId}
       AND sg.teaching_form_id IS NULL"
)->fetchColumn();
 $missingPivotLinks = (int) $db->query(
    "SELECT COUNT(*) FROM subjects s
     WHERE NOT EXISTS (
         SELECT 1 FROM subject_group_assignments sga
         WHERE sga.subject_id = s.id AND sga.subject_group_id = s.subject_group_id
     )"
)->fetchColumn();
if ($verifiedLinks !== count($subjects) || $missingPivotLinks !== 0) {
    throw new RuntimeException('La vérification finale des relations de groupes a échoué.');
}

echo 'Sauvegarde préalable: ' . $backupTable . ' (' . $backupCount . " matières).\n";
echo 'Groupes secondaires actifs créés ou réutilisés: ' . count($groupIds) . ".\n";
echo 'Matières rattachées correctement: ' . $verifiedLinks . ".\n";
echo 'Liens de matières mis à jour: ' . $updatedSubjects . ".\n";
echo 'Liens secondaires ajoutés: ' . $insertedAssignments . ".\n";
echo 'Matières ayant aussi des classes sans section laissées intactes: ' . count($nullSectionLinks) . ".\n";
echo "Index et clé étrangère subject_group_id vérifiés.\n";
