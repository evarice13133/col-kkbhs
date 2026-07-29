<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Services\SettingsStore;

try {
    $db = Database::getInstance()->getConnection();

    echo "=== MIGRATION : ACTIVATION ET POPULATION DES 4 TYPES D'ENSEIGNEMENT ===\n\n";

    // 1. Activer tous les 4 types d'enseignement présents en base (MAT, PRI, SEC00, LMD)
    $db->exec("UPDATE teaching_types SET actif = 1 WHERE code IN ('MAT', 'PRI', 'SEC00', 'LMD')");
    echo "- Les 4 types d'enseignement (Maternelle, Primaire, Secondaire SEC00, Supérieur LMD) ont été activés (actif = 1).\n";

    // 2. Charger les types d'enseignement
    $teachingTypes = $db->query("SELECT id, nom, code FROM teaching_types ORDER BY position ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

    // 3. Charger les clés scopées depuis SettingsStore
    $store = new SettingsStore($db);
    $defaultTtId = $store->getDefaultTeachingTypeId();

    // Récupérer les paramètres actuels de SEC00 pour s'en servir de référence initiale
    $secSettings = $store->all($defaultTtId);

    $scopedKeys = [
        'school_name', 'school_code', 'school_republic', 'school_republic_en',
        'school_ministry', 'school_ministry_en', 'school_slogan', 'school_slogan_en',
        'school_motto', 'school_motto_en', 'school_logo', 'school_city',
        'school_phone', 'school_po_box', 'school_fax', 'school_email', 'school_website',
        'display_school_year', 'principal_name', 'principal_title', 'principal_signature',
        'school_stamp', 'honor_roll_default_threshold', 'bulletin_printing_enabled',
        'registration_fee_policy', 'payment_methods'
    ];

    $stmtInsert = $db->prepare("
        INSERT IGNORE INTO settings (setting_key, setting_value, teaching_type_id)
        VALUES (?, ?, ?)
    ");

    foreach ($teachingTypes as $tt) {
        $ttId = (int) $tt['id'];
        $count = 0;

        foreach ($scopedKeys as $key) {
            $val = $secSettings[$key] ?? SettingsStore::defaults()[$key] ?? '';
            // Adapter spécifiquement le nom par défaut si c'est la valeur générique
            if ($key === 'school_name' && empty($secSettings['school_name'])) {
                $val = "Établissement " . $tt['nom'];
            }
            $stmtInsert->execute([$key, (string)$val, $ttId]);
            if ($stmtInsert->rowCount() > 0) {
                $count++;
            }
        }
        echo "- Type {$tt['nom']} (CODE: {$tt['code']}, ID: {$ttId}) : {$count} paramètres initialisés.\n";
    }

    echo "\nMigration effectuée avec succès pour les 4 types d'enseignement.\n";

} catch (\Throwable $e) {
    echo "Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}
