<?php
/**
 * Migration pour le Module de Gestion des Emplois du Temps
 * NotesMaster / Futura.Camertech
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    echo "=== Execution de la migration: Module Emplois du Temps ===\n";

    // 1. Créneaux Horaires
    $db->exec("
        CREATE TABLE IF NOT EXISTS timetable_time_slots (
            id INT AUTO_INCREMENT PRIMARY KEY,
            heure_debut TIME NOT NULL,
            heure_fin TIME NOT NULL,
            type_creneau ENUM('cours', 'pause') NOT NULL DEFAULT 'cours',
            duree_minutes INT NOT NULL,
            ordre_affichage INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[OK] Table timetable_time_slots créee ou vérifiée.\n";

    // 2. Salles de Classe
    $db->exec("
        CREATE TABLE IF NOT EXISTS class_rooms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            code VARCHAR(50) NOT NULL UNIQUE,
            capacite INT NOT NULL DEFAULT 30,
            description TEXT NULL,
            status TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[OK] Table class_rooms créee ou vérifiée.\n";

    // 3. Semaines de Cours
    $db->exec("
        CREATE TABLE IF NOT EXISTS timetable_weeks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            academic_year_id INT NOT NULL,
            libelle VARCHAR(100) NOT NULL,
            date_debut DATE NOT NULL,
            date_fin DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_year_date_debut (academic_year_id, date_debut)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[OK] Table timetable_weeks créee ou vérifiée.\n";

    // 4. Emplois du Temps (Header)
    $db->exec("
        CREATE TABLE IF NOT EXISTS timetables (
            id INT AUTO_INCREMENT PRIMARY KEY,
            academic_year_id INT NOT NULL,
            teaching_type_id INT NOT NULL,
            cycle_id INT NOT NULL,
            class_id INT NOT NULL,
            week_id INT NOT NULL,
            titre VARCHAR(150) NOT NULL,
            statut ENUM('brouillon', 'publie', 'verrouille') NOT NULL DEFAULT 'brouillon',
            is_locked TINYINT(1) NOT NULL DEFAULT 0,
            created_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_class_week (class_id, week_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[OK] Table timetables créee ou vérifiée.\n";

    // 5. Entrées/Affectations Grille Emploi du Temps
    $db->exec("
        CREATE TABLE IF NOT EXISTS timetable_entries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            timetable_id INT NOT NULL,
            slot_id INT NOT NULL,
            day_of_week ENUM('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche') NOT NULL,
            subject_id INT NOT NULL,
            teacher_id INT NOT NULL,
            room_id INT NOT NULL,
            couleur_hex VARCHAR(7) DEFAULT '#3b82f6',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_slot_day_class (timetable_id, slot_id, day_of_week)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[OK] Table timetable_entries créee ou vérifiée.\n";

    // 6. Journal d'Audit et Verrouillage
    $db->exec("
        CREATE TABLE IF NOT EXISTS timetable_audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            timetable_id INT NOT NULL,
            user_id INT NOT NULL,
            action_type ENUM('LOCK', 'UNLOCK', 'FORCE_EDIT', 'DELETE', 'PUBLISH') NOT NULL,
            details TEXT NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[OK] Table timetable_audit_logs créee ou vérifiée.\n";

    // 7. Insertion des permissions RBAC
    $perms = [
        ['manage_timetables', 'Gérer les emplois du temps', 'Créer, modifier et supprimer des emplois du temps, créneaux, salles et semaines.'],
        ['view_timetables', 'Consulter les emplois du temps', 'Visualiser, partager et imprimer les emplois du temps.'],
        ['unlock_timetables', 'Déverrouiller les emplois du temps', 'Réservé au Superadmin pour déverrouiller un emploi du temps clôturé.']
    ];

    $stmtPerm = $db->prepare("INSERT INTO permissions (perm_code, perm_name, description) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE perm_name = VALUES(perm_name), description = VALUES(description)");
    foreach ($perms as $p) {
        $stmtPerm->execute($p);
    }
    echo "[OK] Permissions RBAC insérées/mises à jour.\n";

    // Association des permissions aux rôles existants
    $rolesMap = [
        'superadmin' => ['manage_timetables', 'view_timetables', 'unlock_timetables'],
        'admin' => ['manage_timetables', 'view_timetables'],
        'it_manager' => ['manage_timetables', 'view_timetables'],
        'enseignant' => ['view_timetables']
    ];

    foreach ($rolesMap as $roleCode => $permCodes) {
        $roleStmt = $db->prepare("SELECT id FROM roles WHERE role_code = ?");
        $roleStmt->execute([$roleCode]);
        $roleId = $roleStmt->fetchColumn();

        if ($roleId) {
            foreach ($permCodes as $code) {
                $pStmt = $db->prepare("SELECT id FROM permissions WHERE perm_code = ?");
                $pStmt->execute([$code]);
                $pId = $pStmt->fetchColumn();

                if ($pId) {
                    $db->exec("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES ($roleId, $pId)");
                }
            }
        }
    }
    echo "[OK] Permissions affectées aux rôles.\n";

    // 8. Seeding initial de quelques créneaux horaires par défaut (si table vide)
    $countSlots = (int)$db->query("SELECT COUNT(*) FROM timetable_time_slots")->fetchColumn();
    if ($countSlots === 0) {
        $db->exec("
            INSERT INTO timetable_time_slots (heure_debut, heure_fin, type_creneau, duree_minutes, ordre_affichage) VALUES
            ('07:30:00', '08:30:00', 'cours', 60, 1),
            ('08:30:00', '09:30:00', 'cours', 60, 2),
            ('09:30:00', '10:30:00', 'cours', 60, 3),
            ('10:30:00', '11:00:00', 'pause', 30, 4),
            ('11:00:00', '12:00:00', 'cours', 60, 5),
            ('12:00:00', '13:00:00', 'cours', 60, 6),
            ('13:00:00', '14:00:00', 'pause', 60, 7),
            ('14:00:00', '15:00:00', 'cours', 60, 8),
            ('15:00:00', '16:00:00', 'cours', 60, 9);
        ");
        echo "[OK] Créneaux horaires par défaut créés.\n";
    }

    // 9. Seeding initial de quelques salles de classe par défaut (si table vide)
    $countRooms = (int)$db->query("SELECT COUNT(*) FROM class_rooms")->fetchColumn();
    if ($countRooms === 0) {
        $db->exec("
            INSERT INTO class_rooms (nom, code, capacite, description) VALUES
            ('Salle Amphi A', 'AMPHI-A', 150, 'Grand amphithéâtre principal avec vidéoprojecteur'),
            ('Salle Amphi B', 'AMPHI-B', 120, 'Amphithéâtre secondaire'),
            ('Labo Informatique 1', 'LAB-INFO-1', 40, 'Salle équipée de 40 postes informatiques'),
            ('Labo Informatique 2', 'LAB-INFO-2', 35, 'Salle équipée de 35 postes informatiques'),
            ('Salle 101', 'S-101', 50, 'Salle de cours standard'),
            ('Salle 102', 'S-102', 50, 'Salle de cours standard'),
            ('Salle 103', 'S-103', 45, 'Salle de cours standard');
        ");
        echo "[OK] Salles de classe par défaut créées.\n";
    }

    echo "=== Migration terminée avec succès ! ===\n";

} catch (\Throwable $e) {
    echo "[ERREUR MIGRATION] " . $e->getMessage() . "\n";
    exit(1);
}
