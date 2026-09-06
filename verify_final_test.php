<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';

$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);

echo "=== CHECKING FINAL_TEST SUBJECT ===\n\n";

$stmt = $db->query("SELECT id, nom, coefficient FROM subjects WHERE nom LIKE 'FINAL_TEST_%' ORDER BY id DESC LIMIT 1");
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
  echo "✓ FOUND FINAL_TEST SUBJECT:\n";
  echo "  ID: " . $result['id'] . "\n";
  echo "  Name: " . $result['nom'] . "\n";
  echo "  Coefficient: " . $result['coefficient'] . "\n";
  
  // Check competencies
  $cId = (int)$result['id'];
  $stmt = $db->query("SELECT COUNT(*) FROM competencies WHERE subject_id = $cId");
  $compCount = (int)$stmt->fetchColumn();
  echo "  Competencies: " . $compCount . "\n";
  
  // Check classes
  $stmt = $db->query("SELECT COUNT(*) FROM subject_classes WHERE subject_id = $cId");
  $classCount = (int)$stmt->fetchColumn();
  echo "  Classes linked: " . $classCount . "\n";
} else {
  echo "✗ FINAL_TEST SUBJECT NOT FOUND\n\n";
  echo "Last 3 subjects:\n";
  $stmt = $db->query("SELECT id, nom, coefficient FROM subjects ORDER BY id DESC LIMIT 3");
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  - ID=" . $row['id'] . ": " . $row['nom'] . "\n";
  }
}
?>
