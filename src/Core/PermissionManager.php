<?php

namespace App\Core;

use PDO;

/**
 * Class PermissionManager
 * 
 * Centrale de contrôle d'accès basée sur les rôles (RBAC) pour NoteMaster.
 * Permet de vérifier les droits et rôles des utilisateurs connectés.
 */
class PermissionManager
{
    private static ?array $permissionsCache = null;

    /**
     * Vérifie si l'utilisateur actuellement connecté possède une permission spécifique.
     * Les superadmins ont accès à tout par défaut.
     * 
     * @param string $permissionCode
     * @return bool
     */
    public static function hasPermission(string $permissionCode): bool
    {
        if (!Session::isLogged()) {
            return false;
        }

        $role = Session::get('user_role');
        if ($role === 'superadmin') {
            return true;
        }

        if (self::$permissionsCache === null) {
            self::loadPermissionsForRole((string) $role);
        }

        return in_array($permissionCode, self::$permissionsCache ?? [], true);
    }

    /**
     * Impose qu'un utilisateur possède une permission spécifique.
     * En cas d'échec, enregistre une log de sécurité et renvoie une erreur 403.
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
     * @param string|array $roles Rôle unique ou tableau de rôles autorisés
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
     * En cas d'échec, log l'action et renvoie une erreur 403.
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
     * Charge en cache les permissions du rôle connecté pour cette requête.
     * 
     * @param string $roleCode
     * @return void
     */
    private static function loadPermissionsForRole(string $roleCode): void
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT p.perm_code 
                FROM role_permissions rp
                JOIN roles r ON rp.role_id = r.id
                JOIN permissions p ON rp.permission_id = p.id
                WHERE r.role_code = ?
            ");
            $stmt->execute([$roleCode]);
            self::$permissionsCache = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (\Throwable $e) {
            self::$permissionsCache = [];
        }
    }

    /**
     * Affiche la page d'erreur 403 et stoppe l'exécution.
     * 
     * @return void
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
