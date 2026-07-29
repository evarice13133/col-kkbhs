<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    echo "=== MIGRATION RBAC : ADAPTATION ADMIN PILOTAGE ===\n";

    // 1. Récupération de l'ID du rôle admin
    $stmt = $db->prepare("SELECT id FROM roles WHERE role_code = 'admin'");
    $stmt->execute();
    $adminRoleId = $stmt->fetchColumn();

    if (!$adminRoleId) {
        echo "Erreur : Rôle 'admin' non trouvé.\n";
        exit(1);
    }

    // 2. Retirer la permission manage_academic_years du rôle admin
    $stmt = $db->prepare("
        DELETE rp FROM role_permissions rp
        JOIN permissions p ON rp.permission_id = p.id
        WHERE rp.role_id = ? AND p.perm_code = 'manage_academic_years'
    ");
    $stmt->execute([$adminRoleId]);
    echo "- Permission 'manage_academic_years' retirée pour 'admin'.\n";

    // 3. Assurer l'attribution des permissions du Centre de Pilotage pour admin
    $permissionsToAssign = [
        'manage_teaching_types',
        'manage_cycles',
        'manage_sections',
        'manage_departments',
        'manage_settings'
    ];

    foreach ($permissionsToAssign as $permCode) {
        $stmtPerm = $db->prepare("SELECT id FROM permissions WHERE perm_code = ?");
        $stmtPerm->execute([$permCode]);
        $permId = $stmtPerm->fetchColumn();

        if ($permId) {
            $stmtInsert = $db->prepare("
                INSERT IGNORE INTO role_permissions (role_id, permission_id) 
                VALUES (?, ?)
            ");
            $stmtInsert->execute([$adminRoleId, $permId]);
            echo "- Permission '{$permCode}' vérifiée/attribuée à 'admin'.\n";
        }
    }

    echo "Migration des permissions RBAC pour 'admin' terminée avec succès.\n";

} catch (\Throwable $e) {
    echo "Erreur lors de la migration RBAC : " . $e->getMessage() . "\n";
    exit(1);
}
