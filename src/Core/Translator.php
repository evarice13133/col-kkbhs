<?php

namespace App\Core;

/**
 * Centralized application translator (FR + EN external files).
 * Supports dynamic replacements (e.g. :count).
 */
class Translator
{
    private static $translations = [];
    private static $currentLang = null;

    /**
     * Initialise et charge les traductions pour la langue donnée.
     */
    public static function load(string $lang): void
    {
        if (self::$currentLang === $lang && !empty(self::$translations)) {
            return;
        }

        // Sécurisation du nom de fichier
        $lang = preg_replace('/[^a-z]/', '', strtolower($lang));
        $filePath = __DIR__ . '/../../i18n/' . $lang . '.php';
        
        if (file_exists($filePath)) {
            self::$translations = require $filePath;
            self::$currentLang = $lang;
        } else {
            // Fallback sur le français si le fichier n'existe pas
            $fallbackPath = __DIR__ . '/../../i18n/fr.php';
            if (file_exists($fallbackPath)) {
                self::$translations = require $fallbackPath;
                self::$currentLang = 'fr';
            }
        }
    }

    public static function lang(): string
    {
        return self::$currentLang ?? 'fr';
    }

    public static function translate(string $key, array $replacements = [], $count = null): string
    {
        // S'assure que les traductions sont chargées
        if (empty(self::$translations)) {
            self::load(Locale::get());
        }

        // Nettoyage de la clé (suppression BOM ou espaces)
        $cleanKey = trim($key, " \t\n\r\0\x0B\u{FEFF}");

        if ($cleanKey === 'lang') {
            return self::$currentLang;
        }

        $text = self::$translations[$cleanKey] ?? $key;

        if ($count !== null) {
            $replacements['count'] = $count;
        }

        foreach ($replacements as $placeholder => $value) {
            $text = str_replace(':' . $placeholder, (string) $value, $text);
        }

        return $text;
    }
}
