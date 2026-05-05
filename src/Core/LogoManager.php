<?php

namespace App\Core;

use PDO;
use App\Services\SettingsStore;

class LogoManager
{
    private static ?LogoManager $instance = null;
    private SettingsStore $settingsStore;
    private string $logoPath = '';
    private string $logoUrl = '';
    private bool $logoExists = false;
    private array $logoInfo = [];

    private function __construct(PDO $db)
    {
        $this->settingsStore = new SettingsStore($db);
        $this->loadLogo();
    }

    public static function getInstance(PDO $db): LogoManager
    {
        if (self::$instance === null) {
            self::$instance = new self($db);
        }
        return self::$instance;
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
        
        // Si c'est déjà une URL complète ou commence par /, retourner tel quel
        if (preg_match('#^https?://#', $path) || $path[0] === '/') {
            return $path;
        }
        
        // Ajouter / au début pour l'accès web
        return '/' . ltrim($path, '/');
    }

    private function getFullFileSystemPath(): string
    {
        if (empty($this->logoPath)) {
            return '';
        }
        
        // Base directory: the project root (up from src/Core)
        $baseDir = realpath(__DIR__ . '/../../');
        if (!$baseDir) return '';

        // Clean the stored path
        $cleanPath = ltrim(str_replace('\\', '/', $this->logoPath), '/');

        // If the path already starts with 'public/', it's already absolute from root
        if (str_starts_with($cleanPath, 'public/')) {
             $fullPath = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $cleanPath);
        } else {
             // Otherwise, it's relative to public/
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

    public function updateLogo(string $newPath): bool
    {
        $this->settingsStore->set('school_logo', $newPath);
        $this->loadLogo(); // Recharger les informations
        return $this->hasLogo();
    }

    public function deleteLogo(): bool
    {
        if ($this->hasLogo()) {
            $fullPath = $this->getFullFileSystemPath();
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
        
        $this->settingsStore->set('school_logo', '');
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
