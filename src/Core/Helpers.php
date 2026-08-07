<?php

namespace App\Core;

class Helpers
{
    /**
     * Normalize logo path for display in production
     * Ensures proper path format for web access
     */
    public static function normalizeLogoPath(string $logoPath): string
    {
        $logoPath = trim($logoPath);
        
        if (empty($logoPath)) {
            return '';
        }
        
        // If it's already a full URL or starts with /, return as is
        if (preg_match('#^https?://#', $logoPath) || $logoPath[0] === '/') {
            return $logoPath;
        }
        
        // Ensure path starts with / for web access
        return '/' . ltrim($logoPath, '/');
    }
    
    /**
     * Get logo HTML img tag with proper path
     */
    public static function getLogoHtml(string $logoPath, string $alt = 'Logo', string $class = ''): string
    {
        $normalizedPath = self::normalizeLogoPath($logoPath);
        
        if (empty($normalizedPath)) {
            return '';
        }
        
        $classAttr = $class ? ' class="' . htmlspecialchars($class) . '"' : '';
        $altAttr = ' alt="' . htmlspecialchars($alt) . '"';
        
        return '<img src="' . htmlspecialchars($normalizedPath) . '"' . $altAttr . $classAttr . '>';
    }

    /**
     * Format creation decree text by splitting on semicolons and converting to line breaks
     *
     * @param string|null $decree Raw text from database
     * @param string $separator Glue between lines (default: '<br>')
     * @param bool $escapeHtml Whether to htmlspecialchars each line (default: true)
     * @return string Formatted HTML string with clean line breaks
     */
    public static function formatCreationDecree(?string $decree, string $separator = '<br>', bool $escapeHtml = true): string
    {
        if ($decree === null || trim($decree) === '') {
            return '';
        }

        // Séparation sur les points-virgules (;) ainsi que sur les retours à la ligne (\r\n / \n)
        $parts = preg_split('/[;\r\n]+/', $decree);
        $cleaned = [];

        foreach ($parts as $part) {
            $trimmed = trim($part);
            if ($trimmed !== '') {
                $cleaned[] = $escapeHtml ? htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8') : $trimmed;
            }
        }

        return implode($separator, $cleaned);
    }
}
