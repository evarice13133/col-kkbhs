<?php

namespace App\Services;

use PDO;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TeacherAssignmentExcelService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createBulkWorkbook(int $academicYearId): Spreadsheet
    {
        $classes = $this->getClassesWithEnrolledStudents($academicYearId);
        if ($classes === []) {
            throw new \RuntimeException('Aucune classe ne contient d’élève inscrit pour l’année scolaire active.');
        }
        if (count($classes) > 255) {
            throw new \RuntimeException('Le nombre de classes dépasse la limite de feuilles Excel.');
        }

        $spreadsheet = new Spreadsheet();
        $classSheet = $spreadsheet->getActiveSheet();
        $listSheet = $spreadsheet->createSheet();
        $listSheet->setTitle('Liste enseignants');
        $classSubjects = [];
        $teacherOptionsByType = [];
        foreach ($classes as $class) {
            $classId = (int) $class['id'];
            $classSubjects[$classId] = $this->getBulkSubjects($classId, $academicYearId);
            foreach ($classSubjects[$classId] as $subject) {
                $typeId = (int) $subject['teaching_type_id'];
                if ($typeId > 0 && !isset($teacherOptionsByType[$typeId])) {
                    $teacherOptionsByType[$typeId] = [];
                    foreach ($this->getTeachers($typeId) as $teacher) {
                        $teacherOptionsByType[$typeId][(int) $teacher['id']] = $this->teacherOptionLabel($teacher);
                    }
                }
            }
        }

        $listColumn = 1;
        $teacherRangeNames = [];
        foreach ($teacherOptionsByType as $typeId => $teacherOptions) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($listColumn);
            $listSheet->setCellValue($columnLetter . '1', 'Type_' . $typeId);
            $row = 2;
            foreach ($teacherOptions as $label) {
                $listSheet->setCellValueExplicit(
                    $columnLetter . $row,
                    $label,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
                $row++;
            }
            if ($row > 2) {
                $rangeName = 'TeacherOptionsType' . $typeId;
                $spreadsheet->addNamedRange(new NamedRange(
                    $rangeName,
                    $listSheet,
                    '$' . $columnLetter . '$2:$' . $columnLetter . '$' . ($row - 1)
                ));
                $teacherRangeNames[$typeId] = $rangeName;
            }
            $listColumn++;
        }
        $listSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VERYHIDDEN);

        foreach ($classes as $classIndex => $class) {
            $classId = (int) $class['id'];
            $sheet = $classIndex === 0 ? $classSheet : $spreadsheet->createSheet();
            $sheetTitle = $this->classSheetTitle((string) $class['nom'], $classId);
            $sheet->setTitle($sheetTitle);
            $sheet->fromArray(
                [['Classe ID', 'Classe', 'Matière ID', 'Matière', 'Enseignant (choisir dans la liste)']],
                null,
                'A1'
            );
            $sheet->getColumnDimension('A')->setVisible(false);
            $sheet->getColumnDimension('C')->setVisible(false);
            $sheet->getComment('E1')->getText()->createTextRun(
                'Choisissez un enseignant dans la liste. Une cellule vide laisse l’affectation existante inchangée.'
            );
            $sheet->getStyle('A1:E1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1F4E78'],
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->freezePane('B2');
            $sheet->setAutoFilter('A1:E' . (count($classSubjects[$classId]) + 1));
            $sheet->getColumnDimension('A')->setWidth(13);
            $sheet->getColumnDimension('B')->setWidth(28);
            $sheet->getColumnDimension('C')->setWidth(13);
            $sheet->getColumnDimension('D')->setWidth(38);
            $sheet->getColumnDimension('E')->setWidth(48);

            $row = 2;
            foreach ($classSubjects[$classId] as $subject) {
                $typeId = (int) $subject['teaching_type_id'];
                $teacherOptions = $teacherOptionsByType[$typeId] ?? [];
                $sheet->fromArray(
                    [[
                        $classId,
                        null,
                        (int) $subject['id'],
                        null,
                        $teacherOptions[(int) ($subject['assigned_teacher_id'] ?? 0)] ?? '',
                    ]],
                    null,
                    'A' . $row
                );
                $sheet->setCellValueExplicit('B' . $row, (string) $class['nom'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('D' . $row, (string) $subject['nom'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                if (isset($teacherRangeNames[$typeId])) {
                    $validation = $sheet->getCell('E' . $row)->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(true);
                    $validation->setShowDropDown(true);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setPromptTitle('Affectation enseignant');
                    $validation->setPrompt('Sélectionnez un enseignant dans la liste.');
                    $validation->setErrorTitle('Choix invalide');
                    $validation->setError('Choisissez un enseignant de la liste ou laissez la cellule vide.');
                    $validation->setFormula1('=' . $teacherRangeNames[$typeId]);
                }
                $row++;
            }
        }

        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    public function createWorkbook(int $classId, int $teachingTypeId, int $academicYearId): Spreadsheet
    {
        $className = $this->getClassName($classId);
        $this->assertTeachingType($teachingTypeId);
        $subjects = $this->getSubjects($classId, $teachingTypeId, $academicYearId);
        $teachers = $this->getTeachers($teachingTypeId);

        if ($subjects === []) {
            throw new \RuntimeException('Aucune matière active n’est affectée à cette classe pour ce type d’enseignement et cette année.');
        }
        if ($teachers === []) {
            throw new \RuntimeException('Aucun enseignant actif n’est disponible pour ce type d’enseignement.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Affectations');
        $sheet->fromArray(
            [['Classe ID', 'Classe', 'Matière ID', 'Matière', 'Enseignant (choisir dans la liste)']],
            null,
            'A1'
        );
        $sheet->getColumnDimension('A')->setVisible(false);
        $sheet->getColumnDimension('C')->setVisible(false);
        $sheet->getComment('E1')->getText()->createTextRun(
            'Choisissez un enseignant dans la liste. Une cellule vide laisse l’affectation existante inchangée.'
        );
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E78'],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->freezePane('B2');
        $sheet->setAutoFilter('A1:E' . (count($subjects) + 1));
        $sheet->getColumnDimension('A')->setWidth(13);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(13);
        $sheet->getColumnDimension('D')->setWidth(38);
        $sheet->getColumnDimension('E')->setWidth(48);

        $teacherOptions = [];
        foreach ($teachers as $teacher) {
            $teacherOptions[(int) $teacher['id']] = $this->teacherOptionLabel($teacher);
        }

        $row = 2;
        foreach ($subjects as $subject) {
            $sheet->fromArray(
                [[
                    $classId,
                    null,
                    (int) $subject['id'],
                    null,
                    $teacherOptions[(int) ($subject['assigned_teacher_id'] ?? 0)] ?? '',
                ]],
                null,
                'A' . $row
            );
            $sheet->setCellValueExplicit(
                'B' . $row,
                $className,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicit(
                'D' . $row,
                (string) $subject['nom'],
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
            $row++;
        }

        $listSheet = $spreadsheet->createSheet();
        $listSheet->setTitle('Liste enseignants');
        $listSheet->setCellValue('A1', 'Enseignant');
        $row = 2;
        foreach ($teacherOptions as $label) {
            $listSheet->setCellValueExplicit(
                'A' . $row,
                $label,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
            $row++;
        }
        $spreadsheet->addNamedRange(new NamedRange(
            'TeacherOptions',
            $listSheet,
            '$A$2:$A$' . ($row - 1)
        ));
        $listSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VERYHIDDEN);

        for ($row = 2; $row <= count($subjects) + 1; $row++) {
            $validation = $sheet->getCell('E' . $row)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setPromptTitle('Affectation enseignant');
            $validation->setPrompt('Sélectionnez un enseignant dans la liste.');
            $validation->setErrorTitle('Choix invalide');
            $validation->setError('Choisissez un enseignant de la liste ou laissez la cellule vide.');
            $validation->setFormula1('=TeacherOptions');
        }

        return $spreadsheet;
    }

    /**
     * Imports selected teacher assignments from every class sheet in the workbook.
     * Empty teacher cells never remove existing assignments.
     *
     * @return array{created:int, updated:int, unchanged:int, skipped:int, classes:int}
     */
    public function importBulk(string $filePath, int $academicYearId): array
    {
        $classes = [];
        foreach ($this->getClassesWithEnrolledStudents($academicYearId) as $class) {
            $classes[(int) $class['id']] = (string) $class['nom'];
        }
        if ($classes === []) {
            throw new \RuntimeException('Aucune classe ne contient d’eleve inscrit pour l’annee scolaire active.');
        }

        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $expectedHeaders = [
            'Classe ID',
            'Classe',
            'Matière ID',
            'Matière',
            'Enseignant (choisir dans la liste)',
        ];
        $assignments = [];
        $seenClasses = [];
        $seenSubjects = [];
        $subjectsByClass = [];
        $teachersByType = [];
        $skipped = 0;
        $totalRows = 0;

        try {
            foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                if ($sheet->getTitle() === 'Liste enseignants') {
                    continue;
                }
                foreach ($expectedHeaders as $index => $expectedHeader) {
                    $actualHeader = trim((string) $sheet->getCellByColumnAndRow($index + 1, 1)->getValue());
                    if ($actualHeader !== $expectedHeader) {
                        throw new \RuntimeException('Le format du modele est invalide. Telechargez un nouveau fichier Excel.');
                    }
                }

                $lastRow = $sheet->getHighestDataRow();
                $totalRows += max(0, $lastRow - 1);
                if ($totalRows > 50000) {
                    throw new \RuntimeException('Le fichier depasse la limite de 50 000 lignes.');
                }
                $sheetClassId = null;
                for ($row = 2; $row <= $lastRow; $row++) {
                    $values = [];
                    for ($column = 1; $column <= 5; $column++) {
                        $value = $sheet->getCellByColumnAndRow($column, $row)->getValue();
                        if ($value !== null && !is_scalar($value)) {
                            throw new \RuntimeException("Feuille {$sheet->getTitle()}, ligne {$row} : valeur de cellule invalide.");
                        }
                        $values[] = trim((string) $value);
                    }
                    if (count(array_filter($values, static fn(string $value): bool => $value !== '')) === 0) {
                        continue;
                    }

                    $classId = $this->positiveInteger($values[0]);
                    $subjectId = $this->positiveInteger($values[2]);
                    if ($classId === null || !isset($classes[$classId]) || $values[1] !== $classes[$classId]) {
                        throw new \RuntimeException("Feuille {$sheet->getTitle()}, ligne {$row} : classe invalide, inactive ou sans eleve inscrit.");
                    }
                    if ($sheetClassId !== null && $sheetClassId !== $classId) {
                        throw new \RuntimeException("Feuille {$sheet->getTitle()} : une feuille ne peut contenir qu'une seule classe.");
                    }
                    $sheetClassId = $classId;

                    if (!isset($subjectsByClass[$classId])) {
                        $subjectsByClass[$classId] = [];
                        foreach ($this->getBulkSubjects($classId, $academicYearId) as $subject) {
                            $subjectsByClass[$classId][(int) $subject['id']] = [
                                'name' => (string) $subject['nom'],
                                'type_id' => (int) $subject['teaching_type_id'],
                            ];
                        }
                    }
                    if ($subjectId === null || !isset($subjectsByClass[$classId][$subjectId])
                        || $values[3] !== $subjectsByClass[$classId][$subjectId]['name']) {
                        throw new \RuntimeException("Feuille {$sheet->getTitle()}, ligne {$row} : la matiere n'appartient pas a cette classe ou a l'annee active.");
                    }
                    if (isset($seenSubjects[$classId][$subjectId])) {
                        throw new \RuntimeException("Feuille {$sheet->getTitle()}, ligne {$row} : la matiere apparait plusieurs fois.");
                    }
                    $seenSubjects[$classId][$subjectId] = true;
                    $seenClasses[$classId] = true;
                    if ($values[4] === '') {
                        $skipped++;
                        continue;
                    }

                    $typeId = $subjectsByClass[$classId][$subjectId]['type_id'];
                    if (!isset($teachersByType[$typeId])) {
                        $teachersByType[$typeId] = [];
                        foreach ($this->getTeachers($typeId) as $teacher) {
                            $teachersByType[$typeId][$this->teacherOptionLabel($teacher)] = (int) $teacher['id'];
                        }
                    }
                    if (!isset($teachersByType[$typeId][$values[4]])) {
                        throw new \RuntimeException("Feuille {$sheet->getTitle()}, ligne {$row} : choisissez un enseignant actif du type d'enseignement de la matiere.");
                    }
                    $assignments[$classId][$subjectId] = $teachersByType[$typeId][$values[4]];
                }
            }

            if ($assignments === []) {
                return ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'skipped' => $skipped, 'classes' => count($seenClasses)];
            }

            $created = 0;
            $updated = 0;
            $unchanged = 0;
            $this->db->beginTransaction();
            $currentSql = 'SELECT user_id FROM teacher_assignments
                           WHERE class_id = ? AND subject_id = ? AND academic_year_id = ?';
            if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
                $currentSql .= ' FOR UPDATE';
            }
            $currentStmt = $this->db->prepare($currentSql);
            $deleteStmt = $this->db->prepare(
                'DELETE FROM teacher_assignments
                 WHERE class_id = ? AND subject_id = ? AND academic_year_id = ?'
            );
            $insertStmt = $this->db->prepare(
                'INSERT INTO teacher_assignments (user_id, subject_id, class_id, academic_year_id)
                 VALUES (?, ?, ?, ?)'
            );
            foreach ($assignments as $classId => $classAssignments) {
                foreach ($classAssignments as $subjectId => $teacherId) {
                    $currentStmt->execute([$classId, $subjectId, $academicYearId]);
                    $currentTeacherId = $currentStmt->fetchColumn();
                    if ($currentTeacherId !== false && (int) $currentTeacherId === $teacherId) {
                        $unchanged++;
                        continue;
                    }
                    if ($currentTeacherId === false) {
                        $created++;
                    } else {
                        $updated++;
                        $deleteStmt->execute([$classId, $subjectId, $academicYearId]);
                    }
                    $insertStmt->execute([$teacherId, $subjectId, $classId, $academicYearId]);
                }
            }
            $this->db->commit();

            return [
                'created' => $created,
                'updated' => $updated,
                'unchanged' => $unchanged,
                'skipped' => $skipped,
                'classes' => count($seenClasses),
            ];
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        } finally {
            $spreadsheet->disconnectWorksheets();
        }
    }

    /**
     * @return array<int, array{id:int, nom:string}>
     */
    private function getClassesWithEnrolledStudents(int $academicYearId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.id, c.nom
             FROM classes c
             WHERE c.status = 1
               AND EXISTS (
                   SELECT 1
                   FROM students st
                   WHERE st.class_id = c.id
                     AND st.academic_year_id = ?
                     AND st.status = 'Inscrit'
                     AND st.actif = 1
                     AND st.is_withdrawn = 0
               )
             ORDER BY c.nom, c.id"
        );
        $stmt->execute([$academicYearId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getBulkSubjects(int $classId, int $academicYearId): array
    {
        $stmt = $this->db->prepare(
            'SELECT s.id, s.nom, COALESCE(s.teaching_type_id, c.teaching_type_id, 0) AS teaching_type_id,
                    (SELECT ta.user_id
                     FROM teacher_assignments ta
                     WHERE ta.class_id = sc.class_id
                       AND ta.subject_id = sc.subject_id
                       AND ta.academic_year_id = sc.academic_year_id
                     LIMIT 1) AS assigned_teacher_id
             FROM subject_classes sc
             INNER JOIN classes c ON c.id = sc.class_id AND c.status = 1
             INNER JOIN subjects s ON s.id = sc.subject_id AND s.status = 1
             WHERE sc.class_id = ? AND sc.academic_year_id = ?
             ORDER BY s.nom, s.id'
        );
        $stmt->execute([$classId, $academicYearId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function classSheetTitle(string $className, int $classId): string
    {
        $cleanName = preg_replace('/[:\\\\\\/?*\\[\\]]/', ' ', $className) ?? '';
        $prefix = function_exists('mb_strcut') ? mb_strcut(trim($cleanName), 0, 24, 'UTF-8') : 'Classe';
        return trim($prefix) . ' ' . $classId;
    }

    private function getClassName(int $classId): string
    {
        $stmt = $this->db->prepare('SELECT nom FROM classes WHERE id = ? AND status = 1');
        $stmt->execute([$classId]);
        $name = $stmt->fetchColumn();
        if ($classId <= 0 || $name === false) {
            throw new \RuntimeException('La classe sélectionnée est invalide ou inactive.');
        }
        return (string) $name;
    }

    private function assertTeachingType(int $teachingTypeId): void
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM teaching_types WHERE id = ? AND actif = 1');
        $stmt->execute([$teachingTypeId]);
        if ($teachingTypeId <= 0 || (int) $stmt->fetchColumn() !== 1) {
            throw new \RuntimeException('Le type d’enseignement sélectionné est invalide ou inactif.');
        }
    }

    private function getSubjects(int $classId, int $teachingTypeId, int $academicYearId): array
    {
        $stmt = $this->db->prepare(
            'SELECT s.id, s.nom,
                    (SELECT ta.user_id
                     FROM teacher_assignments ta
                     WHERE ta.class_id = sc.class_id
                       AND ta.subject_id = sc.subject_id
                       AND ta.academic_year_id = sc.academic_year_id
                     LIMIT 1) AS assigned_teacher_id
             FROM subject_classes sc
             INNER JOIN subjects s ON s.id = sc.subject_id AND s.status = 1
             WHERE sc.class_id = ? AND sc.academic_year_id = ? AND s.teaching_type_id = ?
             ORDER BY s.nom, s.id'
        );
        $stmt->execute([$classId, $academicYearId, $teachingTypeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getTeachers(int $teachingTypeId): array
    {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT u.id, u.nom, u.prenom, u.username
             FROM users u
             INNER JOIN user_teaching_types utt ON utt.user_id = u.id
             WHERE u.role = 'enseignant' AND u.status = 1 AND utt.teaching_type_id = ?
             ORDER BY u.nom, u.prenom, u.id"
        );
        $stmt->execute([$teachingTypeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function teacherOptionLabel(array $teacher): string
    {
        $name = trim((string) ($teacher['prenom'] ?? '') . ' ' . (string) ($teacher['nom'] ?? ''));
        $username = trim((string) ($teacher['username'] ?? ''));
        return (int) $teacher['id'] . ' - ' . $name . ($username === '' ? '' : ' (' . $username . ')');
    }

    private function positiveInteger(string $value): ?int
    {
        if ($value === '' || !is_numeric($value)) {
            return null;
        }
        $number = (float) $value;
        if ($number <= 0 || floor($number) !== $number || $number > PHP_INT_MAX) {
            return null;
        }
        return (int) $number;
    }
}
