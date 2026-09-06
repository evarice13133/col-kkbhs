<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';

$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== FULL SUBJECT CREATION TEST ===\n\n";

$nom = 'FULL_TEST_' . time();
$coeff = 2;
$classes_ids = [15, 16];
$subject_group_id = 3;
$teaching_type_id = 3;
$teaching_form_id = 2;
$competencies = ['Compétence A', 'Compétence B'];

echo "Creating subject:\n";
echo "  nom=$nom\n";
echo "  coeff=$coeff\n";
echo "  classes=" . json_encode($classes_ids) . "\n";
echo "  subject_group_id=$subject_group_id\n\n";

try {
    // Start transaction
    $db->beginTransaction();
    echo "✓ Transaction started\n";
    
    // Get current academic year
    $stmt = $db->query("SELECT id FROM academic_years WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $academicYearId = $stmt->fetchColumn();
    echo "✓ Academic year: $academicYearId\n";
    
    if (!$academicYearId) {
        throw new Exception("No active academic year found");
    }
    
    // Create one subject row per class
    $createdSubjectIds = [];
    foreach ($classes_ids as $classId) {
        echo "\n  Creating subject for class $classId...\n";
        
        $stmt = $db->prepare("
            INSERT INTO subjects (
                nom, coefficient, subject_group_id, 
                teaching_type_id, teaching_form_id, groupe
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $nom, $coeff, $subject_group_id,
            $teaching_type_id, $teaching_form_id, 'Groupe 1'
        ]);
        
        $subjectId = $db->lastInsertId();
        $createdSubjectIds[] = $subjectId;
        echo "    ✓ Subject ID: $subjectId\n";
        
        // Link subject to class
        $stmt = $db->prepare("
            INSERT INTO subject_classes (subject_id, class_id, academic_year_id)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE academic_year_id = VALUES(academic_year_id)
        ");
        $stmt->execute([$subjectId, $classId, $academicYearId]);
        echo "    ✓ Linked to class $classId\n";
        
        // Create competencies for this subject instance
        foreach ($competencies as $index => $comp) {
            $stmt = $db->prepare("
                INSERT INTO competencies (subject_id, libelle, position, created_by)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$subjectId, $comp, $index + 1, 1]); // created_by = 1 (admin)
        }
        echo "    ✓ Created " . count($competencies) . " competencies\n";
    }
    
    // Commit transaction
    $db->commit();
    echo "\n✓ Transaction committed\n";
    echo "✓ Created " . count($createdSubjectIds) . " subject(s): " . json_encode($createdSubjectIds) . "\n";
    
    // Verify in database
    echo "\n=== VERIFICATION ===\n";
    $stmt = $db->prepare("SELECT id, nom, coefficient FROM subjects WHERE nom = ?");
    $stmt->execute([$nom]);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✓ Found " . count($subjects) . " subject records in database:\n";
    foreach ($subjects as $s) {
        echo "  - ID=" . $s['id'] . ": " . $s['nom'] . " (coeff=" . $s['coefficient'] . ")\n";
        
        // Check subject_classes
        $stmt = $db->prepare("SELECT class_id FROM subject_classes WHERE subject_id = ?");
        $stmt->execute([$s['id']]);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "    Classes linked: " . count($classes) . "\n";
        foreach ($classes as $c) {
            echo "      - class_id=" . $c['class_id'] . "\n";
        }
        
        // Check competencies
        $stmt = $db->prepare("SELECT libelle FROM competencies WHERE subject_id = ?");
        $stmt->execute([$s['id']]);
        $comps = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "    Competencies: " . count($comps) . "\n";
        foreach ($comps as $c) {
            echo "      - " . $c['libelle'] . "\n";
        }
    }
    
    echo "\n✓✓✓ CREATION SUCCESSFUL ✓✓✓\n";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "✗ Transaction rolled back\n";
    exit(1);
}

?>
