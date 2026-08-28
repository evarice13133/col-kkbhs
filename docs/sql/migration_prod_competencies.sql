-- NotesMaster - installation production du module Competences
-- A executer dans la base de production cible apres un backup complet.
-- Ce script est non destructif et peut etre execute plusieurs fois.

SET NAMES utf8mb4;

SELECT DATABASE() AS base_cible;

-- Les tables parent doivent deja exister : subjects, users, classes,
-- academic_years. Aucune donnee de ces tables n'est modifiee.

CREATE TABLE IF NOT EXISTS competencies (
    id INT(11) NOT NULL AUTO_INCREMENT,
    subject_id INT(11) DEFAULT NULL COMMENT 'ID de la matiere, NULL si competence transversale',
    libelle VARCHAR(255) NOT NULL COMMENT 'Nom de la competence ou de l objectif',
    description TEXT DEFAULT NULL COMMENT 'Description detaillee de la competence',
    position INT(11) DEFAULT 0 COMMENT 'Ordre d affichage',
    created_by INT(11) DEFAULT NULL COMMENT 'Utilisateur createur',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_subject_id (subject_id),
    KEY idx_created_by (created_by),
    CONSTRAINT fk_competencies_subject
        FOREIGN KEY (subject_id) REFERENCES subjects (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_competencies_user
        FOREIGN KEY (created_by) REFERENCES users (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci
COMMENT='Competences et objectifs par matiere';

CREATE TABLE IF NOT EXISTS evaluation_competencies (
    id INT(11) NOT NULL AUTO_INCREMENT,
    class_id INT(11) NOT NULL COMMENT 'ID de la classe',
    subject_id INT(11) NOT NULL COMMENT 'ID de la matiere',
    academic_year_id INT(11) NOT NULL COMMENT 'ID de l annee academique',
    sequence_id INT(11) DEFAULT NULL COMMENT 'ID de la sequence si disponible',
    periode VARCHAR(50) NOT NULL COMMENT 'Periode d evaluation',
    competency_id INT(11) NOT NULL COMMENT 'ID de la competence',
    position TINYINT(1) DEFAULT 1 COMMENT 'Position de la competence',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_evaluation_competency
        (class_id, subject_id, academic_year_id, periode, competency_id),
    KEY idx_class_id (class_id),
    KEY idx_subject_id (subject_id),
    KEY idx_academic_year_id (academic_year_id),
    KEY idx_sequence_id (sequence_id),
    KEY idx_competency_id (competency_id),
    KEY idx_periode (periode),
    CONSTRAINT fk_eval_comp_class
        FOREIGN KEY (class_id) REFERENCES classes (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_eval_comp_subject
        FOREIGN KEY (subject_id) REFERENCES subjects (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_eval_comp_academic_year
        FOREIGN KEY (academic_year_id) REFERENCES academic_years (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_eval_comp_competency
        FOREIGN KEY (competency_id) REFERENCES competencies (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci
COMMENT='Association evaluations competences';

CREATE TABLE IF NOT EXISTS teacher_class_competencies (
    id INT(11) NOT NULL AUTO_INCREMENT,
    teacher_id INT(11) NOT NULL COMMENT 'ID de l enseignant',
    class_id INT(11) NOT NULL COMMENT 'ID de la classe',
    subject_id INT(11) NOT NULL COMMENT 'ID de la matiere',
    can_manage_competencies TINYINT(1) DEFAULT 1 COMMENT 'Autorisation de gestion',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_teacher_class_subject (teacher_id, class_id, subject_id),
    KEY idx_teacher_id (teacher_id),
    KEY idx_class_id (class_id),
    KEY idx_subject_id (subject_id),
    CONSTRAINT fk_tcc_teacher
        FOREIGN KEY (teacher_id) REFERENCES users (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_tcc_class
        FOREIGN KEY (class_id) REFERENCES classes (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_tcc_subject
        FOREIGN KEY (subject_id) REFERENCES subjects (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci
COMMENT='Droits de gestion des competences par enseignant';

-- Enregistrement de la migration si la table migrations existe.
CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL UNIQUE,
    batch INT NOT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO migrations (migration, batch)
VALUES ('scripts/migration_add_competencies.php', UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE migration = VALUES(migration);

-- Controles finaux : les trois requetes doivent retourner une ligne chacune.
SHOW TABLES LIKE 'competencies';
SHOW TABLES LIKE 'evaluation_competencies';
SHOW TABLES LIKE 'teacher_class_competencies';
