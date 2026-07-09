<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    // Create the receipt_verifications_log table
    $db->exec("
        CREATE TABLE IF NOT EXISTS receipt_verifications_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            verification_code VARCHAR(64) NOT NULL,
            payment_id INT NULL,
            receipt_type VARCHAR(50) NULL,
            student_id INT NULL,
            academic_year_id INT NULL,
            is_valid TINYINT(1) DEFAULT 0,
            error_case VARCHAR(50) NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_verif_code (verification_code),
            INDEX idx_verif_payment (payment_id),
            INDEX idx_verif_student (student_id),
            INDEX idx_verif_year (academic_year_id),
            INDEX idx_verif_date (verified_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "Migration 'receipt_verifications_log' executée avec succès.\n";
} catch (\Exception $e) {
    echo "Erreur lors de la migration: " . $e->getMessage() . "\n";
    exit(1);
}
