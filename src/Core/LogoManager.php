<?php

namespace App\Core;

use PDO;
use App\Services\SettingsStore;

class LogoManager
{
    private static array $instances = [];
    private SettingsStore $settingsStore;
    private string $logoPath = '';
    private string $logoUrl = '';
    private bool $logoExists = false;
    private array $logoInfo = [];

    private function __construct(PDO $db, ?int $teachingTypeId = null)
    {
        $this->settingsStore = new SettingsStore($db, $teachingTypeId);
        $this->loadLogo();
    }

    public static function getInstance(PDO $db, ?int $teachingTypeId = null): LogoManager
    {
        $key = $teachingTypeId ?? 0;
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($db, $teachingTypeId);
        }
        return self::$instances[$key];
    }

    private function loadLogo(): void
    {
        $this->logoPath = $this->settingsStore->get('school_logo', '');

        // Fallback vers les paramètres par défaut (teaching_type_id = NULL / 0) si vide
        if (empty($this->logoPath)) {
            $defaultStore = new SettingsStore($this->settingsStore->getDbConnection(), 0);
            $this->logoPath = $defaultStore->get('school_logo', '');
        }
        
        if (empty($this->logoPath)) {
            $this->logoExists = false;
            return;
        }

        // Normaliser le chemin pour l'accès web
        $this->logoUrl = $this->normalizeLogoPath($this->logoPath);
        
        // Vérifier si le fichier existe physiquement
        $fullPath = $this->getFullFileSystemPath();
        $this->logoExists = !empty($fullPath) && file_exists($fullPath);
        
        if ($this->logoExists) {
            $this->logoInfo = [
                'path' => $this->logoPath,
                'url' => $this->logoUrl,
                'full_path' => $fullPath,
                'size' => @filesize($fullPath),
                'mime' => @mime_content_type($fullPath) ?: 'image/png',
                'dimensions' => @getimagesize($fullPath)
            ];
        }
    }

    private function normalizeLogoPath(string $path): string
    {
        $path = trim($path);
        
        if (empty($path)) {
            return '';
        }
        
        if (preg_match('#^https?://#', $path) || $path[0] === '/') {
            return $path;
        }
        
        return '/' . ltrim($path, '/');
    }

    private function getFullFileSystemPath(): string
    {
        return $this->resolveFullPath($this->logoPath);
    }

    public function hasLogo(): bool
    {
        return $this->logoExists;
    }

    public function getLogoUrl(): string
    {
        return $this->logoUrl;
    }

    public function getLogoPath(): string
    {
        return $this->logoPath;
    }

    public function getLogoInfo(): array
    {
        return $this->logoInfo;
    }

    public function getLogoHtml(string $alt = 'Logo', string $class = '', array $attributes = []): string
    {
        if (!$this->hasLogo()) {
            return '';
        }

        $classAttr = $class ? ' class="' . htmlspecialchars($class) . '"' : '';
        $altAttr = ' alt="' . htmlspecialchars($alt) . '"';
        
        $attrString = '';
        foreach ($attributes as $name => $value) {
            $attrString .= ' ' . $name . '="' . htmlspecialchars($value) . '"';
        }
        
        return '<img src="' . htmlspecialchars($this->logoUrl) . '"' . $altAttr . $classAttr . $attrString . '>';
    }

    public function getLogoBase64(): string
    {
        if (!$this->hasLogo()) {
            return '';
        }

        $fullPath = $this->getFullFileSystemPath();
        if (empty($fullPath) || !file_exists($fullPath)) {
            return '';
        }
        $imageData = @file_get_contents($fullPath);
        if ($imageData === false) {
            return '';
        }
        $mimeType = @mime_content_type($fullPath) ?: 'image/png';
        
        return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
    }

    public function getTutelageLogoPath(): string
    {
        $path = $this->settingsStore->get('tutelage_logo', '');
        if (empty($path)) {
            $defaultStore = new SettingsStore($this->settingsStore->getDbConnection(), 0);
            $path = $defaultStore->get('tutelage_logo', '');
        }
        return $path;
    }

    public function hasTutelageLogo(): bool
    {
        $path = $this->getTutelageLogoPath();
        if (empty($path)) {
            return false;
        }
        $fullPath = $this->resolveFullPath($path);
        return !empty($fullPath) && file_exists($fullPath);
    }

    public function getTutelageLogoUrl(): string
    {
        $path = $this->getTutelageLogoPath();
        if (empty($path)) {
            return '';
        }
        return $this->normalizeLogoPath($path);
    }

    public function getTutelageLogoBase64(): string
    {
        $path = $this->getTutelageLogoPath();
        if (empty($path)) {
            return '';
        }
        $fullPath = $this->resolveFullPath($path);
        if (empty($fullPath) || !file_exists($fullPath)) {
            return '';
        }
        $imageData = file_get_contents($fullPath);
        $mimeType = @mime_content_type($fullPath) ?: 'image/png';
        return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
    }

    public function updateTutelageLogo(string $newPath, ?int $teachingTypeId = null): bool
    {
        $this->settingsStore->set('tutelage_logo', $newPath, $teachingTypeId);
        return $this->hasTutelageLogo();
    }

    public function deleteTutelageLogo(?int $teachingTypeId = null): bool
    {
        $path = $this->getTutelageLogoPath();
        if (!empty($path)) {
            $fullPath = $this->resolveFullPath($path);
            if (!empty($fullPath) && file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
        $this->settingsStore->set('tutelage_logo', '', $teachingTypeId);
        return true;
    }

    private function resolveFullPath(string $path): string
    {
        if (empty($path)) return '';
        $baseDir = realpath(__DIR__ . '/../../');
        $cleanPath = ltrim(str_replace('\\', '/', $path), '/');

        $candidates = [];
        if ($baseDir) {
            $candidates[] = $baseDir . '/' . $cleanPath;
            $candidates[] = $baseDir . '/public/' . ltrim($cleanPath, 'public/');
        }
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/' . $cleanPath;
            $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/public/' . ltrim($cleanPath, 'public/');
        }

        foreach ($candidates as $cand) {
            $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $cand);
            if (file_exists($normalized) && is_file($normalized)) {
                return $normalized;
            }
        }

        return $baseDir ? $baseDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $cleanPath) : '';
    }

    public function updateLogo(string $newPath, ?int $teachingTypeId = null): bool
    {
        $this->settingsStore->set('school_logo', $newPath, $teachingTypeId);
        $this->loadLogo();
        return $this->hasLogo();
    }

    public function deleteLogo(?int $teachingTypeId = null): bool
    {
        if ($this->hasLogo()) {
            $fullPath = $this->getFullFileSystemPath();
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
        
        $this->settingsStore->set('school_logo', '', $teachingTypeId);
        $this->loadLogo();
        return !$this->hasLogo();
    }

    public function getFallbackLetter(): string
    {
        $schoolCode = $this->settingsStore->get('school_code', '');
        $schoolName = $this->settingsStore->get('school_name', 'NotesMaster');
        
        $identity = $schoolCode !== '' ? $schoolCode : $schoolName;
        return strtoupper(substr($identity, 0, 1));
    }

    public function debugInfo(): array
    {
        return [
            'logo_path' => $this->logoPath,
            'logo_url' => $this->logoUrl,
            'logo_exists' => $this->logoExists,
            'logo_info' => $this->logoInfo,
            'full_filesystem_path' => $this->getFullFileSystemPath(),
            'file_exists_check' => file_exists($this->getFullFileSystemPath()),
            'is_readable' => is_readable($this->getFullFileSystemPath()),
            'base_url' => $_SERVER['HTTP_HOST'] ?? 'unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
            'script_dir' => __DIR__,
        ];
    }
}
