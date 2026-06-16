<?php
/**
 * Script de préchargement des matières et coefficients du Primaire
 * 
 * Ce script insère les matières officielles du Primaire (Cameroun) avec leurs coefficients
 * et les lie aux classes francophones, anglophones et bilingues correspondantes.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    echo "=== DÉBUT DU PRÉCHARGEMENT DES MATIÈRES DU PRIMAIRE ===\n\n";

    // 1. Récupération de l'année académique active
    $yearStmt = $pdo->query("SELECT id, nom FROM academic_years WHERE is_active = 1 LIMIT 1");
    $activeYear = $yearStmt->fetch();
    if (!$activeYear) {
        throw new RuntimeException("Aucune année académique active trouvée.");
    }
    $academicYearId = (int)$activeYear['id'];
    echo "[INFO] Année académique active : {$activeYear['nom']} (ID: {$academicYearId})\n";

    // 2. Récupération du type d'enseignement 'Primaire' et du département 'PRIMAIRE'
    $ttStmt = $pdo->prepare("SELECT id FROM teaching_types WHERE code = ?");
    $ttStmt->execute(['PRI']);
    $teachingTypeId = (int)$ttStmt->fetchColumn();

    $deptStmt = $pdo->prepare("SELECT id FROM departments WHERE code = ?");
    $deptStmt->execute(['PRIM']);
    $departmentId = (int)$deptStmt->fetchColumn();

    if (!$teachingTypeId || !$departmentId) {
        throw new RuntimeException("Erreur : Le type d'enseignement 'PRI' ou le département 'PRIM' est manquant.");
    }
    echo "[INFO] ID Type d'Enseignement : {$teachingTypeId}, ID Département : {$departmentId}\n";

    // 3. Récupération de toutes les classes du Primaire pour le mapping
    $classStmt = $pdo->prepare("SELECT id, nom FROM classes WHERE teaching_type_id = ?");
    $classStmt->execute([$teachingTypeId]);
    $classes = $classStmt->fetchAll();
    
    $classesByName = [];
    foreach ($classes as $cl) {
        $classesByName[strtolower(trim($cl['nom']))] = (int)$cl['id'];
    }

    echo "[INFO] Classes du Primaire trouvées : " . count($classesByName) . "\n";

    // Début de la transaction
    $pdo->beginTransaction();

    // 4. Définition des matières par niveau et par langue

    // --- FILIÈRE FRANCOPHONE & BILINGUE ---
    $frLevelAClasses = ['sil', 'cp', 'sil bilingue', 'cp bilingue'];
    $frLevelBClasses = ['ce1', 'ce2', 'cm1', 'cm2', 'ce1 bilingue', 'ce2 bilingue', 'cm1 bilingue', 'cm2 bilingue'];

    $frSubjectsLevelA = [
        ['nom' => 'Langue Française', 'coefficient' => 4, 'groupe' => 'Groupe 1', 'classes' => $frLevelAClasses],
        ['nom' => 'Mathématiques', 'coefficient' => 4, 'groupe' => 'Groupe 1', 'classes' => $frLevelAClasses],
        ['nom' => 'English Language', 'coefficient' => 2, 'groupe' => 'Groupe 1', 'classes' => $frLevelAClasses],
        ['nom' => 'Sciences et Technologie', 'coefficient' => 2, 'groupe' => 'Groupe 2', 'classes' => $frLevelAClasses],
        ['nom' => 'Éducation Civique et Morale', 'coefficient' => 2, 'groupe' => 'Groupe 2', 'classes' => $frLevelAClasses],
        ['nom' => 'Langues et Cultures Nationales', 'coefficient' => 1, 'groupe' => 'Groupe 2', 'classes' => $frLevelAClasses],
        ['nom' => 'Informatique', 'coefficient' => 1, 'groupe' => 'Groupe 2', 'classes' => $frLevelAClasses],
        ['nom' => 'Éducation Artistique', 'coefficient' => 1, 'groupe' => 'Groupe 3', 'classes' => $frLevelAClasses],
        ['nom' => 'Éducation Physique et Sportive', 'coefficient' => 1, 'groupe' => 'Groupe 3', 'classes' => $frLevelAClasses],
        ['nom' => 'Activités Pratiques', 'coefficient' => 1, 'groupe' => 'Groupe 3', 'classes' => $frLevelAClasses],
    ];

    $frSubjectsLevelB = [
        ['nom' => 'Langue Française', 'coefficient' => 5, 'groupe' => 'Groupe 1', 'classes' => $frLevelBClasses],
        ['nom' => 'Mathématiques', 'coefficient' => 5, 'groupe' => 'Groupe 1', 'classes' => $frLevelBClasses],
        ['nom' => 'English Language', 'coefficient' => 3, 'groupe' => 'Groupe 1', 'classes' => $frLevelBClasses],
        ['nom' => 'Sciences et Technologie', 'coefficient' => 3, 'groupe' => 'Groupe 2', 'classes' => $frLevelBClasses],
        ['nom' => 'Histoire et Géographie', 'coefficient' => 2, 'groupe' => 'Groupe 2', 'classes' => $frLevelBClasses],
        ['nom' => 'Éducation Civique et Morale', 'coefficient' => 2, 'groupe' => 'Groupe 2', 'classes' => $frLevelBClasses],
        ['nom' => 'Langues et Cultures Nationales', 'coefficient' => 1, 'groupe' => 'Groupe 2', 'classes' => $frLevelBClasses],
        ['nom' => 'Informatique', 'coefficient' => 1, 'groupe' => 'Groupe 2', 'classes' => $frLevelBClasses],
        ['nom' => 'Éducation Artistique', 'coefficient' => 1, 'groupe' => 'Groupe 3', 'classes' => $frLevelBClasses],
        ['nom' => 'Éducation Physique et Sportive', 'coefficient' => 2, 'groupe' => 'Groupe 3', 'classes' => $frLevelBClasses],
        ['nom' => 'Activités Pratiques', 'coefficient' => 1, 'groupe' => 'Groupe 3', 'classes' => $frLevelBClasses],
    ];

    // --- FILIÈRE ANGLOPHONE ---
    $enLevelAClasses = ['class 1', 'class 2'];
    $enLevelBClasses = ['class 3', 'class 4', 'class 5', 'class 6'];

    $enSubjectsLevelA = [
        ['nom' => 'English Language', 'coefficient' => 4, 'groupe' => 'Groupe 1', 'classes' => $enLevelAClasses],
        ['nom' => 'Mathematics', 'coefficient' => 4, 'groupe' => 'Groupe 1', 'classes' => $enLevelAClasses],
        ['nom' => 'French Language', 'coefficient' => 2, 'groupe' => 'Groupe 1', 'classes' => $enLevelAClasses],
        ['nom' => 'Science and Technology', 'coefficient' => 2, 'groupe' => 'Groupe 2', 'classes' => $enLevelAClasses],
        ['nom' => 'Social Studies', 'coefficient' => 2, 'groupe' => 'Groupe 2', 'classes' => $enLevelAClasses],
        ['nom' => 'Citizenship / Moral Education', 'coefficient' => 2, 'groupe' => 'Groupe 2', 'classes' => $enLevelAClasses],
        ['nom' => 'National Languages and Cultures', 'coefficient' => 1, 'groupe' => 'Groupe 2', 'classes' => $enLevelAClasses],
        ['nom' => 'Computer Science', 'coefficient' => 1, 'groupe' => 'Groupe 2', 'classes' => $enLevelAClasses],
        ['nom' => 'Arts and Craft', 'coefficient' => 1, 'groupe' => 'Groupe 3', 'classes' => $enLevelAClasses],
        ['nom' => 'Physical Education', 'coefficient' => 1, 'groupe' => 'Groupe 3', 'classes' => $enLevelAClasses],
        ['nom' => 'Vocational Studies', 'coefficient' => 1, 'groupe' => 'Groupe 3', 'classes' => $enLevelAClasses],
    ];

    $enSubjectsLevelB = [
        ['nom' => 'English Language', 'coefficient' => 5, 'groupe' => 'Groupe 1', 'classes' => $enLevelBClasses],
        ['nom' => 'Mathematics', 'coefficient' => 5, 'groupe' => 'Groupe 1', 'classes' => $enLevelBClasses],
        ['nom' => 'French Language', 'coefficient' => 3, 'groupe' => 'Groupe 1', 'classes' => $enLevelBClasses],
        ['nom' => 'Science and Technology', 'coefficient' => 3, 'groupe' => 'Groupe 2', 'classes' => $enLevelBClasses],
        ['nom' => 'Social Studies', 'coefficient' => 3, 'groupe' => 'Groupe 2', 'classes' => $enLevelBClasses],
        ['nom' => 'Citizenship / Moral Education', 'coefficient' => 2, 'groupe' => 'Groupe 2', 'classes' => $enLevelBClasses],
        ['nom' => 'National Languages and Cultures', 'coefficient' => 1, 'groupe' => 'Groupe 2', 'classes' => $enLevelBClasses],
        ['nom' => 'Computer Science', 'coefficient' => 1, 'groupe' => 'Groupe 2', 'classes' => $enLevelBClasses],
        ['nom' => 'Arts and Craft', 'coefficient' => 1, 'groupe' => 'Groupe 3', 'classes' => $enLevelBClasses],
        ['nom' => 'Physical Education', 'coefficient' => 2, 'groupe' => 'Groupe 3', 'classes' => $enLevelBClasses],
        ['nom' => 'Vocational Studies', 'coefficient' => 1, 'groupe' => 'Groupe 3', 'classes' => $enLevelBClasses],
    ];

    $allMatiereGroups = [
        $frSubjectsLevelA,
        $frSubjectsLevelB,
        $enSubjectsLevelA,
        $enSubjectsLevelB
    ];

    $subjectsInserted = 0;
    $subjectsLinked = 0;

    $checkSubject = $pdo->prepare("
        SELECT id FROM subjects 
        WHERE LOWER(nom) = LOWER(?) 
          AND coefficient = ? 
          AND groupe = ? 
          AND teaching_type_id = ?
    ");
    $insertSubject = $pdo->prepare("
        INSERT INTO subjects (nom, coefficient, groupe, teaching_type_id, department_id) 
        VALUES (?, ?, ?, ?, ?)
    ");

    $checkLink = $pdo->prepare("
        SELECT 1 FROM subject_classes 
        WHERE subject_id = ? AND class_id = ? AND academic_year_id = ?
    ");
    $insertLink = $pdo->prepare("
        INSERT INTO subject_classes (subject_id, class_id, academic_year_id) 
        VALUES (?, ?, ?)
    ");

    foreach ($allMatiereGroups as $group) {
        foreach ($group as $item) {
            // A. Insérer ou Récupérer la matière
            $checkSubject->execute([$item['nom'], $item['coefficient'], $item['groupe'], $teachingTypeId]);
            $subjectId = $checkSubject->fetchColumn();

            if (!$subjectId) {
                $insertSubject->execute([$item['nom'], $item['coefficient'], $item['groupe'], $teachingTypeId, $departmentId]);
                $subjectId = (int)$pdo->lastInsertId();
                $subjectsInserted++;
                echo "[INSERT MATIÈRE] '{$item['nom']}' (Coeff: {$item['coefficient']}, {$item['groupe']}) créée avec l'ID {$subjectId}.\n";
            } else {
                $subjectId = (int)$subjectId;
                echo "[SKIP MATIÈRE] '{$item['nom']}' (Coeff: {$item['coefficient']}, {$item['groupe']}) déjà existante avec l'ID {$subjectId}.\n";
            }

            // B. Lier la matière aux classes spécifiées
            foreach ($item['classes'] as $className) {
                if (!isset($classesByName[$className])) {
                    echo "[WARNING] Classe '{$className}' introuvable dans la base, liaison ignorée.\n";
                    continue;
                }
                $classId = $classesByName[$className];

                $checkLink->execute([$subjectId, $classId, $academicYearId]);
                $hasLink = $checkLink->fetchColumn();

                if (!$hasLink) {
                    $insertLink->execute([$subjectId, $classId, $academicYearId]);
                    $subjectsLinked++;
                    echo "  [LIEN] Matière {$subjectId} liée à la classe '{$className}' (ID: {$classId}) pour l'année {$academicYearId}.\n";
                }
            }
        }
    }

    $pdo->commit();
    echo "\n=== MIGRATION TERMINÉE AVEC SUCCÈS ===\n";
    echo "Matières créées : {$subjectsInserted}\n";
    echo "Liaisons de classes créées : {$subjectsLinked}\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n[ERREUR FATALE] Échec de la migration : " . $e->getMessage() . "\n";
    exit(1);
}
