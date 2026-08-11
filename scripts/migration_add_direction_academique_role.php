<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Core\PermissionManager;

try {
    $db = Database::getInstance()->getConnection();

    echo "=== MIGRATION RBAC : MISE À JOUR DU RÔLE DIRECTION ACADÉMIQUE (PILOTAGE COMPLET) ===\n";

    // 1. Récupération de l'ID du rôle direction_academique
    $roleColumns = $db->query("SHOW COLUMNS FROM roles")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('is_system', $roleColumns, true)) {
        try {
            $db->exec("ALTER TABLE roles ADD COLUMN is_system TINYINT(1) NOT NULL DEFAULT 1 AFTER description");
            $roleColumns[] = 'is_system';
        } catch (\Throwable $e) {
            // Ignorer si la colonne ne peut pas être ajoutée immédiatement
        }
    }

    $stmt = $db->prepare("SELECT id FROM roles WHERE role_code = 'direction_academique'");
    $stmt->execute();
    $roleId = $stmt->fetchColumn();

    if (!$roleId) {
        if (in_array('is_system', $roleColumns, true)) {
            $stmtInsertRole = $db->prepare("
                INSERT INTO roles (role_code, role_name, description, is_system) 
                VALUES ('direction_academique', 'Direction Académique', 'Gestionnaire académique autonome (Emplois du temps, Enseignants, Notes, Pilotage)', 1)
            ");
        } else {
            $stmtInsertRole = $db->prepare("
                INSERT INTO roles (role_code, role_name, description) 
                VALUES ('direction_academique', 'Direction Académique', 'Gestionnaire académique autonome (Emplois du temps, Enseignants, Notes, Pilotage)')
            ");
        }
        $stmtInsertRole->execute();
        $roleId = $db->lastInsertId();
        echo "- Rôle 'direction_academique' créé avec succès (ID: {$roleId}).\n";
    }

    // 2. Liste complète des permissions attribuées au rôle Direction Académique (incluant tout le Centre de Pilotage Administrateur)
    $targetPermissions = [
        // Emplois du Temps
        'view_timetables',
        'manage_timetables',

        // Enseignants & Personnel Académique
        'manage_teachers',
        'manage_contracts',
        'manage_staff',

        // Notes, Évaluations, Bulletins, Relevés, Discipline & Matières
        'manage_marks',
        'manage_sequences',
        'manage_bulletins',
        'manage_transcripts',
        'view_transcripts',
        'manage_absences',
        'manage_subjects',
        'manage_subject_groups',
        'view_classes',
        'manage_classes_structure',

        // Centre de Pilotage Administrateur complet
        'view_pilotage',
        'dashboard_executiveDashboard',
        'dashboard_index',
        'manage_teaching_types',
        'manage_levels',
        'manage_cycles',
        'manage_sections',
        'manage_departments',
        'manage_settings'
    ];

    $assignedCount = 0;
    foreach ($targetPermissions as $permCode) {
        $stmtPerm = $db->prepare("SELECT id FROM permissions WHERE perm_code = ? AND status = 'active'");
        $stmtPerm->execute([$permCode]);
        $permId = $stmtPerm->fetchColumn();

        if ($permId) {
            $stmtRolePerm = $db->prepare("
                INSERT IGNORE INTO role_permissions (role_id, permission_id) 
                VALUES (?, ?)
            ");
            $stmtRolePerm->execute([$roleId, $permId]);
            $assignedCount++;
            echo "  [+] Permission '{$permCode}' assignée.\n";
        } else {
            echo "  [!] Avertissement : Permission '{$permCode}' non trouvée ou inactive.\n";
        }
    }

    // 3. Réinitialiser et incrémenter le cache RBAC
    PermissionManager::clearCache();
    echo "- Cache RBAC réinitialisé avec succès (rbac_version à jour).\n";

    echo "Migration du rôle Direction Académique terminée avec succès. ({$assignedCount} permissions configurées)\n";

} catch (\Throwable $e) {
    echo "Erreur lors de la migration RBAC : " . $e->getMessage() . "\n";
    exit(1);
}
