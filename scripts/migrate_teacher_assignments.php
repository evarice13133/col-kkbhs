<?php
/**
 * MIGRATION: Correction de la structure de teacher_assignments pour support multi-années
 * 
 * Problèmes corrigés:
 * 1. Clé primaire incorrecte (user_id, subject_id, class_id) empêche les affectations multi-années
 * 2. Contrainte unique incorrecte (class_id, subject_id) empêche différents enseignants dans différentes années
 * 
 * Solution:
 * - Nouvelle clé primaire: (user_id, subject_id, class_id, academic_year_id)
 * - Nouvelle contrainte unique: (user_id, subject_id, class_id, academic_year_id)
 */

echo "=== MIGRATION: teacher_assignments pour support multi-années ===\n\n";

try {
    $db = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Étape 1: Sauvegarde des données existantes
    echo "Étape 1: Sauvegarde des données existantes...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS teacher_assignments_backup AS SELECT * FROM teacher_assignments");
    $backupCount = $db->query("SELECT COUNT(*) FROM teacher_assignments_backup")->fetchColumn();
    echo "✓ Sauvegarde créée avec {$backupCount} enregistrements\n\n";
    
    // Étape 2: Vérifier s'il y a des doublons potentiels
    echo "Étape 2: Vérification des doublons potentiels...\n";
    $duplicateCheck = $db->query("
        SELECT user_id, subject_id, class_id, academic_year_id, COUNT(*) as cnt
        FROM teacher_assignments
        GROUP BY user_id, subject_id, class_id, academic_year_id
        HAVING cnt > 1
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($duplicateCheck)) {
        echo "⚠ Attention: Doublons détectés:\n";
        foreach ($duplicateCheck as $dup) {
            echo "  - user_id: {$dup['user_id']}, subject_id: {$dup['subject_id']}, class_id: {$dup['class_id']}, academic_year_id: {$dup['academic_year_id']}, count: {$dup['cnt']}\n";
        }
        echo "Ces doublons seront supprimés automatiquement.\n\n";
        
        // Supprimer les doublons (garder le plus récent)
        echo "Suppression des doublons...\n";
        $db->exec("
            DELETE ta1 FROM teacher_assignments ta1
            INNER JOIN teacher_assignments ta2 
            WHERE ta1.user_id = ta2.user_id 
            AND ta1.subject_id = ta2.subject_id 
            AND ta1.class_id = ta2.class_id 
            AND ta1.academic_year_id = ta2.academic_year_id 
            AND ta1.id < ta2.id
        ");
        echo "✓ Doublons supprimés\n\n";
    } else {
        echo "✓ Aucun doublon détecté\n\n";
    }
    
    // Étape 3: Recréer la table avec la structure correcte
    echo "Étape 3: Recréation de la table avec la nouvelle structure...\n";
    
    // Créer la nouvelle table
    $db->exec("
        CREATE TABLE teacher_assignments_new (
            user_id int(11) NOT NULL,
            subject_id int(11) NOT NULL,
            class_id int(11) NOT NULL,
            academic_year_id int(11) NOT NULL DEFAULT 2,
            PRIMARY KEY (user_id, subject_id, class_id, academic_year_id),
            UNIQUE KEY idx_unique_year_assignment (user_id, subject_id, class_id, academic_year_id),
            KEY subject_id (subject_id),
            KEY idx_ta_user (user_id),
            KEY idx_teacher_assignments_academic_year (academic_year_id),
            CONSTRAINT fk_teacher_assignments_academic_year_new FOREIGN KEY (academic_year_id) REFERENCES academic_years (id) ON UPDATE CASCADE,
            CONSTRAINT teacher_assignments_ibfk_1_new FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
            CONSTRAINT teacher_assignments_ibfk_2_new FOREIGN KEY (subject_id) REFERENCES subjects (id) ON DELETE CASCADE,
            CONSTRAINT teacher_assignments_ibfk_3_new FOREIGN KEY (class_id) REFERENCES classes (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    echo "✓ Nouvelle table créée\n\n";
    
    // Copier les données
    echo "Étape 4: Copie des données...\n";
    $db->exec("INSERT INTO teacher_assignments_new SELECT * FROM teacher_assignments");
    $copiedCount = $db->query("SELECT COUNT(*) FROM teacher_assignments_new")->fetchColumn();
    echo "✓ {$copiedCount} enregistrements copiés\n\n";
    
    // Supprimer l'ancienne table
    echo "Étape 5: Suppression de l'ancienne table...\n";
    $db->exec("DROP TABLE teacher_assignments");
    echo "✓ Ancienne table supprimée\n\n";
    
    // Renommer la nouvelle table
    echo "Étape 6: Renommage de la nouvelle table...\n";
    $db->exec("RENAME TABLE teacher_assignments_new TO teacher_assignments");
    echo "✓ Table renommée\n\n";
    
    // Étape 9: Vérification finale
    echo "Étape 9: Vérification finale de la structure...\n";
    $structure = $db->query("SHOW CREATE TABLE teacher_assignments")->fetch(PDO::FETCH_ASSOC);
    echo "Structure actuelle:\n";
    echo $structure['Create Table'] . "\n\n";
    
    $currentCount = $db->query("SELECT COUNT(*) FROM teacher_assignments")->fetchColumn();
    echo "Nombre d'enregistrements après migration: {$currentCount}\n";
    
    if ($currentCount == $backupCount) {
        echo "✓ Aucune donnée perdue\n\n";
    } else {
        echo "⚠ Attention: Le nombre d'enregistrements a changé (avant: {$backupCount}, après: {$currentCount})\n\n";
    }
    
    echo "=== MIGRATION TERMINÉE AVEC SUCCÈS ===\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur lors de la migration: " . $e->getMessage() . "\n";
    echo "Rollback en cours...\n";
    
    try {
        // Tentative de restauration depuis la sauvegarde
        $db->exec("DROP TABLE teacher_assignments");
        $db->exec("RENAME TABLE teacher_assignments_backup TO teacher_assignments");
        echo "✓ Restauration effectuée depuis la sauvegarde\n";
    } catch (PDOException $restoreError) {
        echo "❌ Erreur lors de la restauration: " . $restoreError->getMessage() . "\n";
        echo "⚠ Les données sont toujours disponibles dans teacher_assignments_backup\n";
    }
    
    exit(1);
}
