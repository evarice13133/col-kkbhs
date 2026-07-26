<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    echo "Starting Migration: Integrating teaching_type_id to cycles...\n";

    // 1. Ensure teaching_type_id exists in cycles
    $stmt = $db->query("DESCRIBE cycles");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('teaching_type_id', $columns)) {
        echo "Adding teaching_type_id to cycles...\n";
        $db->exec("ALTER TABLE cycles ADD COLUMN teaching_type_id INT NULL");
        $db->exec("ALTER TABLE cycles ADD CONSTRAINT fk_cycle_teaching_type FOREIGN KEY (teaching_type_id) REFERENCES teaching_types(id) ON DELETE SET NULL");
        echo "Column teaching_type_id and foreign key added to cycles successfully.\n";
    } else {
        echo "teaching_type_id already exists in cycles.\n";
    }

    // 2. Find default teaching type ID (e.g., 'Secondaire' or first active teaching type)
    $stmt = $db->prepare("SELECT id FROM teaching_types WHERE nom LIKE '%Secondaire%' AND actif = 1 LIMIT 1");
    $stmt->execute();
    $defaultTypeId = $stmt->fetchColumn();

    if (!$defaultTypeId) {
        $stmt = $db->query("SELECT id FROM teaching_types WHERE actif = 1 ORDER BY position ASC LIMIT 1");
        $defaultTypeId = $stmt->fetchColumn();
    }

    if ($defaultTypeId) {
        echo "Found default teaching_type_id: $defaultTypeId\n";
        // Update existing cycles without a teaching_type_id
        $affected = $db->exec("UPDATE cycles SET teaching_type_id = $defaultTypeId WHERE teaching_type_id IS NULL");
        echo "Updated $affected existing cycles to default teaching_type_id ($defaultTypeId).\n";
    } else {
        echo "Warning: No active teaching type found. Could not set default teaching_type_id for cycles.\n";
    }

    echo "Migration for cycles completed successfully!\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
