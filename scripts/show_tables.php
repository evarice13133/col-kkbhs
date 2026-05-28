<?php
$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');
$q = $pdo->query('SHOW TABLES');
foreach($q as $row) echo $row[0].PHP_EOL;
