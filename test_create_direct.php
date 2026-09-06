<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';

use App\Core\Session;
use App\Core\Database;
use PDO;

Session::start();

$_POST['csrf_token'] = Session::generateCsrfToken();
$_POST['nom'] = 'TEST_DIRECT_' . time();
$_POST['coefficient'] = '3';
$_POST['teaching_type_id'] = '3';
$_POST['teaching_form_id'] = '2';
$_POST['subject_group_id'] = '3';
$_POST['classes'] = ['1', '2']; // ID of 2 classes
$_POST['competencies'] = ['Compétence 1', 'Compétence 2'];
$_POST['vhm'] = '';
$_POST['vhp'] = '';
$_POST['th_max'] = '';
$_POST['observations'] = '';

echo "=== DIRECT SUBJECT CREATION TEST ===\n\n";
echo "Test Subject Name: " . $_POST['nom'] . "\n";
echo "POST Data:\n";
echo "  - Teaching Type: " . $_POST['teaching_type_id'] . "\n";
echo "  - Teaching Form: " . $_POST['teaching_form_id'] . "\n";
echo "  - Subject Group: " . $_POST['subject_group_id'] . "\n";
echo "  - Classes: " . implode(", ", $_POST['classes']) . "\n";
echo "  - Competencies: " . implode(", ", $_POST['competencies']) . "\n\n";

// Simulate the store method by including it
$_SERVER['REQUEST_METHOD'] = 'POST';

// Simulate PermissionManager check (bypass for testing)
class PermissionManager {
    public static function requirePermission($perm) {
        // Pass for testing
    }
}

$db = Database::getInstance()->getConnection();

echo "=== VALIDATION CHECKS ===\n\n";

// Check 1: nom and classes
$nom = trim($_POST['nom'] ?? '');
$classes_ids = array_values(array_unique(array_map('intval', $_POST['classes'] ?? [])));
echo "1. Nom and Classes: ";
if (empty($nom) || empty($classes_ids)) {
  echo "FAIL - nom empty or no classes\n";
  exit;
}
echo "PASS (nom=$nom, classes=" . count($classes_ids) . ")\n";

// Check 2: teaching_type_id
$teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;
echo "2. Teaching Type: ";
if (empty($teaching_type_id)) {
  echo "FAIL\n";
  exit;
}
$stmt = $db->prepare("SELECT id FROM teaching_types WHERE id = ? AND actif = 1");
$stmt->execute([$teaching_type_id]);
if (!$stmt->fetchColumn()) {
  echo "FAIL - not found or inactive\n";
  exit;
}
echo "PASS\n";

// Check 3: teaching_form_id
$teaching_form_id = !empty($_POST['teaching_form_id']) ? (int) $_POST['teaching_form_id'] : null;
echo "3. Teaching Form: ";
if (!empty($teaching_form_id)) {
  $stmt = $db->prepare("SELECT id FROM teaching_forms WHERE id = ? AND status = 1 AND teaching_type_id = ?");
  $stmt->execute([$teaching_form_id, $teaching_type_id]);
  if (!$stmt->fetchColumn()) {
    echo "FAIL - invalid for this type\n";
    exit;
  }
}
echo "PASS\n";

// Check 4: subject_group_id
$subject_group_id = !empty($_POST['subject_group_id']) ? (int) $_POST['subject_group_id'] : null;
echo "4. Subject Group: ";
if (empty($subject_group_id)) {
  echo "FAIL\n";
  exit;
}
$stmt = $db->prepare("SELECT id FROM subject_groups WHERE id = ? AND status = 1");
$stmt->execute([$subject_group_id]);
if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
  echo "FAIL\n";
  exit;
}
echo "PASS\n";

// Check 5: classes
echo "5. Classes Exist: ";
$placeholders = implode(',', array_fill(0, count($classes_ids), '?'));
$stmt = $db->prepare("SELECT id FROM classes WHERE id IN ($placeholders)");
$stmt->execute($classes_ids);
$found = $stmt->rowCount();
if ($found !== count($classes_ids)) {
  echo "FAIL - found $found of " . count($classes_ids) . "\n";
  exit;
}
echo "PASS\n";

// Check 6: duplicate classes
echo "6. Duplicate Check: ";
// This is the key check - does a subject with this name already exist in these classes?
$classPlaceholders = implode(',', array_fill(0, count($classes_ids), '?'));
$dupStmt = $db->prepare("
    SELECT DISTINCT c.nom
    FROM subjects s
    JOIN subject_classes sc ON s.id = sc.subject_id
    JOIN classes c ON sc.class_id = c.id
    WHERE s.nom = ? AND c.id IN ($classPlaceholders)
");
$dupParams = array_merge([$nom], $classes_ids);
$dupStmt->execute($dupParams);
$duplicates = $dupStmt->fetchAll(PDO::FETCH_COLUMN);

if (!empty($duplicates)) {
  echo "FAIL - Subject already exists in: " . implode(", ", $duplicates) . "\n";
  exit;
}
echo "PASS\n";

echo "\n✓ ALL CHECKS PASSED\n";
echo "Ready to create subject: " . $nom . "\n";
?>
