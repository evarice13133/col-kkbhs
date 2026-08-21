<?php
/**
 * SCRATCH TEST: Validation complète de l'alignement bidirectionnel Export <-> Import des matières.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Services\Import\ExcelTemplateService;
use App\Services\Import\SubjectImportProcessor;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    $db = Database::getInstance()->getConnection();
    echo "=== 1. VERIFICATION DU GENERATEUR DE MODELE EXCEL ===\n";

    $templateSvc = new ExcelTemplateService($db);
    $templateContent = $templateSvc->generateSubjectTemplate('fr');

    $tmpTemplatePath = __DIR__ . '/test_template_output.xlsx';
    file_put_contents($tmpTemplatePath, $templateContent);

    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
    $tplSpreadsheet = $reader->load($tmpTemplatePath);
    $visibleSheet = null;
    foreach ($tplSpreadsheet->getAllSheets() as $sh) {
        if ($sh->getSheetState() === \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VISIBLE) {
            $visibleSheet = $sh;
            break;
        }
    }

    if (!$visibleSheet) {
        throw new Exception("Aucune feuille visible trouvée dans le modèle généré !");
    }

    $headerA1 = $visibleSheet->getCell('A1')->getValue();
    $headerB1 = $visibleSheet->getCell('B1')->getValue();
    $headerC1 = $visibleSheet->getCell('C1')->getValue();
    $headerD1 = $visibleSheet->getCell('D1')->getValue();
    $headerE1 = $visibleSheet->getCell('E1')->getValue();
    $headerF1 = $visibleSheet->getCell('F1')->getValue();
    $headerG1 = $visibleSheet->getCell('G1')->getValue();
    $headerH1 = $visibleSheet->getCell('H1')->getValue();

    echo "En-têtes du modèle généré : A1='$headerA1', B1='$headerB1', C1='$headerC1', D1='$headerD1', E1='$headerE1', F1='$headerF1', G1='$headerG1', H1='$headerH1'\n";

    if ($headerA1 !== 'Matière' || $headerD1 !== 'Classes concernées' || $headerE1 !== 'VHm' || $headerH1 !== 'Observations') {
        throw new Exception("Structure des en-têtes du modèle d'import non-conforme !");
    }
    echo "✓ Modèle d'import conforme aux spécifications (8 colonnes).\n";

    echo "\n=== 2. CREATION DE CLASSES ET GROUPS DE TEST ===\n";
    $classes = $db->query("SELECT id, nom FROM classes LIMIT 2")->fetchAll(PDO::FETCH_ASSOC);
    if (count($classes) < 2) {
        $db->exec("INSERT INTO classes (nom) VALUES ('Classe Roundtrip 1'), ('Classe Roundtrip 2')");
        $classes = $db->query("SELECT id, nom FROM classes ORDER BY id DESC LIMIT 2")->fetchAll(PDO::FETCH_ASSOC);
    }
    $c1Name = $classes[0]['nom'];
    $c2Name = $classes[1]['nom'];

    $db->exec("DELETE FROM subjects WHERE nom LIKE 'ROUNDTRIP_%'");

    echo "\n=== 3. GENERATION D'UN FICHIER AU FORMAT EXPORT (.XLSX) ===\n";
    $exportSpreadsheet = new Spreadsheet();
    $expSheet = $exportSpreadsheet->getActiveSheet();
    $expSheet->setTitle('Matières');

    $expHeaders = [
        'A1' => 'Matière',
        'B1' => 'Coef',
        'C1' => 'Groupe',
        'D1' => 'Classes concernées',
        'E1' => 'VHm',
        'F1' => 'VHp',
        'G1' => 'TH(Max)',
        'H1' => 'Observations'
    ];
    foreach ($expHeaders as $cell => $val) {
        $expSheet->setCellValue($cell, $val);
    }

    // Ligne 2 : Matière avec tous les champs et plusieurs classes
    $expSheet->setCellValue('A2', 'ROUNDTRIP_Algorithmique');
    $expSheet->setCellValue('B2', 3);
    $expSheet->setCellValue('C2', 'Groupe 1');
    $expSheet->setCellValue('D2', "$c1Name, $c2Name");
    $expSheet->setCellValue('E2', 60.0);
    $expSheet->setCellValue('F2', 54.0);
    $expSheet->setCellValue('G2', 30.0);
    $expSheet->setCellValue('H2', 'Cours renforcé initiale');

    // Ligne 3 : Matière avec champs optionnels omis
    $expSheet->setCellValue('A3', 'ROUNDTRIP_Anglais');
    $expSheet->setCellValue('B3', 2);
    $expSheet->setCellValue('C3', 'Groupe 1');
    $expSheet->setCellValue('D3', $c1Name);
    $expSheet->setCellValue('E3', 45.0);
    $expSheet->setCellValue('F3', '');
    $expSheet->setCellValue('G3', '');
    $expSheet->setCellValue('H3', '');

    // Ligne 4 : Total VHm (doit être ignorée lors de l'import)
    $expSheet->setCellValue('A4', 'TOTAL VHm');
    $expSheet->setCellValue('E4', '=SUM(E2:E3)');

    $tmpExportPath = __DIR__ . '/test_export_input.xlsx';
    $writer = new Xlsx($exportSpreadsheet);
    $writer->save($tmpExportPath);

    echo "✓ Fichier de simulation d'export créé : $tmpExportPath\n";

    echo "\n=== 4. TEST DE L'IMPORTATION DU FICHIER D'EXPORT ===\n";
    $processor = new SubjectImportProcessor($db);
    $result = $processor->process($tmpExportPath);

    echo "Résultat de l'import initial : Success=" . ($result['success'] ? 'true' : 'false') . ", Processed Count=" . $result['count'] . "\n";
    if (!empty($result['errors'])) {
        echo "Erreurs relevées :\n";
        foreach ($result['errors'] as $err) {
            echo "  - $err\n";
        }
        throw new Exception("L'importation du fichier d'export d'origine a échoué !");
    }

    // Vérifier les données insérées en BDD
    $stmt1 = $db->prepare("SELECT s.*, GROUP_CONCAT(c.nom SEPARATOR ', ') as classes_list FROM subjects s JOIN subject_classes sc ON s.id = sc.subject_id JOIN classes c ON sc.class_id = c.id WHERE s.nom = ? GROUP BY s.id");
    $stmt1->execute(['ROUNDTRIP_Algorithmique']);
    $sub1 = $stmt1->fetch(PDO::FETCH_ASSOC);

    echo "Vérification BDD - Matière 1 : Nom='{$sub1['nom']}', VHm={$sub1['vhm']}, VHp={$sub1['vhp']}, TH(Max)={$sub1['th_max']}, Obs='{$sub1['observations']}', Classes='{$sub1['classes_list']}'\n";

    if ((float) $sub1['vhm'] !== 60.0 || (float) $sub1['vhp'] !== 54.0 || (float) $sub1['th_max'] !== 30.0 || $sub1['observations'] !== 'Cours renforcé initiale') {
        throw new Exception("Les champs optionnels insérés ne correspondent pas aux valeurs attendues !");
    }

    echo "\n=== 5. TEST MODIFICATION DU FICHIER ET RE-IMPORT (UPSERT) ===\n";
    // Modifier les valeurs de ROUNDTRIP_Algorithmique dans le fichier d'export
    $expSheet->setCellValue('E2', 80.0); // Nouveau VHm
    $expSheet->setCellValue('F2', 72.0); // Nouveau VHp
    $expSheet->setCellValue('H2', 'Cours mis à jour via re-importation');

    $writer->save($tmpExportPath);

    $processor2 = new SubjectImportProcessor($db);
    $result2 = $processor2->process($tmpExportPath);

    echo "Résultat du re-import : Success=" . ($result2['success'] ? 'true' : 'false') . ", Processed Count=" . $result2['count'] . "\n";
    if (!empty($result2['errors'])) {
        echo "Erreurs au re-import :\n";
        foreach ($result2['errors'] as $err) {
            echo "  - $err\n";
        }
        throw new Exception("Le re-import après modification a échoué !");
    }

    // Vérifier l'absence de doublons
    $countSub = (int) $db->query("SELECT COUNT(*) FROM subjects WHERE nom LIKE 'ROUNDTRIP_%'")->fetchColumn();
    echo "Nombre total de matières en BDD : $countSub (Attendu: 2)\n";
    if ($countSub !== 2) {
        throw new Exception("Des doublons de matières ont été créés lors du re-import !");
    }

    $stmt1Updated = $db->prepare("SELECT s.* FROM subjects s WHERE s.nom = ?");
    $stmt1Updated->execute(['ROUNDTRIP_Algorithmique']);
    $sub1Up = $stmt1Updated->fetch(PDO::FETCH_ASSOC);

    echo "Vérification BDD après modification : VHm={$sub1Up['vhm']}, VHp={$sub1Up['vhp']}, Obs='{$sub1Up['observations']}'\n";
    if ((float) $sub1Up['vhm'] !== 80.0 || (float) $sub1Up['vhp'] !== 72.0 || $sub1Up['observations'] !== 'Cours mis à jour via re-importation') {
        throw new Exception("Les données modifiées n'ont pas été correctement mises à jour en BDD lors du re-import !");
    }

    echo "\n=== 6. TEST DE DETECTON ET RAPPORT D'ERREURS (DONNEES INVALIDES) ===\n";
    $badSpreadsheet = new Spreadsheet();
    $badSheet = $badSpreadsheet->getActiveSheet();
    $badSheet->setTitle('Matières');
    $badSheet->setCellValue('A1', 'Matière');
    $badSheet->setCellValue('B1', 'Coef');
    $badSheet->setCellValue('C1', 'Groupe');
    $badSheet->setCellValue('D1', 'Classes concernées');
    $badSheet->setCellValue('E1', 'VHm');

    // Ligne 2 : VHm négatif
    $badSheet->setCellValue('A2', 'TEST_ERR_1');
    $badSheet->setCellValue('B2', 2);
    $badSheet->setCellValue('C2', 'Groupe 1');
    $badSheet->setCellValue('D2', $c1Name);
    $badSheet->setCellValue('E2', -15);

    // Ligne 3 : Classe inexistante
    $badSheet->setCellValue('A3', 'TEST_ERR_2');
    $badSheet->setCellValue('B3', 2);
    $badSheet->setCellValue('C3', 'Groupe 1');
    $badSheet->setCellValue('D3', 'ClasseInexistanteXYZ');
    $badSheet->setCellValue('E3', 40);

    $tmpBadPath = __DIR__ . '/test_bad_input.xlsx';
    $writerBad = new Xlsx($badSpreadsheet);
    $writerBad->save($tmpBadPath);

    $processorBad = new SubjectImportProcessor($db);
    $resultBad = $processorBad->process($tmpBadPath);

    echo "Résultat import invalide (Attendu success=false) : Success=" . ($resultBad['success'] ? 'true' : 'false') . ", Nb Erreurs=" . count($resultBad['errors']) . "\n";
    echo "Rapport d'erreurs généré :\n";
    foreach ($resultBad['errors'] as $err) {
        echo "  [Ligne+Colonne+Valeur+Problème] => $err\n";
    }

    if ($resultBad['success'] || count($resultBad['errors']) < 2) {
        throw new Exception("Le processeur d'import n'a pas détecté les erreurs ou n'a pas généré de rapport complet !");
    }
    echo "✓ Détection fine des erreurs validée.\n";

    // Nettoyage des fichiers et données temporaires
    $db->exec("DELETE FROM subjects WHERE nom LIKE 'ROUNDTRIP_%'");
    @unlink($tmpTemplatePath);
    @unlink($tmpExportPath);
    @unlink($tmpBadPath);

    echo "\n=== TOUS LES TESTS BIDIRECTIONNELS EXPORT <-> IMPORT ONT REUSSI ===\n";

} catch (Exception $e) {
    echo "ERREUR DE VALIDATION : " . $e->getMessage() . "\n";
    exit(1);
}
