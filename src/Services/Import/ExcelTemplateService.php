<?php

namespace App\Services\Import;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PDO;

/**
 * ExcelTemplateService
 * 
 * Gestionnaire de génération de modèles Excel intelligents pour l'importation.
 * Ce service crée un fichier Excel structuré avec des protections et des listes
 * de validation dynamiques tirées de la base de données.
 * 
 * @package App\Services\Import
 */
class ExcelTemplateService
{
    private PDO $db;

    /**
     * Constructeur
     * 
     * @param PDO $db Connexion à la base de données
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Génère un fichier template Excel (.xlsx) pour l'import des élèves.
     * 
     * @param string $lang Langue ('fr' ou 'en')
     * @return string Contenu du fichier Excel (binaire)
     */
    public function generateStudentTemplate(string $lang = 'fr'): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // On enlève la feuille par défaut
        
        // --- 1. RÉCUPÉRATION DES COMBINAISONS SECTION / CYCLE ---
        $combinations = $this->db->query("
            SELECT DISTINCT s.id as section_id, s.nom as section_nom, cy.id as cycle_id, cy.nom as cycle_nom
            FROM classes c
            JOIN sections s ON c.section_id = s.id
            JOIN cycles cy ON c.cycle_id = cy.id
            ORDER BY s.nom ASC, cy.nom ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // --- 2. FEUILLE CACHÉE POUR LES DONNÉES COMMUNES ---
        $dataSheet = $spreadsheet->createSheet();
        $dataSheet->setTitle('DATASOURCES');
        $dataSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VERYHIDDEN);
        
        // Sexes (A), Redoublant Labels (E)
        $this->fillCommonData($dataSheet, $lang);

        // --- 3. GÉNÉRATION DES FEUILLES PAR COMBINAISON ---
        foreach ($combinations as $idx => $comb) {
            $sheet = $spreadsheet->createSheet();
            
            // Titre de l'onglet: ex "FR - 1er Cycle"
            $title = strtoupper($comb['section_nom']) . " - " . $comb['cycle_nom'];
            // On s'assure que le titre ne dépasse pas 31 caractères (limite Excel)
            $sheet->setTitle(substr($title, 0, 31));

            // Récupération des classes pour cette combinaison précise
            $classes = $this->db->prepare("SELECT nom FROM classes WHERE section_id = ? AND cycle_id = ? ORDER BY nom ASC");
            $classes->execute([$comb['section_id'], $comb['cycle_id']]);
            $classList = $classes->fetchAll(PDO::FETCH_COLUMN);

            // Ajout des données de classes spécifiques à cette feuille dans DATASOURCES
            // On les met dans des colonnes éloignées ou on crée une colonne par feuille
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 10); // À partir de J
            $row = 1;
            foreach ($classList as $c) {
                $dataSheet->setCellValue($colLetter . ($row++), $c);
            }

            // Configuration de la feuille principale
            $headers = $this->getHeaders($lang);
            $this->applyHeaderStyles($sheet, $headers);
            
            // Validations
            $this->applySheetValidations($sheet, $dataSheet, $colLetter, count($classList), $lang);

            // Largeurs
            foreach(range('A','J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Ligne d'exemple
            $this->addSheetExampleRow($sheet, $classList, $lang);
        }

        // --- 4. GÉNÉRATION DU FLUX ---
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $content;
    }

    private function fillCommonData($sheet, $lang)
    {
        $sheet->setCellValue('A1', 'M');
        $sheet->setCellValue('A2', 'F');
        $sheet->setCellValue('E1', $lang === 'fr' ? 'OUI' : 'YES');
        $sheet->setCellValue('E2', $lang === 'fr' ? 'NON' : 'NO');
    }

    private function applySheetValidations($sheet, $dataSheet, $classCol, $classCount, $lang)
    {
        $rows = 1000;
        $valSexe = $this->createDropdown('DATASOURCES!$A$1:$A$2', __('choose_sex_hint'));
        $valRedoublant = $this->createDropdown('DATASOURCES!$E$1:$E$2', $lang === 'fr' ? 'Est-il redoublant ?' : 'Is repeating?');
        
        $classRange = "DATASOURCES!\${$classCol}\$1:\${$classCol}\$" . ($classCount ?: 1);
        $valClasse = $this->createDropdown($classRange, __('choose_class_hint'));

        for ($i = 2; $i <= $rows; $i++) {
            // Nouveau mapping : A=Nom, B=Prénom, C=Matricule, D=Sexe, E=Date, F=Lieu, G=Classe, H=Redoublant
            $sheet->getCell('D' . $i)->setDataValidation(clone $valSexe);
            $sheet->getCell('G' . $i)->setDataValidation(clone $valClasse);
            $sheet->getCell('H' . $i)->setDataValidation(clone $valRedoublant);
            // Forcer format texte sur Matricule et sur le couple Date/Lieu selon besoins
            $sheet->getStyle('C' . $i)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            $sheet->getStyle('E' . $i . ':F' . $i)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        }
    }

    private function addSheetExampleRow($sheet, $classList, $lang)
    {
        $sample = [
            'A' => 'Ndogmo',
            'B' => 'Evarice',
            'C' => 'MT-0001',
            'D' => 'M',
            'E' => ($lang === 'fr' ? '12/05/2012' : '2012-05-12'),
            'F' => ($lang === 'fr' ? 'Yaoundé' : 'Yaounde'),
            'G' => !empty($classList) ? $classList[0] : '---',
            'H' => $lang === 'fr' ? 'NON' : 'NO',
            'I' => '690000000',
            'J' => '690000001'
        ];
        foreach ($sample as $col => $val) {
            $sheet->setCellValue($col . '2', $val);
        }
        $sheet->getStyle('A2:J2')->getFont()->setItalic(true);
        $sheet->getStyle('A2:J2')->getFont()->getColor()->setRGB('6B7280');
    }

    /**
     * Définit les en-têtes selon la langue.
     */
    private function getHeaders(string $lang): array
    {
        if ($lang === 'en') {
             return [
                'A1' => 'Last Name',
                'B1' => 'First Name',
                'C1' => 'Student ID',
                'D1' => 'Gender (M/F)',
                'E1' => 'Date of Birth',
                'F1' => 'Place of Birth',
                'G1' => 'Class',
                'H1' => 'Repeating (YES/NO)',
                'I1' => 'Parent Contact',
                'J1' => 'Guardian Contact'
             ];
        }

        return [
            'A1' => 'Nom',
            'B1' => 'Prénom',
            'C1' => 'Matricule',
            'D1' => 'Sexe (M/F)',
            'E1' => 'Date de Naissance',
            'F1' => 'Lieu de Naissance',
            'G1' => 'Classe',
            'H1' => 'Redoublant (OUI/NON)',
            'I1' => 'Contact Père/Mère',
            'J1' => 'Contact Tuteur'
        ];
    }

    /**
     * Applique un style professionnel aux en-têtes.
     */
    private function applyHeaderStyles($sheet, array $headers)
    {
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $styleArray = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']]
        ];

        $sheet->getStyle('A1:G1')->applyFromArray($styleArray);
    }

    /**
     * Crée un objet de validation Dropdown.
     */
    private function createDropdown(string $range, string $hint): DataValidation
    {
        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle(__('input_error') ?? 'Erreur de saisie');
        $validation->setError($hint);
        $validation->setPromptTitle(__('select_option') ?? 'Choisir une option');
        $validation->setFormula1($range);
        return $validation;
    }

    /**
     * Modèle Excel premium pour import enseignants avec listes déroulantes.
     */
    public function generateTeacherTemplate(string $lang = 'fr'): string
    {
        $spreadsheet = new Spreadsheet();
        
        // --- 1. CONFIGURATION DES FEUILLES ---
        $mainSheet = $spreadsheet->getActiveSheet();
        $mainSheet->setTitle($lang === 'fr' ? 'Import Enseignants' : 'Teacher import');
        
        $dataSheet = $spreadsheet->createSheet();
        $dataSheet->setTitle('TEACHER_DATASOURCES');
        $dataSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VERYHIDDEN);

        // --- 2. EXTRACTION DES DONNÉES ---
        $departments = $this->db->query("SELECT nom FROM departments ORDER BY nom ASC")->fetchAll(PDO::FETCH_COLUMN);

        // Remplissage de la feuille de données
        // Colonne A: Départements
        $i = 1;
        foreach ($departments as $dept) $dataSheet->setCellValue('A' . ($i++), $dept);

        // --- 3. EN-TÊTES ET STYLES ---
        $headers = $lang === 'fr'
            ? ['A1' => 'Nom', 'B1' => 'Prenom', 'C1' => 'Username']
            : ['A1' => 'Last name', 'B1' => 'First name', 'C1' => 'Username'];

        foreach ($headers as $cell => $value) {
            $mainSheet->setCellValue($cell, $value);
        }

        $styleArray = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']]
        ];
        $mainSheet->getStyle('A1:C1')->applyFromArray($styleArray);

        // --- 5. LIGNE D'EXEMPLE ---
        $mainSheet->setCellValue('A2', 'DUPONT');
        $mainSheet->setCellValue('B2', 'Jean');
        $mainSheet->setCellValue('C2', 'j.dupont');

        $mainSheet->getStyle('A2:C2')->getFont()->setItalic(true);
        $mainSheet->getStyle('A2:C2')->getFont()->getColor()->setRGB('6B7280');

        foreach (range('A', 'C') as $col) {
            $mainSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // --- 6. GÉNÉRATION ---
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $content;
    }

    /**
     * Modèle Excel pour import des matières.
     */
    public function generateSubjectTemplate(string $lang = 'fr'): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // On enlève la feuille par défaut

        // Récupérer les types d'enseignement actifs
        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Feuille masquée pour les données de référence
        $dataSheet = $spreadsheet->createSheet();
        $dataSheet->setTitle('SUBJECT_DATASOURCES');
        $dataSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VERYHIDDEN);

        $i = 0;
        foreach ($teachingTypes as $tt) {
            $ttId = (int) $tt['id'];
            $ttNom = (string) $tt['nom'];

            // Cycles actifs associés à ce type d'enseignement
            $stmtCycles = $this->db->prepare("SELECT nom FROM cycles WHERE status = 1 AND (teaching_type_id = ? OR teaching_type_id IS NULL) ORDER BY nom ASC");
            $stmtCycles->execute([$ttId]);
            $cycleList = $stmtCycles->fetchAll(PDO::FETCH_COLUMN);

            // Départements actifs associés à ce type d'enseignement
            $stmtDepts = $this->db->prepare("SELECT nom FROM departments WHERE status = 1 AND (teaching_type_id = ? OR teaching_type_id IS NULL) ORDER BY nom ASC");
            $stmtDepts->execute([$ttId]);
            $deptList = $stmtDepts->fetchAll(PDO::FETCH_COLUMN);

            // Groupes de modules actifs associés à ce type d'enseignement
            $stmtGroups = $this->db->prepare("SELECT libelle FROM subject_groups WHERE status = 1 AND (teaching_type_id = ? OR teaching_type_id IS NULL) ORDER BY libelle ASC");
            $stmtGroups->execute([$ttId]);
            $groupList = $stmtGroups->fetchAll(PDO::FETCH_COLUMN);

            // Classes actives associées à ce type d'enseignement
            $stmtClasses = $this->db->prepare("
                SELECT c.nom 
                FROM classes c
                LEFT JOIN departments d ON c.department_id = d.id
                LEFT JOIN cycles cy ON c.cycle_id = cy.id
                LEFT JOIN sections sec ON c.section_id = sec.id
                WHERE c.teaching_type_id = ?
                  AND (c.department_id IS NULL OR d.status = 1)
                  AND (c.cycle_id IS NULL OR cy.status = 1)
                  AND (c.section_id IS NULL OR sec.status = 1)
                ORDER BY c.nom ASC
            ");
            $stmtClasses->execute([$ttId]);
            $classList = $stmtClasses->fetchAll(PDO::FETCH_COLUMN);

            // Écrire les listes dans la feuille cachée dans des colonnes distinctes
            // Colonne 1: Cycle, Colonne 2: Département, Colonne 3: Groupe, Colonne 4: Classe
            $colCycle = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(4 * $i + 1);
            $colDept = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(4 * $i + 2);
            $colGroup = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(4 * $i + 3);
            $colClass = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(4 * $i + 4);

            $row = 1;
            foreach ($cycleList as $val) {
                $dataSheet->setCellValue($colCycle . ($row++), (string) $val);
            }
            $row = 1;
            foreach ($deptList as $val) {
                $dataSheet->setCellValue($colDept . ($row++), (string) $val);
            }
            $row = 1;
            foreach ($groupList as $val) {
                $dataSheet->setCellValue($colGroup . ($row++), (string) $val);
            }
            $row = 1;
            foreach ($classList as $val) {
                $dataSheet->setCellValue($colClass . ($row++), (string) $val);
            }

            // Créer la feuille pour ce type d'enseignement
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle(substr($ttNom, 0, 31));

            // En-têtes de colonnes
            $headers = $lang === 'fr'
                ? [
                    'A1' => 'Matiere', 
                    'B1' => 'Cycle', 
                    'C1' => 'Departement', 
                    'D1' => 'Coefficient', 
                    'E1' => 'Groupe', 
                    'F1' => 'Classe 1', 
                    'G1' => 'Classe 2', 
                    'H1' => 'Classe 3', 
                    'I1' => 'Classe 4', 
                    'J1' => 'Classe 5', 
                    'K1' => 'Classe 6', 
                    'L1' => 'Classe 7', 
                    'M1' => 'Classe 8', 
                    'N1' => 'Classe 9', 
                    'O1' => 'Classe 10'
                ]
                : [
                    'A1' => 'Subject', 
                    'B1' => 'Cycle', 
                    'C1' => 'Department', 
                    'D1' => 'Coefficient', 
                    'E1' => 'Group', 
                    'F1' => 'Class 1', 
                    'G1' => 'Class 2', 
                    'H1' => 'Class 3', 
                    'I1' => 'Class 4', 
                    'J1' => 'Class 5', 
                    'K1' => 'Class 6', 
                    'L1' => 'Class 7', 
                    'M1' => 'Class 8', 
                    'N1' => 'Class 9', 
                    'O1' => 'Class 10'
                ];

            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            // Appliquer le style aux en-têtes
            $styleArray = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']]
            ];
            $sheet->getStyle('A1:O1')->applyFromArray($styleArray);

            $cycleCount = count($cycleList);
            $deptCount = count($deptList);
            $groupCount = count($groupList);
            $classCount = count($classList);

            // Validations sur 1000 lignes
            // Validation Cycle
            if ($cycleCount > 0) {
                $validationCycle = $this->createDropdown(
                    "SUBJECT_DATASOURCES!\${$colCycle}\$1:\${$colCycle}\$" . $cycleCount,
                    $lang === 'fr' ? 'Choisir un Cycle' : 'Choose a Cycle'
                );
                for ($row = 2; $row <= 1000; $row++) {
                    $sheet->getCell('B' . $row)->setDataValidation(clone $validationCycle);
                }
            }

            // Validation Département
            if ($deptCount > 0) {
                $validationDept = $this->createDropdown(
                    "SUBJECT_DATASOURCES!\${$colDept}\$1:\${$colDept}\$" . $deptCount,
                    $lang === 'fr' ? 'Choisir un Département' : 'Choose a Department'
                );
                for ($row = 2; $row <= 1000; $row++) {
                    $sheet->getCell('C' . $row)->setDataValidation(clone $validationDept);
                }
            }

            // Validation Groupe
            if ($groupCount > 0) {
                $validationGroup = $this->createDropdown(
                    "SUBJECT_DATASOURCES!\${$colGroup}\$1:\${$colGroup}\$" . $groupCount,
                    $lang === 'fr' ? 'Choisir un Groupe' : 'Choose a Group'
                );
                for ($row = 2; $row <= 1000; $row++) {
                    $sheet->getCell('E' . $row)->setDataValidation(clone $validationGroup);
                }
            }

            // Validation Classes (colonnes F à O)
            if ($classCount > 0) {
                $validationClasse = $this->createDropdown(
                    "SUBJECT_DATASOURCES!\${$colClass}\$1:\${$colClass}\$" . $classCount,
                    $lang === 'fr' ? 'Choisir une Classe' : 'Choose a Class'
                );
                for ($row = 2; $row <= 1000; $row++) {
                    foreach (range('F', 'O') as $colLetter) {
                        $sheet->getCell($colLetter . $row)->setDataValidation(clone $validationClasse);
                    }
                }
            }

            // Ligne d'exemple
            $exampleCycle = (string) ($cycleList[0] ?? ($lang === 'fr' ? '1er Cycle' : '1st Cycle'));
            $exampleDept = (string) ($deptList[0] ?? ($lang === 'fr' ? 'Sciences' : 'Sciences'));
            $exampleGroup = (string) ($groupList[0] ?? ($lang === 'fr' ? 'Groupe 1' : 'Group 1'));
            $exampleClass1 = (string) ($classList[0] ?? ($lang === 'fr' ? '6eme A' : 'Grade 6 A'));
            $exampleClass2 = (string) ($classList[1] ?? ($lang === 'fr' ? '5eme A' : 'Grade 7 A'));

            $sheet->setCellValue('A2', $lang === 'fr' ? 'Mathematiques' : 'Mathematics');
            $sheet->setCellValue('B2', $exampleCycle);
            $sheet->setCellValue('C2', $exampleDept);
            $sheet->setCellValue('D2', 4);
            $sheet->setCellValue('E2', $exampleGroup);
            $sheet->setCellValue('F2', $exampleClass1);
            if ($classCount > 1) {
                $sheet->setCellValue('G2', $exampleClass2);
            }

            $sheet->getStyle('A2:O2')->getFont()->setItalic(true);
            $sheet->getStyle('A2:O2')->getFont()->getColor()->setRGB('6B7280');

            foreach (range('A', 'O') as $colLetter) {
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }

            $i++;
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        $spreadsheet->disconnectWorksheets();
        return $content;
    }

    /**
     * Modèle Excel pour import des classes.
     */
    /**
     * Modèle Excel pour import des classes avec listes déroulantes.
     */
    public function generateClassTemplate(string $lang = 'fr'): string
    {
        $spreadsheet = new Spreadsheet();
        
        // --- 1. CONFIGURATION DES FEUILLES ---
        $mainSheet = $spreadsheet->getActiveSheet();
        $mainSheet->setTitle($lang === 'fr' ? 'Import Classes' : 'Class import');
        
        $dataSheet = $spreadsheet->createSheet();
        $dataSheet->setTitle('CLASS_DATASOURCES');
        $dataSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VERYHIDDEN);

        // --- 2. EXTRACTION DES DONNÉES ---
        $cycles = $this->db->query("SELECT nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_COLUMN);
        $sections = $this->db->query("SELECT nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_COLUMN);

        // Remplissage de la feuille de données
        // Colonne A: Cycles
        $i = 1;
        foreach ($cycles as $cy) $dataSheet->setCellValue('A' . ($i++), $cy);
        // Colonne B: Sections
        $i = 1;
        foreach ($sections as $sec) $dataSheet->setCellValue('B' . ($i++), $sec);
        // Colonne C: Départements
        $i = 1;
        $departments = $this->db->query("SELECT nom FROM departments WHERE status = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($departments as $dept) $dataSheet->setCellValue('C' . ($i++), $dept);
        // Colonne D: Types d'enseignement
        $i = 1;
        $teachingTypes = $this->db->query("SELECT nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($teachingTypes as $tt) $dataSheet->setCellValue('D' . ($i++), $tt);

        // --- 3. EN-TÊTES ET STYLES ---
        $headers = $lang === 'fr'
            ? ['A1' => 'Classe', 'B1' => 'Cycle', 'C1' => 'Section', 'D1' => 'Département', 'E1' => 'Type d\'enseignement']
            : ['A1' => 'Class', 'B1' => 'Cycle', 'C1' => 'Section', 'D1' => 'Department', 'E1' => 'Teaching Type'];

        foreach ($headers as $cell => $value) {
            $mainSheet->setCellValue($cell, $value);
        }

        $styleArray = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']]
        ];
        $mainSheet->getStyle('A1:E1')->applyFromArray($styleArray);

        // --- 4. VALIDATIONS (Menus déroulants) ---
        if (!empty($cycles)) {
            $validationCycle = $this->createDropdown('CLASS_DATASOURCES!$A$1:$A$' . count($cycles), __('choose_cycle_hint') ?? 'Choisir Cycle');
            for ($row = 2; $row <= 1000; $row++) {
                $mainSheet->getCell('B' . $row)->setDataValidation(clone $validationCycle);
            }
        }

        if (!empty($sections)) {
            $validationSection = $this->createDropdown('CLASS_DATASOURCES!$B$1:$B$' . count($sections), __('choose_section_hint') ?? 'Choisir Section');
            for ($row = 2; $row <= 1000; $row++) {
                $mainSheet->getCell('C' . $row)->setDataValidation(clone $validationSection);
            }
        }

        if (!empty($departments)) {
            $validationDept = $this->createDropdown('CLASS_DATASOURCES!$C$1:$C$' . count($departments), __('choose_department_hint') ?? 'Choisir Département');
            for ($row = 2; $row <= 1000; $row++) {
                $mainSheet->getCell('D' . $row)->setDataValidation(clone $validationDept);
            }
        }

        if (!empty($teachingTypes)) {
            $validationTT = $this->createDropdown('CLASS_DATASOURCES!$D$1:$D$' . count($teachingTypes), 'Choisir Type d\'enseignement');
            for ($row = 2; $row <= 1000; $row++) {
                $mainSheet->getCell('E' . $row)->setDataValidation(clone $validationTT);
            }
        }

        // --- 5. LIGNE D'EXEMPLE ---
        $mainSheet->setCellValue('A2', $lang === 'fr' ? '6eme A' : 'Grade 6 A');
        if (!empty($cycles)) $mainSheet->setCellValue('B2', $cycles[0]);
        if (!empty($sections)) $mainSheet->setCellValue('C2', $sections[0]);
        if (!empty($departments)) $mainSheet->setCellValue('D2', $departments[0]);
        if (!empty($teachingTypes)) $mainSheet->setCellValue('E2', $teachingTypes[0]);
        
        $mainSheet->getStyle('A2:E2')->getFont()->setItalic(true);
        $mainSheet->getStyle('A2:E2')->getFont()->getColor()->setRGB('6B7280');

        foreach (range('A', 'E') as $col) {
            $mainSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // --- 6. GÉNÉRATION ---
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $content;
    }

    /**
     * Modèle Excel pour import des notes avec plusieurs feuilles (une par matière).
     */
    public function generateGradeTemplate(string $lang = 'fr', int $classId = 0, int $subjectId = 0): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // On enlève la feuille par défaut

        // --- 1. EXTRACTION DES DONNÉES ---
        // Récupérer le type d'enseignement de la classe
        $teachingTypeId = 0;
        if ($classId > 0) {
            $stmtTT = $this->db->prepare("SELECT teaching_type_id FROM classes WHERE id = ?");
            $stmtTT->execute([$classId]);
            $teachingTypeId = (int) $stmtTT->fetchColumn();
        }

        // Périodes d'évaluation actives pour ce type d'enseignement
        $stmtSeq = $this->db->prepare("
            SELECT s.label 
            FROM sequences s 
            LEFT JOIN teaching_types tt ON s.teaching_type_id = tt.id 
            WHERE s.is_active = 1 
              AND (tt.actif = 1 OR s.teaching_type_id IS NULL) 
              AND s.teaching_type_id = ? 
            ORDER BY s.position ASC
        ");
        $stmtSeq->execute([$teachingTypeId]);
        $evaluationTypes = $stmtSeq->fetchAll(PDO::FETCH_COLUMN);

        // Fallback si vide
        if (empty($evaluationTypes)) {
            $stmtSeq = $this->db->prepare("
                SELECT s.label 
                FROM sequences s 
                LEFT JOIN teaching_types tt ON s.teaching_type_id = tt.id 
                WHERE s.is_active = 1 
                  AND (tt.actif = 1 OR s.teaching_type_id IS NULL) 
                ORDER BY s.position ASC
            ");
            $stmtSeq->execute();
            $evaluationTypes = $stmtSeq->fetchAll(PDO::FETCH_COLUMN);
        }

        // Élèves de la classe spécifiée (exclure supprimés/démissionnaires/abandons)
        $students = [];
        if ($classId > 0) {
            $academicYearStmt = $this->db->prepare("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1");
            $academicYearStmt->execute();
            $academicYearId = (int) $academicYearStmt->fetchColumn();
            
            $stmt = $this->db->prepare("SELECT id, nom, prenom FROM students WHERE class_id = ? AND academic_year_id = ? AND is_withdrawn = 0 AND actif = 1 AND status NOT IN ('Démission', 'Démissionnaire', 'Abandon') ORDER BY nom ASC, prenom ASC");
            $stmt->execute([$classId, $academicYearId]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Matières de la classe
        $subjects = [];
        if ($classId > 0) {
            $stmt = $this->db->prepare("
                SELECT s.id, s.nom
                FROM subject_classes sc
                JOIN subjects s ON sc.subject_id = s.id
                WHERE sc.class_id = ? AND s.status = 1
                ORDER BY s.nom ASC
            ");
            $stmt->execute([$classId]);
            $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Si une matière spécifique est demandée, filtrer
        if ($subjectId > 0) {
            $subjects = array_filter($subjects, function($s) use ($subjectId) {
                return (int) $s['id'] === $subjectId;
            });
        }

        if (empty($subjects)) {
            throw new Exception('Aucune matière trouvée pour cette classe.');
        }

        // --- 2. CRÉATION DES FEUILLES PAR MATIÈRE ---
        foreach ($subjects as $subject) {
            // Nom de la feuille (limité à 31 caractères)
            $sheetName = substr($subject['nom'], 0, 31);
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($sheetName);

            // En-têtes: Nom, Prénom, puis une colonne par période
            $headers = [$lang === 'fr' ? 'Nom' : 'Last Name', $lang === 'fr' ? 'Prénom' : 'First Name'];
            foreach ($evaluationTypes as $eval) {
                $headers[] = $eval;
            }

            // Écrire les en-têtes
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $col++;
            }

            // Style des en-têtes
            $lastCol = chr(ord('A') + count($headers) - 1);
            $styleArray = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']]
            ];
            $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray($styleArray);

            // Remplir avec les élèves
            $row = 2;
            foreach ($students as $student) {
                $sheet->setCellValue('A' . $row, $student['nom']);
                $sheet->setCellValue('B' . $row, $student['prenom']);
                // Les colonnes de périodes restent vides pour la saisie
                $row++;
            }

            // Largeur des colonnes
            foreach (range('A', $lastCol) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        // --- 3. GÉNÉRATION ---
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $content;
    }

    /**
     * Modèle Excel pour import des frais de scolarité et tranches (Grille de scolarité).
     */
    public function generateGrilleTemplate(
        string $lang = 'fr',
        int $teachingTypeId = 0,
        int $cycleId = 0,
        int $sectionId = 0,
        int $classId = 0
    ): string {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($lang === 'fr' ? 'Import Grille Scolarité' : 'Fees grid import');

        // En-têtes
        $headers = [
            'A1' => 'Classe',
            'B1' => 'Inscription (Nouveau)',
            'C1' => 'Inscription (Ancien)',
            'D1' => 'Scolarité Brut',
            'E1' => 'Nombre de Tranches',
            'F1' => 'Tranche 1 - Nom',
            'G1' => 'Tranche 1 - Montant',
            'H1' => 'Tranche 1 - Échéance',
            'I1' => 'Tranche 2 - Nom',
            'J1' => 'Tranche 2 - Montant',
            'K1' => 'Tranche 2 - Échéance',
            'L1' => 'Tranche 3 - Nom',
            'M1' => 'Tranche 3 - Montant',
            'N1' => 'Tranche 3 - Échéance',
            'O1' => 'Tranche 4 - Nom',
            'P1' => 'Tranche 4 - Montant',
            'Q1' => 'Tranche 4 - Échéance',
            'R1' => 'Tranche 5 - Nom',
            'S1' => 'Tranche 5 - Montant',
            'T1' => 'Tranche 5 - Échéance',
            'U1' => 'Tranche 6 - Nom',
            'V1' => 'Tranche 6 - Montant',
            'W1' => 'Tranche 6 - Échéance',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style des en-têtes (Bleu Premium)
        $styleArray = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']]
        ];
        $sheet->getStyle('A1:W1')->applyFromArray($styleArray);

        // Récupérer toutes les classes correspondant aux filtres pour pré-remplir la colonne A
        $query = "SELECT nom FROM classes WHERE 1=1";
        $params = [];
        if ($teachingTypeId) {
            $query .= " AND teaching_type_id = ?";
            $params[] = $teachingTypeId;
        }
        if ($cycleId) {
            $query .= " AND cycle_id = ?";
            $params[] = $cycleId;
        }
        if ($sectionId) {
            $query .= " AND section_id = ?";
            $params[] = $sectionId;
        }
        if ($classId) {
            $query .= " AND id = ?";
            $params[] = $classId;
        }
        $query .= " ORDER BY nom ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $classes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $row = 2;
        foreach ($classes as $className) {
            $sheet->setCellValue('A' . $row, $className);
            $sheet->setCellValue('E' . $row, 3); // Nombre de tranches par défaut
            $sheet->setCellValue('F' . $row, 'Tranche 1');
            $sheet->setCellValue('I' . $row, 'Tranche 2');
            $sheet->setCellValue('L' . $row, 'Tranche 3');
            $sheet->setCellValue('O' . $row, 'Tranche 4');
            $sheet->setCellValue('R' . $row, 'Tranche 5');
            $sheet->setCellValue('U' . $row, 'Tranche 6');
            
            // Format des cellules numériques et texte
            $sheet->getStyle('B' . $row . ':D' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('M' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('P' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('S' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('V' . $row)->getNumberFormat()->setFormatCode('#,##0');
            
            // Format texte pour les dates d'échéances pour éviter les auto-conversions bizarres d'Excel
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            $sheet->getStyle('N' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            $sheet->getStyle('Q' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            $sheet->getStyle('T' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            $sheet->getStyle('W' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            
            $row++;
        }

        // Auto-dimensionner les colonnes
        foreach (range('A', 'W') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $content;
    }
}
