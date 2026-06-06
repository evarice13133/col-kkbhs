<?php
/**
 * Script de migration pour copier les subject_classes d'une année précédente vers l'année active
 * 
 * Usage: Accéder via le navigateur: /migrate_subject_classes.php
 */

// Charger l'application
require_once __DIR__ . '/public/index.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migration subject_classes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">🔄 Migration subject_classes</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        // 1. Récupérer l'année active
                        $activeYear = $db->query("SELECT id, nom FROM academic_years WHERE is_active = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                        
                        if (!$activeYear) {
                            echo "<div class='alert alert-danger'>Aucune année académique active trouvée !</div>";
                            exit;
                        }
                        
                        $activeYearId = (int) $activeYear['id'];
                        echo "<h5>Année active: " . htmlspecialchars($activeYear['nom']) . " (ID: $activeYearId)</h5>";
                        
                        // 2. Vérifier si des subject_classes existent déjà pour l'année active
                        $existingCount = $db->query("SELECT COUNT(*) FROM subject_classes WHERE academic_year_id = $activeYearId")->fetchColumn();
                        
                        if ($existingCount > 0) {
                            echo "<div class='alert alert-warning'>";
                            echo "<strong>Attention:</strong> Il existe déjà $existingCount subject_classes pour l'année active.<br>";
                            echo "La migration créera des doublons si vous continuez.";
                            echo "</div>";
                        }
                        
                        // 3. Trouver les années précédentes avec des subject_classes
                        $previousYears = $db->query("
                            SELECT sc.academic_year_id, ay.nom as year_nom, COUNT(*) as count
                            FROM subject_classes sc
                            JOIN academic_years ay ON ay.id = sc.academic_year_id
                            WHERE sc.academic_year_id != $activeYearId
                            GROUP BY sc.academic_year_id, ay.nom
                            ORDER BY ay.id DESC
                        ")->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (empty($previousYears)) {
                            echo "<div class='alert alert-danger'>Aucune année précédente avec des subject_classes trouvée !</div>";
                            exit;
                        }
                        
                        echo "<h6 class='mt-4'>Années précédentes disponibles:</h6>";
                        echo "<table class='table table-striped'>";
                        echo "<thead><tr><th>Année</th><th>Subject_classes</th><th>Action</th></tr></thead>";
                        echo "<tbody>";
                        
                        foreach ($previousYears as $year) {
                            $sourceYearId = (int) $year['academic_year_id'];
                            $yearNom = htmlspecialchars($year['year_nom']);
                            $count = $year['count'];
                            
                            echo "<tr>";
                            echo "<td>$yearNom (ID: $sourceYearId)</td>";
                            echo "<td>$count</td>";
                            echo "<td>";
                            
                            if (isset($_GET['source']) && (int) $_GET['source'] === $sourceYearId) {
                                // Exécuter la migration
                                echo "<strong>Migration en cours...</strong>";
                                
                                try {
                                    // Récupérer les subject_classes de l'année source
                                    $sourceClasses = $db->query("
                                        SELECT class_id, subject_id
                                        FROM subject_classes
                                        WHERE academic_year_id = $sourceYearId
                                    ")->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    // Insérer dans l'année active (éviter les doublons)
                                    $inserted = 0;
                                    $skipped = 0;
                                    
                                    foreach ($sourceClasses as $sc) {
                                        $classId = (int) $sc['class_id'];
                                        $subjectId = (int) $sc['subject_id'];
                                        
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
                                            $inserted++;
                                        } else {
                                            $skipped++;
                                        }
                                    }
                                    
                                    echo "<div class='alert alert-success mt-2'>";
                                    echo "<strong>Migration terminée !</strong><br>";
                                    echo "✅ $inserted subject_classes copiées<br>";
                                    echo "⏭️ $skipped doublons évités";
                                    echo "</div>";
                                    
                                    echo "<a href='migrate_subject_classes.php' class='btn btn-secondary btn-sm mt-2'>Réinitialiser</a>";
                                    
                                } catch (Exception $e) {
                                    echo "<div class='alert alert-danger mt-2'>Erreur: " . htmlspecialchars($e->getMessage()) . "</div>";
                                }
                                
                            } else {
                                echo "<a href='migrate_subject_classes.php?source=$sourceYearId' class='btn btn-primary btn-sm' onclick='return confirm(\"Copier les subject_classes de $yearNom vers l\\'année active ?\")'>";
                                echo "Copier";
                                echo "</a>";
                            }
                            
                            echo "</td>";
                            echo "</tr>";
                        }
                        
                        echo "</tbody>";
                        echo "</table>";
                        ?>
                        
                        <div class="mt-4">
                            <a href="/dashboard" class="btn btn-outline-secondary">Retour au tableau de bord</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
