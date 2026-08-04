<?php

namespace App\Services\Timetable;

use App\Core\Database;
use PDO;

class TimetableConflictService
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    /**
     * Vérifie s'il existe un conflit avant l'insertion ou la mise à jour d'un créneau dans l'emploi du temps.
     * 
     * @return array ['has_conflict' => bool, 'messages' => array]
     */
    public function checkConflict(
        int $timetableId,
        int $weekId,
        int $slotId,
        string $dayOfWeek,
        int $classId,
        int $teacherId,
        int $roomId,
        ?int $excludeEntryId = null
    ): array {
        $messages = [];

        // 1. Vérification si le créneau est une pause
        $stmtPause = $this->db->prepare("SELECT type_creneau, heure_debut, heure_fin FROM timetable_time_slots WHERE id = ?");
        $stmtPause->execute([$slotId]);
        $slotInfo = $stmtPause->fetch(PDO::FETCH_ASSOC);

        if ($slotInfo && $slotInfo['type_creneau'] === 'pause') {
            $messages[] = "Le créneau " . substr($slotInfo['heure_debut'], 0, 5) . " - " . substr($slotInfo['heure_fin'], 0, 5) . " est défini comme PAUSE. Aucun cours ne peut y être planifié.";
            return ['has_conflict' => true, 'messages' => $messages];
        }

        // 2. Conflit Enseignant (Deux cours pour le même enseignant au même créneau/semaine)
        $sqlTeacher = "
            SELECT t.titre, cl.nom as class_name, ts.heure_debut, ts.heure_fin, TRIM(CONCAT(COALESCE(u.nom, ''), ' ', COALESCE(u.prenom, ''))) as teacher_name
            FROM timetable_entries te
            JOIN timetables t ON te.timetable_id = t.id
            JOIN classes cl ON t.class_id = cl.id
            JOIN timetable_time_slots ts ON te.slot_id = ts.id
            JOIN users u ON te.teacher_id = u.id
            WHERE t.week_id = ?
              AND te.day_of_week = ?
              AND te.slot_id = ?
              AND te.teacher_id = ?
        ";
        $paramsTeacher = [$weekId, $dayOfWeek, $slotId, $teacherId];

        if ($excludeEntryId) {
            $sqlTeacher .= " AND te.id != ?";
            $paramsTeacher[] = $excludeEntryId;
        }

        $stmtT = $this->db->prepare($sqlTeacher);
        $stmtT->execute($paramsTeacher);
        $tConflict = $stmtT->fetch(PDO::FETCH_ASSOC);

        if ($tConflict) {
            $messages[] = "Conflit Enseignant : " . htmlspecialchars($tConflict['teacher_name']) . " a déjà un cours prévu en classe " . htmlspecialchars($tConflict['class_name']) . " le " . $dayOfWeek . " de " . substr($tConflict['heure_debut'], 0, 5) . " à " . substr($tConflict['heure_fin'], 0, 5) . ".";
        }

        // 3. Conflit Salle (Deux cours dans la même salle au même créneau/semaine)
        $sqlRoom = "
            SELECT t.titre, cl.nom as class_name, r.nom as room_name, ts.heure_debut, ts.heure_fin
            FROM timetable_entries te
            JOIN timetables t ON te.timetable_id = t.id
            JOIN classes cl ON t.class_id = cl.id
            JOIN class_rooms r ON te.room_id = r.id
            JOIN timetable_time_slots ts ON te.slot_id = ts.id
            WHERE t.week_id = ?
              AND te.day_of_week = ?
              AND te.slot_id = ?
              AND te.room_id = ?
        ";
        $paramsRoom = [$weekId, $dayOfWeek, $slotId, $roomId];

        if ($excludeEntryId) {
            $sqlRoom .= " AND te.id != ?";
            $paramsRoom[] = $excludeEntryId;
        }

        $stmtR = $this->db->prepare($sqlRoom);
        $stmtR->execute($paramsRoom);
        $rConflict = $stmtR->fetch(PDO::FETCH_ASSOC);

        if ($rConflict) {
            $messages[] = "Conflit Salle : La salle " . htmlspecialchars($rConflict['room_name']) . " est déjà occupée par la classe " . htmlspecialchars($rConflict['class_name']) . " le " . $dayOfWeek . " de " . substr($rConflict['heure_debut'], 0, 5) . " à " . substr($rConflict['heure_fin'], 0, 5) . ".";
        }

        // 4. Conflit Classe (Deux cours pour la même classe au même créneau/semaine)
        $sqlClass = "
            SELECT s.nom as subject_name, ts.heure_debut, ts.heure_fin
            FROM timetable_entries te
            JOIN timetables t ON te.timetable_id = t.id
            JOIN subjects s ON te.subject_id = s.id
            JOIN timetable_time_slots ts ON te.slot_id = ts.id
            WHERE t.week_id = ?
              AND te.day_of_week = ?
              AND te.slot_id = ?
              AND t.class_id = ?
              AND te.timetable_id != ?
        ";
        $paramsClass = [$weekId, $dayOfWeek, $slotId, $classId, $timetableId];

        if ($excludeEntryId) {
            $sqlClass .= " AND te.id != ?";
            $paramsClass[] = $excludeEntryId;
        }

        $stmtC = $this->db->prepare($sqlClass);
        $stmtC->execute($paramsClass);
        $cConflict = $stmtC->fetch(PDO::FETCH_ASSOC);

        if ($cConflict) {
            $messages[] = "Conflit Classe : Cette classe a déjà un cours de " . htmlspecialchars($cConflict['subject_name']) . " programmé le " . $dayOfWeek . " de " . substr($cConflict['heure_debut'], 0, 5) . " à " . substr($cConflict['heure_fin'], 0, 5) . " dans un autre emploi du temps.";
        }

        return [
            'has_conflict' => !empty($messages),
            'messages' => $messages
        ];
    }
}
