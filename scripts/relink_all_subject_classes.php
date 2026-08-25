<?php
/**
 * Script ultra-rapide de reconstruction et de synchronisation des liaisons Matières - Classes
 * Fichier : scripts/relink_all_subject_classes.php
 */

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

    echo "=== DÉBUT DE LA RECONSTRUCTION ILLICO DES LIAISONS MATIÈRES - CLASSES ===\n\n";

    // 1. Déterminer l'année académique active
    $activeYearId = (int)$pdo->query("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1")->fetchColumn();
    if (!$activeYearId) {
        $activeYearId = (int)$pdo->query("SELECT id FROM academic_years LIMIT 1")->fetchColumn();
    }
    if (!$activeYearId) {
        $activeYearId = 3;
    }
    echo "[INFO] Année académique active : ID $activeYearId\n";

    // 2. Charger les classes et les matières
    $classes = $pdo->query("SELECT id, nom, teaching_type_id FROM classes")->fetchAll();
    $subjects = $pdo->query("SELECT id, nom, teaching_type_id FROM subjects")->fetchAll();

    echo "[INFO] Total classes trouvées : " . count($classes) . "\n";
    echo "[INFO] Total matières trouvées : " . count($subjects) . "\n";

    $pdo->beginTransaction();

    // Reconstruire de façon optimisée par batch
    $values = [];
    $params = [];
    $linkedCount = 0;

    foreach ($subjects as $s) {
        $sId = (int)$s['id'];
        $sTt = $s['teaching_type_id'] !== null ? (int)$s['teaching_type_id'] : null;

        foreach ($classes as $c) {
            $cId = (int)$c['id'];
            $cTt = $c['teaching_type_id'] !== null ? (int)$c['teaching_type_id'] : null;

            if ($sTt === null || $cTt === null || $sTt === $cTt) {
                $values[] = "(?, ?, ?)";
                $params[] = $sId;
                $params[] = $cId;
                $params[] = $activeYearId;
                $linkedCount++;

                if (count($values) >= 500) {
                    $sql = "INSERT IGNORE INTO subject_classes (subject_id, class_id, academic_year_id) VALUES " . implode(', ', $values);
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $values = [];
                    $params = [];
                }
            }
        }
    }

    if (!empty($values)) {
        $sql = "INSERT IGNORE INTO subject_classes (subject_id, class_id, academic_year_id) VALUES " . implode(', ', $values);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    $pdo->commit();

    $totalLinks = $pdo->query("SELECT COUNT(*) FROM subject_classes")->fetchColumn();
    echo "\n=== SYNCHRONISATION TERMINÉE AVEC SUCCÈS ===\n";
    echo "Total associations évaluées : $linkedCount\n";
    echo "Total associations actives dans 'subject_classes' : $totalLinks\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "ERREUR : " . $e->getMessage() . "\n";
    exit(1);
}
