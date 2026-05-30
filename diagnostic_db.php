<?php
/**
 * Script de diagnostic pour analyser la structure des tables et les contraintes de clé étrangère
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "==== DIAGNOSTIC DE BASE DE DONNÉES ====\n\n";
    
    // 1. Structure de la table grades
    echo "1. STRUCTURE DE LA TABLE 'grades':\n";
    echo str_repeat("-", 80) . "\n";
    $result = $db->query("DESCRIBE grades");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo sprintf("%-20s %-15s %-5s %-5s\n", 
            $col['Field'], 
            $col['Type'], 
            $col['Null'] === 'YES' ? 'NULL' : 'NOT NULL',
            $col['Key']
        );
    }
    
    // 2. Contraintes de clé étrangère pour la table grades
    echo "\n2. CONTRAINTES DE CLÉ ÉTRANGÈRE POUR 'grades':\n";
    echo str_repeat("-", 80) . "\n";
    $fkQuery = "SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = 'grades' AND REFERENCED_TABLE_NAME IS NOT NULL";
    $fkResult = $db->query($fkQuery)->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($fkResult)) {
        echo "Aucune contrainte de clé étrangère trouvée.\n";
    } else {
        foreach ($fkResult as $fk) {
            echo "  Contrainte: {$fk['CONSTRAINT_NAME']}\n";
            echo "    Colonne locale: {$fk['COLUMN_NAME']}\n";
            echo "    Table référencée: {$fk['REFERENCED_TABLE_NAME']}\n";
            echo "    Colonne référencée: {$fk['REFERENCED_COLUMN_NAME']}\n";
            // Récupérer les règles de delete/update
            $rulesQuery = "SELECT * FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS 
                          WHERE CONSTRAINT_NAME = ? AND CONSTRAINT_SCHEMA = ?";
            $rulesStmt = $db->prepare($rulesQuery);
            $rulesStmt->execute([$fk['CONSTRAINT_NAME'], DB_NAME]);
            $rules = $rulesStmt->fetch(PDO::FETCH_ASSOC);
            if ($rules) {
                echo "    ON DELETE: " . ($rules['DELETE_RULE'] ?? 'N/A') . "\n";
                echo "    ON UPDATE: " . ($rules['UPDATE_RULE'] ?? 'N/A') . "\n";
            }
            echo "\n";
        }
    }
    
    // 3. Structure de la table teachers (si elle existe)
    echo "3. STRUCTURE DE LA TABLE 'users' (enseignants):\n";
    echo str_repeat("-", 80) . "\n";
    try {
        $result = $db->query("DESCRIBE users");
        $columns = $result->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo sprintf("%-20s %-15s %-5s\n", 
                $col['Field'], 
                $col['Type'], 
                $col['Null'] === 'YES' ? 'NULL' : 'NOT NULL'
            );
        }
    } catch (Exception $e) {
        echo "Table 'users' introuvable: " . $e->getMessage() . "\n";
    }
    
    // 4. Analyse du problème : Notes avec teacher_id supprimé
    echo "\n4. ANALYSE DU PROBLÈME - NOTES ORPHELINES:\n";
    echo str_repeat("-", 80) . "\n";
    
    $orphanedQuery = "SELECT COUNT(*) as count FROM grades WHERE teacher_id NOT IN (SELECT id FROM users)";
    $orphaned = $db->query($orphanedQuery)->fetch(PDO::FETCH_ASSOC);
    echo "Notes avec teacher_id qui n'existe pas dans users: " . $orphaned['count'] . "\n";
    
    // 5. Quelles sont les créateurs de notes?
    echo "\n5. ANALYSE - CRÉATEURS DE NOTES (created_by):\n";
    echo str_repeat("-", 80) . "\n";
    $checkColumn = $db->query("SHOW COLUMNS FROM grades LIKE 'created_by'")->rowCount();
    if ($checkColumn > 0) {
        echo "La colonne 'created_by' existe dans la table 'grades'\n";
        $createdByQuery = "SELECT created_by, COUNT(*) as count FROM grades GROUP BY created_by";
        $createdByResult = $db->query($createdByQuery)->fetchAll(PDO::FETCH_ASSOC);
        foreach ($createdByResult as $row) {
            echo "  created_by: {$row['created_by']} => {$row['count']} notes\n";
        }
    } else {
        echo "La colonne 'created_by' N'EXISTE PAS dans la table 'grades'\n";
    }
    
    // 6. Vérifier si created_by_type existe
    $checkColumnType = $db->query("SHOW COLUMNS FROM grades LIKE 'created_by_type'")->rowCount();
    if ($checkColumnType > 0) {
        echo "\nLa colonne 'created_by_type' existe dans la table 'grades'\n";
    } else {
        echo "\nLa colonne 'created_by_type' N'EXISTE PAS dans la table 'grades'\n";
    }
    
    // 7. Vérifier les snapshots
    $checkSnapshot = $db->query("SHOW COLUMNS FROM grades LIKE '%snapshot%'")->rowCount();
    if ($checkSnapshot > 0) {
        echo "\nDes colonnes snapshot existent:\n";
        $snapshotResult = $db->query("SHOW COLUMNS FROM grades LIKE '%snapshot%'")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($snapshotResult as $snap) {
            echo "  - {$snap['Field']}\n";
        }
    } else {
        echo "\nAucune colonne snapshot n'existe dans 'grades'\n";
    }
    
    // 8. Compter les notes totales
    echo "\n6. STATISTIQUES GÉNÉRALES:\n";
    echo str_repeat("-", 80) . "\n";
    $totalGrades = $db->query("SELECT COUNT(*) as count FROM grades")->fetch(PDO::FETCH_ASSOC);
    echo "Total des notes en base: " . $totalGrades['count'] . "\n";
    
    $totalTeachers = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'enseignant'")->fetch(PDO::FETCH_ASSOC);
    echo "Total des enseignants actifs: " . $totalTeachers['count'] . "\n";
    
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
?>
