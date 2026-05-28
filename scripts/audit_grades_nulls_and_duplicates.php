<?php
$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');
// 1. Grades avec academic_year_id NULL ou vide
$q = $pdo->query("SELECT COUNT(*) as n FROM grades WHERE academic_year_id IS NULL OR academic_year_id = ''");
$row = $q->fetch();
echo "grades sans academic_year_id : ".$row['n'].PHP_EOL;
// 2. Doublons potentiels (student_id, subject_id, academic_year_id)
$q = $pdo->query("SELECT student_id, subject_id, academic_year_id, COUNT(*) as n FROM grades GROUP BY student_id, subject_id, academic_year_id HAVING n > 1");
echo "Doublons dans grades : ".($q->rowCount()).PHP_EOL;
foreach($q as $row) {
    echo "  student_id=".$row['student_id'].", subject_id=".$row['subject_id'].", academic_year_id=".$row['academic_year_id'].", n=".$row['n'].PHP_EOL;
}
