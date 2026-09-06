<?php
require 'config/config.php';
$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS);
$stmt = $db->query('DESCRIBE classes');
echo "Colonnes de la table 'classes':\n";
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . ' (' . $row['Type'] . ")\n";
}
?>
