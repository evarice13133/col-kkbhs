<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

echo "Vérification des colonnes snapshot dans la table grades:\n";
$stmt = $db->query("SHOW COLUMNS FROM grades LIKE '%snapshot%'");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($columns)) {
    echo "Aucune colonne snapshot trouvée.\n";
} else {
    foreach ($columns as $col) {
        echo $col['Field'] . ' - ' . $col['Type'] . ' - Null: ' . $col['Null'] . ' - Default: ' . ($col['Default'] ?? 'NULL') . PHP_EOL;
    }
}
