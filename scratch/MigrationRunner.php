<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

echo "=== MIGRATION RUNNER ===\n\n";

try {
    // 1. Create migrations table if needed
    echo "Checking migrations table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            batch INT NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Migrations table ready.\n\n";

    // 2. Get executed migrations
    $executed = $db->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);

    // 3. Find pending migrations from scripts directory (legacy format)
    $scriptsDir = __DIR__ . '/../scripts/';
    $pending = [];
    if (is_dir($scriptsDir)) {
        foreach (scandir($scriptsDir) as $file) {
            if ($file !== '.' && $file !== '..' && strpos($file, 'migration_') === 0 && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $migrationName = 'scripts/' . $file;
                if (!in_array($migrationName, $executed)) {
                    $pending[] = $migrationName;
                }
            }
        }
    }

    // 4. Also check migrations directory
    $migrationDir = __DIR__ . '/../migrations/';
    if (is_dir($migrationDir)) {
        foreach (scandir($migrationDir) as $file) {
            if ($file !== '.' && $file !== '..' && $file !== 'index.html' && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $migrationName = 'migrations/' . $file;
                if (!in_array($migrationName, $executed)) {
                    $pending[] = $migrationName;
                }
            }
        }
    }

    // 5. Check scratch directory for run_*.php migration scripts
    $scratchDir = __DIR__ . '/';
    if (is_dir($scratchDir)) {
        foreach (scandir($scratchDir) as $file) {
            if ($file !== '.' && $file !== '..' && strpos($file, 'run_') === 0 && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $migrationName = 'scratch/' . $file;
                if (!in_array($migrationName, $executed)) {
                    $pending[] = $migrationName;
                }
            }
        }
    }

    if (empty($pending)) {
        echo "No pending migrations found.\n";
        exit(0);
    }

    echo "Pending migrations:\n";
    foreach ($pending as $m) {
        echo "  - $m\n";
    }
    echo "\n";

    // 6. Run migrations
    $batch = time();
    foreach ($pending as $migration) {
        $parts = explode('/', $migration, 2);
        $dir = $parts[0];
        $file = $parts[1];
        
        $path = __DIR__ . "/../$dir/$file";
        
        if (!file_exists($path)) {
            echo "WARNING: Migration file not found: $migration\n";
            continue;
        }

        echo "Running: $migration\n";
        
        // Include and execute migration (they already have their own DB setup)
        $output = shell_exec("php " . escapeshellarg($path) . " 2>&1");
        if ($output) {
            echo $output;
        }

        // Record migration
        $stmt = $db->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute([$migration, $batch]);
        echo "Recorded: $migration\n\n";
    }

    echo "=== MIGRATION COMPLETE ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}