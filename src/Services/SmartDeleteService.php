<?php

namespace App\Services;

use App\Core\Database;
use App\Services\ActivityTracker;
use PDO;
use Exception;

class SmartDeleteService
{
    private PDO $db;
    private ActivityTracker $tracker;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance()->getConnection();
        $this->tracker = new ActivityTracker($this->db);
    }

    /**
     * Traite une demande de suppression intelligente ou directe
     */
    public function execute(string $entityType, int $entityId, string $scenario, array $options = []): array
    {
        $entityType = strtolower(trim($entityType));
        $scenario = strtolower(trim($scenario)); // 'transfer', 'archive', 'deactivate', 'direct'

        $this->db->beginTransaction();

        try {
            switch ($scenario) {
                case 'transfer':
                    $result = $this->handleTransfer($entityType, $entityId, $options);
                    break;

                case 'archive':
                case 'deactivate':
                    $result = $this->handleDeactivateOrArchive($entityType, $entityId, $scenario);
                    break;

                case 'direct':
                case 'delete':
                default:
                    $result = $this->handleDirectDelete($entityType, $entityId);
                    break;
            }

            $this->db->commit();
            return $result;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return [
                'success' => false,
                'message' => "Erreur lors du traitement de la suppression : " . $e->getMessage()
            ];
        }
    }

    private function handleTransfer(string $type, int $sourceId, array $options): array
    {
        $targetId = (int)($options['target_id'] ?? 0);
        if ($targetId <= 0 || $targetId === $sourceId) {
            throw new Exception("Veuillez sélectionner un élément de destination valide dans la liste déroulante pour effectuer le transfert.");
        }

        $transferredDetails = [];

        switch ($type) {
            case 'teacher':
            case 'teachers':
            case 'enseignant':
                // 1. Classes titulaire
                $stmtMain = $this->db->prepare("UPDATE classes SET main_teacher_id = ? WHERE main_teacher_id = ?");
                $stmtMain->execute([$targetId, $sourceId]);
                $transferredDetails['classes_main'] = $stmtMain->rowCount();

                // 2. Matières affectées (teacher_assignments & subject_teacher)
                $stmtHasTA = $this->db->query("SHOW TABLES LIKE 'teacher_assignments'");
                if ($stmtHasTA->fetch()) {
                    $stmtTA = $this->db->prepare("UPDATE IGNORE teacher_assignments SET user_id = ? WHERE user_id = ?");
                    $stmtTA->execute([$targetId, $sourceId]);
                    $transferredDetails['teacher_assignments'] = $stmtTA->rowCount();
                }
                $stmtHasST = $this->db->query("SHOW TABLES LIKE 'subject_teacher'");
                if ($stmtHasST->fetch()) {
                    $stmtST = $this->db->prepare("UPDATE IGNORE subject_teacher SET teacher_id = ? WHERE teacher_id = ?");
                    $stmtST->execute([$targetId, $sourceId]);
                    $transferredDetails['subjects_assigned'] = $stmtST->rowCount();
                }

                // 3. Emplois du temps
                $stmtTT = $this->db->prepare("UPDATE timetable_entries SET teacher_id = ? WHERE teacher_id = ?");
                $stmtTT->execute([$targetId, $sourceId]);
                $transferredDetails['timetable_entries'] = $stmtTT->rowCount();

                // Suppression physique ou désactivation de l'enseignant source
                $this->db->prepare("DELETE FROM users WHERE id = ? AND (role = 'enseignant' OR role = 'teacher')")->execute([$sourceId]);
                $stmtHasTeachersTable = $this->db->query("SHOW TABLES LIKE 'teachers'");
                if ($stmtHasTeachersTable->fetch()) {
                    $this->db->prepare("DELETE FROM teachers WHERE id = ?")->execute([$sourceId]);
                }
                break;

            case 'class':
            case 'classes':
                // 1. Réaffectation des inscriptions élèves
                $stmtEnr = $this->db->prepare("UPDATE enrollments SET class_id = ? WHERE class_id = ?");
                $stmtEnr->execute([$targetId, $sourceId]);
                $transferredDetails['students_transferred'] = $stmtEnr->rowCount();

                // 2. Emplois du temps
                $stmtTT = $this->db->prepare("UPDATE timetables SET class_id = ? WHERE class_id = ?");
                $stmtTT->execute([$targetId, $sourceId]);
                $transferredDetails['timetables'] = $stmtTT->rowCount();

                // Suppression de la classe
                $this->db->prepare("DELETE FROM classes WHERE id = ?")->execute([$sourceId]);
                break;

            case 'subject':
            case 'subjects':
            case 'matiere':
                // 1. Transfert des affectations
                $stmtHasST = $this->db->query("SHOW TABLES LIKE 'subject_teacher'");
                if ($stmtHasST->fetch()) {
                    $stmtST = $this->db->prepare("UPDATE IGNORE subject_teacher SET subject_id = ? WHERE subject_id = ?");
                    $stmtST->execute([$targetId, $sourceId]);
                    $transferredDetails['teacher_assignments'] = $stmtST->rowCount();
                }
                $stmtHasTA = $this->db->query("SHOW TABLES LIKE 'teacher_assignments'");
                if ($stmtHasTA->fetch()) {
                    $stmtTA = $this->db->prepare("UPDATE IGNORE teacher_assignments SET subject_id = ? WHERE subject_id = ?");
                    $stmtTA->execute([$targetId, $sourceId]);
                    $transferredDetails['teacher_assignments'] = ($transferredDetails['teacher_assignments'] ?? 0) + $stmtTA->rowCount();
                }
                $stmtHasSC = $this->db->query("SHOW TABLES LIKE 'subject_classes'");
                if ($stmtHasSC->fetch()) {
                    $stmtSC = $this->db->prepare("UPDATE IGNORE subject_classes SET subject_id = ? WHERE subject_id = ?");
                    $stmtSC->execute([$targetId, $sourceId]);
                }

                // 2. Transfert des créneaux
                $stmtHasTT = $this->db->query("SHOW TABLES LIKE 'timetable_entries'");
                if ($stmtHasTT->fetch()) {
                    $stmtTT = $this->db->prepare("UPDATE timetable_entries SET subject_id = ? WHERE subject_id = ?");
                    $stmtTT->execute([$targetId, $sourceId]);
                    $transferredDetails['timetable_entries'] = $stmtTT->rowCount();
                }

                // 3. Transfert/Fusion des notes
                $stmtGrades = $this->db->prepare("UPDATE grades SET subject_id = ? WHERE subject_id = ?");
                $stmtGrades->execute([$targetId, $sourceId]);
                $transferredDetails['grades'] = $stmtGrades->rowCount();

                $this->db->prepare("DELETE FROM subjects WHERE id = ?")->execute([$sourceId]);
                break;

            case 'room':
            case 'rooms':
            case 'salle':
                // Transfert des séances
                $stmtHasTT = $this->db->query("SHOW TABLES LIKE 'timetable_entries'");
                if ($stmtHasTT->fetch()) {
                    $stmtTT = $this->db->prepare("UPDATE timetable_entries SET room_id = ? WHERE room_id = ?");
                    $stmtTT->execute([$targetId, $sourceId]);
                    $transferredDetails['timetable_entries'] = $stmtTT->rowCount();
                }

                $stmtHasCR = $this->db->query("SHOW TABLES LIKE 'class_rooms'");
                if ($stmtHasCR->fetch()) {
                    $this->db->prepare("DELETE FROM class_rooms WHERE id = ?")->execute([$sourceId]);
                }
                $stmtHasR = $this->db->query("SHOW TABLES LIKE 'rooms'");
                if ($stmtHasR->fetch()) {
                    $this->db->prepare("DELETE FROM rooms WHERE id = ?")->execute([$sourceId]);
                }
                break;

            default:
                throw new Exception("Le transfert n'est pas configuré pour ce type d'élément ('$type').");
        }

        $this->tracker->recordEvent('smart_delete_transfer', 'system', [
            'entity_type' => $type,
            'source_id' => $sourceId,
            'target_id' => $targetId,
            'details' => $transferredDetails
        ]);

        return [
            'success' => true,
            'message' => "Transfert réussi des dépendances vers la cible #$targetId et suppression de la source.",
            'transferred_details' => $transferredDetails
        ];
    }

    private function handleDeactivateOrArchive(string $type, int $id, string $action): array
    {
        $tableMap = [
            'teacher' => 'users', 'teachers' => 'users', 'enseignant' => 'users',
            'student' => 'students', 'students' => 'students', 'eleve' => 'students',
            'user' => 'users', 'users' => 'users',
            'academic_year' => 'academic_years', 'academic_years' => 'academic_years',
            'class' => 'classes', 'classes' => 'classes',
            'subject' => 'subjects', 'subjects' => 'subjects'
        ];

        $table = $tableMap[$type] ?? null;

        if (!$table) {
            throw new Exception("L'archivage/désactivation n'est pas disponible pour l'entité '$type'.");
        }

        // Vérifier les colonnes existantes (status, is_active, active)
        $columns = $this->db->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_COLUMN);

        if (in_array('status', $columns)) {
            $statusVal = ($action === 'archive') ? 'archived' : 'inactive';
            $stmt = $this->db->prepare("UPDATE `$table` SET status = ? WHERE id = ?");
            $stmt->execute([$statusVal, $id]);
        } elseif (in_array('is_active', $columns)) {
            $stmt = $this->db->prepare("UPDATE `$table` SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);
        } elseif (in_array('active', $columns)) {
            $stmt = $this->db->prepare("UPDATE `$table` SET active = 0 WHERE id = ?");
            $stmt->execute([$id]);
        } else {
            throw new Exception("L'entité '$type' ne possède pas de champ de statut/d'état modifiable.");
        }

        $this->tracker->recordEvent('entity_' . $action, 'system', [
            'entity_type' => $type,
            'entity_id' => $id,
            'action' => $action
        ]);

        return [
            'success' => true,
            'message' => "L'élément de type '$type' (#$id) a été " . ($action === 'archive' ? 'archivé' : 'désactivé') . " avec succès."
        ];
    }

    private function handleDirectDelete(string $type, int $id): array
    {
        switch ($type) {
            case 'teacher':
            case 'teachers':
            case 'enseignant':
                // Nettoyage soft des clés étrangères avant delete si nécessaire
                $this->db->prepare("UPDATE classes SET main_teacher_id = NULL WHERE main_teacher_id = ?")->execute([$id]);
                $stmtHasTA = $this->db->query("SHOW TABLES LIKE 'teacher_assignments'");
                if ($stmtHasTA->fetch()) {
                    $this->db->prepare("DELETE FROM teacher_assignments WHERE user_id = ?")->execute([$id]);
                }
                $stmtHasST = $this->db->query("SHOW TABLES LIKE 'subject_teacher'");
                if ($stmtHasST->fetch()) {
                    $this->db->prepare("DELETE FROM subject_teacher WHERE teacher_id = ?")->execute([$id]);
                }
                $this->db->prepare("DELETE FROM timetable_entries WHERE teacher_id = ?")->execute([$id]);
                $stmt = $this->db->prepare("DELETE FROM users WHERE id = ? AND (role = 'enseignant' OR role = 'teacher')");
                $stmt->execute([$id]);
                $stmtHasTeachersTable = $this->db->query("SHOW TABLES LIKE 'teachers'");
                if ($stmtHasTeachersTable->fetch()) {
                    $this->db->prepare("DELETE FROM teachers WHERE id = ?")->execute([$id]);
                }
                break;

            case 'student':
            case 'students':
            case 'eleve':
                $this->db->prepare("DELETE FROM enrollments WHERE student_id = ?")->execute([$id]);
                $stmt = $this->db->prepare("DELETE FROM students WHERE id = ?");
                $stmt->execute([$id]);
                break;

            case 'class':
            case 'classes':
                $stmtHasST = $this->db->query("SHOW TABLES LIKE 'subject_teacher'");
                if ($stmtHasST->fetch()) {
                    $this->db->prepare("DELETE FROM subject_teacher WHERE class_id = ?")->execute([$id]);
                }
                $stmtHasTA = $this->db->query("SHOW TABLES LIKE 'teacher_assignments'");
                if ($stmtHasTA->fetch()) {
                    $this->db->prepare("DELETE FROM teacher_assignments WHERE class_id = ?")->execute([$id]);
                }
                $stmtHasSC = $this->db->query("SHOW TABLES LIKE 'subject_classes'");
                if ($stmtHasSC->fetch()) {
                    $this->db->prepare("DELETE FROM subject_classes WHERE class_id = ?")->execute([$id]);
                }
                $stmtHasTT = $this->db->query("SHOW TABLES LIKE 'timetables'");
                if ($stmtHasTT->fetch()) {
                    $this->db->prepare("DELETE FROM timetables WHERE class_id = ?")->execute([$id]);
                }
                $stmt = $this->db->prepare("DELETE FROM classes WHERE id = ?");
                $stmt->execute([$id]);
                break;

            case 'subject':
            case 'subjects':
            case 'matiere':
                $stmtHasST = $this->db->query("SHOW TABLES LIKE 'subject_teacher'");
                if ($stmtHasST->fetch()) {
                    $this->db->prepare("DELETE FROM subject_teacher WHERE subject_id = ?")->execute([$id]);
                }
                $stmtHasTA = $this->db->query("SHOW TABLES LIKE 'teacher_assignments'");
                if ($stmtHasTA->fetch()) {
                    $this->db->prepare("DELETE FROM teacher_assignments WHERE subject_id = ?")->execute([$id]);
                }
                $stmtHasSC = $this->db->query("SHOW TABLES LIKE 'subject_classes'");
                if ($stmtHasSC->fetch()) {
                    $this->db->prepare("DELETE FROM subject_classes WHERE subject_id = ?")->execute([$id]);
                }
                $stmtHasTT = $this->db->query("SHOW TABLES LIKE 'timetable_entries'");
                if ($stmtHasTT->fetch()) {
                    $this->db->prepare("DELETE FROM timetable_entries WHERE subject_id = ?")->execute([$id]);
                }
                $stmt = $this->db->prepare("DELETE FROM subjects WHERE id = ?");
                $stmt->execute([$id]);
                break;

            case 'room':
            case 'rooms':
            case 'salle':
                $stmtHasTT = $this->db->query("SHOW TABLES LIKE 'timetable_entries'");
                if ($stmtHasTT->fetch()) {
                    $this->db->prepare("UPDATE timetable_entries SET room_id = NULL WHERE room_id = ?")->execute([$id]);
                }
                $stmtHasCR = $this->db->query("SHOW TABLES LIKE 'class_rooms'");
                if ($stmtHasCR->fetch()) {
                    $this->db->prepare("DELETE FROM class_rooms WHERE id = ?")->execute([$id]);
                }
                $stmtHasR = $this->db->query("SHOW TABLES LIKE 'rooms'");
                if ($stmtHasR->fetch()) {
                    $this->db->prepare("DELETE FROM rooms WHERE id = ?")->execute([$id]);
                }
                break;

            case 'cycle':
            case 'cycles':
                $stmt = $this->db->prepare("DELETE FROM cycles WHERE id = ?");
                $stmt->execute([$id]);
                break;

            case 'level':
            case 'levels':
                $stmt = $this->db->prepare("DELETE FROM levels WHERE id = ?");
                $stmt->execute([$id]);
                break;

            case 'user':
            case 'users':
                $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                break;

            case 'timetable':
            case 'timetables':
                // Récupérer le groupe lié à cet emploi du temps (cycle + week)
                $stmtTt = $this->db->prepare("SELECT cycle_id, week_id FROM timetables WHERE id = ?");
                $stmtTt->execute([$id]);
                $ttInfo = $stmtTt->fetch(PDO::FETCH_ASSOC);

                $idsToDelete = [$id];
                if ($ttInfo) {
                    $stmtGrp = $this->db->prepare("SELECT id FROM timetables WHERE cycle_id <=> ? AND week_id <=> ?");
                    $stmtGrp->execute([$ttInfo['cycle_id'], $ttInfo['week_id']]);
                    $idsToDelete = $stmtGrp->fetchAll(PDO::FETCH_COLUMN) ?: [$id];
                }

                foreach ($idsToDelete as $tId) {
                    $this->db->prepare("DELETE FROM timetable_entries WHERE timetable_id = ?")->execute([$tId]);
                    $this->db->prepare("DELETE FROM timetable_audit_logs WHERE timetable_id = ?")->execute([$tId]);
                    $this->db->prepare("DELETE FROM timetables WHERE id = ?")->execute([$tId]);
                }
                break;


            case 'timetable_slot':
            case 'slot':
                $this->db->prepare("DELETE FROM timetable_entries WHERE slot_id = ?")->execute([$id]);
                $stmt = $this->db->prepare("DELETE FROM timetable_time_slots WHERE id = ?");
                $stmt->execute([$id]);
                break;

            case 'timetable_week':
            case 'week':
                $stmtHasTT = $this->db->query("SHOW TABLES LIKE 'timetables'");
                if ($stmtHasTT->fetch()) {
                    $this->db->prepare("UPDATE timetables SET week_id = NULL WHERE week_id = ?")->execute([$id]);
                }
                $stmt = $this->db->prepare("DELETE FROM timetable_weeks WHERE id = ?");
                $stmt->execute([$id]);
                break;

            case 'subject_group':
            case 'subject_groups':
                $this->db->prepare("UPDATE subjects SET group_id = NULL WHERE group_id = ?")->execute([$id]);
                $stmt = $this->db->prepare("DELETE FROM subject_groups WHERE id = ?");
                $stmt->execute([$id]);
                break;

            case 'department':
            case 'departments':
                $this->db->prepare("UPDATE classes SET department_id = NULL WHERE department_id = ?")->execute([$id]);
                $stmt = $this->db->prepare("DELETE FROM departments WHERE id = ?");
                $stmt->execute([$id]);
                break;

            case 'section':
            case 'sections':
                $this->db->prepare("UPDATE classes SET section_id = NULL WHERE section_id = ?")->execute([$id]);
                $stmt = $this->db->prepare("DELETE FROM sections WHERE id = ?");
                $stmt->execute([$id]);
                break;

            case 'academic_year':
            case 'academic_years':
                $stmt = $this->db->prepare("DELETE FROM academic_years WHERE id = ?");
                $stmt->execute([$id]);
                break;

            case 'sequence':
            case 'sequences':
                $stmt = $this->db->prepare("DELETE FROM sequences WHERE id = ?");
                $stmt->execute([$id]);
                break;

            case 'teaching_type':
            case 'teaching_types':
                $this->db->prepare("UPDATE classes SET teaching_type_id = NULL WHERE teaching_type_id = ?")->execute([$id]);
                $stmt = $this->db->prepare("DELETE FROM teaching_types WHERE id = ?");
                $stmt->execute([$id]);
                break;

            default:
                throw new Exception("La suppression n'est pas gérée pour le type '$type'.");
        }

        $this->tracker->recordEvent('direct_delete_executed', 'system', [
            'entity_type' => $type,
            'entity_id' => $id
        ]);

        return [
            'success' => true,
            'message' => "L'élément de type '$type' (#$id) a été définitivement supprimé."
        ];
    }
}
