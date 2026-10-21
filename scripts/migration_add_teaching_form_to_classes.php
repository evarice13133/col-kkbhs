<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

use PDO;

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    echo "=== Migration: Ajout de teaching_form_id à la table classes ===\n\n";

    // Vérifier si la colonne existe déjà
    $stmt = $db->query("DESCRIBE classes");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (in_array('teaching_form_id', $columns)) {
        echo "⚠ La colonne teaching_form_id existe déjà dans la table classes.\n";
        echo "Migration ignorée.\n\n";
        exit(0);
    }

    // Ajouter la colonne teaching_form_id
    echo "Étape 1: Ajout de la colonne teaching_form_id...\n";
    $db->exec("ALTER TABLE classes ADD COLUMN teaching_form_id INT DEFAULT NULL AFTER teaching_type_id");
    echo "✓ Colonne teaching_form_id ajoutée\n\n";

    // Ajouter la clé étrangère
    echo "Étape 2: Ajout de la clé étrangère vers teaching_forms...\n";
    $db->exec("ALTER TABLE classes ADD CONSTRAINT fk_classes_teaching_form FOREIGN KEY (teaching_form_id) REFERENCES teaching_forms(id) ON UPDATE CASCADE");
    echo "✓ Clé étrangère ajoutée\n\n";

    // Ajouter un index pour améliorer les performances
    echo "Étape 3: Ajout de l'index sur teaching_form_id...\n";
    $db->exec("ALTER TABLE classes ADD INDEX idx_teaching_form_id (teaching_form_id)");
    echo "✓ Index ajouté\n\n";

    echo "=== Migration terminée avec succès ===\n\n";

} catch (Exception $e) {
    echo "❌ Erreur lors de la migration: " . $e->getMessage() . "\n";
    exit(1);
}
