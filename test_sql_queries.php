<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';

$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);

echo "=== TESTING EXACT SQL QUERIES ===\n\n";

$classes_ids = [15, 16];

echo "1. Testing basic SELECT:\n";
$stmt = $db->prepare("SELECT id, nom, teaching_type_id, teaching_form_id FROM classes WHERE id IN (?, ?)");
$stmt->execute($classes_ids);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "   Found: " . count($result) . " classes\n";
foreach ($result as $r) {
  echo "   - ID=" . $r['id'] . ", name=" . $r['nom'] . "\n";
}

echo "\n2. Testing with proper filtering (TT=3, TF=2):\n";
$stmt = $db->prepare("SELECT id, nom, teaching_type_id, teaching_form_id FROM classes WHERE id IN (?, ?) AND teaching_type_id = 3 AND teaching_form_id = 2");
$stmt->execute($classes_ids);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "   Found: " . count($result) . " classes\n";
foreach ($result as $r) {
  echo "   - ID=" . $r['id'] . ", name=" . $r['nom'] . " [TT=" . $r['teaching_type_id'] . ", TF=" . $r['teaching_form_id'] . "]\n";
}

echo "\n3. Testing checksum to verify:\n";
$placeholders = implode(',', array_fill(0, count($classes_ids), '?'));
echo "   Placeholders: $placeholders\n";
echo "   Values: " . json_encode($classes_ids) . "\n";

echo "\n4. Testing duplicate check:\n";
$nom = 'DEBUG_POST_1788318787430';
$stmt = $db->prepare("
  SELECT DISTINCT c.nom FROM classes c
  LEFT JOIN subject_classes sc ON c.id = sc.class_id
  LEFT JOIN subjects s ON sc.subject_id = s.id AND s.nom = ?
  WHERE c.id IN ($placeholders) AND s.id IS NOT NULL
");
$stmt->execute(array_merge([$nom], $classes_ids));
$dupClasses = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "   Duplicate classes found: " . count($dupClasses) . "\n";
foreach ($dupClasses as $d) {
  echo "   - " . $d['nom'] . "\n";
}

?>
