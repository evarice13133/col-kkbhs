-- Script de migration SQL pour les paramètres par type d'enseignement (NotesMaster)
-- Date de création: 2026-07-31

-- 1. S'assurer que le type d'enseignement SEC00 (Secondaire) existe
INSERT IGNORE INTO teaching_types (nom, code, position, actif) VALUES ('Secondaire', 'SEC00', 1, 1);

-- 2. Ajouter la colonne teaching_type_id à la table settings si elle n'existe pas encore
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'settings' 
      AND COLUMN_NAME = 'teaching_type_id'
);

SET @stmt_add_col = IF(@col_exists = 0,
    'ALTER TABLE settings DROP PRIMARY KEY, ADD COLUMN teaching_type_id INT NOT NULL DEFAULT 0, ADD PRIMARY KEY (setting_key, teaching_type_id)',
    'SELECT "La colonne teaching_type_id existe deja dans settings"'
);

PREPARE stmt FROM @stmt_add_col;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Migration des données d'établissement existantes vers le type d'enseignement par défaut (SEC00)
SET @sec00_id = (SELECT id FROM teaching_types WHERE code = 'SEC00' LIMIT 1);

UPDATE settings 
SET teaching_type_id = @sec00_id 
WHERE setting_key IN (
    'school_name', 'school_code', 'school_republic', 'school_republic_en',
    'school_ministry', 'school_ministry_en', 'school_slogan', 'school_slogan_en',
    'school_motto', 'school_motto_en', 'school_logo', 'school_city',
    'school_phone', 'school_po_box', 'school_fax', 'school_email', 'school_website',
    'display_school_year', 'principal_name', 'principal_title', 'principal_signature',
    'school_stamp', 'honor_roll_default_threshold', 'bulletin_printing_enabled',
    'registration_fee_policy', 'payment_methods'
) AND teaching_type_id = 0;
