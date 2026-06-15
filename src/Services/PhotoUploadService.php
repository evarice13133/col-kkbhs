<?php

namespace App\Services;

use App\Core\Security;

/**
 * Service de gestion des photos des élèves
 * 
 * Gère l'upload, la validation, le stockage et la suppression des photos
 * avec des mesures de sécurité strictes.
 */
class PhotoUploadService
{
    private string $uploadDir;
    private string $webPath;
    private array $allowedMimeTypes;
    private array $allowedExtensions;
    private int $maxFileSize;
    private int $maxWidth;
    private int $maxHeight;

    public function __construct()
    {
        // Chemin du répertoire d'upload (relatif à la racine du projet)
        $this->uploadDir = __DIR__ . '/../../public/uploads/students/';
        $this->webPath = '/public/uploads/students/';
        
        // Formats autorisés
        $this->allowedMimeTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp'
        ];
        
        $this->allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        
        // Taille maximale: 5MB
        $this->maxFileSize = 5 * 1024 * 1024;
        
        // Dimensions maximales: augmentées pour éviter le rejet des images (ex: gros scans)
        $this->maxWidth = 5000;
        $this->maxHeight = 5000;
        
        // Créer le répertoire s'il n'existe pas
        $this->ensureUploadDirectory();
    }

    /**
     * Vérifie et crée le répertoire d'upload
     */
    private function ensureUploadDirectory(): void
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Valide et upload une photo d'élève
     * 
     * @param array $file Le fichier $_FILES['photo_eleve']
     * @param int $studentId L'ID de l'élève
     * @return array ['success' => bool, 'path' => string, 'error' => string]
     */
    public function uploadPhoto(array $file, int $studentId): array
    {
        // Vérifier si un fichier a été uploadé
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'error' => 'Aucun fichier uploadé'];
        }

        // Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée par php.ini',
                UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale autorisée par le formulaire',
                UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement uploadé',
                UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été uploadé',
                UPLOAD_ERR_NO_TMP_DIR => 'Répertoire temporaire manquant',
                UPLOAD_ERR_CANT_WRITE => 'Impossible d\'écrire le fichier sur le disque',
                UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté l\'upload'
            ];
            return ['success' => false, 'error' => $errorMessages[$file['error']] ?? 'Erreur d\'upload inconnue'];
        }

        // Vérifier la taille du fichier
        if ($file['size'] > $this->maxFileSize) {
            return ['success' => false, 'error' => 'Le fichier dépasse la taille maximale de 5MB'];
        }

        // Vérifier l'extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            return ['success' => false, 'error' => 'Extension non autorisée. Extensions autorisées: ' . implode(', ', $this->allowedExtensions)];
        }

        // Vérifier le type MIME réel
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            return ['success' => false, 'error' => 'Type MIME non autorisé. Type détecté: ' . $mimeType];
        }

        // Vérifier que c'est bien une image valide
        if (!getimagesize($file['tmp_name'])) {
            return ['success' => false, 'error' => 'Le fichier n\'est pas une image valide'];
        }

        // Vérifier les dimensions
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo[0] > $this->maxWidth || $imageInfo[1] > $this->maxHeight) {
            return ['success' => false, 'error' => "L'image dépasse les dimensions maximales ({$this->maxWidth}x{$this->maxHeight}px)"];
        }

        // Générer un nom de fichier sécurisé et unique
        $fileName = $this->generateSecureFileName($studentId, $extension);
        $destination = $this->uploadDir . $fileName;

        // Déplacer le fichier
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'error' => 'Impossible de déplacer le fichier uploadé'];
        }

        // Définir les permissions
        chmod($destination, 0644);

        return [
            'success' => true,
            'path' => $this->webPath . $fileName,
            'filename' => $fileName
        ];
    }

    /**
     * Supprime une photo d'élève
     * 
     * @param string $photoPath Le chemin de la photo (ex: /uploads/students/student_123_20250615.jpg)
     * @return bool
     */
    public function deletePhoto(string $photoPath): bool
    {
        if (empty($photoPath)) {
            return false;
        }

        // Extraire le nom du fichier du chemin web
        $fileName = basename($photoPath);
        $fullPath = $this->uploadDir . $fileName;

        // Vérifier que le fichier existe et est dans le bon répertoire
        if (file_exists($fullPath) && strpos($fullPath, $this->uploadDir) === 0) {
            return unlink($fullPath);
        }

        return false;
    }

    /**
     * Génère un nom de fichier sécurisé et unique
     * 
     * @param int $studentId L'ID de l'élève
     * @param string $extension L'extension du fichier
     * @return string Le nom de fichier généré
     */
    private function generateSecureFileName(int $studentId, string $extension): string
    {
        // Format: student_{id}_{timestamp}.{ext}
        // Utiliser un timestamp avec microsecondes pour éviter les collisions
        $timestamp = microtime(true);
        $timestampStr = str_replace('.', '', $timestamp);
        
        return 'student_' . $studentId . '_' . $timestampStr . '.' . $extension;
    }

    /**
     * Obtient l'URL web d'une photo
     * 
     * @param string $photoPath Le chemin stocké en base de données
     * @return string L'URL complète
     */
    public function getPhotoUrl(string $photoPath): string
    {
        if (empty($photoPath)) {
            return '';
        }

        // Si le chemin commence déjà par /, le retourner tel quel
        if (strpos($photoPath, '/') === 0) {
            return $photoPath;
        }

        return '/' . ltrim($photoPath, '/');
    }

    /**
     * Vérifie si une photo existe
     * 
     * @param string $photoPath Le chemin de la photo
     * @return bool
     */
    public function photoExists(string $photoPath): bool
    {
        if (empty($photoPath)) {
            return false;
        }

        $fileName = basename($photoPath);
        $fullPath = $this->uploadDir . $fileName;

        return file_exists($fullPath);
    }

    /**
     * Nettoie les anciennes photos orphelines (optionnel)
     * 
     * @param array $activePaths Les chemins actifs des photos
     * @return int Nombre de fichiers supprimés
     */
    public function cleanupOrphanedPhotos(array $activePaths): int
    {
        $deletedCount = 0;
        $activeFiles = array_map('basename', $activePaths);

        foreach (glob($this->uploadDir . 'student_*') as $file) {
            $fileName = basename($file);
            if (!in_array($fileName, $activeFiles)) {
                if (unlink($file)) {
                    $deletedCount++;
                }
            }
        }

        return $deletedCount;
    }
}
