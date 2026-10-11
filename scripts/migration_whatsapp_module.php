<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

$db->exec("
    CREATE TABLE IF NOT EXISTS whatsapp_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT NULL,
        is_enabled TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$db->exec("
    CREATE TABLE IF NOT EXISTS whatsapp_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message_type VARCHAR(50) NOT NULL,
        phone_number VARCHAR(32) NOT NULL,
        payload LONGTEXT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'queued',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_whatsapp_logs_phone (phone_number),
        INDEX idx_whatsapp_logs_type (message_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$db->exec("
    CREATE TABLE IF NOT EXISTS whatsapp_consents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        phone_number VARCHAR(32) NOT NULL,
        consent_status VARCHAR(20) NOT NULL DEFAULT 'unknown',
        source VARCHAR(50) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_whatsapp_consent_student_phone (student_id, phone_number),
        INDEX idx_whatsapp_consent_status (consent_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$defaultSettings = [
    'whatsapp_test_mode' => '1',
    'whatsapp_business_phone_number' => '',
    'whatsapp_phone_number_id' => '',
    'whatsapp_webhook_verify_token' => '',
    'whatsapp_api_base_url' => 'https://graph.facebook.com/v20.0',
    'whatsapp_template_name' => 'notemaster_notification',
    'whatsapp_app_id' => '',
    'whatsapp_app_secret' => '',
];

$stmt = $db->prepare(
    "INSERT IGNORE INTO whatsapp_settings (setting_key, setting_value, is_enabled) VALUES (?, ?, 1)"
);
foreach ($defaultSettings as $key => $value) {
    $stmt->execute([$key, $value]);
}

$db->exec("UPDATE whatsapp_settings SET setting_value = 'notemaster_notification' WHERE setting_key = 'whatsapp_template_name' AND (setting_value IS NULL OR setting_value = '')");
$db->exec("UPDATE whatsapp_settings SET setting_value = 'https://graph.facebook.com/v20.0' WHERE setting_key = 'whatsapp_api_base_url' AND setting_value = 'https://graph.facebook.com/v19.0'");

echo "WhatsApp schema initialized successfully.\n";
