<?php
/**
 * Script pour vérifier et corriger l'academic_year_id des notes
 * À exécuter via: /fix_grades_year.php
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
    <title>Correction academic_year_id des notes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0">🔧 Correction academic_year_id des notes</h4>
            </div>
            <div class="card-body">
                <?php
                // 1. Année active
                $activeYear = $db->query("SELECT id, nom FROM academic_years WHERE is_active = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                $activeYearId = (int) $activeYear['id'];
                echo "<h5>Année active: " . htmlspecialchars($activeYear['nom']) . " (ID: $activeYearId)</h5>";
                
                // 2. Distribution des notes par academic_year_id
                $gradesByYear = $db->query("
                    SELECT academic_year_id, COUNT(*) as count
                    FROM grades
                    GROUP BY academic_year_id
                ")->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<h6 class='mt-4'>Distribution des notes par academic_year_id:</h6>";
                echo "<table class='table table-striped'>";
                echo "<thead><tr><th>academic_year_id</th><th>Count</th></tr></thead>";
                echo "<tbody>";
                foreach ($gradesByYear as $row) {
                    $isMatch = (int) $row['academic_year_id'] === $activeYearId ? '✅' : '❌';
                    echo "<tr>";
                    echo "<td>$isMatch " . $row['academic_year_id'] . "</td>";
                    echo "<td>" . $row['count'] . "</td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
                
                // 3. Vérifier si les notes ont le mauvais academic_year_id
                $wrongYearCount = $db->query("
                    SELECT COUNT(*) FROM grades 
                    WHERE academic_year_id != $activeYearId
                ")->fetchColumn();
                
                if ($wrongYearCount > 0) {
                    echo "<div class='alert alert-warning mt-4'>";
                    echo "<strong>⚠️ $wrongYearCount notes ont un academic_year_id incorrect</strong>";
                    echo "</div>";
                    
                    if (isset($_GET['fix']) && $_GET['fix'] === 'yes') {
                        // Mettre à jour toutes les notes vers l'année active
                        $stmt = $db->prepare("UPDATE grades SET academic_year_id = ? WHERE academic_year_id != ?");
                        $stmt->execute([$activeYearId, $activeYearId]);
                        $updated = $stmt->rowCount();
                        
                        echo "<div class='alert alert-success'>";
                        echo "<strong>✅ $updated notes mises à jour vers l'année active</strong>";
                        echo "</div>";
                        
                        echo "<a href='fix_grades_year.php' class='btn btn-secondary'>Vérifier à nouveau</a>";
                    } else {
                        echo "<a href='fix_grades_year.php?fix=yes' class='btn btn-danger mt-3' onclick='return confirm(\"Mettre à jour toutes les notes vers l\\'année active ?\")'>";
                        echo "Corriger toutes les notes";
                        echo "</a>";
                    }
                } else {
                    echo "<div class='alert alert-success mt-4'>";
                    echo "<strong>✅ Toutes les notes ont déjà le bon academic_year_id</strong>";
                    echo "</div>";
                }
                
                // 4. Vérifier les étudiants is_withdrawn
                $withdrawnStudents = $db->query("
                    SELECT COUNT(*) FROM students 
                    WHERE is_withdrawn = 1 AND academic_year_id = $activeYearId
                ")->fetchColumn();
                
                echo "<h6 class='mt-4'>Étudiants retirés (is_withdrawn = 1): $withdrawnStudents</h6>";
                
                if ($withdrawnStudents > 0) {
                    $withdrawnWithGrades = $db->query("
                        SELECT COUNT(DISTINCT g.student_id) 
                        FROM grades g
                        JOIN students st ON st.id = g.student_id
                        WHERE st.is_withdrawn = 1 AND st.academic_year_id = $activeYearId
                    ")->fetchColumn();
                    
                    echo "<p class='text-warning'>$withdrawnWithGrades étudiants retirés ont des notes</p>";
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
