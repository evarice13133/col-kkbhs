<?php

require getcwd() . '/vendor/autoload.php';

use App\Core\Database;
$db = Database::getInstance()->getConnection();

$duplicateGroupsSql = "
    SELECT
        sc.class_id,
        TRIM(LOWER(s.nom)) AS subject_key,
        MIN(s.id) AS canonical_subject_id,
        COUNT(DISTINCT s.id) AS duplicate_count
    FROM subject_classes sc
    JOIN subjects s ON s.id = sc.subject_id
    GROUP BY sc.class_id, TRIM(LOWER(s.nom))
    HAVING COUNT(DISTINCT s.id) > 1
    ORDER BY sc.class_id, subject_key
";

$groups = $db->query($duplicateGroupsSql)->fetchAll(PDO::FETCH_ASSOC);

if (empty($groups)) {
    echo "Aucun doublon de matiere par classe n'a ete detecte." . PHP_EOL;
    exit(0);
}

$movedGrades = 0;
$deletedGrades = 0;
$movedAssignments = 0;
$deletedAssignments = 0;
$deletedLinks = 0;
$deletedSubjects = 0;

$db->beginTransaction();

try {
    foreach ($groups as $group) {
        $classId = (int) $group['class_id'];
        $canonicalSubjectId = (int) $group['canonical_subject_id'];
        $subjectKey = (string) $group['subject_key'];

        $subjectIdsStmt = $db->prepare("
            SELECT DISTINCT s.id
            FROM subject_classes sc
            JOIN subjects s ON s.id = sc.subject_id
            WHERE sc.class_id = ?
              AND TRIM(LOWER(s.nom)) = ?
            ORDER BY s.id ASC
        ");
        $subjectIdsStmt->execute([$classId, $subjectKey]);
        $subjectIds = array_map('intval', $subjectIdsStmt->fetchAll(PDO::FETCH_COLUMN));

        foreach ($subjectIds as $subjectId) {
            if ($subjectId === $canonicalSubjectId) {
                continue;
            }

            // On transfere les notes de cette classe vers la matiere canonique quand aucune note equivalente n'existe deja.
            $gradeStmt = $db->prepare("
                SELECT g.id, g.student_id, g.academic_year_id, g.periode
                FROM grades g
                JOIN students st ON st.id = g.student_id
                WHERE g.subject_id = ?
                  AND st.class_id = ?
                ORDER BY g.id ASC
            ");
            $gradeStmt->execute([$subjectId, $classId]);
            $grades = $gradeStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($grades as $grade) {
                $existsStmt = $db->prepare("
                    SELECT id
                    FROM grades
                    WHERE student_id = ?
                      AND subject_id = ?
                      AND academic_year_id = ?
                      AND periode = ?
                    LIMIT 1
                ");
                $existsStmt->execute([
                    (int) $grade['student_id'],
                    $canonicalSubjectId,
                    (int) $grade['academic_year_id'],
                    (string) $grade['periode'],
                ]);
                $existingGradeId = $existsStmt->fetchColumn();

                if ($existingGradeId) {
                    $deleteGradeStmt = $db->prepare("DELETE FROM grades WHERE id = ?");
                    $deleteGradeStmt->execute([(int) $grade['id']]);
                    $deletedGrades++;
                    continue;
                }

                $moveGradeStmt = $db->prepare("UPDATE grades SET subject_id = ? WHERE id = ?");
                $moveGradeStmt->execute([$canonicalSubjectId, (int) $grade['id']]);
                $movedGrades++;
            }

            // On fusionne les affectations des enseignants vers la matiere canonique pour cette classe.
            $assignmentStmt = $db->prepare("
                SELECT user_id
                FROM teacher_assignments
                WHERE class_id = ?
                  AND subject_id = ?
            ");
            $assignmentStmt->execute([$classId, $subjectId]);
            $assignments = $assignmentStmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($assignments as $userId) {
                $existsAssignmentStmt = $db->prepare("
                    SELECT 1
                    FROM teacher_assignments
                    WHERE user_id = ?
                      AND class_id = ?
                      AND subject_id = ?
                    LIMIT 1
                ");
                $existsAssignmentStmt->execute([(int) $userId, $classId, $canonicalSubjectId]);

                if ($existsAssignmentStmt->fetchColumn()) {
                    $deleteAssignmentStmt = $db->prepare("
                        DELETE FROM teacher_assignments
                        WHERE user_id = ?
                          AND class_id = ?
                          AND subject_id = ?
                    ");
                    $deleteAssignmentStmt->execute([(int) $userId, $classId, $subjectId]);
                    $deletedAssignments++;
                    continue;
                }

                $moveAssignmentStmt = $db->prepare("
                    UPDATE teacher_assignments
                    SET subject_id = ?
                    WHERE user_id = ?
                      AND class_id = ?
                      AND subject_id = ?
                ");
                $moveAssignmentStmt->execute([$canonicalSubjectId, (int) $userId, $classId, $subjectId]);
                $movedAssignments++;
            }

            // On retire le lien redondant entre la matiere doublon et la classe concernee.
            $deleteLinkStmt = $db->prepare("DELETE FROM subject_classes WHERE subject_id = ? AND class_id = ?");
            $deleteLinkStmt->execute([$subjectId, $classId]);
            $deletedLinks += $deleteLinkStmt->rowCount();
        }
    }

    // On supprime les matieres devenues orphelines apres fusion.
    $orphanSubjects = $db->query("
        SELECT s.id
        FROM subjects s
        LEFT JOIN subject_classes sc ON sc.subject_id = s.id
        LEFT JOIN teacher_assignments ta ON ta.subject_id = s.id
        LEFT JOIN grades g ON g.subject_id = s.id
        WHERE sc.subject_id IS NULL
          AND ta.subject_id IS NULL
          AND g.subject_id IS NULL
    ")->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($orphanSubjects)) {
        $deleteSubjectStmt = $db->prepare("DELETE FROM subjects WHERE id = ?");
        foreach ($orphanSubjects as $subjectId) {
            $deleteSubjectStmt->execute([(int) $subjectId]);
            $deletedSubjects += $deleteSubjectStmt->rowCount();
        }
    }

    $db->commit();

    echo "Nettoyage termine." . PHP_EOL;
    echo "Groupes traites : " . count($groups) . PHP_EOL;
    echo "Notes deplacees : " . $movedGrades . PHP_EOL;
    echo "Notes supprimees en doublon : " . $deletedGrades . PHP_EOL;
    echo "Affectations deplacees : " . $movedAssignments . PHP_EOL;
    echo "Affectations supprimees en doublon : " . $deletedAssignments . PHP_EOL;
    echo "Liens classe-matiere supprimes : " . $deletedLinks . PHP_EOL;
    echo "Matieres orphelines supprimees : " . $deletedSubjects . PHP_EOL;
} catch (\Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, "Echec du nettoyage : " . $e->getMessage() . PHP_EOL);
    exit(1);
}
