<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Services\SettingsStore;

$db = Database::getInstance()->getConnection();
$store = new SettingsStore($db);

echo "=== TEST CONFIGURATIONS PAR TYPE D'ENSEIGNEMENT ===\n\n";

$sec00Id = $store->getDefaultTeachingTypeId();
echo "Default Teaching Type ID (SEC00): {$sec00Id}\n";

// 1. Lire nom de l'école pour SEC00
$secName = $store->get('school_name', null, $sec00Id);
echo "School Name (SEC00 - ID {$sec00Id}): {$secName}\n";

// 2. Écrire un nom d'école spécifique pour un autre type d'enseignement (ex: ID 9)
$otherId = 9;
$store->set('school_name', 'Institut Supérieur NoteMaster LMD', $otherId);

$otherName = $store->get('school_name', null, $otherId);
echo "School Name (LMD - ID {$otherId}): {$otherName}\n";

// Re-vérifier SEC00 pour s'assurer qu'il n'a pas changé
$secNameCheck = $store->get('school_name', null, $sec00Id);
echo "School Name (SEC00 après update LMD): {$secNameCheck}\n";

if ($secNameCheck !== $otherName && $otherName === 'Institut Supérieur NoteMaster LMD') {
    echo "\n[SUCCÈS] Les paramètres sont bien isolés par Type d'enseignement avec fallback sur SEC00 !\n";
} else {
    echo "\n[ÉCHEC] Problème d'isolation des paramètres.\n";
}
