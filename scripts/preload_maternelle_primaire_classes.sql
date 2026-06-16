-- ====================================================================
-- SCRIPT DE MIGRATION SQL - PRÉCHARGEMENT MATERNELLE ET PRIMAIRE
-- ====================================================================

-- 1. Insertion de la section 'Bilingue' si elle n'existe pas
INSERT IGNORE INTO `sections` (`nom`) VALUES ('Bilingue');

-- 2. Insertion des nouveaux cycles s'ils n'existent pas
INSERT IGNORE INTO `cycles` (`nom`) VALUES ('Cycle Maternel'), ('Cycle Primaire');

-- 3. Récupération des variables temporaires pour la cohérence des clés étrangères
-- Sections
SET @section_fr = (SELECT `id` FROM `sections` WHERE LOWER(`nom`) = 'francophone' LIMIT 1);
SET @section_en = (SELECT `id` FROM `sections` WHERE LOWER(`nom`) = 'anglophone' LIMIT 1);
SET @section_bi = (SELECT `id` FROM `sections` WHERE LOWER(`nom`) = 'bilingue' LIMIT 1);

-- Cycles
SET @cycle_mat = (SELECT `id` FROM `cycles` WHERE LOWER(`nom`) = 'cycle maternel' LIMIT 1);
SET @cycle_pri = (SELECT `id` FROM `cycles` WHERE LOWER(`nom`) = 'cycle primaire' LIMIT 1);

-- Types d'Enseignement
SET @teaching_mat = (SELECT `id` FROM `teaching_types` WHERE `code` = 'MAT' LIMIT 1);
SET @teaching_pri = (SELECT `id` FROM `teaching_types` WHERE `code` = 'PRI' LIMIT 1);

-- Départements
SET @dept_mat = (SELECT `id` FROM `departments` WHERE `code` = 'MAT' LIMIT 1);
SET @dept_pri = (SELECT `id` FROM `departments` WHERE `code` = 'PRIM' LIMIT 1);

-- 4. Insertion / Mise à jour des classes officielles

-- --- MATERNELLE FRANCOPHONE ---
INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('Petite Section', @section_fr, @cycle_mat, @dept_mat, @teaching_mat)
ON DUPLICATE KEY UPDATE `section_id` = @section_fr, `cycle_id` = @cycle_mat, `department_id` = @dept_mat, `teaching_type_id` = @teaching_mat;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('Moyenne Section', @section_fr, @cycle_mat, @dept_mat, @teaching_mat)
ON DUPLICATE KEY UPDATE `section_id` = @section_fr, `cycle_id` = @cycle_mat, `department_id` = @dept_mat, `teaching_type_id` = @teaching_mat;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('Grande Section', @section_fr, @cycle_mat, @dept_mat, @teaching_mat)
ON DUPLICATE KEY UPDATE `section_id` = @section_fr, `cycle_id` = @cycle_mat, `department_id` = @dept_mat, `teaching_type_id` = @teaching_mat;

-- --- MATERNELLE ANGLOPHONE ---
INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('Nursery 1', @section_en, @cycle_mat, @dept_mat, @teaching_mat)
ON DUPLICATE KEY UPDATE `section_id` = @section_en, `cycle_id` = @cycle_mat, `department_id` = @dept_mat, `teaching_type_id` = @teaching_mat;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('Nursery 2', @section_en, @cycle_mat, @dept_mat, @teaching_mat)
ON DUPLICATE KEY UPDATE `section_id` = @section_en, `cycle_id` = @cycle_mat, `department_id` = @dept_mat, `teaching_type_id` = @teaching_mat;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('Nursery 3', @section_en, @cycle_mat, @dept_mat, @teaching_mat)
ON DUPLICATE KEY UPDATE `section_id` = @section_en, `cycle_id` = @cycle_mat, `department_id` = @dept_mat, `teaching_type_id` = @teaching_mat;

-- --- MATERNELLE BILINGUE ---
INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('PS Bilingue', @section_bi, @cycle_mat, @dept_mat, @teaching_mat)
ON DUPLICATE KEY UPDATE `section_id` = @section_bi, `cycle_id` = @cycle_mat, `department_id` = @dept_mat, `teaching_type_id` = @teaching_mat;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('MS Bilingue', @section_bi, @cycle_mat, @dept_mat, @teaching_mat)
ON DUPLICATE KEY UPDATE `section_id` = @section_bi, `cycle_id` = @cycle_mat, `department_id` = @dept_mat, `teaching_type_id` = @teaching_mat;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('GS Bilingue', @section_bi, @cycle_mat, @dept_mat, @teaching_mat)
ON DUPLICATE KEY UPDATE `section_id` = @section_bi, `cycle_id` = @cycle_mat, `department_id` = @dept_mat, `teaching_type_id` = @teaching_mat;

-- --- PRIMAIRE FRANCOPHONE ---
INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('SIL', @section_fr, @cycle_pri, @dept_pri, @teaching_pri)
ON DUPLICATE KEY UPDATE `section_id` = @section_fr, `cycle_id` = @cycle_pri, `department_id` = @dept_pri, `teaching_type_id` = @teaching_pri;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('CP', @section_fr, @cycle_pri, @dept_pri, @teaching_pri)
ON DUPLICATE KEY UPDATE `section_id` = @section_fr, `cycle_id` = @cycle_pri, `department_id` = @dept_pri, `teaching_type_id` = @teaching_pri;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('CE1', @section_fr, @cycle_pri, @dept_pri, @teaching_pri)
ON DUPLICATE KEY UPDATE `section_id` = @section_fr, `cycle_id` = @cycle_pri, `department_id` = @dept_pri, `teaching_type_id` = @teaching_pri;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('CE2', @section_fr, @cycle_pri, @dept_pri, @teaching_pri)
ON DUPLICATE KEY UPDATE `section_id` = @section_fr, `cycle_id` = @cycle_pri, `department_id` = @dept_pri, `teaching_type_id` = @teaching_pri;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('CM1', @section_fr, @cycle_pri, @dept_pri, @teaching_pri)
ON DUPLICATE KEY UPDATE `section_id` = @section_fr, `cycle_id` = @cycle_pri, `department_id` = @dept_pri, `teaching_type_id` = @teaching_pri;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('CM2', @section_fr, @cycle_pri, @dept_pri, @teaching_pri)
ON DUPLICATE KEY UPDATE `section_id` = @section_fr, `cycle_id` = @cycle_pri, `department_id` = @dept_pri, `teaching_type_id` = @teaching_pri;

-- --- PRIMAIRE ANGLOPHONE ---
INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('Class 1', @section_en, @cycle_pri, @dept_pri, @teaching_pri)
ON DUPLICATE KEY UPDATE `section_id` = @section_en, `cycle_id` = @cycle_pri, `department_id` = @dept_pri, `teaching_type_id` = @teaching_pri;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('Class 2', @section_en, @cycle_pri, @dept_pri, @teaching_pri)
ON DUPLICATE KEY UPDATE `section_id` = @section_en, `cycle_id` = @cycle_pri, `department_id` = @dept_pri, `teaching_type_id` = @teaching_pri;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('Class 3', @section_en, @cycle_pri, @dept_pri, @teaching_pri)
ON DUPLICATE KEY UPDATE `section_id` = @section_en, `cycle_id` = @cycle_pri, `department_id` = @dept_pri, `teaching_type_id` = @teaching_pri;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('Class 4', @section_en, @cycle_pri, @dept_pri, @teaching_pri)
ON DUPLICATE KEY UPDATE `section_id` = @section_en, `cycle_id` = @cycle_pri, `department_id` = @dept_pri, `teaching_type_id` = @teaching_pri;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('Class 5', @section_en, @cycle_pri, @dept_pri, @teaching_pri)
ON DUPLICATE KEY UPDATE `section_id` = @section_en, `cycle_id` = @cycle_pri, `department_id` = @dept_pri, `teaching_type_id` = @teaching_pri;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('Class 6', @section_en, @cycle_pri, @dept_pri, @teaching_pri)
ON DUPLICATE KEY UPDATE `section_id` = @section_en, `cycle_id` = @cycle_pri, `department_id` = @dept_pri, `teaching_type_id` = @teaching_pri;

-- --- PRIMAIRE BILINGUE ---
INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('SIL Bilingue', @section_bi, @cycle_pri, @dept_pri, @teaching_pri)
ON DUPLICATE KEY UPDATE `section_id` = @section_bi, `cycle_id` = @cycle_pri, `department_id` = @dept_pri, `teaching_type_id` = @teaching_pri;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('CP Bilingue', @section_bi, @cycle_pri, @dept_pri, @teaching_pri)
ON DUPLICATE KEY UPDATE `section_id` = @section_bi, `cycle_id` = @cycle_pri, `department_id` = @dept_pri, `teaching_type_id` = @teaching_pri;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('CE1 Bilingue', @section_bi, @cycle_pri, @dept_pri, @teaching_pri)
ON DUPLICATE KEY UPDATE `section_id` = @section_bi, `cycle_id` = @cycle_pri, `department_id` = @dept_pri, `teaching_type_id` = @teaching_pri;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('CE2 Bilingue', @section_bi, @cycle_pri, @dept_pri, @teaching_pri)
ON DUPLICATE KEY UPDATE `section_id` = @section_bi, `cycle_id` = @cycle_pri, `department_id` = @dept_pri, `teaching_type_id` = @teaching_pri;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('CM1 Bilingue', @section_bi, @cycle_pri, @dept_pri, @teaching_pri)
ON DUPLICATE KEY UPDATE `section_id` = @section_bi, `cycle_id` = @cycle_pri, `department_id` = @dept_pri, `teaching_type_id` = @teaching_pri;

INSERT INTO `classes` (`nom`, `section_id`, `cycle_id`, `department_id`, `teaching_type_id`)
VALUES ('CM2 Bilingue', @section_bi, @cycle_pri, @dept_pri, @teaching_pri)
ON DUPLICATE KEY UPDATE `section_id` = @section_bi, `cycle_id` = @cycle_pri, `department_id` = @dept_pri, `teaching_type_id` = @teaching_pri;

-- 5. Synchronisation des types d'enseignement des élèves rattachés à ces classes
UPDATE `students` st
JOIN `classes` c ON c.`id` = st.`class_id`
SET st.`teaching_type_id` = c.`teaching_type_id`
WHERE c.`teaching_type_id` IN (@teaching_mat, @teaching_pri);
