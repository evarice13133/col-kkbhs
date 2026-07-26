<?php
/**
 * Migration : Création de la table subject_groups et ajout de subject_group_id à subjects
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    echo "Starting Migration: Creating subject_groups table...\n";

    // 1. Déterminer le type d'enseignement 'Secondaire' par défaut
    $stmtTT = $db->query("SELECT id FROM teaching_types WHERE code = 'ESG' OR LOWER(nom) LIKE '%secondaire%' LIMIT 1");
    $secondaireId = $stmtTT ? $stmtTT->fetchColumn() : 1;
    if (!$secondaireId) $secondaireId = 1;

    // 2. Créer la table subject_groups
    $db->exec("CREATE TABLE IF NOT EXISTS subject_groups (
        id INT AUTO_INCREMENT PRIMARY KEY,
        libelle VARCHAR(100) NOT NULL,
        teaching_type_id INT NULL,
        status TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (teaching_type_id) REFERENCES teaching_types(id) ON DELETE SET NULL
    )");

    // Initialiser les groupes existants pour le Secondaire s'il n'y a encore rien dans subject_groups
    $countGroups = $db->query("SELECT COUNT(*) FROM subject_groups")->fetchColumn();
    if ($countGroups == 0) {
        $defaultGroups = ['Groupe 1', 'Groupe 2', 'Groupe 3'];
        $stmtIns = $db->prepare("INSERT INTO subject_groups (libelle, teaching_type_id, status) VALUES (?, ?, 1)");
        foreach ($defaultGroups as $grpLabel) {
            $stmtIns->execute([$grpLabel, $secondaireId]);
        }
        echo "Default groups initialized for Secondaire.\n";
    }

    // 3. Ajouter la colonne subject_group_id à la table subjects si absente
    $stmtCols = $db->query("DESCRIBE subjects");
    $columns = $stmtCols->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('subject_group_id', $columns)) {
        echo "Adding subject_group_id column to subjects table...\n";
        $db->exec("ALTER TABLE subjects ADD COLUMN subject_group_id INT NULL AFTER groupe");

        // Mapper les valeurs existantes du champ string 'groupe' vers subject_group_id
        $groupsMap = $db->query("SELECT id, libelle FROM subject_groups WHERE teaching_type_id = {$secondaireId}")->fetchAll(PDO::FETCH_KEY_PAIR);
        // Ex: ['Groupe 1' => 1, 'Groupe 2' => 2, ...]
        if (!empty($groupsMap)) {
            foreach ($groupsMap as $libelle => $grpId) {
                $stmtUpdate = $db->prepare("UPDATE subjects SET subject_group_id = ? WHERE (groupe = ? OR groupe LIKE ?) AND subject_group_id IS NULL");
                $stmtUpdate->execute([$grpId, $libelle, '%' . $libelle . '%']);
            }
        }

        // Associer toutes les matières restantes sans subject_group_id au premier groupe Secondaire
        $firstGrpId = (int)(reset($groupsMap) ?: 1);
        $db->exec("UPDATE subjects SET subject_group_id = " . $firstGrpId . " WHERE subject_group_id IS NULL");

        try {
            $db->exec("ALTER TABLE subjects ADD CONSTRAINT fk_subject_group FOREIGN KEY (subject_group_id) REFERENCES subject_groups(id) ON DELETE SET NULL");
        } catch (\Exception $ex) {
            echo "Warning FK: " . $ex->getMessage() . "\n";
        }
    }

    echo "Migration for subject_groups completed successfully!\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
