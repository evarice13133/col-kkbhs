<?php

namespace App\Services\Timetable;

use App\Core\Database;
use App\Core\Security;
use App\Models\Timetable;
use App\Models\TimetableAuditLog;
use App\Models\TimetableEntry;
use PDO;
use Throwable;

class BulkSchedulingService
{
    private PDO $db;
    private TimetableConflictService $conflictService;
    private TimetableLockService $lockService;
    private Timetable $timetableModel;
    private TimetableEntry $entryModel;
    private TimetableAuditLog $auditLogModel;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance()->getConnection();
        $this->conflictService = new TimetableConflictService($this->db);
        $this->lockService = new TimetableLockService($this->db);
        $this->timetableModel = new Timetable();
        $this->entryModel = new TimetableEntry();
        $this->auditLogModel = new TimetableAuditLog();
    }

    /**
     * Analyse et pré-valide la planification en masse de cours.
     */
    public function validateBulkSchedule(array $params): array
    {
        $weekId = (int)($params['week_id'] ?? 0);
        $subjectId = (int)($params['subject_id'] ?? 0);
        $teacherId = (int)($params['teacher_id'] ?? 0);
        $classIds = array_filter(array_map('intval', (array)($params['class_ids'] ?? [])));
        $days = array_filter((array)($params['days'] ?? []));
        $slotIds = array_filter(array_map('intval', (array)($params['slot_ids'] ?? [])));
        $roomMode = $params['room_mode'] ?? 'auto'; // 'mutualized', 'auto', 'custom_pool'
        $singleRoomId = (int)($params['room_id'] ?? 0);
        $poolRoomIds = array_filter(array_map('intval', (array)($params['room_ids'] ?? [])));
        $colorHex = trim($params['couleur_hex'] ?? '#3b82f6');

        if (!$weekId || !$subjectId || !$teacherId || empty($classIds) || empty($days) || empty($slotIds)) {
            return [
                'success' => false,
                'message' => 'Paramètres incomplets pour l\'analyse de la planification.',
                'schedules' => [],
                'total_generated' => 0,
                'valid_count' => 0,
                'conflict_count' => 0
            ];
        }

        // 1. Récupération des informations complémentaires
        $stmtSub = $this->db->prepare("SELECT id, nom, COALESCE(code_uv, code_ue, '') as code FROM subjects WHERE id = ?");
        $stmtSub->execute([$subjectId]);
        $subject = $stmtSub->fetch(PDO::FETCH_ASSOC);

        $stmtTeach = $this->db->prepare("SELECT id, TRIM(CONCAT(COALESCE(nom, ''), ' ', COALESCE(prenom, ''))) as name FROM users WHERE id = ?");
        $stmtTeach->execute([$teacherId]);
        $teacher = $stmtTeach->fetch(PDO::FETCH_ASSOC);

        // Fetch classes metadata
        $inClassClause = implode(',', $classIds);
        $classesStmt = $this->db->query("SELECT id, nom FROM classes WHERE id IN ($inClassClause) ORDER BY nom ASC");
        $classesList = $classesStmt->fetchAll(PDO::FETCH_ASSOC);
        $classesMap = [];
        foreach ($classesList as $c) {
            $classesMap[$c['id']] = $c['nom'];
        }

        // Fetch slots metadata
        $inSlotClause = implode(',', $slotIds);
        $slotsStmt = $this->db->query("SELECT id, heure_debut, heure_fin, type_creneau, ordre_affichage FROM timetable_time_slots WHERE id IN ($inSlotClause) ORDER BY ordre_affichage ASC");
        $slotsList = $slotsStmt->fetchAll(PDO::FETCH_ASSOC);
        $slotsMap = [];
        foreach ($slotsList as $s) {
            $slotsMap[$s['id']] = $s;
        }

        // Fetch all rooms metadata
        $roomsStmt = $this->db->query("SELECT id, nom, code, capacite FROM class_rooms WHERE status = 1 ORDER BY nom ASC");
        $allRooms = $roomsStmt->fetchAll(PDO::FETCH_ASSOC);
        $roomsMap = [];
        foreach ($allRooms as $r) {
            $roomsMap[$r['id']] = $r['nom'];
        }

        // Map timetables for classes
        $existingTimetables = [];
        $stmtTt = $this->db->prepare("SELECT id, class_id FROM timetables WHERE week_id = ? AND class_id IN ($inClassClause)");
        $stmtTt->execute([$weekId]);
        foreach ($stmtTt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $existingTimetables[(int)$row['class_id']] = (int)$row['id'];
        }

        $generatedSchedules = [];
        $validCount = 0;
        $conflictCount = 0;
        $batchAssignedRooms = []; // Key: "day_slot_roomId" => count or class_id

        // 2. Génération du produit cartésien : Jours x Créneaux x Classes
        foreach ($days as $day) {
            foreach ($slotIds as $slotId) {
                $slotInfo = $slotsMap[$slotId] ?? null;
                if (!$slotInfo || $slotInfo['type_creneau'] === 'pause') {
                    // Exclure les pauses
                    continue;
                }

                $slotLabel = substr($slotInfo['heure_debut'], 0, 5) . ' - ' . substr($slotInfo['heure_fin'], 0, 5);

                foreach ($classIds as $classId) {
                    $className = $classesMap[$classId] ?? "Classe #$classId";
                    $timetableId = $existingTimetables[$classId] ?? 0;

                    // Attribuer la salle selon le mode
                    $assignedRoomId = 0;

                    if ($roomMode === 'mutualized') {
                        $assignedRoomId = $singleRoomId;
                    } elseif ($roomMode === 'custom_pool' && !empty($poolRoomIds)) {
                        // Trouver la première salle disponible dans le pool
                        foreach ($poolRoomIds as $pRoomId) {
                            $batchKey = $day . '_' . $slotId . '_' . $pRoomId;
                            if (!isset($batchAssignedRooms[$batchKey]) && $this->isRoomAvailable($weekId, $day, $slotId, $pRoomId, $classId, $teacherId, $subjectId)) {
                                $assignedRoomId = $pRoomId;
                                $batchAssignedRooms[$batchKey] = $classId;
                                break;
                            }
                        }
                        if (!$assignedRoomId) {
                            $assignedRoomId = reset($poolRoomIds);
                        }
                    } else {
                        // Mode 'auto' : Trouver une salle libre en BDD
                        foreach ($allRooms as $r) {
                            $rId = (int)$r['id'];
                            $batchKey = $day . '_' . $slotId . '_' . $rId;
                            if (!isset($batchAssignedRooms[$batchKey]) && $this->isRoomAvailable($weekId, $day, $slotId, $rId, $classId, $teacherId, $subjectId)) {
                                $assignedRoomId = $rId;
                                $batchAssignedRooms[$batchKey] = $classId;
                                break;
                            }
                        }
                        if (!$assignedRoomId && !empty($allRooms)) {
                            $assignedRoomId = (int)$allRooms[0]['id'];
                        }
                    }

                    $roomName = $roomsMap[$assignedRoomId] ?? "Salle #$assignedRoomId";

                    // Vérification des conflits via TimetableConflictService
                    $conflictCheck = $this->conflictService->checkConflict(
                        $timetableId,
                        $weekId,
                        $slotId,
                        $day,
                        $classId,
                        $teacherId,
                        $assignedRoomId,
                        null,
                        $subjectId
                    );

                    // Auto-suggestion de salle si conflit de salle
                    $suggestedRoomId = null;
                    $suggestedRoomName = null;

                    if ($conflictCheck['has_conflict']) {
                        foreach ($conflictCheck['messages'] as $msg) {
                            if (strpos($msg, 'Conflit Salle') !== false) {
                                // Chercher une salle disponible alternative
                                foreach ($allRooms as $r) {
                                    $altId = (int)$r['id'];
                                    if ($altId !== $assignedRoomId) {
                                        $altCheck = $this->conflictService->checkConflict($timetableId, $weekId, $slotId, $day, $classId, $teacherId, $altId, null, $subjectId);
                                        if (!$altCheck['has_conflict']) {
                                            $suggestedRoomId = $altId;
                                            $suggestedRoomName = $r['nom'];
                                            break;
                                        }
                                    }
                                }
                                break;
                            }
                        }
                    }

                    $hasConflict = $conflictCheck['has_conflict'];
                    if ($hasConflict) {
                        $conflictCount++;
                    } else {
                        $validCount++;
                    }

                    $tempId = 'bulk_' . $classId . '_' . $day . '_' . $slotId;

                    $generatedSchedules[] = [
                        'temp_id' => $tempId,
                        'timetable_id' => $timetableId,
                        'week_id' => $weekId,
                        'class_id' => $classId,
                        'class_name' => $className,
                        'day_of_week' => $day,
                        'slot_id' => $slotId,
                        'slot_label' => $slotLabel,
                        'subject_id' => $subjectId,
                        'subject_name' => $subject['nom'] ?? "Matière #$subjectId",
                        'subject_code' => $subject['code'] ?? '',
                        'teacher_id' => $teacherId,
                        'teacher_name' => $teacher['name'] ?? "Enseignant #$teacherId",
                        'room_id' => $assignedRoomId,
                        'room_name' => $roomName,
                        'couleur_hex' => $colorHex,
                        'has_conflict' => $hasConflict,
                        'conflict_messages' => $conflictCheck['messages'],
                        'suggested_room_id' => $suggestedRoomId,
                        'suggested_room_name' => $suggestedRoomName,
                        'all_rooms' => $allRooms
                    ];
                }
            }
        }

        return [
            'success' => true,
            'schedules' => $generatedSchedules,
            'total_generated' => count($generatedSchedules),
            'valid_count' => $validCount,
            'conflict_count' => $conflictCount
        ];
    }

    /**
     * Teste si une salle est disponible sans conflit BDD.
     */
    private function isRoomAvailable(int $weekId, string $day, int $slotId, int $roomId, int $classId, int $teacherId, int $subjectId): bool
    {
        $stmt = $this->db->prepare("
            SELECT te.subject_id, te.teacher_id
            FROM timetable_entries te
            JOIN timetables t ON te.timetable_id = t.id
            WHERE t.week_id = ? AND te.day_of_week = ? AND te.slot_id = ? AND te.room_id = ? AND t.class_id != ?
        ");
        $stmt->execute([$weekId, $day, $slotId, $roomId, $classId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return true;
        }

        // Si occupée par la même matière ET le même enseignant (cours mutualisé) -> Autorisé
        foreach ($rows as $row) {
            if ((int)$row['subject_id'] !== $subjectId || (int)$row['teacher_id'] !== $teacherId) {
                return false;
            }
        }

        return true;
    }

    /**
     * Enregistrement transactionnel en masse des programmations validées.
     */
    public function saveBulkSchedule(array $schedules, int $userId): array
    {
        if (empty($schedules)) {
            return [
                'success' => false,
                'message' => 'Aucune programmation à enregistrer.',
                'saved_count' => 0
            ];
        }

        $savedCount = 0;
        $errors = [];

        try {
            $this->db->beginTransaction();

            $activeYearId = (int)$this->db->query("SELECT id FROM academic_years WHERE status = 1 ORDER BY id DESC LIMIT 1")->fetchColumn();

            foreach ($schedules as $s) {
                $classId = (int)($s['class_id'] ?? 0);
                $weekId = (int)($s['week_id'] ?? 0);
                $slotId = (int)($s['slot_id'] ?? 0);
                $dayOfWeek = trim($s['day_of_week'] ?? '');
                $subjectId = (int)($s['subject_id'] ?? 0);
                $teacherId = (int)($s['teacher_id'] ?? 0);
                $roomId = (int)($s['room_id'] ?? 0);
                $colorHex = trim($s['couleur_hex'] ?? '#3b82f6');

                if (!$classId || !$weekId || !$slotId || !$dayOfWeek || !$subjectId || !$teacherId || !$roomId) {
                    continue;
                }

                // 1. Assurer l'existence du timetable parent pour cette classe et semaine
                $timetable = $this->timetableModel->findByClassAndWeek($classId, $weekId);
                $timetableId = $timetable ? (int)$timetable['id'] : 0;

                if (!$timetableId) {
                    $classRow = $this->db->query("SELECT nom, cycle_id, teaching_type_id FROM classes WHERE id = $classId")->fetch(PDO::FETCH_ASSOC);
                    $weekRow = $this->db->query("SELECT libelle FROM timetable_weeks WHERE id = $weekId")->fetch(PDO::FETCH_ASSOC);

                    $timetableId = $this->timetableModel->create([
                        'academic_year_id' => $activeYearId,
                        'teaching_type_id' => $classRow['teaching_type_id'] ?? 9,
                        'cycle_id' => $classRow['cycle_id'] ?? 1,
                        'class_id' => $classId,
                        'week_id' => $weekId,
                        'titre' => "Emploi du Temps - " . ($classRow['nom'] ?? 'Classe') . " (" . ($weekRow['libelle'] ?? 'Semaine') . ")",
                        'statut' => 'brouillon',
                        'created_by' => $userId
                    ]);
                    $timetable = $this->timetableModel->find($timetableId);
                }

                // 2. Vérifier si l'emploi du temps est modifiable
                if (!$timetable || !$this->lockService->canModify($timetable)) {
                    $errors[] = "La classe #$classId est verrouillée.";
                    continue;
                }

                // 3. Upsert de l'entrée d'emploi du temps
                $upserted = $this->entryModel->upsertEntry([
                    'timetable_id' => $timetableId,
                    'slot_id' => $slotId,
                    'day_of_week' => $dayOfWeek,
                    'subject_id' => $subjectId,
                    'teacher_id' => $teacherId,
                    'room_id' => $roomId,
                    'couleur_hex' => $colorHex
                ]);

                if ($upserted) {
                    $savedCount++;

                    // Raccordement automatique Subject-Class
                    $stmtCheckSubClass = $this->db->prepare("SELECT 1 FROM subject_classes WHERE class_id = ? AND subject_id = ?");
                    $stmtCheckSubClass->execute([$classId, $subjectId]);
                    if (!$stmtCheckSubClass->fetchColumn()) {
                        $stmtInsSubClass = $this->db->prepare("
                            INSERT INTO subject_classes (subject_id, class_id, academic_year_id)
                            VALUES (?, ?, ?)
                            ON DUPLICATE KEY UPDATE subject_id = VALUES(subject_id)
                        ");
                        $stmtInsSubClass->execute([$subjectId, $classId, $activeYearId]);
                    }

                    // Raccordement automatique Teacher-Assignment
                    $stmtAssig = $this->db->prepare("
                        INSERT INTO teacher_assignments (user_id, subject_id, class_id, academic_year_id)
                        VALUES (?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE user_id = VALUES(user_id)
                    ");
                    $stmtAssig->execute([$teacherId, $subjectId, $classId, $activeYearId]);

                    // Audit Log
                    $this->auditLogModel->logAction(
                        $timetableId,
                        $userId,
                        'BULK_SCHEDULE_ADD',
                        "Planification en masse : Cours de '$subjectId' affecté le $dayOfWeek (Créneau #$slotId, Salle #$roomId).",
                        $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                    );
                }
            }

            $this->db->commit();
            Security::log("Planification en masse exécutée par l'utilisateur #{$userId} : {$savedCount} cours créés.");

            return [
                'success' => true,
                'saved_count' => $savedCount,
                'message' => "$savedCount cours planifiés avec succès.",
                'errors' => $errors
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return [
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde en masse : ' . $e->getMessage(),
                'saved_count' => 0
            ];
        }
    }
}
