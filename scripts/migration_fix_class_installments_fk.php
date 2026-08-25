<?php
/**
 * Migration : Nettoyage complet des données orphelines et ajout de la contrainte FK sur class_installments
 * Fichier : scripts/migration_fix_class_installments_fk.php
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

    echo "=== DÉBUT DE LA MIGRATION FIX_CLASS_INSTALLMENTS_FK ===\n";

    $scratchDir = __DIR__ . '/../scratch';
    if (!is_dir($scratchDir)) {
        mkdir($scratchDir, 0777, true);
    }
    $backupFile = $scratchDir . '/orphans_backup_' . date('Ymd_His') . '.sql';
    $backupHandle = fopen($backupFile, 'w');

    fwrite($backupHandle, "-- SAUVEGARDE DES DONNÉES ORPHELINES AVANT NETTOYAGE - " . date('Y-m-d H:i:s') . "\n\n");

    // 1. Sauvegarde et nettoyage de class_installments
    $orphansCI = $pdo->query("
        SELECT ci.* FROM class_installments ci 
        LEFT JOIN classes c ON ci.class_id = c.id 
        WHERE c.id IS NULL
    ")->fetchAll();

    if (!empty($orphansCI)) {
        echo "Sauvegarde et suppression de " . count($orphansCI) . " orphelins dans 'class_installments'...\n";
        foreach ($orphansCI as $row) {
            $sql = "INSERT INTO `class_installments` (`id`, `class_id`, `installment_number`, `amount`, `created_at`) VALUES (" .
                (int)$row['id'] . ", " .
                (int)$row['class_id'] . ", " .
                (int)$row['installment_number'] . ", " .
                (float)$row['amount'] . ", " .
                $pdo->quote($row['created_at']) . ");\n";
            fwrite($backupHandle, $sql);
        }
        $pdo->exec("DELETE ci FROM class_installments ci LEFT JOIN classes c ON ci.class_id = c.id WHERE c.id IS NULL");
        echo "  - Lignes orphelines supprimées de 'class_installments'.\n";
    }

    // 2. Nettoyage exhaustif des tables annexes (classes, élèves, utilisateurs)
    $cleanups = [
        ['tbl' => 'fee_installments', 'col' => 'class_id', 'ref' => 'classes', 'ref_col' => 'id'],
        ['tbl' => 'installment_deadlines', 'col' => 'class_id', 'ref' => 'classes', 'ref_col' => 'id'],
        ['tbl' => 'school_fees', 'col' => 'class_id', 'ref' => 'classes', 'ref_col' => 'id'],
        ['tbl' => 'enrollments', 'col' => 'class_id', 'ref' => 'classes', 'ref_col' => 'id'],
        ['tbl' => 'enrollments', 'col' => 'student_id', 'ref' => 'students', 'ref_col' => 'id'],
        ['tbl' => 'student_installments', 'col' => 'student_id', 'ref' => 'students', 'ref_col' => 'id'],
        ['tbl' => 'timetables', 'col' => 'class_id', 'ref' => 'classes', 'ref_col' => 'id'],
        ['tbl' => 'timetables', 'col' => 'created_by', 'ref' => 'users', 'ref_col' => 'id'],
        ['tbl' => 'timetable_events', 'col' => 'teacher_id', 'ref' => 'users', 'ref_col' => 'id'],
        ['tbl' => 'timetable_entries', 'col' => 'teacher_id', 'ref' => 'users', 'ref_col' => 'id'],
        ['tbl' => 'timetable_audit_logs', 'col' => 'user_id', 'ref' => 'users', 'ref_col' => 'id'],
        ['tbl' => 'user_teaching_types', 'col' => 'user_id', 'ref' => 'users', 'ref_col' => 'id'],
    ];

    foreach ($cleanups as $c) {
        $tbl = $c['tbl'];
        $col = $c['col'];
        $ref = $c['ref'];
        $ref_col = $c['ref_col'];

        $tblExists = $pdo->query("SHOW TABLES LIKE '$tbl'")->fetch();
        if ($tblExists) {
            $orphans = $pdo->query("
                SELECT t.* FROM `$tbl` t 
                LEFT JOIN `$ref` r ON t.`$col` = r.`$ref_col` 
                WHERE t.`$col` IS NOT NULL AND r.`$ref_col` IS NULL
            ")->fetchAll();

            if (!empty($orphans)) {
                echo "Nettoyage de " . count($orphans) . " orphelins dans '$tbl'.'$col' -> '$ref'.'$ref_col'...\n";
                foreach ($orphans as $oRow) {
                    fwrite($backupHandle, "-- Orphan in $tbl.$col (ref $ref.$ref_col): " . json_encode($oRow) . "\n");
                }
                $pdo->exec("DELETE t FROM `$tbl` t LEFT JOIN `$ref` r ON t.`$col` = r.`$ref_col` WHERE t.`$col` IS NOT NULL AND r.`$ref_col` IS NULL");
            }
        }
    }

    fclose($backupHandle);
    echo "Sauvegarde des orphelins effectuée dans : $backupFile\n";

    // 3. Pose de la contrainte FK sur class_installments
    echo "Ajout de la contrainte FK 'class_installments_ibfk_1' sur 'class_installments'...\n";
    $checkFk = $pdo->query("
        SELECT CONSTRAINT_NAME 
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
          AND TABLE_NAME = 'class_installments' 
          AND CONSTRAINT_NAME = 'class_installments_ibfk_1'
    ")->fetch();

    if ($checkFk) {
        echo "  - La contrainte 'class_installments_ibfk_1' existe déjà.\n";
    } else {
        $pdo->exec("
            ALTER TABLE `class_installments`
            ADD CONSTRAINT `class_installments_ibfk_1`
            FOREIGN KEY (`class_id`)
            REFERENCES `classes` (`id`)
            ON DELETE CASCADE
        ");
        echo "  - Contrainte 'class_installments_ibfk_1' (FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE) ajoutée avec succès !\n";
    }

    echo "=== MIGRATION FIX_CLASS_INSTALLMENTS_FK TERMINÉE AVEC SUCCÈS ===\n";

} catch (Exception $e) {
    echo "ERREUR DE MIGRATION : " . $e->getMessage() . "\n";
    exit(1);
}
