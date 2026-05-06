<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "--- Début de la migration ---\n";
    
    // 1. Création de la table departments
    $db->exec("CREATE TABLE IF NOT EXISTS departments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        code VARCHAR(20) NOT NULL,
        status TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "[OK] Table 'departments' créée ou déjà existante.\n";
    
    // 2. Ajout de la colonne department_id dans classes
    $checkColumn = $db->query("SHOW COLUMNS FROM classes LIKE 'department_id'")->fetch();
    if (!$checkColumn) {
        $db->exec("ALTER TABLE classes ADD COLUMN department_id INT NULL AFTER section_id");
        $db->exec("ALTER TABLE classes ADD CONSTRAINT fk_classes_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL");
        echo "[OK] Colonne 'department_id' ajoutée à la table 'classes'.\n";
    } else {
        echo "[INFO] Colonne 'department_id' déjà existante dans 'classes'.\n";
    }
    
    echo "--- Migration terminée avec succès ---\n";
} catch (Exception $e) {
    echo "[ERREUR] " . $e->getMessage() . "\n";
}
