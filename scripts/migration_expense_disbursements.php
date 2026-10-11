<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    $db->exec("
        CREATE TABLE IF NOT EXISTS expense_disbursements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            expense_id INT NOT NULL,
            user_id INT NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT uq_expense_disbursement_user UNIQUE (expense_id, user_id),
            CONSTRAINT fk_expense_disbursements_expense
                FOREIGN KEY (expense_id) REFERENCES expenses(id) ON DELETE CASCADE,
            CONSTRAINT fk_expense_disbursements_user
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "Expense disbursement tracking is ready.\n";
} catch (\Throwable $e) {
    echo "Expense disbursement migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
