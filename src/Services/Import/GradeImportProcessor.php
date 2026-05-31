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
        $this->warmupStudents();
        $this->warmupEvaluationTypes();
        $this->setActiveYear();
    }

    /**
     * @return array{success: bool, count: int, errors: list<string>}
     */
    public function process(string $filePath, int $classId, int $subjectId): array
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheets = $spreadsheet->getAllSheets();

            if (count($sheets) === 0) {
                throw new Exception('Document vide ou sans feuilles.');
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

                // Identifier les colonnes de périodes (à partir de la colonne C)
                $periodColumns = [];
                $col = 'C';
                while (isset($headers[$col])) {
                    $periodLabel = trim((string) $headers[$col]);
                    if ($periodLabel !== '' && in_array($periodLabel, $this->evaluationTypes, true)) {
                        $periodColumns[$col] = $periodLabel;
                    }
                    $col++;
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
                    $this->processRowMultiPeriod($row, $line, $classId, $subjectId, $subjectNom, $teacherNom, $teacherPrenom, $periodColumns, $sheetName);
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

    private function processRowMultiPeriod(array $row, int $line, int $classId, int $subjectId, string $subjectNom, string $teacherNom, string $teacherPrenom, array $periodColumns, string $sheetName): void
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
        foreach ($periodColumns as $col => $periode) {
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

            // Récupérer l'ID de séquence
            $seqStmt = $this->db->prepare("SELECT id FROM sequences WHERE label = ? LIMIT 1");
            $seqStmt->execute([$periode]);
            $sequenceId = $seqStmt->fetchColumn();
            if (!$sequenceId) {
                $this->logError($line, "Feuille '{$sheetName}': Periode invalide: {$periode}");
                continue;
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

    private function resolveStudentId(string $nom, string $prenom, int $classId, int $line): ?int
    {
        $key = mb_strtolower($nom . '|' . $prenom);
        if (isset($this->studentsByName[$key])) {
            $studentId = $this->studentsByName[$key];
            // Vérifier que l'élève appartient bien à la classe spécifiée
            $stmt = $this->db->prepare("SELECT class_id FROM students WHERE id = ?");
            $stmt->execute([$studentId]);
            $studentClassId = $stmt->fetchColumn();
            if ((int) $studentClassId !== $classId) {
                $this->logError($line, "L'eleve {$nom} {$prenom} n'appartient pas a la classe specifiee.");
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
        $rows = $this->db->query("SELECT id, nom, prenom FROM students WHERE is_withdrawn = 0")->fetchAll(PDO::FETCH_ASSOC);
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
