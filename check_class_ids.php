<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';

$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);

echo "=== CHECKING CLASS IDS 15 AND 16 ===\n\n";

$stmt = $db->prepare("SELECT id, nom, teaching_type_id, teaching_form_id FROM classes WHERE id IN (15, 16)");
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  echo "ID=" . $row['id'] . " : " . $row['nom'];
  echo " [TT=" . $row['teaching_type_id'] . ", TF=" . $row['teaching_form_id'] . "]\n";
}

echo "\n=== ALL CLASSES STARTING FROM ID 15 ===\n";
$stmt = $db->prepare("SELECT id, nom, teaching_type_id, teaching_form_id FROM classes WHERE id >= 15 ORDER BY id LIMIT 20");
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  echo "ID=" . $row['id'] . " : " . $row['nom'];
  echo " [TT=" . $row['teaching_type_id'] . ", TF=" . $row['teaching_form_id'] . "]\n";
}
?>
