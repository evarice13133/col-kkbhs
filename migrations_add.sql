-- Migration pour mettre à jour les données vers l'année active
-- À exécuter sur la base de données en production (Hostinger)
-- IMPORTANT: Faire un backup de la base avant d'exécuter ce script

-- 1. Récupérer l'ID de l'année active
SET @active_year_id = (SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1);

-- Afficher l'année active pour vérification
SELECT @active_year_id AS 'Année active ID';

-- 2. Mettre à jour tous les élèves vers l'année active
UPDATE students SET academic_year_id = @active_year_id WHERE academic_year_id != @active_year_id;

SELECT ROW_COUNT() AS 'Nombre d\'élèves mis à jour';

-- 3. Supprimer les notes de l'année active pour éviter les doublons
DELETE FROM grades WHERE academic_year_id = @active_year_id;

SELECT ROW_COUNT() AS 'Nombre de notes supprimées de l\'année active';

-- 4. Mettre à jour toutes les notes vers l'année active
UPDATE grades SET academic_year_id = @active_year_id WHERE academic_year_id != @active_year_id;

SELECT ROW_COUNT() AS 'Nombre de notes mises à jour';

-- 5. Vérifier la distribution finale
SELECT academic_year_id, COUNT(*) as count FROM students GROUP BY academic_year_id;
SELECT academic_year_id, COUNT(*) as count FROM grades GROUP BY academic_year_id;

-- Migration terminée
