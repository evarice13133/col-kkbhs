<?php
/**
 * MIGRATION: Ajout du module Gestion des Dépenses
 * 
 * Crée les tables nécessaires (expense_categories, expenses, expense_logs) et la permission de sécurité.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    
    echo "=== MIGRATION: GESTION DES DEPENSES ===\n";
    
    // 1. Table expense_categories
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS expense_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL UNIQUE,
            active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✓ Table expense_categories ready.\n";

    // 2. Catégories par défaut
    $count = $pdo->query("SELECT COUNT(*) FROM expense_categories")->fetchColumn();
    if ($count == 0) {
        $defaultCategories = [
            'Fournitures de bureau',
            'Salaires',
            'Entretien',
            'Électricité',
            'Eau',
            'Internet',
            'Transport',
            'Maintenance informatique',
            'Événements',
            'Divers'
        ];
        $stmt = $pdo->prepare("INSERT INTO expense_categories (name, active) VALUES (?, 1)");
        foreach ($defaultCategories as $cat) {
            $stmt->execute([$cat]);
        }
        echo "✓ Default categories inserted.\n";
    }

    // 3. Table expenses
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS expenses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            reference VARCHAR(50) NOT NULL UNIQUE,
            expense_date DATE NOT NULL,
            category_id INT NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            motive VARCHAR(255) NOT NULL,
            description TEXT NULL,
            user_id INT NOT NULL,
            academic_year_id INT NOT NULL,
            status ENUM('active', 'inactive', 'cancelled') DEFAULT 'active',
            cancel_reason VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_expenses_category FOREIGN KEY (category_id) REFERENCES expense_categories(id) ON DELETE RESTRICT,
            CONSTRAINT fk_expenses_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
            CONSTRAINT fk_expenses_academic_year FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✓ Table expenses ready.\n";

    // 4. Table expense_logs
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS expense_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            expense_id INT NULL,
            category_id INT NULL,
            user_id INT NOT NULL,
            action ENUM('create', 'update', 'deactivate', 'reactivate', 'cancel') NOT NULL,
            old_values TEXT NULL,
            new_values TEXT NULL,
            reason VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_expense_logs_expense FOREIGN KEY (expense_id) REFERENCES expenses(id) ON DELETE SET NULL,
            CONSTRAINT fk_expense_logs_category FOREIGN KEY (category_id) REFERENCES expense_categories(id) ON DELETE SET NULL,
            CONSTRAINT fk_expense_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✓ Table expense_logs ready.\n";

    // 5. Permission 'manage_expenses'
    $stmt = $pdo->prepare("SELECT id FROM permissions WHERE perm_code = 'manage_expenses'");
    $stmt->execute();
    $permId = $stmt->fetchColumn();
    if (!$permId) {
        $pdo->exec("INSERT INTO permissions (perm_code, perm_name, description) VALUES ('manage_expenses', 'Gérer les dépenses', 'Permet de gérer les dépenses et les catégories de dépenses.')");
        $permId = $pdo->lastInsertId();
        echo "✓ Permission 'manage_expenses' created.\n";
    }

    // 6. Mapping des rôles
    $rolesToMap = ['superadmin', 'admin', 'caissier', 'comptable'];
    $stmtMap = $pdo->prepare("
        INSERT IGNORE INTO role_permissions (role_id, permission_id)
        SELECT r.id, ? FROM roles r WHERE r.role_code = ?
    ");
    foreach ($rolesToMap as $roleCode) {
        $stmtMap->execute([$permId, $roleCode]);
    }
    echo "✓ Permission mapped to roles: superadmin, admin, caissier, comptable.\n";

    echo "=== MIGRATION COMPLETED SUCCESSFULLY ===\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
