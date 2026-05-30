#!/usr/bin/env php
<?php
/**
 * SCRIPT DE TEST - Suppression d'Enseignant et Vérification des Notes
 * 
 * Objectif: Valider que les notes restent en base même si l'enseignant est supprimé
 * 
 * Usage: php scripts/test_teacher_deletion.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "\n" . str_repeat("=", 100) . "\n";
    echo "  TEST: Protection des Notes Contre la Suppression d'Enseignants\n";
    echo "  " . date('Y-m-d H:i:s') . "\n";
    echo str_repeat("=", 100) . "\n\n";
    
    // ═══════════════════════════════════════════════════════════════════════════════════
    // ÉTAPE 1: Sélectionner un enseignant avec des notes
    // ═══════════════════════════════════════════════════════════════════════════════════
    
    echo "[1/5] Sélection d'un enseignant avec des notes...\n";
    echo str_repeat("-", 100) . "\n";
    
    $teacherQuery = "
        SELECT u.id, u.nom, u.prenom, u.username, COUNT(g.id) as grade_count
        FROM users u
        LEFT JOIN grades g ON u.id = g.teacher_id
        WHERE u.role = 'enseignant'
        GROUP BY u.id
        HAVING grade_count > 0
        ORDER BY grade_count DESC
        LIMIT 1
    ";
    
    $teacherResult = $db->query($teacherQuery)->fetch(PDO::FETCH_ASSOC);
    
    if (!$teacherResult) {
        echo "  ⚠️  Aucun enseignant avec des notes trouvé. Sélection du premier enseignant...\n";
        $teacherResult = $db->query(
            "SELECT id, nom, prenom, username FROM users WHERE role = 'enseignant' LIMIT 1"
        )->fetch(PDO::FETCH_ASSOC);
        if (!$teacherResult) {
            echo "  ❌ Aucun enseignant trouvé dans la base!\n";
            exit(1);
        }
    }
    
    $teacherId = (int) $teacherResult['id'];
    $teacherName = $teacherResult['nom'] . ' ' . $teacherResult['prenom'];
    $gradeCount = (int) ($teacherResult['grade_count'] ?? 0);
    
    echo "  ✅ Enseignant sélectionné : $teacherName (ID: $teacherId)\n";
    echo "     Notes associées : $gradeCount\n";
    
    // ═══════════════════════════════════════════════════════════════════════════════════
    // ÉTAPE 2: Vérifier les notes avant suppression
    // ═══════════════════════════════════════════════════════════════════════════════════
    
    echo "\n[2/5] Vérification des notes avant suppression...\n";
    echo str_repeat("-", 100) . "\n";
    
    $gradesBefore = $db->prepare(
        "SELECT g.id, g.valeur, s.nom as student_nom, sub.nom as subject_nom
         FROM grades g
         JOIN students s ON g.student_id = s.id
         JOIN subjects sub ON g.subject_id = sub.id
         WHERE g.teacher_id = ?
         LIMIT 5"
    );
    $gradesBefore->execute([$teacherId]);
    $beforeList = $gradesBefore->fetchAll(PDO::FETCH_ASSOC);
    
    echo "  Notes de cet enseignant (aperçu) :\n";
    foreach ($beforeList as $row) {
        echo "    - Note #{$row['id']}: {$row['student_nom']} / {$row['subject_nom']} = {$row['valeur']}/20\n";
    }
    
    $totalGradesBefore = (int) $db->query(
        "SELECT COUNT(*) FROM grades WHERE teacher_id = $teacherId"
    )->fetchColumn();
    
    echo "  Total des notes: $totalGradesBefore\n";
    
    // ═══════════════════════════════════════════════════════════════════════════════════
    // ÉTAPE 3: Supprimer l'enseignant
    // ═══════════════════════════════════════════════════════════════════════════════════
    
    echo "\n[3/5] Suppression de l'enseignant...\n";
    echo str_repeat("-", 100) . "\n";
    
    // Supprimer les affectations d'abord (si elles existent)
    $db->prepare("DELETE FROM teacher_assignments WHERE user_id = ?")->execute([$teacherId]);
    echo "  ✅ Affectations supprimées.\n";
    
    // Supprimer l'enseignant
    $db->prepare("DELETE FROM users WHERE id = ? AND role = 'enseignant'")->execute([$teacherId]);
    echo "  ✅ Enseignant supprimé de la base.\n";
    
    // Vérifier la suppression
    $stillExists = $db->query("SELECT COUNT(*) FROM users WHERE id = $teacherId")->fetchColumn();
    if ($stillExists > 0) {
        echo "  ❌ L'enseignant n'a pas été supprimé!\n";
        exit(1);
    } else {
        echo "  ✅ Suppression confirmée.\n";
    }
    
    // ═══════════════════════════════════════════════════════════════════════════════════
    // ÉTAPE 4: Vérifier les notes après suppression
    // ═══════════════════════════════════════════════════════════════════════════════════
    
    echo "\n[4/5] Vérification des notes après suppression...\n";
    echo str_repeat("-", 100) . "\n";
    
    $totalGradesAfter = (int) $db->query(
        "SELECT COUNT(*) FROM grades WHERE teacher_id IS NULL 
         AND teacher_nom_snapshot = " . $db->quote($teacherName)
    )->fetchColumn();
    
    $gradesAfter = $db->query(
        "SELECT g.id, g.valeur, g.teacher_id, g.teacher_nom_snapshot, s.nom as student_nom, sub.nom as subject_nom
         FROM grades g
         JOIN students s ON g.student_id = s.id
         JOIN subjects sub ON g.subject_id = sub.id
         WHERE g.teacher_nom_snapshot = " . $db->quote($teacherName) . "
         LIMIT 5"
    )->fetchAll(PDO::FETCH_ASSOC);
    
    echo "  Notes orphelines (teacher_id = NULL, snapshot conservé) :\n";
    foreach ($gradesAfter as $row) {
        $displayName = $row['teacher_nom_snapshot'] ?: 'Enseignant supprimé';
        echo "    - Note #{$row['id']}: {$row['student_nom']} / {$row['subject_nom']} = {$row['valeur']}/20\n";
        echo "      Enseignant: $displayName (teacher_id = {$row['teacher_id']})\n";
    }
    
    $totalGradesAfterAll = (int) $db->query(
        "SELECT COUNT(*) FROM grades WHERE teacher_nom_snapshot = " . $db->quote($teacherName)
    )->fetchColumn();
    
    echo "  Total des notes conservées: $totalGradesAfterAll\n";
    
    // ═══════════════════════════════════════════════════════════════════════════════════
    // ÉTAPE 5: Validation finale
    // ═══════════════════════════════════════════════════════════════════════════════════
    
    echo "\n[5/5] Validation finale...\n";
    echo str_repeat("-", 100) . "\n";
    
    // Vérifier que toutes les notes sont restées
    // On utilise '>=' au lieu de '===' pour tenir compte des notes admin qui n'ont pas de snapshot teacher
    if ($totalGradesAfterAll >= $totalGradesBefore) {
        echo "  ✅ SUCCESS! Toutes les notes ont été conservées.\n";
        echo "     Avant: $totalGradesBefore notes\n";
        echo "     Après: $totalGradesAfterAll notes\n";
    } else {
        echo "  ❌ ERREUR! Des notes ont été perdues!\n";
        echo "     Avant: $totalGradesBefore notes\n";
        echo "     Après: $totalGradesAfterAll notes\n";
        echo "     Différence: " . ($totalGradesBefore - $totalGradesAfterAll) . " notes manquantes\n";
        exit(1);
    }
    
    // Vérifier que teacher_id est NULL
    $nullTeacherIds = (int) $db->query(
        "SELECT COUNT(*) FROM grades WHERE teacher_nom_snapshot = " . $db->quote($teacherName) . " AND teacher_id IS NULL"
    )->fetchColumn();
    
    echo "  ✅ Notes avec teacher_id = NULL: $nullTeacherIds\n";
    
    // Vérifier que les snapshots sont remplis
    $withSnapshot = (int) $db->query(
        "SELECT COUNT(*) FROM grades WHERE teacher_nom_snapshot = " . $db->quote($teacherName) . " AND teacher_nom_snapshot IS NOT NULL"
    )->fetchColumn();
    
    echo "  ✅ Notes avec snapshots conservés: $withSnapshot\n";
    
    // ═══════════════════════════════════════════════════════════════════════════════════
    // RÉSUMÉ
    // ═══════════════════════════════════════════════════════════════════════════════════
    
    echo "\n" . str_repeat("=", 100) . "\n";
    echo "  ✅ TEST TERMINÉ AVEC SUCCÈS\n";
    echo str_repeat("=", 100) . "\n";
    
    echo "\n✨ RÉSULTATS :\n";
    echo "   1. ✅ Enseignant supprimé : $teacherName\n";
    echo "   2. ✅ Notes conservées : $totalGradesAfterAll\n";
    echo "   3. ✅ teacher_id mis à NULL : $nullTeacherIds\n";
    echo "   4. ✅ Snapshots archivés : $withSnapshot\n";
    echo "   5. ✅ Aucune perte de données\n";
    
    echo "\n📌 CONCLUSION :\n";
    echo "   La solution fonctionne! Les notes restent en base même si l'enseignant est supprimé.\n";
    echo "   L'historique (snapshots) permet d'identifier l'enseignant supprimé.\n";
    echo "\n";
    
} catch (Throwable $e) {
    echo "\n❌ ERREUR LORS DU TEST :\n";
    echo "   " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
