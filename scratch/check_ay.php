<?php
require_once __DIR__ . '/../config/config.php';
$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);

echo "Academic Years IDs:\n";
$stmt = $pdo->query("SELECT id, nom FROM academic_years");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

echo "Unique Academic Year IDs in Students:\n";
$stmt = $pdo->query("SELECT DISTINCT academic_year_id, COUNT(*) as cnt FROM students GROUP BY academic_year_id");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
