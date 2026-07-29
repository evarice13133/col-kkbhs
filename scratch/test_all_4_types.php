<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Services\SettingsStore;

$db = Database::getInstance()->getConnection();
$store = new SettingsStore($db);

echo "=== VÉRIFICATION DES PARAMÈTRES DES 4 TYPES D'ENSEIGNEMENT ===\n\n";

$types = $db->query("SELECT id, nom, code FROM teaching_types ORDER BY position ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

foreach ($types as $t) {
    $id = (int)$t['id'];
    $s = $store->all($id);
    echo "Type {$t['nom']} (CODE: {$t['code']}, ID: {$id}) :\n";
    echo "  - Nom école: " . ($s['school_name'] ?? 'N/A') . "\n";
    echo "  - Code école: " . ($s['school_code'] ?? 'N/A') . "\n";
    echo "  - Titre principal: " . ($s['principal_title'] ?? 'N/A') . "\n";
    echo "  - Ville: " . ($s['school_city'] ?? 'N/A') . "\n\n";
}
