<?php

namespace App\Core;

use PDO;

/**
 * Class PermissionManager
 * 
 * Moteur central de contrôle d'accès basé sur les rôles et surcharges utilisateurs (RBAC Enterprise) pour NoteMaster.
 */
class PermissionManager
{
    private static ?array $userEffectivePermissionsCache = [];
    private static ?array $rolePermissionsCache = [];
    private static ?int $systemRbacVersion = null;

    /**
     * Vérifie si un utilisateur possède une permission spécifique.
     * Si l'ID utilisateur n'est pas fourni, utilise l'utilisateur actuellement connecté.
     * 
     * Logique de priorité :
     * 1. Superadmin -> Accès complet (true)
     * 2. Surcharge Utilisateur explicite (user_permissions) :
     *    - is_granted = 1 -> AUTORISÉ (true)
     *    - is_granted = 0 -> INTERDIT (false)
     * 3. Attribution via le Rôle (role_permissions) :
     *    - présent et permission active -> AUTORISÉ (true)
     * 4. Par défaut -> INTERDIT (false)
     * 
     * @param string $permissionCode Code unique de la permission (ex: 'manage_rbac')
     * @param int|null $userId ID de l'utilisateur (optionnel)
     * @return bool
     */
    public static function hasPermission(string $permissionCode, ?int $userId = null): bool
    {
        if ($userId === null) {
            if (!Session::isLogged()) {
                return false;
            }
            $userId = (int) Session::get('user_id');
            $userRole = (string) Session::get('user_role');
        } else {
            $userRole = self::getUserRoleById($userId);
        }

        if (empty($permissionCode) || $userId <= 0) {
            return false;
        }

        // Vérifier la fraîcheur du cache RBAC
        self::checkCacheFreshness();

        // Charger l'ensemble des permissions effectives de l'utilisateur
        $effective = self::getEffectivePermissionsForUser($userId, $userRole);

        return !empty($effective[$permissionCode]);
    }

    /**
     * Impose qu'un utilisateur possède une permission spécifique.
     * En cas d'échec, enregistre une log de sécurité et renvoie une page 403.
     * 
     * @param string $permissionCode
     * @return void
     */
    public static function requirePermission(string $permissionCode): void
    {
        if (!self::hasPermission($permissionCode)) {
            $username = Session::get('user_name', 'INCONNU');
            $role = Session::get('user_role', 'INVITE');
            Security::log("Accès refusé pour la permission '{$permissionCode}' - Utilisateur: {$username} (Rôle: {$role})");
            
            self::denyAccess();
        }
    }

    /**
     * Vérifie si l'utilisateur possède l'un des rôles spécifiés.
     * 
     * @param string|array $roles Rôle unique ou tableau de rôles
     * @return bool
     */
    public static function hasRole($roles): bool
    {
        if (!Session::isLogged()) {
            return false;
        }

        $userRole = Session::get('user_role');
        if (is_array($roles)) {
            return in_array($userRole, $roles, true);
        }

        return $userRole === $roles;
    }

    /**
     * Impose que l'utilisateur possède l'un des rôles spécifiés.
     * 
     * @param string|array $roles
     * @return void
     */
    public static function requireRole($roles): void
    {
        if (!self::hasRole($roles)) {
            $username = Session::get('user_name', 'INCONNU');
            $role = Session::get('user_role', 'INVITE');
            $required = is_array($roles) ? implode(', ', $roles) : $roles;
            Security::log("Accès refusé pour les rôles requis [{$required}] - Utilisateur: {$username} (Rôle actuel: {$role})");

            self::denyAccess();
        }
    }

    /**
     * Récupère la carte associative des permissions effectives d'un utilisateur [perm_code => true/false].
     * 
     * @param int $userId
     * @param string|null $roleCode
     * @return array [perm_code => bool]
     */
    public static function getEffectivePermissionsForUser(int $userId, ?string $roleCode = null): array
    {
        if (isset(self::$userEffectivePermissionsCache[$userId])) {
            return self::$userEffectivePermissionsCache[$userId];
        }

        if ($roleCode === null) {
            $roleCode = self::getUserRoleById($userId);
        }

        try {
            $db = Database::getInstance()->getConnection();

            // 1. Récupérer toutes les permissions actives du catalogue
            $stmt = $db->query("SELECT id, perm_code FROM permissions WHERE status = 'active'");
            $catalog = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

            // 2. Récupérer les permissions accordées au rôle de l'utilisateur
            $rolePerms = [];
            if ($roleCode) {
                $rolePerms = self::getPermissionsForRoleCode($roleCode);
            }

            // 3. Récupérer les surcharges d'exceptions individuelles de l'utilisateur
            $stmtUserOver = $db->prepare("
                SELECT p.perm_code, up.is_granted
                FROM user_permissions up
                JOIN permissions p ON up.permission_id = p.id
                WHERE up.user_id = ? AND p.status = 'active'
            ");
            $stmtUserOver->execute([$userId]);
            $userOverrides = $stmtUserOver->fetchAll(PDO::FETCH_KEY_PAIR) ?: []; // [perm_code => 1 ou 0]

            // 4. Calcul du statut final par permission selon la priorité
            $effectiveMap = [];
            foreach ($catalog as $permId => $permCode) {
                if (isset($userOverrides[$permCode])) {
                    // Surcharge explicite utilisateur (DENY or ALLOW)
                    $effectiveMap[$permCode] = ((int)$userOverrides[$permCode] === 1);
                } elseif ($roleCode === 'superadmin') {
                    // Superadmin par défaut
                    $effectiveMap[$permCode] = true;
                } else {
                    // Rôle
                    $effectiveMap[$permCode] = in_array($permCode, $rolePerms, true);
                }
            }

            self::$userEffectivePermissionsCache[$userId] = $effectiveMap;
            return $effectiveMap;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Récupère la liste des permissions d'un rôle par son code.
     * 
     * @param string $roleCode
     * @return array Liste des codes de permission
     */
    public static function getPermissionsForRoleCode(string $roleCode): array
    {
        if (isset(self::$rolePermissionsCache[$roleCode])) {
            return self::$rolePermissionsCache[$roleCode];
        }

        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT p.perm_code 
                FROM role_permissions rp
                JOIN roles r ON rp.role_id = r.id
                JOIN permissions p ON rp.permission_id = p.id
                WHERE r.role_code = ? AND p.status = 'active'
            ");
            $stmt->execute([$roleCode]);
            $perms = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
            self::$rolePermissionsCache[$roleCode] = $perms;
            return $perms;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Récupère la version système du cache RBAC.
     */
    public static function getSystemRbacVersion(): int
    {
        if (self::$systemRbacVersion !== null) {
            return self::$systemRbacVersion;
        }

        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'rbac_version' LIMIT 1");
            $ver = $stmt ? $stmt->fetchColumn() : null;
            self::$systemRbacVersion = $ver !== false && $ver !== null ? (int)$ver : 1;
        } catch (\Throwable $e) {
            self::$systemRbacVersion = 1;
        }

        return self::$systemRbacVersion;
    }

    /**
     * Invalide globalement le cache RBAC de l'application.
     * Incrémente la version `rbac_version` en base et vide les caches locaux.
     */
    public static function clearCache(): void
    {
        self::$userEffectivePermissionsCache = [];
        self::$rolePermissionsCache = [];

        try {
            $db = Database::getInstance()->getConnection();
            $newVer = time();
            $stmt = $db->prepare("
                INSERT INTO settings (setting_key, setting_value) 
                VALUES ('rbac_version', ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $stmt->execute([(string)$newVer]);
            self::$systemRbacVersion = $newVer;
        } catch (\Throwable $e) {
            self::$systemRbacVersion = time();
        }
    }

    /**
     * Vérifie si le cache de la session active est à jour.
     */
    private static function checkCacheFreshness(): void
    {
        if (Session::isLogged()) {
            $sysVer = self::getSystemRbacVersion();
            $sessionVer = Session::get('rbac_version');

            if ($sessionVer === null || (int)$sessionVer !== $sysVer) {
                self::$userEffectivePermissionsCache = [];
                self::$rolePermissionsCache = [];
                Session::set('rbac_version', $sysVer);
            }
        }
    }

    /**
     * Journalise une action d'audit de sécurité RBAC.
     */
    public static function logAudit(string $actionType, string $entityType, ?string $entityId, ?string $details = null, $payloadBefore = null, $payloadAfter = null): void
    {
        try {
            $db = Database::getInstance()->getConnection();
            $userId = Session::get('user_id');
            $userName = Session::get('user_name', 'Système');
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            $stmt = $db->prepare("
                INSERT INTO permission_audit_logs (user_id, user_name, action_type, entity_type, entity_id, details, payload_before, payload_after, ip_address)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $userName,
                $actionType,
                $entityType,
                $entityId,
                $details,
                $payloadBefore ? json_encode($payloadBefore, JSON_UNESCAPED_UNICODE) : null,
                $payloadAfter ? json_encode($payloadAfter, JSON_UNESCAPED_UNICODE) : null,
                $ip
            ]);
        } catch (\Throwable $e) {
            // Ne bloque pas le flux métier si l'audit échoue
        }
    }

    /**
     * Récupère le rôle d'un utilisateur par son ID.
     */
    private static function getUserRoleById(int $userId): ?string
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            return $stmt->fetchColumn() ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Refuse l'accès (Erreur 403 HTTP).
     */
    private static function denyAccess(): void
    {
        http_response_code(403);
        
        $errorPage = __DIR__ . '/../Views/errors/403.php';
        if (file_exists($errorPage)) {
            include $errorPage;
        } else {
            echo "<div style='text-align:center; font-family:sans-serif; margin-top:100px; color:#333;'>";
            echo "<h1 style='font-size:3rem; margin-bottom:10px; color:#dc3545;'>403 - Accès Interdit</h1>";
            echo "<p style='font-size:1.2rem; color:#666;'>Vous ne disposez pas des autorisations nécessaires pour accéder à cette ressource.</p>";
            echo "<a href='/' style='display:inline-block; margin-top:20px; padding:10px 20px; background:#007BFF; color:#fff; text-decoration:none; border-radius:5px;'>Retour à l'accueil</a>";
            echo "</div>";
        }
        exit;
    }
}
