<?php
/**
 * Migration Script for Discount Types Module
 * Run via: php scripts/run_discount_types_migration.php
 */

require_once __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "=== DÉBUT DE LA MIGRATION DES TYPES DE RÉDUCTIONS ===\n";

    // 1. Création de la table discount_types
    echo "Création de la table 'discount_types'...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS discount_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT DEFAULT NULL,
        comment TEXT DEFAULT NULL,
        status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
        created_by INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_name (name),
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    echo "  - Table 'discount_types' opérationnelle.\n";

    // 2. Altération des tables existantes pour ajouter discount_type_id
    $tables = ['student_discounts', 'class_discounts', 'student_scholarships', 'class_scholarships'];

    foreach ($tables as $table) {
        echo "Vérification de la table '$table'...\n";
        
        // Vérifier si la colonne existe déjà
        $columns = [];
        $q = $pdo->query("DESCRIBE $table");
        while ($row = $q->fetch()) {
            $columns[] = $row['Field'];
        }

        if (!in_array('discount_type_id', $columns)) {
            $afterCol = (strpos($table, 'class_') === 0) ? 'class_id' : 'student_id';
            $pdo->exec("ALTER TABLE $table ADD COLUMN discount_type_id INT DEFAULT NULL AFTER $afterCol");
            echo "  - Colonne 'discount_type_id' ajoutée à '$table' après '$afterCol'.\n";

            // Ajouter la clé étrangère
            try {
                $pdo->exec("ALTER TABLE $table ADD CONSTRAINT fk_{$table}_type_id FOREIGN KEY (discount_type_id) REFERENCES discount_types(id) ON DELETE RESTRICT");
                echo "  - Clé étrangère ajoutée à '$table'.\n";
            } catch (\Exception $ex) {
                echo "  - Note : Échec de l'ajout direct de la contrainte (peut-être déjà existante) : " . $ex->getMessage() . "\n";
            }
        } else {
            echo "  - La colonne 'discount_type_id' existe déjà dans '$table'.\n";
        }
    }

    // 3. Migration des motifs existants
    echo "Migration des motifs existants (motive) vers les types de réductions...\n";
    
    // Récupérer tous les motifs uniques depuis les 4 tables
    $motives = [];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT DISTINCT motive FROM $table WHERE motive IS NOT NULL AND TRIM(motive) != ''");
        while ($m = $stmt->fetchColumn()) {
            $cleaned = trim($m);
            if (!in_array($cleaned, $motives) && $cleaned !== '') {
                $motives[] = $cleaned;
            }
        }
    }

    echo "  - " . count($motives) . " motifs distincts identifiés.\n";

    // Insérer chaque motif comme un type et lier les lignes existantes
    $typeInsert = $pdo->prepare("INSERT INTO discount_types (name, description, comment, status) VALUES (?, ?, ?, 'active')");
    $typeSelect = $pdo->prepare("SELECT id FROM discount_types WHERE name = ?");

    foreach ($motives as $motive) {
        // Vérifier si le type existe déjà
        $typeSelect->execute([$motive]);
        $typeId = $typeSelect->fetchColumn();

        if (!$typeId) {
            $typeInsert->execute([
                $motive,
                "Créé automatiquement à partir de l'historique financier.",
                "Migration système"
            ]);
            $typeId = $pdo->lastInsertId();
            echo "  - Type créé : '$motive' (ID: $typeId)\n";
        } else {
            echo "  - Type existant : '$motive' (ID: $typeId)\n";
        }

        // Mettre à jour les enregistrements dans les 4 tables pour ce motif
        foreach ($tables as $table) {
            $upd = $pdo->prepare("UPDATE $table SET discount_type_id = ? WHERE motive = ? AND discount_type_id IS NULL");
            $upd->execute([$typeId, $motive]);
            $rows = $upd->rowCount();
            if ($rows > 0) {
                echo "    * $rows ligne(s) mise(s) à jour dans la table '$table' pour le motif '$motive'.\n";
            }
        }
    }

    echo "=== MIGRATION DES TYPES DE RÉDUCTIONS TERMINÉE AVEC SUCCÈS ===\n";

} catch (\Exception $e) {
    echo "ERREUR DE MIGRATION : " . $e->getMessage() . "\n";
    exit(1);
}
