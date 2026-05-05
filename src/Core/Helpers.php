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
}
