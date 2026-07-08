<?php
/**
 * MIGRATION: Workflow d'inscription des élèves
 * 
 * Ajoute la colonne status aux élèves pour distinguer l'importation de l'inscription officielle.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    
    echo "=== MIGRATION: WORKFLOW INSCRIPTION ELEVES ===\n";
    
    // 1. Ajouter la colonne status
    $colCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'students' AND COLUMN_NAME = 'status'");
    $colCheck->execute();
    if ((int)$colCheck->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE students ADD COLUMN status ENUM('Non inscrit', 'Inscrit', 'Démissionnaire', 'Archivé') NOT NULL DEFAULT 'Inscrit'");
        echo "✓ Colonne 'status' ajoutée à la table 'students'.\n";
    } else {
        echo "✓ La colonne 'status' existe déjà.\n";
    }

    // 2. Synchroniser le statut des élèves démissionnaires existants
    $pdo->exec("UPDATE students SET status = 'Démissionnaire' WHERE is_withdrawn = 1");
    echo "✓ Élèves démissionnaires synchronisés.\n";

    echo "=== MIGRATION TERMINEE AVEC SUCCES ===\n";
} catch (Exception $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
    exit(1);
}
