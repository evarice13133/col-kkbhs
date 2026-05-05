<?php

namespace App\Services;

use PDO;

class JobRunLogger
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
            CREATE TABLE IF NOT EXISTS system_job_runs (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                job_name VARCHAR(100) NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'running',
                message TEXT NULL,
                details LONGTEXT NULL,
                attempts INT UNSIGNED NOT NULL DEFAULT 1,
                started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                finished_at DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_system_job_runs_name_started (job_name, started_at),
                INDEX idx_system_job_runs_status_started (status, started_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        self::$schemaEnsured = true;
    }

    public function start(string $jobName, array $details = []): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO system_job_runs (job_name, status, message, details, attempts, started_at)
            VALUES (?, 'running', ?, ?, 1, NOW())
        ");
        $stmt->execute([
            $jobName,
            'Job started.',
            !empty($details) ? json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function finish(int $runId, string $status, string $message, array $details = [], int $attempts = 1): void
    {
        $stmt = $this->db->prepare("
            UPDATE system_job_runs
            SET status = ?, message = ?, details = ?, attempts = ?, finished_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $status,
            $message,
            !empty($details) ? json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            max(1, $attempts),
            $runId,
        ]);
    }

    public function latest(string $jobName): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM system_job_runs
            WHERE job_name = ?
            ORDER BY started_at DESC
            LIMIT 1
        ");
        $stmt->execute([$jobName]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}
