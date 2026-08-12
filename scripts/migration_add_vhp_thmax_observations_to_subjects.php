<?php
/**
 * MIGRATION: Ajout des colonnes VHp, TH(Max) et Observations dans la table subjects
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    
    echo "=== MIGRATION: AJOUT VHP, TH_MAX, OBSERVATIONS DANS SUBJECTS ===\n";
    
    // Ensure vhm column exists
    $vhmCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subjects' AND COLUMN_NAME = 'vhm'");
    $vhmCheck->execute();
    if ((int)$vhmCheck->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE subjects ADD COLUMN vhm DECIMAL(8,2) NULL DEFAULT NULL AFTER code_ue");
        echo "✓ Colonne 'vhm' ajoutée.\n";
    }

    // Add vhp column
    $vhpCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subjects' AND COLUMN_NAME = 'vhp'");
    $vhpCheck->execute();
    if ((int)$vhpCheck->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE subjects ADD COLUMN vhp DECIMAL(8,2) NULL DEFAULT NULL AFTER vhm");
        echo "✓ Colonne 'vhp' ajoutée à la table 'subjects'.\n";
    } else {
        echo "✓ La colonne 'vhp' existe déjà dans la table 'subjects'.\n";
    }

    // Add th_max column
    $thMaxCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subjects' AND COLUMN_NAME = 'th_max'");
    $thMaxCheck->execute();
    if ((int)$thMaxCheck->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE subjects ADD COLUMN th_max DECIMAL(8,2) NULL DEFAULT NULL AFTER vhp");
        echo "✓ Colonne 'th_max' ajoutée à la table 'subjects'.\n";
    } else {
        echo "✓ La colonne 'th_max' existe déjà dans la table 'subjects'.\n";
    }

    // Add observations column
    $obsCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subjects' AND COLUMN_NAME = 'observations'");
    $obsCheck->execute();
    if ((int)$obsCheck->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE subjects ADD COLUMN observations TEXT NULL DEFAULT NULL AFTER th_max");
        echo "✓ Colonne 'observations' ajoutée à la table 'subjects'.\n";
    } else {
        echo "✓ La colonne 'observations' existe déjà dans la table 'subjects'.\n";
    }

    echo "=== MIGRATION TERMINEE AVEC SUCCES ===\n";
} catch (Exception $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
    exit(1);
}
