<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    echo "=== MIGRATION SETTINGS : AJOUT TYPE D'ENSEIGNEMENT ===\n";

    // 1. Récupérer l'ID du type d'enseignement SEC00
    $sec00Id = $db->query("SELECT id FROM teaching_types WHERE code = 'SEC00' LIMIT 1")->fetchColumn();
    if (!$sec00Id) {
        // En cas d'absence, créer le type SEC00
        $db->exec("INSERT INTO teaching_types (nom, code, position, actif) VALUES ('Secondaire', 'SEC00', 1, 1)");
        $sec00Id = $db->lastInsertId();
    }
    $sec00Id = (int) $sec00Id;
    echo "- Type d'enseignement par défaut SEC00 (ID: {$sec00Id}) identifié.\n";

    // 2. Vérifier si la colonne teaching_type_id existe dans settings
    $colCheck = $db->query("SHOW COLUMNS FROM settings LIKE 'teaching_type_id'")->fetch();
    if (!$colCheck) {
        echo "- Ajout de la colonne teaching_type_id à la table settings...\n";
        
        // Supprimer l'ancienne clé primaire et ajouter la colonne avec la clé composite
        $db->exec("ALTER TABLE settings DROP PRIMARY KEY");
        $db->exec("ALTER TABLE settings ADD COLUMN teaching_type_id INT NOT NULL DEFAULT 0");
        $db->exec("ALTER TABLE settings ADD PRIMARY KEY (setting_key, teaching_type_id)");
        
        echo "- Structure de la table settings mise à jour avec succès.\n";

        // Associer les paramètres d'établissement/académiques existants au type SEC00
        $scopedKeys = [
            'school_name', 'school_code', 'school_republic', 'school_republic_en',
            'school_ministry', 'school_ministry_en', 'school_slogan', 'school_slogan_en',
            'school_motto', 'school_motto_en', 'school_logo', 'school_city',
            'school_phone', 'school_po_box', 'school_fax', 'school_email', 'school_website',
            'display_school_year', 'principal_name', 'principal_title', 'principal_signature',
            'school_stamp', 'honor_roll_default_threshold', 'bulletin_printing_enabled',
            'registration_fee_policy', 'payment_methods'
        ];

        $inClause = "'" . implode("','", $scopedKeys) . "'";
        $db->exec("UPDATE settings SET teaching_type_id = {$sec00Id} WHERE setting_key IN ({$inClause}) AND teaching_type_id = 0");
        echo "- Paramètres d'établissement existants associés au type SEC00 (ID: {$sec00Id}).\n";
    } else {
        echo "- La colonne teaching_type_id existe déjà dans la table settings.\n";
    }

    echo "Migration des paramètres terminée avec succès.\n";

} catch (\Throwable $e) {
    echo "Erreur lors de la migration settings : " . $e->getMessage() . "\n";
    exit(1);
}
