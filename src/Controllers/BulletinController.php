<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Services\SettingsStore;
use PDO;

class BulletinController
{
    protected $db;
    protected $cache = [];

    private const DEFAULT_SEQUENCES = [
        ['code' => 'SEQ1', 'label' => 'Trimestre 1 - Sequence 1', 'trimestre' => 1, 'position' => 1],
        ['code' => 'SEQ2', 'label' => 'Trimestre 1 - Sequence 2', 'trimestre' => 1, 'position' => 2],
        ['code' => 'SEQ3', 'label' => 'Trimestre 2 - Sequence 3', 'trimestre' => 2, 'position' => 3],
        ['code' => 'SEQ4', 'label' => 'Trimestre 2 - Sequence 4', 'trimestre' => 2, 'position' => 4],
        ['code' => 'SEQ5', 'label' => 'Trimestre 3 - Sequence 5', 'trimestre' => 3, 'position' => 5],
        ['code' => 'SEQ6', 'label' => 'Trimestre 3 - Sequence 6', 'trimestre' => 3, 'position' => 6],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureSequencesSchema();
        $this->ensureDisciplineSchema();
    }

    public function index()
    {
        $academicYears = $this->db->query("SELECT id, nom, is_active FROM academic_years ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        if ($academicYearId <= 0) {
            $activeYear = $this->getActiveAcademicYear();
            $academicYearId = (int) $activeYear['id'];
        }

        $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $sectionId = (int) ($_GET['section_id'] ?? 0);

        // Classes are now shared across years, no year filtering
        $classesQuery = "SELECT c.id, c.nom FROM classes c";
        $classesParams = [];
        if ($sectionId > 0) {
            $classesQuery .= " AND c.section_id = ?";
            $classesParams[] = $sectionId;
        }
        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'], true)) {
            $academicYearId = $this->getActiveAcademicYear()['id'] ?? 0;
            $classesQuery .= " AND EXISTS (SELECT 1 FROM teacher_assignments ta WHERE ta.class_id = c.id AND ta.user_id = ? AND ta.academic_year_id = {$academicYearId})";
            $classesParams[] = (int) Session::get('user_id');
        }
        $classesQuery .= " ORDER BY c.nom ASC";

        $stmt = $this->db->prepare($classesQuery);
        $stmt->execute($classesParams);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $classId = (int) ($_GET['class_id'] ?? 0);
        $students = $classId > 0 ? $this->getStudentsByClass($classId) : [];
        $sequences = $this->getActiveSequences();
        $terms = [1, 2, 3];

        include __DIR__ . '/../Views/bulletins/index.php';
    }

    public function discipline()
    {
        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'], true)) {
            header("Location: /bulletins");
            exit;
        }

        $academicYears = $this->db->query("SELECT id, nom, is_active FROM academic_years ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        if ($academicYearId <= 0) {
            $activeYear = $this->getActiveAcademicYear();
            $academicYearId = (int) ($activeYear['id'] ?? 0);
        }

        // Classes are now shared across years, no year filtering
        $classes = $this->db->query("SELECT id, nom FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $classId = (int) ($_GET['class_id'] ?? 0);
        $term = (int) ($_GET['term'] ?? 1);
        if (!in_array($term, [1, 2, 3], true)) {
            $term = 1;
        }

        $students = $classId > 0 ? $this->getStudentsByClass($classId) : [];
        $disciplineMap = $classId > 0 ? $this->getClassDisciplineFormMap($classId, (string)$term, $academicYearId) : [];

        $flashSuccess = Session::getFlash('discipline_success');
        $flashError = Session::getFlash('discipline_error');

        include __DIR__ . '/../Views/bulletins/discipline.php';
    }

    public function saveDiscipline()
    {
        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'], true)) {
            header("Location: /bulletins");
            exit;
        }

        $classId = (int) ($_POST['class_id'] ?? 0);
        $term = (int) ($_POST['term'] ?? 0);
        $academicYearId = (int) ($_POST['academic_year_id'] ?? 0);
        $absTotalMap = $_POST['absences_total'] ?? [];
        $absJustifiedMap = $_POST['absences_justified'] ?? [];
        $absUnjustifiedMap = $_POST['absences_unjustified'] ?? [];
        $exclusionDaysMap = $_POST['exclusion_days'] ?? [];
        $consignesMap = $_POST['consignes'] ?? [];
        $warningConductMap = $_POST['warning_conduct'] ?? [];
        $blameConductMap = $_POST['blame_conduct'] ?? [];
        $warningWorkMap = $_POST['warning_work'] ?? [];
        $tableauHonneurMap = $_POST['tableau_honneur'] ?? [];
        $encouragementsMap = $_POST['encouragements'] ?? [];
        $felicitationsMap = $_POST['felicitations'] ?? [];

        if (
            $classId <= 0
            || !in_array($term, [1, 2, 3], true)
            || $academicYearId <= 0
            || !is_array($absTotalMap)
            || !is_array($absJustifiedMap)
            || !is_array($absUnjustifiedMap)
            || !is_array($exclusionDaysMap)
            || !is_array($consignesMap)
            || !is_array($warningConductMap)
            || !is_array($blameConductMap)
            || !is_array($warningWorkMap)
            || !is_array($tableauHonneurMap)
            || !is_array($encouragementsMap)
            || !is_array($felicitationsMap)
        ) {
            Session::setFlash('discipline_error', __('discipline_save_failed'));
            header("Location: /bulletins/discipline?class_id={$classId}&term={$term}&academic_year_id={$academicYearId}");
            exit;
        }

        $period = (string) $term; // Utiliser directement 1, 2 ou 3 comme période pour correspondre aux bulletins

        try {
            $this->db->beginTransaction();

            // 1. Récupérer tous les élèves de la classe pour s'assurer de tout enregistrer (même les 0)
            $academicYearId = $this->getActiveAcademicYear()['id'] ?? 0;
            $studentIdsStmt = $this->db->prepare("SELECT id FROM students WHERE class_id = ? AND academic_year_id = ?");
            $studentIdsStmt->execute([$classId, $academicYearId]);
            $allStudentIds = $studentIdsStmt->fetchAll(PDO::FETCH_COLUMN);

            // 2. Préparer les requêtes (Ciblées uniquement sur la discipline pour ne pas casser les mentions)
            $selectStmt = $this->db->prepare("SELECT id FROM discipline WHERE student_id = ? AND academic_year_id = ? AND periode = ? LIMIT 1");
            
            $updateStmt = $this->db->prepare("UPDATE discipline SET 
                    absences_total = ?, absences_justified = ?, exclusion_days = ?, 
                    consignes = ?, warning_conduct = ?, blame_conduct = ?
                WHERE id = ?");

            $insertStmt = $this->db->prepare("INSERT INTO discipline (
                    student_id, academic_year_id, periode,
                    absences_total, absences_justified, exclusion_days, 
                    consignes, warning_conduct, blame_conduct,
                    warning_work, tableau_honneur, encouragements, felicitations
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, '', '', '', '')");

            foreach ($allStudentIds as $studentId) {
                $studentId = (int) $studentId;

                $total = max(0, (int) ($absTotalMap[$studentId] ?? 0));
                $justified = max(0, (int) ($absJustifiedMap[$studentId] ?? 0));
                $unjustified = max(0, (int) ($absUnjustifiedMap[$studentId] ?? 0));
                $exclusionDays = max(0, (int) ($exclusionDaysMap[$studentId] ?? 0));
                $consignes = max(0, (int) ($consignesMap[$studentId] ?? 0));
                $warningConduct = trim((string) ($warningConductMap[$studentId] ?? ''));
                $blameConduct = trim((string) ($blameConductMap[$studentId] ?? ''));

                // Si "non justifiées" est saisi (même si lecture seule en JS), on harmonise.
                if ($unjustified > 0) {
                    $total = max($total, $justified + $unjustified);
                    $justified = max(0, $total - $unjustified);
                } elseif ($justified > $total) {
                    $justified = $total;
                }

                if (function_exists('mb_substr')) {
                    $warningConduct = mb_substr($warningConduct, 0, 20, 'UTF-8');
                    $blameConduct = mb_substr($blameConduct, 0, 20, 'UTF-8');
                } else {
                    $warningConduct = substr($warningConduct, 0, 20);
                    $blameConduct = substr($blameConduct, 0, 20);
                }

                $selectStmt->execute([$studentId, $academicYearId, $period]);
                $existingId = (int) ($selectStmt->fetchColumn() ?: 0);

                if ($existingId > 0) {
                    $updateStmt->execute([
                        $total, $justified, $exclusionDays, 
                        $consignes, $warningConduct, $blameConduct,
                        $existingId
                    ]);
                } else {
                    $insertStmt->execute([
                        $studentId, $academicYearId, $period, 
                        $total, $justified, $exclusionDays, 
                        $consignes, $warningConduct, $blameConduct
                    ]);
                }
            }

            $this->db->commit();
            Session::setFlash('discipline_success', __('discipline_saved_success'));
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            Session::setFlash('discipline_error', __('discipline_save_failed'));
        }

        header("Location: /bulletins/discipline?class_id={$classId}&term={$term}&academic_year_id={$academicYearId}");
        exit;
    }

    public function sequence()
    {
        $studentId = (int) ($_GET['student_id'] ?? 0);
        $sequenceId = (int) ($_GET['sequence_id'] ?? 0);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        $student = $this->getAccessibleStudent($studentId);
        $sequence = $this->getSequenceById($sequenceId);

        if (!$student || !$sequence || !(int) $sequence['is_active']) {
            header("Location: /bulletins");
            exit;
        }

        // Détection automatique de la langue selon la section (Anglophone -> English)
        $this->detectAndApplyBulletinLanguage((int)$student['class_id']);

        $academicYear = $this->resolveAcademicYear($academicYearId);
        $ranking = $this->computeSequenceRanking((int) $student['class_id'], $sequence['label'], (int) $academicYear['id']);
        $data = $this->buildSequenceBulletinData($student, $sequence, $academicYear, $ranking);
        $pdf_filename = $this->buildPdfFileNameStudent($student, $sequence['label']);
        $data['pdf_filename'] = $pdf_filename;
        extract($data);

        if (($_GET['format'] ?? '') === 'pdf') {
            ob_start();
            $isPdf = true;
            include __DIR__ . '/../Views/bulletins/sequence.php';
            $html = ob_get_clean();
            $this->streamPdf($html, $pdf_filename . '.pdf');
            return;
        }

        include __DIR__ . '/../Views/bulletins/sequence.php';
    }

    public function trimestre()
    {
        $studentId = (int) ($_GET['student_id'] ?? 0);
        $term = (int) ($_GET['term'] ?? 0);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        $student = $this->getAccessibleStudent($studentId);

        if (!$student || !in_array($term, [1, 2, 3], true)) {
            header("Location: /bulletins");
            exit;
        }

        // Détection automatique de la langue selon la section (Anglophone -> English)
        $this->detectAndApplyBulletinLanguage((int)$student['class_id']);

        $academicYear = $this->resolveAcademicYear($academicYearId);
        $termSequences = $this->getActiveSequencesByTerm($term);
        $ranking = $this->computeTrimesterRanking((int) $student['class_id'], $termSequences, (int) $academicYear['id']);
        $data = $this->buildTrimesterBulletinData($student, $term, $academicYear, $ranking);
        $pdf_filename = $this->buildPdfFileNameStudent($student, 'TRIMESTRE ' . $term);
        $data['pdf_filename'] = $pdf_filename;
        extract($data);

        if (($_GET['format'] ?? '') === 'pdf') {
            ob_start();
            $isPdf = true;
            include __DIR__ . '/../Views/bulletins/trimestre.php';
            $html = ob_get_clean();
            $this->streamPdf($html, $pdf_filename . '.pdf');
            return;
        }

        include __DIR__ . '/../Views/bulletins/trimestre.php';
    }

    public function sequenceClass()
    {
        // Augmentation des limites pour les gros volumes (3000+ élèves)
        ini_set('memory_limit', '1024M');
        set_time_limit(600);

        $classId = (int) ($_GET['class_id'] ?? 0);
        $sequenceId = (int) ($_GET['sequence_id'] ?? 0);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        $sequence = $this->getSequenceById($sequenceId);
        $academicYear = $this->resolveAcademicYear($academicYearId);
        $students = $this->getStudentsByClass($classId);

        if (!$this->canAccessClass($classId) || !$sequence || !(int) $sequence['is_active'] || empty($students)) {
            header("Location: /bulletins");
            exit;
        }

        // Détection automatique de la langue selon la section (Anglophone -> English)
        $this->detectAndApplyBulletinLanguage($classId);

        $ranking = $this->computeSequenceRanking($classId, $sequence['label'], (int) $academicYear['id'], true);
        $students = $this->sortStudentsByRanking($students, $ranking);

        // Optimisation : précalculer les stats par matière une seule fois pour toute la classe
        // Pour la section anglophone, on utilise uniquement les matières avec des notes saisies
        $subjectStats = $this->getSubjectStatsForSequence($classId, $sequence['label'], (int) $academicYear['id']);

        $classNotesMap = $this->getClassSequenceNotesMap($classId, [$sequence['label']], (int) $academicYear['id']);
        $disciplinePeriode = 'trim_' . (int) ($sequence['trimestre'] ?? 1);
        $classDisciplineMap = $this->getClassDisciplineDataMap($classId, [$disciplinePeriode], (int) $academicYear['id']);

        $bulletins = [];
        foreach ($students as $student) {
            $studentId = (int) $student['id'];
            $bulletins[] = $this->buildSequenceBulletinData(
                $student,
                $sequence,
                $academicYear,
                $ranking,
                $subjectStats,
                $classNotesMap[$studentId] ?? [],
                $classDisciplineMap[$studentId] ?? null
            );
        }

        $classInfo = $this->getClassInfo($classId);
        $pdf_filename = $this->buildPdfFileNameClass($sequence['label'], $classInfo['nom'] ?? 'classe');

        if (($_GET['format'] ?? '') === 'pdf') {
            ob_start();
            $isPdf = true;
            include __DIR__ . '/../Views/bulletins/sequence_class.php';
            $html = ob_get_clean();
            $this->streamPdf($html, $pdf_filename . '.pdf');
            return;
        }

        include __DIR__ . '/../Views/bulletins/sequence_class.php';
    }

    public function trimestreClass()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(600);

        $classId = (int) ($_GET['class_id'] ?? 0);
        $term = (int) ($_GET['term'] ?? 0);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        $academicYear = $this->resolveAcademicYear($academicYearId);
        $students = $this->getStudentsByClass($classId);

        if (!$this->canAccessClass($classId) || !in_array($term, [1, 2, 3], true) || empty($students)) {
            header("Location: /bulletins");
            exit;
        }

        // Détection automatique de la langue selon la section (Anglophone -> English)
        $this->detectAndApplyBulletinLanguage($classId);

        $termSequences = $this->getActiveSequencesByTerm($term);
        $ranking = $this->computeTrimesterRanking($classId, $termSequences, (int) $academicYear['id'], true);
        $students = $this->sortStudentsByRanking($students, $ranking);

        // Optimisation : précalculer les stats par matière une seule fois pour toute la classe
        // Pour la section anglophone, on utilise uniquement les matières avec des notes saisies
        $subjectStats = $this->getSubjectStatsForTrimester($classId, $termSequences, (int) $academicYear['id']);

        $precomputedSeqRankings = [];
        foreach ($termSequences as $seq) {
            $precomputedSeqRankings[] = $this->computeSequenceRanking($classId, $seq['label'], (int) $academicYear['id']);
        }

        $sequenceLabels = array_column($termSequences, 'label');
        $classNotesMap = $this->getClassSequenceNotesMap($classId, $sequenceLabels, (int) $academicYear['id']);
        $classDisciplineMap = $this->getClassDisciplineDataMap($classId, [$term], (int) $academicYear['id']);

        $bulletins = [];
        foreach ($students as $student) {
            $studentId = (int) $student['id'];
            $bulletins[] = $this->buildTrimesterBulletinData(
                $student,
                $term,
                $academicYear,
                $ranking,
                $precomputedSeqRankings,
                $subjectStats,
                $classNotesMap[$studentId] ?? [],
                $classDisciplineMap[$studentId] ?? null
            );
        }

        $classInfo = $this->getClassInfo($classId);
        $pdf_filename = $this->buildPdfFileNameClass('TRIMESTRE ' . $term, $classInfo['nom'] ?? 'classe');

        if (($_GET['format'] ?? '') === 'pdf') {
            ob_start();
            $isPdf = true;
            include __DIR__ . '/../Views/bulletins/trimestre_class.php';
            $html = ob_get_clean();
            $this->streamPdf($html, $pdf_filename . '.pdf');
            return;
        }

        include __DIR__ . '/../Views/bulletins/trimestre_class.php';
    }

    public function annuel()
    {
        $studentId = (int) ($_GET['student_id'] ?? 0);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        $student = $this->getAccessibleStudent($studentId);

        if (!$student) {
            header("Location: /bulletins");
            exit;
        }

        // Détection automatique de la langue selon la section (Anglophone -> English)
        $this->detectAndApplyBulletinLanguage((int)$student['class_id']);

        $academicYear = $this->resolveAcademicYear($academicYearId);
        $termSequencesByTerm = [
            1 => $this->getActiveSequencesByTerm(1),
            2 => $this->getActiveSequencesByTerm(2),
            3 => $this->getActiveSequencesByTerm(3),
        ];
        $ranking = $this->computeAnnualRanking((int) $student['class_id'], $termSequencesByTerm, (int) $academicYear['id']);
        $data = $this->buildAnnualBulletinData($student, $academicYear, $ranking);
        $pdf_filename = $this->buildPdfFileNameStudent($student, 'ANNUEL');
        $data['pdf_filename'] = $pdf_filename;
        extract($data);

        if (($_GET['format'] ?? '') === 'pdf') {
            ob_start();
            $isPdf = true;
            include __DIR__ . '/../Views/bulletins/annuel.php';
            $html = ob_get_clean();
            $this->streamPdf($html, $pdf_filename . '.pdf');
            return;
        }

        include __DIR__ . '/../Views/bulletins/annuel.php';
    }

    public function annuelClass()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(600);

        $classId = (int) ($_GET['class_id'] ?? 0);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        $academicYear = $this->resolveAcademicYear($academicYearId);
        $students = $this->getStudentsByClass($classId);

        if (!$this->canAccessClass($classId) || empty($students)) {
            header("Location: /bulletins");
            exit;
        }

        // Détection automatique de la langue selon la section (Anglophone -> English)
        $this->detectAndApplyBulletinLanguage($classId);

        $termSequencesByTerm = [
            1 => $this->getActiveSequencesByTerm(1),
            2 => $this->getActiveSequencesByTerm(2),
            3 => $this->getActiveSequencesByTerm(3),
        ];
        $ranking = $this->computeAnnualRanking($classId, $termSequencesByTerm, (int) $academicYear['id'], true);
        $students = $this->sortStudentsByRanking($students, $ranking);

        // Optimisation : précalculer les stats par matière une seule fois pour toute la classe
        // Pour la section anglophone, on utilise uniquement les matières avec des notes saisies
        $subjectStats = $this->getSubjectStatsForAnnual($classId, $termSequencesByTerm, (int) $academicYear['id']);

        $precomputedTrimRankings = [];
        foreach ([1, 2, 3] as $term) {
            $precomputedTrimRankings[$term] = $this->computeTrimesterRanking($classId, $termSequencesByTerm[$term], (int) $academicYear['id']);
        }

        $allSequenceLabels = [];
        foreach ($termSequencesByTerm as $seqs) {
            $allSequenceLabels = array_merge($allSequenceLabels, array_column($seqs, 'label'));
        }
        $classNotesMap = $this->getClassSequenceNotesMap($classId, $allSequenceLabels, (int) $academicYear['id']);
        $classDisciplineMap = $this->getClassDisciplineDataMap($classId, ['trim_1', 'trim_2', 'trim_3'], (int) $academicYear['id']);

        $bulletins = [];
        foreach ($students as $student) {
            $studentId = (int) $student['id'];
            $bulletins[] = $this->buildAnnualBulletinData(
                $student,
                $academicYear,
                $ranking,
                $precomputedTrimRankings,
                $subjectStats,
                $classNotesMap[$studentId] ?? [],
                $classDisciplineMap[$studentId] ?? null
            );
        }

        $classInfo = $this->getClassInfo($classId);
        $pdf_filename = $this->buildPdfFileNameClass('ANNUEL', $classInfo['nom'] ?? 'classe');

        if (($_GET['format'] ?? '') === 'pdf') {
            ob_start();
            $isPdf = true;
            include __DIR__ . '/../Views/bulletins/annuel_class.php';
            $html = ob_get_clean();
            $this->streamPdf($html, $pdf_filename . '.pdf');
            return;
        }

        include __DIR__ . '/../Views/bulletins/annuel_class.php';
    }

    protected function buildSequenceBulletinData(array $student, array $sequence, ?array $academicYear = null, ?array $precomputedRanking = null, ?array $precomputedSubjectStats = null, ?array $precomputedNotesMap = null, ?array $precomputedDiscipline = null)
    {
        $activeYear = $academicYear ?? $this->getActiveAcademicYear();
        // Pour la section anglophone, on utilise uniquement les matières avec des notes saisies
        $isEnglishSection = $this->isEnglishSection((int) $student['class_id']);
        if ($isEnglishSection) {
            $subjects = $this->getSubjectsWithGrades((int) $student['class_id'], [$sequence['label']], (int) $activeYear['id']);
        } else {
            $subjects = $this->getClassSubjects((int) $student['class_id']);
        }
        $notesMap = $precomputedNotesMap ?? $this->getStudentSequenceNotesMap((int) $student['id'], (int) $student['class_id'], [$sequence['label']], (int) $activeYear['id']);
        $subjectStats = $precomputedSubjectStats ?? $this->getSubjectStatsForSequence((int) $student['class_id'], $sequence['label'], (int) $activeYear['id']);

        $rows = [];
        $weightedSum = 0.0;
        $coeffSum = 0.0;
        $coeffValidSum = 0.0;

        foreach ($subjects as $subject) {
            $key = $subject['id'] . '|' . $sequence['label'];
            $note = $notesMap[$key] ?? null;
            $value = $note !== null ? (float) $note['valeur'] : null;
            $coefficient = (float) $subject['coefficient'];

            // Pour la section anglophone, on n'inclut que les matières avec des notes dans le calcul
            if ($isEnglishSection) {
                if ($value !== null) {
                    $weightedSum += $value * $coefficient;
                    $coeffSum += $coefficient;
                    $coeffValidSum += $coefficient;
                }
            } else {
                $weightedSum += ($value ?? 0.0) * $coefficient;
                $coeffSum += $coefficient;
                if ($value !== null) {
                    $coeffValidSum += $coefficient;
                }
            }

            $subjStats = $subjectStats[$subject['id']] ?? null;
            $classAverageSubj = $subjStats['average'] ?? null;
            $studentRankSubj = $subjStats['student_ranks'][$student['id']] ?? null;

            $rows[] = [
                'subject' => $subject['nom'],
                'teacher' => strtoupper((string) ($subject['teacher_name'] ?? '')),
                'group' => $subject['groupe'] ?? __('group_default'),
                'note' => $value,
                'coefficient' => $coefficient,
                'weighted' => ($value !== null ? round($value * $coefficient, 2) : null),
                'class_average_subject' => $classAverageSubj,
                'rank_subject' => $studentRankSubj,
                'appreciation' => $this->getAcquisitionLevel($value),
            ];
        }

        $coeffValidSum = 0.0;
        foreach ($rows as $r) {
            if (($r['note'] ?? 0) >= 10) {
                $coeffValidSum += (float) $r['coefficient'];
            }
        }

        $average = $coeffSum > 0 ? round($weightedSum / $coeffSum, 2) : null;
        $ranking = $precomputedRanking ?? $this->computeSequenceRanking((int) $student['class_id'], $sequence['label'], (int) $activeYear['id']);
        $rank = $ranking[(int) $student['id']]['rank'] ?? null;
        $classStats = $this->buildClassStats($ranking);
        $mention = $this->getMention($average);
        $tableFont = $this->getTableFontSize(count($rows));
        $groupedRows = $this->groupRowsBySubjectGroup($rows);
        // La période discipline correspond au trimestre de la séquence
        $disciplinePeriode = 'trim_' . (int) ($sequence['trimestre'] ?? 1);
        $discipline = $precomputedDiscipline ?? $this->buildDisciplineData($student, [$disciplinePeriode], (int) $activeYear['id']);
        $professor_name = $this->getProfessorPrincipalName((int) $student['class_id']);

        return [
            'bulletinType' => __('bulletin_sequence'),
            'student' => $student,
            'sequence' => $sequence,
            'rows' => $rows,
            'groupedRows' => $groupedRows,
            'average' => $average,
            'total_coefficients' => $coeffSum,
            'total_coef_valide' => $coeffValidSum,
            'rank' => $rank,
            'mention' => $mention,
            'classStats' => $classStats,
            'effectif' => count($ranking),
            'tableFont' => $tableFont,
            'activeYear' => $activeYear,
            'institution' => $this->getInstitutionSettings(),
            'evaluationLabels' => [$this->getShortSequenceLabel((string) $sequence['label'])],
            'discipline' => $discipline,
            'professor_name' => $professor_name,
            'displayMatricule' => $this->getDisplayMatricule($student),
            'globalAppreciation' => $this->getCouncilAppreciation($average),
        ];
    }

    protected function buildTrimesterBulletinData(array $student, int $term, ?array $academicYear = null, ?array $precomputedRanking = null, array $precomputedSeqRankings = [], ?array $precomputedSubjectStats = null, ?array $precomputedNotesMap = null, ?array $precomputedDiscipline = null)
    {
        $activeYear = $academicYear ?? $this->getActiveAcademicYear();
        $termSequences = $this->getActiveSequencesByTerm($term);
        // Pour la section anglophone, on utilise uniquement les matières avec des notes saisies
        $isEnglishSection = $this->isEnglishSection((int) $student['class_id']);
        if ($isEnglishSection) {
            $sequenceLabels = array_column($termSequences, 'label');
            $subjects = $this->getSubjectsWithGrades((int) $student['class_id'], $sequenceLabels, (int) $activeYear['id']);
        } else {
            $subjects = $this->getClassSubjects((int) $student['class_id']);
        }
        $sequenceLabels = array_column($termSequences, 'label');
        $notesMap = $precomputedNotesMap ?? $this->getStudentSequenceNotesMap((int) $student['id'], (int) $student['class_id'], $sequenceLabels, (int) $activeYear['id']);
        $subjectStats = $precomputedSubjectStats ?? $this->getSubjectStatsForTrimester((int) $student['class_id'], $termSequences, (int) $activeYear['id']);

        $rows = [];
        $weightedSum = 0.0;
        $coeffSum = 0.0;
        $coeffValidSum = 0.0;

        foreach ($subjects as $subject) {
            $sequenceValues = [];
            foreach ($termSequences as $seq) {
                $key = $subject['id'] . '|' . $seq['label'];
                $sequenceValues[] = isset($notesMap[$key]) ? (float) $notesMap[$key]['valeur'] : null;
            }

            $existingValues = array_values(array_filter($sequenceValues, function ($v) {
                return $v !== null;
            }));
            $numSeqs = count($termSequences) ?: 1;
            $termNoteCalc = array_sum($sequenceValues) / $numSeqs;
            $termNote = !empty($existingValues) ? round($termNoteCalc, 2) : null;
            $coefficient = (float) $subject['coefficient'];
            $weighted = round($termNoteCalc * $coefficient, 2);

            // Pour la section anglophone, on n'inclut que les matières avec des notes dans le calcul
            if ($isEnglishSection) {
                if ($termNote !== null) {
                    $weightedSum += $weighted;
                    $coeffSum += $coefficient;
                    $coeffValidSum += $coefficient;
                }
            } else {
                $weightedSum += $weighted;
                $coeffSum += $coefficient;
                if ($termNote !== null) {
                    $coeffValidSum += $coefficient;
                }
            }

            $subjStats = $subjectStats[$subject['id']] ?? null;
            $classAverageSubj = $subjStats['average'] ?? null;
            $studentRankSubj = $subjStats['student_ranks'][$student['id']] ?? null;

            $rows[] = [
                'subject' => $subject['nom'],
                'teacher' => strtoupper((string) ($subject['teacher_name'] ?? '')),
                'group' => $subject['groupe'] ?? __('group_default'),
                'sequence_values' => $sequenceValues,
                'term_note' => $termNote,
                'coefficient' => $coefficient,
                'weighted' => $weighted,
                'class_average_subject' => $classAverageSubj,
                'rank_subject' => $studentRankSubj,
                'appreciation' => $this->getAcquisitionLevel($termNote),
            ];
        }

        $average = $coeffSum > 0 ? round($weightedSum / $coeffSum, 2) : null;
        $ranking = $precomputedRanking ?? $this->computeTrimesterRanking((int) $student['class_id'], $termSequences, (int) $activeYear['id']);
        $rank = $ranking[(int) $student['id']]['rank'] ?? null;
        $classStats = $this->buildClassStats($ranking);

        $seqAverages = [];
        $seqRanks = [];
        foreach ($termSequences as $idx => $seq) {
            $seqRanking = isset($precomputedSeqRankings[$idx]) ? $precomputedSeqRankings[$idx] : null;
            if (!$seqRanking) {
                // Fallback: compute it now
                $seqRanking = $this->computeSequenceRanking((int) $student['class_id'], $seq['label'], (int) $activeYear['id']);
            }

            // Find student in this ranking. Keys are usually student IDs.
            $studentId = (int) $student['id'];
            $foundData = $seqRanking[$studentId] ?? null;

            if ($foundData) {
                $seqAverages[] = $foundData['average'];
                $seqRanks[] = $foundData['rank'];
            } else {
                $seqAverages[] = null;
                $seqRanks[] = null;
            }
        }

        $mention = $this->getMention($average);
        $tableFont = $this->getTableFontSize(count($rows));

        $strengths = array_column(array_filter($rows, function ($row) {
            return $row['term_note'] !== null && $row['term_note'] >= 14;
        }), 'subject');
        $weaknesses = array_column(array_filter($rows, function ($row) {
            return $row['term_note'] !== null && $row['term_note'] < 10;
        }), 'subject');

        $groupedRows = $this->groupRowsBySubjectGroup($rows);
        // La période discipline correspond directement au trimestre (1, 2 ou 3)
        $discipline = $precomputedDiscipline ?? $this->buildDisciplineData($student, [$term], (int) $activeYear['id']);
        $professor_name = $this->getProfessorPrincipalName((int) $student['class_id']);
        $evaluationLabels = array_map(function ($seq) {
            return $this->getShortSequenceLabel((string) $seq['label']);
        }, $termSequences);
        $evaluationLabels[] = __('trimester_short') . ' ' . $term;

        return [
            'bulletinType' => __('bulletin_trimester'),
            'student' => $student,
            'term' => $term,
            'termSequences' => $termSequences,
            'rows' => $rows,
            'groupedRows' => $groupedRows,
            'average' => $average,
            'rank' => $rank,
            'mention' => $mention,
            'classStats' => $classStats,
            'effectif' => count($ranking),
            'tableFont' => $tableFont,
            'activeYear' => $activeYear,
            'total_coefficients' => $coeffSum,
            'total_coef_valide' => $coeffValidSum,
            'councilAppreciation' => $this->getCouncilAppreciation($average),
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
            'institution' => $this->getInstitutionSettings(),
            'evaluationLabels' => $evaluationLabels,
            'discipline' => $discipline,
            'seqAverages' => $seqAverages,
            'seqRanks' => $seqRanks,
            'professor_name' => $professor_name,
            'displayMatricule' => $this->getDisplayMatricule($student),
            'globalAppreciation' => $this->getCouncilAppreciation($average),
        ];
    }

    protected function buildAnnualBulletinData(array $student, ?array $academicYear = null, ?array $precomputedRanking = null, array $precomputedTrimRankings = [], ?array $precomputedSubjectStats = null, ?array $precomputedNotesMap = null, ?array $precomputedDiscipline = null)
    {
        $activeYear = $academicYear ?? $this->getActiveAcademicYear();
        $classId = (int) $student['class_id'];
        $studentId = (int) $student['id'];
        // Pour la section anglophone, on utilise uniquement les matières avec des notes saisies
        $isEnglishSection = $this->isEnglishSection($classId);
        if ($isEnglishSection) {
            $termSequencesByTerm = [
                1 => $this->getActiveSequencesByTerm(1),
                2 => $this->getActiveSequencesByTerm(2),
                3 => $this->getActiveSequencesByTerm(3),
            ];
            $allSeqLabels = [];
            foreach ($termSequencesByTerm as $seqs)
                $allSeqLabels = array_merge($allSeqLabels, array_column($seqs, 'label'));
            $subjects = $this->getSubjectsWithGrades($classId, $allSeqLabels, (int) $activeYear['id']);
        } else {
            $subjects = $this->getClassSubjects($classId);
            $termSequencesByTerm = [
                1 => $this->getActiveSequencesByTerm(1),
                2 => $this->getActiveSequencesByTerm(2),
                3 => $this->getActiveSequencesByTerm(3),
            ];
        }

        $allSeqLabels = [];
        foreach ($termSequencesByTerm as $seqs)
            $allSeqLabels = array_merge($allSeqLabels, array_column($seqs, 'label'));

        $notesMap = $precomputedNotesMap ?? $this->getStudentSequenceNotesMap($studentId, $classId, $allSeqLabels, (int) $activeYear['id']);
        $subjectStats = $precomputedSubjectStats ?? $this->getSubjectStatsForAnnual($classId, $termSequencesByTerm, (int) $activeYear['id']);

        $rows = [];
        $weightedSum = 0.0;
        $coeffSum = 0.0;
        $coeffValidSum = 0.0;

        foreach ($subjects as $subject) {
            $subjectId = (int) $subject['id'];

            $termNotes = [];
            $hasTermValue = false;
            foreach ([1, 2, 3] as $term) {
                $val = $this->computeSubjectAverageFromMap($subjectId, $notesMap, $termSequencesByTerm[$term]);
                $termNotes[$term] = $val;
                if ($val !== null)
                    $hasTermValue = true;
            }

            $numTerms = 3;
            $annualNoteCalc = array_sum(array_map(function ($v) {
                return $v ?? 0;
            }, $termNotes)) / $numTerms;
            $annualNote = ($hasTermValue) ? round($annualNoteCalc, 2) : null;
            $coefficient = (float) $subject['coefficient'];
            $weighted = round($annualNoteCalc * $coefficient, 2);

            // Pour la section anglophone, on n'inclut que les matières avec des notes dans le calcul
            if ($isEnglishSection) {
                if ($annualNote !== null) {
                    $weightedSum += $weighted;
                    $coeffSum += $coefficient;
                    $coeffValidSum += $coefficient;
                }
            } else {
                $weightedSum += $weighted;
                $coeffSum += $coefficient;
                if ($annualNote !== null) {
                    $coeffValidSum += $coefficient;
                }
            }

            $subjStats = $subjectStats[$subjectId] ?? null;
            $classAverageSubj = $subjStats['average'] ?? null;
            $studentRankSubj = $subjStats['student_ranks'][$studentId] ?? null;

            $rows[] = [
                'subject' => $subject['nom'],
                'teacher' => strtoupper((string) ($subject['teacher_name'] ?? '')),
                'group' => $subject['groupe'] ?? __('group_default'),
                'term_values' => [
                    $termNotes[1] ?? null,
                    $termNotes[2] ?? null,
                    $termNotes[3] ?? null,
                ],
                'annual_note' => $annualNote,
                'coefficient' => $coefficient,
                'weighted' => $weighted,
                'class_average_subject' => $classAverageSubj,
                'rank_subject' => $studentRankSubj,
                'appreciation' => $this->getAcquisitionLevel($annualNote),
            ];
        }

        $average = $coeffSum > 0 ? round($weightedSum / $coeffSum, 2) : null;
        $ranking = $precomputedRanking ?? $this->computeAnnualRanking($classId, $termSequencesByTerm, (int) $activeYear['id']);
        $rank = $ranking[$studentId]['rank'] ?? null;
        $classStats = $this->buildClassStats($ranking);

        $termAverages = [];
        $termRanks = [];
        foreach ([1, 2, 3] as $term) {
            $termRanking = $precomputedTrimRankings[$term] ?? $this->computeTrimesterRanking($classId, $termSequencesByTerm[$term], (int) $activeYear['id']);
            $termAverages[] = $termRanking[$studentId]['average'] ?? null;
            $termRanks[] = $termRanking[$studentId]['rank'] ?? null;
        }

        $mention = $this->getMention($average);
        $tableFont = $this->getTableFontSize(count($rows));
        $groupedRows = $this->groupRowsBySubjectGroup($rows);
        // L'annuel agrège les 3 trimestres (périodes 1, 2, 3)
        $discipline = $precomputedDiscipline ?? $this->buildDisciplineData($student, [1, 2, 3], (int) $activeYear['id']);
        $professor_name = $this->getProfessorPrincipalName($classId);

        return [
            'bulletinType' => __('bulletin_annual'),
            'student' => $student,
            'rows' => $rows,
            'groupedRows' => $groupedRows,
            'average' => $average,
            'rank' => $rank,
            'mention' => $mention,
            'classStats' => $classStats,
            'effectif' => count($ranking),
            'tableFont' => $tableFont,
            'activeYear' => $activeYear,
            'total_coefficients' => $coeffSum,
            'total_coef_valide' => $coeffValidSum,
            'institution' => $this->getInstitutionSettings(),
            'discipline' => $discipline,
            'termAverages' => $termAverages,
            'termRanks' => $termRanks,
            'professor_name' => $professor_name,
            'displayMatricule' => $this->getDisplayMatricule($student),
            'globalAppreciation' => $this->getCouncilAppreciation($average),
        ];
    }

    protected function computeSequenceRanking(int $classId, string $sequenceLabel, int $academicYearId, bool $withNames = false)
    {
        // Pour la section anglophone, on utilise uniquement les matières avec des notes saisies
        if ($this->isEnglishSection($classId)) {
            $subjects = $this->getSubjectsWithGrades($classId, [$sequenceLabel], $academicYearId);
        } else {
            $subjects = $this->getClassSubjects($classId);
        }
        $subjectMap = [];
        foreach ($subjects as $subject) {
            $subjectMap[(int) $subject['id']] = (float) $subject['coefficient'];
        }

        $sql = "SELECT st.id AS student_id, st.nom, st.prenom, g.subject_id, g.valeur
                FROM students st
                LEFT JOIN grades g ON g.student_id = st.id
                    AND g.periode = ?
                    AND g.academic_year_id = ?
                WHERE st.class_id = ? AND st.is_withdrawn = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sequenceLabel, $academicYearId, $classId]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $averages = [];
        foreach ($records as $record) {
            $studentId = (int) $record['student_id'];
            if (!isset($averages[$studentId])) {
                $averages[$studentId] = ['weighted' => 0.0, 'coeff' => 0.0];
            }

            if ($record['subject_id'] !== null && $record['valeur'] !== null && isset($subjectMap[(int) $record['subject_id']])) {
                $coeff = $subjectMap[(int) $record['subject_id']];
                $averages[$studentId]['weighted'] += ((float) $record['valeur']) * $coeff;
                $averages[$studentId]['coeff'] += $coeff;
            }
        }

        return $this->finalizeRanking($averages, $records, $withNames);
    }

    protected function computeTrimesterRanking(int $classId, array $termSequences, int $academicYearId, bool $withNames = false)
    {
        // Pour la section anglophone, on utilise uniquement les matières avec des notes saisies
        if ($this->isEnglishSection($classId)) {
            $sequenceLabels = array_column($termSequences, 'label');
            $subjects = $this->getSubjectsWithGrades($classId, $sequenceLabels, $academicYearId);
        } else {
            $subjects = $this->getClassSubjects($classId);
        }
        $subjectMap = [];
        foreach ($subjects as $subject) {
            $subjectMap[(int) $subject['id']] = (float) $subject['coefficient'];
        }

        $students = $this->getStudentsByClass($classId);
        $sequenceLabels = array_column($termSequences, 'label');
        if (empty($sequenceLabels)) {
            return $this->finalizeRanking([], $students, $withNames);
        }

        $placeholders = implode(', ', array_fill(0, count($sequenceLabels), '?'));
        $params = array_merge($sequenceLabels, [$academicYearId, $classId]);
        $sql = "SELECT st.id AS student_id, g.subject_id, g.periode, g.valeur
                FROM students st
                LEFT JOIN grades g ON g.student_id = st.id
                    AND g.periode IN ($placeholders)
                    AND g.academic_year_id = ?
                WHERE st.class_id = ? AND st.is_withdrawn = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $perStudentSubject = [];
        foreach ($records as $record) {
            $studentId = (int) $record['student_id'];
            if ($record['subject_id'] === null || $record['valeur'] === null) {
                continue;
            }
            $subjectId = (int) $record['subject_id'];
            $perStudentSubject[$studentId][$subjectId][] = (float) $record['valeur'];
        }

        $averages = [];
        foreach ($students as $student) {
            $studentId = (int) $student['id'];
            $weightedSum = 0.0;
            $coeffSum = 0.0;

            foreach ($subjectMap as $subjectId => $coefficient) {
                $notes = $perStudentSubject[$studentId][$subjectId] ?? [];
                // Average of sequences in the trimester, missing = 0
                $termNote = 0.0;
                if (!empty($notes) || !empty($termSequences)) {
                    $termNote = array_sum($notes) / count($termSequences);
                }
                $weightedSum += $termNote * $coefficient;
                $coeffSum += $coefficient;
            }
            $averages[$studentId] = ['weighted' => $weightedSum, 'coeff' => $coeffSum];
        }

        return $this->finalizeRanking($averages, $students, $withNames);
    }

    protected function computeAnnualRanking(int $classId, array $termSequencesByTerm, int $academicYearId, bool $withNames = false)
    {
        // Pour la section anglophone, on utilise uniquement les matières avec des notes saisies
        if ($this->isEnglishSection($classId)) {
            $allSequenceLabels = [];
            foreach ([1, 2, 3] as $term) {
                foreach ($termSequencesByTerm[$term] ?? [] as $sequence) {
                    $allSequenceLabels[] = $sequence['label'];
                }
            }
            $subjects = $this->getSubjectsWithGrades($classId, $allSequenceLabels, $academicYearId);
        } else {
            $subjects = $this->getClassSubjects($classId);
        }
        $subjectMap = [];
        foreach ($subjects as $subject) {
            $subjectMap[(int) $subject['id']] = (float) $subject['coefficient'];
        }

        $students = $this->getStudentsByClass($classId);
        $sequenceLabels = [];
        $labelToTerm = [];
        foreach ([1, 2, 3] as $term) {
            foreach ($termSequencesByTerm[$term] ?? [] as $sequence) {
                $sequenceLabels[] = $sequence['label'];
                $labelToTerm[(string) $sequence['label']] = $term;
            }
        }

        $averages = [];
        foreach ($students as $student) {
            $averages[(int) $student['id']] = ['weighted' => 0.0, 'coeff' => 0.0];
        }

        if (empty($sequenceLabels)) {
            return $this->finalizeRanking($averages, $students, $withNames);
        }

        $placeholders = implode(', ', array_fill(0, count($sequenceLabels), '?'));
        $params = array_merge($sequenceLabels, [$academicYearId, $classId]);
        $sql = "SELECT st.id AS student_id, st.nom, st.prenom, g.subject_id, g.periode, g.valeur
                FROM students st
                LEFT JOIN grades g ON g.student_id = st.id
                    AND g.periode IN ($placeholders)
                    AND g.academic_year_id = ?
                WHERE st.class_id = ? AND st.is_withdrawn = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $perStudentSubjectTerm = [];
        foreach ($records as $record) {
            if ($record['subject_id'] === null || $record['valeur'] === null) {
                continue;
            }

            $term = $labelToTerm[(string) $record['periode']] ?? null;
            if ($term === null) {
                continue;
            }

            $studentId = (int) $record['student_id'];
            $subjectId = (int) $record['subject_id'];
            $perStudentSubjectTerm[$studentId][$subjectId][$term][] = (float) $record['valeur'];
        }

        foreach ($students as $student) {
            $studentId = (int) $student['id'];
            foreach ($subjectMap as $subjectId => $coefficient) {
                $termNotes = [];
                foreach ([1, 2, 3] as $term) {
                    $values = $perStudentSubjectTerm[$studentId][$subjectId][$term] ?? [];
                    // Average of sequences in the term, missing = 0
                    $sequencesInTerm = $this->getActiveSequencesByTerm($term);
                    $numSeqs = count($sequencesInTerm) ?: 1;
                    $termNotes[$term] = array_sum($values) / $numSeqs;
                }

                $annualNote = $this->computeAnnualAverageFromTerms($termNotes);
                $averages[$studentId]['weighted'] += $annualNote * $coefficient;
                $averages[$studentId]['coeff'] += $coefficient;
            }
        }

        return $this->finalizeRanking($averages, $records ?: $students, $withNames);
    }

    protected function finalizeRanking(array $averages, array $nameSource = [], bool $withNames = false)
    {
        $result = [];
        $names = [];

        foreach ($nameSource as $row) {
            if (!isset($row['student_id']) && !isset($row['id'])) {
                continue;
            }
            $studentId = (int) ($row['student_id'] ?? $row['id']);
            $names[$studentId] = [
                'nom' => $row['nom'] ?? '',
                'prenom' => $row['prenom'] ?? '',
            ];
        }

        foreach ($averages as $studentId => $values) {
            $average = $values['coeff'] > 0 ? round($values['weighted'] / $values['coeff'], 2) : 0.0;
            $result[$studentId] = [
                'average' => $average,
                'rank' => null,
                'nom' => $names[$studentId]['nom'] ?? '',
                'prenom' => $names[$studentId]['prenom'] ?? '',
            ];
        }

        uasort($result, static function ($a, $b) {
            if ($b['average'] <=> $a['average']) {
                return $b['average'] <=> $a['average'];
            }

            $byLastName = strcasecmp((string) $a['nom'], (string) $b['nom']);
            if ($byLastName !== 0) {
                return $byLastName;
            }

            return strcasecmp((string) $a['prenom'], (string) $b['prenom']);
        });

        $currentRank = 0;
        $lastAverage = null;
        foreach ($result as &$item) {
            if ($lastAverage === null || $item['average'] < $lastAverage) {
                $currentRank++;
            }
            $item['rank'] = $currentRank;
            $lastAverage = $item['average'];
        }
        unset($item);

        return $result;
    }

    protected function buildClassStats(array $ranking)
    {
        if (empty($ranking)) {
            return ['average' => null, 'max' => null, 'min' => null];
        }

        $values = array_column($ranking, 'average');
        $passed = array_filter($values, fn($v) => $v >= 10);
        return [
            'average' => round(array_sum($values) / count($values), 2),
            'max' => max($values),
            'min' => min($values),
            'success_rate' => count($values) > 0 ? round((count($passed) / count($values)) * 100, 2) : 0
        ];
    }

    protected function getStudentsByClass(int $classId)
    {
        if (!$this->canAccessClass($classId)) {
            return [];
        }

        $academicYearId = $this->getActiveAcademicYear()['id'] ?? 0;
        $stmt = $this->db->prepare("SELECT st.*, c.nom AS class_nom FROM students st JOIN classes c ON c.id = st.class_id WHERE st.class_id = ? AND st.academic_year_id = ? AND st.is_withdrawn = 0 ORDER BY st.nom ASC, st.prenom ASC");
        $stmt->execute([$classId, $academicYearId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function getAccessibleStudent(int $studentId)
    {
        $academicYearId = $this->getActiveAcademicYear()['id'] ?? 0;
        $stmt = $this->db->prepare("SELECT st.*, c.nom AS class_nom FROM students st JOIN classes c ON c.id = st.class_id WHERE st.id = ? AND st.academic_year_id = ? AND st.is_withdrawn = 0");
        $stmt->execute([$studentId, $academicYearId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student || !$this->canAccessClass((int) $student['class_id'])) {
            return null;
        }

        return $student;
    }

    protected function getAccessibleClasses()
    {
        // Classes are now shared across years, no year filtering
        if (in_array(Session::get('user_role'), ['superadmin', 'admin'], true)) {
            $stmt = $this->db->query("SELECT id, nom FROM classes ORDER BY nom ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $academicYearId = $this->getActiveAcademicYear()['id'] ?? 0;
        $stmt = $this->db->prepare("SELECT DISTINCT c.id, c.nom
            FROM teacher_assignments ta
            JOIN classes c ON c.id = ta.class_id
            WHERE ta.user_id = ? AND ta.academic_year_id = ?
            ORDER BY c.nom ASC");
        $stmt->execute([(int) Session::get('user_id'), $academicYearId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClassesBySectionJson()
    {
        $sectionId = (int) ($_GET['section_id'] ?? 0);

        // Classes are now shared across years, no year filtering
        $classesQuery = "SELECT id, nom FROM classes";
        $params = [];
        if ($sectionId > 0) {
            $classesQuery .= " AND section_id = ?";
            $params[] = $sectionId;
        }
        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'], true)) {
            $academicYearId = $this->getActiveAcademicYear()['id'] ?? 0;
            $classesQuery .= " AND EXISTS (SELECT 1 FROM teacher_assignments ta WHERE ta.class_id = classes.id AND ta.user_id = ? AND ta.academic_year_id = {$academicYearId})";
            $params[] = (int) Session::get('user_id');
        }
        $classesQuery .= " ORDER BY nom ASC";

        $stmt = $this->db->prepare($classesQuery);
        $stmt->execute($params);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($classes);
        exit;
    }

    protected function getClassInfo(int $classId)
    {
        $stmt = $this->db->prepare("
            SELECT c.id, c.nom, c.section_id, c.cycle_id, u.nom as main_teacher_nom, u.prenom as main_teacher_prenom 
            FROM classes c 
            LEFT JOIN users u ON c.main_teacher_id = u.id 
            WHERE c.id = ?
        ");
        $stmt->execute([$classId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    protected function getHonorRollThreshold(int $classId): float
    {
        $settingsStore = new SettingsStore($this->db);
        $defaultThreshold = (float) $settingsStore->get('honor_roll_default_threshold', '12');

        // Classes are now shared across years, no year filtering
        $stmt = $this->db->prepare("SELECT cycle_id, section_id FROM classes WHERE id = ? LIMIT 1");
        $stmt->execute([$classId]);
        $class = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$class) {
            return $defaultThreshold;
        }

        $classThreshold = trim((string) $settingsStore->get('honor_roll_threshold_class_' . $classId, ''));
        if ($classThreshold !== '') {
            return (float) $classThreshold;
        }

        $sectionId = (int) ($class['section_id'] ?? 0);
        $cycleId = (int) ($class['cycle_id'] ?? 0);

        if ($sectionId > 0) {
            $sectionThreshold = trim((string) $settingsStore->get('honor_roll_threshold_section_' . $sectionId, ''));
            if ($sectionThreshold !== '') {
                return (float) $sectionThreshold;
            }
        }

        if ($cycleId > 0) {
            $cycleThreshold = trim((string) $settingsStore->get('honor_roll_threshold_cycle_' . $cycleId, ''));
            if ($cycleThreshold !== '') {
                return (float) $cycleThreshold;
            }
        }

        return $defaultThreshold;
    }

    /**
     * Détecte la langue du bulletin en fonction de la section de la classe.
     * Si la section est "Anglophone", le bulletin passe en Anglais.
     * Sinon, il repasse en Français par défaut pour garantir la cohérence.
     */
    protected function detectAndApplyBulletinLanguage(int $classId): void
    {
        $stmt = $this->db->prepare("
            SELECT s.nom
            FROM sections s
            JOIN classes c ON c.section_id = s.id
            WHERE c.id = ?
        ");
        $stmt->execute([$classId]);
        $sectionName = (string) $stmt->fetchColumn();

        if (stripos($sectionName, 'Anglophone') !== false || stripos($sectionName, 'English') !== false) {
            \App\Core\Locale::set('en');
        } else {
            \App\Core\Locale::set('fr');
        }
    }

    /**
     * Vérifie si une classe appartient à la section anglophone.
     * Pour la section anglophone, seules les matières composées sont utilisées dans le calcul de la moyenne.
     */
    protected function isEnglishSection(int $classId): bool
    {
        $stmt = $this->db->prepare("
            SELECT s.nom
            FROM sections s
            JOIN classes c ON c.section_id = s.id
            WHERE c.id = ?
        ");
        $stmt->execute([$classId]);
        $sectionName = (string) $stmt->fetchColumn();

        return stripos($sectionName, 'Anglophone') !== false || stripos($sectionName, 'English') !== false;
    }

    protected function canAccessClass(int $classId)
    {
        if (in_array(Session::get('user_role'), ['superadmin', 'admin'], true)) {
            return true;
        }

        $academicYearId = $this->getActiveAcademicYear()['id'] ?? 0;
        $stmt = $this->db->prepare("SELECT 1 FROM teacher_assignments WHERE user_id = ? AND class_id = ? AND academic_year_id = ?");
        $stmt->execute([(int) Session::get('user_id'), $classId, $academicYearId]);
        return (bool) $stmt->fetchColumn();
    }

    protected function getClassSubjects(int $classId)
    {
        // On recupere les matieres et le nom de l'enseignant (le premier trouve)
        $sql = "SELECT s.id, s.nom, s.coefficient, COALESCE(s.groupe, 'Groupe 1') AS groupe,
                                    (SELECT CONCAT(u.nom, ' ', u.prenom)
                                     FROM teacher_assignments ta
                                     JOIN users u ON u.id = ta.user_id
                                     WHERE ta.subject_id = s.id AND ta.class_id = sc.class_id AND u.role = 'enseignant'
                                     LIMIT 1) as teacher_name
            FROM subject_classes sc
            JOIN subjects s ON s.id = sc.subject_id
            WHERE sc.class_id = ? AND s.status = 1
            ORDER BY s.nom ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$classId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les matières qui ont des notes saisies pour une période donnée.
     * Pour la section anglophone, seules ces matières seront utilisées dans le calcul de la moyenne.
     */
    protected function getSubjectsWithGrades(int $classId, array $periodLabels, int $academicYearId)
    {
        $placeholders = implode(', ', array_fill(0, count($periodLabels), '?'));
        $sql = "SELECT DISTINCT s.id, s.nom, s.coefficient, COALESCE(s.groupe, 'Groupe 1') AS groupe,
                                    (SELECT CONCAT(u.nom, ' ', u.prenom)
                                     FROM teacher_assignments ta
                                     JOIN users u ON u.id = ta.user_id
                                     WHERE ta.subject_id = s.id AND ta.class_id = sc.class_id AND u.role = 'enseignant'
                                     LIMIT 1) as teacher_name
            FROM subject_classes sc
            JOIN subjects s ON s.id = sc.subject_id
            JOIN grades g ON g.subject_id = s.id
            WHERE sc.class_id = ? AND s.status = 1 AND g.periode IN ($placeholders) AND g.academic_year_id = ?
            ORDER BY s.nom ASC";

        $params = array_merge([$classId], $periodLabels, [$academicYearId]);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function getStudentSequenceNotesMap(int $studentId, int $classId, array $sequenceLabels, int $academicYearId)
    {
        $classMap = $this->getClassSequenceNotesMap($classId, $sequenceLabels, $academicYearId);
        return $classMap[$studentId] ?? [];
    }

    protected function getClassSequenceNotesMap(int $classId, array $sequenceLabels, int $academicYearId)
    {
        if (empty($sequenceLabels)) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($sequenceLabels), '?'));
        $params = array_merge([$classId, $academicYearId], $sequenceLabels);
        $sql = "SELECT g.student_id, g.subject_id, g.periode, g.valeur, g.appreciation
            FROM grades g
            JOIN students st ON st.id = g.student_id
            JOIN subjects sub ON sub.id = g.subject_id
            WHERE st.class_id = ?
              AND g.academic_year_id = ?
              AND g.periode IN ($placeholders)
              AND sub.status = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $data = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $data[(int) $row['student_id']][$row['subject_id'] . '|' . $row['periode']] = $row;
        }
        return $data;
    }

    protected function getClassDisciplineDataMap(int $classId, array $periods, int $academicYearId)
    {
        if (empty($periods))
            return [];
        $placeholders = implode(',', array_fill(0, count($periods), '?'));
        $sql = "SELECT 
                    student_id,
                    SUM(absences_total) as absences_total,
                    SUM(absences_justified) as absences_justified,
                    SUM(exclusion_days) as exclusion_days,
                    SUM(consignes) as consignes,
                    MAX(conduct) as conduct,
                    MAX(warning_conduct) as warning_conduct,
                    MAX(blame_conduct) as blame_conduct,
                    MAX(warning_work) as warning_work,
                    MAX(tableau_honneur) as tableau_honneur,
                    MAX(encouragements) as encouragements,
                    MAX(felicitations) as felicitations
                FROM discipline
                WHERE academic_year_id = ? AND periode IN ($placeholders)
                AND student_id IN (SELECT id FROM students WHERE class_id = ? AND academic_year_id = ?)
                GROUP BY student_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge([$academicYearId], $periods, [$classId, $academicYearId]));

        $results = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $d) {
            $warn_c = trim((string) ($d['warning_conduct'] ?? ''));
            $blame_c = trim((string) ($d['blame_conduct'] ?? ''));
            
            $results[(int) $d['student_id']] = [
                'absences' => [
                    'total' => (int) ($d['absences_total'] ?? 0),
                    'justified' => (int) ($d['absences_justified'] ?? 0),
                    'unjustified' => (int) ($d['absences_total'] ?? 0) - (int) ($d['absences_justified'] ?? 0)
                ],
                'warning_conduct' => $warn_c !== '' ? $warn_c : '00',
                'conduct' => (string) ($d['conduct'] ?? ''),
                'blame_conduct' => $blame_c !== '' ? $blame_c : '00',
                'exclusion_days' => (int) ($d['exclusion_days'] ?? 0),
                'consignes' => (int) ($d['consignes'] ?? 0),
                'tableau_honneur' => $d['tableau_honneur'] ?: '',
                'encouragements' => $d['encouragements'] ?: '',
                'felicitations' => $d['felicitations'] ?: '',
                'warning_work' => $d['warning_work'] ?: ''
            ];
        }
        return $results;
    }

    protected function getActiveAcademicYear()
    {
        $stmt = $this->db->query("SELECT id, nom FROM academic_years WHERE is_active = 1 LIMIT 1");
        $year = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$year) {
            return ['id' => 0, 'nom' => 'Annee non definie'];
        }
        return $year;
    }

    protected function getAcademicYearById(int $academicYearId)
    {
        $stmt = $this->db->prepare("SELECT id, nom FROM academic_years WHERE id = ?");
        $stmt->execute([$academicYearId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    protected function getActiveSequences()
    {
        $stmt = $this->db->query("SELECT * FROM sequences WHERE is_active = 1 ORDER BY position ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function getActiveSequencesByTerm(int $term)
    {
        $stmt = $this->db->prepare("SELECT * FROM sequences WHERE trimestre = ? AND is_active = 1 ORDER BY position ASC");
        $stmt->execute([$term]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function getSequenceById(int $sequenceId)
    {
        $stmt = $this->db->prepare("SELECT * FROM sequences WHERE id = ?");
        $stmt->execute([$sequenceId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    protected function getMention($average)
    {
        if ($average === null) {
            return '-';
        }
        if ($average >= 18)
            return __('mention_excellent');
        if ($average >= 16)
            return __('mention_very_good');
        if ($average >= 14)
            return __('mention_good');
        if ($average >= 12)
            return __('mention_fairly_good');
        if ($average >= 9.89)
            return __('mention_passable');
        if ($average >= 8.50)
            return __('mention_remedial');
        return __('mention_insufficient');
    }

    protected function getCouncilAppreciation($average)
    {
        if ($average === null) {
            return __('council_incomplete');
        }
        if ($average >= 16)
            return __('council_excellent');
        if ($average >= 14)
            return __('council_good');
        if ($average >= 12)
            return __('council_encouraging');
        if ($average >= 10)
            return __('council_passable');
        if ($average >= 8.5)
            return __('council_fragile');
        return __('council_insufficient');
    }

    protected function getTableFontSize(int $subjectCount)
    {
        if ($subjectCount > 16) {
            return 9;
        }
        if ($subjectCount > 12) {
            return 10;
        }
        return 11;
    }

    protected function ensureSequencesSchema()
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS sequences (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(20) NOT NULL UNIQUE,
            label VARCHAR(100) NOT NULL UNIQUE,
            trimestre TINYINT NOT NULL,
            position TINYINT NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1
        )");

        $stmt = $this->db->prepare("INSERT IGNORE INTO sequences (code, label, trimestre, position, is_active) VALUES (?, ?, ?, ?, 1)");
        foreach (self::DEFAULT_SEQUENCES as $sequence) {
            $stmt->execute([$sequence['code'], $sequence['label'], $sequence['trimestre'], $sequence['position']]);
        }
    }

    protected function ensureDisciplineSchema()
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS discipline (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            academic_year_id INT NOT NULL,
            periode VARCHAR(20) NOT NULL,
            absences_total INT NOT NULL DEFAULT 0,
            absences_justified INT NOT NULL DEFAULT 0,
            exclusion_days INT NOT NULL DEFAULT 0,
            consignes INT NOT NULL DEFAULT 0,
            conduct VARCHAR(120) DEFAULT '',
            warning_conduct VARCHAR(20) DEFAULT '',
            blame_conduct VARCHAR(20) DEFAULT '',
            warning_work VARCHAR(20) DEFAULT '',
            tableau_honneur VARCHAR(20) DEFAULT '',
            encouragements VARCHAR(20) DEFAULT '',
            felicitations VARCHAR(20) DEFAULT '',
            UNIQUE KEY uniq_discipline_student_period (student_id, academic_year_id, periode)
        )");

        try {
            $hasConduct = $this->db->query("SHOW COLUMNS FROM discipline LIKE 'conduct'")->fetch(PDO::FETCH_ASSOC);
            if (!$hasConduct) {
                $this->db->exec("ALTER TABLE discipline ADD COLUMN conduct VARCHAR(120) DEFAULT '' AFTER consignes");
            }
        } catch (\Throwable $e) {
            // Keep backward compatibility if DB engine doesn't support this statement.
        }
    }

    protected function getInstitutionSettings()
    {
        $defaults = [
            'school_republic' => 'Republique du Cameroun',
            'school_republic_en' => 'Republic of Cameroon',
            'school_ministry' => 'Ministere des Enseignements Secondaires',
            'school_ministry_en' => 'Ministry of Secondary Education',
            'school_slogan' => 'Discipline - Travail - Succes',
            'school_slogan_en' => 'Discipline - Work - Success',
            'school_motto' => 'Paix - Travail - Patrie',
            'school_motto_en' => 'Peace - Work - Fatherland',
            'school_logo' => '',
            'school_code' => 'CMR-COL',
            'school_po_box' => '',
            'school_phone' => '',
            'school_fax' => '',
            'school_email' => '',
            'school_website' => '',
        ];

        try {
            $this->db->exec("CREATE TABLE IF NOT EXISTS settings (setting_key VARCHAR(50) PRIMARY KEY, setting_value TEXT)");
            $stmt = $this->db->query("SELECT setting_key, setting_value FROM settings");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $defaults[$row['setting_key']] = $row['setting_value'];
            }
        } catch (\Throwable $e) {
        }

        // Transformation du logo en Base64 pour Dompdf (plus fiable que les URLs/chemins)
        if (!empty($defaults['school_logo'])) {
            $logoPath = __DIR__ . '/../../public/' . ltrim($defaults['school_logo'], '/');
            if (file_exists($logoPath)) {
                $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                $data = file_get_contents($logoPath);
                $defaults['school_logo_base64'] = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        return $defaults;
    }

    protected function resolveAcademicYear(int $academicYearId)
    {
        if ($academicYearId > 0) {
            $year = $this->getAcademicYearById($academicYearId);
            if ($year) {
                return $year;
            }
        }

        return $this->getActiveAcademicYear();
    }

    protected function sortStudentsByRanking(array $students, array $ranking)
    {
        usort($students, static function ($a, $b) use ($ranking) {
            $aData = $ranking[(int) $a['id']] ?? ['average' => 0, 'nom' => $a['nom'], 'prenom' => $a['prenom']];
            $bData = $ranking[(int) $b['id']] ?? ['average' => 0, 'nom' => $b['nom'], 'prenom' => $b['prenom']];

            if ($bData['average'] <=> $aData['average']) {
                return $bData['average'] <=> $aData['average'];
            }

            $byLastName = strcasecmp((string) $a['nom'], (string) $b['nom']);
            if ($byLastName !== 0) {
                return $byLastName;
            }

            return strcasecmp((string) $a['prenom'], (string) $b['prenom']);
        });

        return $students;
    }

    protected function buildPdfFileNameClass(string $period, string $className)
    {
        $classSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $className), '_'));
        $periodSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $period), '_'));
        return 'bulletins_' . $periodSlug . '_' . ($classSlug !== '' ? $classSlug : 'classe');
    }

    protected function buildPdfFileNameStudent(array $student, string $evaluation)
    {
        $studentName = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $student['nom'] . '_' . $student['prenom']), '-'));
        $evaluationSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $evaluation), '-'));
        $classSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $student['class_nom'] ?? 'classe'), '-'));
        return $evaluationSlug . '-' . $studentName . '-' . $classSlug;
    }

    protected function streamPdf(string $html, string $filename)
    {
        // Nettoyage complet des tampons de sortie pour éviter toute donnée parasite dans le PDF
        while (ob_get_level()) {
            ob_end_clean();
        }

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');

        try {
            $dompdf->render();
            $dompdf->stream($filename, ["Attachment" => true]);
        } catch (\Throwable $e) {
            // Fallback si la génération serveur échoue (souvent dû à la mémoire sur de très gros fichiers)
            echo "<div style='font-family:sans-serif; padding:40px; text-align:center;'>";
            echo "<h2 style='color:#d32f2f;'>Le fichier est trop volumineux pour l'exportation directe (" . count(explode('bulletin-sheet', $html)) . " pages)</h2>";
            echo "<p>Pour garantir la qualité du document, veuillez utiliser la fonction d'impression de votre navigateur :</p>";
            echo "<ol style='display:inline-block; text-align:left;'><li>Cliquez sur le bouton bleu <b>'Imprimer'</b></li><li>Dans 'Destination', choisissez <b>'Enregistrer au format PDF'</b></li></ol>";
            echo "<br><br><button onclick='window.history.back()' style='padding:10px 20px; cursor:pointer;'>Retourner à la page précédente</button>";
            echo "</div>";
        }
        exit;
    }

    protected function groupRowsBySubjectGroup(array $rows): array
    {
        $groups = [
            'Groupe 1' => ['label' => __('group_label_literary'), 'rows' => [], 'total_points' => 0.0, 'total_coefficients' => 0.0, 'total_coeffs_all' => 0.0, 'average' => null],
            'Groupe 2' => ['label' => __('group_label_scientific'), 'rows' => [], 'total_points' => 0.0, 'total_coefficients' => 0.0, 'total_coeffs_all' => 0.0, 'average' => null],
            'Groupe 3' => ['label' => __('group_label_complementary'), 'rows' => [], 'total_points' => 0.0, 'total_coefficients' => 0.0, 'total_coeffs_all' => 0.0, 'average' => null],
        ];

        foreach ($rows as $row) {
            $groupName = $row['group'] ?? 'Groupe 1';
            if (!isset($groups[$groupName])) {
                $groups[$groupName] = ['label' => $groupName, 'rows' => [], 'total_points' => 0.0, 'total_coefficients' => 0.0, 'total_coeffs_all' => 0.0, 'average' => null];
            }

            $groups[$groupName]['rows'][] = $row;

            $groups[$groupName]['total_coeffs_all'] += (float) ($row['coefficient'] ?? 0);
            $groups[$groupName]['total_points'] += (float) ($row['weighted'] ?? 0);
            $groups[$groupName]['total_coefficients'] += (float) ($row['coefficient'] ?? 0);
        }

        foreach ($groups as &$group) {
            if ($group['total_coefficients'] > 0) {
                $group['average'] = round($group['total_points'] / $group['total_coefficients'], 2);
            }
        }
        unset($group);

        return array_values(array_filter($groups, static function ($group) {
            return !empty($group['rows']);
        }));
    }

    protected function getShortSequenceLabel(string $label): string
    {
        // Try to find the short_label in the DB for this label
        try {
            $stmt = $this->db->prepare("SELECT short_label FROM sequences WHERE label = ? OR code = ? LIMIT 1");
            $stmt->execute([$label, $label]);
            $short = $stmt->fetchColumn();
            if ($short) {
                return (string) $short;
            }
        } catch (\Throwable $e) {
            // Fallback to regex if DB fails
        }

        if (preg_match('/sequence\s*(\d+)/i', $label, $matches)) {
            return 'Seq ' . $matches[1];
        }
        if (preg_match('/CONTRÔLE\s*-\s*CONTINUE\s*(\d+)/i', $label, $matches)) {
            return 'CC ' . $matches[1];
        }
        return $label;
    }

    protected function getDisplayMatricule($student): string
    {
        $raw = trim((string) ($student['matricule'] ?? $student['email'] ?? '-'));
        $className = trim((string) ($student['class_nom'] ?? ''));

        if ($raw === '' || $raw === '-') {
            return '-';
        }

        if ($className !== '' && stripos($raw, $className) === 0) {
            $trimmed = trim(substr($raw, strlen($className)), " -_/\\");
            if ($trimmed !== '') {
                return $trimmed;
            }
        }

        if (str_contains($raw, '-')) {
            $parts = explode('-', $raw);
            $last = trim((string) end($parts));
            if ($last !== '') {
                return $last;
            }
        }

        return $raw;
    }

    protected function getAcquisitionLevel($note): string
    {
        if ($note === null)
            return '-';
        if ($note < 8)
            return 'CNA';
        if ($note < 10)
            return 'CMA';
        if ($note < 14)
            return 'CA';
        if ($note < 17)
            return 'CBA';
        return 'CTBA';
    }

    protected function getSubjectStatsForSequence(int $classId, string $sequenceLabel, int $academicYearId)
    {
        $sql = "SELECT g.subject_id, g.student_id, g.valeur
                FROM grades g
                JOIN students st ON st.id = g.student_id
                JOIN subjects s ON s.id = g.subject_id
                WHERE st.class_id = ? AND g.periode = ? AND g.academic_year_id = ? AND st.is_withdrawn = 0 AND s.status = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$classId, $sequenceLabel, $academicYearId]);
        $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stats = [];
        foreach ($grades as $g) {
            $subj = (int) $g['subject_id'];
            $stats[$subj]['grades'][] = [
                'student_id' => (int) $g['student_id'],
                'valeur' => (float) $g['valeur']
            ];
        }

        $totalStudents = count($this->getStudentsByClass($classId)) ?: 1;
        foreach ($stats as $subj => &$s) {
            $values = array_column($s['grades'], 'valeur');
            $s['average'] = round(array_sum($values) / $totalStudents, 2);

            usort($s['grades'], fn($a, $b) => $b['valeur'] <=> $a['valeur']);
            $currentRank = 0;
            $lastVal = null;
            foreach ($s['grades'] as &$g) {
                if ($lastVal === null || $g['valeur'] < $lastVal) {
                    $currentRank++;
                }
                $g['rank'] = $currentRank;
                $lastVal = $g['valeur'];
            }
            $s['student_ranks'] = array_column($s['grades'], 'rank', 'student_id');
        }
        return $stats;
    }

    protected function getSubjectStatsForTrimester(int $classId, array $termSequences, int $academicYearId)
    {
        $sequenceLabels = array_column($termSequences, 'label');
        if (empty($sequenceLabels))
            return [];

        $placeholders = implode(', ', array_fill(0, count($sequenceLabels), '?'));
        $sql = "SELECT g.subject_id, g.student_id, g.valeur, g.periode
                FROM grades g
                JOIN students st ON st.id = g.student_id
                JOIN subjects s ON s.id = g.subject_id
                WHERE st.class_id = ? AND g.periode IN ($placeholders) AND g.academic_year_id = ? AND st.is_withdrawn = 0 AND s.status = 1";

        $params = array_merge([$classId], $sequenceLabels, [$academicYearId]);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rawGrades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $perStudentSubject = [];
        foreach ($rawGrades as $rg) {
            $perStudentSubject[(int) $rg['student_id']][(int) $rg['subject_id']][] = (float) $rg['valeur'];
        }

        $stats = [];
        foreach ($perStudentSubject as $studentId => $subjects) {
            foreach ($subjects as $subjectId => $values) {
                $termNote = array_sum($values) / count($values);
                $stats[$subjectId]['grades'][] = [
                    'student_id' => $studentId,
                    'valeur' => $termNote
                ];
            }
        }

        $totalStudents = count($this->getStudentsByClass($classId)) ?: 1;
        foreach ($stats as $subj => &$s) {
            $values = array_column($s['grades'], 'valeur');
            $s['average'] = round(array_sum($values) / $totalStudents, 2);

            usort($s['grades'], fn($a, $b) => $b['valeur'] <=> $a['valeur']);
            $currentRank = 0;
            $lastVal = null;
            foreach ($s['grades'] as &$g) {
                if ($lastVal === null || $g['valeur'] < $lastVal) {
                    $currentRank++;
                }
                $g['rank'] = $currentRank;
                $lastVal = $g['valeur'];
            }
            $s['student_ranks'] = array_column($s['grades'], 'rank', 'student_id');
        }
        return $stats;
    }

    protected function getSubjectStatsForAnnual(int $classId, array $termSequencesByTerm, int $academicYearId)
    {
        $sequenceLabels = [];
        $labelToTerm = [];
        foreach ([1, 2, 3] as $term) {
            foreach ($termSequencesByTerm[$term] ?? [] as $sequence) {
                $sequenceLabels[] = $sequence['label'];
                $labelToTerm[(string) $sequence['label']] = $term;
            }
        }

        if (empty($sequenceLabels)) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($sequenceLabels), '?'));
        $sql = "SELECT g.subject_id, g.student_id, g.valeur, g.periode
                FROM grades g
                JOIN students st ON st.id = g.student_id
                JOIN subjects s ON s.id = g.subject_id
                WHERE st.class_id = ? AND g.periode IN ($placeholders) AND g.academic_year_id = ? AND st.is_withdrawn = 0 AND s.status = 1";

        $params = array_merge([$classId], $sequenceLabels, [$academicYearId]);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rawGrades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $perStudentSubjectTerm = [];
        foreach ($rawGrades as $grade) {
            $term = $labelToTerm[(string) $grade['periode']] ?? null;
            if ($term === null) {
                continue;
            }

            $perStudentSubjectTerm[(int) $grade['student_id']][(int) $grade['subject_id']][$term][] = (float) $grade['valeur'];
        }

        $stats = [];
        foreach ($perStudentSubjectTerm as $studentId => $subjects) {
            foreach ($subjects as $subjectId => $terms) {
                $termNotes = [];
                foreach ([1, 2, 3] as $term) {
                    $values = $terms[$term] ?? [];
                    $termNotes[$term] = !empty($values) ? array_sum($values) / count($values) : null;
                }

                $annualNote = $this->computeAnnualAverageFromTerms($termNotes);
                if ($annualNote === null) {
                    continue;
                }

                $stats[$subjectId]['grades'][] = [
                    'student_id' => $studentId,
                    'valeur' => $annualNote,
                ];
            }
        }

        $totalStudents = count($this->getStudentsByClass($classId)) ?: 1;
        foreach ($stats as &$subjectStats) {
            $values = array_column($subjectStats['grades'], 'valeur');
            $subjectStats['average'] = round(array_sum($values) / $totalStudents, 2);

            usort($subjectStats['grades'], fn($a, $b) => $b['valeur'] <=> $a['valeur']);
            $currentRank = 0;
            $lastVal = null;
            foreach ($subjectStats['grades'] as &$grade) {
                if ($lastVal === null || $grade['valeur'] < $lastVal) {
                    $currentRank++;
                }
                $grade['rank'] = $currentRank;
                $lastVal = $grade['valeur'];
            }
            unset($grade);

            $subjectStats['student_ranks'] = array_column($subjectStats['grades'], 'rank', 'student_id');
        }
        unset($subjectStats);

        return $stats;
    }

    protected function computeSubjectAverageFromMap(int $subjectId, array $notesMap, array $sequences): ?float
    {
        $values = [];
        foreach ($sequences as $sequence) {
            $key = $subjectId . '|' . $sequence['label'];
            if (isset($notesMap[$key]) && $notesMap[$key]['valeur'] !== null) {
                $values[] = (float) $notesMap[$key]['valeur'];
            }
        }

        if (empty($sequences))
            return 0.0;
        return round(array_sum($values) / count($sequences), 2);
    }

    protected function computeAnnualAverageFromTerms(array $termNotes): float
    {
        $sum = 0.0;
        foreach ([1, 2, 3] as $term) {
            $sum += (float) ($termNotes[$term] ?? 0);
        }
        return round($sum / 3, 2);
    }

    protected function buildDisciplineData(array $student, array $periods, int $academicYearId): array
    {
        if (empty($periods)) {
            return [
                'absences' => ['total' => '', 'justified' => '', 'unjustified' => ''],
                'conduct' => '',
                'warning_conduct' => '',
                'blame_conduct' => '',
                'exclusion_days' => '',
                'consignes' => '',
                'tableau_honneur' => '',
                'encouragements' => '',
                'felicitations' => '',
                'warning_work' => ''
            ];
        }

        $placeholders = implode(',', array_fill(0, count($periods), '?'));
        $sql = "SELECT 
                    SUM(absences_total) as absences_total,
                    SUM(absences_justified) as absences_justified,
                    SUM(exclusion_days) as exclusion_days,
                    SUM(consignes) as consignes,
                    MAX(conduct) as conduct,
                    MAX(warning_conduct) as warning_conduct,
                    MAX(blame_conduct) as blame_conduct,
                    MAX(warning_work) as warning_work,
                    MAX(tableau_honneur) as tableau_honneur,
                    MAX(encouragements) as encouragements,
                    MAX(felicitations) as felicitations
                FROM discipline
                WHERE student_id = ? AND academic_year_id = ? AND periode IN ($placeholders)";

        $params = array_merge([(int) $student['id'], $academicYearId], $periods);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        // DEBUG TEMPORAIRE
        error_log("buildDisciplineData: student_id=" . (int) $student['id'] . " year=$academicYearId periods=" . implode(',', $periods) . " result_abs=" . ($d['absences_total'] ?? 'NULL'));

        if (!$d) {
            return [
                'absences' => ['total' => 0, 'justified' => 0, 'unjustified' => 0],
                'conduct' => '',
                'warning_conduct' => '00',
                'blame_conduct' => '00',
                'exclusion_days' => 0,
                'consignes' => 0,
                'tableau_honneur' => '',
                'encouragements' => '',
                'felicitations' => '',
                'warning_work' => ''
            ];
        }

        $abs_total = (int) ($d['absences_total'] ?? 0);
        $abs_just = (int) ($d['absences_justified'] ?? 0);
        $warn_c = trim((string) ($d['warning_conduct'] ?? ''));
        $blame_c = trim((string) ($d['blame_conduct'] ?? ''));

        return [
            'absences' => [
                'total' => $abs_total,
                'justified' => $abs_just,
                'unjustified' => ($abs_total - $abs_just)
            ],
            'conduct' => (string) ($d['conduct'] ?? ''),
            'warning_conduct' => $warn_c !== '' ? $warn_c : '00',
            'blame_conduct' => $blame_c !== '' ? $blame_c : '00',
            'exclusion_days' => (int) ($d['exclusion_days'] ?? 0),
            'consignes' => (int) ($d['consignes'] ?? 0),
            'tableau_honneur' => $d['tableau_honneur'] ? 'X' : '',
            'encouragements' => $d['encouragements'] ? 'X' : '',
            'felicitations' => $d['felicitations'] ? 'X' : '',
            'warning_work' => $d['warning_work'] ? 'X' : ''
        ];
    }

    protected function getClassDisciplineFormMap(int $classId, string $period, int $academicYearId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                student_id, absences_total, absences_justified, exclusion_days, 
                consignes, warning_conduct, blame_conduct, warning_work, 
                tableau_honneur, encouragements, felicitations
            FROM discipline 
            WHERE academic_year_id = ? AND periode = ? 
            AND student_id IN (SELECT id FROM students WHERE class_id = ? AND academic_year_id = ?)
        ");
        $stmt->execute([$academicYearId, $period, $classId, $academicYearId]);

        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $total = (int) ($row['absences_total'] ?? 0);
            $justified = (int) ($row['absences_justified'] ?? 0);
            $map[(int) $row['student_id']] = [
                'absences_total' => $total,
                'absences_justified' => $justified,
                'absences_unjustified' => max(0, $total - $justified),
                'exclusion_days' => (int) ($row['exclusion_days'] ?? 0),
                'consignes' => (int) ($row['consignes'] ?? 0),
                'warning_conduct' => (string) ($row['warning_conduct'] ?? ''),
                'blame_conduct' => (string) ($row['blame_conduct'] ?? ''),
            ];
        }

        return $map;
    }

    protected function getProfessorPrincipalName(int $classId): string
    {
        $cacheKey = "prof_principal_$classId";
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        // 1. Priorité au vrai professeur principal défini par l'admin
        $stmt = $this->db->prepare("
            SELECT CONCAT(u.nom, ' ', u.prenom) 
            FROM classes c 
            JOIN users u ON c.main_teacher_id = u.id 
            WHERE c.id = ?
        ");
        $stmt->execute([$classId]);
        $name = $stmt->fetchColumn();

        if (!$name) {
            // 2. Fallback : l'enseignant qui donne le plus de matières (> 1) dans cette classe
            $stmt = $this->db->prepare("
                SELECT CONCAT(u.nom, ' ', u.prenom) as name, COUNT(ta.subject_id) as subject_count
                FROM teacher_assignments ta
                JOIN users u ON ta.user_id = u.id
                WHERE ta.class_id = ?
                GROUP BY u.id
                HAVING subject_count > 1
                ORDER BY subject_count DESC
                LIMIT 1
            ");
            $stmt->execute([$classId]);
            $name = $stmt->fetchColumn();
        }

        $result = (string) ($name ?: '');
        $this->cache[$cacheKey] = $result;
        return $result;
    }
}
