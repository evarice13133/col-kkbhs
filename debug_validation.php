<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';

$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== CHECKING CONTROLLER VALIDATION ISSUES ===\n\n";

// Vérifier que les IDs existent
echo "1. Checking if teaching_type_id=3 exists:\n";
$stmt = $db->query("SELECT id, nom FROM teaching_types WHERE id = 3 AND actif = 1");
$tt = $stmt->fetch(PDO::FETCH_ASSOC);
echo $tt ? "  ✓ FOUND: " . $tt['nom'] . "\n" : "  ✗ NOT FOUND\n";

echo "\n2. Checking if teaching_form_id=2 exists:\n";
$stmt = $db->query("SELECT id, nom FROM teaching_forms WHERE id = 2 AND status = 1 AND teaching_type_id = 3");
$tf = $stmt->fetch(PDO::FETCH_ASSOC);
echo $tf ? "  ✓ FOUND: " . $tf['nom'] . "\n" : "  ✗ NOT FOUND (need TT=3)\n";

echo "\n3. Checking if subject_group_id=3 exists:\n";
$stmt = $db->query("SELECT id, libelle FROM subject_groups WHERE id = 3 AND status = 1");
$sg = $stmt->fetch(PDO::FETCH_ASSOC);
echo $sg ? "  ✓ FOUND: " . $sg['libelle'] . "\n" : "  ✗ NOT FOUND\n";

echo "\n4. Checking if classes with TT=3, TF=2 exist:\n";
$stmt = $db->query("SELECT id, nom FROM classes WHERE teaching_type_id = 3 AND teaching_form_id = 2 LIMIT 5");
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "  Found " . count($classes) . " classes:\n";
foreach ($classes as $c) {
  echo "    - " . $c['nom'] . "\n";
}

echo "\n5. Checking if academic_year exists and is active:\n";
$stmt = $db->query("SELECT id, nom FROM academic_years WHERE is_active = 1 LIMIT 1");
$year = $stmt->fetch(PDO::FETCH_ASSOC);
echo $year ? "  ✓ ACTIVE YEAR: " . $year['nom'] . " (id=" . $year['id'] . ")\n" : "  ✗ NO ACTIVE YEAR\n";

echo "\n6. Checking tables exist:\n";
echo "  subjects: " . (checkTableExists($db, 'subjects') ? "✓" : "✗") . "\n";
echo "  subject_classes: " . (checkTableExists($db, 'subject_classes') ? "✓" : "✗") . "\n";
echo "  subject_group_assignments: " . (checkTableExists($db, 'subject_group_assignments') ? "✓" : "✗") . "\n";
echo "  competencies: " . (checkTableExists($db, 'competencies') ? "✓" : "✗") . "\n";

function checkTableExists($db, $table) {
  $result = $db->query("SHOW TABLES LIKE '$table'");
  return $result->rowCount() > 0;
}
?>
