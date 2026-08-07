<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;
use App\Services\ImpactAnalysisService;
use App\Services\SmartDeleteService;

echo "=== TEST RADIOGRAPHIE D'IMPACT ===\n\n";

$db = Database::getInstance()->getConnection();
$analyzer = new ImpactAnalysisService($db);
$deleter = new SmartDeleteService($db);

// 1. Enseignants
$teacherId = (int)$db->query("SELECT id FROM users WHERE role = 'enseignant' ORDER BY id LIMIT 1")->fetchColumn();
if ($teacherId > 0) {
    echo "1. Analyse Enseignant #$teacherId :\n";
    $res = $analyzer->analyze('teacher', $teacherId);
    echo "   Nom : " . $res['entity']['name'] . "\n";
    echo "   Niveau de risque : " . strtoupper($res['risk_level']) . "\n";
    echo "   Action recommandée : " . $res['recommended_action'] . "\n";
    echo "   Direct delete possible ? " . ($res['can_direct_delete'] ? 'OUI' : 'NON') . "\n";
    echo "   Stats d'impact : " . count($res['stats']) . " compteurs de dépendances.\n\n";
} else {
    echo "1. Aucun enseignant trouvé en BDD pour test.\n\n";
}

// 2. Classes
$classId = (int)$db->query("SELECT id FROM classes ORDER BY id LIMIT 1")->fetchColumn();
if ($classId > 0) {
    echo "2. Analyse Classe #$classId :\n";
    $resClass = $analyzer->analyze('class', $classId);
    echo "   Nom : " . $resClass['entity']['name'] . "\n";
    echo "   Niveau de risque : " . strtoupper($resClass['risk_level']) . "\n";
    echo "   Action recommandée : " . $resClass['recommended_action'] . "\n";
    echo "   Cibles de transfert disponibles : " . count($resClass['transfer_options']['items'] ?? []) . " classes.\n\n";
} else {
    echo "2. Aucune classe trouvée en BDD pour test.\n\n";
}

// 3. Matières
$subId = (int)$db->query("SELECT id FROM subjects ORDER BY id LIMIT 1")->fetchColumn();
if ($subId > 0) {
    echo "3. Analyse Matière #$subId :\n";
    $resSub = $analyzer->analyze('subject', $subId);
    echo "   Nom : " . $resSub['entity']['name'] . "\n";
    echo "   Niveau de risque : " . strtoupper($resSub['risk_level']) . "\n";
    echo "   Action recommandée : " . $resSub['recommended_action'] . "\n\n";
}

echo "=== TEST SERVEUR RADIOGRAPHIE TERMINÉ AVEC SUCCÈS ===\n";
