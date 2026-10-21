<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    $idColumn = $db->query("SHOW COLUMNS FROM permissions WHERE Field = 'id'")->fetch(\PDO::FETCH_ASSOC);
    if (!$idColumn) {
        throw new RuntimeException("La colonne permissions.id est introuvable.");
    }

    $legacyPermission = $db->query("SELECT * FROM permissions WHERE id = 0")->fetch(\PDO::FETCH_ASSOC);
    if ($legacyPermission) {
        $db->beginTransaction();

        $temporaryCode = '__rbac_reconcile_legacy_id_zero__';
        $checkTemporaryCode = $db->prepare("SELECT 1 FROM permissions WHERE perm_code = ?");
        $checkTemporaryCode->execute([$temporaryCode]);
        if ($checkTemporaryCode->fetchColumn()) {
            throw new RuntimeException("Le code temporaire RBAC existe déjà.");
        }

        $renameLegacyPermission = $db->prepare("UPDATE permissions SET perm_code = ? WHERE id = 0");
        $renameLegacyPermission->execute([$temporaryCode]);

        $restoredPermissionId = (int) $db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM permissions")->fetchColumn();
        $restoreLegacyPermission = $db->prepare("
            INSERT INTO permissions
                (id, perm_code, perm_name, module, submodule, action, description, criticality, status, is_system)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $restoreLegacyPermission->execute([
            $restoredPermissionId,
            $legacyPermission['perm_code'],
            $legacyPermission['perm_name'],
            $legacyPermission['module'],
            $legacyPermission['submodule'],
            $legacyPermission['action'],
            $legacyPermission['description'],
            $legacyPermission['criticality'],
            $legacyPermission['status'],
            $legacyPermission['is_system'],
        ]);

        $copyRoleMappings = $db->prepare("
            INSERT IGNORE INTO role_permissions (role_id, permission_id)
            SELECT role_id, ? FROM role_permissions WHERE permission_id = 0
        ");
        $copyRoleMappings->execute([$restoredPermissionId]);
        $db->exec("DELETE FROM role_permissions WHERE permission_id = 0");

        $copyUserOverrides = $db->prepare("
            INSERT IGNORE INTO user_permissions (user_id, permission_id, is_granted)
            SELECT user_id, ?, is_granted FROM user_permissions WHERE permission_id = 0
        ");
        $copyUserOverrides->execute([$restoredPermissionId]);
        $db->exec("DELETE FROM user_permissions WHERE permission_id = 0");
        $db->exec("DELETE FROM permissions WHERE id = 0");
        $db->commit();
    }

    if (stripos($idColumn['Extra'], 'auto_increment') === false) {
        $idType = $idColumn['Type'];
        if (!preg_match('/^(tinyint|smallint|mediumint|int|bigint)(\(\d+\))?( unsigned)?$/i', $idType)) {
            throw new RuntimeException("Type non pris en charge pour permissions.id : {$idType}");
        }

        $db->exec("SET FOREIGN_KEY_CHECKS = 0");
        try {
            $db->exec("ALTER TABLE permissions MODIFY id {$idType} NOT NULL AUTO_INCREMENT");
        } finally {
            $db->exec("SET FOREIGN_KEY_CHECKS = 1");
        }
    }

    $db->beginTransaction();

    $permissions = [
        ['view_pilotage', 'Accéder au Centre de Pilotage', 'system', 'pilotage', 'view', 'Accéder aux tableaux de bord analytiques et bilans d’impact.', 'medium'],
        ['manage_levels', 'Gérer les niveaux d’étude', 'pedagogy', 'structure', 'manage', 'Configurer les niveaux d’étude.', 'medium'],
        ['manage_subject_groups', 'Gérer les groupes de matières', 'pedagogy', 'subjects', 'manage', 'Organiser les matières en groupes/UE.', 'medium'],
        ['view_timetables', 'Consulter les emplois du temps', 'pedagogy', 'timetables', 'view', 'Visualiser, partager et imprimer les emplois du temps.', 'low'],
        ['unlock_timetables', 'Déverrouiller les emplois du temps', 'pedagogy', 'timetables', 'manage', 'Déverrouiller un emploi du temps verrouillé.', 'critical'],
        ['export_students', 'Exporter les données élèves', 'students', 'registry', 'export', 'Exporter les registres élèves au format Excel/PDF.', 'medium'],
        ['manage_transcripts', 'Gérer les relevés de notes', 'evaluations', 'transcripts', 'manage', 'Générer les relevés de notes officiels.', 'medium'],
        ['view_transcripts', 'Consulter les relevés de notes', 'evaluations', 'transcripts', 'view', 'Visualiser et prévisualiser les relevés de notes des élèves.', 'low'],
        ['manage_expenses', 'Gérer les dépenses d’établissement', 'finance', 'expenses', 'manage', 'Saisir et approuver les dépenses et frais d’exploitation.', 'high'],
    ];

    $permissionStatement = $db->prepare("
        INSERT INTO permissions
            (perm_code, perm_name, module, submodule, action, description, criticality, status, is_system)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'active', 1)
        ON DUPLICATE KEY UPDATE
            perm_name = VALUES(perm_name),
            module = VALUES(module),
            submodule = VALUES(submodule),
            action = VALUES(action),
            description = VALUES(description),
            criticality = VALUES(criticality),
            status = 'active',
            is_system = 1
    ");

    foreach ($permissions as $permission) {
        $permissionStatement->execute($permission);
    }

    $roleMappings = [
        'admin' => [
            'view_pilotage', 'manage_levels', 'manage_subject_groups',
            'export_students', 'manage_transcripts', 'view_transcripts',
            'view_timetables', 'manage_expenses',
        ],
        'it_manager' => [
            'view_pilotage', 'manage_levels', 'manage_subject_groups',
            'export_students', 'manage_transcripts', 'view_transcripts',
            'view_timetables',
        ],
        'direction_academique' => [
            'view_pilotage', 'manage_levels', 'manage_subject_groups',
            'manage_transcripts', 'view_transcripts', 'view_timetables',
        ],
        'caissier' => ['manage_expenses'],
        'comptable' => ['manage_expenses'],
        'enseignant' => ['view_timetables'],
    ];

    $mappingStatement = $db->prepare("
        INSERT IGNORE INTO role_permissions (role_id, permission_id)
        SELECT r.id, p.id
        FROM roles r
        JOIN permissions p ON p.perm_code = ?
        WHERE r.role_code = ? AND p.status = 'active'
    ");

    foreach ($roleMappings as $roleCode => $permissionCodes) {
        foreach ($permissionCodes as $permissionCode) {
            $mappingStatement->execute([$permissionCode, $roleCode]);
        }
    }

    $db->exec("
        INSERT IGNORE INTO role_permissions (role_id, permission_id)
        SELECT r.id, p.id
        FROM roles r
        JOIN permissions p ON p.status = 'active'
        WHERE r.role_code = 'superadmin'
    ");

    $db->commit();
    echo "Catalogue RBAC reconcilié; permissions manquantes créées et accès superadmin synchronisé.\n";
} catch (\Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    fwrite(STDERR, "Échec de la réconciliation RBAC : " . $e->getMessage() . PHP_EOL);
    exit(1);
}
