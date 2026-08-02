<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\PermissionManager;
use App\Services\SettingsStore;
use App\Core\LogoManager;
use App\Core\Helpers;
use PDO;

/**
 * Class TranscriptController
 * 
 * Contrôleur HMVC pour la gestion, la génération et l'impression des Relevés de Notes.
 */
class TranscriptController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();

        // Barrage Sécurité & RBAC
        if (!Session::isLogged()) {
            header('Location: /login');
            exit;
        }

        if (!PermissionManager::hasPermission('manage_transcripts') && 
            !PermissionManager::hasPermission('view_transcripts') && 
            !PermissionManager::hasPermission('manage_bulletins') && 
            !PermissionManager::hasRole(['superadmin', 'admin'])) {
            PermissionManager::requirePermission('manage_transcripts');
        }
    }

    /**
     * Page d'accueil & interface de sélection pour les Relevés de Notes.
     */
    public function index()
    {
        $academicYears = $this->db->query("SELECT id, nom, is_active FROM academic_years ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);
        if ($academicYearId <= 0) {
            $activeYear = $this->getActiveAcademicYear();
            $academicYearId = (int) ($activeYear['id'] ?? 0);
        }

        $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $sectionId = (int) ($_GET['section_id'] ?? 0);

        $classesQuery = "SELECT c.id, c.nom FROM classes c WHERE 1=1";
        $classesParams = [];
        if ($sectionId > 0) {
            $classesQuery .= " AND c.section_id = ?";
            $classesParams[] = $sectionId;
        }
        if (!PermissionManager::hasRole(['superadmin', 'admin'])) {
            $classesQuery .= " AND EXISTS (SELECT 1 FROM teacher_assignments ta WHERE ta.class_id = c.id AND ta.user_id = ? AND ta.academic_year_id = ?)";
            $classesParams[] = (int) Session::get('user_id');
            $classesParams[] = $academicYearId;
        }
        $classesQuery .= " ORDER BY c.nom ASC";

        $stmt = $this->db->prepare($classesQuery);
        $stmt->execute($classesParams);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $classId = (int) ($_GET['class_id'] ?? 0);
        $students = $classId > 0 ? $this->getStudentsByClass($classId, $academicYearId) : [];

        include __DIR__ . '/../Views/transcripts/index.php';
    }

    /**
     * Génération / Affichage d'un Relevé de Notes pour un élève ou une classe.
     */
    public function generate()
    {
        $studentId = (int) ($_GET['student_id'] ?? 0);
        $classId = (int) ($_GET['class_id'] ?? 0);
        $academicYearId = (int) ($_GET['academic_year_id'] ?? 0);

        if ($academicYearId <= 0) {
            $activeYear = $this->getActiveAcademicYear();
            $academicYearId = (int) ($activeYear['id'] ?? 0);
        }

        if ($studentId > 0) {
            $studentsData = [$this->getTranscriptDataForStudent($studentId, $academicYearId)];
        } elseif ($classId > 0) {
            $students = $this->getStudentsByClass($classId, $academicYearId);
            $studentsData = [];
            foreach ($students as $st) {
                $studentsData[] = $this->getTranscriptDataForStudent((int) $st['id'], $academicYearId);
            }
        } else {
            header('Location: /transcripts');
            exit;
        }

        // Nettoyer les éléments nuls si un élève n'existe pas
        $studentsData = array_filter($studentsData);

        if (empty($studentsData)) {
            Session::setFlash('error', __('student_not_found') ?? 'Élève non trouvé.');
            header('Location: /transcripts');
            exit;
        }

        // Institution & Branding setup
        $settingsStore = new SettingsStore($this->db);
        $institution = $settingsStore->all();

        $activeYear = $this->getAcademicYearById($academicYearId);

        include __DIR__ . '/../Views/transcripts/document.php';
    }

    /**
     * Récupère l'année académique active.
     */
    protected function getActiveAcademicYear(): array
    {
        $stmt = $this->db->query("SELECT * FROM academic_years WHERE is_active = 1 LIMIT 1");
        $year = $stmt->fetch(PDO::FETCH_ASSOC);
        return $year ?: ['id' => 0, 'nom' => date('Y') . '-' . (date('Y') + 1)];
    }

    /**
     * Récupère une année académique par son ID.
     */
    protected function getAcademicYearById(int $id): array
    {
        $stmt = $this->db->prepare("SELECT * FROM academic_years WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: $this->getActiveAcademicYear();
    }

    /**
     * Récupère la liste des étudiants inscrits dans une classe pour une année donnée.
     */
    protected function getStudentsByClass(int $classId, int $academicYearId): array
    {
        $sql = "
            SELECT s.* 
            FROM students s
            LEFT JOIN enrollments e ON e.student_id = s.id AND e.academic_year_id = ?
            WHERE (s.class_id = ? OR e.class_id = ?)
            AND s.actif = 1 AND s.is_withdrawn = 0
            ORDER BY s.nom ASC, s.prenom ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$academicYearId, $classId, $classId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calcule et prépare toutes les données du relevé de notes pour un élève.
     */
    protected function getTranscriptDataForStudent(int $studentId, int $academicYearId): ?array
    {
        // 1. Récupération des informations de l'élève avec rattachements académiques
        $sql = "
            SELECT 
                s.*,
                c.nom as class_name,
                c.teaching_type_id as class_teaching_type_id,
                l.code as level_code,
                l.libelle_fr as level_libelle_fr,
                l.libelle_en as level_libelle_en,
                cy.nom as cycle_name,
                sec.nom as section_name,
                d.nom as department_name,
                tt.nom as teaching_type_name
            FROM students s
            LEFT JOIN classes c ON c.id = s.class_id
            LEFT JOIN levels l ON l.id = c.level_id
            LEFT JOIN cycles cy ON cy.id = c.cycle_id
            LEFT JOIN sections sec ON sec.id = c.section_id
            LEFT JOIN departments d ON d.id = c.department_id
            LEFT JOIN teaching_types tt ON tt.id = c.teaching_type_id
            WHERE s.id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            return null;
        }

        $classId = (int) ($student['class_id'] ?? 0);
        $teachingTypeId = (int) ($student['class_teaching_type_id'] ?? $student['teaching_type_id'] ?? 0);

        // 2. Formatage des métadonnées requises
        $settingsStore = new SettingsStore($this->db);
        $schoolCode = (string) $settingsStore->get('school_code', 'FUTURA');
        $displayMatricule = !empty($student['matricule']) 
            ? $student['matricule'] 
            : (!empty($student['email']) ? $student['email'] : ($schoolCode . '-' . sprintf('%04d', $student['id'])));

        // 3. Récupération des matières applicables à la classe / type d'enseignement
        $subjectsQuery = "
            SELECT 
                sub.*,
                COALESCE(sg.libelle, sub.groupe, 'UE FONDAMENTALES') as groupe_nom,
                'UE' as groupe_code
            FROM subjects sub
            LEFT JOIN subject_groups sg ON (sg.id = sub.subject_group_id OR sg.libelle = sub.groupe)
            WHERE sub.status = 1
        ";
        $params = [];
        if ($teachingTypeId > 0) {
            $subjectsQuery .= " AND (sub.teaching_type_id = ? OR sub.teaching_type_id IS NULL)";
            $params[] = $teachingTypeId;
        }
        $subjectsQuery .= " ORDER BY COALESCE(sg.libelle, sub.groupe) ASC, sub.nom ASC";

        $stmtSub = $this->db->prepare($subjectsQuery);
        $stmtSub->execute($params);
        $rawSubjects = $stmtSub->fetchAll(PDO::FETCH_ASSOC);

        // 4. Récupération des notes de l'élève pour l'année académique
        $gradesQuery = "
            SELECT 
                g.*,
                seq.trimestre,
                seq.position as seq_position
            FROM grades g
            LEFT JOIN sequences seq ON seq.id = g.sequence_id
            WHERE g.student_id = ?
            AND (g.academic_year_id = ? OR g.academic_year_id IS NULL)
        ";
        $stmtGrades = $this->db->prepare($gradesQuery);
        $stmtGrades->execute([$studentId, $academicYearId]);
        $rawGrades = $stmtGrades->fetchAll(PDO::FETCH_ASSOC);

        // Indexer les notes par subject_id et semestre (Semestre 1: Trimestre 1 ou SEQ1,SEQ2 / Semestre 2: Trimestre 2,3 ou SEQ3-6)
        $gradesBySubjectSem = [];
        foreach ($rawGrades as $g) {
            $subId = (int) $g['subject_id'];
            $val = $g['valeur'];
            if ($val === null || $val === '') continue;

            $trim = (int) ($g['trimestre'] ?? 0);
            $seqPos = (int) ($g['seq_position'] ?? 0);
            $periode = strtolower((string) ($g['periode'] ?? ''));

            // Determination du semestre (1 ou 2)
            if ($trim === 1 || $seqPos === 1 || $seqPos === 2 || strpos($periode, 's1') !== false || strpos($periode, 'semestre 1') !== false || strpos($periode, 't1') !== false) {
                $sem = 1;
            } else {
                $sem = 2;
            }

            $gradesBySubjectSem[$sem][$subId][] = (float) $val;
        }

        // 5. Organisation des semestres (Semestre 1 et Semestre 2)
        $yearName = date('Y');
        $semesters = [
            1 => [
                'title' => __('semestre_1'),
                'session' => 'Fév. ' . $yearName,
                'groups' => []
            ],
            2 => [
                'title' => __('semestre_2'),
                'session' => 'Juin ' . $yearName,
                'groups' => []
            ]
        ];

        // Calculs généraux
        $grandTotalEarnedCredits = 0;
        $grandTotalExpectedCredits = 0;
        $grandWeightedPoints = 0;
        $grandTotalCoeffs = 0;

        foreach ([1, 2] as $sem) {
            $groupedByUE = [];
            $semExpectedCredits = 0;
            $semEarnedCredits = 0;
            $semWeightedPoints = 0;
            $semTotalCoeffs = 0;

            foreach ($rawSubjects as $index => $sub) {
                $subId = (int) $sub['id'];
                $grpName = $sub['groupe_nom'];

                // Si pas de note pour ce semestre, on calcule si l'élève a une note ou attribution
                $notes = $gradesBySubjectSem[$sem][$subId] ?? [];
                $avgNote = !empty($notes) ? (array_sum($notes) / count($notes)) : null;

                // Si pas de note du tout sur le semestre, on simule une évaluation ou affiche 0 / N/A
                $noteFormatted = $avgNote !== null ? round($avgNote, 2) : 0;
                $coeff = (float) ($sub['coefficient'] ?? 1);
                
                // Code UV et Code UE par défaut s'ils sont absents
                $codeUv = !empty($sub['code_uv']) ? $sub['code_uv'] : 'UV' . sprintf('%02d', $subId);
                $codeUe = !empty($sub['code_ue']) ? $sub['code_ue'] : 'UE' . sprintf('%02d', abs(crc32($grpName)) % 90 + 10);

                // Mention
                $mention = $this->calculateMention($noteFormatted);

                // Crédits acquis (Si Note >= 10/20)
                $creditsAcquis = ($noteFormatted >= 10.0) ? $coeff : 0;

                $semExpectedCredits += $coeff;
                $semEarnedCredits += $creditsAcquis;
                $semWeightedPoints += ($noteFormatted * $coeff);
                $semTotalCoeffs += $coeff;

                if (!isset($groupedByUE[$grpName])) {
                    $groupedByUE[$grpName] = [
                        'code_ue' => $codeUe,
                        'libelle' => $grpName,
                        'subjects' => []
                    ];
                }

                $groupedByUE[$grpName]['subjects'][] = [
                    'code_uv' => $codeUv,
                    'nom' => $sub['nom'],
                    'coefficient' => $coeff,
                    'note' => $noteFormatted,
                    'mention' => $mention,
                    'credits_acquis' => $creditsAcquis,
                    'session' => $semesters[$sem]['session']
                ];
            }

            $semAverage = $semTotalCoeffs > 0 ? round($semWeightedPoints / $semTotalCoeffs, 2) : 0;
            $semDecision = ($semAverage >= 10.0 && $semEarnedCredits >= ($semExpectedCredits * 0.5)) ? __('valide') : __('non_valide');

            $semesters[$sem]['groups'] = $groupedByUE;
            $semesters[$sem]['stats'] = [
                'expected_credits' => $semExpectedCredits,
                'earned_credits' => $semEarnedCredits,
                'average' => $semAverage,
                'decision' => $semDecision
            ];

            $grandTotalExpectedCredits += $semExpectedCredits;
            $grandTotalEarnedCredits += $semEarnedCredits;
            $grandWeightedPoints += $semWeightedPoints;
            $grandTotalCoeffs += $semTotalCoeffs;
        }

        $generalAverage = $grandTotalCoeffs > 0 ? round($grandWeightedPoints / $grandTotalCoeffs, 2) : 0;
        $finalDecision = ($generalAverage >= 10.0 && $grandTotalEarnedCredits >= ($grandTotalExpectedCredits * 0.6)) ? __('valide') : __('non_valide');
        $generalMention = $this->calculateMention($generalAverage);

        return [
            'student' => $student,
            'display_matricule' => $displayMatricule,
            'filiere' => $student['department_name'] ?? $student['section_name'] ?? 'Pédagogique Général',
            'niveau' => $student['level_libelle_fr'] ?? $student['level_code'] ?? $student['class_name'],
            'cycle' => $student['cycle_name'] ?? 'Enseignement Supérieur',
            'specialite' => $student['department_name'] ?? $student['class_name'],
            'option' => $student['section_name'] ?? 'Standard',
            'academic_year_name' => $this->getAcademicYearById($academicYearId)['nom'] ?? date('Y'),
            'semesters' => $semesters,
            'summary' => [
                'total_expected_credits' => $grandTotalExpectedCredits,
                'total_earned_credits' => $grandTotalEarnedCredits,
                'general_average' => $generalAverage,
                'final_decision' => $finalDecision,
                'general_mention' => $generalMention
            ]
        ];
    }

    /**
     * Attribue la mention selon la note sur 20.
     */
    protected function calculateMention(float $note): string
    {
        if ($note >= 18.0) return 'Excellent';
        if ($note >= 16.0) return 'Très Bien';
        if ($note >= 14.0) return 'Bien';
        if ($note >= 12.0) return 'Assez Bien';
        if ($note >= 10.0) return 'Passable';
        return 'Ajourné';
    }
}
