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

    echo "=== 1. SEARCH FOR CLASS_ID 104, 105, 106, 107 IN ALL TABLES ===\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $target_class_ids = [104, 105, 106, 107];

    foreach ($tables as $table) {
        $cols = $pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($cols as $col) {
            if (strpos($col, 'class') !== false || strpos($col, 'classe') !== false) {
                foreach ($target_class_ids as $cid) {
                    $cnt = $pdo->query("SELECT COUNT(*) FROM `$table` WHERE `$col` = $cid")->fetchColumn();
                    if ($cnt > 0) {
                        echo "Table `$table`, Column `$col` has $cnt rows with class_id = $cid:\n";
                        $rows = $pdo->query("SELECT * FROM `$table` WHERE `$col` = $cid")->fetchAll();
                        print_r($rows);
                    }
                }
            }
        }
    }

    echo "\n=== 2. ACTIVITY LOGS FOR CLASSES DELETION / CREATION ===\n";
    if (in_array('activity_logs', $tables)) {
        $logs = $pdo->query("
            SELECT * FROM activity_logs 
            WHERE entity_type = 'class' OR route LIKE '%class%' OR metadata LIKE '%104%' OR metadata LIKE '%105%' OR metadata LIKE '%106%' OR metadata LIKE '%107%'
            ORDER BY id DESC LIMIT 30
        ")->fetchAll();
        echo "Found " . count($logs) . " relevant activity logs:\n";
        foreach ($logs as $l) {
            echo "[{$l['created_at']}] Event: {$l['event_type']} | Route: {$l['route']} | Metadata: {$l['metadata']}\n";
        }
    }

    echo "\n=== 3. AUTO_INCREMENT AND MAX ID FOR CLASSES ===\n";
    $maxId = $pdo->query("SELECT MAX(id) FROM classes")->fetchColumn();
    $status = $pdo->query("SHOW TABLE STATUS LIKE 'classes'")->fetch();
    echo "Max classes.id: $maxId, Auto_increment: {$status['Auto_increment']}\n";

    echo "\n=== 4. AUDIT ALL FOREIGN KEY CONSTRAINTS AND ORPHAN REFS IN DB ===\n";
    // Check tables referencing classes(id)
    $referencing_classes = [
        'students' => 'class_id',
        'subject_classes' => 'class_id',
        'teacher_assignments' => 'class_id',
        'class_installments' => 'class_id',
        'class_discounts' => 'class_id',
        'class_scholarships' => 'class_id',
        'enrollments' => 'class_id',
        'decisions_fin_annee' => 'class_id',
        'conseils_classe' => 'class_id',
        'historique_passages' => 'from_class_id',
    ];

    foreach ($referencing_classes as $tbl => $col) {
        $tbl_exists = $pdo->query("SHOW TABLES LIKE '$tbl'")->fetch();
        if ($tbl_exists) {
            $cols = $pdo->query("DESCRIBE `$tbl`")->fetchAll(PDO::FETCH_COLUMN);
            if (in_array($col, $cols)) {
                $orphans = $pdo->query("
                    SELECT t.`$col`, COUNT(*) as cnt 
                    FROM `$tbl` t 
                    LEFT JOIN classes c ON t.`$col` = c.id 
                    WHERE t.`$col` IS NOT NULL AND c.id IS NULL 
                    GROUP BY t.`$col`
                ")->fetchAll();
                if (!empty($orphans)) {
                    echo "ORPHANS IN `$tbl`.`$col`: \n";
                    print_r($orphans);
                } else {
                    echo "OK: `$tbl`.`$col` has no orphans referencing classes(id).\n";
                }
            }
        }
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
