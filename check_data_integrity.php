<?php
/**
 * Script pour vérifier l'intégrité des données entre les tables
 * À exécuter via: /check_data_integrity.php
 */

require_once __DIR__ . '/public/index.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification Intégrité des Données</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0">🔍 Vérification Intégrité des Données</h4>
            </div>
            <div class="card-body">
                <?php
                // 1. Année active
                $activeYear = $db->query("SELECT id, nom FROM academic_years WHERE is_active = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                $activeYearId = (int) $activeYear['id'];
                echo "<h5>Année active: " . htmlspecialchars($activeYear['nom']) . " (ID: $activeYearId)</h5>";
                
                // 2. Vérifier si les student_id dans grades existent dans students
                $orphanGrades = $db->query("
                    SELECT COUNT(*) FROM grades g
                    WHERE NOT EXISTS (SELECT 1 FROM students st WHERE st.id = g.student_id)
                ")->fetchColumn();
                
                echo "<h6 class='mt-4'>Notes avec student_id inexistant dans students: $orphanGrades</h6>";
                if ($orphanGrades > 0) {
                    echo "<p class='text-danger'>❌ Problème: Certaines notes référencent des étudiants qui n'existent pas</p>";
                }
                
                // 3. Vérifier si les subject_id dans grades existent dans subjects
                $orphanSubjects = $db->query("
                    SELECT COUNT(*) FROM grades g
                    WHERE NOT EXISTS (SELECT 1 FROM subjects s WHERE s.id = g.subject_id)
                ")->fetchColumn();
                
                echo "<h6 class='mt-4'>Notes avec subject_id inexistant dans subjects: $orphanSubjects</h6>";
                if ($orphanSubjects > 0) {
                    echo "<p class='text-danger'>❌ Problème: Certaines notes référencent des matières qui n'existent pas</p>";
                }
                
                // 4. Vérifier les class_id des étudiants vs subject_classes
                $studentClasses = $db->query("
                    SELECT DISTINCT st.class_id 
                    FROM students st 
                    WHERE st.academic_year_id = $activeYearId AND st.is_withdrawn = 0
                ")->fetchAll(PDO::FETCH_COLUMN);
                
                $subjectClassClasses = $db->query("
                    SELECT DISTINCT sc.class_id 
                    FROM subject_classes sc 
                    WHERE sc.academic_year_id = $activeYearId
                ")->fetchAll(PDO::FETCH_COLUMN);
                
                $missingInSubjectClasses = array_diff($studentClasses, $subjectClassClasses);
                
                echo "<h6 class='mt-4'>Classes avec élèves mais sans subject_classes:</h6>";
                if (count($missingInSubjectClasses) > 0) {
                    echo "<p class='text-danger'>❌ Classes: " . implode(', ', $missingInSubjectClasses) . "</p>";
                } else {
                    echo "<p class='text-success'>✅ Toutes les classes avec élèves ont des subject_classes</p>";
                }
                
                // 5. Vérifier les subject_id des grades vs subject_classes
                $gradeSubjects = $db->query("
                    SELECT DISTINCT g.subject_id 
                    FROM grades g 
                    WHERE g.academic_year_id = $activeYearId
                ")->fetchAll(PDO::FETCH_COLUMN);
                
                $subjectClassSubjects = $db->query("
                    SELECT DISTINCT sc.subject_id 
                    FROM subject_classes sc 
                    WHERE sc.academic_year_id = $activeYearId
                ")->fetchAll(PDO::FETCH_COLUMN);
                
                $missingSubjects = array_diff($gradeSubjects, $subjectClassSubjects);
                
                echo "<h6 class='mt-4'>Matières dans grades mais pas dans subject_classes:</h6>";
                if (count($missingSubjects) > 0) {
                    echo "<p class='text-danger'>❌ Matières: " . implode(', ', $missingSubjects) . "</p>";
                } else {
                    echo "<p class='text-success'>✅ Toutes les matières dans grades sont dans subject_classes</p>";
                }
                
                // 6. Test direct de la requête getBulkGlobalFilledCounts
                $activeEvaluations = $db->query("SELECT label FROM sequences WHERE is_active = 1 ORDER BY position ASC")->fetchAll(PDO::FETCH_COLUMN) ?: [];
                
                if (!empty($activeEvaluations)) {
                    $placeholders = implode(',', array_fill(0, count($activeEvaluations), '?'));
                    $sql = "SELECT CONCAT(st.class_id, '_', g.subject_id) as key_combo, COUNT(*) as count
                            FROM grades g
                            JOIN students st ON st.id = g.student_id
                            WHERE g.academic_year_id = ? AND g.periode IN ($placeholders) AND st.is_withdrawn = 0
                            GROUP BY st.class_id, g.subject_id";
                    $stmt = $db->prepare($sql);
                    $stmt->execute(array_merge([$activeYearId], $activeEvaluations));
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo "<h6 class='mt-4'>Test direct de la requête getBulkGlobalFilledCounts:</h6>";
                    echo "<p>Résultats trouvés: " . count($result) . "</p>";
                    
                    if (count($result) > 0) {
                        echo "<table class='table table-sm'>";
                        echo "<thead><tr><th>Key</th><th>Count</th></tr></thead>";
                        echo "<tbody>";
                        foreach ($result as $row) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['key_combo']) . "</td>";
                            echo "<td>" . $row['count'] . "</td>";
                            echo "</tr>";
                        }
                        echo "</tbody>";
                        echo "</table>";
                    } else {
                        echo "<p class='text-danger'>❌ La requête ne retourne aucun résultat</p>";
                        
                        // Test sans la condition is_withdrawn
                        $sql2 = "SELECT CONCAT(st.class_id, '_', g.subject_id) as key_combo, COUNT(*) as count
                                 FROM grades g
                                 JOIN students st ON st.id = g.student_id
                                 WHERE g.academic_year_id = ? AND g.periode IN ($placeholders)
                                 GROUP BY st.class_id, g.subject_id";
                        $stmt2 = $db->prepare($sql2);
                        $stmt2->execute(array_merge([$activeYearId], $activeEvaluations));
                        $result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                        
                        echo "<h6 class='mt-4'>Test SANS condition is_withdrawn:</h6>";
                        echo "<p>Résultats trouvés: " . count($result2) . "</p>";
                        
                        if (count($result2) > 0) {
                            echo "<p class='text-warning'>⚠️ Le problème vient de la condition is_withdrawn</p>";
                        }
                    }
                }
                ?>
                
                <div class="mt-4">
                    <a href="/dashboard" class="btn btn-outline-secondary">Retour au tableau de bord</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
