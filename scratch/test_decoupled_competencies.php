<?php

/**
 * Test script for verifying Competencies adaptation to Decoupled Subjects.
 */

require_once __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "Connexion DB réussie.\n";
} catch (Exception $e) {
    die("Échec connexion : " . $e->getMessage() . "\n");
}

echo "=== TEST D'ADAPTATION DES COMPETENCES A LA DESOLIDARISATION ===\n\n";

$pdo->beginTransaction();

try {
    // Récupérer 2 classes de test
    $classes = $pdo->query("SELECT id, nom FROM classes LIMIT 2")->fetchAll();
    if (count($classes) < 2) {
        throw new Exception("Besoin d'au moins 2 classes pour tester.");
    }

    $classA = $classes[0];
    $classB = $classes[1];
    echo "Classes de test : {$classA['nom']} (ID {$classA['id']}) et {$classB['nom']} (ID {$classB['id']})\n";

    $academicYearId = (int) $pdo->query("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1")->fetchColumn();
    if (!$academicYearId) {
        $academicYearId = (int) $pdo->query("SELECT MIN(id) FROM academic_years")->fetchColumn();
    }
    $createdById = (int) $pdo->query("SELECT MIN(id) FROM users")->fetchColumn();

    // 1. Création pour une classe seule
    echo "\n1. Test création matière pour une classe unique...\n";
    $subjectNameSingle = "Test Single " . time();
    $stmtInsSub = $pdo->prepare("INSERT INTO subjects (nom, coefficient, groupe, status) VALUES (?, 2, 'Groupe 1', 1)");
    $stmtInsSub->execute([$subjectNameSingle]);
    $subSingleId = (int) $pdo->lastInsertId();

    $stmtSC = $pdo->prepare("INSERT INTO subject_classes (subject_id, class_id, academic_year_id) VALUES (?, ?, ?)");
    $stmtSC->execute([$subSingleId, $classA['id'], $academicYearId]);

    $stmtComp = $pdo->prepare("INSERT INTO competencies (subject_id, libelle, position, created_by) VALUES (?, ?, ?, ?)");
    $stmtComp->execute([$subSingleId, "Compétence Single 1", 1, $createdById]);
    $compSingleId = (int) $pdo->lastInsertId();

    echo "  ✓ Matière ID $subSingleId créée pour classe {$classA['nom']} avec compétence ID $compSingleId.\n";

    // 2 & 3. Création désolidarisée pour plusieurs classes avec duplication des compétences
    echo "\n2 & 3. Test création désolidarisée pour plusieurs classes ({$classA['nom']} & {$classB['nom']})...\n";
    $subjectNameMulti = "Accounting " . time();
    $competenciesList = ["Comprendre la comptabilité", "Établir un bilan"];

    $createdSubjectIds = [];
    $createdCompIds = [];

    foreach ([$classA, $classB] as $cls) {
        $stmtInsSub->execute([$subjectNameMulti]);
        $sId = (int) $pdo->lastInsertId();
        $createdSubjectIds[$cls['id']] = $sId;

        $stmtSC->execute([$sId, $cls['id'], $academicYearId]);

        $createdCompIds[$cls['id']] = [];
        foreach ($competenciesList as $idx => $compLib) {
            $stmtComp->execute([$sId, $compLib, $idx + 1, $createdById]);
            $createdCompIds[$cls['id']][] = (int) $pdo->lastInsertId();
        }
    }

    echo "  ✓ Instance pour {$classA['nom']} : Subject ID {$createdSubjectIds[$classA['id']]}, Competencies: " . implode(', ', $createdCompIds[$classA['id']]) . "\n";
    echo "  ✓ Instance pour {$classB['nom']} : Subject ID {$createdSubjectIds[$classB['id']]}, Competencies: " . implode(', ', $createdCompIds[$classB['id']]) . "\n";

    if ($createdSubjectIds[$classA['id']] === $createdSubjectIds[$classB['id']]) {
        throw new Exception("Échec : Les ID de matière doivent être distincts.");
    }

    // 4. Modification indépendante des compétences
    echo "\n4. Test modification indépendante des compétences...\n";
    $compAFirst = $createdCompIds[$classA['id']][0];
    $compBFirst = $createdCompIds[$classB['id']][0];

    $updateComp = $pdo->prepare("UPDATE competencies SET libelle = ? WHERE id = ?");
    $updateComp->execute(["Comprendre la comptabilité SPÉCIALE FORM 1", $compAFirst]);

    // Vérifier
    $libA = $pdo->query("SELECT libelle FROM competencies WHERE id = $compAFirst")->fetchColumn();
    $libB = $pdo->query("SELECT libelle FROM competencies WHERE id = $compBFirst")->fetchColumn();

    echo "  - Libellé Classe A (Modifié) : '$libA'\n";
    echo "  - Libellé Classe B (Intact)   : '$libB'\n";

    if ($libA === $libB) {
        throw new Exception("Échec : La modification sur la classe A ne doit pas affecter la classe B.");
    }
    echo "  ✓ Les compétences sont totalement indépendantes lors des modifications.\n";

    // 5. Suppression indépendante
    echo "\n5. Test suppression autonome d'une occurrence de matière...\n";
    $subAId = $createdSubjectIds[$classA['id']];
    $subBId = $createdSubjectIds[$classB['id']];

    $deleteSub = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
    $deleteSub->execute([$subAId]);

    $countCompA = (int) $pdo->query("SELECT COUNT(*) FROM competencies WHERE subject_id = $subAId")->fetchColumn();
    $countCompB = (int) $pdo->query("SELECT COUNT(*) FROM competencies WHERE subject_id = $subBId")->fetchColumn();

    echo "  - Nombre de compétences pour Classe A supprimée : $countCompA (Attendu: 0)\n";
    echo "  - Nombre de compétences pour Classe B conservée : $countCompB (Attendu: 2)\n";

    if ($countCompA !== 0 || $countCompB !== 2) {
        throw new Exception("Échec : La suppression de la matière Classe A a affecté la Classe B.");
    }
    echo "  ✓ La suppression est totalement isolée entre les occurrences.\n";

    // 6. Test d'utilisation dans evaluation_competencies
    echo "\n6. Test liaison des compétences aux évaluations (evaluation_competencies)...\n";
    $compB1 = $createdCompIds[$classB['id']][0];
    $compB2 = $createdCompIds[$classB['id']][1];

    $stmtEvalComp = $pdo->prepare("INSERT INTO evaluation_competencies (class_id, subject_id, academic_year_id, periode, competency_id, position) VALUES (?, ?, ?, 'Trimestre 1', ?, ?)");
    $stmtEvalComp->execute([$classB['id'], $subBId, $academicYearId, $compB1, 1]);
    $stmtEvalComp->execute([$classB['id'], $subBId, $academicYearId, $compB2, 2]);

    $evalCompCount = (int) $pdo->query("SELECT COUNT(*) FROM evaluation_competencies WHERE subject_id = $subBId AND class_id = {$classB['id']}")->fetchColumn();
    echo "  - Nombre d'évaluations liées pour la classe B : $evalCompCount (Attendu: 2)\n";

    if ($evalCompCount !== 2) {
        throw new Exception("Échec de l'enregistrement dans evaluation_competencies.");
    }
    echo "  ✓ Intégration aux évaluations validée avec succès.\n";

    // Rollback test changes to keep clean DB
    $pdo->rollBack();
    echo "\n=== TOUS LES TESTS SE SONT DEROULES AVEC SUCCES (Rollback effectué) ===\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n❌ ERREUR DURANT LE TEST : " . $e->getMessage() . "\n";
    exit(1);
}
