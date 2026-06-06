<?php
/**
 * Script de diagnostic pour la progression globale en production
 * À exécuter via: /diagnose_progress.php
 * Supprimer après diagnostic
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
    <title>Diagnostic Progression Globale</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">🔍 Diagnostic Progression Globale - Production</h4>
            </div>
            <div class="card-body">
                <?php
                // 1. Année active
                $activeYear = $db->query("SELECT id, nom FROM academic_years WHERE is_active = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                $activeYearId = (int) $activeYear['id'];
                echo "<h5>Année active: " . htmlspecialchars($activeYear['nom']) . " (ID: $activeYearId)</h5>";
                
                // 2. Séquences actives
                $activeEvaluations = $db->query("SELECT label FROM sequences WHERE is_active = 1 ORDER BY position ASC")->fetchAll(PDO::FETCH_COLUMN) ?: [];
                $numEvals = count($activeEvaluations);
                echo "<h5>Séquences actives ($numEvals): " . implode(', ', $activeEvaluations) . "</h5>";
                
                // 3. Subject_classes pour l'année active
                $allSubjectClasses = $db->query("
                    SELECT sc.class_id, sc.subject_id
                    FROM subject_classes sc
                    JOIN subjects s ON s.id = sc.subject_id
                    WHERE sc.academic_year_id = {$activeYearId} AND s.status = 1
                ")->fetchAll(PDO::FETCH_ASSOC);
                echo "<h5>Subject_classes actives: " . count($allSubjectClasses) . "</h5>";
                
                // 4. Effectifs par classe
                $allClassCounts = $db->query("SELECT class_id, COUNT(*) FROM students WHERE is_withdrawn = 0 AND academic_year_id = {$activeYearId} GROUP BY class_id")->fetchAll(PDO::FETCH_KEY_PAIR);
                echo "<h5>Effectifs par classe: " . count($allClassCounts) . " classes avec élèves</h5>";
                
                // 5. Notes saisies par classe/matière
                if (!empty($activeEvaluations)) {
                    $placeholders = implode(',', array_fill(0, count($activeEvaluations), '?'));
                    $sql = "SELECT CONCAT(st.class_id, '_', g.subject_id) as key_combo, COUNT(*) as count
                            FROM grades g
                            JOIN students st ON st.id = g.student_id
                            WHERE g.academic_year_id = ? AND g.periode IN ($placeholders) AND st.is_withdrawn = 0
                            GROUP BY st.class_id, g.subject_id";
                    $stmt = $db->prepare($sql);
                    $stmt->execute(array_merge([$activeYearId], $activeEvaluations));
                    $allFilledCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                    echo "<h5>Notes saisies par classe/matière: " . count($allFilledCounts) . "</h5>";
                } else {
                    echo "<h5 class='text-danger'>AUCUNE SÉQUENCE ACTIVE</h5>";
                    $allFilledCounts = [];
                }
                
                // 6. Calcul de la progression
                $globalExpected = 0;
                $globalFilled = 0;
                
                echo "<h6 class='mt-4'>Détail du calcul:</h6>";
                echo "<table class='table table-sm table-striped'>";
                echo "<thead><tr><th>Classe</th><th>Matière</th><th>Élèves</th><th>Notes saisies</th><th>Attendu</th></tr></thead>";
                echo "<tbody>";
                
                $shownCount = 0;
                foreach ($allSubjectClasses as $sc) {
                    $cId = (int) $sc['class_id'];
                    $sId = (int) $sc['subject_id'];
                    $studentCount = $allClassCounts[$cId] ?? 0;
                    $key = "{$cId}_{$sId}";
                    $filledCount = $allFilledCounts[$key] ?? 0;
                    $expectedForThis = $studentCount * $numEvals;
                    
                    if ($studentCount > 0 || $filledCount > 0) {
                        $shownCount++;
                        if ($shownCount <= 20) { // Afficher seulement les 20 premiers avec des données
                            echo "<tr>";
                            echo "<td>$cId</td>";
                            echo "<td>$sId</td>";
                            echo "<td>$studentCount</td>";
                            echo "<td>$filledCount</td>";
                            echo "<td>$expectedForThis</td>";
                            echo "</tr>";
                        }
                    }
                    
                    $globalExpected += $expectedForThis;
                    $globalFilled += $filledCount;
                }
                
                if ($shownCount > 20) {
                    echo "<tr><td colspan='5' class='text-center'>... et " . ($shownCount - 20) . " autres combinaisons</td></tr>";
                }
                echo "</tbody>";
                echo "</table>";
                
                $globalProgress = $globalExpected > 0 ? round(($globalFilled / $globalExpected) * 100) : 0;
                
                echo "<div class='alert mt-4 " . ($globalProgress > 0 ? 'alert-success' : 'alert-danger') . "'>";
                echo "<h5>Résultat:</h5>";
                echo "<strong>Attendu:</strong> $globalExpected notes<br>";
                echo "<strong>Saisi:</strong> $globalFilled notes<br>";
                echo "<strong>Progression:</strong> $globalProgress%<br>";
                echo "</div>";
                
                // 7. Diagnostic des problèmes
                echo "<h6 class='mt-4'>Diagnostic:</h6>";
                $issues = [];
                
                if ($numEvals == 0) {
                    $issues[] = "❌ Aucune séquence active (is_active = 1 dans la table sequences)";
                }
                
                if (count($allSubjectClasses) == 0) {
                    $issues[] = "❌ Aucune subject_class définie pour l'année active";
                }
                
                if (count($allClassCounts) == 0) {
                    $issues[] = "❌ Aucun élève actif (is_withdrawn = 0) pour l'année active";
                }
                
                if (count($allFilledCounts) == 0 && $globalExpected > 0) {
                    $issues[] = "❌ Aucune note saisie pour les périodes actives";
                }
                
                if ($globalExpected == 0) {
                    $issues[] = "❌ globalExpected = 0 (pas d'élèves ou pas de séquences)";
                }
                
                if (empty($issues)) {
                    echo "<div class='alert alert-success'>✅ Aucun problème détecté dans la structure des données</div>";
                } else {
                    echo "<div class='alert alert-danger'>";
                    foreach ($issues as $issue) {
                        echo "<p class='mb-1'>$issue</p>";
                    }
                    echo "</div>";
                }
                
                // 8. Vérification des périodes dans grades
                $gradesPeriodes = $db->query("
                    SELECT DISTINCT periode, COUNT(*) as count
                    FROM grades
                    WHERE academic_year_id = $activeYearId
                    GROUP BY periode
                    ORDER BY periode
                ")->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<h6 class='mt-4'>Périodes présentes dans grades:</h6>";
                echo "<table class='table table-sm'>";
                echo "<thead><tr><th>Période</th><th>Count</th><th>Active?</th></tr></thead>";
                echo "<tbody>";
                foreach ($gradesPeriodes as $row) {
                    $isActive = in_array($row['periode'], $activeEvaluations) ? '✅' : '❌';
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['periode']) . "</td>";
                    echo "<td>" . $row['count'] . "</td>";
                    echo "<td>$isActive</td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
                ?>
                
                <div class="mt-4">
                    <a href="/dashboard" class="btn btn-outline-secondary">Retour au tableau de bord</a>
                    <button onclick="if(confirm('Supprimer ce script de diagnostic?')) window.location.href='delete_diagnostic.php'" class="btn btn-danger">Supprimer ce script</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
