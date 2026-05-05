<?php

namespace App\Core;

/**
 * Classe Session
 * 
 * Gère les opérations liées aux sessions PHP, les messages flash 
 * et la protection contre les failles CSRF.
 *
 * @package App\Core
 */
class Session
{

    /**
     * Démarre la session de manière sécurisée.
     */
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Sécurisation de l'URL : Force l'usage des cookies uniquement
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_trans_sid', 0);

            // Cookies stricts
            $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            session_start();

            // Fixation de session : rotation d'identifiant toutes les 30 minutes d'inactivité
            if (!isset($_SESSION['last_regeneration'])) {
                self::regenerate();
            } elseif (time() - $_SESSION['last_regeneration'] > 1800) {
                self::regenerate();
            }
        }
    }

    /**
     * Régénère l'identifiant de session de manière sécurisée.
     */
    public static function regenerate()
    {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }

    /**
     * Définit une variable de session.
     * 
     * @param string $key Clé de la variable
     * @param mixed $value Valeur à stocker
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Récupère une variable de session.
     * 
     * @param string $key Clé à récupérer
     * @param mixed|null $default Valeur par défaut si la clé n'existe pas
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Supprime une variable de session spécifique.
     * 
     * @param string $key Clé à supprimer
     */
    public static function remove($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Détruit complètement la session actuelle.
     */
    public static function destroy()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }

    /**
     * Vérifie si un utilisateur est actuellement authentifié.
     * 
     * @return bool
     */
    public static function isLogged()
    {
        return self::get('user_id') !== null;
    }

    /**
     * Vérifie si une clé spécifique existe en session.
     * 
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Définit une variable de session destinée à n'être lue qu'une fois (Message Flash).
     * 
     * @param string $key Clé de la variable
     * @param mixed $value Valeur à stocker
     */
    public static function setFlash($key, $value)
    {
        self::set($key, $value);
    }

    /**
     * Récupère une variable de session puis la supprime immédiatement (Message Flash).
     * 
     * @param string $key
     * @return mixed|null
     */
    public static function getFlash($key)
    {
        $value = self::get($key);
        if ($value !== null) {
            self::remove($key);
        }
        return $value;
    }

    /**
     * Génère un jeton CSRF et le stocke en session s'il n'existe pas déjà.
     * 
     * @return string Le jeton CSRF valide
     */
    public static function generateCsrfToken()
    {
        if (!self::has('csrf_token')) {
            self::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return self::get('csrf_token');
    }

    /**
     * Vérifie si le jeton fourni correspond à celui stocké en session.
     * 
     * @param string|null $token Le jeton à vérifier (provenant généralement de $_POST)
     * @return bool True si valide, false sinon
     */
    public static function verifyCsrfToken($token)
    {
        $sessionToken = self::get('csrf_token');
        if (!$sessionToken || !$token) {
            return false;
        }
        return hash_equals($sessionToken, $token);
    }
}
