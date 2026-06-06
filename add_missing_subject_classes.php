<?php
/**
 * Script pour ajouter les subject_classes manquantes basées sur les notes existantes
 * À exécuter via: /add_missing_subject_classes.php
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
    <title>Ajouter Subject_classes Manquantes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">➕ Ajouter Subject_classes Manquantes</h4>
            </div>
            <div class="card-body">
                <?php
                // 1. Année active
                $activeYear = $db->query("SELECT id, nom FROM academic_years WHERE is_active = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                $activeYearId = (int) $activeYear['id'];
                echo "<h5>Année active: " . htmlspecialchars($activeYear['nom']) . " (ID: $activeYearId)</h5>";
                
                // 2. Trouver les combinaisons classe/matière dans grades qui n'existent pas dans subject_classes
                $missingCombinations = $db->query("
                    SELECT DISTINCT st.class_id, g.subject_id
                    FROM grades g
                    JOIN students st ON st.id = g.student_id
                    WHERE g.academic_year_id = $activeYearId
                    AND NOT EXISTS (
                        SELECT 1 FROM subject_classes sc
                        WHERE sc.academic_year_id = $activeYearId
                        AND sc.class_id = st.class_id
                        AND sc.subject_id = g.subject_id
                    )
                ")->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<h6 class='mt-4'>Combinaisons classe/matière dans grades mais pas dans subject_classes: " . count($missingCombinations) . "</h6>";
                
                if (count($missingCombinations) > 0) {
                    echo "<table class='table table-sm table-striped'>";
                    echo "<thead><tr><th>Classe ID</th><th>Matière ID</th></tr></thead>";
                    echo "<tbody>";
                    foreach ($missingCombinations as $comb) {
                        echo "<tr>";
                        echo "<td>" . $comb['class_id'] . "</td>";
                        echo "<td>" . $comb['subject_id'] . "</td>";
                        echo "</tr>";
                    }
                    echo "</tbody>";
                    echo "</table>";
                    
                    if (isset($_GET['add']) && $_GET['add'] === 'yes') {
                        // Ajouter les subject_classes manquantes
                        $added = 0;
                        $skipped = 0;
                        
                        foreach ($missingCombinations as $comb) {
                            $classId = (int) $comb['class_id'];
                            $subjectId = (int) $comb['subject_id'];
                            
                            // Vérifier si la matière est active
                            $subjectActive = $db->query("SELECT status FROM subjects WHERE id = $subjectId")->fetchColumn();
                            
                            if ($subjectActive == 1) {
                                // Vérifier si cette combinaison existe déjà
                                $exists = $db->query("
                                    SELECT COUNT(*) FROM subject_classes
                                    WHERE academic_year_id = $activeYearId
                                    AND class_id = $classId
                                    AND subject_id = $subjectId
                                ")->fetchColumn();
                                
                                if ($exists == 0) {
                                    $db->query("
                                        INSERT INTO subject_classes (academic_year_id, class_id, subject_id)
                                        VALUES ($activeYearId, $classId, $subjectId)
                                    ");
                                    $added++;
                                } else {
                                    $skipped++;
                                }
                            } else {
                                $skipped++;
                            }
                        }
                        
                        echo "<div class='alert alert-success mt-4'>";
                        echo "<strong>✅ $added subject_classes ajoutées</strong><br>";
                        echo "<strong>⏭️ $skipped ignorées (matières inactives ou doublons)</strong>";
                        echo "</div>";
                        
                        echo "<a href='add_missing_subject_classes.php' class='btn btn-secondary'>Vérifier à nouveau</a>";
                    } else {
                        echo "<a href='add_missing_subject_classes.php?add=yes' class='btn btn-success mt-3' onclick='return confirm(\"Ajouter les " . count($missingCombinations) . " subject_classes manquantes ?\")'>";
                        echo "Ajouter les subject_classes manquantes";
                        echo "</a>";
                    }
                } else {
                    echo "<div class='alert alert-success mt-4'>";
                    echo "<strong>✅ Toutes les combinaisons sont déjà dans subject_classes</strong>";
                    echo "</div>";
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
