<?php
/**
 * Optimization Script: Database Indexing
 * Adds indexes to speed up dashboard queries and general reporting.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    echo "Démarrage de l'optimisation des index...\n";

    // 1. Index sur grades pour accélérer le filtrage par enseignant et année
    $db->exec("CREATE INDEX IF NOT EXISTS idx_grades_stats ON grades (teacher_id, academic_year_id, subject_id)");
    echo " Index idx_grades_stats créé sur la table grades.\n";

    // 2. Index sur students pour accélérer le comptage par classe (déjà FK mais utile)
    $db->exec("CREATE INDEX IF NOT EXISTS idx_students_class ON students (class_id)");
    echo " Index idx_students_class créé sur la table students.\n";

    // 3. Index sur teacher_assignments pour accélérer la récupération des classes d'un prof
    $db->exec("CREATE INDEX IF NOT EXISTS idx_ta_user ON teacher_assignments (user_id)");
    echo " Index idx_ta_user créé sur la table teacher_assignments.\n";

    echo " Optimisation terminée avec succès !\n";
} catch (Exception $e) {
    echo " Erreur : " . $e->getMessage() . "\n";
}
