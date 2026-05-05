<?php

namespace App\Core;

class Locale
{
    public const DEFAULT = 'fr';

    public const SUPPORTED = ['fr', 'en'];

    private const COOKIE_NAME = 'app_lang';
    private const COOKIE_TTL = 31536000; // 1 year

    /**
     * Resolve language from request (GET, Session, Cookie, or Default).
     */
    public static function bootstrapFromRequest(): string
    {
        $lang = self::DEFAULT;

        // 1. Priorité au paramètre d'URL (changement manuel)
        if (isset($_GET['lang']) && self::isSupported($_GET['lang'])) {
            return self::set($_GET['lang']);
        }

        // 2. Ensuite la session
        $sessionLang = Session::get('app_lang');
        if ($sessionLang && self::isSupported($sessionLang)) {
            return self::set($sessionLang);
        }

        // 3. Enfin le cookie
        $cookieLang = $_COOKIE[self::COOKIE_NAME] ?? null;
        if ($cookieLang && self::isSupported($cookieLang)) {
            return self::set($cookieLang);
        }

        return self::set(self::DEFAULT);
    }

    public static function get(): string
    {
        return Session::get('app_lang', self::DEFAULT);
    }

    public static function set(string $lang): string
    {
        $lang = self::isSupported($lang) ? strtolower($lang) : self::DEFAULT;
        
        Session::set('app_lang', $lang);
        Session::set('lang', $lang);
        self::syncCookie($lang);
        
        // On informe le Translator du changement
        Translator::load($lang);

        return $lang;
    }

    private static function syncCookie(string $lang): void
    {
        if (($_COOKIE[self::COOKIE_NAME] ?? null) === $lang) {
            return;
        }
        setcookie(self::COOKIE_NAME, $lang, [
            'expires' => time() + self::COOKIE_TTL,
            'path' => '/',
            'samesite' => 'Lax',
            'httponly' => true
        ]);
        $_COOKIE[self::COOKIE_NAME] = $lang;
    }

    public static function normalize(?string $lang): string
    {
        if (!$lang) return self::DEFAULT;
        $lang = strtolower(substr($lang, 0, 2));
        return self::isSupported($lang) ? $lang : self::DEFAULT;
    }

    public static function isSupported(?string $lang): bool
    {
        if (!$lang) return false;
        return in_array(strtolower((string) $lang), self::SUPPORTED, true);
    }
}
