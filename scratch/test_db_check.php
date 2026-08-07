<?php
try {
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $dbs = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);
    echo "Databases found: " . implode(', ', $dbs) . "\n";
    if (in_array('u290233073_col_futura_db', $dbs)) {
        $pdo2 = new PDO('mysql:host=localhost;dbname=u290233073_col_futura_db', 'root', '');
        $tables = $pdo2->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        echo "Tables in u290233073_col_futura_db: " . implode(', ', $tables) . "\n";
    }
    if (in_array('u290233073_col_futura_db2', $dbs)) {
        $pdo3 = new PDO('mysql:host=localhost;dbname=u290233073_col_futura_db2', 'root', '');
        $tables2 = $pdo3->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        echo "Tables in u290233073_col_futura_db2: " . implode(', ', $tables2) . "\n";
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
