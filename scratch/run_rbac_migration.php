<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

echo "Running RBAC migrations (without DDL in transactions)...\n";

try {
    // 1. Create roles table
    echo "Creating 'roles' table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            role_code VARCHAR(50) NOT NULL UNIQUE,
            role_name VARCHAR(100) NOT NULL,
            description TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 2. Create permissions table
    echo "Creating 'permissions' table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            perm_code VARCHAR(100) NOT NULL UNIQUE,
            perm_name VARCHAR(150) NOT NULL,
            description TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 3. Create role_permissions join table
    echo "Creating 'role_permissions' table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS role_permissions (
            role_id INT NOT NULL,
            permission_id INT NOT NULL,
            PRIMARY KEY (role_id, permission_id),
            CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
            CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 4. Alter users table to modify 'role' column from ENUM to VARCHAR
    echo "Altering 'users' table column 'role'...\n";
    $db->exec("ALTER TABLE users MODIFY COLUMN role VARCHAR(50) NOT NULL DEFAULT 'enseignant'");

    // Now start transaction for data seeding
    $db->beginTransaction();

    // 5. Seed default roles
    echo "Seeding default roles...\n";
    $roles = [
        ['superadmin', 'Super Administrateur', 'Accès complet au système.'],
        ['admin', 'Administrateur', 'Administration classique de l\'établissement.'],
        ['it_manager', 'IT Manager', 'Responsable technique et pédagogique.'],
        ['caissier', 'Caissier', 'Caissier de l\'établissement scolaire.'],
        ['comptable', 'Comptable', 'Comptable et responsable financier de l\'établissement.'],
        ['enseignant', 'Enseignant', 'Enseignant de l\'établissement scolaire.']
    ];

    $insRole = $db->prepare("INSERT INTO roles (role_code, role_name, description) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE role_name = VALUES(role_name), description = VALUES(description)");
    foreach ($roles as $r) {
        $insRole->execute($r);
    }

    // 6. Seed permissions
    echo "Seeding default permissions...\n";
    $permissions = [
        // Administration & System
        ['manage_users', 'Gérer les utilisateurs', 'Créer, modifier et supprimer des utilisateurs.'],
        ['manage_settings', 'Gérer les paramètres', 'Modifier les configurations système globales.'],
        ['view_system_logs', 'Consulter les journaux', 'Voir les logs d\'audit et d\'activité système.'],
        ['manage_rbac', 'Gérer la sécurité RBAC', 'Configurer les rôles et affecter des permissions.'],

        // Pedagogical structure
        ['view_classes', 'Consulter les classes', 'Visualiser la liste des classes et leurs effectifs.'],
        ['manage_classes_structure', 'Gérer la structure des classes', 'Créer, modifier la structure pédagogique et supprimer des classes.'],
        ['manage_teaching_types', 'Gérer les types d\'enseignement', 'Créer et configurer les types d\'enseignement.'],
        ['manage_cycles', 'Gérer les cycles', 'Créer et modifier les cycles académiques.'],
        ['manage_sections', 'Gérer les sections', 'Créer et modifier les sections d\'études.'],
        ['manage_departments', 'Gérer les départements', 'Créer et modifier les départements d\'enseignement.'],
        ['manage_subjects', 'Gérer les matières', 'Gérer le catalogue des matières et coefficients.'],
        ['manage_teachers', 'Gérer les enseignants', 'Gérer le registre des enseignants et leurs affectations.'],
        ['manage_timetables', 'Gérer les emplois du temps', 'Établir et modifier les emplois du temps.'],
        ['manage_academic_years', 'Gérer les années scolaires', 'Créer, activer et archiver des années académiques.'],
        ['manage_sequences', 'Gérer les séquences', 'Configurer les évaluations, séquences et périodes.'],

        // Student & Grades
        ['view_students', 'Consulter les élèves', 'Visualiser les registres des élèves.'],
        ['manage_students', 'Gérer les élèves', 'Inscrire, modifier les profils et supprimer des élèves.'],
        ['manage_marks', 'Gérer les notes', 'Saisir, importer et modifier des notes d\'évaluation.'],
        ['manage_bulletins', 'Gérer les bulletins et PV', 'Calculer les moyennes, générer des bulletins et exporter des PV.'],
        ['manage_absences', 'Gérer les absences', 'Saisir et suivre les absences et la discipline.'],

        // Human Resources
        ['manage_staff', 'Gérer le personnel', 'Gérer les fiches du personnel de l\'établissement.'],
        ['manage_contracts', 'Gérer les contrats', 'Établir et suivre les contrats de travail.'],

        // Finance
        ['manage_fees', 'Gérer la scolarité', 'Accéder aux modules de scolarité (grille, tranches, versements).'],
        ['view_class_finances', 'Consulter les finances de classe', 'Voir les grilles tarifaires et tranches d\'une classe.'],
        ['edit_class_finances', 'Modifier les finances de classe', 'Définir la scolarité et les tranches d\'une classe.'],
        ['manage_payments', 'Gérer les paiements et reçus', 'Enregistrer les versements et imprimer des reçus.'],
        ['manage_discounts', 'Gérer les réductions', 'Attribuer et paramétrer les réductions de scolarité.'],
        ['manage_scholarships', 'Gérer les bourses', 'Attribuer et paramétrer les bourses scolaires.'],
        ['view_financial_history', 'Consulter l\'historique financier', 'Consulter le journal d\'audit financier.'],
        ['view_financial_reports', 'Consulter les rapports financiers', 'Consulter les statistiques financières et les insolvables.']
    ];

    $insPerm = $db->prepare("INSERT INTO permissions (perm_code, perm_name, description) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE perm_name = VALUES(perm_name), description = VALUES(description)");
    foreach ($permissions as $p) {
        $insPerm->execute($p);
    }

    // 7. Map permissions to roles
    echo "Mapping permissions to roles...\n";

    $mapRolePermissions = function($db, $roleCode, $permCodes) {
        $roleId = $db->query("SELECT id FROM roles WHERE role_code = '$roleCode'")->fetchColumn();
        if (!$roleId) return;

        // Clear existing maps for safety
        $db->exec("DELETE FROM role_permissions WHERE role_id = $roleId");

        $stmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        foreach ($permCodes as $code) {
            $permId = $db->query("SELECT id FROM permissions WHERE perm_code = '$code'")->fetchColumn();
            if ($permId) {
                $stmt->execute([$roleId, $permId]);
            }
        }
    };

    // IT Manager: Complete administrative, pedagogical, and user access but STRICTLY NO financials.
    $itManagerPerms = [
        'manage_users', 'manage_settings', 'view_system_logs',
        'view_classes', 'manage_classes_structure', 'manage_teaching_types',
        'manage_cycles', 'manage_sections', 'manage_departments',
        'manage_subjects', 'manage_teachers', 'manage_timetables',
        'manage_academic_years', 'manage_sequences',
        'view_students', 'manage_students', 'manage_marks',
        'manage_bulletins', 'manage_absences',
        'manage_staff', 'manage_contracts'
    ];
    $mapRolePermissions($db, 'it_manager', $itManagerPerms);

    // Caissier: Daily payment operations.
    $caissierPerms = [
        'view_classes', 'view_class_finances',
        'view_students',
        'manage_fees', 'manage_payments', 'manage_discounts', 'manage_scholarships',
        'view_financial_history', 'view_financial_reports'
    ];
    $mapRolePermissions($db, 'caissier', $caissierPerms);

    // Comptable: Full financial management including tariff configuration.
    $comptablePerms = [
        'view_classes', 'view_class_finances', 'edit_class_finances',
        'view_students',
        'manage_staff', 'manage_contracts',
        'manage_fees', 'manage_payments', 'manage_discounts', 'manage_scholarships',
        'view_financial_history', 'view_financial_reports'
    ];
    $mapRolePermissions($db, 'comptable', $comptablePerms);

    // Enseignant: Can view classes, students, and enter marks.
    $enseignantPerms = [
        'view_classes', 'view_students', 'manage_marks'
    ];
    $mapRolePermissions($db, 'enseignant', $enseignantPerms);

    // Admin: Can manage almost everything (pedagogical, student, HR, finances).
    $adminPerms = array_merge(
        $itManagerPerms,
        $comptablePerms
    );
    $adminPerms = array_values(array_unique($adminPerms));
    $mapRolePermissions($db, 'admin', $adminPerms);

    // Superadmin: Seed all permissions
    $allPerms = $db->query("SELECT perm_code FROM permissions")->fetchAll(PDO::FETCH_COLUMN);
    $mapRolePermissions($db, 'superadmin', $allPerms);

    $db->commit();
    echo "RBAC migrations completed successfully!\n";

} catch (\Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "Migration failed: " . $e->getMessage() . "\n";
}
