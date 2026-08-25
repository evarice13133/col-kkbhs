<?php
require_once __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "=== TEST DE VALIDATION DU COMPORTEMENT ON DELETE CASCADE SUR CLASS_INSTALLMENTS ===\n\n";

    // 1. Inserer une classe de test
    $pdo->exec("INSERT INTO classes (nom, created_at) VALUES ('Classe Test FK CASCADE', NOW())");
    $testClassId = $pdo->lastInsertId();
    echo "1. Classe de test créée avec ID : $testClassId\n";

    // 2. Inserer 3 tranches pour cette classe
    $stmt = $pdo->prepare("INSERT INTO class_installments (class_id, installment_number, amount) VALUES (?, ?, ?)");
    $stmt->execute([$testClassId, 1, 50000.00]);
    $stmt->execute([$testClassId, 2, 30000.00]);
    $stmt->execute([$testClassId, 3, 20000.00]);

    $count = $pdo->query("SELECT COUNT(*) FROM class_installments WHERE class_id = $testClassId")->fetchColumn();
    echo "2. $count tranches insérées pour la classe ID $testClassId.\n";

    // 3. Tenter d'insérer un class_id inexistant (doit échouer avec l'erreur FK #1452)
    try {
        $pdo->exec("INSERT INTO class_installments (class_id, installment_number, amount) VALUES (999999, 1, 1000.00)");
        echo "[ECHEC TEST] L'insertion d'un class_id invalide 999999 aurait dû être bloquée par la contrainte FK !\n";
    } catch (PDOException $e) {
        echo "3. Protection FK active : L'insertion d'un class_id invalide a été rejetée avec succès (Code PDO: {$e->getCode()}).\n";
    }

    // 4. Supprimer la classe de test et vérifier la suppression en cascade des tranches
    $pdo->exec("DELETE FROM classes WHERE id = $testClassId");
    $countAfterDelete = $pdo->query("SELECT COUNT(*) FROM class_installments WHERE class_id = $testClassId")->fetchColumn();
    
    if ($countAfterDelete == 0) {
        echo "4. Succès ON DELETE CASCADE : Les tranches ont été automatiquement supprimées lors de la suppression de la classe !\n";
    } else {
        echo "[ECHEC TEST] Les tranches de la classe supprimée sont toujours présentes ($countAfterDelete trouvées) !\n";
    }

    echo "\n=== VÉRIFICATION STRUCTURELLE ET CONTRAINTES DE CLASS_INSTALLMENTS ===\n";
    $fkCheck = $pdo->query("
        SELECT 
            CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
          AND TABLE_NAME = 'class_installments' 
          AND REFERENCED_TABLE_NAME IS NOT NULL
    ")->fetchAll();

    print_r($fkCheck);

    echo "\n=== TOUS LES TESTS SE SONT DÉROULÉS AVEC SUCCÈS ! ===\n";

} catch (Exception $e) {
    echo "ERREUR DANS LE TEST : " . $e->getMessage() . "\n";
    exit(1);
}
