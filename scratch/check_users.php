<?php
require 'config/config.php';
$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS);
$stmt = $db->query('SELECT id, username FROM users WHERE id IN (1, 2, 3) LIMIT 5');
echo "Utilisateurs disponibles:\n";
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo '  ID: ' . $row['id'] . ' - Username: ' . $row['username'] . "\n";
}
?>
