<?php
/**
 * Renumérote le groupe subject_groups.id = 0 vers un nouvel id positif (max+1).
 * Vérifications:
 * - s'assure qu'il existe bien un groupe id=0
 * - vérifie qu'aucune subject ne référence id=0
 * - met à jour l'autoincrement si nécessaire
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
use App\Core\Database;

$db = Database::getInstance()->getConnection();

$exists = $db->query("SELECT COUNT(*) FROM subject_groups WHERE id = 0")->fetchColumn();
if (!$exists) {
    echo "No subject_group with id=0 found.\n";
    exit(0);
}

$refCount = $db->query("SELECT COUNT(*) FROM subjects WHERE subject_group_id = 0")->fetchColumn();
if ($refCount > 0) {
    echo "Cannot renumber: {$refCount} subjects reference subject_group_id = 0. Aborting.\n";
    exit(1);
}

$maxId = (int)$db->query("SELECT COALESCE(MAX(id), 0) FROM subject_groups")->fetchColumn();
$newId = $maxId + 1;

try {
    $db->beginTransaction();
    // Update the PK value
    $stmt = $db->prepare("UPDATE subject_groups SET id = :new WHERE id = 0");
    $stmt->execute([':new' => $newId]);

    // Ensure AUTO_INCREMENT is at least newId+1
    $ai = (int)$db->query("SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subject_groups'")->fetchColumn();
    if ($ai <= $newId) {
        $db->exec("ALTER TABLE subject_groups AUTO_INCREMENT = " . ($newId + 1));
    }

    $db->commit();
    echo "Renumbered subject_groups id 0 -> {$newId}. AUTO_INCREMENT adjusted if needed.\n";
    echo "Please refresh the /subject-groups page.\n";
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "Error during renumbering: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);
