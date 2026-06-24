<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Starting database migration for 'Scolarité' module...\n";

    // 1. Table school_fees
    echo "Creating 'school_fees' table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS school_fees (
            id INT AUTO_INCREMENT PRIMARY KEY,
            academic_year_id INT NOT NULL,
            class_id INT NULL,
            cycle_id INT NULL,
            teaching_type_id INT NULL,
            amount DECIMAL(15,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_school_fees_year (academic_year_id),
            INDEX idx_school_fees_class (class_id),
            INDEX idx_school_fees_cycle (cycle_id),
            INDEX idx_school_fees_teaching_type (teaching_type_id),
            FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
            FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
            FOREIGN KEY (cycle_id) REFERENCES cycles(id) ON DELETE SET NULL,
            FOREIGN KEY (teaching_type_id) REFERENCES teaching_types(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 2. Table fee_installments
    echo "Creating 'fee_installments' table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS fee_installments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            academic_year_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            installment_order INT NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            deadline_date DATE NOT NULL,
            class_id INT NULL,
            cycle_id INT NULL,
            teaching_type_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_fee_installments_year (academic_year_id),
            INDEX idx_fee_installments_class (class_id),
            INDEX idx_fee_installments_cycle (cycle_id),
            INDEX idx_fee_installments_teaching_type (teaching_type_id),
            FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
            FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
            FOREIGN KEY (cycle_id) REFERENCES cycles(id) ON DELETE SET NULL,
            FOREIGN KEY (teaching_type_id) REFERENCES teaching_types(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 3. Table installment_deadlines
    echo "Creating 'installment_deadlines' table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS installment_deadlines (
            id INT AUTO_INCREMENT PRIMARY KEY,
            academic_year_id INT NOT NULL,
            class_id INT NOT NULL,
            installment_number INT NOT NULL,
            deadline_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_deadlines_class_year (class_id, academic_year_id),
            FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
            FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 4. Table student_payments (versements)
    echo "Creating 'student_payments' table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS student_payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            academic_year_id INT NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            payment_date DATE NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            reference VARCHAR(100) NULL,
            observation TEXT NULL,
            created_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_student_payments_student (student_id),
            INDEX idx_student_payments_year (academic_year_id),
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 5. Table student_payment_allocations
    echo "Creating 'student_payment_allocations' table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS student_payment_allocations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_payment_id INT NOT NULL,
            student_installment_id INT NOT NULL,
            amount_allocated DECIMAL(15,2) NOT NULL,
            INDEX idx_allocations_payment (student_payment_id),
            INDEX idx_allocations_installment (student_installment_id),
            FOREIGN KEY (student_payment_id) REFERENCES student_payments(id) ON DELETE CASCADE,
            FOREIGN KEY (student_installment_id) REFERENCES student_installments(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 6. Table insolvent_students
    echo "Creating 'insolvent_students' table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS insolvent_students (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            academic_year_id INT NOT NULL,
            amount_due DECIMAL(15,2) NOT NULL,
            unpaid_installments_count INT NOT NULL,
            last_overdue_deadline DATE NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_insolvent_student_year (student_id, academic_year_id),
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 7. Table payment_receipts
    echo "Creating 'payment_receipts' table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS payment_receipts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_payment_id INT NOT NULL,
            receipt_number VARCHAR(50) NOT NULL,
            verification_code VARCHAR(64) NOT NULL,
            print_count INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY (receipt_number),
            UNIQUE KEY (verification_code),
            INDEX idx_payment_receipts_pay (student_payment_id),
            FOREIGN KEY (student_payment_id) REFERENCES student_payments(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 8. Migration of existing scolarite payments (for retro-compatibility)
    echo "Migrating existing scolarite payments to student_payments...\n";
    // Check if there are existing scolarite payments in `payments`
    $existing = $db->query("SELECT * FROM payments WHERE type = 'scolarite'")->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($existing)) {
        $stmtCheck = $db->prepare("SELECT id FROM student_payments WHERE id = ?");
        $stmtInsert = $db->prepare("
            INSERT INTO student_payments (id, student_id, academic_year_id, amount, payment_date, payment_method, reference, observation, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $count = 0;
        foreach ($existing as $pay) {
            $stmtCheck->execute([$pay['id']]);
            if (!$stmtCheck->fetchColumn()) {
                $stmtInsert->execute([
                    $pay['id'],
                    $pay['student_id'],
                    $pay['academic_year_id'],
                    $pay['amount'],
                    $pay['payment_date'],
                    $pay['payment_method'],
                    $pay['reference'],
                    $pay['commentaire'],
                    $pay['created_by'],
                    $pay['created_at']
                ]);
                
                // Generer receipt entry as well for compatibility
                $stmtReceipt = $db->prepare("
                    INSERT INTO payment_receipts (student_payment_id, receipt_number, verification_code, print_count, created_at)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $receiptNum = 'REC-' . date('Ymd', strtotime($pay['payment_date'])) . '-' . sprintf('%04d', $pay['id']);
                $vCode = $pay['verification_code'] ?: bin2hex(random_bytes(16));
                $stmtReceipt->execute([
                    $pay['id'],
                    $receiptNum,
                    $vCode,
                    $pay['print_count'],
                    $pay['created_at']
                ]);
                
                $count++;
            }
        }
        echo "Migrated $count existing payments successfully.\n";
    }

    echo "Migration completed successfully!\n";
} catch (\Exception $e) {
    echo "ERROR during migration: " . $e->getMessage() . "\n";
    exit(1);
}
