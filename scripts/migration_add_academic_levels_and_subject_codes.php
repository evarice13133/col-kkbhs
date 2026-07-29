<?php
/**
 * Migration: Ajout de la table levels (Niveaux académiques),
 * rattachement des classes aux niveaux (level_id),
 * et ajout des colonnes code_uv et code_ue dans subjects.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    echo "=== MIGRATION NIVEAUX ET CODES UV/UE ===\n\n";

    // 1. Création de la table `levels`
    echo "1. Création de la table 'levels'...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS levels (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(50) NOT NULL,
            libelle_fr VARCHAR(150) NOT NULL,
            libelle_en VARCHAR(150) NOT NULL,
            teaching_type_id INT NOT NULL,
            status TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_levels_teaching_type FOREIGN KEY (teaching_type_id) REFERENCES teaching_types(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "   -> Table 'levels' créée ou déjà existante.\n\n";

    // 2. Ajout de la colonne `level_id` dans la table `classes`
    echo "2. Vérification de la colonne 'level_id' dans 'classes'...\n";
    $checkCol = $db->query("SHOW COLUMNS FROM classes LIKE 'level_id'")->fetch();
    if (!$checkCol) {
        $db->exec("ALTER TABLE classes ADD COLUMN level_id INT NULL DEFAULT NULL AFTER teaching_type_id");
        echo "   -> Colonne 'level_id' ajoutée à 'classes'.\n";

        // Ajout de la clé étrangère si absente
        try {
            $db->exec("ALTER TABLE classes ADD CONSTRAINT fk_classes_level FOREIGN KEY (level_id) REFERENCES levels(id) ON DELETE SET NULL");
            echo "   -> Clé étrangère 'fk_classes_level' ajoutée.\n";
        } catch (\PDOException $e) {
            echo "   -> Remarque FK: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   -> Colonne 'level_id' existe déjà.\n";
    }
    echo "\n";

    // 3. Ajout des colonnes `code_uv` et `code_ue` dans la table `subjects`
    echo "3. Vérification des colonnes 'code_uv' et 'code_ue' dans 'subjects'...\n";
    $checkUv = $db->query("SHOW COLUMNS FROM subjects LIKE 'code_uv'")->fetch();
    if (!$checkUv) {
        $db->exec("ALTER TABLE subjects ADD COLUMN code_uv VARCHAR(50) NULL DEFAULT NULL AFTER groupe");
        echo "   -> Colonne 'code_uv' ajoutée à 'subjects'.\n";
    } else {
        echo "   -> Colonne 'code_uv' existe déjà.\n";
    }

    $checkUe = $db->query("SHOW COLUMNS FROM subjects LIKE 'code_ue'")->fetch();
    if (!$checkUe) {
        $db->exec("ALTER TABLE subjects ADD COLUMN code_ue VARCHAR(50) NULL DEFAULT NULL AFTER code_uv");
        echo "   -> Colonne 'code_ue' ajoutée à 'subjects'.\n";
    } else {
        echo "   -> Colonne 'code_ue' existe déjà.\n";
    }
    echo "\n";

    // 4. Permission RBAC `manage_levels`
    echo "4. Configuration de la permission RBAC 'manage_levels'...\n";
    $checkPerm = $db->query("SELECT id FROM permissions WHERE perm_code = 'manage_levels'")->fetch();
    if (!$checkPerm) {
        $db->exec("INSERT INTO permissions (perm_code, perm_name, description) VALUES ('manage_levels', 'Gérer les niveaux', 'Créer, modifier et paramétrer les niveaux académiques.')");
        $permId = $db->lastInsertId();
        echo "   -> Permission 'manage_levels' insérée (ID: $permId).\n";
    } else {
        $permId = $checkPerm['id'];
        echo "   -> Permission 'manage_levels' déjà existante (ID: $permId).\n";
    }

    // Affecter la permission aux rôles 'admin' et 'superadmin'
    $roles = $db->query("SELECT id, role_code FROM roles WHERE role_code IN ('superadmin', 'admin')")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($roles as $r) {
        $checkRp = $db->prepare("SELECT 1 FROM role_permissions WHERE role_id = ? AND permission_id = ?");
        $checkRp->execute([$r['id'], $permId]);
        if (!$checkRp->fetch()) {
            $insRp = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            $insRp->execute([$r['id'], $permId]);
            echo "   -> Permission attribuée au rôle '{$r['role_code']}'.\n";
        }
    }

    echo "\n=== MIGRATION RÉUSSIE ===\n";

} catch (\Exception $e) {
    echo "ERREUR DORS DE LA MIGRATION : " . $e->getMessage() . "\n";
    exit(1);
}
