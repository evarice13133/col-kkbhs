<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

echo "=== MIGRATION RELATION CYCLE <-> NIVEAUX (cycle_levels) ===\n\n";

try {
    $db = Database::getInstance()->getConnection();

    // 1. Création de la table pivot `cycle_levels`
    echo "1. Création de la table pivot 'cycle_levels'...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS cycle_levels (
            cycle_id INT NOT NULL,
            level_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (cycle_id, level_id),
            CONSTRAINT fk_cycle_levels_cycle FOREIGN KEY (cycle_id) REFERENCES cycles(id) ON DELETE CASCADE,
            CONSTRAINT fk_cycle_levels_level FOREIGN KEY (level_id) REFERENCES levels(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "   -> Table 'cycle_levels' créée ou déjà existante.\n\n";

    // 2. Migration des données existantes
    echo "2. Migration des associations pour tous les cycles existants...\n";

    // Récupérer tous les cycles
    $cycles = $db->query("SELECT id, nom FROM cycles")->fetchAll(PDO::FETCH_ASSOC);

    // Déterminer le Niveau 1 par défaut (code = '1', 'L1', 'N1' ou le tout premier niveau dans levels)
    $stmtDefaultLevel = $db->query("
        SELECT id FROM levels 
        WHERE status = 1 AND (code IN ('1', 'L1', 'N1', 'Niveau 1') OR libelle_fr LIKE '%1%' OR libelle_en LIKE '%1%')
        ORDER BY id ASC LIMIT 1
    ");
    $defaultLevelId = $stmtDefaultLevel->fetchColumn();

    if (!$defaultLevelId) {
        $stmtFirstLevel = $db->query("SELECT id FROM levels WHERE status = 1 ORDER BY id ASC LIMIT 1");
        $defaultLevelId = $stmtFirstLevel->fetchColumn();
    }

    $insertStmt = $db->prepare("INSERT IGNORE INTO cycle_levels (cycle_id, level_id) VALUES (?, ?)");

    $migratedCount = 0;
    foreach ($cycles as $cycle) {
        $cycleId = (int)$cycle['id'];

        // a) Associer aux niveaux des classes rattachées à ce cycle
        $classLevelsStmt = $db->prepare("
            SELECT DISTINCT level_id FROM classes 
            WHERE cycle_id = ? AND level_id IS NOT NULL
        ");
        $classLevelsStmt->execute([$cycleId]);
        $levelsFromClasses = $classLevelsStmt->fetchAll(PDO::FETCH_COLUMN);

        $associatedLevels = array_unique(array_filter($levelsFromClasses));

        // b) Si aucun niveau trouvé via les classes, attribuer le Niveau 1 par défaut
        if (empty($associatedLevels) && $defaultLevelId) {
            $associatedLevels[] = (int)$defaultLevelId;
        }

        foreach ($associatedLevels as $lvlId) {
            $insertStmt->execute([$cycleId, (int)$lvlId]);
            $migratedCount++;
        }
    }

    echo "   -> $migratedCount association(s) cycle <-> niveau enregistrée(s).\n\n";
    echo "=== MIGRATION COMPLÉTÉE AVEC SUCCÈS ! ===\n";

} catch (Exception $e) {
    echo "ERREUR MIGRATION : " . $e->getMessage() . "\n";
    exit(1);
}
