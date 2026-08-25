<?php
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

    echo "=== COMPREHENSIVE DB FOREIGN KEY & ORPHAN AUDIT ===\n";

    // Fetch all foreign key constraints from INFORMATION_SCHEMA
    $fkQuery = "
        SELECT 
            TABLE_NAME, 
            COLUMN_NAME, 
            CONSTRAINT_NAME, 
            REFERENCED_TABLE_NAME, 
            REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
          AND REFERENCED_TABLE_NAME IS NOT NULL
    ";
    $fks = $pdo->query($fkQuery)->fetchAll();

    echo "Found " . count($fks) . " active foreign key constraints in schema.\n";
    foreach ($fks as $fk) {
        $tbl = $fk['TABLE_NAME'];
        $col = $fk['COLUMN_NAME'];
        $refTbl = $fk['REFERENCED_TABLE_NAME'];
        $refCol = $fk['REFERENCED_COLUMN_NAME'];

        $orphanQuery = "
            SELECT COUNT(*) FROM `$tbl` t
            LEFT JOIN `$refTbl` r ON t.`$col` = r.`$refCol`
            WHERE t.`$col` IS NOT NULL AND r.`$refCol` IS NULL
        ";
        $orphansCount = $pdo->query($orphanQuery)->fetchColumn();
        if ($orphansCount > 0) {
            echo "[FAIL FK CONSTRAINED] `$tbl`.`$col` -> `$refTbl`.`$refCol` has $orphansCount orphans!\n";
        }
    }

    echo "\n=== POTENTIAL UNCONSTRAINED FOREIGN KEYS (CONVENTION-BASED) ===\n";
    // Check all tables and columns ending with _id or starting with id_
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    // Map common ID conventions to parent tables
    $conventionMap = [
        'class_id' => ['table' => 'classes', 'col' => 'id'],
        'student_id' => ['table' => 'students', 'col' => 'id'],
        'academic_year_id' => ['table' => 'academic_years', 'col' => 'id'],
        'subject_id' => ['table' => 'subjects', 'col' => 'id'],
        'teacher_id' => ['table' => 'users', 'col' => 'id'],
        'user_id' => ['table' => 'users', 'col' => 'id'],
        'cycle_id' => ['table' => 'cycles', 'col' => 'id'],
        'section_id' => ['table' => 'sections', 'col' => 'id'],
        'department_id' => ['table' => 'departments', 'col' => 'id'],
        'teaching_type_id' => ['table' => 'teaching_types', 'col' => 'id'],
        'main_teacher_id' => ['table' => 'users', 'col' => 'id'],
        'created_by' => ['table' => 'users', 'col' => 'id'],
    ];

    foreach ($tables as $tbl) {
        $cols = $pdo->query("DESCRIBE `$tbl`")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($cols as $col) {
            if (isset($conventionMap[$col])) {
                $refTbl = $conventionMap[$col]['table'];
                $refCol = $conventionMap[$col]['col'];

                // Check if ref table exists
                if (in_array($refTbl, $tables)) {
                    $orphanQuery = "
                        SELECT COUNT(*) FROM `$tbl` t
                        LEFT JOIN `$refTbl` r ON t.`$col` = r.`$refCol`
                        WHERE t.`$col` IS NOT NULL AND r.`$refCol` IS NULL
                    ";
                    $orphansCount = $pdo->query($orphanQuery)->fetchColumn();
                    if ($orphansCount > 0) {
                        echo "[UNCONSTRAINED ORPHANS] `$tbl`.`$col` -> `$refTbl`.`$refCol`: $orphansCount orphans found!\n";
                        $orphanDetails = $pdo->query("
                            SELECT t.* FROM `$tbl` t
                            LEFT JOIN `$refTbl` r ON t.`$col` = r.`$refCol`
                            WHERE t.`$col` IS NOT NULL AND r.`$refCol` IS NULL
                            LIMIT 10
                        ")->fetchAll();
                        print_r($orphanDetails);
                    }
                }
            }
        }
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
