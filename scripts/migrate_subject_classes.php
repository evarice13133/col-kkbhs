<?php
/**
 * MIGRATION: subject_classes pour support multi-années
 * 
 * Objectifs:
 * - Changer la clé primaire de (subject_id, class_id) à (subject_id, class_id, academic_year_id)
 * - Permettre la même matière-classe dans différentes années scolaires
 */

echo "=== MIGRATION: subject_classes pour support multi-années ===\n\n";

try {
    $db = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Étape 1: Sauvegarde des données existantes
    echo "Étape 1: Sauvegarde des données existantes...\n";
    $backupCount = $db->query("SELECT COUNT(*) FROM subject_classes")->fetchColumn();
    echo "✓ Sauvegarde créée avec {$backupCount} enregistrements\n\n";
    
    // Étape 2: Vérifier les doublons potentiels
    echo "Étape 2: Vérification des doublons potentiels...\n";
    $duplicates = $db->query("
        SELECT COUNT(*) as count 
        FROM subject_classes 
        GROUP BY subject_id, class_id, academic_year_id 
        HAVING count > 1
    ")->fetchColumn();
    
    if ($duplicates > 0) {
        echo "⚠ {$duplicates} doublons détectés, suppression...\n";
        $db->exec("
            DELETE sc1 FROM subject_classes sc1
            INNER JOIN subject_classes sc2 
            WHERE sc1.subject_id = sc2.subject_id 
            AND sc1.class_id = sc2.class_id 
            AND sc1.academic_year_id = sc2.academic_year_id 
            AND sc1.subject_id < sc2.subject_id
        ");
        echo "✓ Doublons supprimés\n\n";
    } else {
        echo "✓ Aucun doublon détecté\n\n";
    }
    
    // Étape 3: Supprimer toutes les clés étrangères
    echo "Étape 3: Suppression des clés étrangères...\n";
    $foreignKeys = $db->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'subject_classes' AND CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME != 'PRIMARY'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($foreignKeys as $fk) {
        try {
            $db->exec("ALTER TABLE subject_classes DROP FOREIGN KEY {$fk}");
            echo "  ✓ Clé étrangère {$fk} supprimée\n";
        } catch (PDOException $e) {
            echo "  ⚠ Clé étrangère {$fk} non trouvée ou erreur: " . $e->getMessage() . "\n";
        }
    }
    echo "✓ Clés étrangères supprimées\n\n";
    
    // Étape 4: Supprimer l'ancienne clé primaire
    echo "Étape 4: Suppression de l'ancienne clé primaire...\n";
    try {
        $db->exec("ALTER TABLE subject_classes DROP PRIMARY KEY");
        echo "✓ Ancienne clé primaire supprimée\n\n";
    } catch (PDOException $e) {
        echo "❌ Erreur lors de la suppression de la clé primaire: " . $e->getMessage() . "\n";
        throw $e;
    }
    
    // Étape 5: Ajouter la nouvelle clé primaire
    echo "Étape 5: Ajout de la nouvelle clé primaire (subject_id, class_id, academic_year_id)...\n";
    $db->exec("ALTER TABLE subject_classes ADD PRIMARY KEY (subject_id, class_id, academic_year_id)");
    echo "✓ Nouvelle clé primaire ajoutée\n\n";
    
    // Étape 6: Re-créer les clés étrangères
    echo "Étape 6: Re-création des clés étrangères...\n";
    $db->exec("ALTER TABLE subject_classes ADD CONSTRAINT fk_subject_classes_academic_year FOREIGN KEY (academic_year_id) REFERENCES academic_years (id) ON UPDATE CASCADE");
    $db->exec("ALTER TABLE subject_classes ADD CONSTRAINT subject_classes_ibfk_1 FOREIGN KEY (subject_id) REFERENCES subjects (id) ON DELETE CASCADE");
    $db->exec("ALTER TABLE subject_classes ADD CONSTRAINT subject_classes_ibfk_2 FOREIGN KEY (class_id) REFERENCES classes (id) ON DELETE CASCADE");
    echo "✓ Clés étrangères re-créées\n\n";
    
    // Étape 7: Vérification finale
    echo "Étape 7: Vérification finale de la structure...\n";
    $structure = $db->query("SHOW CREATE TABLE subject_classes")->fetch(PDO::FETCH_ASSOC);
    echo "Structure actuelle:\n";
    echo $structure['Create Table'] . "\n\n";
    
    $currentCount = $db->query("SELECT COUNT(*) FROM subject_classes")->fetchColumn();
    echo "Nombre d'enregistrements après migration: {$currentCount}\n";
    
    if ($currentCount == $backupCount) {
        echo "✓ Aucune donnée perdue\n\n";
    } else {
        echo "⚠ Attention: Le nombre d'enregistrements a changé (avant: {$backupCount}, après: {$currentCount})\n\n";
    }
    
    echo "=== MIGRATION TERMINÉE AVEC SUCCÈS ===\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur lors de la migration: " . $e->getMessage() . "\n";
    exit(1);
}
