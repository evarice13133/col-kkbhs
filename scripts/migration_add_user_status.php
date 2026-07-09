<?php
/**
 * MIGRATION: Statut des utilisateurs
 * 
 * Ajoute la colonne status aux utilisateurs pour permettre la désactivation.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    
    echo "=== MIGRATION: STATUT UTILISATEURS ===\n";
    
    // 1. Ajouter la colonne status
    $colCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'status'");
    $colCheck->execute();
    if ((int)$colCheck->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN status TINYINT(1) NOT NULL DEFAULT 1");
        echo "✓ Colonne 'status' ajoutée à la table 'users'.\n";
    } else {
        echo "✓ La colonne 'status' existe déjà.\n";
    }

    echo "=== MIGRATION TERMINEE AVEC SUCCES ===\n";
} catch (Exception $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
    exit(1);
}
