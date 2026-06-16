-- ====================================================================
-- SCRIPT DE MIGRATION SQL - PRÉCHARGEMENT DES MATIÈRES DU PRIMAIRE
-- ====================================================================

-- 1. Récupération des ID nécessaires pour la migration
SET @active_year = (SELECT `id` FROM `academic_years` WHERE `is_active` = 1 LIMIT 1);
SET @teaching_pri = (SELECT `id` FROM `teaching_types` WHERE `code` = 'PRI' LIMIT 1);
SET @dept_pri = (SELECT `id` FROM `departments` WHERE `code` = 'PRIM' LIMIT 1);

-- 2. Fonction de nettoyage : Supprimer d'éventuels orphelins si ré-exécuté (optionnel)
-- Nous privilégions ici des requêtes INSERT/UPDATE sécurisées.

-- 3. Définition et insertion des matières du Primaire Francophone et Anglophone
-- (Note : nous insérons chaque matière avec ses caractéristiques uniques de niveau).

-- --- MATERNELLES ET PRIMAIRES FR/BI - NIVEAU A (SIL, CP) ---
INSERT INTO `subjects` (`nom`, `coefficient`, `groupe`, `teaching_type_id`, `department_id`) VALUES
('Langue Française', 4, 'Groupe 1', @teaching_pri, @dept_pri),
('Mathématiques', 4, 'Groupe 1', @teaching_pri, @dept_pri),
('English Language', 2, 'Groupe 1', @teaching_pri, @dept_pri),
('Sciences et Technologie', 2, 'Groupe 2', @teaching_pri, @dept_pri),
('Éducation Civique et Morale', 2, 'Groupe 2', @teaching_pri, @dept_pri),
('Langues et Cultures Nationales', 1, 'Groupe 2', @teaching_pri, @dept_pri),
('Informatique', 1, 'Groupe 2', @teaching_pri, @dept_pri),
('Éducation Artistique', 1, 'Groupe 3', @teaching_pri, @dept_pri),
('Éducation Physique et Sportive', 1, 'Groupe 3', @teaching_pri, @dept_pri),
('Activités Pratiques', 1, 'Groupe 3', @teaching_pri, @dept_pri);

-- --- MATERNELLES ET PRIMAIRES FR/BI - NIVEAU B (CE1, CE2, CM1, CM2) ---
INSERT INTO `subjects` (`nom`, `coefficient`, `groupe`, `teaching_type_id`, `department_id`) VALUES
('Langue Française', 5, 'Groupe 1', @teaching_pri, @dept_pri),
('Mathématiques', 5, 'Groupe 1', @teaching_pri, @dept_pri),
('English Language', 3, 'Groupe 1', @teaching_pri, @dept_pri),
('Sciences et Technologie', 3, 'Groupe 2', @teaching_pri, @dept_pri),
('Histoire et Géographie', 2, 'Groupe 2', @teaching_pri, @dept_pri),
('Éducation Civique et Morale', 2, 'Groupe 2', @teaching_pri, @dept_pri),
('Langues et Cultures Nationales', 1, 'Groupe 2', @teaching_pri, @dept_pri),
('Informatique', 1, 'Groupe 2', @teaching_pri, @dept_pri),
('Éducation Artistique', 1, 'Groupe 3', @teaching_pri, @dept_pri),
('Éducation Physique et Sportive', 2, 'Groupe 3', @teaching_pri, @dept_pri),
('Activités Pratiques', 1, 'Groupe 3', @teaching_pri, @dept_pri);

-- --- PRIMAIRES EN - NIVEAU A (Class 1, Class 2) ---
INSERT INTO `subjects` (`nom`, `coefficient`, `groupe`, `teaching_type_id`, `department_id`) VALUES
('English Language', 4, 'Groupe 1', @teaching_pri, @dept_pri),
('Mathematics', 4, 'Groupe 1', @teaching_pri, @dept_pri),
('French Language', 2, 'Groupe 1', @teaching_pri, @dept_pri),
('Science and Technology', 2, 'Groupe 2', @teaching_pri, @dept_pri),
('Social Studies', 2, 'Groupe 2', @teaching_pri, @dept_pri),
('Citizenship / Moral Education', 2, 'Groupe 2', @teaching_pri, @dept_pri),
('National Languages and Cultures', 1, 'Groupe 2', @teaching_pri, @dept_pri),
('Computer Science', 1, 'Groupe 2', @teaching_pri, @dept_pri),
('Arts and Craft', 1, 'Groupe 3', @teaching_pri, @dept_pri),
('Physical Education', 1, 'Groupe 3', @teaching_pri, @dept_pri),
('Vocational Studies', 1, 'Groupe 3', @teaching_pri, @dept_pri);

-- --- PRIMAIRES EN - NIVEAU B (Class 3, Class 4, Class 5, Class 6) ---
INSERT INTO `subjects` (`nom`, `coefficient`, `groupe`, `teaching_type_id`, `department_id`) VALUES
('English Language', 5, 'Groupe 1', @teaching_pri, @dept_pri),
('Mathematics', 5, 'Groupe 1', @teaching_pri, @dept_pri),
('French Language', 3, 'Groupe 1', @teaching_pri, @dept_pri),
('Science and Technology', 3, 'Groupe 2', @teaching_pri, @dept_pri),
('Social Studies', 3, 'Groupe 2', @teaching_pri, @dept_pri),
-- Note : Citizenship, National Languages, Computer Science, Arts and Craft, Vocational Studies 
-- partagent le même coefficient et groupe entre Niveau A et B, ils seront réutilisés via liaisons de classes.
('Physical Education', 2, 'Groupe 3', @teaching_pri, @dept_pri);

-- 4. Liaison automatique Matière ↔ Classes
-- Ce processus est géré de façon dynamique par le script de migration PHP pour
-- résoudre correctement les IDs de clés étrangères générées de façon séquentielle.
-- Les requêtes d'association type ressemblent à ceci :
-- INSERT IGNORE INTO `subject_classes` (`subject_id`, `class_id`, `academic_year_id`) VALUES (X, Y, @active_year);
