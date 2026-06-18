<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
$db = Database::getInstance()->getConnection();

echo "Running migrations...\n";

// Helper to check if a column exists
function columnExists($db, $table, $column) {
    try {
        $stmt = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $stmt->rowCount() > 0;
    } catch (\Exception $e) {
        return false;
    }
}

// Helper to check if a setting exists
function settingExists($db, $key) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    return (int)$stmt->fetchColumn() > 0;
}

try {
    // 1. Add column 'frais_inscription_reinscription' to 'classes'
    if (!columnExists($db, 'classes', 'frais_inscription_reinscription')) {
        echo "Adding column 'frais_inscription_reinscription' to 'classes'...\n";
        $db->exec("ALTER TABLE classes ADD COLUMN frais_inscription_reinscription DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER frais_inscription");
        echo "Column 'frais_inscription_reinscription' added successfully.\n";
    } else {
        echo "Column 'frais_inscription_reinscription' already exists in 'classes'.\n";
    }

    // 2. Add column 'student_status' to 'enrollments'
    if (!columnExists($db, 'enrollments', 'student_status')) {
        echo "Adding column 'student_status' to 'enrollments'...\n";
        $db->exec("ALTER TABLE enrollments ADD COLUMN student_status ENUM('nouveau', 'ancien') NOT NULL DEFAULT 'nouveau' AFTER academic_year_id");
        echo "Column 'student_status' added successfully.\n";
    } else {
        echo "Column 'student_status' already exists in 'enrollments'.\n";
    }

    // 3. Add default settings
    if (!settingExists($db, 'registration_fee_policy')) {
        echo "Inserting default setting 'registration_fee_policy'...\n";
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->execute(['registration_fee_policy', 'all']);
        echo "Setting 'registration_fee_policy' inserted.\n";
    } else {
        echo "Setting 'registration_fee_policy' already exists.\n";
    }

    if (!settingExists($db, 'payment_methods')) {
        echo "Inserting default setting 'payment_methods'...\n";
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->execute(['payment_methods', 'Espèces,Mobile Money,Orange Money,MTN Mobile Money,Carte bancaire,Virement bancaire,Chèque,Autre']);
        echo "Setting 'payment_methods' inserted.\n";
    } else {
        echo "Setting 'payment_methods' already exists.\n";
    }

    echo "Migrations completed successfully!\n";

} catch (\Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
