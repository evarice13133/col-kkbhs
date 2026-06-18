<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PDO;

/**
 * TemplateGenerator
 * 
 * Gestionnaire de génération de modèles Excel pour l'importation.
 * Génère des templates dynamiques en fonction du type d'enseignement.
 */
class TemplateGenerator
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Génère un fichier template Excel (.xlsx) pour l'import des élèves.
     * 
     * @param string $lang Langue ('fr' ou 'en')
     * @param int|null $teachingTypeId Filtrer par type d'enseignement (optionnel)
     * @return string Contenu du fichier Excel (binaire)
     */
    public function generateStudentTemplate(string $lang = 'fr', ?int $teachingTypeId = null): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        
        // 1. Récupération des sections et cycles (filtrés par teaching_type si fourni)
        $query = "
            SELECT DISTINCT s.id as section_id, s.nom as section_nom, cy.id as cycle_id, cy.nom as cycle_nom
            FROM classes c
            JOIN sections s ON c.section_id = s.id
            JOIN cycles cy ON c.cycle_id = cy.id
        ";
        $params = [];
        
        if ($teachingTypeId !== null) {
            $query .= " WHERE c.teaching_type_id = ?";
            $params[] = $teachingTypeId;
        }
        
        $query .= " ORDER BY s.nom ASC, cy.nom ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $combinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Si aucune combinaison n'est trouvée pour ce type d'enseignement, on crée une feuille par défaut
        if (empty($combinations)) {
            $combinations = [['section_id' => 0, 'cycle_id' => 0, 'section_nom' => 'Défaut', 'cycle_nom' => 'Classes']];
        }

        // 2. Feuille cachée pour les données communes
        $dataSheet = $spreadsheet->createSheet();
        $dataSheet->setTitle('DATASOURCES');
        $dataSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VERYHIDDEN);
        
        $this->fillCommonData($dataSheet, $lang);

        // 3. Génération des feuilles par combinaison
        foreach ($combinations as $idx => $comb) {
            $sheet = $spreadsheet->createSheet();
            $title = strtoupper($comb['section_nom']) . " - " . $comb['cycle_nom'];
            $sheet->setTitle(substr($title, 0, 31));

            // Récupérer les classes spécifiques
            $classQuery = "SELECT nom FROM classes WHERE section_id = ? AND cycle_id = ?";
            $classParams = [$comb['section_id'], $comb['cycle_id']];
            if ($teachingTypeId !== null) {
                $classQuery .= " AND teaching_type_id = ?";
                $classParams[] = $teachingTypeId;
            }
            $classQuery .= " ORDER BY nom ASC";
            
            $stmtClass = $this->db->prepare($classQuery);
            $stmtClass->execute($classParams);
            $classList = $stmtClass->fetchAll(PDO::FETCH_COLUMN);

            if (empty($classList) && $comb['section_id'] == 0) {
                // Cas d'erreur ou fallback, on charge toutes les classes du type si possible
                if ($teachingTypeId !== null) {
                    $fallbackStmt = $this->db->prepare("SELECT nom FROM classes WHERE teaching_type_id = ? ORDER BY nom ASC");
                    $fallbackStmt->execute([$teachingTypeId]);
                    $classList = $fallbackStmt->fetchAll(PDO::FETCH_COLUMN);
                } else {
                    $classList = [];
                }
            }

            // Ajout des données de classes spécifiques à cette feuille dans DATASOURCES
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 10);
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

        // 4. Génération du flux
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
        $valSexe = $this->createDropdown('DATASOURCES!$A$1:$A$2', __('choose_sex_hint') ?? 'Sexe');
        $valRedoublant = $this->createDropdown('DATASOURCES!$E$1:$E$2', $lang === 'fr' ? 'Est-il redoublant ?' : 'Is repeating?');
        
        $classRange = "DATASOURCES!\${$classCol}\$1:\${$classCol}\$" . ($classCount ?: 1);
        $valClasse = $this->createDropdown($classRange, __('choose_class_hint') ?? 'Classe');

        for ($i = 2; $i <= $rows; $i++) {
            $sheet->getCell('D' . $i)->setDataValidation(clone $valSexe);
            $sheet->getCell('G' . $i)->setDataValidation(clone $valClasse);
            $sheet->getCell('H' . $i)->setDataValidation(clone $valRedoublant);
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

        $sheet->getStyle('A1:J1')->applyFromArray($styleArray);
    }

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
}
