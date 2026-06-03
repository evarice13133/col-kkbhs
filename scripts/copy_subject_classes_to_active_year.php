<?php
/**
 * Copie les subject_classes d'une année source vers l'année active
 * Utile pour initialiser une nouvelle année scolaire
 */

echo "=== COPIE DES SUBJECT_CLASSES VERS L'ANNÉE ACTIVE ===\n\n";

try {
    $db = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer l'année active
    $activeYear = $db->query("SELECT id, nom FROM academic_years WHERE is_active = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if (!$activeYear) {
        echo "❌ Aucune année active trouvée\n";
        exit(1);
    }
    
    $activeYearId = $activeYear['id'];
    $activeYearName = $activeYear['nom'];
    
    echo "Année active: {$activeYearName} (ID: {$activeYearId})\n\n";
    
    // Vérifier si des subject_classes existent déjà pour l'année active
    $existingCount = $db->query("SELECT COUNT(*) FROM subject_classes WHERE academic_year_id = {$activeYearId}")->fetchColumn();
    if ($existingCount > 0) {
        echo "⚠ {$existingCount} subject_classes existent déjà pour l'année active\n";
        echo "Voulez-vous les supprimer avant de copier? (O/N)\n";
        exit(1);
    }
    
    // Récupérer l'année la plus récente avec des subject_classes
    $sourceYear = $db->query("
        SELECT sc.academic_year_id, ay.nom 
        FROM subject_classes sc 
        JOIN academic_years ay ON sc.academic_year_id = ay.id 
        GROUP BY sc.academic_year_id, ay.nom 
        ORDER BY sc.academic_year_id DESC 
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);
    
    if (!$sourceYear) {
        echo "❌ Aucune subject_classes trouvée dans aucune année\n";
        exit(1);
    }
    
    $sourceYearId = $sourceYear['academic_year_id'];
    $sourceYearName = $sourceYear['nom'];
    
    echo "Année source: {$sourceYearName} (ID: {$sourceYearId})\n\n";
    
    // Copier les subject_classes
    echo "Copie des subject_classes...\n";
    $db->exec("
        INSERT INTO subject_classes (subject_id, class_id, academic_year_id)
        SELECT subject_id, class_id, {$activeYearId}
        FROM subject_classes
        WHERE academic_year_id = {$sourceYearId}
    ");
    
    $copiedCount = $db->query("SELECT COUNT(*) FROM subject_classes WHERE academic_year_id = {$activeYearId}")->fetchColumn();
    echo "✓ {$copiedCount} subject_classes copiées\n\n";
    
    echo "=== COPIE TERMINÉE AVEC SUCCÈS ===\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
