-- ==============================================================================
-- SCRIPT DE PREPARATION A LA PRODUCTION - PURGE DES DONNEES DE TEST & DEMO
-- Application : NotesMaster / Futura
-- Auteur : Antigravity (Google DeepMind Team)
-- Date : 2026-07-29
-- DB Cible : u290233073_col_futura_db2 (ou votre base de production)
-- ==============================================================================
-- 
-- PREREQUIS OBLIGATOIRE - SAUVEGARDE AVANT EXECUTION :
-- Sur votre serveur MySQL / MariaDB (en ligne de commande ou via phpMyAdmin), 
-- veuillez exécuter une sauvegarde complète de votre base de données :
--
-- Ex: mysqldump -u [utilisateur] -p u290233073_col_futura_db2 > backup_pre_prod_$(date +%Y%m%d_%H%M%S).sql
--
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;

START TRANSACTION;

-- 1. Tables de données académiques et évaluation des élèves
DELETE FROM `grades`;
ALTER TABLE `grades` AUTO_INCREMENT = 1;

DELETE FROM `discipline`;
ALTER TABLE `discipline` AUTO_INCREMENT = 1;

DELETE FROM `conseils_classe`;
ALTER TABLE `conseils_classe` AUTO_INCREMENT = 1;

DELETE FROM `decisions_fin_annee`;
ALTER TABLE `decisions_fin_annee` AUTO_INCREMENT = 1;

DELETE FROM `historique_modifications_conseil`;
ALTER TABLE `historique_modifications_conseil` AUTO_INCREMENT = 1;

DELETE FROM `historique_passages`;
ALTER TABLE `historique_passages` AUTO_INCREMENT = 1;

-- 2. Tables de reçus et allocations de paiements
DELETE FROM `payment_receipts`;
ALTER TABLE `payment_receipts` AUTO_INCREMENT = 1;

DELETE FROM `receipt_verifications_log`;
ALTER TABLE `receipt_verifications_log` AUTO_INCREMENT = 1;

DELETE FROM `student_payment_allocations`;
ALTER TABLE `student_payment_allocations` AUTO_INCREMENT = 1;

-- 3. Tables des paiements et tranches des élèves
DELETE FROM `payments`;
ALTER TABLE `payments` AUTO_INCREMENT = 1;

DELETE FROM `student_payments`;
ALTER TABLE `student_payments` AUTO_INCREMENT = 1;

DELETE FROM `student_installments`;
ALTER TABLE `student_installments` AUTO_INCREMENT = 1;

DELETE FROM `student_discounts`;
ALTER TABLE `student_discounts` AUTO_INCREMENT = 1;

DELETE FROM `student_scholarships`;
ALTER TABLE `student_scholarships` AUTO_INCREMENT = 1;

DELETE FROM `insolvent_students`;
ALTER TABLE `insolvent_students` AUTO_INCREMENT = 1;

-- 4. Inscriptions et Elèves
DELETE FROM `enrollments`;
ALTER TABLE `enrollments` AUTO_INCREMENT = 1;

DELETE FROM `students`;
ALTER TABLE `students` AUTO_INCREMENT = 1;

-- 5. Dépenses et journaux de dépenses (Caisse / Comptabilité)
DELETE FROM `expense_logs`;
ALTER TABLE `expense_logs` AUTO_INCREMENT = 1;

DELETE FROM `expenses`;
ALTER TABLE `expenses` AUTO_INCREMENT = 1;

-- 6. Purge ciblée des journaux d'activité et d'historique financier (Logs de test)
DELETE FROM `financial_history` WHERE `entity_type` IN ('payment', 'student_payment');
ALTER TABLE `financial_history` AUTO_INCREMENT = 1;

DELETE FROM `activity_logs` WHERE `entity_type` IN ('student', 'enrollment', 'payment', 'student_payment', 'grade', 'gradebook', 'expense') OR `route` LIKE '/expenses%';
ALTER TABLE `activity_logs` AUTO_INCREMENT = 1;

COMMIT;

SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- FIN DU SCRIPT DE PURGE PRE-PRODUCTION
-- Toutes les données de configuration (Utilisateurs, Rôles, Structure académique,
-- Matières, Barèmes de frais par classe, Groupes, Types d'enseignement) ont été préservées.
-- ==============================================================================
