<?php

namespace App\Services;

class PhotoStudioService
{
    private const MAX_IMAGE_BYTES = 20 * 1024 * 1024;
    private const MAX_IMAGE_PIXELS = 20_000_000;
    private const MAX_IMAGE_SIDE = 8000;

    private string $uploadDir;
    private string $webPath;

    public function __construct()
    {
        $this->uploadDir = __DIR__ . '/../../public/uploads/studio/';
        $this->webPath = '/public/uploads/studio/';
        $this->ensureUploadDirectory();
    }

    public function ensureUploadDirectory(): void
    {
        if (!is_dir($this->uploadDir) && !mkdir($this->uploadDir, 0755, true) && !is_dir($this->uploadDir)) {
            throw new \RuntimeException('Unable to create the studio photo directory.');
        }
    }

    public function saveStudentPhoto(string $dataUrl, int $studentId): array
    {
        $trimmed = trim($dataUrl);
        if ($trimmed === '' || strlen($trimmed) > (int) ceil(self::MAX_IMAGE_BYTES * 4 / 3) + 128) {
            return ['success' => false, 'error' => 'studio_photo_invalid_size', 'http_status' => 413];
        }

        if (!preg_match('/^data:(image\/(?:jpeg|png|webp));base64,([A-Za-z0-9+\/]+={0,2})$/i', $trimmed, $matches)) {
            return ['success' => false, 'error' => 'studio_photo_invalid_format', 'http_status' => 400];
        }

        $expectedMime = strtolower($matches[1]);
        $imageBytes = base64_decode($matches[2], true);
        if ($imageBytes === false || strlen($imageBytes) > self::MAX_IMAGE_BYTES) {
            return ['success' => false, 'error' => 'studio_photo_invalid_size', 'http_status' => 413];
        }

        $imageInfo = @getimagesizefromstring($imageBytes);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return ['success' => false, 'error' => 'studio_photo_mime_check_failed', 'http_status' => 500];
        }
        $actualMime = finfo_buffer($finfo, $imageBytes);
        finfo_close($finfo);

        if (
            $imageInfo === false
            || $actualMime !== $expectedMime
            || ($imageInfo['mime'] ?? '') !== $expectedMime
            || $imageInfo[0] <= 0
            || $imageInfo[1] <= 0
            || $imageInfo[0] > self::MAX_IMAGE_SIDE
            || $imageInfo[1] > self::MAX_IMAGE_SIDE
            || $imageInfo[0] * $imageInfo[1] > self::MAX_IMAGE_PIXELS
        ) {
            return ['success' => false, 'error' => 'studio_photo_invalid_image', 'http_status' => 400];
        }

        $extension = match ($expectedMime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        };
        $fileName = 'student_' . $studentId . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
        $destination = $this->uploadDir . $fileName;

        if (file_put_contents($destination, $imageBytes, LOCK_EX) === false) {
            return ['success' => false, 'error' => 'studio_photo_disk_save_failed', 'http_status' => 500];
        }
        chmod($destination, 0644);

        return [
            'success' => true,
            'path' => $this->webPath . $fileName,
            'full_path' => $destination,
        ];
    }

    public function removeSavedStudentPhoto(string $path): bool
    {
        if (!preg_match('#^/public/uploads/studio/student_[1-9][0-9]*_[a-f0-9]{12}\.(?:jpg|png|webp)$#', $path)) {
            return false;
        }

        $filePath = $this->uploadDir . basename($path);
        return !is_file($filePath) || unlink($filePath);
    }
}
