<?php
// Simule une session et un POST réel
require_once 'vendor/autoload.php';
require_once 'config/config.php';

// Simule la session
session_start();

// Crée un CSRF token valide
$_SESSION['csrf_token'] = hash('sha256', 'test_token_' . time());
$_POST['csrf_token'] = $_SESSION['csrf_token'];

// Données du POST capturées depuis le navigateur
$_POST = [
    'csrf_token' => $_SESSION['csrf_token'],
    'nom' => 'DEBUG_POST_1788318787430',
    'teaching_type_id' => '3',
    'teaching_form_id' => '2',
    'subject_group_id' => '3',
    'coefficient' => '2',
    'classes' => ['15', '16'],  // Les IDs capturées du navigateur
    'competencies' => ['Compétence A', 'Compétence B'],
    'vhm' => '',
    'vhp' => '',
    'th_max' => '',
    'observations' => '',
    'code_uv' => '',
    'code_ue' => ''
];

echo "=== SIMULATING POST REQUEST ===\n";
echo "nom: " . $_POST['nom'] . "\n";
echo "classes: " . json_encode($_POST['classes']) . "\n";
echo "teaching_type_id: " . $_POST['teaching_type_id'] . "\n";
echo "teaching_form_id: " . $_POST['teaching_form_id'] . "\n";
echo "subject_group_id: " . $_POST['subject_group_id'] . "\n\n";

// Crée une instance du controller
$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);

// Simule les validations du controller
$nom = trim($_POST['nom'] ?? '');
$classes_ids = array_values(array_unique(array_map('intval', $_POST['classes'] ?? [])));
$teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;
$teaching_form_id = !empty($_POST['teaching_form_id']) ? (int) $_POST['teaching_form_id'] : null;
$subject_group_id = !empty($_POST['subject_group_id']) ? (int) $_POST['subject_group_id'] : null;

echo "Parsed classes_ids: " . json_encode($classes_ids) . "\n\n";

// Test 1: Nom et classes
echo "✓ Test 1 - Nom and Classes: ";
if (empty($nom) || empty($classes_ids)) {
    echo "FAIL\n";
} else {
    echo "PASS\n";
}

// Test 2: Teaching Type
echo "✓ Test 2 - Teaching Type: ";
$stmtCheckTt = $db->prepare("SELECT id FROM teaching_types WHERE id = ? AND actif = 1");
$stmtCheckTt->execute([$teaching_type_id]);
if (!$stmtCheckTt->fetchColumn()) {
    echo "FAIL\n";
} else {
    echo "PASS\n";
}

// Test 3: Teaching Form
echo "✓ Test 3 - Teaching Form: ";
$stmtCheckTf = $db->prepare("SELECT id FROM teaching_forms WHERE id = ? AND status = 1 AND teaching_type_id = ?");
$stmtCheckTf->execute([$teaching_form_id, $teaching_type_id]);
if (!$stmtCheckTf->fetchColumn()) {
    echo "FAIL\n";
} else {
    echo "PASS\n";
}

// Test 4: Subject Group
echo "✓ Test 4 - Subject Group: ";
$stmtCheckGrp = $db->prepare("SELECT id, teaching_type_id, libelle FROM subject_groups WHERE id = ? AND status = 1");
$stmtCheckGrp->execute([$subject_group_id]);
$grpData = $stmtCheckGrp->fetch(PDO::FETCH_ASSOC);
if (!$grpData) {
    echo "FAIL\n";
} else {
    echo "PASS (group=" . $grpData['libelle'] . ")\n";
}

// Test 5: Classes Exist
echo "✓ Test 5 - Classes Exist: ";
$placeholders = implode(',', array_fill(0, count($classes_ids), '?'));
$stmtCheckClasses = $db->prepare("SELECT id, nom, teaching_type_id, teaching_form_id FROM classes WHERE id IN ($placeholders)");
$stmtCheckClasses->execute($classes_ids);
$fetchedClasses = $stmtCheckClasses->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($fetchedClasses) . " of " . count($classes_ids) . "\n";
if (count($fetchedClasses) !== count($classes_ids)) {
    echo "   FAIL - Missing classes!\n";
} else {
    echo "   PASS - All classes found\n";
    foreach ($fetchedClasses as $c) {
        echo "     - ID=" . $c['id'] . ": " . $c['nom'] . " [TT=" . $c['teaching_type_id'] . ", TF=" . $c['teaching_form_id'] . "]\n";
    }
}

echo "\n=== ALL VALIDATION PASSED? ===\n";
if (count($fetchedClasses) === count($classes_ids)) {
    echo "✓ YES - Ready for INSERT\n";
} else {
    echo "✗ NO - Validation failed\n";
}

?>
