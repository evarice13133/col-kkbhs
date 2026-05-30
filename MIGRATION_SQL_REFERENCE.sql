-- ═════════════════════════════════════════════════════════════════════════════════════
-- SCRIPT SQL - Migration Protection des Notes
-- ═════════════════════════════════════════════════════════════════════════════════════
-- 
-- Ce script contient TOUTES les modifications SQL appliquées à la base de données.
-- À utiliser comme référence ou pour recréer la migration en cas de besoin.
--
-- IMPORTANT: Ce script a DÉJÀ ÉTÉ APPLIQUÉ via PHP scripts/fix_grades_cascade.php
--

-- ═════════════════════════════════════════════════════════════════════════════════════
-- ÉTAPE 1 : AJOUTER LES COLONNES SNAPSHOT
-- ═════════════════════════════════════════════════════════════════════════════════════

ALTER TABLE grades ADD COLUMN teacher_nom_snapshot VARCHAR(100) DEFAULT NULL 
    COMMENT 'Nom de l''enseignant au moment de la saisie';

ALTER TABLE grades ADD COLUMN teacher_prenom_snapshot VARCHAR(100) DEFAULT NULL 
    COMMENT 'Prénom de l''enseignant au moment de la saisie';

ALTER TABLE grades ADD COLUMN subject_nom_snapshot VARCHAR(100) DEFAULT NULL 
    COMMENT 'Nom de la matière au moment de la saisie';

ALTER TABLE grades ADD COLUMN created_by_type ENUM('enseignant', 'admin') DEFAULT 'enseignant'
    COMMENT 'Type de créateur (enseignant ou admin)';

-- ═════════════════════════════════════════════════════════════════════════════════════
-- ÉTAPE 2 : REMPLIR LES SNAPSHOTS POUR LES DONNÉES EXISTANTES
-- ═════════════════════════════════════════════════════════════════════════════════════

UPDATE grades g
LEFT JOIN users u ON g.teacher_id = u.id
LEFT JOIN subjects s ON g.subject_id = s.id
SET 
    g.teacher_nom_snapshot = COALESCE(u.nom, 'Supprimé'),
    g.teacher_prenom_snapshot = COALESCE(u.prenom, 'Supprimé'),
    g.subject_nom_snapshot = COALESCE(s.nom, 'Supprimé'),
    g.created_by_type = 'enseignant'
WHERE g.teacher_nom_snapshot IS NULL;

-- ═════════════════════════════════════════════════════════════════════════════════════
-- ÉTAPE 3 : RENDRE NULLABLE LA COLONNE teacher_id
-- ═════════════════════════════════════════════════════════════════════════════════════

ALTER TABLE grades MODIFY teacher_id INT NULL;

-- ═════════════════════════════════════════════════════════════════════════════════════
-- ÉTAPE 4 : SUPPRIMER L'ANCIENNE CONTRAINTE FK
-- ═════════════════════════════════════════════════════════════════════════════════════

-- Avant cette modification, la contrainte était :
-- CONSTRAINT `grades_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE

ALTER TABLE grades DROP FOREIGN KEY grades_ibfk_3;

-- ═════════════════════════════════════════════════════════════════════════════════════
-- ÉTAPE 5 : AJOUTER LA NOUVELLE CONTRAINTE FK AVEC ON DELETE SET NULL
-- ═════════════════════════════════════════════════════════════════════════════════════

ALTER TABLE grades 
ADD CONSTRAINT grades_fk_teacher_safe
FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE RESTRICT;

-- ═════════════════════════════════════════════════════════════════════════════════════
-- ÉTAPE 6 : NETTOYAGE DES RÉFÉRENCES INVALIDES (si nécessaire)
-- ═════════════════════════════════════════════════════════════════════════════════════

-- Mettre à NULL les teacher_id qui pointent vers des utilisateurs supprimés
UPDATE grades SET teacher_id = NULL 
WHERE teacher_id NOT IN (SELECT id FROM users WHERE id IS NOT NULL);

-- ═════════════════════════════════════════════════════════════════════════════════════
-- VÉRIFICATION FINALE
-- ═════════════════════════════════════════════════════════════════════════════════════

-- Vérifier la structure de la table
SHOW CREATE TABLE grades;

-- Vérifier les contraintes
SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_NAME = 'grades' AND REFERENCED_TABLE_NAME IS NOT NULL;

-- Vérifier l'intégrité
SELECT COUNT(*) as total_grades FROM grades;
SELECT COUNT(*) as notes_with_teacher FROM grades WHERE teacher_id IS NOT NULL;
SELECT COUNT(*) as orphaned_notes FROM grades WHERE teacher_id IS NULL;
SELECT COUNT(*) as with_snapshot FROM grades WHERE teacher_nom_snapshot IS NOT NULL;
SELECT COUNT(*) as invalid_refs FROM grades WHERE teacher_id NOT IN (SELECT id FROM users WHERE id IS NOT NULL);

-- ═════════════════════════════════════════════════════════════════════════════════════
-- ÉTAT FINAL DE LA TABLE
-- ═════════════════════════════════════════════════════════════════════════════════════

/*
STRUCTURE FINALE :

CREATE TABLE `grades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,              ← NULLABLE
  `academic_year_id` int(11) DEFAULT NULL,
  `sequence_id` int(11) DEFAULT NULL,
  `periode` varchar(50) NOT NULL,
  `valeur` float DEFAULT NULL CHECK (`valeur` >= 0 and `valeur` <= 20),
  `appreciation` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `teacher_nom_snapshot` varchar(100) DEFAULT NULL,          ← NOUVEAU
  `teacher_prenom_snapshot` varchar(100) DEFAULT NULL,       ← NOUVEAU
  `subject_nom_snapshot` varchar(100) DEFAULT NULL,          ← NOUVEAU
  `created_by_type` enum('enseignant','admin') DEFAULT 'enseignant',  ← NOUVEAU
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_subject_period` (`student_id`,`subject_id`,`periode`,`academic_year_id`),
  KEY `subject_id` (`subject_id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `idx_grades_stats` (`teacher_id`,`academic_year_id`,`subject_id`),
  KEY `sequence_id` (`sequence_id`),
  CONSTRAINT `grades_fk_teacher_safe` FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) 
    ON DELETE SET NULL ON UPDATE RESTRICT,                   ← MODIFIÉE (CASCADE → SET NULL)
  CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) 
    ON DELETE CASCADE,
  CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) 
    ON DELETE CASCADE,
  CONSTRAINT `grades_ibfk_4` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years`(`id`) 
    ON DELETE SET NULL,
  CONSTRAINT `grades_ibfk_5` FOREIGN KEY (`sequence_id`) REFERENCES `sequences`(`id`) 
    ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1604 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

*/

-- ═════════════════════════════════════════════════════════════════════════════════════
-- EXEMPLE DE DONNÉES APRÈS MIGRATION
-- ═════════════════════════════════════════════════════════════════════════════════════

/*
Avant suppression d'enseignant:
┌────┬────────┬──────────┬────────┬──────────────────┬───────────────────┐
│ id │ st_id  │ subj_id  │ tc_id  │ teacher_nom_snap │ teacher_prenom_.. │
├────┼────────┼──────────┼────────┼──────────────────┼───────────────────┤
│ 42 │ 1      │ 5        │ 29     │ Lonfo            │ Derick            │
│ 43 │ 2      │ 5        │ 29     │ Lonfo            │ Derick            │
└────┴────────┴──────────┴────────┴──────────────────┴───────────────────┘

Après suppression du teacher_id = 29:
┌────┬────────┬──────────┬────────┬──────────────────┬───────────────────┐
│ id │ st_id  │ subj_id  │ tc_id  │ teacher_nom_snap │ teacher_prenom_.. │
├────┼────────┼──────────┼────────┼──────────────────┼───────────────────┤
│ 42 │ 1      │ 5        │ NULL   │ Lonfo            │ Derick            │ ← teacher_id = NULL
│ 43 │ 2      │ 5        │ NULL   │ Lonfo            │ Derick            │ ← teacher_id = NULL
└────┴────────┴──────────┴────────┴──────────────────┴───────────────────┘

NOTE: Les données sont CONSERVÉES, teacher_id devient NULL mais les snapshots restent!
*/

-- ═════════════════════════════════════════════════════════════════════════════════════
-- FIN DE LA MIGRATION
-- ═════════════════════════════════════════════════════════════════════════════════════
