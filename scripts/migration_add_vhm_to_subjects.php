<?php
/**
 * MIGRATION: Ajout de la colonne VHm (Volume Horaire Ministériel) dans la table subjects
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    
    echo "=== MIGRATION: AJOUT VHM DANS SUBJECTS ===\n";
    
    $colCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subjects' AND COLUMN_NAME = 'vhm'");
    $colCheck->execute();
    if ((int)$colCheck->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE subjects ADD COLUMN vhm DECIMAL(8,2) NULL DEFAULT NULL AFTER code_ue");
        echo "✓ Colonne 'vhm' ajoutée à la table 'subjects'.\n";
    } else {
        echo "✓ La colonne 'vhm' existe déjà dans la table 'subjects'.\n";
    }

    echo "=== MIGRATION TERMINEE AVEC SUCCES ===\n";
} catch (Exception $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
    exit(1);
}
