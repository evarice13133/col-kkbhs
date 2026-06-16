<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    
    echo "Démarrage de la migration : Module Type Enseignement\n";
    echo str_repeat("-", 50) . "\n";

    // 1. Création des tables
    echo "1. Création de la table teaching_types...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS teaching_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            code VARCHAR(20) NOT NULL UNIQUE,
            position INT DEFAULT 0,
            actif TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    echo "1.b Création de la table pivot user_teaching_types...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS user_teaching_types (
            user_id INT NOT NULL,
            teaching_type_id INT NOT NULL,
            PRIMARY KEY (user_id, teaching_type_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 2. Insertion des données par défaut
    echo "2. Insertion des données par défaut...\n";
    $stmt = $db->prepare("INSERT IGNORE INTO teaching_types (nom, code, position) VALUES (?, ?, ?)");
    $stmt->execute(['Maternelle', 'MAT', 1]);
    $stmt->execute(['Primaire', 'PRI', 2]);
    $stmt->execute(['Secondaire', 'SEC', 3]);

    // Récupérer l'ID de "Secondaire"
    $secId = $db->query("SELECT id FROM teaching_types WHERE code = 'SEC'")->fetchColumn();
    if (!$secId) {
        throw new Exception("Impossible de trouver le type d'enseignement Secondaire.");
    }

    // 3. Ajout des colonnes et mise à jour
    $tablesToUpdate = ['students', 'classes', 'subjects', 'teacher_assignments', 'grades'];
    
    foreach ($tablesToUpdate as $table) {
        echo "3. Traitement de la table `$table`...\n";
        
        // Vérifier si la colonne existe
        $colExists = $db->query("SHOW COLUMNS FROM `$table` LIKE 'teaching_type_id'")->fetch();
        
        if (!$colExists) {
            echo "   - Ajout de la colonne teaching_type_id\n";
            $db->exec("ALTER TABLE `$table` ADD COLUMN teaching_type_id INT NULL");
            
            echo "   - Mise à jour des enregistrements existants avec ID Secondaire ($secId)\n";
            $db->exec("UPDATE `$table` SET teaching_type_id = $secId");
        } else {
            echo "   - La colonne existe déjà. Mise à jour des valeurs NULL vers Secondaire ($secId)\n";
            $db->exec("UPDATE `$table` SET teaching_type_id = $secId WHERE teaching_type_id IS NULL");
        }
    }

    // 4. Associer tous les enseignants existants au Secondaire
    echo "4. Association des enseignants existants au Secondaire...\n";
    $enseignants = $db->query("SELECT id FROM users WHERE role = 'enseignant'")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($enseignants)) {
        $insertStmt = $db->prepare("INSERT IGNORE INTO user_teaching_types (user_id, teaching_type_id) VALUES (?, ?)");
        $count = 0;
        foreach ($enseignants as $userId) {
            $insertStmt->execute([$userId, $secId]);
            if ($insertStmt->rowCount() > 0) {
                $count++;
            }
        }
        echo "   - $count enseignants associés au Secondaire.\n";
    }

    echo str_repeat("-", 50) . "\n";
    echo "Migration terminée avec succès !\n";

} catch (PDOException $e) {
    echo "Erreur de base de données : " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
