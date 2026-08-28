-- NotesMaster - mise a niveau production pour la branche update-bulletin
-- Date : 2026-08-28
--
-- IMPORTANT
-- 1. Faire un dump complet de la base avant execution.
-- 2. Executer ce fichier dans la base de production cible, jamais dans une autre base.
-- 3. Les instructions DDL font des COMMIT implicites dans MySQL.
-- 4. Ce script ne copie aucune donnee metier depuis la base locale.
-- 5. Les sections 1 a 4 correspondent aux migrations 20260827 a 20260830.
-- 6. La section 5 corrige les AUTO_INCREMENT comme les migrations locales ; elle est
--    volontairement separee car elle peut modifier des identifiants egaux a zero.

SET NAMES utf8mb4;

-- ================================================================
-- 0. STRUCTURE MINIMALE ET CONTROLES PREALABLES
-- ================================================================
-- Cette table doit exister avant les colonnes de liaison ci-dessous.
-- Si elle est absente en production, deployer d'abord
-- scripts/migration_create_subject_groups.php puis reprendre ce fichier.
SELECT DATABASE() AS base_cible;

-- ================================================================
-- 1. FORMES D'ENSEIGNEMENT ET DEPARTEMENTS
-- Migration locale : 20260827_create_teaching_forms.php
-- ================================================================

CREATE TABLE IF NOT EXISTS teaching_forms (
    id INT NOT NULL AUTO_INCREMENT,
    nom VARCHAR(150) NOT NULL,
    code VARCHAR(50) NOT NULL,
    teaching_type_id INT NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_teaching_forms_code_type (code, teaching_type_id),
    KEY idx_teaching_forms_type (teaching_type_id),
    CONSTRAINT fk_teaching_forms_teaching_type
        FOREIGN KEY (teaching_type_id) REFERENCES teaching_types(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @has_department_form_column := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'departments'
      AND COLUMN_NAME = 'teaching_form_id'
);
SET @sql := IF(@has_department_form_column = 0,
    'ALTER TABLE departments ADD COLUMN teaching_form_id INT NULL AFTER teaching_type_id',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_department_form_fk := (
    SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND CONSTRAINT_NAME = 'fk_departments_teaching_form'
);
SET @sql := IF(@has_department_form_fk = 0,
    'ALTER TABLE departments ADD CONSTRAINT fk_departments_teaching_form FOREIGN KEY (teaching_form_id) REFERENCES teaching_forms(id) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Permission fonctionnelle, sans doublon.
INSERT INTO permissions
    (perm_code, perm_name, module, submodule, action, description, criticality, status, is_system)
VALUES
    ('manage_teaching_forms', 'Gerer les formes d''enseignement', 'pedagogy', 'structure', 'manage', 'Creer et modifier les formes d''enseignement.', 'medium', 'active', 1)
ON DUPLICATE KEY UPDATE
    perm_name = VALUES(perm_name),
    description = VALUES(description);

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.perm_code = 'manage_teaching_forms'
WHERE r.role_code IN ('admin', 'superadmin', 'direction_academique', 'it_manager');

-- ================================================================
-- 2. FORME SUR LES GROUPES DE MODULES
-- Migration locale : 20260828_add_teaching_form_to_subject_groups.php
-- ================================================================

SET @has_group_form_column := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subject_groups'
      AND COLUMN_NAME = 'teaching_form_id'
);
SET @sql := IF(@has_group_form_column = 0,
    'ALTER TABLE subject_groups ADD COLUMN teaching_form_id INT NULL AFTER teaching_type_id',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_group_form_index := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subject_groups'
      AND INDEX_NAME = 'idx_subject_groups_teaching_form'
);
SET @sql := IF(@has_group_form_index = 0,
    'ALTER TABLE subject_groups ADD INDEX idx_subject_groups_teaching_form (teaching_form_id)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_group_form_fk := (
    SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND CONSTRAINT_NAME = 'fk_subject_groups_teaching_form'
);
SET @sql := IF(@has_group_form_fk = 0,
    'ALTER TABLE subject_groups ADD CONSTRAINT fk_subject_groups_teaching_form FOREIGN KEY (teaching_form_id) REFERENCES teaching_forms(id) ON DELETE RESTRICT ON UPDATE CASCADE',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Reproduit l'inference de la migration locale uniquement pour les groupes
-- dont le type possede une seule forme active. Les autres restent a NULL pour
-- eviter un rattachement arbitraire.
UPDATE subject_groups sg
JOIN (
    SELECT tt.id AS teaching_type_id, MIN(tf.id) AS teaching_form_id
    FROM teaching_types tt
    JOIN teaching_forms tf ON tf.teaching_type_id = tt.id AND tf.status = 1
    GROUP BY tt.id
    HAVING COUNT(tf.id) = 1
) one_form ON one_form.teaching_type_id = sg.teaching_type_id
SET sg.teaching_form_id = one_form.teaching_form_id
WHERE sg.teaching_form_id IS NULL;

-- ================================================================
-- 3. POSITION UNIQUE PAR FORME
-- Migration locale : 20260830_add_subject_group_position.php
-- ================================================================

SET @has_position_column := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subject_groups'
      AND COLUMN_NAME = 'position'
);
SET @position_column_was_added := IF(@has_position_column = 0, 1, 0);
SET @sql := IF(@has_position_column = 0,
    'ALTER TABLE subject_groups ADD COLUMN position INT NOT NULL DEFAULT 1 AFTER teaching_form_id',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Initialisation deterministe : 1, 2, 3... a l'interieur de chaque forme.
-- Les groupes legacy sans forme recoivent aussi une position technique stable.
SET @current_form := NULL;
SET @current_position := 0;
UPDATE subject_groups sg
JOIN (
    SELECT id,
           @current_position := IF(@current_form <=> teaching_form_id, @current_position + 1, 1) AS new_position,
           @current_form := teaching_form_id AS form_marker
    FROM subject_groups
    CROSS JOIN (SELECT @current_form := NULL, @current_position := 0) vars
    ORDER BY teaching_form_id IS NULL, teaching_form_id, id
) ordered ON ordered.id = sg.id
SET sg.position = ordered.new_position
WHERE @position_column_was_added = 1;

SET @has_position_unique_index := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subject_groups'
      AND INDEX_NAME = 'uq_subject_groups_form_position'
);
SET @sql := IF(@has_position_unique_index = 0,
    'ALTER TABLE subject_groups ADD UNIQUE KEY uq_subject_groups_form_position (teaching_form_id, position)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ================================================================
-- 4. CONTROLES DE COHERENCE AVANT UTILISATION
-- ================================================================
SELECT teaching_form_id, position, COUNT(*) AS occurrences
FROM subject_groups
WHERE teaching_form_id IS NOT NULL
GROUP BY teaching_form_id, position
HAVING COUNT(*) > 1;

SELECT sg.id, sg.libelle, sg.teaching_type_id, sg.teaching_form_id, sg.position
FROM subject_groups sg
LEFT JOIN teaching_forms tf ON tf.id = sg.teaching_form_id
WHERE sg.teaching_form_id IS NOT NULL
  AND (tf.id IS NULL OR tf.status <> 1);

-- Un resultat vide est attendu pour les deux controles ci-dessus.

-- ================================================================
-- 5. AUTO_INCREMENT (OPTIONNEL, equivalent des migrations 20260826,
--    20260829 et 20260830_fix_subject_groups_autoinc)
-- ================================================================
-- Executer cette section uniquement apres sauvegarde et verification des FK.
-- Elle ne copie aucune donnee et ne modifie que les identifiants egaux a 0
-- lorsqu'ils existent, puis remet AUTO_INCREMENT au prochain identifiant.

-- 5A. subjects : verification avant correction
SELECT id, COUNT(*) AS occurrences FROM subjects GROUP BY id HAVING id = 0 OR COUNT(*) > 1;
-- Si une ligne id=0 existe, traiter d'abord ses eventuelles references metier.
-- Puis, seulement si aucune collision n'est possible :
-- SET @next_subject_id := (SELECT COALESCE(MAX(id), 0) + 1 FROM subjects);
-- UPDATE subjects SET id = @next_subject_id WHERE id = 0;
-- ALTER TABLE subjects MODIFY id INT NOT NULL AUTO_INCREMENT;
-- SET @sql := CONCAT('ALTER TABLE subjects AUTO_INCREMENT = ', @next_subject_id);
-- PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 5A-bis. roles et permissions : la migration locale s'assure que leurs IDs
-- sont auto-increment. Ne pas ajouter de cle primaire ici si elle existe deja.
ALTER TABLE roles MODIFY id INT NOT NULL AUTO_INCREMENT;
ALTER TABLE permissions MODIFY id INT NOT NULL AUTO_INCREMENT;

-- 5B. subject_groups : le correctif d'ID zero est deja couvert par la section
-- 3 pour la position, mais la remise en AUTO_INCREMENT peut etre executee ici.
-- ALTER TABLE subject_groups MODIFY id INT NOT NULL AUTO_INCREMENT;
-- SET @next_group_id := (SELECT COALESCE(MAX(id), 0) + 1 FROM subject_groups);
-- SET @sql := CONCAT('ALTER TABLE subject_groups AUTO_INCREMENT = ', @next_group_id);
-- PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 5C. Tables financieres corrigees localement : student_discounts,
-- student_scholarships. A executer seulement si le SELECT retourne id=0.
SELECT 'student_discounts' AS table_name, id FROM student_discounts WHERE id = 0;
SELECT 'student_scholarships' AS table_name, id FROM student_scholarships WHERE id = 0;
-- Exemple de procedure manuelle pour chaque table, apres sauvegarde :
-- SET @next_fin_id := (SELECT COALESCE(MAX(id), 0) + 1 FROM student_discounts);
-- UPDATE student_discounts SET id = @next_fin_id WHERE id = 0;
-- ALTER TABLE student_discounts MODIFY id INT NOT NULL AUTO_INCREMENT;
-- SET @sql := CONCAT('ALTER TABLE student_discounts AUTO_INCREMENT = ', @next_fin_id);
-- PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ================================================================
-- 6. VERIFICATION FINALE
-- ================================================================
SHOW COLUMNS FROM departments LIKE 'teaching_form_id';
SHOW COLUMNS FROM subject_groups LIKE 'teaching_form_id';
SHOW COLUMNS FROM subject_groups LIKE 'position';
SHOW INDEX FROM subject_groups WHERE Key_name IN ('idx_subject_groups_teaching_form', 'uq_subject_groups_form_position');
SELECT id, libelle, teaching_type_id, teaching_form_id, position
FROM subject_groups
ORDER BY teaching_form_id IS NULL, teaching_form_id, position, id;
