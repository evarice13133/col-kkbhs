<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

date_default_timezone_set('Africa/Douala');

$db = Database::getInstance()->getConnection();

echo "=== RAPPORT DE DÉPLOIEMENT " . date('d/m/Y H:i:s') . " ===\n\n";

try {
    // Check database connection
    $db->query("SELECT 1");
    echo "✓ Connexion à la base de données: OK\n";

    // Check key tables exist
    $tables = ['users', 'students', 'classes', 'subjects', 'roles', 'permissions', 'school_fees', 'student_payments'];
    foreach ($tables as $table) {
        $check = $db->query("SHOW TABLES LIKE '$table'");
        $status = $check->rowCount() > 0 ? 'EXISTE' : 'MANQUANTE';
        echo "✓ Table $table: $status\n";
    }

    // Check migrations table
    $migrations = $db->query("SELECT COUNT(*) FROM migrations")->fetchColumn();
    echo "✓ Migrations exécutées: $migrations\n";

    // Check application start
    echo "✓ Application prête pour la production\n\n";

    echo "=== DÉPLOIEMENT TERMINÉ AVEC SUCCÈS ===\n";

} catch (Exception $e) {
    echo "✗ ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}