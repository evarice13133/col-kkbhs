<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';

$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);

echo "=== FINDING VALID CLASS IDS ===\n\n";

// Get classes with TT=3, TF=2
$stmt = $db->query("SELECT id, nom FROM classes WHERE teaching_type_id = 3 AND teaching_form_id = 2 LIMIT 10");
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Classes with TT=3, TF=2:\n";
foreach ($classes as $c) {
  echo "  - ID=" . $c['id'] . " : " . $c['nom'] . "\n";
}

if (empty($classes)) {
  echo "\nNo classes found with TT=3 and TF=2\n";
  echo "\nAll classes with TT=3:\n";
  $stmt = $db->query("SELECT id, nom, teaching_form_id FROM classes WHERE teaching_type_id = 3 ORDER BY id LIMIT 10");
  while ($c = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  - ID=" . $c['id'] . " TF=" . $c['teaching_form_id'] . " : " . $c['nom'] . "\n";
  }
}
?>
