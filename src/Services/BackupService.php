<?php

namespace App\Services;

use PDO;
use RuntimeException;
use ZipArchive;

class BackupService
{
    private PDO $db;
    private SettingsStore $settingsStore;
    private JobRunLogger $jobLogger;
    private string $projectRoot;

    public function __construct(PDO $db, ?SettingsStore $settingsStore = null, ?JobRunLogger $jobLogger = null)
    {
        $this->db = $db;
        $this->settingsStore = $settingsStore ?? new SettingsStore($db);
        $this->jobLogger = $jobLogger ?? new JobRunLogger($db);
        $this->projectRoot = dirname(__DIR__, 2);
    }

    public function runAutomatedBackup(array $options = []): array
    {
        $jobName = (string) ($options['job_name'] ?? 'weekly_database_backup');
        $trigger = (string) ($options['trigger'] ?? 'scheduler');
        $pushEnabled = array_key_exists('push', $options)
            ? (bool) $options['push']
            : $this->settingsStore->getBool('backup_push_enabled', true);
        $pushAttempts = max(1, (int) ($options['push_attempts'] ?? 3));
        $runId = $this->jobLogger->start($jobName, ['trigger' => $trigger, 'push_enabled' => $pushEnabled]);

        try {
            $archive = $this->createArchive($trigger);
            $publish = [
                'attempted' => false,
                'success' => true,
                'attempts' => 1,
                'message' => 'GitHub push disabled.',
            ];

            if ($pushEnabled) {
                $publish = $this->publishArchiveWithRetry($archive, $pushAttempts);
            }

            $status = ($pushEnabled && !$publish['success']) ? 'warning' : 'success';
            $message = $status === 'warning'
                ? 'Backup created locally but GitHub push failed.'
                : 'Backup completed successfully.';

            $details = ['trigger' => $trigger, 'archive' => $archive, 'publish' => $publish];
            $this->jobLogger->finish($runId, $status, $message, $details, (int) ($publish['attempts'] ?? 1));
            $this->writeLog($status, $message, $details);

            return [
                'status' => $status,
                'message' => $message,
                'archive' => $archive,
                'publish' => $publish,
                'run_id' => $runId,
            ];
        } catch (\Throwable $e) {
            $message = 'Backup failed: ' . $e->getMessage();
            $details = ['trigger' => $trigger, 'exception' => $e->getMessage()];
            $this->jobLogger->finish($runId, 'failed', $message, $details, 1);
            $this->writeLog('failed', $message, $details);

            return [
                'status' => 'failed',
                'message' => $message,
                'archive' => null,
                'publish' => ['attempted' => false, 'success' => false, 'attempts' => 0, 'message' => $e->getMessage()],
                'run_id' => $runId,
            ];
        }
    }

    public function createArchive(string $trigger = 'manual'): array
    {
        $paths = $this->getStoragePaths();
        foreach ($paths as $path) {
            $this->ensureDirectory($path);
        }

        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('ZipArchive extension is required for compressed backups.');
        }

        $timestamp = date('Y_m_d_His');
        $baseName = 'backup_' . $timestamp;
        $sqlName = $baseName . '.sql';
        $archiveName = $baseName . '.zip';
        $sqlPath = $paths['temp'] . DIRECTORY_SEPARATOR . $sqlName;
        $manifestPath = $paths['temp'] . DIRECTORY_SEPARATOR . $baseName . '_manifest.json';
        $archivePath = $paths['archives'] . DIRECTORY_SEPARATOR . $archiveName;

        $this->writeDumpFile($sqlPath);
        file_put_contents($manifestPath, json_encode([
            'trigger' => $trigger,
            'database' => defined('DB_NAME') ? DB_NAME : null,
            'generated_at' => date(DATE_ATOM),
            'app_env' => defined('APP_ENV') ? APP_ENV : null,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $zip = new ZipArchive();
        if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($sqlPath);
            @unlink($manifestPath);
            throw new RuntimeException('Unable to create compressed backup archive.');
        }

        $zip->addFile($sqlPath, $sqlName);
        $zip->addFile($manifestPath, basename($manifestPath));
        $zip->close();

        @unlink($sqlPath);
        @unlink($manifestPath);
        $this->pruneArchives($paths['archives'], $this->settingsStore->getInt('backup_retention_count', 12));

        return [
            'filename' => $archiveName,
            'archive_path' => $archivePath,
            'archive_directory' => $paths['archives'],
            'size' => is_file($archivePath) ? (int) filesize($archivePath) : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'trigger' => $trigger,
        ];
    }

    public function listArchives(): array
    {
        $archivesDir = $this->getStoragePaths()['archives'];
        if (!is_dir($archivesDir)) {
            return [];
        }

        $items = [];
        foreach (glob($archivesDir . DIRECTORY_SEPARATOR . 'backup_*.zip') ?: [] as $file) {
            $items[] = [
                'filename' => basename($file),
                'path' => $file,
                'size' => (int) filesize($file),
                'date' => date('Y-m-d H:i:s', (int) filemtime($file)),
            ];
        }

        usort($items, static fn(array $left, array $right) => strcmp($right['date'], $left['date']));

        return $items;
    }

    public function resolveArchivePath(string $filename): ?string
    {
        $path = $this->getStoragePaths()['archives'] . DIRECTORY_SEPARATOR . basename($filename);

        return is_file($path) ? $path : null;
    }

    private function publishArchiveWithRetry(array $archive, int $maxAttempts): array
    {
        $lastResult = ['attempted' => true, 'success' => false, 'attempts' => 0, 'message' => 'GitHub push not attempted.', 'repository' => null];

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $result = $this->publishArchive($archive);
            $result['attempts'] = $attempt;
            $lastResult = $result;

            if ($result['success']) {
                return $result;
            }

            if ($attempt < $maxAttempts) {
                sleep(2);
            }
        }

        return $lastResult;
    }

    private function publishArchive(array $archive): array
    {
        $settings = $this->settingsStore->all();
        $remoteUrl = $this->buildRemoteUrl($settings);
        if ($remoteUrl === '') {
            return ['attempted' => true, 'success' => false, 'message' => 'GitHub repository is not configured.', 'repository' => null];
        }

        $worktree = $this->resolvePath((string) ($settings['backup_git_worktree'] ?? 'storage/backup-repository'));
        $branch = trim((string) ($settings['backup_github_branch'] ?? 'main')) ?: 'main';
        $gitUserName = trim((string) ($settings['backup_git_user_name'] ?? 'NotesMaster Backup Bot')) ?: 'NotesMaster Backup Bot';
        $gitUserEmail = trim((string) ($settings['backup_git_user_email'] ?? 'backup-bot@notesmaster.local')) ?: 'backup-bot@notesmaster.local';

        $prepare = $this->prepareWorktree($worktree, $remoteUrl);
        if (!$prepare['success']) {
            return ['attempted' => true, 'success' => false, 'message' => $prepare['message'], 'repository' => $remoteUrl];
        }

        $this->runCommand(['git', 'config', 'user.name', $gitUserName], $worktree);
        $this->runCommand(['git', 'config', 'user.email', $gitUserEmail], $worktree);

        $checkout = $this->runCommand(['git', 'checkout', $branch], $worktree);
        if (!$checkout['success']) {
            $createBranch = $this->runCommand(['git', 'checkout', '-b', $branch], $worktree);
            if (!$createBranch['success']) {
                return ['attempted' => true, 'success' => false, 'message' => $createBranch['output'], 'repository' => $remoteUrl];
            }
        }

        $backupRepoDir = $worktree . DIRECTORY_SEPARATOR . 'backups';
        $this->ensureDirectory($backupRepoDir);
        $destination = $backupRepoDir . DIRECTORY_SEPARATOR . basename((string) $archive['filename']);

        if (!copy((string) $archive['archive_path'], $destination)) {
            return ['attempted' => true, 'success' => false, 'message' => 'Unable to copy the backup into the Git worktree.', 'repository' => $remoteUrl];
        }

        $this->writeRepositoryReadme($worktree, $settings);
        $this->pruneArchives($backupRepoDir, $this->settingsStore->getInt('backup_retention_count', 12));

        $add = $this->runCommand(['git', 'add', '.'], $worktree);
        if (!$add['success']) {
            return ['attempted' => true, 'success' => false, 'message' => $add['output'], 'repository' => $remoteUrl];
        }

        $status = $this->runCommand(['git', 'status', '--porcelain'], $worktree);
        if (!$status['success']) {
            return ['attempted' => true, 'success' => false, 'message' => $status['output'], 'repository' => $remoteUrl];
        }

        if (trim($status['output']) === '') {
            return ['attempted' => true, 'success' => true, 'message' => 'Backup repository already up to date.', 'repository' => $remoteUrl];
        }

        $commit = $this->runCommand(['git', 'commit', '-m', 'Automated backup ' . basename((string) $archive['filename'])], $worktree);
        if (!$commit['success']) {
            return ['attempted' => true, 'success' => false, 'message' => $commit['output'], 'repository' => $remoteUrl];
        }

        $push = $this->runCommand(['git', 'push', '-u', 'origin', $branch], $worktree);

        return ['attempted' => true, 'success' => $push['success'], 'message' => $push['output'], 'repository' => $remoteUrl];
    }

    private function prepareWorktree(string $worktree, string $remoteUrl): array
    {
        $gitDir = $worktree . DIRECTORY_SEPARATOR . '.git';
        $parentDir = dirname($worktree);
        $this->ensureDirectory($parentDir);

        if (!is_dir($gitDir)) {
            $clone = $this->runCommand(['git', 'clone', $remoteUrl, $worktree], $parentDir);
            if (!$clone['success']) {
                $this->ensureDirectory($worktree);
                $init = $this->runCommand(['git', 'init'], $worktree);
                if (!$init['success']) {
                    return ['success' => false, 'message' => $init['output']];
                }

                $remoteAdd = $this->runCommand(['git', 'remote', 'add', 'origin', $remoteUrl], $worktree);
                if (!$remoteAdd['success']) {
                    return ['success' => false, 'message' => $remoteAdd['output']];
                }
            }
        } else {
            $remoteGet = $this->runCommand(['git', 'remote', 'get-url', 'origin'], $worktree);
            if (!$remoteGet['success']) {
                $remoteAdd = $this->runCommand(['git', 'remote', 'add', 'origin', $remoteUrl], $worktree);
                if (!$remoteAdd['success']) {
                    return ['success' => false, 'message' => $remoteAdd['output']];
                }
            } elseif (trim($remoteGet['output']) !== $remoteUrl) {
                $remoteSet = $this->runCommand(['git', 'remote', 'set-url', 'origin', $remoteUrl], $worktree);
                if (!$remoteSet['success']) {
                    return ['success' => false, 'message' => $remoteSet['output']];
                }
            }
        }

        return ['success' => true, 'message' => 'Git worktree ready.'];
    }

    private function writeDumpFile(string $sqlPath): void
    {
        $handle = fopen($sqlPath, 'wb');
        if ($handle === false) {
            throw new RuntimeException('Unable to create temporary SQL dump file.');
        }

        $databaseName = defined('DB_NAME') ? DB_NAME : '';
        fwrite($handle, "-- NotesMaster automated backup\n");
        fwrite($handle, "-- Generated at: " . date('c') . "\n\n");
        fwrite($handle, "SET NAMES utf8mb4;\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n");
        if ($databaseName !== '') {
            fwrite($handle, "USE `" . str_replace('`', '``', $databaseName) . "`;\n\n");
        }

        foreach ($this->fetchTableNames() as $table) {
            $tableName = str_replace('`', '``', $table);
            $createStmt = $this->db->query("SHOW CREATE TABLE `{$tableName}`")->fetch(PDO::FETCH_NUM);
            fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");
            fwrite($handle, $createStmt[1] . ";\n\n");

            $stmt = $this->db->query("SELECT * FROM `{$tableName}`", PDO::FETCH_ASSOC);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $columns = array_map(static fn(string $column): string => '`' . str_replace('`', '``', $column) . '`', array_keys($row));
                $values = [];
                foreach ($row as $value) {
                    $values[] = $value === null ? 'NULL' : $this->db->quote((string) $value);
                }

                fwrite($handle, "INSERT INTO `{$tableName}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n");
            }

            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS = 1;\n");
        fclose($handle);
    }

    private function fetchTableNames(): array
    {
        $stmt = $this->db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        $tables = [];
        foreach ($stmt->fetchAll(PDO::FETCH_NUM) as $row) {
            $tables[] = (string) $row[0];
        }

        return $tables;
    }

    private function getStoragePaths(): array
    {
        $base = $this->resolvePath((string) ($this->settingsStore->get('backup_storage_path', 'storage/backups') ?? 'storage/backups'));

        return [
            'base' => $base,
            'archives' => $base . DIRECTORY_SEPARATOR . 'archives',
            'temp' => $base . DIRECTORY_SEPARATOR . 'tmp',
            'logs' => $base . DIRECTORY_SEPARATOR . 'logs',
        ];
    }

    private function buildRemoteUrl(array $settings): string
    {
        $owner = trim((string) ($settings['backup_github_owner'] ?? ''));
        $repository = trim((string) ($settings['backup_github_repository'] ?? ''));
        $authMode = strtolower(trim((string) ($settings['backup_github_auth'] ?? 'ssh')));

        if ($owner === '' || $repository === '') {
            return '';
        }

        return $authMode === 'https'
            ? 'https://github.com/' . $owner . '/' . $repository . '.git'
            : 'git@github.com:' . $owner . '/' . $repository . '.git';
    }

    private function writeRepositoryReadme(string $worktree, array $settings): void
    {
        $readmePath = $worktree . DIRECTORY_SEPARATOR . 'README.md';
        if (is_file($readmePath)) {
            return;
        }

        $repositoryName = trim((string) ($settings['backup_github_repository'] ?? 'notesmaster-backups')) ?: 'notesmaster-backups';
        file_put_contents($readmePath, "# {$repositoryName}\n\nAutomated weekly backups generated by NotesMaster.\n");
    }

    private function pruneArchives(string $directory, int $keepCount): void
    {
        if ($keepCount <= 0 || !is_dir($directory)) {
            return;
        }

        $files = glob($directory . DIRECTORY_SEPARATOR . 'backup_*.zip') ?: [];
        usort($files, static fn(string $left, string $right): int => filemtime($right) <=> filemtime($left));

        foreach (array_slice($files, $keepCount) as $staleFile) {
            if (is_file($staleFile)) {
                @unlink($staleFile);
            }
        }
    }

    private function writeLog(string $status, string $message, array $context = []): void
    {
        $logDir = $this->getStoragePaths()['logs'];
        $this->ensureDirectory($logDir);
        $line = json_encode([
            'timestamp' => date(DATE_ATOM),
            'status' => $status,
            'message' => $message,
            'context' => $context,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        file_put_contents($logDir . DIRECTORY_SEPARATOR . 'backup-' . date('Y-m') . '.log', $line . PHP_EOL, FILE_APPEND);
    }

    private function ensureDirectory(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
            throw new RuntimeException('Unable to create directory: ' . $path);
        }
    }

    private function resolvePath(string $path): string
    {
        $trimmed = trim($path);
        if ($trimmed === '') {
            return $this->projectRoot . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';
        }

        if ($this->isAbsolutePath($trimmed)) {
            return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $trimmed);
        }

        return $this->projectRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $trimmed);
    }

    private function isAbsolutePath(string $path): bool
    {
        return preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1
            || str_starts_with($path, '\\\\')
            || str_starts_with($path, '/');
    }

    private function runCommand(array $parts, ?string $workingDirectory = null): array
    {
        $command = implode(' ', array_map('escapeshellarg', $parts));
        $process = @proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $workingDirectory);
        if (!is_resource($process)) {
            return ['success' => false, 'output' => 'Unable to start command: ' . $command];
        }

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);
        $output = trim($stdout . "\n" . $stderr);

        return ['success' => $exitCode === 0, 'output' => $output !== '' ? $output : 'Command finished with exit code ' . $exitCode . '.'];
    }
}
