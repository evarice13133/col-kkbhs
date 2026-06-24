<?php
/**
 * Script de migration pour le module financier
 * Exécuter via : php scripts/migrate_finance.php
 */

require_once __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "=== DÉBUT DE LA MIGRATION FINANCIÈRE ===\n";

    // Résolution des academic_year_id orphelins ou nuls dans la table students
    echo "Nettoyage des academic_year_id orphelins dans la table 'students'...\n";
    $activeYearId = (int)$pdo->query("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1")->fetchColumn();
    if (!$activeYearId) {
        $activeYearId = (int)$pdo->query("SELECT id FROM academic_years LIMIT 1")->fetchColumn();
    }
    if ($activeYearId > 0) {
        $cleanStmt = $pdo->prepare("UPDATE students SET academic_year_id = ? WHERE academic_year_id NOT IN (SELECT id FROM academic_years) OR academic_year_id IS NULL");
        $cleanStmt->execute([$activeYearId]);
        echo "  - academic_year_id orphelins mis à jour vers l'année active : ID $activeYearId.\n";
    }


    // 1. Altération de la table classes
    echo "Mise à jour de la table 'classes'...\n";
    $columnsToCheck = ['frais_inscription', 'frais_scolarite_brut', 'nbr_tranches'];
    $existingColumns = [];
    $stmt = $pdo->query("DESCRIBE classes");
    while ($row = $stmt->fetch()) {
        $existingColumns[] = $row['Field'];
    }

    if (!in_array('frais_inscription', $existingColumns)) {
        $pdo->exec("ALTER TABLE classes ADD COLUMN frais_inscription DECIMAL(15,2) NOT NULL DEFAULT 0.00");
        echo "  - Colonne 'frais_inscription' ajoutée.\n";
    }
    if (!in_array('frais_scolarite_brut', $existingColumns)) {
        $pdo->exec("ALTER TABLE classes ADD COLUMN frais_scolarite_brut DECIMAL(15,2) NOT NULL DEFAULT 0.00");
        echo "  - Colonne 'frais_scolarite_brut' ajoutée.\n";
    }
    if (!in_array('nbr_tranches', $existingColumns)) {
        $pdo->exec("ALTER TABLE classes ADD COLUMN nbr_tranches INT NOT NULL DEFAULT 0");
        echo "  - Colonne 'nbr_tranches' ajoutée.\n";
    }

    // 2. Création de class_installments
    echo "Création de la table 'class_installments'...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS class_installments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_id INT NOT NULL,
        installment_number INT NOT NULL,
        amount DECIMAL(15,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
        UNIQUE KEY uniq_class_inst (class_id, installment_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    // 3. Création de enrollments
    echo "Création de la table 'enrollments'...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS enrollments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        class_id INT NOT NULL,
        academic_year_id INT NOT NULL,
        frais_scolarite_brut DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        total_reductions DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        total_bourses DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        total_paye DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        reste_a_payer DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
        FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
        UNIQUE KEY uniq_student_year (student_id, academic_year_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    // 4. Création de student_installments
    echo "Création de la table 'student_installments'...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_installments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        academic_year_id INT NOT NULL,
        installment_number INT NOT NULL,
        amount_planned DECIMAL(15,2) NOT NULL,
        amount_paid DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
        UNIQUE KEY uniq_stud_inst (student_id, academic_year_id, installment_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    // 5. Création de student_discounts
    echo "Création de la table 'student_discounts'...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_discounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        amount DECIMAL(15,2) NOT NULL,
        amount_type ENUM('fixed', 'percentage') NOT NULL,
        motive VARCHAR(255) NOT NULL,
        date_effet DATE NOT NULL,
        status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
        commentaire TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    // 6. Création de class_discounts
    echo "Création de la table 'class_discounts'...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS class_discounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_id INT NOT NULL,
        amount DECIMAL(15,2) NOT NULL,
        amount_type ENUM('fixed', 'percentage') NOT NULL,
        motive VARCHAR(255) NOT NULL,
        date_effet DATE NOT NULL,
        status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
        commentaire TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    // 7. Création de student_scholarships
    echo "Création de la table 'student_scholarships'...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_scholarships (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        amount DECIMAL(15,2) NOT NULL,
        amount_type ENUM('fixed', 'percentage') NOT NULL,
        motive VARCHAR(255) NOT NULL,
        date_effet DATE NOT NULL,
        status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
        commentaire TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    // 8. Création de class_scholarships
    echo "Création de la table 'class_scholarships'...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS class_scholarships (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_id INT NOT NULL,
        amount DECIMAL(15,2) NOT NULL,
        amount_type ENUM('fixed', 'percentage') NOT NULL,
        motive VARCHAR(255) NOT NULL,
        date_effet DATE NOT NULL,
        status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
        commentaire TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    // 9. Création de payments
    echo "Création de la table 'payments'...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        academic_year_id INT NOT NULL,
        amount DECIMAL(15,2) NOT NULL,
        type ENUM('inscription', 'scolarite') NOT NULL,
        payment_date DATE NOT NULL,
        payment_method VARCHAR(50) NOT NULL DEFAULT 'CASH',
        reference VARCHAR(100) DEFAULT NULL,
        commentaire TEXT DEFAULT NULL,
        created_by INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    // 10. Création de financial_history
    echo "Création de la table 'financial_history'...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS financial_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT DEFAULT NULL,
        event_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        entity_type VARCHAR(50) NOT NULL,
        entity_id INT NOT NULL,
        action VARCHAR(50) NOT NULL,
        old_value TEXT DEFAULT NULL,
        new_value TEXT DEFAULT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    // 11. Migration des données élèves existantes vers la table enrollments
    echo "Migration des élèves existants vers 'enrollments'...\n";
    $stmt = $pdo->query("SELECT id, class_id, academic_year_id FROM students WHERE class_id IS NOT NULL AND academic_year_id IS NOT NULL");
    $migratedCount = 0;
    while ($student = $stmt->fetch()) {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = ? AND academic_year_id = ?");
        $checkStmt->execute([$student['id'], $student['academic_year_id']]);
        if ((int)$checkStmt->fetchColumn() === 0) {
            $insert = $pdo->prepare("INSERT INTO enrollments (student_id, class_id, academic_year_id, frais_scolarite_brut, total_reductions, total_bourses, total_paye, reste_a_payer) VALUES (?, ?, ?, 0, 0, 0, 0, 0)");
            $insert->execute([$student['id'], $student['class_id'], $student['academic_year_id']]);
            $migratedCount++;
        }
    }
    echo "  - $migratedCount élèves inscrits migrés.\n";

    echo "=== MIGRATION FINANCIÈRE TERMINÉE AVEC SUCCÈS ===\n";

} catch (\Exception $e) {
    echo "ERREUR DE MIGRATION : " . $e->getMessage() . "\n";
    exit(1);
}
