<?php

namespace App\Core;

/**
 * Classe Security
 * 
 * Centralise les fonctions de sécurité : headers HTTP, échappement XSS,
 * logs de sécurité et protection CSRF.
 */
class Security {

    /**
     * Applique les en-têtes HTTP de sécurité.
     */
    public static function applyHeaders() {
        // Anti-Clickjacking (SAMEORIGIN permet l'intégration d'iframes sur le même domaine pour la prévisualisation d'impression)
        header('X-Frame-Options: SAMEORIGIN');
        
        // Anti-MIME-Sniffing
        header('X-Content-Type-Options: nosniff');
        
        // HSTS (si HTTPS est détecté)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
        
        // Content-Security-Policy (CSP) de base
        // Note: On autorise 'unsafe-inline' temporairement car le projet utilise des styles/scripts inline
        // mais on restreint les sources aux domaines de confiance.
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src 'self' data:;");
    }

    /**
     * Fonction utilitaire pour l'échappement XSS.
     * 
     * @param mixed $str
     * @return string
     */
    public static function h($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Enregistre un événement suspect dans les logs de sécurité.
     * 
     * @param string $message
     */
    public static function log($message) {
        $logDir = __DIR__ . '/../../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/security.log';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userId = Session::get('user_id', 'GUEST');
        $timestamp = date('Y-m-d H:i:s');
        
        $entry = sprintf("[%s] [IP: %s] [UID: %s] %s\n", $timestamp, $ip, $userId, $message);
        file_put_contents($logFile, $entry, FILE_APPEND);
    }

    /**
     * Vérifie le taux de requêtes pour les actions sensibles.
     * 
     * @param string $action
     * @param int $maxAttempts
     * @param int $durationSeconds
     * @return bool
     */
    public static function checkRateLimit($action, $maxAttempts = 5, $durationSeconds = 300) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = "rate_limit_{$action}_{$ip}";
        
        $attempts = Session::get($key, []);
        $now = time();
        
        // Nettoie les anciennes tentatives
        $attempts = array_filter($attempts, function($timestamp) use ($now, $durationSeconds) {
            return $timestamp > ($now - $durationSeconds);
        });
        
        if (count($attempts) >= $maxAttempts) {
            self::log("Rate limit atteint pour l'action '$action' par l'IP $ip");
            return false;
        }
        
        $attempts[] = $now;
        Session::set($key, $attempts);
        return true;
    }

    /**
     * Valide que l'utilisateur a une session valide et n'est pas expiré.
     */
    public static function validateSession() {
        if (!Session::isLogged()) {
            return false;
        }

        // Vérification de l'activité (ex: expire après 2h d'inactivité totale)
        $lastActivity = Session::get('last_activity', 0);
        $now = time();
        if ($lastActivity > 0 && ($now - $lastActivity > 7200)) {
            Session::destroy();
            self::log("Session expirée pour inactivité prolongée.");
            return false;
        }
        
        Session::set('last_activity', $now);
        return true;
    }
}

/**
 * Définit h() dans le scope global pour faciliter l'usage dans les vues.
 */
if (!function_exists('h')) {
    function h($str) {
        return Security::h($str);
    }
}
