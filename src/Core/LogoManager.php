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
        if (empty($this->logoPath)) {
            return '';
        }
        
        $baseDir = realpath(__DIR__ . '/../../');
        if (!$baseDir) return '';

        $cleanPath = ltrim(str_replace('\\', '/', $this->logoPath), '/');

        if (str_starts_with($cleanPath, 'public/')) {
             $fullPath = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $cleanPath);
        } else {
             $fullPath = $baseDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $cleanPath);
        }

        return $fullPath;
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
        $imageData = file_get_contents($fullPath);
        $mimeType = mime_content_type($fullPath);
        
        return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
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
                unlink($fullPath);
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
