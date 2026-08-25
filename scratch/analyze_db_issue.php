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

    echo "=== 1. CHECK CLASS_INSTALLMENTS STRUCTURE & DATA ===\n";
    $ci_exists = $pdo->query("SHOW TABLES LIKE 'class_installments'")->fetch();
    if ($ci_exists) {
        $stmt = $pdo->query("DESCRIBE class_installments");
        echo "class_installments columns:\n";
        print_r($stmt->fetchAll());

        $count = $pdo->query("SELECT COUNT(*) FROM class_installments")->fetchColumn();
        echo "class_installments total rows: $count\n";

        $rows = $pdo->query("SELECT * FROM class_installments LIMIT 50")->fetchAll();
        echo "class_installments sample rows:\n";
        print_r($rows);

        $keys = $pdo->query("SHOW KEYS FROM class_installments")->fetchAll();
        echo "class_installments keys:\n";
        print_r($keys);
    } else {
        echo "class_installments table DOES NOT EXIST.\n";
    }

    echo "\n=== 2. CHECK CLASSES STRUCTURE & DATA ===\n";
    $c_exists = $pdo->query("SHOW TABLES LIKE 'classes'")->fetch();
    if ($c_exists) {
        $stmt = $pdo->query("DESCRIBE classes");
        echo "classes columns:\n";
        print_r($stmt->fetchAll());

        $count = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
        echo "classes total rows: $count\n";

        $classIds = $pdo->query("SELECT id, nom FROM classes ORDER BY id")->fetchAll();
        echo "classes List (id => nom):\n";
        foreach ($classIds as $c) {
            echo "  ID {$c['id']}: {$c['nom']}\n";
        }
    } else {
        echo "classes table DOES NOT EXIST.\n";
    }

    echo "\n=== 3. ORPHANED CLASS_INSTALLMENTS ===\n";
    if ($ci_exists && $c_exists) {
        $orphans = $pdo->query("
            SELECT ci.* 
            FROM class_installments ci 
            LEFT JOIN classes c ON ci.class_id = c.id 
            WHERE c.id IS NULL
        ")->fetchAll();
        echo "Orphaned class_installments count: " . count($orphans) . "\n";
        print_r($orphans);

        $distinct_invalid_ids = $pdo->query("
            SELECT DISTINCT ci.class_id 
            FROM class_installments ci 
            LEFT JOIN classes c ON ci.class_id = c.id 
            WHERE c.id IS NULL
        ")->fetchAll(PDO::FETCH_COLUMN);
        echo "Invalid class_ids in class_installments: " . implode(', ', $distinct_invalid_ids) . "\n";
    }

    echo "\n=== 4. EXISTING FOREIGN KEYS ON CLASS_INSTALLMENTS ===\n";
    $fks = $pdo->query("
        SELECT 
            TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
          AND TABLE_NAME = 'class_installments' 
          AND REFERENCED_TABLE_NAME IS NOT NULL
    ")->fetchAll();
    print_r($fks);

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
