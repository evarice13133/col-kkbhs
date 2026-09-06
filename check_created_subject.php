<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';

$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== CHECKING FOR CREATED SUBJECT ===\n\n";

$stmt = $db->query("SELECT id, nom, coefficient, subject_group_id FROM subjects WHERE nom LIKE 'MATIERE_COMPLETE_%' ORDER BY id DESC LIMIT 1");
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
  echo "✓ FOUND SUBJECT:\n";
  echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
  
  // Check for competencies
  $compStmt = $db->prepare("SELECT id, libelle FROM competencies WHERE subject_id = ? ORDER BY position");
  $compStmt->execute([$result['id']]);
  $competencies = $compStmt->fetchAll(PDO::FETCH_ASSOC);
  
  echo "\nCompetencies (" . count($competencies) . "):\n";
  foreach ($competencies as $comp) {
    echo "  - " . $comp['libelle'] . "\n";
  }
  
  // Check for class links
  $classStmt = $db->prepare("SELECT class_id, academic_year_id FROM subject_classes WHERE subject_id = ?");
  $classStmt->execute([$result['id']]);
  $classes = $classStmt->fetchAll(PDO::FETCH_ASSOC);
  
  echo "\nClass Links (" . count($classes) . "):\n";
  foreach ($classes as $cl) {
    echo "  - class_id=" . $cl['class_id'] . ", year_id=" . $cl['academic_year_id'] . "\n";
  }
  
} else {
  echo "✗ NOT FOUND - Last 5 subjects:\n\n";
  $stmt = $db->query("SELECT id, nom, coefficient FROM subjects ORDER BY id DESC LIMIT 5");
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
  }
}
?>
