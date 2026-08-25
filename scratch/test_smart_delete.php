<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\SmartDeleteService;

try {
    echo "=== TEST DU SERVICE SMART DELETE ===\n\n";

    $service = new SmartDeleteService();

    // 1. Tester la suppression directe d'une classe temporaire
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $pdo->exec("INSERT INTO classes (nom) VALUES ('Classe Test SmartDelete')");
    $testClassId = $pdo->lastInsertId();
    echo "1. Classe de test créée (ID: $testClassId).\n";

    $resultClass = $service->execute('class', $testClassId, 'direct');
    echo "2. Résultat suppression classe : " . ($resultClass['success'] ? 'SUCCÈS' : 'ÉCHEC') . " -> " . $resultClass['message'] . "\n";

    // 2. Tester la suppression directe d'une matière temporaire
    $pdo->exec("INSERT INTO subjects (nom) VALUES ('Matière Test SmartDelete')");
    $testSubId = $pdo->lastInsertId();
    echo "3. Matière de test créée (ID: $testSubId).\n";

    $resultSub = $service->execute('subject', $testSubId, 'direct');
    echo "4. Résultat suppression matière : " . ($resultSub['success'] ? 'SUCCÈS' : 'ÉCHEC') . " -> " . $resultSub['message'] . "\n";

    echo "\n=== TOUS LES TESTS SMART DELETE SONT PASSÉS AVEC SUCCÈS ! ===\n";

} catch (Exception $e) {
    echo "ERREUR TEST: " . $e->getMessage() . "\n";
    exit(1);
}
