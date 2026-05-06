<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "--- Correction de la table 'departments' ---\n";
    
    // Ajout de 'code' si manquant
    $checkCode = $db->query("SHOW COLUMNS FROM departments LIKE 'code'")->fetch();
    if (!$checkCode) {
        $db->exec("ALTER TABLE departments ADD COLUMN code VARCHAR(20) NOT NULL AFTER nom");
        echo "[OK] Colonne 'code' ajoutée.\n";
    }

    // Ajout de 'status' si manquant
    $checkStatus = $db->query("SHOW COLUMNS FROM departments LIKE 'status'")->fetch();
    if (!$checkStatus) {
        $db->exec("ALTER TABLE departments ADD COLUMN status TINYINT(1) DEFAULT 1 AFTER code");
        echo "[OK] Colonne 'status' ajoutée.\n";
    }
    
    echo "--- Correction terminée ---\n";
} catch (Exception $e) {
    echo "[ERREUR] " . $e->getMessage() . "\n";
}
