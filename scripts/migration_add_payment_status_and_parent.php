<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Starting migration to add missing columns in 'payments' and 'student_payments'...\n";

    // 1. Check and add columns to 'payments' table
    $paymentsCols = $db->query("DESCRIBE payments")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('parent_payment_id', $paymentsCols)) {
        echo "Adding 'parent_payment_id' to 'payments'...\n";
        $db->exec("ALTER TABLE payments ADD COLUMN parent_payment_id INT NULL DEFAULT NULL AFTER created_at");
        // Add foreign key constraint
        try {
            $db->exec("ALTER TABLE payments ADD CONSTRAINT fk_payments_parent FOREIGN KEY (parent_payment_id) REFERENCES payments(id) ON DELETE SET NULL");
        } catch (Exception $e) {
            echo "Warning adding foreign key fk_payments_parent: " . $e->getMessage() . "\n";
        }
    }

    if (!in_array('status', $paymentsCols)) {
        echo "Adding 'status' to 'payments'...\n";
        $db->exec("ALTER TABLE payments ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'valide' AFTER parent_payment_id");
    }

    if (!in_array('cancelled_by', $paymentsCols)) {
        echo "Adding 'cancelled_by' to 'payments'...\n";
        $db->exec("ALTER TABLE payments ADD COLUMN cancelled_by INT NULL DEFAULT NULL AFTER status");
        try {
            $db->exec("ALTER TABLE payments ADD CONSTRAINT fk_payments_cancelled_by FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL");
        } catch (Exception $e) {
            echo "Warning adding foreign key fk_payments_cancelled_by: " . $e->getMessage() . "\n";
        }
    }

    if (!in_array('cancelled_at', $paymentsCols)) {
        echo "Adding 'cancelled_at' to 'payments'...\n";
        $db->exec("ALTER TABLE payments ADD COLUMN cancelled_at DATETIME NULL DEFAULT NULL AFTER cancelled_by");
    }

    if (!in_array('cancellation_motive', $paymentsCols)) {
        echo "Adding 'cancellation_motive' to 'payments'...\n";
        $db->exec("ALTER TABLE payments ADD COLUMN cancellation_motive TEXT NULL DEFAULT NULL AFTER cancelled_at");
    }

    // 2. Check and add columns to 'student_payments' table
    $studentPaymentsCols = $db->query("DESCRIBE student_payments")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('status', $studentPaymentsCols)) {
        echo "Adding 'status' to 'student_payments'...\n";
        $db->exec("ALTER TABLE student_payments ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'valide'");
    }

    if (!in_array('cancelled_by', $studentPaymentsCols)) {
        echo "Adding 'cancelled_by' to 'student_payments'...\n";
        $db->exec("ALTER TABLE student_payments ADD COLUMN cancelled_by INT NULL DEFAULT NULL");
        try {
            $db->exec("ALTER TABLE student_payments ADD CONSTRAINT fk_student_payments_cancelled_by FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL");
        } catch (Exception $e) {
            echo "Warning adding foreign key fk_student_payments_cancelled_by: " . $e->getMessage() . "\n";
        }
    }

    if (!in_array('cancelled_at', $studentPaymentsCols)) {
        echo "Adding 'cancelled_at' to 'student_payments'...\n";
        $db->exec("ALTER TABLE student_payments ADD COLUMN cancelled_at DATETIME NULL DEFAULT NULL");
    }

    if (!in_array('cancellation_motive', $studentPaymentsCols)) {
        echo "Adding 'cancellation_motive' to 'student_payments'...\n";
        $db->exec("ALTER TABLE student_payments ADD COLUMN cancellation_motive TEXT NULL DEFAULT NULL");
    }

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
