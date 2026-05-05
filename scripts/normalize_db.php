<?php
/**
 * Migration Script: Database Normalization
 * Removes redundant columns and enforces sequence_id link in grades.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    echo "Démarrage de la normalisation de la base de données...\n";

    // 1. Assurer la présence de la table sequences
    $db->exec("CREATE TABLE IF NOT EXISTS sequences (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(20) NOT NULL UNIQUE,
        label VARCHAR(100) NOT NULL UNIQUE,
        trimestre TINYINT NOT NULL,
        position TINYINT NOT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1
    )");
    echo "✅ Table sequences vérifiée.\n";

    // Remplissage par défaut si vide
    $check = $db->query("SELECT COUNT(*) FROM sequences")->fetchColumn();
    if ($check == 0) {
        $db->exec("INSERT INTO sequences (code, label, trimestre, position, is_active) VALUES 
            ('SEQ1', 'Trimestre 1 - Sequence 1', 1, 1, 1),
            ('SEQ2', 'Trimestre 1 - Sequence 2', 1, 2, 1),
            ('SEQ3', 'Trimestre 2 - Sequence 3', 2, 3, 1),
            ('SEQ4', 'Trimestre 2 - Sequence 4', 2, 4, 1),
            ('SEQ5', 'Trimestre 3 - Sequence 5', 3, 5, 1),
            ('SEQ6', 'Trimestre 3 - Sequence 6', 3, 6, 1)");
        echo "✅ Séquences par défaut insérées.\n";
    }

    // 2. Normalisation de la table students (Suppression cycle_id et section_id)
    // On vérifie d'abord si les colonnes existent pour éviter les erreurs
    $cols = $db->query("SHOW COLUMNS FROM students")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('cycle_id', $cols)) {
        $db->exec("ALTER TABLE students DROP FOREIGN KEY students_ibfk_1"); // Facultatif si le nom varie, mais tentons
        $db->exec("ALTER TABLE students DROP COLUMN cycle_id");
        echo "✅ Colonne cycle_id supprimée de students.\n";
    }
    if (in_array('section_id', $cols)) {
        // Tentative de suppression sans connaître le nom exact de la FK (on ignore si erreur)
        try { $db->exec("ALTER TABLE students DROP FOREIGN KEY students_ibfk_2"); } catch(Exception $e) {}
        $db->exec("ALTER TABLE students DROP COLUMN section_id");
        echo "✅ Colonne section_id supprimée de students.\n";
    }

    // 3. Normalisation de la table grades (Lien vers sequences)
    if (!in_array('sequence_id', $db->query("SHOW COLUMNS FROM grades")->fetchAll(PDO::FETCH_COLUMN))) {
        $db->exec("ALTER TABLE grades ADD COLUMN sequence_id INT AFTER academic_year_id");
        $db->exec("ALTER TABLE grades ADD FOREIGN KEY (sequence_id) REFERENCES sequences(id) ON DELETE SET NULL");
        echo "✅ Colonne sequence_id ajoutée à grades.\n";

        // Migration automatique des données basée sur le label periode
        $db->exec("UPDATE grades g 
                   JOIN sequences s ON s.label = g.periode 
                   SET g.sequence_id = s.id");
        echo "✅ Migration des anciennes périodes vers sequence_id terminée.\n";
    }

    echo "🚀 Normalisation terminée avec succès !\n";
} catch (Exception $e) {
    echo "❌ Erreur lors de la normalisation : " . $e->getMessage() . "\n";
}
