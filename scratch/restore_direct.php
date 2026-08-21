<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

$pdo = new PDO('mysql:host=localhost;dbname=u290233073_col_futura_db2;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = file_get_contents(__DIR__ . '/../db_prod.sql');

echo "File length: " . strlen($sql) . "\n";

$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

// Split by INSERT INTO
$queries = explode("INSERT INTO ", $sql);
echo "INSERT statement blocks: " . count($queries) . "\n";

foreach ($queries as $i => $q) {
    if ($i === 0) continue;
    $fullQuery = "INSERT INTO " . $q;
    // Extract up to semicolon
    $pos = strpos($fullQuery, ";");
    if ($pos !== false) {
        $singleQuery = substr($fullQuery, 0, $pos + 1);
        try {
            $pdo->exec($singleQuery);
        } catch (Exception $e) {
            echo "Error exec block $i: " . $e->getMessage() . "\n";
        }
    }
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

$subCount = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
echo "Restored Subjects count: $subCount\n";
