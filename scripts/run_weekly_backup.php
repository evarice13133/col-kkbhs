<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;
use App\Services\BackupService;
use App\Services\SettingsStore;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be executed from the command line." . PHP_EOL);
    exit(1);
}

$db = Database::getInstance()->getConnection();
$settingsStore = new SettingsStore($db);

if (!$settingsStore->getBool('backup_enabled', true)) {
    fwrite(STDOUT, "Weekly backup is disabled in settings." . PHP_EOL);
    exit(0);
}

$backupService = new BackupService($db, $settingsStore);
$result = $backupService->runAutomatedBackup([
    'trigger' => 'scheduled_cli',
    'job_name' => 'weekly_database_backup',
    'push' => $settingsStore->getBool('backup_push_enabled', true),
    'push_attempts' => 3,
]);

fwrite(STDOUT, ($result['message'] ?? 'Backup finished.') . PHP_EOL);
exit(($result['status'] ?? 'failed') === 'success' ? 0 : 1);
