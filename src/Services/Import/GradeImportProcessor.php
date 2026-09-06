<?php

namespace App\Services\Import;

use Exception;
use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GradeImportProcessor
{
    private PDO $db;
    private array $errors = [];
    private int $successCount = 0;
    private array $studentsByName = [];
    private array $evaluationTypes = [];
    private int $activeYearId;
    private int $teacherId;
    private string $createdByType;

    public function __construct(PDO $db, int $teacherId, string $userRole)
    {
        $this->db = $db;
        $this->teacherId = $teacherId;
        $this->createdByType = in_array($userRole, ['admin', 'superadmin']) ? 'admin' : 'enseignant';
        $this->setActiveYear();
        $this->warmupStudents();
        $this->warmupEvaluationTypes();
    }

    /**
     * @return array{success: bool, count: int, errors: list<string>}
     */
    public function process(string $filePath, int $classId, int $subjectId, array $competencyIds = []): array

    {

        try {

            $spreadsheet = IOFactory::load($filePath);

            $sheets = $spreadsheet->getAllSheets();



            if (count($sheets) === 0) {

                throw new Exception('Document vide ou sans feuilles.');

            }



            // Déterminer le type d'enseignement de la classe
            $stmtTT = $this->db->prepare("SELECT teaching_type_id FROM classes WHERE id = ?");
            $stmtTT->execute([$classId]);
            $teachingTypeId = (int) $stmtTT->fetchColumn();

            // Charger les séquences spécifiques à ce type d'enseignement
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
            $this->evaluationTypes = $stmtSeq->fetchAll(PDO::FETCH_COLUMN);

            // Fallback si vide
            if (empty($this->evaluationTypes)) {
                $stmtSeq = $this->db->prepare("
                    SELECT s.label 
                    FROM sequences s 
                    LEFT JOIN teaching_types tt ON s.teaching_type_id = tt.id 
                    WHERE s.is_active = 1 
                      AND (tt.actif = 1 OR s.teaching_type_id IS NULL) 
                    ORDER BY s.position ASC
                ");
                $stmtSeq->execute();
                $this->evaluationTypes = $stmtSeq->fetchAll(PDO::FETCH_COLUMN);
            }

            // Récupérer les infos de l'enseignant pour les snapshots

            $teacherData = $this->db->prepare("SELECT nom, prenom FROM users WHERE id = ? LIMIT 1");

            $teacherData->execute([$this->teacherId]);

            $teacherResult = $teacherData->fetch(PDO::FETCH_ASSOC);

            $teacherNom = $teacherResult['nom'] ?? 'Enseignant Supprimé';

            $teacherPrenom = $teacherResult['prenom'] ?? '';



            $this->db->beginTransaction();



            // Traiter chaque feuille (chaque feuille = une matière)

            foreach ($sheets as $sheet) {

                $sheetName = $sheet->getTitle();

                $rows = $sheet->toArray(null, true, true, true);



                if (count($rows) < 2) {

                    continue; // Feuille vide

                }



                $headers = array_shift($rows);

                $this->validateHeaders($headers);



                // Récupérer l'ID de la matière à partir du nom de la feuille

                $subjectId = $this->resolveSubjectId($sheetName, $classId);

                if ($subjectId === null) {

                    $this->logError(0, "Feuille '{$sheetName}': matière introuvable pour cette classe.");

                    continue;

                }



                // Récupérer les infos de la matière pour les snapshots

                $subjectData = $this->db->prepare("SELECT nom FROM subjects WHERE id = ? LIMIT 1");

                $subjectData->execute([$subjectId]);

                $subjectResult = $subjectData->fetch(PDO::FETCH_ASSOC);

                $subjectNom = $subjectResult['nom'] ?? 'Matière Supprimée';



                if (!empty($competencyIds)) {
                    $this->validateSubjectCompetencies($classId, $subjectId, $competencyIds);
                }

                // Identifier les colonnes de périodes (à partir de la colonne C)
                $periodColumns = [];
                $col = 'C';

                while (isset($headers[$col])) {
                    $periodLabel = trim((string) $headers[$col]);
                    if ($periodLabel !== '' && in_array($periodLabel, $this->evaluationTypes, true)) {
                        $periodColumns[$col] = [
                            'label' => $periodLabel,
                            'note_col' => $col,
                            'competency_col' => null,
                            'custom_col' => null,
                        ];

                        $competencyCol = $this->nextColumnLetter($col);
                        $customCol = $this->nextColumnLetter($competencyCol);

                        $competencyHeader = trim((string) ($headers[$competencyCol] ?? ''));
                        if ($competencyHeader !== '' && (str_contains(mb_strtolower($competencyHeader), 'competence') || str_contains(mb_strtolower($competencyHeader), 'skill'))) {
                            $periodColumns[$col]['competency_col'] = $competencyCol;
                        }

                        $customHeader = trim((string) ($headers[$customCol] ?? ''));
                        if ($customHeader !== '' && (str_contains(mb_strtolower($customHeader), 'ajout') || str_contains(mb_strtolower($customHeader), 'add') || str_contains(mb_strtolower($customHeader), 'nouvelle') || str_contains(mb_strtolower($customHeader), 'new'))) {
                            $periodColumns[$col]['custom_col'] = $customCol;
                        }

                        $col = $this->nextColumnLetter($customCol);
                        continue;
                    }

                    $col = $this->nextColumnLetter($col);
                }



                if (empty($periodColumns)) {

                    $this->logError(0, "Feuille '{$sheetName}': aucune période valide trouvée dans les en-têtes.");

                    continue;

                }



                // Traiter chaque ligne (chaque ligne = un élève)

                foreach ($rows as $idx => $row) {

                    $line = $idx + 2;

                    if (!$this->rowHasData($row)) {

                        continue;

                    }

                    $this->processRowMultiPeriod($row, $line, $classId, $subjectId, $subjectNom, $teacherNom, $teacherPrenom, $periodColumns, $sheetName, $teachingTypeId);

                }

            }

            if (empty($this->errors)) {
                $this->db->commit();
            } else {
                $this->db->rollBack();
            }

            return [
                'success' => count($this->errors) === 0,
                'count' => $this->successCount,
                'errors' => $this->errors,
            ];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return [
                'success' => false,
                'count' => 0,
                'errors' => ['Erreur fatale : ' . $e->getMessage()],
            ];
        }
    }

    private function rowHasData(array $row): bool
    {
        foreach (['A', 'B'] as $col) {
            if (trim((string) ($row[$col] ?? '')) !== '') {
                return true;
            }
        }
        return false;
    }

    private function validateHeaders(array $headers): void
    {
        $first = strtolower(trim((string) ($headers['A'] ?? '')));
        if ($first === '' || (!str_contains($first, 'nom') && !str_contains($first, 'name'))) {
            throw new Exception('Format d\'en-tete invalide. Utilisez le modele officiel.');
        }
    }

    private function resolveSubjectId(string $sheetName, int $classId): ?int
    {
        $stmt = $this->db->prepare("
            SELECT s.id
            FROM subject_classes sc
            JOIN subjects s ON sc.subject_id = s.id
            WHERE sc.class_id = ? AND s.nom = ? AND s.status = 1
            LIMIT 1
        ");
        $stmt->execute([$classId, $sheetName]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }

    private function processRowMultiPeriod(array $row, int $line, int $classId, int $subjectId, string $subjectNom, string $teacherNom, string $teacherPrenom, array $periodColumns, string $sheetName, int $teachingTypeId): void
    {
        $studentNom = trim((string) ($row['A'] ?? ''));
        $studentPrenom = trim((string) ($row['B'] ?? ''));

        if ($studentNom === '' || $studentPrenom === '') {
            $this->logError($line, "Feuille '{$sheetName}': Nom et prenom de l'eleve sont obligatoires.");
            return;
        }

        // Résoudre l'élève
        $studentId = $this->resolveStudentId($studentNom, $studentPrenom, $classId, $line);
        if ($studentId === null) {
            return;
        }

        // Traiter chaque période (chaque colonne de période)
        foreach ($periodColumns as $col => $config) {
            $periode = (string) $config['label'];
            $noteRaw = trim((string) ($row[$col] ?? ''));

            // Ignorer si la note est vide
            if ($noteRaw === '') {
                continue;
            }

            $note = (float) str_replace(',', '.', $noteRaw);
            if ($note < 0 || $note > 20) {
                $this->logError($line, "Feuille '{$sheetName}', période '{$periode}': La note doit etre entre 0 et 20 (valeur: {$note}).");
                continue;
            }

            // Récupérer l'ID de séquence en filtrant par le type d'enseignement
            $seqStmt = $this->db->prepare("
                SELECT s.id 
                FROM sequences s 
                LEFT JOIN teaching_types tt ON s.teaching_type_id = tt.id 
                WHERE s.label = ? 
                  AND s.teaching_type_id = ?
                  AND s.is_active = 1
                LIMIT 1
            ");
            $seqStmt->execute([$periode, $teachingTypeId]);
            $sequenceId = $seqStmt->fetchColumn();

            // Fallback si non trouvé
            if (!$sequenceId) {
                $seqStmt = $this->db->prepare("
                    SELECT s.id 
                    FROM sequences s 
                    LEFT JOIN teaching_types tt ON s.teaching_type_id = tt.id 
                    WHERE s.label = ? 
                      AND s.is_active = 1
                    LIMIT 1
                ");
                $seqStmt->execute([$periode]);
                $sequenceId = $seqStmt->fetchColumn();
            }

            if (!$sequenceId) {
                $this->logError($line, "Feuille '{$sheetName}': Periode invalide: {$periode}");
                continue;
            }

            // Récupérer l'association compétence correspondante si elle est fournie dans le modèle Excel
            $selectedCompetencyId = $this->resolveCompetencyIdFromTemplateRow($subjectId, $row, $config, $line, $sheetName);
            if ($selectedCompetencyId !== null) {
                $this->saveEvaluationCompetencyAssociation((int) $classId, $subjectId, $this->activeYearId, (int) $sequenceId, $periode, $selectedCompetencyId);
            }

            // Générer l'appréciation
            $appreciation = $this->generateAppreciation($note);

            try {
                $stmt = $this->db->prepare("
                    INSERT INTO grades (student_id, subject_id, teacher_id, academic_year_id, sequence_id, periode, valeur, appreciation, teacher_nom_snapshot, teacher_prenom_snapshot, subject_nom_snapshot, created_by_type)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        valeur = VALUES(valeur),
                        appreciation = VALUES(appreciation),
                        teacher_id = VALUES(teacher_id),
                        sequence_id = VALUES(sequence_id),
                        teacher_nom_snapshot = VALUES(teacher_nom_snapshot),
                        teacher_prenom_snapshot = VALUES(teacher_prenom_snapshot),
                        subject_nom_snapshot = VALUES(subject_nom_snapshot),
                        created_by_type = VALUES(created_by_type)
                ");
                $stmt->execute([
                    $studentId,
                    $subjectId,
                    $this->teacherId,
                    $this->activeYearId,
                    $sequenceId,
                    $periode,
                    $note,
                    $appreciation,
                    $teacherNom,
                    $teacherPrenom,
                    $subjectNom,
                    $this->createdByType
                ]);

                $this->successCount++;
            } catch (\Throwable $e) {
                $this->logError($line, "Feuille '{$sheetName}', période '{$periode}': Erreur base de donnees : " . $e->getMessage());
            }
        }
    }

    private function resolveCompetencyIdFromTemplateRow(int $subjectId, array $row, array $config, int $line, string $sheetName): ?int
    {
        $candidate = trim((string) ($row[$config['competency_col']] ?? ''));
        $custom = trim((string) ($row[$config['custom_col']] ?? ''));

        if ($candidate === '' && $custom === '') {
            return null;
        }

        $chosen = $candidate !== '' ? $candidate : $custom;

        $stmt = $this->db->prepare("SELECT id FROM competencies WHERE subject_id = ? AND LOWER(TRIM(libelle)) = LOWER(TRIM(?)) LIMIT 1");
        $stmt->execute([$subjectId, $chosen]);
        $existingId = $stmt->fetchColumn();
        if ($existingId !== false) {
            return (int) $existingId;
        }

        if ($custom !== '') {
            $insert = $this->db->prepare("INSERT INTO competencies (subject_id, libelle, position, created_by) VALUES (?, ?, ?, ?)");
            $maxPos = $this->db->prepare("SELECT COALESCE(MAX(position), 0) FROM competencies WHERE subject_id = ?");
            $maxPos->execute([$subjectId]);
            $position = (int) $maxPos->fetchColumn() + 1;
            $insert->execute([$subjectId, $chosen, $position, $this->teacherId]);
            return (int) $this->db->lastInsertId();
        }

        return null;
    }

    private function saveEvaluationCompetencyAssociation(int $classId, int $subjectId, int $academicYearId, int $sequenceId, string $periode, int $competencyId): void
    {
        $delete = $this->db->prepare("DELETE FROM evaluation_competencies WHERE class_id = ? AND subject_id = ? AND academic_year_id = ? AND periode = ? AND sequence_id = ?");
        $delete->execute([$classId, $subjectId, $academicYearId, $periode, $sequenceId]);

        $insert = $this->db->prepare("INSERT INTO evaluation_competencies (class_id, subject_id, academic_year_id, sequence_id, periode, competency_id, position) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $insert->execute([$classId, $subjectId, $academicYearId, $sequenceId, $periode, $competencyId]);
    }

    private function nextColumnLetter(string $column): string
    {
        $index = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($column);
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
    }

    private function validateSubjectCompetencies(int $classId, int $subjectId, array $competencyIds): void
    {
        $normalized = array_values(array_unique(array_map('intval', array_filter($competencyIds, static function ($value) {
            return $value !== null && $value !== '' && $value !== false;
        }))));

        if ($subjectId <= 0) {
            throw new Exception('Import rejeté : matière invalide ou absente pour cette classe. Vérifiez le filtre matière et la feuille Excel correspondante.');
        }

        if (empty($normalized)) {
            throw new Exception('Import rejeté : aucune compétence n\'a été renseignée dans le fichier Excel pour cette matière. Remplissez la colonne « Compétence (...) » ou « Ajouter compétence (...) » avant de relancer l\'import.');
        }

        if (count($normalized) > 2) {
            throw new Exception('Import rejeté : trop de compétences déclarées pour une évaluation. Une seule évaluation accepte au maximum 2 compétences.');
        }

        $subjectCheck = $this->db->prepare('SELECT 1 FROM subject_classes WHERE class_id = ? AND subject_id = ? LIMIT 1');
        $subjectCheck->execute([$classId, $subjectId]);
        if ($subjectCheck->fetchColumn() === false) {
            throw new Exception('La matière sélectionnée n\'est pas affectée à cette classe.');
        }

        $in = implode(',', array_fill(0, count($normalized), '?'));
        $stmt = $this->db->prepare("SELECT id FROM competencies WHERE id IN ({$in}) AND subject_id = ?");
        $params = $normalized;
        $params[] = $subjectId;
        $stmt->execute($params);
        $validIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

        if (count($validIds) !== count(array_unique($normalized))) {
            throw new Exception('Import rejeté : au moins une compétence ne correspond pas à la matière sélectionnée. Vérifiez la liste de compétences de la feuille Excel et les libellés exacts enregistrés en base.');
        }
    }

    private function resolveStudentId(string $nom, string $prenom, int $classId, int $line): ?int
    {
        $key = mb_strtolower($nom . '|' . $prenom);
        if (isset($this->studentsByName[$key])) {
            $studentId = $this->studentsByName[$key];
            // Vérifier que l'élève appartient bien à la classe spécifiée et à l'année académique active
            $stmt = $this->db->prepare("SELECT class_id, academic_year_id FROM students WHERE id = ?");
            $stmt->execute([$studentId]);
            $studentData = $stmt->fetch(PDO::FETCH_ASSOC);
            if ((int) $studentData['class_id'] !== $classId || (int) $studentData['academic_year_id'] !== $this->activeYearId) {
                $this->logError($line, "L'eleve {$nom} {$prenom} n'appartient pas a la classe specifiee ou a l'annee academique active.");
                return null;
            }
            return $studentId;
        }

        $this->logError($line, "Eleve introuvable: {$nom} {$prenom}");
        return null;
    }

    private function resolvePeriode(?string $periode, int $line): ?string
    {
        if ($periode === '' || $periode === null) {
            // Utiliser la première période active par défaut
            return $this->evaluationTypes[0] ?? null;
        }

        if (in_array($periode, $this->evaluationTypes, true)) {
            return $periode;
        }

        $this->logError($line, "Periode invalide: {$periode}. Periodes disponibles: " . implode(', ', $this->evaluationTypes));
        return null;
    }

    private function warmupStudents(): void
    {
        $rows = $this->db->query("SELECT id, nom, prenom FROM students WHERE academic_year_id = {$this->activeYearId} AND is_withdrawn = 0 AND actif = 1 AND status NOT IN ('Démission', 'Démissionnaire', 'Abandon')")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $key = mb_strtolower($row['nom'] . '|' . $row['prenom']);
            $this->studentsByName[$key] = (int) $row['id'];
        }
    }

    private function warmupEvaluationTypes(): void
    {
        try {
            $stmt = $this->db->query("SELECT label FROM sequences WHERE is_active = 1 ORDER BY position ASC");
            $this->evaluationTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (\Throwable $e) {
            $this->evaluationTypes = [];
        }
    }

    private function setActiveYear(): void
    {
        $stmt = $this->db->prepare("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1");
        $stmt->execute();
        $this->activeYearId = (int) $stmt->fetchColumn();
    }

    private function generateAppreciation(float $note): string
    {
        if ($note >= 18) return __('grade_appreciation_excellent');
        if ($note >= 16) return __('grade_appreciation_very_good');
        if ($note >= 14) return __('grade_appreciation_good');
        if ($note >= 12) return __('grade_appreciation_fairly_good');
        if ($note >= 10) return __('grade_appreciation_passable');
        if ($note >= 8) return __('grade_appreciation_insufficient');
        return __('grade_appreciation_very_insufficient');
    }

    private function logError(int $line, string $message): void
    {
        $this->errors[] = "Ligne {$line} : {$message}";
    }
}
