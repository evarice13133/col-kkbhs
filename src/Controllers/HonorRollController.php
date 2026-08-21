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

        // Récupérer uniquement les types d'enseignement actifs
        $teachingTypes = $this->db->query("SELECT id, code, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        $teachingTypeId = (int) ($_GET['teaching_type_id'] ?? 0);
        $classId = (int) ($_GET['class_id'] ?? 0);

        // Si une classe est sélectionnée sans teaching_type_id spécifié, le retrouver
        if ($classId > 0 && $teachingTypeId <= 0) {
            $stmtTt = $this->db->prepare("SELECT teaching_type_id FROM classes WHERE id = ?");
            $stmtTt->execute([$classId]);
            $teachingTypeId = (int) ($stmtTt->fetchColumn() ?: 0);
        }

        // Pré-sélectionner le premier type actif par défaut si aucun n'est spécifié
        if ($teachingTypeId <= 0 && !empty($teachingTypes)) {
            $teachingTypeId = (int) $teachingTypes[0]['id'];
        }

        // Récupérer les classes actives rattachées au type d'enseignement sélectionné
        $allClasses = $this->getClassesByTeachingType($teachingTypeId, $academicYearId);
        
        // Filtrer pour ne conserver que les classes avec au moins un élève au tableau d'honneur
        $classesWithHonorRoll = [];
        foreach ($allClasses as $class) {
            $threshold = $this->getHonorRollThreshold($class['id']);
            $honorRollStudentCount = 0;
            
            // Vérifier le classement des 3 trimestres
            for ($term = 1; $term <= 3; $term++) {
                $termSequences = $this->getActiveSequencesByTerm($term);
                if (empty($termSequences)) continue;
                
                $ranking = $this->computeTrimesterRanking($class['id'], $termSequences, $academicYearId, false);
                if (!empty($ranking)) {
                    foreach ($ranking as $student) {
                        if (isset($student['average']) && $student['average'] >= $threshold) {
                            $honorRollStudentCount++;
                        }
                    }
                }
            }
            
            // Vérifier le classement annuel
            $termSequencesByTerm = [
                1 => $this->getActiveSequencesByTerm(1),
                2 => $this->getActiveSequencesByTerm(2),
                3 => $this->getActiveSequencesByTerm(3),
            ];
            $ranking = $this->computeAnnualRanking($class['id'], $termSequencesByTerm, $academicYearId, false);
            if (!empty($ranking)) {
                foreach ($ranking as $student) {
                    if (isset($student['average']) && $student['average'] >= $threshold) {
                        $honorRollStudentCount++;
                    }
                }
            }
            
            if ($honorRollStudentCount > 0) {
                $classesWithHonorRoll[] = $class;
            }
        }
        $classes = $classesWithHonorRoll;
        
        // Sécurité & Cohérence : vérifier si la classe sélectionnée appartient aux classes autorisées
        if ($classId > 0) {
            $validClassIds = array_column($allClasses, 'id');
            if (!in_array($classId, array_map('intval', $validClassIds), true)) {
                $classId = 0;
            }
        }
        $terms = [1, 2, 3];

        include __DIR__ . '/../Views/honors/index.php';
    }

    public function trimestre()
    {
        $classId = (int) ($_GET['class_id'] ?? 0);
        $term = (int) ($_GET['term'] ?? 0);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        $reqTeachingTypeId = (int) ($_GET['teaching_type_id'] ?? 0);
        $academicYear = $this->resolveAcademicYear($academicYearId);
        
        if (!$this->canAccessClass($classId, $reqTeachingTypeId) || !in_array($term, [1, 2, 3], true)) {
            header("Location: /honors");
            exit;
        }

        $termSequences = $this->getActiveSequencesByTerm($term);
        $ranking = $this->computeTrimesterRanking($classId, $termSequences, (int) $academicYear['id'], true);
        $threshold = $this->getHonorRollThreshold($classId);

        $honors = array_filter($ranking, fn($student) => $student['average'] >= $threshold);
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
        $reqTeachingTypeId = (int) ($_GET['teaching_type_id'] ?? 0);
        $academicYear = $this->resolveAcademicYear($academicYearId);
        
        if (!$this->canAccessClass($classId, $reqTeachingTypeId) || !in_array($term, [1, 2, 3], true)) {
            header("Location: /honors");
            exit;
        }

        $termSequences = $this->getActiveSequencesByTerm($term);
        $ranking = $this->computeTrimesterRanking($classId, $termSequences, (int) $academicYear['id'], true);
        $threshold = $this->getHonorRollThreshold($classId);

        $honors = array_filter($ranking, fn($student) => $student['average'] >= $threshold);
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
        $reqTeachingTypeId = (int) ($_GET['teaching_type_id'] ?? 0);
        $academicYear = $this->resolveAcademicYear($academicYearId);
        
        if (!$this->canAccessClass($classId, $reqTeachingTypeId)) {
            header("Location: /honors");
            exit;
        }

        $termSequencesByTerm = [
            1 => $this->getActiveSequencesByTerm(1),
            2 => $this->getActiveSequencesByTerm(2),
            3 => $this->getActiveSequencesByTerm(3),
        ];
        $ranking = $this->computeAnnualRanking($classId, $termSequencesByTerm, (int) $academicYear['id'], true);
        $threshold = $this->getHonorRollThreshold($classId);

        $honors = array_filter($ranking, fn($student) => $student['average'] >= $threshold);
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
        $reqTeachingTypeId = (int) ($_GET['teaching_type_id'] ?? 0);
        $academicYear = $this->resolveAcademicYear($academicYearId);
        
        if (!$this->canAccessClass($classId, $reqTeachingTypeId)) {
            header("Location: /honors");
            exit;
        }

        $termSequencesByTerm = [
            1 => $this->getActiveSequencesByTerm(1),
            2 => $this->getActiveSequencesByTerm(2),
            3 => $this->getActiveSequencesByTerm(3),
        ];
        $ranking = $this->computeAnnualRanking($classId, $termSequencesByTerm, (int) $academicYear['id'], true);
        $threshold = $this->getHonorRollThreshold($classId);

        $honors = array_filter($ranking, fn($student) => $student['average'] >= $threshold);
        uasort($honors, fn($a, $b) => $b['average'] <=> $a['average']);

        $classInfo = $this->getClassInfo($classId);
        $institution = $this->getInstitutionSettings();
        $activeYear = $academicYear;
        $periodLabel = __('annual');

        include __DIR__ . '/../Views/honors/bulk.php';
    }
}
