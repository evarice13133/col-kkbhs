<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use PDO;

class FicheNotesController extends BulletinController
{
    public function index()
    {
        $academicYears = $this->db->query("SELECT id, nom, is_active FROM academic_years ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        if ($academicYearId <= 0) {
            $activeYear = $this->getActiveAcademicYear();
            $academicYearId = (int) $activeYear['id'];
        }

        $classes = $this->getAccessibleClasses();
        $classId = (int) ($_GET['class_id'] ?? 0);
        $students = $classId > 0 ? $this->getStudentsByClass($classId) : [];
        $studentId = (int) ($_GET['student_id'] ?? 0);
        $sequences = $this->getActiveSequences();
        $terms = [1, 2, 3];

        include __DIR__ . '/../Views/fiches/index.php';
    }

    public function sequence()
    {
        $studentId = (int) ($_GET['student_id'] ?? 0);
        $sequenceId = (int) ($_GET['sequence_id'] ?? 0);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        $student = $this->getAccessibleStudent($studentId);
        $sequence = $this->getSequenceById($sequenceId);

        if (!$student || !$sequence || !(int) $sequence['is_active']) {
            header("Location: /fiches");
            exit;
        }

        $academicYear = $this->resolveAcademicYear($academicYearId);
        $ranking = $this->computeSequenceRanking((int) $student['class_id'], $sequence['label'], (int) $academicYear['id']);
        $data = $this->buildSequenceBulletinData($student, $sequence, $academicYear, $ranking);
        
        $data['documentTitle'] = __('fiche_report_card');
        $pdf_filename = $this->buildPdfFileNameStudent($student, 'Fiche-' . $sequence['label']);
        $data['pdf_filename'] = $pdf_filename;
        extract($data);

        if (($_GET['format'] ?? '') === 'pdf') {
            ob_start();
            $isPdf = true;
            include __DIR__ . '/../Views/fiches/document.php';
            $html = ob_get_clean();
            $this->streamPdf($html, $pdf_filename . '.pdf');
            return;
        }

        include __DIR__ . '/../Views/fiches/document.php';
    }

    public function trimestre()
    {
        $studentId = (int) ($_GET['student_id'] ?? 0);
        $term = (int) ($_GET['term'] ?? 0);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        $student = $this->getAccessibleStudent($studentId);

        if (!$student || !in_array($term, [1, 2, 3], true)) {
            header("Location: /fiches");
            exit;
        }

        $academicYear = $this->resolveAcademicYear($academicYearId);
        $termSequences = $this->getActiveSequencesByTerm($term);
        $ranking = $this->computeTrimesterRanking((int) $student['class_id'], $termSequences, (int) $academicYear['id']);
        $data = $this->buildTrimesterBulletinData($student, $term, $academicYear, $ranking);
        
        $data['documentTitle'] = __('fiche_report_card');
        $pdf_filename = $this->buildPdfFileNameStudent($student, 'Fiche-TRIMESTRE-' . $term);
        $data['pdf_filename'] = $pdf_filename;
        extract($data);

        if (($_GET['format'] ?? '') === 'pdf') {
            ob_start();
            $isPdf = true;
            include __DIR__ . '/../Views/fiches/document.php';
            $html = ob_get_clean();
            $this->streamPdf($html, $pdf_filename . '.pdf');
            return;
        }

        include __DIR__ . '/../Views/fiches/document.php';
    }

    public function annuel()
    {
        $studentId = (int) ($_GET['student_id'] ?? 0);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        $student = $this->getAccessibleStudent($studentId);

        if (!$student) {
            header("Location: /fiches");
            exit;
        }

        $academicYear = $this->resolveAcademicYear($academicYearId);
        $termSequencesByTerm = [
            1 => $this->getActiveSequencesByTerm(1),
            2 => $this->getActiveSequencesByTerm(2),
            3 => $this->getActiveSequencesByTerm(3),
        ];
        $ranking = $this->computeAnnualRanking((int) $student['class_id'], $termSequencesByTerm, (int) $academicYear['id']);
        $data = $this->buildAnnualBulletinData($student, $academicYear, $ranking);
        
        $data['documentTitle'] = __('fiche_report_card');
        $pdf_filename = $this->buildPdfFileNameStudent($student, 'Fiche-ANNUEL');
        $data['pdf_filename'] = $pdf_filename;
        extract($data);

        if (($_GET['format'] ?? '') === 'pdf') {
            ob_start();
            $isPdf = true;
            include __DIR__ . '/../Views/fiches/document.php';
            $html = ob_get_clean();
            $this->streamPdf($html, $pdf_filename . '.pdf');
            return;
        }

        include __DIR__ . '/../Views/fiches/document.php';
    }

    public function sequenceClass()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(600);

        $classId = (int) ($_GET['class_id'] ?? 0);
        $sequenceId = (int) ($_GET['sequence_id'] ?? 0);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        $sequence = $this->getSequenceById($sequenceId);
        $academicYear = $this->resolveAcademicYear($academicYearId);
        $students = $this->getStudentsByClass($classId);

        if (!$this->canAccessClass($classId) || !$sequence || !(int) $sequence['is_active'] || empty($students)) {
            header("Location: /fiches");
            exit;
        }

        $ranking = $this->computeSequenceRanking($classId, $sequence['label'], (int) $academicYear['id'], true);
        $students = $this->sortStudentsByRanking($students, $ranking);
        $subjectStats = $this->getSubjectStatsForSequence($classId, $sequence['label'], (int) $academicYear['id']);

        $bulletins = [];
        foreach ($students as $student) {
            $data = $this->buildSequenceBulletinData($student, $sequence, $academicYear, $ranking, $subjectStats);
            $data['documentTitle'] = __('fiche_report_card');
            $bulletins[] = $data;
        }

        $classInfo = $this->getClassInfo($classId);
        $pdf_filename = $this->buildPdfFileNameClass('Fiche-' . $sequence['label'], $classInfo['nom'] ?? 'classe');
        
        if (($_GET['format'] ?? '') === 'pdf') {
            ob_start();
            $isPdf = true;
            include __DIR__ . '/../Views/fiches/document_class.php';
            $html = ob_get_clean();
            $this->streamPdf($html, $pdf_filename . '.pdf');
            return;
        }

        include __DIR__ . '/../Views/fiches/document_class.php';
    }

    public function trimestreClass()
    {
        $classId = (int) ($_GET['class_id'] ?? 0);
        $term = (int) ($_GET['term'] ?? 0);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        $academicYear = $this->resolveAcademicYear($academicYearId);
        $students = $this->getStudentsByClass($classId);

        if (!$this->canAccessClass($classId) || !in_array($term, [1, 2, 3], true) || empty($students)) {
            header("Location: /fiches");
            exit;
        }

        $termSequences = $this->getActiveSequencesByTerm($term);
        $ranking = $this->computeTrimesterRanking($classId, $termSequences, (int) $academicYear['id'], true);
        $students = $this->sortStudentsByRanking($students, $ranking);
        $subjectStats = $this->getSubjectStatsForTrimester($classId, $termSequences, (int) $academicYear['id']);

        $precomputedSeqRankings = [];
        foreach ($termSequences as $seq) {
            $precomputedSeqRankings[] = $this->computeSequenceRanking($classId, $seq['label'], (int) $academicYear['id']);
        }

        $bulletins = [];
        foreach ($students as $student) {
            $data = $this->buildTrimesterBulletinData($student, $term, $academicYear, $ranking, $precomputedSeqRankings, $subjectStats);
            $data['documentTitle'] = __('fiche_report_card');
            $bulletins[] = $data;
        }

        $classInfo = $this->getClassInfo($classId);
        $pdf_filename = $this->buildPdfFileNameClass('Fiche-TRIMESTRE-' . $term, $classInfo['nom'] ?? 'classe');
        
        if (($_GET['format'] ?? '') === 'pdf') {
            ini_set('memory_limit', '1024M');
            set_time_limit(600);
            ob_start();
            $isPdf = true;
            include __DIR__ . '/../Views/fiches/document_class.php';
            $html = ob_get_clean();
            $this->streamPdf($html, $pdf_filename . '.pdf');
            return;
        }

        include __DIR__ . '/../Views/fiches/document_class.php';
    }

    public function annuelClass()
    {
        $classId = (int) ($_GET['class_id'] ?? 0);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        $academicYear = $this->resolveAcademicYear($academicYearId);
        $students = $this->getStudentsByClass($classId);

        if (!$this->canAccessClass($classId) || empty($students)) {
            header("Location: /fiches");
            exit;
        }

        $termSequencesByTerm = [
            1 => $this->getActiveSequencesByTerm(1),
            2 => $this->getActiveSequencesByTerm(2),
            3 => $this->getActiveSequencesByTerm(3),
        ];
        $ranking = $this->computeAnnualRanking($classId, $termSequencesByTerm, (int) $academicYear['id'], true);
        $students = $this->sortStudentsByRanking($students, $ranking);
        $subjectStats = $this->getSubjectStatsForAnnual($classId, $termSequencesByTerm, (int) $academicYear['id']);

        $precomputedTrimRankings = [];
        foreach ([1, 2, 3] as $term) {
            $precomputedTrimRankings[$term] = $this->computeTrimesterRanking($classId, $termSequencesByTerm[$term], (int) $academicYear['id']);
        }

        $bulletins = [];
        foreach ($students as $student) {
            $data = $this->buildAnnualBulletinData($student, $academicYear, $ranking, $precomputedTrimRankings, $subjectStats);
            $data['documentTitle'] = __('fiche_report_card');
            $bulletins[] = $data;
        }

        $classInfo = $this->getClassInfo($classId);
        $pdf_filename = $this->buildPdfFileNameClass('Fiche-ANNUEL', $classInfo['nom'] ?? 'classe');

        if (($_GET['format'] ?? '') === 'pdf') {
            ini_set('memory_limit', '1024M');
            set_time_limit(600);
            ob_start();
            $isPdf = true;
            include __DIR__ . '/../Views/fiches/document_class.php';
            $html = ob_get_clean();
            $this->streamPdf($html, $pdf_filename . '.pdf');
            return;
        }

        include __DIR__ . '/../Views/fiches/document_class.php';
    }
}
