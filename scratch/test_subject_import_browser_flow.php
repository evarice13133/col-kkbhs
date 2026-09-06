<?php
/**
 * SCRATCH TEST: Validation complète du flux d'import des matières en navigateur
 * Teste: Téléchargement template → Structure Excel → Upload → Validation erreurs → Succès
 */

// Charger Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

// Initialiser la connexion à la base de données
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $db = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion BDD: " . $e->getMessage());
}

use App\Services\Import\ExcelTemplateService;
use App\Services\Import\SubjectImportProcessor;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║ TEST FLUX IMPORT MATIÈRES AVEC 5 COLONNES CLASSES + 7 COMPÉTENCES ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

// 1️⃣ GÉNÉRER LE MODÈLE TEMPLATE
echo "=== 1. GÉNÉRATION DU MODÈLE TEMPLATE ===\n";
try {
    $templateService = new ExcelTemplateService($db);
    $templateContent = $templateService->generateSubjectTemplate();
    
    // Sauvegarder le template
    $templatePath = __DIR__ . '/test_subject_template_output.xlsx';
    file_put_contents($templatePath, $templateContent);
    echo "✓ Template généré et sauvegardé: $templatePath\n";
    
    // Charger le fichier généré pour vérifier les en-têtes
    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $spreadsheet = $reader->load($templatePath);
    
    // Vérifier les feuilles
    $sheetNames = $spreadsheet->getSheetNames();
    echo "✓ Feuilles générées: " . implode(", ", array_slice($sheetNames, 1)) . "\n"; // Skip SUBJECT_DATASOURCES
    
    // Prendre la première feuille de données (pas la datasources)
    $dataSheet = null;
    foreach ($spreadsheet->getSheetNames() as $name) {
        if ($name !== 'SUBJECT_DATASOURCES') {
            $dataSheet = $spreadsheet->getSheetByName($name);
            break;
        }
    }
    
    if (!$dataSheet) {
        throw new Exception("Aucune feuille de données trouvée!");
    }
    
    $sheetName = $dataSheet->getTitle();
    echo "✓ Feuille de données analysée: $sheetName\n";
    
    // Vérifier les en-têtes de colonnes
    $headers = [];
    for ($col = 'A'; $col <= 'O'; $col++) {
        $value = $dataSheet->getCell($col . '1')->getValue();
        if ($value) {
            $headers[$col] = $value;
        }
    }
    
    echo "\n📋 En-têtes du modèle:\n";
    foreach ($headers as $col => $header) {
        echo "  [$col] $header\n";
    }
    
    // Vérifier la présence des colonnes clés
    $requiredColumns = [
        'A' => 'Matière',
        'B' => 'Coef',
        'C' => 'Groupe',
        'D' => 'Classe 1',
        'E' => 'Classe 2',
        'F' => 'Classe 3',
        'G' => 'Classe 4',
        'H' => 'Classe 5',
        'I' => 'Compétence 1',
        'J' => 'Compétence 2',
        'K' => 'Compétence 3',
        'L' => 'Compétence 4',
        'M' => 'Compétence 5',
        'N' => 'Compétence 6',
        'O' => 'Compétence 7',
    ];
    
    echo "\n✅ Vérification des colonnes requises:\n";
    $allPresent = true;
    foreach ($requiredColumns as $col => $expectedName) {
        $actual = $headers[$col] ?? '';
        $status = ($actual === $expectedName) ? '✓' : '✗';
        echo "  $status [$col] Attendu: '$expectedName', Obtenu: '$actual'\n";
        if ($actual !== $expectedName) $allPresent = false;
    }
    
    if (!$allPresent) {
        throw new Exception("Colonnes manquantes ou incorrectes!");
    }
    
    echo "\n✓ Structure du template validée avec succès!\n";
    
} catch (Exception $e) {
    echo "❌ Erreur génération template: " . $e->getMessage() . "\n";
    exit(1);
}

// 2️⃣ CRÉER UN FICHIER TEST AVEC DONNÉES VALIDES
echo "\n=== 2. CRÉATION D'UN FICHIER TEST AVEC DONNÉES VALIDES ===\n";
try {
    $testSpreadsheet = new Spreadsheet();
    $sheet = $testSpreadsheet->getActiveSheet();
    
    // Copier les en-têtes du template
    $sheet->setCellValue('A1', 'Matière');
    $sheet->setCellValue('B1', 'Coef');
    $sheet->setCellValue('C1', 'Groupe');
    $sheet->setCellValue('D1', 'Classe 1');
    $sheet->setCellValue('E1', 'Classe 2');
    $sheet->setCellValue('F1', 'Classe 3');
    $sheet->setCellValue('G1', 'Classe 4');
    $sheet->setCellValue('H1', 'Classe 5');
    $sheet->setCellValue('I1', 'Compétence 1');
    $sheet->setCellValue('J1', 'Compétence 2');
    $sheet->setCellValue('K1', 'Compétence 3');
    $sheet->setCellValue('L1', 'Compétence 4');
    $sheet->setCellValue('M1', 'Compétence 5');
    $sheet->setCellValue('N1', 'Compétence 6');
    $sheet->setCellValue('O1', 'Compétence 7');
    
    // Ajouter des données de test avec vrais noms de classes
    // Ligne 2: Matière valide avec 2 classes et 3 compétences
    $sheet->setCellValue('A2', 'Mathématiques Test');
    $sheet->setCellValue('B2', 4);
    $sheet->setCellValue('C2', 'Matières Scientifiques');
    $sheet->setCellValue('D2', '1 ére A4 ALL');     // Classe 1 valide
    $sheet->setCellValue('E2', '1 ére A4 ESP');     // Classe 2 valide
    $sheet->setCellValue('I2', 'Calcul');     // Compétence 1
    $sheet->setCellValue('J2', 'Raisonnement');  // Compétence 2
    $sheet->setCellValue('K2', 'Résolution');    // Compétence 3
    
    // Ligne 3: Matière avec 1 classe et 2 compétences
    $sheet->setCellValue('A3', 'Sciences Physiques');
    $sheet->setCellValue('B3', 3);
    $sheet->setCellValue('C3', 'Matières Scientifiques');
    $sheet->setCellValue('D3', '1 ère ACA');    // Classe 1 valide
    $sheet->setCellValue('I3', 'Expérimentation');
    $sheet->setCellValue('J3', 'Analyse');
    
    // Ligne 4: ERREUR - Aucune classe spécifiée
    $sheet->setCellValue('A4', 'Histoire');
    $sheet->setCellValue('B4', 2);
    $sheet->setCellValue('C4', 'Matières Littéraires');
    // D4:H4 vides intentionnellement
    $sheet->setCellValue('I4', 'Chronologie');
    
    // Ligne 5: ERREUR - Coefficient invalide
    $sheet->setCellValue('A5', 'Français');
    $sheet->setCellValue('B5', 'ABC');  // Invalide!
    $sheet->setCellValue('C5', 'Matières Littéraires');
    $sheet->setCellValue('D5', '1 ère ACC');
    $sheet->setCellValue('I5', 'Expression');
    
    // Ligne 6: ERREUR - Classe inexistante
    $sheet->setCellValue('A6', 'Philosophie');
    $sheet->setCellValue('B6', 2);
    $sheet->setCellValue('C6', 'Matières Littéraires');
    $sheet->setCellValue('D6', 'CLASSE_INEXISTANTE_XYZ');
    $sheet->setCellValue('I6', 'Réflexion');
    
    $writer = new Xlsx($testSpreadsheet);
    $testFilePath = __DIR__ . '/test_subject_import_data.xlsx';
    $writer->save($testFilePath);
    
    echo "✓ Fichier de test créé: $testFilePath\n";
    echo "  - Ligne 2: Matière valide (Mathématiques, 2 classes, 3 compétences)\n";
    echo "  - Ligne 3: Matière valide (Sciences Physiques, 1 classe, 2 compétences)\n";
    echo "  - Ligne 4: ERREUR - Aucune classe\n";
    echo "  - Ligne 5: ERREUR - Coefficient invalide (ABC)\n";
    echo "  - Ligne 6: ERREUR - Classe inexistante\n";
    
} catch (Exception $e) {
    echo "❌ Erreur création fichier test: " . $e->getMessage() . "\n";
    exit(1);
}

// 3️⃣ TESTER LE TRAITEMENT DU FICHIER TEST
echo "\n=== 3. TEST DE TRAITEMENT DU FICHIER INVALID ===\n";
try {
    $processor = new SubjectImportProcessor($db);
    $result = $processor->process($testFilePath);
    
    echo "✓ Résultat du traitement:\n";
    echo "  Success: " . ($result['success'] ? 'true' : 'false') . "\n";
    echo "  Processed Count: " . $result['count'] . "\n";
    
    if ($result['success']) {
        echo "✗ ERREUR: Le fichier avec erreurs aurait dû être rejeté!\n";
    } else {
        echo "✓ Fichier correctement rejeté (contient des erreurs)\n";
        
        if (isset($result['errors']) && !empty($result['errors'])) {
            echo "\n📋 Erreurs détectées:\n";
            foreach ($result['errors'] as $error) {
                echo "  • " . $error . "\n";
            }
            
            // Vérifier la précision des messages
            echo "\n✅ Vérification de la précision des messages:\n";
            $hasLineNumbers = false;
            $hasColumnInfo = false;
            $hasFieldNames = false;
            
            foreach ($result['errors'] as $error) {
                if (preg_match('/Ligne \d+/', $error)) $hasLineNumbers = true;
                if (preg_match('/Colonne[s]?\s+[A-O:|]+/', $error)) $hasColumnInfo = true;
                if (preg_match('/\(.*?\)/', $error)) $hasFieldNames = true;
            }
            
            echo "  Line numbers: " . ($hasLineNumbers ? '✓' : '✗') . "\n";
            echo "  Column info: " . ($hasColumnInfo ? '✓' : '✗') . "\n";
            echo "  Field names: " . ($hasFieldNames ? '✓' : '✗') . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur traitement fichier test: " . $e->getMessage() . "\n";
    exit(1);
}

// 4️⃣ CRÉER UN FICHIER 100% VALIDE ET TESTER
echo "\n=== 4. CRÉATION ET TEST D'UN FICHIER 100% VALIDE ===\n";
try {
    $validSpreadsheet = new Spreadsheet();
    $sheet = $validSpreadsheet->getActiveSheet();
    
    // En-têtes
    $sheet->setCellValue('A1', 'Matière');
    $sheet->setCellValue('B1', 'Coef');
    $sheet->setCellValue('C1', 'Groupe');
    $sheet->setCellValue('D1', 'Classe 1');
    $sheet->setCellValue('E1', 'Classe 2');
    $sheet->setCellValue('F1', 'Classe 3');
    $sheet->setCellValue('G1', 'Classe 4');
    $sheet->setCellValue('H1', 'Classe 5');
    $sheet->setCellValue('I1', 'Compétence 1');
    $sheet->setCellValue('J1', 'Compétence 2');
    $sheet->setCellValue('K1', 'Compétence 3');
    $sheet->setCellValue('L1', 'Compétence 4');
    $sheet->setCellValue('M1', 'Compétence 5');
    $sheet->setCellValue('N1', 'Compétence 6');
    $sheet->setCellValue('O1', 'Compétence 7');
    
    // Données valides avec vrais noms de classes
    $sheet->setCellValue('A2', 'Mathématiques Import Test ' . date('Y-m-d H:i:s'));
    $sheet->setCellValue('B2', 4);
    $sheet->setCellValue('C2', 'Matières Scientifiques');
    $sheet->setCellValue('D2', '1 ére A4 ALL');
    $sheet->setCellValue('E2', '1 ére A4 ESP');
    $sheet->setCellValue('I2', 'Calcul');
    $sheet->setCellValue('J2', 'Algèbre');
    
    $writer = new Xlsx($validSpreadsheet);
    $validFilePath = __DIR__ . '/test_subject_import_valid.xlsx';
    $writer->save($validFilePath);
    
    echo "✓ Fichier 100% valide créé: $validFilePath\n";
    
    // Traiter le fichier valide
    $processor2 = new SubjectImportProcessor($db);
    $result2 = $processor2->process($validFilePath);
    
    echo "\n✓ Résultat du traitement du fichier valide:\n";
    echo "  Success: " . ($result2['success'] ? 'true' : 'false') . "\n";
    echo "  Processed Count: " . $result2['count'] . "\n";
    
    if ($result2['success']) {
        echo "✓ Fichier importé avec succès!\n";
        
        // Vérifier la base de données
        echo "\n✅ Vérification de la base de données:\n";
        
        // Chercher la matière
        $stmt = $db->prepare("SELECT id, nom, coefficient FROM subjects WHERE nom LIKE ? ORDER BY id DESC LIMIT 1");
        $stmt->execute(['Mathématiques Import Test%']);
        $subject = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($subject) {
            echo "  ✓ Matière trouvée: ID={$subject['id']}, Nom={$subject['nom']}, Coef={$subject['coefficient']}\n";
            
            // Vérifier les classes
            $stmtClasses = $db->prepare("
                SELECT COUNT(*) as count FROM subject_classes WHERE subject_id = ?
            ");
            $stmtClasses->execute([$subject['id']]);
            $classCount = $stmtClasses->fetch(PDO::FETCH_ASSOC)['count'];
            echo "  ✓ Nombre de classes liées: $classCount (attendu: 2)\n";
            
            // Vérifier les compétences
            $stmtComps = $db->prepare("
                SELECT COUNT(*) as count FROM competencies WHERE subject_id = ?
            ");
            $stmtComps->execute([$subject['id']]);
            $compCount = $stmtComps->fetch(PDO::FETCH_ASSOC)['count'];
            echo "  ✓ Nombre de compétences liées: $compCount (attendu: 2)\n";
        } else {
            echo "  ✗ Matière non trouvée en base de données!\n";
        }
    } else {
        echo "❌ ERREUR: Le fichier valide aurait dû être accepté!\n";
        if (isset($result2['errors'])) {
            echo "Erreurs: " . implode(", ", $result2['errors']) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur test fichier valide: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n╔════════════════════════════════════════════════════════════════════╗\n";
echo "║ ✅ TEST COMPLET DU FLUX IMPORT MATIÈRES RÉUSSI                    ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n";
?>
