<?php
/**
 * Script de préchargement des classes de Maternelle et Primaire
 * 
 * Ce script précharge l'ensemble des classes officielles de la Maternelle et du Primaire
 * pour les sections Francophone, Anglophone et Bilingue.
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

    echo "=== DÉBUT DU PRÉCHARGEMENT DES CLASSES ===\n\n";

    // Début de la transaction
    $pdo->beginTransaction();

    // 1. Assurer l'existence de la section 'Bilingue'
    $stmt = $pdo->prepare("SELECT id FROM sections WHERE LOWER(nom) = LOWER(?)");
    $stmt->execute(['Bilingue']);
    $bilingueSectionId = $stmt->fetchColumn();

    if (!$bilingueSectionId) {
        $ins = $pdo->prepare("INSERT INTO sections (nom) VALUES (?)");
        $ins->execute(['Bilingue']);
        $bilingueSectionId = (int)$pdo->lastInsertId();
        echo "[INFO] Section 'Bilingue' créée avec l'ID {$bilingueSectionId}.\n";
    } else {
        $bilingueSectionId = (int)$bilingueSectionId;
        echo "[INFO] Section 'Bilingue' déjà existante avec l'ID {$bilingueSectionId}.\n";
    }

    // 2. Assurer l'existence des cycles
    $cyclesToEnsure = ['Cycle Maternel', 'Cycle Primaire'];
    $cycleIds = [];

    foreach ($cyclesToEnsure as $cycleNom) {
        $stmt = $pdo->prepare("SELECT id FROM cycles WHERE LOWER(nom) = LOWER(?)");
        $stmt->execute([$cycleNom]);
        $cycleId = $stmt->fetchColumn();

        if (!$cycleId) {
            $ins = $pdo->prepare("INSERT INTO cycles (nom) VALUES (?)");
            $ins->execute([$cycleNom]);
            $cycleId = (int)$pdo->lastInsertId();
            echo "[INFO] Cycle '{$cycleNom}' créé avec l'ID {$cycleId}.\n";
        } else {
            $cycleId = (int)$cycleId;
            echo "[INFO] Cycle '{$cycleNom}' déjà existant avec l'ID {$cycleId}.\n";
        }
        $cycleIds[$cycleNom] = $cycleId;
    }

    // 3. Récupération des autres ID de référence
    // Sections
    $stmt = $pdo->prepare("SELECT id FROM sections WHERE LOWER(nom) = LOWER(?)");
    $stmt->execute(['Francophone']);
    $francophoneSectionId = (int)$stmt->fetchColumn();

    $stmt->execute(['Anglophone']);
    $anglophoneSectionId = (int)$stmt->fetchColumn();

    if (!$francophoneSectionId || !$anglophoneSectionId) {
        throw new RuntimeException("Erreur : Impossible de récupérer les ID des sections de base (Francophone/Anglophone).");
    }

    // Teaching Types
    $stmt = $pdo->prepare("SELECT id FROM teaching_types WHERE code = ?");
    $stmt->execute(['MAT']);
    $maternelleTeachingTypeId = (int)$stmt->fetchColumn();

    $stmt->execute(['PRI']);
    $primaireTeachingTypeId = (int)$stmt->fetchColumn();

    if (!$maternelleTeachingTypeId || !$primaireTeachingTypeId) {
        throw new RuntimeException("Erreur : Impossible de récupérer les ID des types d'enseignement (MAT/PRI).");
    }

    // Departments
    $stmt = $pdo->prepare("SELECT id FROM departments WHERE code = ?");
    $stmt->execute(['MAT']);
    $maternelleDepartmentId = (int)$stmt->fetchColumn();

    $stmt->execute(['PRIM']);
    $primaireDepartmentId = (int)$stmt->fetchColumn();

    if (!$maternelleDepartmentId || !$primaireDepartmentId) {
        throw new RuntimeException("Erreur : Impossible de récupérer les ID des départements de base (MAT/PRIM).");
    }

    // 4. Définition de la liste des classes avec leurs relations
    $classesList = [
        // === MATERNELLE FRANCOPHONE ===
        ['nom' => 'Petite Section', 'section_id' => $francophoneSectionId, 'teaching_type_id' => $maternelleTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Maternel'], 'department_id' => $maternelleDepartmentId],
        ['nom' => 'Moyenne Section', 'section_id' => $francophoneSectionId, 'teaching_type_id' => $maternelleTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Maternel'], 'department_id' => $maternelleDepartmentId],
        ['nom' => 'Grande Section', 'section_id' => $francophoneSectionId, 'teaching_type_id' => $maternelleTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Maternel'], 'department_id' => $maternelleDepartmentId],

        // === MATERNELLE ANGLOPHONE ===
        ['nom' => 'Nursery 1', 'section_id' => $anglophoneSectionId, 'teaching_type_id' => $maternelleTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Maternel'], 'department_id' => $maternelleDepartmentId],
        ['nom' => 'Nursery 2', 'section_id' => $anglophoneSectionId, 'teaching_type_id' => $maternelleTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Maternel'], 'department_id' => $maternelleDepartmentId],
        ['nom' => 'Nursery 3', 'section_id' => $anglophoneSectionId, 'teaching_type_id' => $maternelleTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Maternel'], 'department_id' => $maternelleDepartmentId],

        // === MATERNELLE BILINGUE ===
        ['nom' => 'PS Bilingue', 'section_id' => $bilingueSectionId, 'teaching_type_id' => $maternelleTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Maternel'], 'department_id' => $maternelleDepartmentId],
        ['nom' => 'MS Bilingue', 'section_id' => $bilingueSectionId, 'teaching_type_id' => $maternelleTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Maternel'], 'department_id' => $maternelleDepartmentId],
        ['nom' => 'GS Bilingue', 'section_id' => $bilingueSectionId, 'teaching_type_id' => $maternelleTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Maternel'], 'department_id' => $maternelleDepartmentId],

        // === PRIMAIRE FRANCOPHONE ===
        ['nom' => 'SIL', 'section_id' => $francophoneSectionId, 'teaching_type_id' => $primaireTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Primaire'], 'department_id' => $primaireDepartmentId],
        ['nom' => 'CP', 'section_id' => $francophoneSectionId, 'teaching_type_id' => $primaireTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Primaire'], 'department_id' => $primaireDepartmentId],
        ['nom' => 'CE1', 'section_id' => $francophoneSectionId, 'teaching_type_id' => $primaireTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Primaire'], 'department_id' => $primaireDepartmentId],
        ['nom' => 'CE2', 'section_id' => $francophoneSectionId, 'teaching_type_id' => $primaireTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Primaire'], 'department_id' => $primaireDepartmentId],
        ['nom' => 'CM1', 'section_id' => $francophoneSectionId, 'teaching_type_id' => $primaireTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Primaire'], 'department_id' => $primaireDepartmentId],
        ['nom' => 'CM2', 'section_id' => $francophoneSectionId, 'teaching_type_id' => $primaireTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Primaire'], 'department_id' => $primaireDepartmentId],

        // === PRIMAIRE ANGLOPHONE ===
        ['nom' => 'Class 1', 'section_id' => $anglophoneSectionId, 'teaching_type_id' => $primaireTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Primaire'], 'department_id' => $primaireDepartmentId],
        ['nom' => 'Class 2', 'section_id' => $anglophoneSectionId, 'teaching_type_id' => $primaireTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Primaire'], 'department_id' => $primaireDepartmentId],
        ['nom' => 'Class 3', 'section_id' => $anglophoneSectionId, 'teaching_type_id' => $primaireTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Primaire'], 'department_id' => $primaireDepartmentId],
        ['nom' => 'Class 4', 'section_id' => $anglophoneSectionId, 'teaching_type_id' => $primaireTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Primaire'], 'department_id' => $primaireDepartmentId],
        ['nom' => 'Class 5', 'section_id' => $anglophoneSectionId, 'teaching_type_id' => $primaireTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Primaire'], 'department_id' => $primaireDepartmentId],
        ['nom' => 'Class 6', 'section_id' => $anglophoneSectionId, 'teaching_type_id' => $primaireTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Primaire'], 'department_id' => $primaireDepartmentId],

        // === PRIMAIRE BILINGUE ===
        ['nom' => 'SIL Bilingue', 'section_id' => $bilingueSectionId, 'teaching_type_id' => $primaireTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Primaire'], 'department_id' => $primaireDepartmentId],
        ['nom' => 'CP Bilingue', 'section_id' => $bilingueSectionId, 'teaching_type_id' => $primaireTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Primaire'], 'department_id' => $primaireDepartmentId],
        ['nom' => 'CE1 Bilingue', 'section_id' => $bilingueSectionId, 'teaching_type_id' => $primaireTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Primaire'], 'department_id' => $primaireDepartmentId],
        ['nom' => 'CE2 Bilingue', 'section_id' => $bilingueSectionId, 'teaching_type_id' => $primaireTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Primaire'], 'department_id' => $primaireDepartmentId],
        ['nom' => 'CM1 Bilingue', 'section_id' => $bilingueSectionId, 'teaching_type_id' => $primaireTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Primaire'], 'department_id' => $primaireDepartmentId],
        ['nom' => 'CM2 Bilingue', 'section_id' => $bilingueSectionId, 'teaching_type_id' => $primaireTeachingTypeId, 'cycle_id' => $cycleIds['Cycle Primaire'], 'department_id' => $primaireDepartmentId],
    ];

    $insertCount = 0;
    $updateCount = 0;

    $stmtCheck = $pdo->prepare("SELECT id, nom, cycle_id, section_id, department_id, teaching_type_id FROM classes WHERE LOWER(nom) = LOWER(?)");
    $stmtInsert = $pdo->prepare("INSERT INTO classes (nom, cycle_id, section_id, department_id, teaching_type_id) VALUES (?, ?, ?, ?, ?)");
    $stmtUpdate = $pdo->prepare("UPDATE classes SET cycle_id = ?, section_id = ?, department_id = ?, teaching_type_id = ? WHERE id = ?");

    foreach ($classesList as $c) {
        $stmtCheck->execute([$c['nom']]);
        $existing = $stmtCheck->fetch();

        if ($existing) {
            // Vérifier si des modifications sont nécessaires
            if (
                (int)$existing['cycle_id'] !== $c['cycle_id'] ||
                (int)$existing['section_id'] !== $c['section_id'] ||
                (int)$existing['department_id'] !== $c['department_id'] ||
                (int)$existing['teaching_type_id'] !== $c['teaching_type_id']
            ) {
                $stmtUpdate->execute([$c['cycle_id'], $c['section_id'], $c['department_id'], $c['teaching_type_id'], $existing['id']]);
                $updateCount++;
                echo "[UPDATE] Classe '{$c['nom']}' mise à jour (Cycle, Section, Département, ou Teaching Type).\n";
            } else {
                echo "[SKIP] Classe '{$c['nom']}' déjà existante et conforme.\n";
            }
        } else {
            $stmtInsert->execute([$c['nom'], $c['cycle_id'], $c['section_id'], $c['department_id'], $c['teaching_type_id']]);
            $insertCount++;
            echo "[INSERT] Classe '{$c['nom']}' créée avec succès.\n";
        }
    }

    // Validation de la transaction
    $pdo->commit();

    echo "\n=== SYNCHRONISATION DES ÉLÈVES AVEC LES TYPES D'ENSEIGNEMENT ===\n";
    // Mettre à jour les types d'enseignement des élèves en fonction de la classe pour synchroniser le tout
    $pdo->exec("
        UPDATE students st
        JOIN classes c ON c.id = st.class_id
        SET st.teaching_type_id = c.teaching_type_id
        WHERE c.teaching_type_id IN ({$maternelleTeachingTypeId}, {$primaireTeachingTypeId})
    ");
    echo "[INFO] Synchronisation des types d'enseignement élèves effectuée avec succès.\n";

    echo "\n=== OPÉRATION TERMINÉE AVEC SUCCÈS ===\n";
    echo "Classes insérées : {$insertCount}\n";
    echo "Classes mises à jour : {$updateCount}\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n[ERREUR FATALE] La migration a échoué : " . $e->getMessage() . "\n";
    exit(1);
}
