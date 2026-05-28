<?php
$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');
$tables = ['grades', 'students', 'classes', 'subjects'];
foreach ($tables as $table) {
    echo "==== $table ====".PHP_EOL;
    $q = $pdo->query("SELECT academic_year_id, COUNT(*) as n FROM $table GROUP BY academic_year_id");
    foreach($q as $row) {
        echo "  academic_year_id=".$row['academic_year_id']." : ".$row['n'].PHP_EOL;
    }
}
