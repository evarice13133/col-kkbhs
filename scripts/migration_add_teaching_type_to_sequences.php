<?php
/**
 * Migration : Ajout de teaching_type_id et des dates de période à la table sequences
 * 
 * Instructions de déploiement :
 * 1. Exécuter ce script via php scratch/MigrationRunner.php
 * 2. Ou exécuter manuellement les requêtes SQL ci-dessous dans la BDD.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    echo "Starting Migration: Adding teaching_type_id, start_date, and end_date to sequences table...\n";

    // 1. Check if teaching_types table exists to get default 'Secondaire' id
    $stmtTT = $db->query("SELECT id FROM teaching_types WHERE code = 'ESG' OR LOWER(nom) LIKE '%secondaire%' LIMIT 1");
    $defaultTeachingTypeId = $stmtTT ? $stmtTT->fetchColumn() : null;
    if (!$defaultTeachingTypeId) {
        $defaultTeachingTypeId = 1; // Fallback
    }

    // 2. Add columns if not existing
    $stmt = $db->query("DESCRIBE sequences");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('teaching_type_id', $columns)) {
        echo "Adding teaching_type_id column...\n";
        $db->exec("ALTER TABLE sequences ADD COLUMN teaching_type_id INT NULL AFTER id");
        
        // Associate existing sequences to default teaching_type_id
        $db->exec("UPDATE sequences SET teaching_type_id = {$defaultTeachingTypeId} WHERE teaching_type_id IS NULL");
        
        // Add foreign key constraint
        try {
            $db->exec("ALTER TABLE sequences ADD CONSTRAINT fk_sequence_teaching_type FOREIGN KEY (teaching_type_id) REFERENCES teaching_types(id) ON DELETE SET NULL");
        } catch (\Exception $ex) {
            echo "Warning (FK): " . $ex->getMessage() . "\n";
        }
        echo "teaching_type_id added and populated.\n";
    }

    if (!in_array('start_date', $columns)) {
        echo "Adding start_date column...\n";
        $db->exec("ALTER TABLE sequences ADD COLUMN start_date DATE NULL AFTER short_label");
    }

    if (!in_array('end_date', $columns)) {
        echo "Adding end_date column...\n";
        $db->exec("ALTER TABLE sequences ADD COLUMN end_date DATE NULL AFTER start_date");
    }

    // Allow label to not be unique globally if scope changes per teaching type (optional/safe drop of unique on label if exists)
    try {
        $db->exec("ALTER TABLE sequences DROP INDEX label");
    } catch (\Exception $ex) {
        // Index might not exist or have a different name, ignore
    }
    try {
        $db->exec("ALTER TABLE sequences DROP INDEX code");
    } catch (\Exception $ex) {
        // Index might not exist or have a different name, ignore
    }

    echo "Migration for sequences completed successfully!\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
