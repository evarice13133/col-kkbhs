<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Services\SettingsStore;

$db = Database::getInstance()->getConnection();

echo "=== VÉRIFICATION ET POPULATION DES 4 TYPES D'ENSEIGNEMENT ===\n\n";

$teachingTypes = $db->query("SELECT * FROM teaching_types ORDER BY position ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

print_r($teachingTypes);
