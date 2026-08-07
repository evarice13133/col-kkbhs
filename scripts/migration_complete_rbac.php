<?php
/**
 * Migration Complete RBAC - NoteMaster
 * 
 * Crée et met à jour la structure complète du système RBAC :
 * - Table roles
 * - Table permissions (avec module, sous-module, action, criticité, statut)
 * - Table role_permissions
 * - Table user_permissions (surcharges individuelles par utilisateur)
 * - Table permission_audit_logs (historique et traçabilité des modifications)
 * - Table permission_backups (sauvegardes et restaurations de configurations)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    echo "=== DEBUT MIGRATION COMPLÈTE RBAC ===\n\n";

    // 1. Table roles
    $db->exec("
        CREATE TABLE IF NOT EXISTS roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            role_code VARCHAR(50) NOT NULL UNIQUE,
            role_name VARCHAR(100) NOT NULL,
            description TEXT NULL,
            is_system TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    $roleColumns = $db->query("SHOW COLUMNS FROM roles")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('is_system', $roleColumns)) {
        $db->exec("ALTER TABLE roles ADD COLUMN is_system TINYINT(1) NOT NULL DEFAULT 1 AFTER description");
    }
    echo "[✓] Table 'roles' vérifiée/créée.\n";

    // 2. Table permissions
    $db->exec("
        CREATE TABLE IF NOT EXISTS permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            perm_code VARCHAR(100) NOT NULL UNIQUE,
            perm_name VARCHAR(150) NOT NULL,
            module VARCHAR(50) NOT NULL DEFAULT 'general',
            submodule VARCHAR(50) NOT NULL DEFAULT 'general',
            action VARCHAR(50) NOT NULL DEFAULT 'view',
            description TEXT NULL,
            criticality ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
            status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            is_system TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_perm_module (module),
            INDEX idx_perm_submodule (submodule),
            INDEX idx_perm_action (action)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // S'assurer que les colonnes ajoutées existent sur une table 'permissions' pré-existante
    $columns = $db->query("SHOW COLUMNS FROM permissions")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('module', $columns)) {
        $db->exec("ALTER TABLE permissions ADD COLUMN module VARCHAR(50) NOT NULL DEFAULT 'general' AFTER perm_name");
    }
    if (!in_array('submodule', $columns)) {
        $db->exec("ALTER TABLE permissions ADD COLUMN submodule VARCHAR(50) NOT NULL DEFAULT 'general' AFTER module");
    }
    if (!in_array('action', $columns)) {
        $db->exec("ALTER TABLE permissions ADD COLUMN action VARCHAR(50) NOT NULL DEFAULT 'view' AFTER submodule");
    }
    if (!in_array('criticality', $columns)) {
        $db->exec("ALTER TABLE permissions ADD COLUMN criticality ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium' AFTER description");
    }
    if (!in_array('status', $columns)) {
        $db->exec("ALTER TABLE permissions ADD COLUMN status ENUM('active', 'inactive') NOT NULL DEFAULT 'active' AFTER criticality");
    }
    if (!in_array('is_system', $columns)) {
        $db->exec("ALTER TABLE permissions ADD COLUMN is_system TINYINT(1) NOT NULL DEFAULT 1 AFTER status");
    }
    echo "[✓] Table 'permissions' vérifiée/enrichie.\n";

    // 3. Table role_permissions
    $db->exec("
        CREATE TABLE IF NOT EXISTS role_permissions (
            role_id INT NOT NULL,
            permission_id INT NOT NULL,
            PRIMARY KEY (role_id, permission_id),
            CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
            CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[✓] Table 'role_permissions' vérifiée/créée.\n";

    // 4. Table user_permissions (Surcharges individuelles)
    $db->exec("
        CREATE TABLE IF NOT EXISTS user_permissions (
            user_id INT NOT NULL,
            permission_id INT NOT NULL,
            is_granted TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Accordé explicitement, 0 = Interdit explicitement',
            granted_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id, permission_id),
            CONSTRAINT fk_user_permissions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT fk_user_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[✓] Table 'user_permissions' vérifiée/créée.\n";

    // 5. Table permission_audit_logs (Journal d'audit)
    $db->exec("
        CREATE TABLE IF NOT EXISTS permission_audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            user_name VARCHAR(150) NULL,
            action_type VARCHAR(50) NOT NULL COMMENT 'role_updated, user_override_added, user_override_removed, backup_restored, scan_executed',
            entity_type VARCHAR(50) NOT NULL COMMENT 'role, user, system',
            entity_id VARCHAR(100) NULL,
            details TEXT NULL,
            payload_before LONGTEXT NULL,
            payload_after LONGTEXT NULL,
            ip_address VARCHAR(45) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_audit_user (user_id),
            INDEX idx_audit_action (action_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[✓] Table 'permission_audit_logs' vérifiée/créée.\n";

    // 6. Table permission_backups (Sauvegardes)
    $db->exec("
        CREATE TABLE IF NOT EXISTS permission_backups (
            id INT AUTO_INCREMENT PRIMARY KEY,
            backup_name VARCHAR(150) NOT NULL,
            description TEXT NULL,
            config_data LONGTEXT NOT NULL,
            created_by INT NULL,
            created_by_name VARCHAR(150) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[✓] Table 'permission_backups' vérifiée/créée.\n";

    // ============================================
    // INITIALISATION / POPULATION DES RÔLES
    // ============================================
    $rolesSeed = [
        ['role_code' => 'superadmin', 'role_name' => 'Super Administrateur', 'description' => 'Accès complet absolu au système.', 'is_system' => 1],
        ['role_code' => 'admin', 'role_name' => 'Administrateur', 'description' => 'Administration classique et globale de l\'établissement.', 'is_system' => 1],
        ['role_code' => 'it_manager', 'role_name' => 'IT Manager', 'description' => 'Responsable de la configuration technique et pédagogique.', 'is_system' => 1],
        ['role_code' => 'caissier', 'role_name' => 'Caissier', 'description' => 'Gestionnaire des encaissements et versements quotidiens.', 'is_system' => 1],
        ['role_code' => 'comptable', 'role_name' => 'Comptable', 'description' => 'Responsable financier, des tarifs, bourses et bilans.', 'is_system' => 1],
        ['role_code' => 'enseignant', 'role_name' => 'Enseignant', 'description' => 'Enseignant accédant aux notes, absences et livrets.', 'is_system' => 1],
    ];

    $stmtRole = $db->prepare("
        INSERT INTO roles (role_code, role_name, description, is_system) 
        VALUES (:role_code, :role_name, :description, :is_system)
        ON DUPLICATE KEY UPDATE role_name = VALUES(role_name), description = VALUES(description)
    ");
    foreach ($rolesSeed as $r) {
        $stmtRole->execute($r);
    }
    echo "[✓] Rôles système initialisés.\n";

    // ============================================
    // CATALOGUE COMPLET DES PERMISSIONS
    // ============================================
    $permissionsSeed = [
        // Administration & RBAC
        ['perm_code' => 'manage_rbac', 'perm_name' => 'Gérer la sécurité RBAC', 'module' => 'system', 'submodule' => 'rbac', 'action' => 'manage', 'description' => 'Configurer les rôles, les autorisations et les exceptions utilisateurs.', 'criticality' => 'critical'],
        ['perm_code' => 'manage_users', 'perm_name' => 'Gérer les utilisateurs', 'module' => 'system', 'submodule' => 'users', 'action' => 'manage', 'description' => 'Créer, modifier et gérer les comptes d\'accès système.', 'criticality' => 'high'],
        ['perm_code' => 'manage_settings', 'perm_name' => 'Gérer les paramètres généraux', 'module' => 'system', 'submodule' => 'settings', 'action' => 'manage', 'description' => 'Configurer l\'établissement, le logo et les paramètres globaux.', 'criticality' => 'high'],
        ['perm_code' => 'view_system_logs', 'perm_name' => 'Consulter les journaux système', 'module' => 'system', 'submodule' => 'audit', 'action' => 'view', 'description' => 'Visualiser les logs d\'activité et les événements de sécurité.', 'criticality' => 'medium'],
        ['perm_code' => 'view_pilotage', 'perm_name' => 'Accéder au Centre de Pilotage', 'module' => 'system', 'submodule' => 'pilotage', 'action' => 'view', 'description' => 'Accéder aux tableaux de bord analytiques et bilans d\'impact.', 'criticality' => 'medium'],

        // Structure Pédagogique
        ['perm_code' => 'view_classes', 'perm_name' => 'Consulter les classes', 'module' => 'pedagogy', 'submodule' => 'classes', 'action' => 'view', 'description' => 'Afficher la liste des classes et effectifs.', 'criticality' => 'low'],
        ['perm_code' => 'manage_classes_structure', 'perm_name' => 'Gérer la structure des classes', 'module' => 'pedagogy', 'submodule' => 'classes', 'action' => 'manage', 'description' => 'Créer, modifier et supprimer des classes.', 'criticality' => 'high'],
        ['perm_code' => 'manage_teaching_types', 'perm_name' => 'Gérer les types d\'enseignement', 'module' => 'pedagogy', 'submodule' => 'structure', 'action' => 'manage', 'description' => 'Configurer les types d\'enseignement (Général, Technique, LMD).', 'criticality' => 'medium'],
        ['perm_code' => 'manage_cycles', 'perm_name' => 'Gérer les cycles', 'module' => 'pedagogy', 'submodule' => 'structure', 'action' => 'manage', 'description' => 'Gérer les cycles académiques.', 'criticality' => 'medium'],
        ['perm_code' => 'manage_levels', 'perm_name' => 'Gérer les niveaux d\'étude', 'module' => 'pedagogy', 'submodule' => 'structure', 'action' => 'manage', 'description' => 'Configurer les niveaux d\'étude.', 'criticality' => 'medium'],
        ['perm_code' => 'manage_sections', 'perm_name' => 'Gérer les sections', 'module' => 'pedagogy', 'submodule' => 'structure', 'action' => 'manage', 'description' => 'Gérer les sections francophones / anglophones.', 'criticality' => 'medium'],
        ['perm_code' => 'manage_departments', 'perm_name' => 'Gérer les départements', 'module' => 'pedagogy', 'submodule' => 'structure', 'action' => 'manage', 'description' => 'Gérer les départements d\'enseignement.', 'criticality' => 'medium'],
        ['perm_code' => 'manage_subjects', 'perm_name' => 'Gérer les matières', 'module' => 'pedagogy', 'submodule' => 'subjects', 'action' => 'manage', 'description' => 'Gérer le catalogue des matières et coefficients.', 'criticality' => 'high'],
        ['perm_code' => 'manage_subject_groups', 'perm_name' => 'Gérer les groupes de matières', 'module' => 'pedagogy', 'submodule' => 'subjects', 'action' => 'manage', 'description' => 'Organiser les matières en groupes/UE.', 'criticality' => 'medium'],
        ['perm_code' => 'manage_teachers', 'perm_name' => 'Gérer les enseignants', 'module' => 'pedagogy', 'submodule' => 'teachers', 'action' => 'manage', 'description' => 'Gérer le registre des enseignants et leurs affectations.', 'criticality' => 'high'],
        ['perm_code' => 'manage_timetables', 'perm_name' => 'Gérer les emplois du temps', 'module' => 'pedagogy', 'submodule' => 'timetables', 'action' => 'manage', 'description' => 'Planifier et éditer les emplois du temps des classes.', 'criticality' => 'high'],
        ['perm_code' => 'manage_academic_years', 'perm_name' => 'Gérer les années scolaires', 'module' => 'pedagogy', 'submodule' => 'academic_years', 'action' => 'manage', 'description' => 'Activer, clôturer et basculer les années académiques.', 'criticality' => 'critical'],
        ['perm_code' => 'manage_sequences', 'perm_name' => 'Gérer les séquences', 'module' => 'pedagogy', 'submodule' => 'sequences', 'action' => 'manage', 'description' => 'Définir les séquences et semestres d\'évaluation.', 'criticality' => 'medium'],

        // Élèves & Évaluations
        ['perm_code' => 'view_students', 'perm_name' => 'Consulter les élèves', 'module' => 'students', 'submodule' => 'registry', 'action' => 'view', 'description' => 'Visualiser les registres des élèves.', 'criticality' => 'low'],
        ['perm_code' => 'manage_students', 'perm_name' => 'Gérer les registres élèves', 'module' => 'students', 'submodule' => 'registry', 'action' => 'manage', 'description' => 'Inscrire, modifier les profils et gérer la scolarité des élèves.', 'criticality' => 'high'],
        ['perm_code' => 'export_students', 'perm_name' => 'Exporter les données élèves', 'module' => 'students', 'submodule' => 'registry', 'action' => 'export', 'description' => 'Exporter les registres élèves au format Excel/PDF.', 'criticality' => 'medium'],
        ['perm_code' => 'manage_marks', 'perm_name' => 'Saisir et modifier les notes', 'module' => 'evaluations', 'submodule' => 'grades', 'action' => 'manage', 'description' => 'Saisir, verrouiller et valider les notes d\'évaluation.', 'criticality' => 'high'],
        ['perm_code' => 'manage_bulletins', 'perm_name' => 'Gérer les bulletins de notes', 'module' => 'evaluations', 'submodule' => 'bulletins', 'action' => 'manage', 'description' => 'Calculer les moyennes, éditer les bulletins et PV.', 'criticality' => 'high'],
        ['perm_code' => 'manage_transcripts', 'perm_name' => 'Gérer les relevés de notes', 'module' => 'evaluations', 'submodule' => 'transcripts', 'action' => 'manage', 'description' => 'Générer les relevés de notes officiels.', 'criticality' => 'medium'],
        ['perm_code' => 'manage_absences', 'perm_name' => 'Gérer les absences et discipline', 'module' => 'evaluations', 'submodule' => 'discipline', 'action' => 'manage', 'description' => 'Saisir et récapituler les absences et blâmes.', 'criticality' => 'medium'],

        // Finances & Scolarité
        ['perm_code' => 'view_class_finances', 'perm_name' => 'Consulter les tarifs de scolarité', 'module' => 'finance', 'submodule' => 'fees', 'action' => 'view', 'description' => 'Voir la grille tarifaire des frais de scolarité.', 'criticality' => 'low'],
        ['perm_code' => 'edit_class_finances', 'perm_name' => 'Configurer la grille tarifaire', 'module' => 'finance', 'submodule' => 'fees', 'action' => 'edit', 'description' => 'Définir les échéances et montants des tranches.', 'criticality' => 'high'],
        ['perm_code' => 'manage_fees', 'perm_name' => 'Gérer les frais de scolarité', 'module' => 'finance', 'submodule' => 'fees', 'action' => 'manage', 'description' => 'Accès global à la configuration de la scolarité.', 'criticality' => 'high'],
        ['perm_code' => 'manage_payments', 'perm_name' => 'Enregistrer et gérer les paiements', 'module' => 'finance', 'submodule' => 'payments', 'action' => 'manage', 'description' => 'Saisir les versements, imprimer les reçus et annuler.', 'criticality' => 'high'],
        ['perm_code' => 'manage_discounts', 'perm_name' => 'Gérer les réductions de scolarité', 'module' => 'finance', 'submodule' => 'discounts', 'action' => 'manage', 'description' => 'Accorder des remises ou réductions aux élèves.', 'criticality' => 'high'],
        ['perm_code' => 'manage_scholarships', 'perm_name' => 'Gérer les bourses scolaires', 'module' => 'finance', 'submodule' => 'scholarships', 'action' => 'manage', 'description' => 'Attribuer et suivre les bourses d\'études.', 'criticality' => 'high'],
        ['perm_code' => 'manage_expenses', 'perm_name' => 'Gérer les dépenses d\'établissement', 'module' => 'finance', 'submodule' => 'expenses', 'action' => 'manage', 'description' => 'Saisir et approuver les dépenses et frais d\'exploitation.', 'criticality' => 'high'],
        ['perm_code' => 'view_financial_history', 'perm_name' => 'Consulter l\'historique financier', 'module' => 'finance', 'submodule' => 'reports', 'action' => 'view', 'description' => 'Consulter le journal des transactions financières.', 'criticality' => 'medium'],
        ['perm_code' => 'view_financial_reports', 'perm_name' => 'Consulter les rapports et insolvables', 'module' => 'finance', 'submodule' => 'reports', 'action' => 'view', 'description' => 'Consulter les bilans d\'encaissement et listes d\'insolvabilité.', 'criticality' => 'high'],

        // Ressources Humaines
        ['perm_code' => 'manage_staff', 'perm_name' => 'Gérer le personnel', 'module' => 'hr', 'submodule' => 'staff', 'action' => 'manage', 'description' => 'Gérer les fiches et dossiers administratifs du personnel.', 'criticality' => 'medium'],
        ['perm_code' => 'manage_contracts', 'perm_name' => 'Gérer les contrats de travail', 'module' => 'hr', 'submodule' => 'contracts', 'action' => 'manage', 'description' => 'Gérer la rédaction et le suivi des contrats.', 'criticality' => 'medium']
    ];

    $stmtPerm = $db->prepare("
        INSERT INTO permissions (perm_code, perm_name, module, submodule, action, description, criticality, status, is_system)
        VALUES (:perm_code, :perm_name, :module, :submodule, :action, :description, :criticality, 'active', 1)
        ON DUPLICATE KEY UPDATE 
            perm_name = VALUES(perm_name),
            module = VALUES(module),
            submodule = VALUES(submodule),
            action = VALUES(action),
            description = VALUES(description),
            criticality = VALUES(criticality)
    ");

    foreach ($permissionsSeed as $p) {
        $stmtPerm->execute($p);
    }
    echo "[✓] Catalogue de " . count($permissionsSeed) . " permissions initialisé/mis à jour.\n";

    // ============================================
    // MAPPING INITIAL PAR DÉFAUT RÔLES -> PERMISSIONS
    // ============================================
    $roleMappings = [
        'it_manager' => [
            'manage_users', 'manage_settings', 'view_system_logs', 'view_pilotage',
            'view_classes', 'manage_classes_structure', 'manage_teaching_types',
            'manage_cycles', 'manage_levels', 'manage_sections', 'manage_departments',
            'manage_subjects', 'manage_subject_groups', 'manage_teachers', 'manage_timetables',
            'manage_academic_years', 'manage_sequences',
            'view_students', 'manage_students', 'export_students', 'manage_marks',
            'manage_bulletins', 'manage_transcripts', 'manage_absences',
            'manage_staff', 'manage_contracts'
        ],
        'caissier' => [
            'view_classes', 'view_students', 'view_class_finances',
            'manage_fees', 'manage_payments', 'manage_discounts', 'manage_scholarships',
            'view_financial_history', 'view_financial_reports'
        ],
        'comptable' => [
            'view_classes', 'view_students', 'view_class_finances', 'edit_class_finances',
            'manage_fees', 'manage_payments', 'manage_discounts', 'manage_scholarships', 'manage_expenses',
            'view_financial_history', 'view_financial_reports',
            'manage_staff', 'manage_contracts'
        ],
        'enseignant' => [
            'view_classes', 'view_students', 'manage_marks', 'manage_absences'
        ],
        'admin' => [
            'manage_users', 'manage_settings', 'view_system_logs', 'view_pilotage',
            'view_classes', 'manage_classes_structure', 'manage_teaching_types',
            'manage_cycles', 'manage_levels', 'manage_sections', 'manage_departments',
            'manage_subjects', 'manage_subject_groups', 'manage_teachers', 'manage_timetables',
            'manage_sequences',
            'view_students', 'manage_students', 'export_students', 'manage_marks',
            'manage_bulletins', 'manage_transcripts', 'manage_absences',
            'view_class_finances', 'edit_class_finances', 'manage_fees', 'manage_payments',
            'manage_discounts', 'manage_scholarships', 'manage_expenses',
            'view_financial_history', 'view_financial_reports',
            'manage_staff', 'manage_contracts'
        ]
    ];

    foreach ($roleMappings as $roleCode => $permCodes) {
        $stmtRoleId = $db->prepare("SELECT id FROM roles WHERE role_code = ?");
        $stmtRoleId->execute([$roleCode]);
        $roleId = $stmtRoleId->fetchColumn();

        if ($roleId) {
            foreach ($permCodes as $pCode) {
                $stmtPermId = $db->prepare("SELECT id FROM permissions WHERE perm_code = ?");
                $stmtPermId->execute([$pCode]);
                $permId = $stmtPermId->fetchColumn();

                if ($permId) {
                    $db->exec("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES ({$roleId}, {$permId})");
                }
            }
        }
    }

    // Superadmin reçoit TOUTES les permissions
    $superadminRoleId = $db->query("SELECT id FROM roles WHERE role_code = 'superadmin'")->fetchColumn();
    if ($superadminRoleId) {
        $db->exec("
            INSERT IGNORE INTO role_permissions (role_id, permission_id)
            SELECT {$superadminRoleId}, id FROM permissions
        ");
    }
    echo "[✓] Alignement initial des privilèges par rôle achevé.\n";

    echo "\n=== MIGRATION RBAC EXÉCUTÉE AVEC SUCCÈS ===\n";

} catch (\Throwable $e) {
    echo "ERROR lors de la migration RBAC : " . $e->getMessage() . "\n";
    exit(1);
}
