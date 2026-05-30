#!/usr/bin/env php
<?php
/**
 * SCRIPT DE MIGRATION - Protection des Notes Contre la Suppression d'Enseignants
 * 
 * Objectif : Convertir ON DELETE CASCADE -> ON DELETE SET NULL + Ajouter des snapshots
 * Cela garantit que TOUTES les notes restent en base, peu importe qui les a saisies.
 * 
 * Usage: php scripts/fix_grades_cascade.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

use App\Core\Database;

define('MIGRATION_NAME', 'fix_grades_cascade');
define('TIMESTAMP', date('Y-m-d H:i:s'));

try {
    $db = Database::getInstance()->getConnection();
    
    echo "\n" . str_repeat("=", 100) . "\n";
    echo "  MIGRATION : Protection des Notes Contre la Suppression d'Enseignants\n";
    echo "  " . TIMESTAMP . "\n";
    echo str_repeat("=", 100) . "\n\n";
    
    // ═══════════════════════════════════════════════════════════════════════════════════
    // ÉTAPE 1 : Ajouter les colonnes snapshot (si elles n'existent pas)
    // ═══════════════════════════════════════════════════════════════════════════════════
    
    echo "[1/5] Ajout des colonnes snapshot (archivage d'historique)...\n";
    echo str_repeat("-", 100) . "\n";
    
    $snapshotColumns = [
        'teacher_nom_snapshot' => "VARCHAR(100) DEFAULT NULL COMMENT 'Nom de l''enseignant au moment de la saisie'",
        'teacher_prenom_snapshot' => "VARCHAR(100) DEFAULT NULL COMMENT 'Prénom de l''enseignant au moment de la saisie'",
        'subject_nom_snapshot' => "VARCHAR(100) DEFAULT NULL COMMENT 'Nom de la matière au moment de la saisie'",
        'created_by_type' => "ENUM('enseignant', 'admin') DEFAULT 'enseignant' COMMENT 'Type de créateur (enseignant ou admin)'",
    ];
    
    foreach ($snapshotColumns as $columnName => $columnDef) {
        $checkColumn = $db->query("SHOW COLUMNS FROM grades LIKE '$columnName'")->rowCount();
        if ($checkColumn == 0) {
            $db->exec("ALTER TABLE grades ADD COLUMN $columnName $columnDef");
            echo "  ✅ Colonne '$columnName' ajoutée.\n";
        } else {
            echo "  ⏭️  Colonne '$columnName' déjà présente.\n";
        }
    }
    
    // ═══════════════════════════════════════════════════════════════════════════════════
    // ÉTAPE 2 : Remplir les snapshots existants avec les données actuelles
    // ═══════════════════════════════════════════════════════════════════════════════════
    
    echo "\n[2/5] Remplissage des snapshots (données existantes)...\n";
    echo str_repeat("-", 100) . "\n";
    
    // Récupérer les notes sans snapshot
    $noSnapshotCount = (int) $db->query(
        "SELECT COUNT(*) FROM grades WHERE teacher_nom_snapshot IS NULL"
    )->fetchColumn();
    
    if ($noSnapshotCount > 0) {
        echo "  Notes à mettre à jour : $noSnapshotCount\n";
        
        // Requête de mise à jour : joindre avec les tables users et subjects
        $updateSql = "
            UPDATE grades g
            LEFT JOIN users u ON g.teacher_id = u.id
            LEFT JOIN subjects s ON g.subject_id = s.id
            SET 
                g.teacher_nom_snapshot = COALESCE(u.nom, 'Supprimé'),
                g.teacher_prenom_snapshot = COALESCE(u.prenom, 'Supprimé'),
                g.subject_nom_snapshot = COALESCE(s.nom, 'Supprimé'),
                g.created_by_type = 'enseignant'
            WHERE g.teacher_nom_snapshot IS NULL
        ";
        
        $db->exec($updateSql);
        $updatedCount = (int) $db->query(
            "SELECT COUNT(*) FROM grades WHERE teacher_nom_snapshot IS NOT NULL"
        )->fetchColumn();
        
        echo "  ✅ $updatedCount notes ont été mises à jour avec les snapshots.\n";
    } else {
        echo "  ✅ Tous les snapshots sont déjà remplis.\n";
    }
    
    // ═══════════════════════════════════════════════════════════════════════════════════
    // ÉTAPE 3 : Permettre NULL pour teacher_id
    // ═══════════════════════════════════════════════════════════════════════════════════
    
    echo "\n[3/5] Modification de la colonne teacher_id (permettre NULL)...\n";
    echo str_repeat("-", 100) . "\n";
    
    // Vérifier si teacher_id est déjà nullable
    $columnInfo = $db->query("SHOW COLUMNS FROM grades WHERE Field = 'teacher_id'")->fetch(PDO::FETCH_ASSOC);
    
    if ($columnInfo['Null'] === 'NO') {
        // Modifier la colonne pour la rendre NULLABLE
        $db->exec("ALTER TABLE grades MODIFY teacher_id INT NULL");
        echo "  ✅ Colonne teacher_id est maintenant NULLABLE.\n";
    } else {
        echo "  ⏭️  Colonne teacher_id est déjà NULLABLE.\n";
    }
    
    // ═══════════════════════════════════════════════════════════════════════════════════
    // ÉTAPE 4 : Modifier les contraintes de clé étrangère
    // ═══════════════════════════════════════════════════════════════════════════════════
    
    echo "\n[4/5] Modification des contraintes de clé étrangère...\n";
    echo str_repeat("-", 100) . "\n";
    
    // Récupérer le nom exact de la contrainte pour teacher_id
    $fkResult = $db->query(
        "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
         WHERE TABLE_NAME = 'grades' AND COLUMN_NAME = 'teacher_id' AND REFERENCED_TABLE_NAME IS NOT NULL"
    )->fetch(PDO::FETCH_ASSOC);
    
    if ($fkResult) {
        $constraintName = $fkResult['CONSTRAINT_NAME'];
        echo "  Contrainte trouvée : $constraintName\n";
        
        // Supprimer la contrainte existante
        $db->exec("ALTER TABLE grades DROP FOREIGN KEY $constraintName");
        echo "  ✅ Contrainte supprimée.\n";
    } else {
        echo "  ⏭️  Aucune contrainte FK trouvée pour teacher_id.\n";
    }
    
    // Ajouter la nouvelle contrainte avec ON DELETE SET NULL
    $db->exec("
        ALTER TABLE grades 
        ADD CONSTRAINT grades_fk_teacher_safe
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE RESTRICT
    ");
    echo "  ✅ Nouvelle contrainte ajoutée : ON DELETE SET NULL\n";
    echo "     -> Les notes resteront en base si l'enseignant est supprimé.\n";
    
    // ═══════════════════════════════════════════════════════════════════════════════════
    // ÉTAPE 5 : Validation et statistiques
    // ═══════════════════════════════════════════════════════════════════════════════════
    
    echo "\n[5/5] Validation et statistiques finales...\n";
    echo str_repeat("-", 100) . "\n";
    
    $stats = [
        'total_grades' => (int) $db->query("SELECT COUNT(*) FROM grades")->fetchColumn(),
        'grades_with_teacher' => (int) $db->query("SELECT COUNT(*) FROM grades WHERE teacher_id IS NOT NULL")->fetchColumn(),
        'grades_without_teacher' => (int) $db->query("SELECT COUNT(*) FROM grades WHERE teacher_id IS NULL")->fetchColumn(),
        'grades_with_snapshot' => (int) $db->query("SELECT COUNT(*) FROM grades WHERE teacher_nom_snapshot IS NOT NULL")->fetchColumn(),
    ];
    
    echo "  📊 Statistiques :\n";
    echo "     Total des notes : " . $stats['total_grades'] . "\n";
    echo "     Notes avec enseignant référencé : " . $stats['grades_with_teacher'] . "\n";
    echo "     Notes orphelines (sans enseignant) : " . $stats['grades_without_teacher'] . "\n";
    echo "     Notes avec snapshot : " . $stats['grades_with_snapshot'] . "\n";
    
    // Vérifier l'intégrité
    $orphaned = $db->query(
        "SELECT COUNT(*) FROM grades WHERE teacher_id NOT IN (SELECT id FROM users WHERE role = 'enseignant')"
    )->fetchColumn();
    echo "\n  🔍 Vérification d'intégrité :\n";
    echo "     Notes avec teacher_id qui n'existe pas : $orphaned\n";
    
    if ($orphaned == 0) {
        echo "     ✅ Toutes les références sont valides.\n";
    } else {
        echo "     ⚠️  $orphaned notes ont des références invalides.\n";
    }
    
    // ═══════════════════════════════════════════════════════════════════════════════════
    // RÉSUMÉ FINAL
    // ═══════════════════════════════════════════════════════════════════════════════════
    
    echo "\n" . str_repeat("=", 100) . "\n";
    echo "  ✅ MIGRATION COMPLÉTÉE AVEC SUCCÈS\n";
    echo str_repeat("=", 100) . "\n";
    
    echo "\n📌 RÉSUMÉ DES CHANGEMENTS :\n";
    echo "   1. ✅ Colonnes snapshot ajoutées (historique d'archivage)\n";
    echo "   2. ✅ Données existantes migrées avec snapshots\n";
    echo "   3. ✅ Contrainte FK modifiée : ON DELETE CASCADE → ON DELETE SET NULL\n";
    echo "   4. ✅ Colonne teacher_id est maintenant NULLABLE\n";
    echo "   5. ✅ Validation et intégrité vérifiées\n";
    
    echo "\n🔐 GARANTIES :\n";
    echo "   • TOUTES les notes restent en base, peu importe qui les a saisies\n";
    echo "   • Si un enseignant est supprimé, ses notes deviennent orphelines (teacher_id = NULL)\n";
    echo "   • Les noms et prénoms de l'enseignant + le nom de la matière sont archivés\n";
    echo "   • L'interface PHP peut afficher : 'Enseignant supprimé' si teacher_id est NULL\n";
    echo "   • Aucune perte de données\n";
    
    echo "\n✨ PROCHAINES ÉTAPES (Backend PHP) :\n";
    echo "   1. Adapter l'affichage des notes pour gérer teacher_id = NULL\n";
    echo "   2. Mettre à jour GradeController::export() pour utiliser les snapshots\n";
    echo "   3. Vérifier TeacherController::delete() pour que les notes ne soient jamais supprimées\n";
    echo "\n";
    
} catch (Throwable $e) {
    echo "\n❌ ERREUR LORS DE LA MIGRATION :\n";
    echo "   " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
