<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\PermissionManager;
use App\Core\Session;
use App\Services\PermissionAutoDetectorService;
use PDO;

/**
 * Class RbacController
 * 
 * Contrôleur de gestion centrale des privilèges, rôles, exceptions utilisateurs et sécurité RBAC.
 */
class RbacController
{
    private PDO $db;

    public function __construct()
    {
        PermissionManager::requirePermission('manage_rbac');
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Page principale de l'interface de Gestion des Permissions (M365 Style)
     */
    public function index(): void
    {
        include __DIR__ . '/../Views/pilotage/rbac.php';
    }

    /**
     * REST API: Récupère la liste des permissions du catalogue
     */
    public function getPermissions(): void
    {
        $module = $_GET['module'] ?? '';
        $criticality = $_GET['criticality'] ?? '';
        $search = trim($_GET['search'] ?? '');
        $status = $_GET['status'] ?? '';

        $sql = "SELECT * FROM permissions WHERE 1=1";
        $params = [];

        if ($module !== '') {
            $sql .= " AND module = ?";
            $params[] = $module;
        }
        if ($criticality !== '') {
            $sql .= " AND criticality = ?";
            $params[] = $criticality;
        }
        if ($status !== '') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        if ($search !== '') {
            $sql .= " AND (perm_code LIKE ? OR perm_name LIKE ? OR description LIKE ? OR submodule LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY module ASC, submodule ASC, perm_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->jsonResponse(['success' => true, 'data' => $permissions]);
    }

    /**
     * REST API: Récupère la liste des rôles avec compteurs
     */
    public function getRoles(): void
    {
        $stmt = $this->db->query("
            SELECT r.*, 
                   (SELECT COUNT(*) FROM role_permissions rp WHERE rp.role_id = r.id) as perm_count,
                   (SELECT COUNT(*) FROM users u WHERE u.role = r.role_code) as user_count
            FROM roles r
            ORDER BY r.id ASC
        ");
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->jsonResponse(['success' => true, 'data' => $roles]);
    }

    /**
     * REST API: Récupère les IDs de permissions associées à un rôle
     */
    public function getRolePermissions(): void
    {
        $roleId = (int) ($_GET['role_id'] ?? 0);
        if ($roleId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de rôle invalide.'], 400);
            return;
        }

        $stmt = $this->db->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
        $stmt->execute([$roleId]);
        $permIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->jsonResponse(['success' => true, 'role_id' => $roleId, 'permission_ids' => $permIds]);
    }

    /**
     * REST API: Sauvegarde la matrice des permissions d'un rôle
     */
    public function saveRolePermissions(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $roleId = (int) ($input['role_id'] ?? 0);
        $permissionIds = (array) ($input['permission_ids'] ?? []);

        if ($roleId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Rôle non spécifié.'], 400);
            return;
        }

        // Récupération informations rôle
        $stmtRole = $this->db->prepare("SELECT * FROM roles WHERE id = ?");
        $stmtRole->execute([$roleId]);
        $role = $stmtRole->fetch(PDO::FETCH_ASSOC);

        if (!$role) {
            $this->jsonResponse(['success' => false, 'message' => 'Rôle introuvable.'], 404);
            return;
        }

        // Empêcher la suppression accidentelle des droits RBAC du superadmin
        if ($role['role_code'] === 'superadmin') {
            $rbacPermId = $this->db->query("SELECT id FROM permissions WHERE perm_code = 'manage_rbac'")->fetchColumn();
            if ($rbacPermId && !in_array($rbacPermId, $permissionIds)) {
                $permissionIds[] = (int)$rbacPermId;
            }
        }

        // Anciennes permissions pour l'audit
        $stmtOld = $this->db->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
        $stmtOld->execute([$roleId]);
        $oldPermIds = $stmtOld->fetchAll(PDO::FETCH_COLUMN);

        $this->db->beginTransaction();
        try {
            // Suppression des anciennes associations
            $stmtDel = $this->db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $stmtDel->execute([$roleId]);

            // Insertion des nouvelles
            if (!empty($permissionIds)) {
                $stmtIns = $this->db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                foreach ($permissionIds as $pId) {
                    $stmtIns->execute([$roleId, (int)$pId]);
                }
            }

            $this->db->commit();
            PermissionManager::clearCache();

            PermissionManager::logAudit(
                'role_updated',
                'role',
                (string)$roleId,
                "Mise à jour des privilèges pour le rôle '{$role['role_name']}' (" . count($permissionIds) . " permissions).",
                $oldPermIds,
                $permissionIds
            );

            $this->jsonResponse(['success' => true, 'message' => "Les permissions du rôle '{$role['role_name']}' ont été enregistrées."]);
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->jsonResponse(['success' => false, 'message' => "Erreur lors de l'enregistrement: " . $e->getMessage()], 500);
        }
    }

    /**
     * REST API: Copie les permissions d'un rôle source vers un rôle cible
     */
    public function copyRolePermissions(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $sourceRoleId = (int) ($input['source_role_id'] ?? 0);
        $targetRoleId = (int) ($input['target_role_id'] ?? 0);

        if ($sourceRoleId <= 0 || $targetRoleId <= 0 || $sourceRoleId === $targetRoleId) {
            $this->jsonResponse(['success' => false, 'message' => 'Rôles source et cible invalides.'], 400);
            return;
        }

        // Obtenir permissions source
        $stmtSource = $this->db->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
        $stmtSource->execute([$sourceRoleId]);
        $sourcePermIds = $stmtSource->fetchAll(PDO::FETCH_COLUMN);

        $this->db->beginTransaction();
        try {
            $this->db->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([$targetRoleId]);

            if (!empty($sourcePermIds)) {
                $stmtIns = $this->db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                foreach ($sourcePermIds as $pId) {
                    $stmtIns->execute([$targetRoleId, (int)$pId]);
                }
            }

            $this->db->commit();
            PermissionManager::clearCache();

            PermissionManager::logAudit(
                'role_copied',
                'role',
                (string)$targetRoleId,
                "Copie des permissions du rôle ID {$sourceRoleId} vers le rôle ID {$targetRoleId}."
            );

            $this->jsonResponse(['success' => true, 'message' => 'Copie des permissions effectuée avec succès.']);
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la copie : ' . $e->getMessage()], 500);
        }
    }

    /**
     * REST API: Compare les permissions de deux rôles
     */
    public function compareRoles(): void
    {
        $roleId1 = (int) ($_GET['role_id_1'] ?? 0);
        $roleId2 = (int) ($_GET['role_id_2'] ?? 0);

        if ($roleId1 <= 0 || $roleId2 <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Veuillez sélectionner deux rôles à comparer.'], 400);
            return;
        }

        $stmtRole = $this->db->prepare("SELECT id, role_name, role_code FROM roles WHERE id IN (?, ?)");
        $stmtRole->execute([$roleId1, $roleId2]);
        $roles = $stmtRole->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        $p1 = $this->db->query("SELECT permission_id FROM role_permissions WHERE role_id = {$roleId1}")->fetchAll(PDO::FETCH_COLUMN);
        $p2 = $this->db->query("SELECT permission_id FROM role_permissions WHERE role_id = {$roleId2}")->fetchAll(PDO::FETCH_COLUMN);

        $allPerms = $this->db->query("SELECT id, perm_code, perm_name, module, description FROM permissions ORDER BY module, perm_code")->fetchAll(PDO::FETCH_ASSOC);

        $comparison = [];
        foreach ($allPerms as $perm) {
            $hasRole1 = in_array($perm['id'], $p1);
            $hasRole2 = in_array($perm['id'], $p2);

            $comparison[] = [
                'permission' => $perm,
                'role_1_has' => $hasRole1,
                'role_2_has' => $hasRole2,
                'status' => ($hasRole1 && $hasRole2) ? 'both' : ($hasRole1 ? 'role_1_only' : ($hasRole2 ? 'role_2_only' : 'neither'))
            ];
        }

        $this->jsonResponse([
            'success' => true,
            'role_1' => $roles[$roleId1] ?? null,
            'role_2' => $roles[$roleId2] ?? null,
            'comparison' => $comparison
        ]);
    }

    /**
     * REST API: Réinitialise un rôle avec son profil système par défaut
     */
    public function resetRolePermissions(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $roleId = (int) ($input['role_id'] ?? 0);

        if ($roleId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Rôle invalide.'], 400);
            return;
        }

        // Relancer la réinitialisation par défaut via le script de migration RBAC
        require_once __DIR__ . '/../../scripts/migration_complete_rbac.php';
        PermissionManager::clearCache();

        PermissionManager::logAudit('role_reset', 'role', (string)$roleId, "Réinitialisation des permissions du rôle ID {$roleId} à sa configuration système.");

        $this->jsonResponse(['success' => true, 'message' => 'Le rôle a été réinitialisé avec succès avec son profil par défaut.']);
    }

    /**
     * REST API: Recherche des utilisateurs pour la gestion des surcharges
     */
    public function searchUsers(): void
    {
        $query = trim($_GET['q'] ?? '');
        $sql = "SELECT u.id, u.name, u.username, u.email, u.role, r.role_name 
                FROM users u
                LEFT JOIN roles r ON u.role = r.role_code
                WHERE 1=1";
        $params = [];

        if ($query !== '') {
            $sql .= " AND (u.name LIKE ? OR u.username LIKE ? OR u.email LIKE ? OR u.role LIKE ?)";
            $searchTerm = "%{$query}%";
            $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        }

        $sql .= " ORDER BY u.name ASC LIMIT 50";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->jsonResponse(['success' => true, 'data' => $users]);
    }

    /**
     * REST API: Obtenir les privilèges effectifs et surcharges d'un utilisateur
     */
    public function getUserPermissions(): void
    {
        $userId = (int) ($_GET['user_id'] ?? 0);
        if ($userId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'ID utilisateur manquant.'], 400);
            return;
        }

        $stmtUser = $this->db->prepare("SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role = r.role_code WHERE u.id = ?");
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $this->jsonResponse(['success' => false, 'message' => 'Utilisateur introuvable.'], 404);
            return;
        }

        // Surcharges directes dans user_permissions
        $stmtOver = $this->db->prepare("
            SELECT permission_id, is_granted 
            FROM user_permissions 
            WHERE user_id = ?
        ");
        $stmtOver->execute([$userId]);
        $overrides = $stmtOver->fetchAll(PDO::FETCH_KEY_PAIR) ?: []; // [perm_id => 1 (allow) ou 0 (deny)]

        // Permissions héritées du rôle
        $roleCode = $user['role'];
        $stmtRolePerms = $this->db->prepare("
            SELECT rp.permission_id 
            FROM role_permissions rp
            JOIN roles r ON rp.role_id = r.id
            WHERE r.role_code = ?
        ");
        $stmtRolePerms->execute([$roleCode]);
        $rolePermIds = $stmtRolePerms->fetchAll(PDO::FETCH_COLUMN) ?: [];

        // Permissions effectives globales
        $effectiveMap = PermissionManager::getEffectivePermissionsForUser($userId, $roleCode);

        $this->jsonResponse([
            'success' => true,
            'user' => $user,
            'overrides' => $overrides,
            'role_permission_ids' => $rolePermIds,
            'effective_permissions' => $effectiveMap
        ]);
    }

    /**
     * REST API: Sauvegarder les surcharges d'exceptions individuelles d'un utilisateur
     */
    public function saveUserPermissions(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $userId = (int) ($input['user_id'] ?? 0);
        $overrides = (array) ($input['overrides'] ?? []); // [perm_id => 1 (allow), 0 (deny), ou -1 (inherited/clear)]

        if ($userId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Utilisateur non spécifié.'], 400);
            return;
        }

        $adminId = Session::get('user_id');

        $this->db->beginTransaction();
        try {
            // Nettoyer anciennes surcharges
            $stmtDel = $this->db->prepare("DELETE FROM user_permissions WHERE user_id = ?");
            $stmtDel->execute([$userId]);

            // Ajouter nouvelles surcharges
            $stmtIns = $this->db->prepare("
                INSERT INTO user_permissions (user_id, permission_id, is_granted, granted_by) 
                VALUES (?, ?, ?, ?)
            ");

            foreach ($overrides as $permId => $status) {
                $status = (int)$status;
                if ($status === 1 || $status === 0) {
                    $stmtIns->execute([$userId, (int)$permId, $status, $adminId]);
                }
            }

            $this->db->commit();
            PermissionManager::clearCache();

            PermissionManager::logAudit(
                'user_override_updated',
                'user',
                (string)$userId,
                "Modification des exceptions de permissions pour l'utilisateur ID {$userId}."
            );

            $this->jsonResponse(['success' => true, 'message' => "Les surcharges pour l'utilisateur ont été mises à jour avec succès."]);
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->jsonResponse(['success' => false, 'message' => "Erreur lors de la sauvegarde : " . $e->getMessage()], 500);
        }
    }

    /**
     * REST API: Lance la détection automatique des permissions dans le code source
     */
    public function runScan(): void
    {
        $service = new PermissionAutoDetectorService($this->db);
        $report = $service->scanAndSync();

        $this->jsonResponse([
            'success' => true,
            'message' => "Scan de l'application terminé avec succès.",
            'report' => $report
        ]);
    }

    /**
     * REST API: Récupère les logs d'audit des privilèges
     */
    public function getAuditLogs(): void
    {
        $stmt = $this->db->query("
            SELECT * FROM permission_audit_logs 
            ORDER BY created_at DESC 
            LIMIT 100
        ");
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->jsonResponse(['success' => true, 'data' => $logs]);
    }

    /**
     * REST API: Créer une sauvegarde de configuration RBAC
     */
    public function createBackup(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $name = trim($input['name'] ?? 'Sauvegarde_' . date('Y-m-d_H-i-s'));
        $description = trim($input['description'] ?? 'Sauvegarde manuelle du système RBAC.');

        // Compiler toute la structure RBAC
        $roles = $this->db->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);
        $permissions = $this->db->query("SELECT * FROM permissions")->fetchAll(PDO::FETCH_ASSOC);
        $rolePermissions = $this->db->query("SELECT * FROM role_permissions")->fetchAll(PDO::FETCH_ASSOC);
        $userPermissions = $this->db->query("SELECT * FROM user_permissions")->fetchAll(PDO::FETCH_ASSOC);

        $snapshot = [
            'timestamp' => date('Y-m-d H:i:s'),
            'roles' => $roles,
            'permissions' => $permissions,
            'role_permissions' => $rolePermissions,
            'user_permissions' => $userPermissions
        ];

        $stmt = $this->db->prepare("
            INSERT INTO permission_backups (backup_name, description, config_data, created_by, created_by_name)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $name,
            $description,
            json_encode($snapshot, JSON_UNESCAPED_UNICODE),
            Session::get('user_id'),
            Session::get('user_name', 'Administrateur')
        ]);

        PermissionManager::logAudit('backup_created', 'system', 'backup', "Création de la sauvegarde RBAC '{$name}'.");

        $this->jsonResponse(['success' => true, 'message' => "La sauvegarde '{$name}' a été créée."]);
    }

    /**
     * REST API: Récupérer les sauvegardes disponibles
     */
    public function getBackups(): void
    {
        $stmt = $this->db->query("SELECT id, backup_name, description, created_by_name, created_at FROM permission_backups ORDER BY created_at DESC");
        $backups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->jsonResponse(['success' => true, 'data' => $backups]);
    }

    /**
     * REST API: Restaurer une sauvegarde RBAC
     */
    public function restoreBackup(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $backupId = (int) ($input['backup_id'] ?? 0);

        if ($backupId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de sauvegarde valide requis.'], 400);
            return;
        }

        $stmt = $this->db->prepare("SELECT * FROM permission_backups WHERE id = ?");
        $stmt->execute([$backupId]);
        $backup = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$backup) {
            $this->jsonResponse(['success' => false, 'message' => 'Sauvegarde introuvable.'], 404);
            return;
        }

        $snapshot = json_decode($backup['config_data'], true);
        if (!$snapshot) {
            $this->jsonResponse(['success' => false, 'message' => 'Données de sauvegarde corrompues.'], 500);
            return;
        }

        $this->db->beginTransaction();
        try {
            $this->db->exec("DELETE FROM role_permissions");
            $this->db->exec("DELETE FROM user_permissions");

            if (!empty($snapshot['role_permissions'])) {
                $stmtRP = $this->db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                foreach ($snapshot['role_permissions'] as $rp) {
                    $stmtRP->execute([$rp['role_id'], $rp['permission_id']]);
                }
            }

            if (!empty($snapshot['user_permissions'])) {
                $stmtUP = $this->db->prepare("INSERT INTO user_permissions (user_id, permission_id, is_granted, granted_by) VALUES (?, ?, ?, ?)");
                foreach ($snapshot['user_permissions'] as $up) {
                    $stmtUP->execute([$up['user_id'], $up['permission_id'], $up['is_granted'], $up['granted_by'] ?? null]);
                }
            }

            $this->db->commit();
            PermissionManager::clearCache();

            PermissionManager::logAudit('backup_restored', 'system', (string)$backupId, "Restauration de la configuration RBAC depuis la sauvegarde '{$backup['backup_name']}'.");

            $this->jsonResponse(['success' => true, 'message' => "La configuration a été restaurée avec succès depuis '{$backup['backup_name']}'."]);
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la restauration : ' . $e->getMessage()], 500);
        }
    }

    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
