<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use PDO;

class HonorRollController extends BulletinController
{
    public function index()
    {
        $academicYears = $this->db->query("SELECT id, nom, is_active FROM academic_years ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        if ($academicYearId <= 0) {
            $activeYear = $this->getActiveAcademicYear();
            $academicYearId = (int) ($activeYear['id'] ?? 0);
        }

        $classes = $this->getAccessibleClasses();
        $classId = (int) ($_GET['class_id'] ?? 0);
        $terms = [1, 2, 3];

        include __DIR__ . '/../Views/honors/index.php';
    }

    public function trimestre()
    {
        $classId = (int) ($_GET['class_id'] ?? 0);
        $term = (int) ($_GET['term'] ?? 0);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        $academicYear = $this->resolveAcademicYear($academicYearId);
        
        if (!$this->canAccessClass($classId) || !in_array($term, [1, 2, 3], true)) {
            header("Location: /honors");
            exit;
        }

        $termSequences = $this->getActiveSequencesByTerm($term);
        $ranking = $this->computeTrimesterRanking($classId, $termSequences, (int) $academicYear['id'], true);
        
        // Filter students with average >= 12
        $honors = array_filter($ranking, fn($student) => $student['average'] >= 12);
        
        // Sort by average descending
        uasort($honors, fn($a, $b) => $b['average'] <=> $a['average']);

        $classInfo = $this->getClassInfo($classId);
        $institution = $this->getInstitutionSettings();
        $activeYear = $academicYear;

        include __DIR__ . '/../Views/honors/trimester.php';
    }

    public function trimesterBulk()
    {
        $classId = (int) ($_GET['class_id'] ?? 0);
        $term = (int) ($_GET['term'] ?? 0);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        $academicYear = $this->resolveAcademicYear($academicYearId);
        
        if (!$this->canAccessClass($classId) || !in_array($term, [1, 2, 3], true)) {
            header("Location: /honors");
            exit;
        }

        $termSequences = $this->getActiveSequencesByTerm($term);
        $ranking = $this->computeTrimesterRanking($classId, $termSequences, (int) $academicYear['id'], true);
        
        $honors = array_filter($ranking, fn($student) => $student['average'] >= 12);
        uasort($honors, fn($a, $b) => $b['average'] <=> $a['average']);

        $classInfo = $this->getClassInfo($classId);
        $institution = $this->getInstitutionSettings();
        $activeYear = $academicYear;
        $periodLabel = __('trimester_short_title') . ' ' . $term;

        include __DIR__ . '/../Views/honors/bulk.php';
    }

    public function annuel()
    {
        $classId = (int) ($_GET['class_id'] ?? 0);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        $academicYear = $this->resolveAcademicYear($academicYearId);
        
        if (!$this->canAccessClass($classId)) {
            header("Location: /honors");
            exit;
        }

        $termSequencesByTerm = [
            1 => $this->getActiveSequencesByTerm(1),
            2 => $this->getActiveSequencesByTerm(2),
            3 => $this->getActiveSequencesByTerm(3),
        ];
        $ranking = $this->computeAnnualRanking($classId, $termSequencesByTerm, (int) $academicYear['id'], true);
        
        // Filter students with average >= 12
        $honors = array_filter($ranking, fn($student) => $student['average'] >= 12);
        
        // Sort by average descending
        uasort($honors, fn($a, $b) => $b['average'] <=> $a['average']);

        $classInfo = $this->getClassInfo($classId);
        $institution = $this->getInstitutionSettings();
        $activeYear = $academicYear;

        include __DIR__ . '/../Views/honors/annual.php';
    }

    public function annuelBulk()
    {
        $classId = (int) ($_GET['class_id'] ?? 0);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        $academicYear = $this->resolveAcademicYear($academicYearId);
        
        if (!$this->canAccessClass($classId)) {
            header("Location: /honors");
            exit;
        }

        $termSequencesByTerm = [
            1 => $this->getActiveSequencesByTerm(1),
            2 => $this->getActiveSequencesByTerm(2),
            3 => $this->getActiveSequencesByTerm(3),
        ];
        $ranking = $this->computeAnnualRanking($classId, $termSequencesByTerm, (int) $academicYear['id'], true);
        
        $honors = array_filter($ranking, fn($student) => $student['average'] >= 12);
        uasort($honors, fn($a, $b) => $b['average'] <=> $a['average']);

        $classInfo = $this->getClassInfo($classId);
        $institution = $this->getInstitutionSettings();
        $activeYear = $academicYear;
        $periodLabel = __('annual');

        include __DIR__ . '/../Views/honors/bulk.php';
    }
}
