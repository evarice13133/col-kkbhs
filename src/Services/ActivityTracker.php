<?php

namespace App\Services;

use App\Core\Session;
use PDO;

class ActivityTracker
{
    private PDO $db;
    private static bool $schemaEnsured = false;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        self::ensureSchema($db);
    }

    public static function ensureSchema(PDO $db): void
    {
        if (self::$schemaEnsured) {
            return;
        }

        $db->exec("
            CREATE TABLE IF NOT EXISTS activity_logs (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                user_role VARCHAR(50) NULL,
                event_type VARCHAR(100) NOT NULL,
                event_category VARCHAR(50) NOT NULL DEFAULT 'system',
                route VARCHAR(255) NULL,
                http_method VARCHAR(10) NULL,
                entity_type VARCHAR(50) NULL,
                entity_id INT NULL,
                event_count INT NOT NULL DEFAULT 1,
                metadata LONGTEXT NULL,
                ip_address VARCHAR(45) NULL,
                user_agent VARCHAR(255) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_activity_logs_created_at (created_at),
                INDEX idx_activity_logs_user_created (user_id, created_at),
                INDEX idx_activity_logs_type_created (event_type, created_at),
                INDEX idx_activity_logs_category_created (event_category, created_at),
                CONSTRAINT fk_activity_logs_user
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        self::$schemaEnsured = true;
    }

    public function trackRequest(string $path, string $method): void
    {
        if (!Session::isLogged()) {
            return;
        }

        $method = strtoupper($method);
        if ($method !== 'GET') {
            return;
        }

        if (in_array($path, ['/login', '/logout'], true) || str_starts_with($path, '/api/')) {
            return;
        }

        $fingerprint = md5($path . '|' . $method);
        $lastFingerprint = (string) Session::get('activity_last_page_fingerprint', '');
        $lastAt = (int) Session::get('activity_last_page_at', 0);

        if ($lastFingerprint === $fingerprint && (time() - $lastAt) < 5) {
            return;
        }

        $this->recordEvent('page_view', 'usage', [
            'route' => $path,
            'http_method' => $method,
        ]);

        Session::set('activity_last_page_fingerprint', $fingerprint);
        Session::set('activity_last_page_at', time());
    }

    public function recordLogin(int $userId, string $userRole): void
    {
        $this->recordEvent('auth_login', 'authentication', [
            'user_id' => $userId,
            'user_role' => $userRole,
            'route' => '/login',
            'http_method' => 'POST',
        ]);
    }

    public function recordSettingsUpdate(): void
    {
        $this->recordEvent('settings_updated', 'admin_activity');
    }

    public function recordGradesSaved(int $teacherId, string $periode, int $classId, int $subjectId, int $createdCount, int $updatedCount): void
    {
        $baseContext = [
            'user_id' => $teacherId,
            'user_role' => (string) Session::get('user_role'),
            'route' => '/notes/store',
            'http_method' => 'POST',
            'entity_type' => 'gradebook',
            'entity_id' => $subjectId,
            'metadata' => [
                'class_id' => $classId,
                'subject_id' => $subjectId,
                'periode' => $periode,
            ],
        ];

        if ($createdCount > 0) {
            $context = $baseContext;
            $context['event_count'] = $createdCount;
            $this->recordEvent('grades_created', 'teacher_activity', $context);
        }

        if ($updatedCount > 0) {
            $context = $baseContext;
            $context['event_count'] = $updatedCount;
            $this->recordEvent('grades_updated', 'teacher_activity', $context);
        }
    }

    public function recordEvent(string $eventType, string $category, array $context = []): void
    {
        try {
            $userId = isset($context['user_id']) ? (int) $context['user_id'] : (Session::isLogged() ? (int) Session::get('user_id') : null);
            $userRole = isset($context['user_role']) ? (string) $context['user_role'] : (Session::isLogged() ? (string) Session::get('user_role') : null);
            $route = $context['route'] ?? parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
            $method = strtoupper((string) ($context['http_method'] ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET')));
            $entityType = $context['entity_type'] ?? null;
            $entityId = isset($context['entity_id']) ? (int) $context['entity_id'] : null;
            $eventCount = max(1, (int) ($context['event_count'] ?? 1));
            $metadata = $context['metadata'] ?? null;

            $stmt = $this->db->prepare("
                INSERT INTO activity_logs
                    (user_id, user_role, event_type, event_category, route, http_method, entity_type, entity_id, event_count, metadata, ip_address, user_agent)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $userId,
                $userRole,
                $eventType,
                $category,
                $route ? (string) $route : null,
                $method ?: null,
                $entityType ? (string) $entityType : null,
                $entityId,
                $eventCount,
                $metadata !== null ? json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : null,
                isset($_SERVER['HTTP_USER_AGENT']) ? substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 255) : null,
            ]);
        } catch (\Throwable $e) {
            // Le tracking reste non bloquant.
        }
    }
}
