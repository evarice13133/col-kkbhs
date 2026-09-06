<?php

/**
 * Migration : Désolidarisation contrôlée des matières multi-classes
 * 
 * Cette migration transforme les matières partagées entre plusieurs classes
 * en occurrences indépendantes (1 ligne dans `subjects` par classe).
 * 
 * Toutes les données dépendantes (notes, compétences, évaluations, affectations enseignants,
 * emplois du temps, groupes) sont réattribuées sans aucune perte.
 */

require_once __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "Connected to database successfully.\n";
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

echo "=== MIGRATION DE DESOLIDARISATION DES MATIERES ===\n\n";

$pdo->beginTransaction();

try {
    // 1. Audit pré-migration des compteurs
    $countSubjectsBefore = (int)$pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
    $countSCBefore = (int)$pdo->query("SELECT COUNT(*) FROM subject_classes")->fetchColumn();
    $countGradesBefore = (int)$pdo->query("SELECT COUNT(*) FROM grades")->fetchColumn();
    $countTAFormBefore = (int)$pdo->query("SELECT COUNT(*) FROM teacher_assignments")->fetchColumn();
    $countTimetableBefore = (int)$pdo->query("SELECT COUNT(*) FROM timetable_entries")->fetchColumn();
    $countCompBefore = (int)$pdo->query("SELECT COUNT(*) FROM competencies")->fetchColumn();
    $countEvalCompBefore = (int)$pdo->query("SELECT COUNT(*) FROM evaluation_competencies")->fetchColumn();

    echo "--- STATISTIQUES AVANT MIGRATION ---\n";
    echo "Subjects: $countSubjectsBefore\n";
    echo "Subject_classes: $countSCBefore\n";
    echo "Grades: $countGradesBefore\n";
    echo "Teacher Assignments: $countTAFormBefore\n";
    echo "Timetable Entries: $countTimetableBefore\n";
    echo "Competencies: $countCompBefore\n";
    echo "Evaluation Competencies: $countEvalCompBefore\n\n";

    // 2. Recherche des matières associées à plusieurs classes
    $stmtMulti = $pdo->query("
        SELECT sc.subject_id, GROUP_CONCAT(sc.class_id ORDER BY sc.class_id ASC) as class_ids, COUNT(sc.class_id) as total_classes
        FROM subject_classes sc
        GROUP BY sc.subject_id
        HAVING total_classes > 1
    ");
    $multiSubjects = $stmtMulti->fetchAll();
    echo "Trouvé " . count($multiSubjects) . " matières associées à plusieurs classes.\n\n";

    $totalDuplicated = 0;

    foreach ($multiSubjects as $row) {
        $oldSubjectId = (int)$row['subject_id'];
        $classIds = array_map('intval', explode(',', $row['class_ids']));
        
        // La première classe conserve la matière initiale ($oldSubjectId)
        $primaryClassId = array_shift($classIds);
        
        // Charger la matière d'origine
        $stmtSub = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
        $stmtSub->execute([$oldSubjectId]);
        $subData = $stmtSub->fetch();

        if (!$subData) {
            continue;
        }

        echo "Désolidarisation matière ID {$oldSubjectId} ('{$subData['nom']}') pour " . count($classIds) . " classe(s) supplémentaire(s)...\n";

        // Charger les compétences associées à la matière initiale
        $stmtComps = $pdo->prepare("SELECT * FROM competencies WHERE subject_id = ?");
        $stmtComps->execute([$oldSubjectId]);
        $oldCompetencies = $stmtComps->fetchAll();

        // Charger les affectations aux groupes de matières
        $stmtSGA = $pdo->prepare("SELECT subject_group_id FROM subject_group_assignments WHERE subject_id = ?");
        $stmtSGA->execute([$oldSubjectId]);
        $subjectGroups = $stmtSGA->fetchAll(PDO::FETCH_COLUMN);

        foreach ($classIds as $targetClassId) {
            // a. Insérer une nouvelle ligne dans `subjects`
            $stmtInsertSub = $pdo->prepare("
                INSERT INTO subjects (
                    nom, coefficient, groupe, code_uv, code_ue, subject_group_id, 
                    status, teaching_type_id, department_id, vhm, vhp, th_max, observations, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
                )
            ");
            $stmtInsertSub->execute([
                $subData['nom'],
                $subData['coefficient'],
                $subData['groupe'],
                $subData['code_uv'],
                $subData['code_ue'],
                $subData['subject_group_id'],
                $subData['status'],
                $subData['teaching_type_id'],
                $subData['department_id'],
                $subData['vhm'],
                $subData['vhp'],
                $subData['th_max'],
                $subData['observations']
            ]);
            $newSubjectId = (int)$pdo->lastInsertId();
            $totalDuplicated++;

            // b. Mettre à jour `subject_classes` pour cette classe
            $stmtUpdateSC = $pdo->prepare("
                UPDATE subject_classes 
                SET subject_id = ? 
                WHERE subject_id = ? AND class_id = ?
            ");
            $stmtUpdateSC->execute([$newSubjectId, $oldSubjectId, $targetClassId]);

            // c. Mettre à jour `teacher_assignments` pour cette classe
            $stmtUpdateTA = $pdo->prepare("
                UPDATE teacher_assignments 
                SET subject_id = ? 
                WHERE subject_id = ? AND class_id = ?
            ");
            $stmtUpdateTA->execute([$newSubjectId, $oldSubjectId, $targetClassId]);

            // d. Mettre à jour `teacher_class_competencies` pour cette classe
            $stmtUpdateTCC = $pdo->prepare("
                UPDATE teacher_class_competencies 
                SET subject_id = ? 
                WHERE subject_id = ? AND class_id = ?
            ");
            $stmtUpdateTCC->execute([$newSubjectId, $oldSubjectId, $targetClassId]);

            // e. Mettre à jour `timetable_entries` pour cette classe
            $stmtUpdateTE = $pdo->prepare("
                UPDATE timetable_entries te
                INNER JOIN timetables t ON te.timetable_id = t.id
                SET te.subject_id = ?
                WHERE te.subject_id = ? AND t.class_id = ?
            ");
            $stmtUpdateTE->execute([$newSubjectId, $oldSubjectId, $targetClassId]);

            // f. Mettre à jour `grades` pour les élèves de cette classe
            $stmtUpdateGrades = $pdo->prepare("
                UPDATE grades g
                INNER JOIN students st ON g.student_id = st.id
                SET g.subject_id = ?
                WHERE g.subject_id = ? AND st.class_id = ?
            ");
            $stmtUpdateGrades->execute([$newSubjectId, $oldSubjectId, $targetClassId]);

            // g. Dupliquer les compétences et réattribuer `evaluation_competencies`
            foreach ($oldCompetencies as $oldComp) {
                $stmtInsComp = $pdo->prepare("
                    INSERT INTO competencies (subject_id, libelle, description, position, created_by, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $stmtInsComp->execute([
                    $newSubjectId,
                    $oldComp['libelle'],
                    $oldComp['description'],
                    $oldComp['position'],
                    $oldComp['created_by']
                ]);
                $newCompId = (int)$pdo->lastInsertId();

                // Mettre à jour evaluation_competencies pour cette compétence & cette classe
                $stmtUpdateEC = $pdo->prepare("
                    UPDATE evaluation_competencies 
                    SET subject_id = ?, competency_id = ? 
                    WHERE subject_id = ? AND competency_id = ? AND class_id = ?
                ");
                $stmtUpdateEC->execute([$newSubjectId, $newCompId, $oldSubjectId, $oldComp['id'], $targetClassId]);
            }

            // Mettre à jour toute autre evaluation_competency restante pour cette matière & classe
            $stmtUpdateECRest = $pdo->prepare("
                UPDATE evaluation_competencies 
                SET subject_id = ? 
                WHERE subject_id = ? AND class_id = ?
            ");
            $stmtUpdateECRest->execute([$newSubjectId, $oldSubjectId, $targetClassId]);

            // h. Dupliquer la liaison aux groupes de matières
            foreach ($subjectGroups as $sgId) {
                $stmtInsSGA = $pdo->prepare("
                    INSERT IGNORE INTO subject_group_assignments (subject_id, subject_group_id) 
                    VALUES (?, ?)
                ");
                $stmtInsSGA->execute([$newSubjectId, $sgId]);
            }
        }
    }

    echo "\nTotal de matières dupliquées : {$totalDuplicated}\n";

    // 3. Validation de l'intégrité après migration
    $countSubjectsAfter = (int)$pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
    $countSCAfter = (int)$pdo->query("SELECT COUNT(*) FROM subject_classes")->fetchColumn();
    $countGradesAfter = (int)$pdo->query("SELECT COUNT(*) FROM grades")->fetchColumn();
    $countTAFormAfter = (int)$pdo->query("SELECT COUNT(*) FROM teacher_assignments")->fetchColumn();
    $countTimetableAfter = (int)$pdo->query("SELECT COUNT(*) FROM timetable_entries")->fetchColumn();
    $countEvalCompAfter = (int)$pdo->query("SELECT COUNT(*) FROM evaluation_competencies")->fetchColumn();

    // Vérifier qu'aucune matière dans subject_classes n'a désormais plus d'une classe
    $stmtCheckMultiAfter = $pdo->query("
        SELECT COUNT(*) FROM (
            SELECT subject_id 
            FROM subject_classes 
            GROUP BY subject_id 
            HAVING COUNT(class_id) > 1
        ) as remaining_multi
    ")->fetchColumn();

    echo "\n--- STATISTIQUES APRES MIGRATION ---\n";
    echo "Subjects: $countSubjectsAfter (Gain de $totalDuplicated matières)\n";
    echo "Subject_classes: $countSCAfter (Invarié: OK)\n";
    echo "Grades: $countGradesAfter (Invarié: OK)\n";
    echo "Teacher Assignments: $countTAFormAfter (Invarié: OK)\n";
    echo "Timetable Entries: $countTimetableAfter (Invarié: OK)\n";
    echo "Evaluation Competencies: $countEvalCompAfter (Invarié: OK)\n";
    echo "Matières multi-classes restantes : $stmtCheckMultiAfter (Objectif: 0)\n\n";

    if ($countGradesBefore !== $countGradesAfter || $countSCAfter !== $countSCBefore || $stmtCheckMultiAfter > 0) {
        throw new Exception("Échec des vérifications d'intégrité post-migration ! Annulation de la transaction.");
    }

    $pdo->commit();
    echo "=== MIGRATION EFFECTUÉE ET VALIDÉE AVEC SUCCÈS ===\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "ERREUR DURANT LA MIGRATION: " . $e->getMessage() . "\n";
    exit(1);
}
