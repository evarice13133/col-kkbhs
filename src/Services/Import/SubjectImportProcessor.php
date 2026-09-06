<?php

namespace App\Services\Import;

use Exception;
use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SubjectImportProcessor
{
    private PDO $db;
    private array $errors = [];
    private int $successCount = 0;
    private array $classesByName = [];
    private array $teachingTypesByName = [];
    private array $departmentsByName = [];
    private array $cyclesByName = [];
    private array $subjectGroupsByName = [];
    private int $activeYearId;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->setActiveYear();
        $this->warmupData();
    }

    /**
     * @return array{success: bool, count: int, errors: list<string>}
     */
    public function process(string $filePath): array
    {
        try {
            $workbook = IOFactory::load($filePath);
            $this->db->beginTransaction();

            $hasData = false;
            foreach ($workbook->getAllSheets() as $sheet) {
                // Ignorer les feuilles masquées comme SUBJECT_DATASOURCES
                if ($sheet->getSheetState() !== \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VISIBLE) {
                    continue;
                }

                $sheetName = $sheet->getTitle();
                $rows = $sheet->toArray(null, true, true, true);
                if (count($rows) < 2) {
                    continue; // Sauter les feuilles vides
                }

                $headers = array_shift($rows);
                $colMap = $this->analyzeHeaders($headers, $sheetName);
                if ($colMap === null) {
                    continue;
                }

                $teachingTypeId = $this->resolveTeachingTypeIdBySheetName($sheetName);

                foreach ($rows as $idx => $row) {
                    $line = $idx + 2;
                    if (!$this->rowHasData($row, $colMap)) {
                        continue;
                    }
                    $hasData = true;
                    $this->processRow($row, $line, $teachingTypeId, $sheetName, $colMap);
                }
            }

            if (!$hasData) {
                throw new Exception('Document vide ou sans données.');
            }

            if (empty($this->errors)) {
                $this->db->commit();
            } else {
                $this->db->rollBack();
            }

            return [
                'success' => count($this->errors) === 0,
                'count' => count($this->errors) === 0 ? $this->successCount : 0,
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

    private function analyzeHeaders(array $headers, string $sheetName): ?array
    {
        $map = [
            'subject' => null,
            'coef' => null,
            'group' => null,
            'classes' => [],
            'vhm' => null,
            'vhp' => null,
            'th_max' => null,
            'observations' => null,
            'competences' => [],
            'cycle' => null,
            'department' => null,
            'is_legacy' => false,
        ];

        foreach ($headers as $col => $val) {
            $headerText = mb_strtolower(trim((string) $val));
            if ($headerText === '') continue;

            if (str_contains($headerText, 'mati') || str_contains($headerText, 'subject')) {
                $map['subject'] = $col;
            } elseif (str_contains($headerText, 'coef')) {
                $map['coef'] = $col;
            } elseif (str_contains($headerText, 'group')) {
                $map['group'] = $col;
            } elseif (preg_match('/classe\s*(1|2|3|4|5)|class\s*(1|2|3|4|5)|classes\s*concern|classes concern|classe/iu', $headerText)) {
                $map['classes'][] = $col;
            } elseif ($headerText === 'vhm' || str_contains($headerText, 'ministér') || str_contains($headerText, 'minister')) {
                $map['vhm'] = $col;
            } elseif ($headerText === 'vhp' || str_contains($headerText, 'propos')) {
                $map['vhp'] = $col;
            } elseif (str_contains($headerText, 'th(') || str_contains($headerText, 'th_max') || str_contains($headerText, 'taux max') || $headerText === 'thmax' || $headerText === 'th') {
                $map['th_max'] = $col;
            } elseif (str_contains($headerText, 'obs') || str_contains($headerText, 'remarque')) {
                $map['observations'] = $col;
            } elseif (preg_match('/competence(s)?\s*(1|2|3|4|5|6|7)?|skill(s)?/iu', $headerText)) {
                $map['competences'][] = $col;
            } elseif (str_contains($headerText, 'cycle')) {
                $map['cycle'] = $col;
                $map['is_legacy'] = true;
            } elseif (str_contains($headerText, 'depart') || str_contains($headerText, 'départ')) {
                $map['department'] = $col;
                $map['is_legacy'] = true;
            }
        }

        if ($map['subject'] === null && isset($headers['A'])) {
            $first = mb_strtolower(trim((string) $headers['A']));
            if (str_contains($first, 'mati') || str_contains($first, 'subject') || $first !== '') {
                $map['subject'] = 'A';
            }
        }

        if ($map['subject'] === null) {
            $this->logError(1, "Feuille '{$sheetName}', En-tête : Colonne 'Matière' introuvable. Veuillez utiliser le modèle d'importation officiel.");
            return null;
        }

        if ($map['coef'] === null && isset($headers['B'])) $map['coef'] = 'B';
        if ($map['group'] === null && isset($headers['C'])) $map['group'] = 'C';
        
        // PRIORITÉ: Reconnaître d'abord les colonnes Classe 1-5 (D-H) et Compétence 1-7 (I-O)
        if (empty($map['classes'])) {
            foreach (range('D', 'H') as $col) {
                if (isset($headers[$col])) {
                    $map['classes'][] = $col;
                }
            }
        }
        if (empty($map['competences'])) {
            foreach (range('I', 'O') as $col) {
                if (isset($headers[$col])) {
                    $map['competences'][] = $col;
                }
            }
        }
        
        // Fallback pour VHM/VHP/TH/Observations: ne pas utiliser D-O (réservés pour classes et compétences)
        if ($map['vhm'] === null && isset($headers['P']) && !$map['is_legacy']) $map['vhm'] = 'P';
        if ($map['vhp'] === null && isset($headers['Q']) && !$map['is_legacy']) $map['vhp'] = 'Q';
        if ($map['th_max'] === null && isset($headers['R']) && !$map['is_legacy']) $map['th_max'] = 'R';
        if ($map['observations'] === null && isset($headers['S']) && !$map['is_legacy']) $map['observations'] = 'S';

        return $map;
    }

    private function rowHasData(array $row, array $colMap): bool
    {
        $subjectCol = $colMap['subject'] ?? 'A';
        $subjectVal = trim((string) ($row[$subjectCol] ?? ''));
        if ($subjectVal !== '') {
            return true;
        }

        foreach (range('A', 'O') as $col) {
            if (trim((string) ($row[$col] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    private function processRow(array $row, int $line, ?int $teachingTypeId, string $sheetName, array $colMap): void
    {
        $subjectCol = $colMap['subject'] ?? 'A';
        $subjectName = trim((string) ($row[$subjectCol] ?? ''));

        // Ignorer les lignes de somme/total (ex: TOTAL VHm)
        if (mb_strpos(mb_strtolower($subjectName), 'total') === 0) {
            return;
        }

        if ($subjectName === '') {
            $this->logError($line, "Feuille '{$sheetName}', Colonne {$subjectCol} (Matière) : L'intitulé de la matière est obligatoire. Correction attendue : Renseigner un nom de matière.");
            return;
        }

        // Parsing Coefficient
        $coefCol = $colMap['coef'] ?? 'B';
        $coefficientRaw = trim((string) ($row[$coefCol] ?? ''));
        $coefficient = 1;
        if ($coefficientRaw !== '') {
            if (!is_numeric($coefficientRaw) || (float) $coefficientRaw <= 0) {
                $this->logError($line, "Feuille '{$sheetName}', Colonne {$coefCol} (Coef) : La valeur '{$coefficientRaw}' est invalide. Correction attendue : Renseigner un nombre supérieur à 0.");
                return;
            }
            $coefficient = (float) $coefficientRaw;
        }

        // Parsing Groupe
        $groupCol = $colMap['group'] ?? 'C';
        $groupRaw = trim((string) ($row[$groupCol] ?? ''));
        $subjectGroupId = null;
        $groupe = 'Groupe 1';
        if ($groupRaw !== '' && $groupRaw !== '-') {
            $subjectGroupId = $this->resolveSubjectGroupId($groupRaw, $line, $groupCol, $sheetName);
            if ($subjectGroupId === false) {
                return; // Erreur enregistrée dans logError
            }
            $groupe = $groupRaw;
        }

        // Parsing Classes
        $classNames = $this->extractClassNamesFromRow($row, $colMap);
        if (empty($classNames)) {
            $classesCols = !empty($colMap['classes']) ? implode(', ', $colMap['classes']) : 'D:H';
            $this->logError($line, "Feuille '{$sheetName}', Colonnes {$classesCols} (Classes concernées) : Aucune classe n'a été renseignée. Correction attendue : remplir au moins une des 5 colonnes Classe 1 à Classe 5 avec un nom de classe existant.");
            return;
        }

        $classIds = $this->resolveClassIds($classNames, $line, !empty($colMap['classes']) ? implode(', ', $colMap['classes']) : 'D:H', $sheetName);
        if (empty($classIds)) {
            return;
        }

        // Parsing VHm (Volume Horaire Ministériel)
        $vhmCol = $colMap['vhm'] ?? null;  // Pas de fallback à 'E' - E est réservé pour Classe 2
        $vhmRaw = trim((string) ($vhmCol ? ($row[$vhmCol] ?? '') : ''));
        $vhm = null;
        if ($vhmRaw !== '') {
            if (!is_numeric($vhmRaw) || (float) $vhmRaw < 0) {
                $this->logError($line, "Feuille '{$sheetName}', Colonne {$vhmCol} (VHm) : La valeur '{$vhmRaw}' n'est pas un nombre valide. Correction attendue : Renseigner un nombre supérieur ou égal à 0.");
                return;
            }
            $vhm = (float) $vhmRaw;
        }

        // Parsing VHp (Volume Horaire Proposé)
        $vhpCol = $colMap['vhp'] ?? null;  // Pas de fallback à 'F' - F est réservé pour Classe 3
        $vhpRaw = trim((string) ($vhpCol ? ($row[$vhpCol] ?? '') : ''));
        $vhp = null;
        if ($vhpRaw !== '') {
            if (!is_numeric($vhpRaw) || (float) $vhpRaw < 0) {
                $this->logError($line, "Feuille '{$sheetName}', Colonne {$vhpCol} (VHp) : La valeur '{$vhpRaw}' n'est pas un nombre valide. Correction attendue : Renseigner un nombre supérieur ou égal à 0.");
                return;
            }
            $vhp = (float) $vhpRaw;
        }

        // Parsing TH(Max) (Taux Horaire Maximal)
        $thMaxCol = $colMap['th_max'] ?? null;  // Pas de fallback à 'G' - G est réservé pour Classe 4
        $thMaxRaw = trim((string) ($thMaxCol ? ($row[$thMaxCol] ?? '') : ''));
        $thMax = null;
        if ($thMaxRaw !== '') {
            if (!is_numeric($thMaxRaw) || (float) $thMaxRaw < 0) {
                $this->logError($line, "Feuille '{$sheetName}', Colonne {$thMaxCol} (TH(Max)) : La valeur '{$thMaxRaw}' n'est pas un nombre valide. Correction attendue : Renseigner un nombre supérieur ou égal à 0.");
                return;
            }
            $thMax = (float) $thMaxRaw;
        }

        // Parsing Observations
        $obsCol = $colMap['observations'] ?? null;  // Pas de fallback à 'H' - H est réservé pour Classe 5
        $obsRaw = trim((string) ($obsCol ? ($row[$obsCol] ?? '') : ''));
        $observations = $obsRaw !== '' ? $obsRaw : null;

        // Parsing Compétences
        $competenceLabels = [];
        foreach ($colMap['competences'] ?? ['I', 'J', 'K', 'L', 'M', 'N', 'O'] as $competenceCol) {
            $competenceRaw = trim((string) ($row[$competenceCol] ?? ''));
            $competenceLabels = array_merge($competenceLabels, $this->extractCompetencyNames($competenceRaw));
        }
        $competenceLabels = array_values(array_unique(array_filter($competenceLabels, static fn($value) => trim((string) $value) !== '')));

        // Mode UPSERT : Mise à jour si la matière existe déjà, sinon Insertion sans doublon
        try {
            $stmtCheck = $this->db->prepare("SELECT id FROM subjects WHERE LOWER(TRIM(nom)) = LOWER(TRIM(?)) LIMIT 1");
            $stmtCheck->execute([$subjectName]);
            $existingId = $stmtCheck->fetchColumn();

            if ($existingId) {
                $subjectId = (int) $existingId;
                $stmtUpdate = $this->db->prepare("
                    UPDATE subjects 
                    SET coefficient = ?, 
                        groupe = ?, 
                        subject_group_id = COALESCE(?, subject_group_id), 
                        vhm = ?, 
                        vhp = ?, 
                        th_max = ?, 
                        observations = ?
                    WHERE id = ?
                ");
                $stmtUpdate->execute([$coefficient, $groupe, $subjectGroupId, $vhm, $vhp, $thMax, $observations, $subjectId]);

                $stmtInsCl = $this->db->prepare("
                    INSERT INTO subject_classes (subject_id, class_id, academic_year_id) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE subject_id = subject_id
                ");
                foreach ($classIds as $classId) {
                    $stmtInsCl->execute([$subjectId, $classId, $this->activeYearId]);
                }

                $this->saveSubjectCompetencies($subjectId, $competenceLabels, $line, $sheetName, $competenceCol);
                $this->successCount++;
            } else {
                $stmtInsert = $this->db->prepare("
                    INSERT INTO subjects (nom, coefficient, groupe, subject_group_id, teaching_type_id, vhm, vhp, th_max, observations, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
                ");
                $stmtInsert->execute([$subjectName, $coefficient, $groupe, $subjectGroupId, $teachingTypeId, $vhm, $vhp, $thMax, $observations]);
                $subjectId = (int) $this->db->lastInsertId();
                if ($subjectId <= 1) {
                    // Fallback basé sur le nom (ordre décroissant d'id)
                    try {
                        $fb = $this->db->prepare("SELECT id FROM subjects WHERE LOWER(TRIM(nom)) = LOWER(TRIM(?)) ORDER BY id DESC LIMIT 1");
                        $fb->execute([$subjectName]);
                        $found = (int) $fb->fetchColumn();
                        if ($found > 0) {
                            $subjectId = $found;
                        } else {
                            $this->logError($line, "Impossible de récupérer l'ID pour la matière '{$subjectName}' après insertion.");
                        }
                    } catch (\Throwable $e) {
                        $this->logError($line, "Fallback select id échoué: " . $e->getMessage());
                    }
                }

                $stmtInsCl = $this->db->prepare("
                    INSERT INTO subject_classes (subject_id, class_id, academic_year_id) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE subject_id = subject_id
                ");
                foreach ($classIds as $classId) {
                    $stmtInsCl->execute([$subjectId, $classId, $this->activeYearId]);
                }

                $this->saveSubjectCompetencies($subjectId, $competenceLabels, $line, $sheetName, $competenceCol);
                $this->successCount++;
            }
        } catch (\Throwable $e) {
            $this->logError($line, "Feuille '{$sheetName}' : Erreur base de données : " . $e->getMessage());
        }
    }

    private function extractCompetencyNames(string $raw): array
    {
        if (trim($raw) === '') {
            return [];
        }

        $parts = preg_split('/[\n\r,;|\/]+/', $raw);
        $names = [];
        foreach ($parts as $part) {
            $name = trim((string) $part);
            if ($name !== '' && $name !== '-') {
                $names[] = $name;
            }
        }

        return array_values(array_unique($names));
    }

    private function saveSubjectCompetencies(int $subjectId, array $labels, int $line, string $sheetName, string $competenceCol): void
    {
        $deleteStmt = $this->db->prepare("DELETE FROM competencies WHERE subject_id = ?");
        $deleteStmt->execute([$subjectId]);

        if (empty($labels)) {
            return;
        }

        $insertStmt = $this->db->prepare("INSERT INTO competencies (subject_id, libelle, position, created_by) VALUES (?, ?, ?, ?)");
        
        // Utiliser l'ID de l'utilisateur admin (40) ou chercher le premier utilisateur disponible
        $createdById = 40;
        $userCheckStmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
        $userCheckStmt->execute([$createdById]);
        if (!$userCheckStmt->fetch()) {
            // Si l'ID 40 n'existe pas, chercher le premier utilisateur
            $firstUserStmt = $this->db->query("SELECT MIN(id) as id FROM users");
            $firstUser = $firstUserStmt->fetch(PDO::FETCH_ASSOC);
            $createdById = $firstUser['id'] ?? 1;
        }
        
        foreach ($labels as $index => $label) {
            $clean = trim((string) $label);
            if ($clean === '') {
                continue;
            }
            $insertStmt->execute([$subjectId, $clean, $index + 1, $createdById]);
        }
    }

    private function extractClassNamesFromRow(array $row, array $colMap): array
    {
        $classNames = [];

        $classCols = $colMap['classes'] ?? ['D', 'E', 'F', 'G', 'H'];
        $classCols = is_array($classCols) ? $classCols : [$classCols];

        foreach ($classCols as $classCol) {
            $raw = trim((string) ($row[$classCol] ?? ''));
            if ($raw === '' || $raw === '-') {
                continue;
            }

            $parts = preg_split('/[\n\r,;\/]+/', $raw);
            foreach ($parts as $part) {
                $trimmed = trim((string) $part);
                if ($trimmed !== '' && $trimmed !== '-') {
                    $classNames[] = $trimmed;
                }
            }
        }

        return array_values(array_unique($classNames));
    }

    private function resolveClassIds(array $names, int $line, string $colLetter, string $sheetName): array
    {
        $ids = [];
        $missing = [];

        foreach ($names as $name) {
            $key = mb_strtolower(trim($name));
            if (isset($this->classesByName[$key])) {
                $ids[] = $this->classesByName[$key]['id'];
            } else {
                $missing[] = $name;
            }
        }

        if (!empty($missing)) {
            $this->logError($line, "Feuille '{$sheetName}', Colonne {$colLetter} (Classes concernées) : Classe(s) introuvable(s) dans l'établissement : " . implode(', ', $missing) . ". Correction attendue : Vérifier l'orthographe des classes.");
            return [];
        }

        return array_values(array_unique($ids));
    }

    private function resolveTeachingTypeIdBySheetName(string $sheetName): ?int
    {
        $key = mb_strtolower(trim($sheetName));
        foreach ($this->teachingTypesByName as $name => $id) {
            $truncatedName = mb_substr($name, 0, 31);
            if ($truncatedName === $key) {
                return $id;
            }
        }
        return null;
    }

    /**
     * @return int|false
     */
    private function resolveSubjectGroupId(string $name, int $line, string $colLetter, string $sheetName)
    {
        $key = mb_strtolower(trim($name));
        if (isset($this->subjectGroupsByName[$key])) {
            return $this->subjectGroupsByName[$key]['id'];
        }
        
        $this->logError($line, "Feuille '{$sheetName}', Colonne {$colLetter} (Groupe) : Groupe de modules introuvable : '{$name}'. Correction attendue : Saisir un nom de groupe valide.");
        return false;
    }

    private function warmupData(): void
    {
        // Récupérer toutes les classes actives
        $rows = $this->db->query("
            SELECT c.id, c.nom, c.teaching_type_id, c.cycle_id 
            FROM classes c
            LEFT JOIN departments d ON c.department_id = d.id
            LEFT JOIN cycles cy ON c.cycle_id = cy.id
            LEFT JOIN sections sec ON c.section_id = sec.id
            LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id
            WHERE (c.department_id IS NULL OR d.status = 1)
              AND (c.cycle_id IS NULL OR cy.status = 1)
              AND (c.section_id IS NULL OR sec.status = 1)
              AND (c.teaching_type_id IS NULL OR tt.actif = 1)
        ")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $this->classesByName[mb_strtolower(trim((string) $row['nom']))] = [
                'id' => (int) $row['id'],
                'teaching_type_id' => $row['teaching_type_id'] !== null ? (int) $row['teaching_type_id'] : null,
                'cycle_id' => $row['cycle_id'] !== null ? (int) $row['cycle_id'] : null
            ];
        }

        // Récupérer les types d'enseignement actifs
        $tts = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($tts as $tt) {
            $this->teachingTypesByName[mb_strtolower(trim((string) $tt['nom']))] = (int) $tt['id'];
        }

        // Récupérer les départements actifs
        $depts = $this->db->query("
            SELECT d.id, d.nom, d.teaching_type_id 
            FROM departments d
            LEFT JOIN teaching_types tt ON d.teaching_type_id = tt.id
            WHERE d.status = 1 AND (d.teaching_type_id IS NULL OR tt.actif = 1)
        ")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($depts as $dept) {
            $this->departmentsByName[mb_strtolower(trim((string) $dept['nom']))] = [
                'id' => (int) $dept['id'],
                'teaching_type_id' => $dept['teaching_type_id'] !== null ? (int) $dept['teaching_type_id'] : null
            ];
        }

        // Récupérer les cycles actifs
        $cycles = $this->db->query("
            SELECT cy.id, cy.nom, cy.teaching_type_id 
            FROM cycles cy
            LEFT JOIN teaching_types tt ON cy.teaching_type_id = tt.id
            WHERE cy.status = 1 AND (cy.teaching_type_id IS NULL OR tt.actif = 1)
        ")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cycles as $cy) {
            $this->cyclesByName[mb_strtolower(trim((string) $cy['nom']))] = [
                'id' => (int) $cy['id'],
                'teaching_type_id' => $cy['teaching_type_id'] !== null ? (int) $cy['teaching_type_id'] : null
            ];
        }

        // Récupérer les groupes de modules actifs
        $groups = $this->db->query("
            SELECT sg.id, sg.libelle, sg.teaching_type_id 
            FROM subject_groups sg
            LEFT JOIN teaching_types tt ON sg.teaching_type_id = tt.id
            WHERE sg.status = 1 AND (sg.teaching_type_id IS NULL OR tt.actif = 1)
        ")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($groups as $g) {
            $this->subjectGroupsByName[mb_strtolower(trim((string) $g['libelle']))] = [
                'id' => (int) $g['id'],
                'teaching_type_id' => $g['teaching_type_id'] !== null ? (int) $g['teaching_type_id'] : null
            ];
        }
    }

    private function setActiveYear(): void
    {
        $stmt = $this->db->prepare("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1");
        $stmt->execute();
        $activeId = $stmt->fetchColumn();
        if (!$activeId) {
            $activeId = $this->db->query("SELECT id FROM academic_years LIMIT 1")->fetchColumn();
        }
        $this->activeYearId = (int) $activeId;
    }

    private function logError(int $line, string $message): void
    {
        $this->errors[] = "Ligne {$line} : {$message}";
    }
}
