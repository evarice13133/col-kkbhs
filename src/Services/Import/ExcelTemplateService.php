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
            foreach(range('A','G') as $col) {
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
            'H' => $lang === 'fr' ? 'NON' : 'NO'
        ];
        foreach ($sample as $col => $val) {
            $sheet->setCellValue($col . '2', $val);
        }
        $sheet->getStyle('A2:H2')->getFont()->setItalic(true);
        $sheet->getStyle('A2:H2')->getFont()->getColor()->setRGB('6B7280');
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
                'H1' => 'Repeating (YES/NO)'
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
            'H1' => 'Redoublant (OUI/NON)'
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
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($lang === 'fr' ? 'Import Matieres' : 'Subject import');
        $dataSheet = $spreadsheet->createSheet();
        $dataSheet->setTitle('SUBJECT_DATASOURCES');
        $dataSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VERYHIDDEN);

        $headers = $lang === 'fr'
            ? ['A1' => 'Matiere', 'B1' => 'Coefficient', 'C1' => 'Groupe', 'D1' => 'Classe 1', 'E1' => 'Classe 2', 'F1' => 'Classe 3', 'G1' => 'Classe 4', 'H1' => 'Classe 5', 'I1' => 'Classe 6', 'J1' => 'Classe 7', 'K1' => 'Classe 8', 'L1' => 'Classe 9', 'M1' => 'Classe 10']
            : ['A1' => 'Subject', 'B1' => 'Coefficient', 'C1' => 'Group', 'D1' => 'Class 1', 'E1' => 'Class 2', 'F1' => 'Class 3', 'G1' => 'Class 4', 'H1' => 'Class 5', 'I1' => 'Class 6', 'J1' => 'Class 7', 'K1' => 'Class 8', 'L1' => 'Class 9', 'M1' => 'Class 10'];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $styleArray = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']]
        ];
        $sheet->getStyle('A1:M1')->applyFromArray($styleArray);

        $classes = $this->db->query("SELECT nom FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_COLUMN);
        $i = 1;
        foreach ($classes as $className) {
            $dataSheet->setCellValue('A' . $i, (string) $className);
            $i++;
        }

        // Ajout des groupes dans SUBJECT_DATASOURCES colonne B
        $groups = $lang === 'fr' ? [
            'Groupe 1 - Matières Littéraires',
            'Groupe 2 - Matières Scientifiques',
            'Groupe 3 - Développement Personnel'
        ] : [
            'Group 1 - Literary Subjects',
            'Group 2 - Scientific Subjects',
            'Group 3 - Personal Development'
        ];
        $i = 1;
        foreach ($groups as $group) {
            $dataSheet->setCellValue('B' . $i, (string) $group);
            $i++;
        }

        $exampleClass = (string) ($classes[0] ?? '6eme A');
        $sheet->setCellValue('A2', $lang === 'fr' ? 'Mathematiques' : 'Mathematics');
        $sheet->setCellValue('B2', 4);
        $sheet->setCellValue('C2', $groups[0]);
        $sheet->setCellValue('D2', $exampleClass);
        if (!empty($classes[1])) {
            $sheet->setCellValue('E2', (string) $classes[1]);
        }

        $sheet->getStyle('A2:H2')->getFont()->setItalic(true);
        $sheet->getStyle('A2:H2')->getFont()->getColor()->setRGB('6B7280');

        if (!empty($classes)) {
            $validationClasse = $this->createDropdown(
                'SUBJECT_DATASOURCES!$A$1:$A$' . count($classes),
                __('choose_class_hint') ?? 'Choisir une classe'
            );
            for ($row = 2; $row <= 1000; $row++) {
                foreach (range('D', 'M') as $col) {
                    $sheet->getCell($col . $row)->setDataValidation(clone $validationClasse);
                }
            }
        }
        
        $validationGroupe = $this->createDropdown(
            'SUBJECT_DATASOURCES!$B$1:$B$' . count($groups),
            __('choose_group_hint') ?? 'Choisir un groupe'
        );
        for ($row = 2; $row <= 1000; $row++) {
            $sheet->getCell('C' . $row)->setDataValidation(clone $validationGroupe);
        }

        foreach (range('A', 'M') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
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

        // --- 3. EN-TÊTES ET STYLES ---
        $headers = $lang === 'fr'
            ? ['A1' => 'Classe', 'B1' => 'Cycle', 'C1' => 'Section', 'D1' => 'Département']
            : ['A1' => 'Class', 'B1' => 'Cycle', 'C1' => 'Section', 'D1' => 'Department'];

        foreach ($headers as $cell => $value) {
            $mainSheet->setCellValue($cell, $value);
        }

        $styleArray = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']]
        ];
        $mainSheet->getStyle('A1:D1')->applyFromArray($styleArray);

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

        // --- 5. LIGNE D'EXEMPLE ---
        $mainSheet->setCellValue('A2', $lang === 'fr' ? '6eme A' : 'Grade 6 A');
        if (!empty($cycles)) $mainSheet->setCellValue('B2', $cycles[0]);
        if (!empty($sections)) $mainSheet->setCellValue('C2', $sections[0]);
        if (!empty($departments)) $mainSheet->setCellValue('D2', $departments[0]);
        
        $mainSheet->getStyle('A2:D2')->getFont()->setItalic(true);
        $mainSheet->getStyle('A2:D2')->getFont()->getColor()->setRGB('6B7280');

        foreach (range('A', 'D') as $col) {
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
}
