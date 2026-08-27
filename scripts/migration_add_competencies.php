<?php

/**
 * Migration: Ajout du système de gestion des compétences/objectifs
 * 
 * Cette migration ajoute les tables nécessaires pour gérer les compétences
 * des matières et leur association aux évaluations.
 * 
 * @author Migration System
 * @date 2026-08-25
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    // Vérifier l'existence de la table competencies
    $check = $db->query("SHOW TABLES LIKE 'competencies'");
    if ($check->rowCount() > 0) {
        echo "La table 'competencies' existe déjà. Migration ignorée.\n";
        exit;
    }
    
    $db->beginTransaction();
    
    // Table 1: competencies - Stocke les compétences/objectifs
    $sqlCompetencies = "
        CREATE TABLE `competencies` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `subject_id` int(11) DEFAULT NULL COMMENT 'ID de la matière (NULL si compétence transversale)',
            `libelle` varchar(255) NOT NULL COMMENT 'Nom de la compétence/objectif',
            `description` text DEFAULT NULL COMMENT 'Description détaillée de la compétence',
            `position` int(11) DEFAULT 0 COMMENT 'Ordre d\'affichage',
            `created_by` int(11) DEFAULT NULL COMMENT 'ID de l\'utilisateur créateur',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `idx_subject_id` (`subject_id`),
            KEY `idx_created_by` (`created_by`),
            CONSTRAINT `fk_competencies_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk_competencies_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Compétences/Objectifs par matière';
    ";
    $db->exec($sqlCompetencies);
    
    // Table 2: evaluation_competencies - Associe les évaluations aux compétences
    // Cette table permet de lier une évaluation (classe+matière+période) à 1 ou 2 compétences
    $sqlEvaluationCompetencies = "
        CREATE TABLE `evaluation_competencies` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `class_id` int(11) NOT NULL COMMENT 'ID de la classe',
            `subject_id` int(11) NOT NULL COMMENT 'ID de la matière',
            `academic_year_id` int(11) NOT NULL COMMENT 'ID de l\'année académique',
            `sequence_id` int(11) DEFAULT NULL COMMENT 'ID de la séquence (si disponible)',
            `periode` varchar(50) NOT NULL COMMENT 'Période d\'évaluation (ex: Trimestre 1 - Sequence 1)',
            `competency_id` int(11) NOT NULL COMMENT 'ID de la compétence évaluée',
            `position` tinyint(1) DEFAULT 1 COMMENT 'Position (1 ou 2 pour max 2 compétences)',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_evaluation_competency` (`class_id`, `subject_id`, `academic_year_id`, `periode`, `competency_id`),
            KEY `idx_class_id` (`class_id`),
            KEY `idx_subject_id` (`subject_id`),
            KEY `idx_academic_year_id` (`academic_year_id`),
            KEY `idx_sequence_id` (`sequence_id`),
            KEY `idx_competency_id` (`competency_id`),
            KEY `idx_periode` (`periode`),
            CONSTRAINT `fk_eval_comp_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk_eval_comp_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk_eval_comp_academic_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk_eval_comp_competency` FOREIGN KEY (`competency_id`) REFERENCES `competencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Association évaluations-compétences (max 2 par évaluation)';
    ";
    $db->exec($sqlEvaluationCompetencies);
    
    // Table 3: teacher_class_competencies - Permet aux enseignants de gérer les compétences de leurs classes
    // Cette table étend le système teacher_assignments pour inclure la gestion des compétences
    $sqlTeacherClassCompetencies = "
        CREATE TABLE `teacher_class_competencies` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `teacher_id` int(11) NOT NULL COMMENT 'ID de l\'enseignant',
            `class_id` int(11) NOT NULL COMMENT 'ID de la classe',
            `subject_id` int(11) NOT NULL COMMENT 'ID de la matière',
            `can_manage_competencies` tinyint(1) DEFAULT 1 COMMENT 'Peut gérer les compétences pour cette classe/matière',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_teacher_class_subject` (`teacher_id`, `class_id`, `subject_id`),
            KEY `idx_teacher_id` (`teacher_id`),
            KEY `idx_class_id` (`class_id`),
            KEY `idx_subject_id` (`subject_id`),
            CONSTRAINT `fk_tcc_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk_tcc_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk_tcc_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Droits de gestion des compétences par enseignant';
    ";
    $db->exec($sqlTeacherClassCompetencies);
    
    // Enregistrer la migration dans la table migrations
    $db->exec("INSERT INTO migrations (migration, batch) VALUES ('migration_add_competencies', 1)");
    
    $db->commit();
    
    echo "✓ Migration 'competencies' exécutée avec succès.\n";
    echo "  - Table 'competencies' créée\n";
    echo "  - Table 'evaluation_competencies' créée\n";
    echo "  - Table 'teacher_class_competencies' créée\n";
    
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "✗ Erreur lors de la migration: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "✗ Erreur lors de la migration: " . $e->getMessage() . "\n";
    exit(1);
}
