<?php
/**
 * Script de migration pour ajouter les colonnes snapshot à la table grades
 * Cela permet de préserver l'historique des informations de matière au moment de la saisie
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

echo "Ajout des colonnes snapshot à la table grades...\n";

try {
    // Ajouter subject_nom_snapshot
    $db->exec("ALTER TABLE grades ADD COLUMN subject_nom_snapshot VARCHAR(255)");
    echo "✓ Colonne subject_nom_snapshot ajoutée\n";
} catch (\PDOException $e) {
    echo "ℹ Colonne subject_nom_snapshot existe déjà ou erreur: " . $e->getMessage() . "\n";
}

try {
    // Ajouter subject_coefficient_snapshot
    $db->exec("ALTER TABLE grades ADD COLUMN subject_coefficient_snapshot DECIMAL(3,2) DEFAULT 1.00");
    echo "✓ Colonne subject_coefficient_snapshot ajoutée\n";
} catch (\PDOException $e) {
    echo "ℹ Colonne subject_coefficient_snapshot existe déjà ou erreur: " . $e->getMessage() . "\n";
}

try {
    // Ajouter subject_groupe_snapshot
    $db->exec("ALTER TABLE grades ADD COLUMN subject_groupe_snapshot VARCHAR(50) DEFAULT 'Groupe 1'");
    echo "✓ Colonne subject_groupe_snapshot ajoutée\n";
} catch (\PDOException $e) {
    echo "ℹ Colonne subject_groupe_snapshot existe déjà ou erreur: " . $e->getMessage() . "\n";
}

echo "\nMigration terminée.\n";
