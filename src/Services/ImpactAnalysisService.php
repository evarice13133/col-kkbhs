<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class ImpactAnalysisService
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    /**
     * Analyse l'impact d'une suppression pour une entité donnée
     */
    public function analyze(string $entityType, int $entityId): array
    {
        $entityType = strtolower(trim($entityType));
        
        switch ($entityType) {
            case 'teacher':
            case 'teachers':
            case 'enseignant':
                return $this->analyzeTeacher($entityId);
                
            case 'student':
            case 'students':
            case 'eleve':
                return $this->analyzeStudent($entityId);

            case 'class':
            case 'classes':
                return $this->analyzeClass($entityId);

            case 'subject':
            case 'subjects':
            case 'matiere':
                return $this->analyzeSubject($entityId);

            case 'subject_group':
            case 'subject_groups':
                return $this->analyzeSubjectGroup($entityId);

            case 'room':
            case 'rooms':
            case 'salle':
                return $this->analyzeRoom($entityId);

            case 'cycle':
            case 'cycles':
                return $this->analyzeCycle($entityId);

            case 'level':
            case 'levels':
            case 'niveau':
                return $this->analyzeLevel($entityId);

            case 'department':
            case 'departments':
                return $this->analyzeDepartment($entityId);

            case 'section':
            case 'sections':
                return $this->analyzeSection($entityId);

            case 'user':
            case 'users':
            case 'utilisateur':
                return $this->analyzeUser($entityId);

            case 'timetable':
            case 'timetables':
                return $this->analyzeTimetable($entityId);

            case 'timetable_slot':
            case 'slot':
                return $this->analyzeTimetableSlot($entityId);

            case 'timetable_week':
            case 'week':
                return $this->analyzeTimetableWeek($entityId);

            case 'academic_year':
            case 'academic_years':
                return $this->analyzeAcademicYear($entityId);

            case 'sequence':
            case 'sequences':
                return $this->analyzeSequence($entityId);

            case 'teaching_type':
            case 'teaching_types':
                return $this->analyzeTeachingType($entityId);

            default:
                return $this->buildGenericAnalysis($entityType, $entityId);
        }
    }

    private function analyzeTeacher(int $id): array
    {
        // Essayer d'abord la table users avec rôle enseignant ou la table teachers
        $stmt = $this->db->prepare("SELECT id, nom, prenom, email, username as matricule FROM users WHERE id = ? AND (role = 'enseignant' OR role = 'teacher')");
        $stmt->execute([$id]);
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$teacher) {
            $stmtHasTeachersTable = $this->db->query("SHOW TABLES LIKE 'teachers'");
            if ($stmtHasTeachersTable->fetch()) {
                $stmtAlt = $this->db->prepare("SELECT id, nom, prenom, matricule, email FROM teachers WHERE id = ?");
                $stmtAlt->execute([$id]);
                $teacher = $stmtAlt->fetch(PDO::FETCH_ASSOC);
            }
        }

        if (!$teacher) {
            return $this->notFoundResponse('teacher', $id);
        }

        $name = trim(($teacher['prenom'] ?? '') . ' ' . ($teacher['nom'] ?? ''));
        if (empty($name)) {
            $name = $teacher['username'] ?? "Enseignant #$id";
        }

        // Classes titulaire
        $stmtClasses = $this->db->prepare("SELECT COUNT(*) FROM classes WHERE main_teacher_id = ?");
        $stmtClasses->execute([$id]);
        $mainClassesCount = (int)$stmtClasses->fetchColumn();

        // Matières enseignées (teacher_assignments ou subject_teacher)
        $assignedSubjectsCount = 0;
        $stmtHasTA = $this->db->query("SHOW TABLES LIKE 'teacher_assignments'");
        if ($stmtHasTA->fetch()) {
            $stmtTA = $this->db->prepare("SELECT COUNT(DISTINCT subject_id) FROM teacher_assignments WHERE user_id = ?");
            $stmtTA->execute([$id]);
            $assignedSubjectsCount += (int)$stmtTA->fetchColumn();
        }
        $stmtHasST = $this->db->query("SHOW TABLES LIKE 'subject_teacher'");
        if ($stmtHasST->fetch()) {
            $stmtST = $this->db->prepare("SELECT COUNT(DISTINCT subject_id) FROM subject_teacher WHERE teacher_id = ?");
            $stmtST->execute([$id]);
            $assignedSubjectsCount += (int)$stmtST->fetchColumn();
        }

        // Entrées emploi du temps
        $stmtEntries = $this->db->prepare("SELECT COUNT(*) FROM timetable_entries WHERE teacher_id = ?");
        $stmtEntries->execute([$id]);
        $timetableEntriesCount = (int)$stmtEntries->fetchColumn();

        // Notes saisies
        $stmtGrades = $this->db->prepare("SELECT COUNT(*) FROM grades WHERE teacher_id = ?");
        $stmtGrades->execute([$id]);
        $gradesCount = (int)$stmtGrades->fetchColumn();

        // Risque & Recommandations
        $risk = 'low';
        $recommendedAction = 'delete';

        if ($gradesCount > 0) {
            $risk = 'critical';
            $recommendedAction = 'deactivate';
        } elseif ($mainClassesCount > 0 || $timetableEntriesCount > 0 || $assignedSubjectsCount > 0) {
            $risk = 'high';
            $recommendedAction = 'transfer';
        }

        // Cibles de transfert (autres enseignants)
        $stmtTargets = $this->db->prepare("SELECT id, CONCAT(IFNULL(prenom,''), ' ', IFNULL(nom,'')) as name FROM users WHERE id != ? AND (role = 'enseignant' OR role = 'teacher') ORDER BY nom, prenom");
        $stmtTargets->execute([$id]);
        $transferTargets = $stmtTargets->fetchAll(PDO::FETCH_ASSOC);

        if (empty($transferTargets)) {
            $stmtAltT = $this->db->prepare("SELECT id, CONCAT(IFNULL(prenom,''), ' ', IFNULL(nom,'')) as name FROM teachers WHERE id != ? ORDER BY nom, prenom");
            $stmtAltT->execute([$id]);
            $transferTargets = $stmtAltT->fetchAll(PDO::FETCH_ASSOC);
        }

        return [
            'entity' => [
                'type' => 'teacher',
                'type_label' => 'Enseignant',
                'id' => $id,
                'name' => $name,
                'subtext' => 'Email: ' . ($teacher['email'] ?: 'N/A')
            ],
            'risk_level' => $risk,
            'recommended_action' => $recommendedAction,
            'can_direct_delete' => ($gradesCount === 0),
            'stats' => [
                ['label' => 'Notes attribuées', 'count' => $gradesCount, 'icon' => 'fas fa-graduation-cap', 'severity' => $gradesCount > 0 ? 'danger' : 'success'],
                ['label' => 'Classes sous tutelle', 'count' => $mainClassesCount, 'icon' => 'fas fa-chalkboard-teacher', 'severity' => $mainClassesCount > 0 ? 'warning' : 'neutral'],
                ['label' => 'Matières affectées', 'count' => $assignedSubjectsCount, 'icon' => 'fas fa-book', 'severity' => $assignedSubjectsCount > 0 ? 'warning' : 'neutral'],
                ['label' => 'Séances d\'emploi du temps', 'count' => $timetableEntriesCount, 'icon' => 'fas fa-calendar-alt', 'severity' => $timetableEntriesCount > 0 ? 'warning' : 'neutral'],
            ],
            'impact_summary' => [
                'direct_deletion' => "Le profil de l'enseignant $name.",
                'dependencies' => "$assignedSubjectsCount affectations de cours, $timetableEntriesCount créneaux de cours.",
                'historical_data' => "$gradesCount notes seront conservées (référence enseignant conservée ou anonymisée).",
                'invalid_references' => $mainClassesCount > 0 ? "$mainClassesCount classes se retrouveront sans professeur principal." : "Aucune."
            ],
            'transfer_options' => [
                'label' => 'Transférer la tutelle, les cours et créneaux vers :',
                'param_name' => 'target_id',
                'items' => $transferTargets
            ]
        ];
    }

    private function analyzeStudent(int $id): array
    {
        $stmt = $this->db->prepare("SELECT id, nom, prenom, matricule FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            return $this->notFoundResponse('student', $id);
        }

        $name = trim(($student['prenom'] ?? '') . ' ' . ($student['nom'] ?? ''));

        // Notes
        $stmtGrades = $this->db->prepare("SELECT COUNT(*) FROM grades WHERE student_id = ?");
        $stmtGrades->execute([$id]);
        $gradesCount = (int)$stmtGrades->fetchColumn();

        // Inscriptions
        $stmtEnrollments = $this->db->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = ?");
        $stmtEnrollments->execute([$id]);
        $enrollmentsCount = (int)$stmtEnrollments->fetchColumn();

        // Paiements
        $stmtPayments = $this->db->prepare("SELECT COUNT(*) FROM student_payments WHERE student_id = ?");
        $stmtPayments->execute([$id]);
        $paymentsCount = (int)$stmtPayments->fetchColumn();

        // Absences
        $stmtAbsences = $this->db->query("SHOW TABLES LIKE 'absences'");
        $absencesCount = 0;
        if ($stmtAbsences->fetch()) {
            $stmtAbs = $this->db->prepare("SELECT COUNT(*) FROM absences WHERE student_id = ?");
            $stmtAbs->execute([$id]);
            $absencesCount = (int)$stmtAbs->fetchColumn();
        }

        $risk = ($gradesCount > 0 || $paymentsCount > 0) ? 'critical' : ($enrollmentsCount > 0 ? 'high' : 'low');
        $recommendedAction = ($gradesCount > 0 || $paymentsCount > 0) ? 'deactivate' : 'delete';

        return [
            'entity' => [
                'type' => 'student',
                'type_label' => 'Élève',
                'id' => $id,
                'name' => $name,
                'subtext' => 'Matricule: ' . ($student['matricule'] ?: 'N/A')
            ],
            'risk_level' => $risk,
            'recommended_action' => $recommendedAction,
            'can_direct_delete' => ($gradesCount === 0 && $paymentsCount === 0),
            'stats' => [
                ['label' => 'Notes & Évaluations', 'count' => $gradesCount, 'icon' => 'fas fa-pen-nib', 'severity' => $gradesCount > 0 ? 'danger' : 'neutral'],
                ['label' => 'Versements / Paiements', 'count' => $paymentsCount, 'icon' => 'fas fa-receipt', 'severity' => $paymentsCount > 0 ? 'danger' : 'neutral'],
                ['label' => 'Inscriptions (Années)', 'count' => $enrollmentsCount, 'icon' => 'fas fa-user-graduate', 'severity' => $enrollmentsCount > 0 ? 'warning' : 'neutral'],
                ['label' => 'Absences enregistrées', 'count' => $absencesCount, 'icon' => 'fas fa-user-clock', 'severity' => 'neutral'],
            ],
            'impact_summary' => [
                'direct_deletion' => "Dossier scolaire complet de $name.",
                'dependencies' => "Historique d'inscription, bulletin et soumissions de devoirs.",
                'historical_data' => "$paymentsCount paiements comptables et $gradesCount notes associées.",
                'invalid_references' => "Les bulletins et relevés imprimés feront référence à un élève archivé."
            ],
            'transfer_options' => null
        ];
    }

    private function analyzeClass(int $id): array
    {
        $stmt = $this->db->prepare("SELECT id, nom FROM classes WHERE id = ?");
        $stmt->execute([$id]);
        $class = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$class) {
            return $this->notFoundResponse('class', $id);
        }

        // Élèves inscrits
        $stmtEnrollments = $this->db->prepare("SELECT COUNT(*) FROM enrollments WHERE class_id = ?");
        $stmtEnrollments->execute([$id]);
        $studentsCount = (int)$stmtEnrollments->fetchColumn();

        // Matières rattachées à la classe
        $subjectsCount = 0;
        $stmtHasST = $this->db->query("SHOW TABLES LIKE 'subject_teacher'");
        if ($stmtHasST->fetch()) {
            $stmtSubjects = $this->db->prepare("SELECT COUNT(*) FROM subject_teacher WHERE class_id = ?");
            $stmtSubjects->execute([$id]);
            $subjectsCount += (int)$stmtSubjects->fetchColumn();
        }
        $stmtHasTA = $this->db->query("SHOW TABLES LIKE 'teacher_assignments'");
        if ($stmtHasTA->fetch()) {
            $stmtTA = $this->db->prepare("SELECT COUNT(*) FROM teacher_assignments WHERE class_id = ?");
            $stmtTA->execute([$id]);
            $subjectsCount += (int)$stmtTA->fetchColumn();
        }

        // Emplois du temps
        $stmtTimetables = $this->db->prepare("SELECT COUNT(*) FROM timetables WHERE class_id = ?");
        $stmtTimetables->execute([$id]);
        $timetablesCount = (int)$stmtTimetables->fetchColumn();

        // Bulletins
        $bulletinsCount = 0;
        $stmtHasBul = $this->db->query("SHOW TABLES LIKE 'bulletins'");
        if ($stmtHasBul->fetch()) {
            $stmtBulletins = $this->db->prepare("SELECT COUNT(*) FROM bulletins WHERE class_id = ?");
            $stmtBulletins->execute([$id]);
            $bulletinsCount = (int)$stmtBulletins->fetchColumn();
        }

        $risk = ($studentsCount > 0 || $bulletinsCount > 0) ? 'critical' : ($subjectsCount > 0 || $timetablesCount > 0 ? 'high' : 'low');
        $recommendedAction = $studentsCount > 0 ? 'transfer' : 'delete';

        // Classes équivalentes pour transfert d'élèves
        $stmtTargets = $this->db->prepare("SELECT id, nom as name FROM classes WHERE id != ? ORDER BY nom");
        $stmtTargets->execute([$id]);
        $transferTargets = $stmtTargets->fetchAll(PDO::FETCH_ASSOC);

        return [
            'entity' => [
                'type' => 'class',
                'type_label' => 'Classe',
                'id' => $id,
                'name' => $class['nom'],
                'subtext' => "Classe ID #$id"
            ],
            'risk_level' => $risk,
            'recommended_action' => $recommendedAction,
            'can_direct_delete' => ($studentsCount === 0 && $bulletinsCount === 0),
            'stats' => [
                ['label' => 'Élèves inscrits', 'count' => $studentsCount, 'icon' => 'fas fa-users', 'severity' => $studentsCount > 0 ? 'danger' : 'success'],
                ['label' => 'Matières & Cours affectés', 'count' => $subjectsCount, 'icon' => 'fas fa-book-open', 'severity' => $subjectsCount > 0 ? 'warning' : 'neutral'],
                ['label' => 'Bulletins générés', 'count' => $bulletinsCount, 'icon' => 'fas fa-file-alt', 'severity' => $bulletinsCount > 0 ? 'danger' : 'neutral'],
                ['label' => 'Emplois du temps', 'count' => $timetablesCount, 'icon' => 'fas fa-calendar-alt', 'severity' => $timetablesCount > 0 ? 'warning' : 'neutral'],
            ],
            'impact_summary' => [
                'direct_deletion' => "La classe " . $class['nom'] . ".",
                'dependencies' => "$subjectsCount cours programmés, $timetablesCount grilles d'emploi du temps.",
                'historical_data' => "$bulletinsCount bulletins officiels et historiques de notes.",
                'invalid_references' => $studentsCount > 0 ? "$studentsCount élèves seront désinscrits et n'auront plus de classe d'affectation." : "Aucune."
            ],
            'transfer_options' => [
                'label' => 'Réaffecter automatiquement les élèves et cours vers la classe :',
                'param_name' => 'target_id',
                'items' => $transferTargets
            ]
        ];
    }

    private function analyzeSubject(int $id): array
    {
        $stmt = $this->db->prepare("SELECT id, nom FROM subjects WHERE id = ?");
        $stmt->execute([$id]);
        $sub = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sub) {
            return $this->notFoundResponse('subject', $id);
        }

        // Affectations enseignants
        $assignedCount = 0;
        $stmtHasST = $this->db->query("SHOW TABLES LIKE 'subject_teacher'");
        if ($stmtHasST->fetch()) {
            $stmtTeacher = $this->db->prepare("SELECT COUNT(*) FROM subject_teacher WHERE subject_id = ?");
            $stmtTeacher->execute([$id]);
            $assignedCount += (int)$stmtTeacher->fetchColumn();
        }
        $stmtHasTA = $this->db->query("SHOW TABLES LIKE 'teacher_assignments'");
        if ($stmtHasTA->fetch()) {
            $stmtTA = $this->db->prepare("SELECT COUNT(*) FROM teacher_assignments WHERE subject_id = ?");
            $stmtTA->execute([$id]);
            $assignedCount += (int)$stmtTA->fetchColumn();
        }

        // Notes
        $stmtGrades = $this->db->prepare("SELECT COUNT(*) FROM grades WHERE subject_id = ?");
        $stmtGrades->execute([$id]);
        $gradesCount = (int)$stmtGrades->fetchColumn();

        // Créneaux d'emploi du temps
        $stmtEntries = $this->db->prepare("SELECT COUNT(*) FROM timetable_entries WHERE subject_id = ?");
        $stmtEntries->execute([$id]);
        $timetableEntries = (int)$stmtEntries->fetchColumn();

        $risk = $gradesCount > 0 ? 'critical' : ($assignedCount > 0 || $timetableEntries > 0 ? 'high' : 'low');
        $recommendedAction = $gradesCount > 0 ? 'deactivate' : ($assignedCount > 0 ? 'transfer' : 'delete');

        // Autres matières pour transfert
        $stmtTargets = $this->db->prepare("SELECT id, nom as name FROM subjects WHERE id != ? ORDER BY nom");
        $stmtTargets->execute([$id]);
        $transferTargets = $stmtTargets->fetchAll(PDO::FETCH_ASSOC);

        return [
            'entity' => [
                'type' => 'subject',
                'type_label' => 'Matière / Cours',
                'id' => $id,
                'name' => $sub['nom'],
                'subtext' => 'Matière ID #' . $id
            ],
            'risk_level' => $risk,
            'recommended_action' => $recommendedAction,
            'can_direct_delete' => ($gradesCount === 0),
            'stats' => [
                ['label' => 'Notes saisies', 'count' => $gradesCount, 'icon' => 'fas fa-star', 'severity' => $gradesCount > 0 ? 'danger' : 'success'],
                ['label' => 'Enseignants affectés', 'count' => $assignedCount, 'icon' => 'fas fa-chalkboard-teacher', 'severity' => $assignedCount > 0 ? 'warning' : 'neutral'],
                ['label' => 'Créneaux d\'emploi du temps', 'count' => $timetableEntries, 'icon' => 'fas fa-clock', 'severity' => $timetableEntries > 0 ? 'warning' : 'neutral'],
            ],
            'impact_summary' => [
                'direct_deletion' => "La matière " . $sub['nom'] . ".",
                'dependencies' => "$assignedCount cours dans le programme d'études, $timetableEntries plages horaires.",
                'historical_data' => "$gradesCount notes de contrôles et examens.",
                'invalid_references' => "Moyennes et bulletins d'anciens semestres."
            ],
            'transfer_options' => [
                'label' => 'Fusionner / Transférer les notes et cours vers :',
                'param_name' => 'target_id',
                'items' => $transferTargets
            ]
        ];
    }

    private function analyzeRoom(int $id): array
    {
        $stmtHasCR = $this->db->query("SHOW TABLES LIKE 'class_rooms'");
        $tableName = ($stmtHasCR && $stmtHasCR->fetch()) ? 'class_rooms' : 'rooms';

        $columns = $this->db->query("DESCRIBE `$tableName`")->fetchAll(PDO::FETCH_COLUMN);
        $hasCode = in_array('code', $columns);
        $hasType = in_array('type_salle', $columns);

        $selectFields = ["id", "nom", "capacite"];
        if ($hasCode) $selectFields[] = "code";
        if ($hasType) $selectFields[] = "type_salle";

        $stmt = $this->db->prepare("SELECT " . implode(', ', $selectFields) . " FROM `$tableName` WHERE id = ?");
        $stmt->execute([$id]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$room) {
            return $this->notFoundResponse('room', $id);
        }

        $stmtEntries = $this->db->prepare("SELECT COUNT(*) FROM timetable_entries WHERE room_id = ?");
        $stmtEntries->execute([$id]);
        $entriesCount = (int)$stmtEntries->fetchColumn();

        $risk = $entriesCount > 0 ? 'medium' : 'low';
        $recommendedAction = $entriesCount > 0 ? 'transfer' : 'delete';

        $stmtTargets = $this->db->prepare("SELECT id, CONCAT(nom, ' (Cap: ', capacite, ')') as name FROM `$tableName` WHERE id != ? ORDER BY nom");
        $stmtTargets->execute([$id]);
        $transferTargets = $stmtTargets->fetchAll(PDO::FETCH_ASSOC);

        $codeText = !empty($room['code']) ? 'Code: ' . $room['code'] . ' | ' : '';
        $typeText = !empty($room['type_salle']) ? ' | Type: ' . $room['type_salle'] : '';

        return [
            'entity' => [
                'type' => 'room',
                'type_label' => 'Salle de classe',
                'id' => $id,
                'name' => $room['nom'],
                'subtext' => $codeText . 'Capacité: ' . ($room['capacite'] ?? 0) . ' places' . $typeText
            ],
            'risk_level' => $risk,
            'recommended_action' => $recommendedAction,
            'can_direct_delete' => true,
            'stats' => [
                ['label' => 'Créneaux horaires occupés', 'count' => $entriesCount, 'icon' => 'fas fa-door-open', 'severity' => $entriesCount > 0 ? 'warning' : 'success'],
            ],
            'impact_summary' => [
                'direct_deletion' => "La salle " . $room['nom'] . ".",
                'dependencies' => "$entriesCount créneaux de l'emploi du temps.",
                'historical_data' => "Aucune altération des notes ni des inscriptions.",
                'invalid_references' => $entriesCount > 0 ? "$entriesCount séances de cours se retrouveront sans salle assignée." : "Aucune."
            ],
            'transfer_options' => [
                'label' => 'Réaffecter tous les créneaux occupés vers la salle :',
                'param_name' => 'target_id',
                'items' => $transferTargets
            ]
        ];
    }

    private function analyzeCycle(int $id): array
    {
        $stmt = $this->db->prepare("SELECT id, nom FROM cycles WHERE id = ?");
        $stmt->execute([$id]);
        $cycle = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cycle) return $this->notFoundResponse('cycle', $id);

        $stmtLevels = $this->db->prepare("SELECT COUNT(*) FROM levels WHERE cycle_id = ?");
        $stmtLevels->execute([$id]);
        $levelsCount = (int)$stmtLevels->fetchColumn();

        $stmtClasses = $this->db->prepare("SELECT COUNT(*) FROM classes WHERE cycle_id = ?");
        $stmtClasses->execute([$id]);
        $classesCount = (int)$stmtClasses->fetchColumn();

        $risk = ($classesCount > 0 || $levelsCount > 0) ? 'critical' : 'low';

        return [
            'entity' => ['type' => 'cycle', 'type_label' => 'Cycle Scolaire', 'id' => $id, 'name' => $cycle['nom']],
            'risk_level' => $risk,
            'recommended_action' => $classesCount > 0 ? 'deactivate' : 'delete',
            'can_direct_delete' => ($classesCount === 0 && $levelsCount === 0),
            'stats' => [
                ['label' => 'Niveaux d\'études', 'count' => $levelsCount, 'icon' => 'fas fa-layer-group', 'severity' => $levelsCount > 0 ? 'danger' : 'neutral'],
                ['label' => 'Classes rattachées', 'count' => $classesCount, 'icon' => 'fas fa-school', 'severity' => $classesCount > 0 ? 'danger' : 'neutral'],
            ],
            'impact_summary' => [
                'direct_deletion' => "Le cycle " . $cycle['nom'] . ".",
                'dependencies' => "$levelsCount niveaux et $classesCount classes associées.",
                'historical_data' => "Structure de scolarité globale de l'établissement.",
                'invalid_references' => "$classesCount classes risquent d'être orphelines de leur cycle."
            ]
        ];
    }

    private function analyzeLevel(int $id): array
    {
        $stmt = $this->db->prepare("SELECT id, nom FROM levels WHERE id = ?");
        $stmt->execute([$id]);
        $level = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$level) return $this->notFoundResponse('level', $id);

        $stmtClasses = $this->db->prepare("SELECT COUNT(*) FROM classes WHERE level_id = ?");
        $stmtClasses->execute([$id]);
        $classesCount = (int)$stmtClasses->fetchColumn();

        $risk = $classesCount > 0 ? 'high' : 'low';

        return [
            'entity' => ['type' => 'level', 'type_label' => 'Niveau d\'étude', 'id' => $id, 'name' => $level['nom']],
            'risk_level' => $risk,
            'recommended_action' => $classesCount > 0 ? 'deactivate' : 'delete',
            'can_direct_delete' => ($classesCount === 0),
            'stats' => [
                ['label' => 'Classes du niveau', 'count' => $classesCount, 'icon' => 'fas fa-graduation-cap', 'severity' => $classesCount > 0 ? 'warning' : 'neutral']
            ],
            'impact_summary' => [
                'direct_deletion' => "Le niveau " . $level['nom'] . ".",
                'dependencies' => "$classesCount classes.",
                'historical_data' => "Paramétrage des frais et des relevés.",
                'invalid_references' => "Aucune si réaffecté."
            ]
        ];
    }

    private function analyzeUser(int $id): array
    {
        $stmt = $this->db->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) return $this->notFoundResponse('user', $id);

        $stmtLogs = $this->db->prepare("SELECT COUNT(*) FROM activity_logs WHERE user_id = ?");
        $stmtLogs->execute([$id]);
        $logsCount = (int)$stmtLogs->fetchColumn();

        $risk = strtolower($user['role']) === 'superadministrateur' ? 'critical' : 'medium';

        return [
            'entity' => ['type' => 'user', 'type_label' => 'Compte Utilisateur', 'id' => $id, 'name' => $user['username'], 'subtext' => 'Rôle: ' . strtoupper($user['role'])],
            'risk_level' => $risk,
            'recommended_action' => 'deactivate',
            'can_direct_delete' => strtolower($user['role']) !== 'superadministrateur',
            'stats' => [
                ['label' => 'Journaux d\'activité', 'count' => $logsCount, 'icon' => 'fas fa-history', 'severity' => 'neutral']
            ],
            'impact_summary' => [
                'direct_deletion' => "Le compte utilisateur " . $user['username'] . ".",
                'dependencies' => "Accès et jetons de connexion.",
                'historical_data' => "$logsCount entrées de journaux d'audit.",
                'invalid_references' => "Les actions passées seront conservées avec la mention 'Utilisateur supprimé'."
            ]
        ];
    }

    private function analyzeTimetable(int $id): array
    {
        $stmt = $this->db->prepare("
            SELECT t.id, t.cycle_id, t.class_id, t.week_id, c.nom as class_name, cy.nom as cycle_name, w.libelle as week_name
            FROM timetables t
            LEFT JOIN classes c ON t.class_id = c.id
            LEFT JOIN cycles cy ON t.cycle_id = cy.id
            LEFT JOIN timetable_weeks w ON t.week_id = w.id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        $tt = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tt) return $this->notFoundResponse('timetable', $id);

        // Si cet emploi du temps fait partie d'un groupe (cycle + week), on compte sur le groupe d'IDs
        $stmtGroup = $this->db->prepare("SELECT id FROM timetables WHERE cycle_id <=> ? AND week_id <=> ?");
        $stmtGroup->execute([$tt['cycle_id'], $tt['week_id']]);
        $groupTimetableIds = $stmtGroup->fetchAll(PDO::FETCH_COLUMN) ?: [$id];
        
        $in = implode(',', array_fill(0, count($groupTimetableIds), '?'));

        // 1. Séances programmées
        $stmtEntries = $this->db->prepare("SELECT COUNT(*) FROM timetable_entries WHERE timetable_id IN ($in)");
        $stmtEntries->execute($groupTimetableIds);
        $entriesCount = (int)$stmtEntries->fetchColumn();

        // 2. Enseignants concernés
        $stmtTeachers = $this->db->prepare("SELECT COUNT(DISTINCT teacher_id) FROM timetable_entries WHERE timetable_id IN ($in) AND teacher_id IS NOT NULL AND teacher_id > 0");
        $stmtTeachers->execute($groupTimetableIds);
        $teachersCount = (int)$stmtTeachers->fetchColumn();

        // 3. Salles concernées
        $stmtRooms = $this->db->prepare("SELECT COUNT(DISTINCT room_id) FROM timetable_entries WHERE timetable_id IN ($in) AND room_id IS NOT NULL AND room_id > 0");
        $stmtRooms->execute($groupTimetableIds);
        $roomsCount = (int)$stmtRooms->fetchColumn();

        // 4. Classes concernées
        $stmtClasses = $this->db->prepare("SELECT COUNT(DISTINCT class_id) FROM timetables WHERE id IN ($in) AND class_id IS NOT NULL AND class_id > 0");
        $stmtClasses->execute($groupTimetableIds);
        $classesCount = (int)$stmtClasses->fetchColumn();

        $name = "Emploi du temps (" . ($tt['cycle_name'] ?: 'Cycle') . " - " . ($tt['week_name'] ?: 'Semaine') . ")";

        return [
            'entity' => ['type' => 'timetable', 'type_label' => 'Emploi du temps', 'id' => $id, 'name' => $name],
            'risk_level' => $entriesCount > 0 ? 'medium' : 'low',
            'recommended_action' => 'delete',
            'can_direct_delete' => true,
            'stats' => [
                ['label' => 'Séances / Programmations', 'count' => $entriesCount, 'icon' => 'fas fa-calendar-day', 'severity' => $entriesCount > 0 ? 'warning' : 'neutral'],
                ['label' => 'Enseignants impactés', 'count' => $teachersCount, 'icon' => 'fas fa-chalkboard-teacher', 'severity' => $teachersCount > 0 ? 'warning' : 'neutral'],
                ['label' => 'Classes concernées', 'count' => $classesCount, 'icon' => 'fas fa-school', 'severity' => $classesCount > 0 ? 'warning' : 'neutral'],
                ['label' => 'Salles occupées', 'count' => $roomsCount, 'icon' => 'fas fa-building', 'severity' => $roomsCount > 0 ? 'warning' : 'neutral'],
            ],
            'impact_summary' => [
                'direct_deletion' => "L'emploi du temps $name.",
                'dependencies' => "$entriesCount programmations de cours, $teachersCount enseignants, $classesCount classes et $roomsCount salles affectées.",
                'historical_data' => "Historique des affectations de créneaux.",
                'invalid_references' => "Aucune (les séances seront retirées des planning enseignants)."
            ]
        ];
    }


    private function analyzeTimetableSlot(int $id): array
    {
        $stmt = $this->db->prepare("SELECT id, heure_debut, heure_fin, label FROM timetable_time_slots WHERE id = ?");
        $stmt->execute([$id]);
        $slot = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$slot) return $this->notFoundResponse('timetable_slot', $id);

        $stmtEntries = $this->db->prepare("SELECT COUNT(*) FROM timetable_entries WHERE slot_id = ?");
        $stmtEntries->execute([$id]);
        $entriesCount = (int)$stmtEntries->fetchColumn();

        $name = ($slot['label'] ?: 'Créneau') . " (" . substr($slot['heure_debut'], 0, 5) . " - " . substr($slot['heure_fin'], 0, 5) . ")";

        return [
            'entity' => ['type' => 'timetable_slot', 'type_label' => 'Créneau Horaire', 'id' => $id, 'name' => $name],
            'risk_level' => $entriesCount > 0 ? 'high' : 'low',
            'recommended_action' => $entriesCount > 0 ? 'deactivate' : 'delete',
            'can_direct_delete' => true,
            'stats' => [
                ['label' => 'Cours dans ce créneau', 'count' => $entriesCount, 'icon' => 'fas fa-clock', 'severity' => $entriesCount > 0 ? 'warning' : 'neutral']
            ],
            'impact_summary' => [
                'direct_deletion' => "Le créneau horaire $name.",
                'dependencies' => "$entriesCount séances associées à cette plage dans les emplois du temps.",
                'historical_data' => "Plages de cours.",
                'invalid_references' => "$entriesCount cours perdront leur créneau horaire."
            ]
        ];
    }

    private function analyzeTimetableWeek(int $id): array
    {
        $stmt = $this->db->prepare("SELECT id, libelle, date_debut, date_fin FROM timetable_weeks WHERE id = ?");
        $stmt->execute([$id]);
        $week = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$week) return $this->notFoundResponse('timetable_week', $id);

        $weekName = !empty($week['libelle']) ? $week['libelle'] : 'Semaine du ' . date('d/m/Y', strtotime($week['date_debut']));

        $stmtTT = $this->db->prepare("SELECT COUNT(*) FROM timetables WHERE week_id = ?");
        $stmtTT->execute([$id]);
        $ttCount = (int)$stmtTT->fetchColumn();

        return [
            'entity' => [
                'type' => 'timetable_week',
                'type_label' => 'Semaine Emploi du Temps',
                'id' => $id,
                'name' => $weekName,
                'subtext' => 'Du ' . date('d/m/Y', strtotime($week['date_debut'])) . ' au ' . date('d/m/Y', strtotime($week['date_fin']))
            ],
            'risk_level' => $ttCount > 0 ? 'high' : 'low',
            'recommended_action' => $ttCount > 0 ? 'deactivate' : 'delete',
            'can_direct_delete' => true,
            'stats' => [
                ['label' => 'Emplois du temps associés', 'count' => $ttCount, 'icon' => 'fas fa-calendar-week', 'severity' => $ttCount > 0 ? 'warning' : 'neutral']
            ],
            'impact_summary' => [
                'direct_deletion' => "La semaine de planning " . $weekName . ".",
                'dependencies' => "$ttCount grilles d'emploi du temps.",
                'historical_data' => "Dates de début et de fin de semaine.",
                'invalid_references' => "Aucune."
            ]
        ];
    }

    private function analyzeSubjectGroup(int $id): array
    {
        $stmt = $this->db->prepare("SELECT id, nom FROM subject_groups WHERE id = ?");
        $stmt->execute([$id]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$group) return $this->notFoundResponse('subject_group', $id);

        $stmtSubs = $this->db->prepare("SELECT COUNT(*) FROM subjects WHERE group_id = ?");
        $stmtSubs->execute([$id]);
        $subsCount = (int)$stmtSubs->fetchColumn();

        return [
            'entity' => ['type' => 'subject_group', 'type_label' => 'Groupe de Matières (UE)', 'id' => $id, 'name' => $group['nom']],
            'risk_level' => $subsCount > 0 ? 'medium' : 'low',
            'recommended_action' => $subsCount > 0 ? 'deactivate' : 'delete',
            'can_direct_delete' => true,
            'stats' => [
                ['label' => 'Matières rattachées', 'count' => $subsCount, 'icon' => 'fas fa-boxes', 'severity' => $subsCount > 0 ? 'warning' : 'neutral']
            ],
            'impact_summary' => [
                'direct_deletion' => "Le groupe de matières " . $group['nom'] . ".",
                'dependencies' => "$subsCount matières rattachées à ce groupe.",
                'historical_data' => "Coefficients d'UE sur les bulletins.",
                'invalid_references' => "Les matières seront dissociées de tout groupe."
            ]
        ];
    }

    private function analyzeDepartment(int $id): array
    {
        $stmt = $this->db->prepare("SELECT id, nom FROM departments WHERE id = ?");
        $stmt->execute([$id]);
        $dept = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dept) return $this->notFoundResponse('department', $id);

        $stmtClasses = $this->db->prepare("SELECT COUNT(*) FROM classes WHERE department_id = ?");
        $stmtClasses->execute([$id]);
        $classesCount = (int)$stmtClasses->fetchColumn();

        return [
            'entity' => ['type' => 'department', 'type_label' => 'Département', 'id' => $id, 'name' => $dept['nom']],
            'risk_level' => $classesCount > 0 ? 'medium' : 'low',
            'recommended_action' => 'delete',
            'can_direct_delete' => true,
            'stats' => [
                ['label' => 'Classes du département', 'count' => $classesCount, 'icon' => 'fas fa-building', 'severity' => $classesCount > 0 ? 'warning' : 'neutral']
            ],
            'impact_summary' => [
                'direct_deletion' => "Le département " . $dept['nom'] . ".",
                'dependencies' => "$classesCount classes.",
                'historical_data' => "Filières et spécialités.",
                'invalid_references' => "Les classes perdront l'association avec ce département."
            ]
        ];
    }

    private function analyzeSection(int $id): array
    {
        $stmt = $this->db->prepare("SELECT id, nom FROM sections WHERE id = ?");
        $stmt->execute([$id]);
        $sec = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sec) return $this->notFoundResponse('section', $id);

        $stmtClasses = $this->db->prepare("SELECT COUNT(*) FROM classes WHERE section_id = ?");
        $stmtClasses->execute([$id]);
        $classesCount = (int)$stmtClasses->fetchColumn();

        return [
            'entity' => ['type' => 'section', 'type_label' => 'Section', 'id' => $id, 'name' => $sec['nom']],
            'risk_level' => $classesCount > 0 ? 'medium' : 'low',
            'recommended_action' => 'delete',
            'can_direct_delete' => true,
            'stats' => [
                ['label' => 'Classes dans la section', 'count' => $classesCount, 'icon' => 'fas fa-flag', 'severity' => $classesCount > 0 ? 'warning' : 'neutral']
            ],
            'impact_summary' => [
                'direct_deletion' => "La section " . $sec['nom'] . ".",
                'dependencies' => "$classesCount classes.",
                'historical_data' => "Section linguistique ou académique.",
                'invalid_references' => "Aucune."
            ]
        ];
    }

    private function analyzeAcademicYear(int $id): array
    {
        $stmt = $this->db->prepare("SELECT id, nom, is_active FROM academic_years WHERE id = ?");
        $stmt->execute([$id]);
        $year = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$year) return $this->notFoundResponse('academic_year', $id);

        $stmtEnrollments = $this->db->prepare("SELECT COUNT(*) FROM enrollments WHERE academic_year_id = ?");
        $stmtEnrollments->execute([$id]);
        $enrollmentsCount = (int)$stmtEnrollments->fetchColumn();

        $stmtGrades = $this->db->prepare("SELECT COUNT(*) FROM grades WHERE academic_year_id = ?");
        $stmtGrades->execute([$id]);
        $gradesCount = (int)$stmtGrades->fetchColumn();

        $risk = ($year['is_active'] || $enrollmentsCount > 0 || $gradesCount > 0) ? 'critical' : 'medium';

        return [
            'entity' => ['type' => 'academic_year', 'type_label' => 'Année Académique', 'id' => $id, 'name' => $year['nom']],
            'risk_level' => $risk,
            'recommended_action' => 'deactivate',
            'can_direct_delete' => ($enrollmentsCount === 0 && $gradesCount === 0 && !$year['is_active']),
            'stats' => [
                ['label' => 'Notes de l\'année', 'count' => $gradesCount, 'icon' => 'fas fa-graduation-cap', 'severity' => $gradesCount > 0 ? 'danger' : 'neutral'],
                ['label' => 'Inscriptions totales', 'count' => $enrollmentsCount, 'icon' => 'fas fa-user-graduate', 'severity' => $enrollmentsCount > 0 ? 'danger' : 'neutral'],
            ],
            'impact_summary' => [
                'direct_deletion' => "L'année académique " . $year['nom'] . ".",
                'dependencies' => "Toutes les inscriptions, devoirs et bulletins de cette année.",
                'historical_data' => "$gradesCount notes et $enrollmentsCount dossiers d'élèves.",
                'invalid_references' => "Pertes irréversibles des données d'évaluation de l'année."
            ]
        ];
    }

    private function analyzeSequence(int $id): array
    {
        $stmt = $this->db->prepare("SELECT id, nom FROM sequences WHERE id = ?");
        $stmt->execute([$id]);
        $seq = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$seq) return $this->notFoundResponse('sequence', $id);

        $stmtGrades = $this->db->prepare("SELECT COUNT(*) FROM grades WHERE sequence_id = ?");
        $stmtGrades->execute([$id]);
        $gradesCount = (int)$stmtGrades->fetchColumn();

        return [
            'entity' => ['type' => 'sequence', 'type_label' => 'Séquence d\'Évaluation', 'id' => $id, 'name' => $seq['nom']],
            'risk_level' => $gradesCount > 0 ? 'critical' : 'low',
            'recommended_action' => $gradesCount > 0 ? 'deactivate' : 'delete',
            'can_direct_delete' => ($gradesCount === 0),
            'stats' => [
                ['label' => 'Notes associées', 'count' => $gradesCount, 'icon' => 'fas fa-star-half-alt', 'severity' => $gradesCount > 0 ? 'danger' : 'neutral']
            ],
            'impact_summary' => [
                'direct_deletion' => "La séquence " . $seq['nom'] . ".",
                'dependencies' => "$gradesCount notes enregistrées.",
                'historical_data' => "Bulletins et calculs des moyennes séquentielles.",
                'invalid_references' => "Altération des moyennes de sous-période."
            ]
        ];
    }

    private function analyzeTeachingType(int $id): array
    {
        $stmt = $this->db->prepare("SELECT id, nom FROM teaching_types WHERE id = ?");
        $stmt->execute([$id]);
        $type = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$type) return $this->notFoundResponse('teaching_type', $id);

        $stmtClasses = $this->db->prepare("SELECT COUNT(*) FROM classes WHERE teaching_type_id = ?");
        $stmtClasses->execute([$id]);
        $classesCount = (int)$stmtClasses->fetchColumn();

        return [
            'entity' => ['type' => 'teaching_type', 'type_label' => 'Type d\'Enseignement', 'id' => $id, 'name' => $type['nom']],
            'risk_level' => $classesCount > 0 ? 'high' : 'low',
            'recommended_action' => 'deactivate',
            'can_direct_delete' => ($classesCount === 0),
            'stats' => [
                ['label' => 'Classes associées', 'count' => $classesCount, 'icon' => 'fas fa-school', 'severity' => $classesCount > 0 ? 'warning' : 'neutral']
            ],
            'impact_summary' => [
                'direct_deletion' => "Le type d'enseignement " . $type['nom'] . ".",
                'dependencies' => "$classesCount classes configurées.",
                'historical_data' => "Logique LMD / Secondaire.",
                'invalid_references' => "Classes sans type d'enseignement assigné."
            ]
        ];
    }

    private function buildGenericAnalysis(string $type, int $id): array
    {
        return [
            'entity' => [
                'type' => $type,
                'type_label' => ucfirst($type),
                'id' => $id,
                'name' => ucfirst($type) . " #" . $id
            ],
            'risk_level' => 'medium',
            'recommended_action' => 'delete',
            'can_direct_delete' => true,
            'stats' => [],
            'impact_summary' => [
                'direct_deletion' => "L'élément de type $type (#$id).",
                'dependencies' => "Aucune dépendance majeure détectée.",
                'historical_data' => "Aucune donnée critique.",
                'invalid_references' => "Aucune."
            ]
        ];
    }

    private function notFoundResponse(string $type, int $id): array
    {
        return [
            'error' => true,
            'message' => "L'élément de type '$type' avec l'identifiant #$id n'existe pas.",
            'entity' => ['type' => $type, 'id' => $id, 'name' => 'Introuvable']
        ];
    }
}
