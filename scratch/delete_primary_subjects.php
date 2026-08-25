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

    echo "=== SUPPRESSION DES MATIÈRES DU TYPE D'ENSEIGNEMENT 'PRIMAIRE' ===\n\n";

    // 1. Trouver les types d'enseignement correspondants (code 'PRI' ou nom contenant 'Primaire')
    $ttList = $pdo->query("
        SELECT id, nom, code FROM teaching_types 
        WHERE UPPER(code) = 'PRI' OR LOWER(nom) LIKE '%primaire%'
    ")->fetchAll();

    echo "Types d'enseignement trouvés :\n";
    print_r($ttList);

    $ttIds = array_column($ttList, 'id');

    // Trouver également les départements 'PRIMAIRE'
    $deptList = $pdo->query("
        SELECT id, nom, code FROM departments 
        WHERE UPPER(code) = 'PRIM' OR LOWER(nom) LIKE '%primaire%'
    ")->fetchAll();
    echo "Départements trouvés :\n";
    print_r($deptList);

    $deptIds = array_column($deptList, 'id');

    // 2. Compter les matières concernées
    $whereConditions = [];
    $params = [];

    if (!empty($ttIds)) {
        $inTT = implode(',', array_fill(0, count($ttIds), '?'));
        $whereConditions[] = "teaching_type_id IN ($inTT)";
        $params = array_merge($params, $ttIds);
    }

    if (!empty($deptIds)) {
        $inDept = implode(',', array_fill(0, count($deptIds), '?'));
        $whereConditions[] = "department_id IN ($inDept)";
        $params = array_merge($params, $deptIds);
    }

    if (empty($whereConditions)) {
        echo "Aucun type d'enseignement ou département 'Primaire' trouvé.\n";
        exit(0);
    }

    $sqlCheck = "SELECT id, nom, teaching_type_id, department_id FROM subjects WHERE " . implode(' OR ', $whereConditions);
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute($params);
    $subjectsToDelete = $stmtCheck->fetchAll();

    echo "\nTotal matières du Primaire trouvées à supprimer : " . count($subjectsToDelete) . "\n";
    foreach ($subjectsToDelete as $sub) {
        echo "  - ID {$sub['id']} : {$sub['nom']} (Type: {$sub['teaching_type_id']}, Dept: {$sub['department_id']})\n";
    }

    if (!empty($subjectsToDelete)) {
        $pdo->beginTransaction();

        $subIds = array_column($subjectsToDelete, 'id');
        $inSubs = implode(',', array_fill(0, count($subIds), '?'));

        // Nettoyer les liaisons subject_classes et teacher_assignments
        $pdo->prepare("DELETE FROM subject_classes WHERE subject_id IN ($inSubs)")->execute($subIds);
        $pdo->prepare("DELETE FROM teacher_assignments WHERE subject_id IN ($inSubs)")->execute($subIds);
        $pdo->prepare("DELETE FROM timetable_entries WHERE subject_id IN ($inSubs)")->execute($subIds);
        $pdo->prepare("DELETE FROM grades WHERE subject_id IN ($inSubs)")->execute($subIds);

        // Supprimer les matières
        $stmtDel = $pdo->prepare("DELETE FROM subjects WHERE id IN ($inSubs)");
        $stmtDel->execute($subIds);

        $pdo->commit();

        echo "\n✓ " . count($subjectsToDelete) . " matières du Primaire ont été supprimées avec succès !\n";
    }

    // Statistiques après suppression
    $remainingSubjects = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
    $remainingLinks = $pdo->query("SELECT COUNT(*) FROM subject_classes")->fetchColumn();

    echo "\n=== BILAN APPRÈS SUPPRESSION ===\n";
    echo "  - Matières restantes : $remainingSubjects\n";
    echo "  - Liaisons Matières-Classes restantes : $remainingLinks\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "ERREUR : " . $e->getMessage() . "\n";
    exit(1);
}
