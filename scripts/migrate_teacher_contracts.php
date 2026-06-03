<?php
/**
 * MIGRATION: Création de la table teacher_contracts pour suivi historique professionnel
 * 
 * Objectifs:
 * - Suivre l'historique des statuts et contrats des enseignants
 * - Permettre la gestion des départs et retours
 * - Permettre les changements de statut (permanent → vacataire, etc.)
 */

echo "=== MIGRATION: Création de teacher_contracts ===\n\n";

try {
    $db = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Étape 1: Vérifier si la table existe déjà
    echo "Étape 1: Vérification de l'existence de la table...\n";
    $tableExists = $db->query("SHOW TABLES LIKE 'teacher_contracts'")->fetch();
    if ($tableExists) {
        echo "⚠ La table teacher_contracts existe déjà\n";
        echo "Voulez-vous la recréer? (Cela supprimera les données existantes)\n";
        echo "Pour continuer, supprimez d'abord la table manuellement ou modifiez ce script.\n";
        exit(1);
    }
    echo "✓ Table n'existe pas, procédant à la création\n\n";
    
    // Étape 2: Créer la table teacher_contracts
    echo "Étape 2: Création de la table teacher_contracts...\n";
    $db->exec("
        CREATE TABLE teacher_contracts (
            id int(11) NOT NULL AUTO_INCREMENT,
            teacher_id int(11) NOT NULL,
            academic_year_id int(11) NOT NULL,
            contract_type enum('PERMANENT','VACATAIRE','CONTRACTUEL','STAGIAIRE','SUSPENDU','RETRAITE','INACTIF') NOT NULL DEFAULT 'VACATAIRE',
            start_date date DEFAULT NULL,
            end_date date DEFAULT NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            notes text DEFAULT NULL,
            created_at timestamp NOT NULL DEFAULT current_timestamp(),
            updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (id),
            UNIQUE KEY idx_teacher_year_contract (teacher_id, academic_year_id),
            KEY academic_year_id (academic_year_id),
            CONSTRAINT fk_teacher_contracts_teacher FOREIGN KEY (teacher_id) REFERENCES users (id) ON DELETE CASCADE,
            CONSTRAINT fk_teacher_contracts_academic_year FOREIGN KEY (academic_year_id) REFERENCES academic_years (id) ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    echo "✓ Table teacher_contracts créée\n\n";
    
    // Étape 3: Créer des contrats par défaut pour les enseignants actifs
    echo "Étape 3: Création de contrats par défaut pour les enseignants actifs...\n";
    
    // Récupérer les enseignants avec des affectations
    $teachersWithAssignments = $db->query("
        SELECT DISTINCT ta.user_id as teacher_id, ta.academic_year_id
        FROM teacher_assignments ta
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $contractsCreated = 0;
    foreach ($teachersWithAssignments as $teacher) {
        try {
            $stmt = $db->prepare("
                INSERT INTO teacher_contracts (teacher_id, academic_year_id, contract_type, is_active)
                VALUES (?, ?, 'VACATAIRE', 1)
            ");
            $stmt->execute([$teacher['teacher_id'], $teacher['academic_year_id']]);
            $contractsCreated++;
        } catch (PDOException $e) {
            // Le contrat existe peut-être déjà
            echo "  ⚠ Contrat déjà existant pour teacher_id {$teacher['teacher_id']}, academic_year_id {$teacher['academic_year_id']}\n";
        }
    }
    
    echo "✓ {$contractsCreated} contrats créés pour les enseignants actifs\n\n";
    
    // Étape 4: Vérification finale
    echo "Étape 4: Vérification finale de la structure...\n";
    $structure = $db->query("SHOW CREATE TABLE teacher_contracts")->fetch(PDO::FETCH_ASSOC);
    echo "Structure actuelle:\n";
    echo $structure['Create Table'] . "\n\n";
    
    $contractCount = $db->query("SELECT COUNT(*) FROM teacher_contracts")->fetchColumn();
    echo "Nombre de contrats créés: {$contractCount}\n\n";
    
    echo "=== MIGRATION TERMINÉE AVEC SUCCÈS ===\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur lors de la migration: " . $e->getMessage() . "\n";
    exit(1);
}
