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
        ?int $excludeEntryId = null,
        int $subjectId = 0
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

        // 2. Vérification de l'association Matière-Classe
        // Règle Métier : Si une matière n'est pas rattachée à la classe, l'application propose une confirmation de rattachement automatique au lieu de bloquer.
        // La validation réelle se fait via la fenêtre de confirmation.

        // 3. Conflit Enseignant (Deux cours pour le même enseignant au même créneau/semaine)
        // Règle Métier : Un enseignant peut changer de salle d'un créneau à un autre dans la même journée.
        // Toutefois, au MÊME créneau horaire, il ne peut pas dispenser deux matières différentes.
        $sqlTeacher = "
            SELECT te.subject_id, t.titre, cl.nom as class_name, r.nom as room_name, ts.heure_debut, ts.heure_fin,
                   TRIM(CONCAT(COALESCE(u.nom, ''), ' ', COALESCE(u.prenom, ''))) as teacher_name,
                   s.nom as existing_subject_name
            FROM timetable_entries te
            JOIN timetables t ON te.timetable_id = t.id
            JOIN classes cl ON t.class_id = cl.id
            JOIN class_rooms r ON te.room_id = r.id
            JOIN timetable_time_slots ts ON te.slot_id = ts.id
            JOIN users u ON te.teacher_id = u.id
            JOIN subjects s ON te.subject_id = s.id
            WHERE t.week_id = ?
              AND te.day_of_week = ?
              AND te.slot_id = ?
              AND te.teacher_id = ?
              AND t.class_id != ?
        ";
        $paramsTeacher = [$weekId, $dayOfWeek, $slotId, $teacherId, $classId];

        if ($excludeEntryId) {
            $sqlTeacher .= " AND te.id != ?";
            $paramsTeacher[] = $excludeEntryId;
        }

        $stmtT = $this->db->prepare($sqlTeacher);
        $stmtT->execute($paramsTeacher);
        $tConflicts = $stmtT->fetchAll(PDO::FETCH_ASSOC);

        foreach ($tConflicts as $tConflict) {
            // Si la matière existante est différente de la matière à programmer -> Conflit sur le même créneau !
            if ($subjectId > 0 && (int)$tConflict['subject_id'] !== $subjectId) {
                $messages[] = "Conflit Enseignant au créneau " . substr($tConflict['heure_debut'], 0, 5) . " - " . substr($tConflict['heure_fin'], 0, 5) . " : " . htmlspecialchars($tConflict['teacher_name']) . " est déjà en salle " . htmlspecialchars($tConflict['room_name']) . " avec la classe " . htmlspecialchars($tConflict['class_name']) . " pour le cours de '" . htmlspecialchars($tConflict['existing_subject_name']) . "'. Un enseignant ne peut pas dispenser deux matières différentes au même créneau.";
            }
        }

        // 4. Conflit Salle (Deux cours dans la même salle au même créneau/semaine)
        // Règle Métier : Plusieurs classes peuvent occuper la même salle au même créneau
        // UNIQUEMENT s'il s'agit du MÊME cours mutualisé (MÊME matière ET MÊME enseignant).
        $sqlRoom = "
            SELECT te.subject_id, te.teacher_id, t.titre, cl.nom as class_name, r.nom as room_name, ts.heure_debut, ts.heure_fin,
                   s.nom as existing_subject_name,
                   TRIM(CONCAT(COALESCE(u.nom, ''), ' ', COALESCE(u.prenom, ''))) as teacher_name
            FROM timetable_entries te
            JOIN timetables t ON te.timetable_id = t.id
            JOIN classes cl ON t.class_id = cl.id
            JOIN class_rooms r ON te.room_id = r.id
            JOIN timetable_time_slots ts ON te.slot_id = ts.id
            JOIN subjects s ON te.subject_id = s.id
            JOIN users u ON te.teacher_id = u.id
            WHERE t.week_id = ?
              AND te.day_of_week = ?
              AND te.slot_id = ?
              AND te.room_id = ?
              AND t.class_id != ?
        ";
        $paramsRoom = [$weekId, $dayOfWeek, $slotId, $roomId, $classId];

        if ($excludeEntryId) {
            $sqlRoom .= " AND te.id != ?";
            $paramsRoom[] = $excludeEntryId;
        }

        $stmtR = $this->db->prepare($sqlRoom);
        $stmtR->execute($paramsRoom);
        $rConflicts = $stmtR->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rConflicts as $rConflict) {
            $sameSubject = ($subjectId > 0 && (int)$rConflict['subject_id'] === $subjectId);
            $sameTeacher = ($teacherId > 0 && (int)$rConflict['teacher_id'] === $teacherId);

            if (!$sameSubject || !$sameTeacher) {
                if (!$sameSubject && !$sameTeacher) {
                    $messages[] = "Conflit Salle : La salle " . htmlspecialchars($rConflict['room_name']) . " est déjà occupée au même créneau par la classe " . htmlspecialchars($rConflict['class_name']) . " (" . htmlspecialchars($rConflict['teacher_name']) . " - " . htmlspecialchars($rConflict['existing_subject_name']) . "). L'occupation d'une même salle par plusieurs classes est réservée aux cours mutualisés (Même matière ET même enseignant).";
                } elseif (!$sameSubject) {
                    $messages[] = "Conflit Salle : La salle " . htmlspecialchars($rConflict['room_name']) . " est déjà occupée au même créneau par la classe " . htmlspecialchars($rConflict['class_name']) . " pour une autre matière (" . htmlspecialchars($rConflict['existing_subject_name']) . ").";
                } else {
                    $messages[] = "Conflit Salle : La salle " . htmlspecialchars($rConflict['room_name']) . " est déjà occupée au même créneau par la classe " . htmlspecialchars($rConflict['class_name']) . " avec un autre enseignant (" . htmlspecialchars($rConflict['teacher_name']) . ").";
                }
            }
        }

        // 5. Conflit Classe (Deux cours pour la même classe au même créneau/semaine)
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
        ";
        $paramsClass = [$weekId, $dayOfWeek, $slotId, $classId];

        if ($timetableId > 0) {
            $sqlClass .= " AND te.timetable_id != ?";
            $paramsClass[] = $timetableId;
        }

        if ($excludeEntryId) {
            $sqlClass .= " AND te.id != ?";
            $paramsClass[] = $excludeEntryId;
        }

        $stmtC = $this->db->prepare($sqlClass);
        $stmtC->execute($paramsClass);
        $cConflict = $stmtC->fetch(PDO::FETCH_ASSOC);

        if ($cConflict) {
            $messages[] = "Conflit Classe : Cette classe a déjà un cours de " . htmlspecialchars($cConflict['subject_name']) . " programmé le " . $dayOfWeek . " de " . substr($cConflict['heure_debut'], 0, 5) . " à " . substr($cConflict['heure_fin'], 0, 5) . ".";
        }

        return [
            'has_conflict' => !empty($messages),
            'messages' => $messages
        ];
    }

    /**
     * Analyse tous les conflits sur une grille multi-classes complète pour une semaine donnée.
     * Prends en compte les cours mutualisés (tronc commun).
     * 
     * @return array Indexé par "day_slot_classId" => array de messages de conflit
     */
    public function getGridConflictsForWeek(int $weekId, array $classIds): array
    {
        if (empty($classIds) || $weekId <= 0) {
            return [];
        }

        $inClause = implode(',', array_map('intval', $classIds));
        $stmt = $this->db->query("
            SELECT te.id as entry_id, te.slot_id, te.day_of_week, te.teacher_id, te.room_id, te.subject_id,
                   t.class_id, t.id as timetable_id,
                   cl.nom as class_name,
                   r.nom as room_name,
                   s.nom as subject_name,
                   TRIM(CONCAT(COALESCE(u.nom, ''), ' ', COALESCE(u.prenom, ''))) as teacher_name
            FROM timetable_entries te
            JOIN timetables t ON te.timetable_id = t.id
            JOIN classes cl ON t.class_id = cl.id
            JOIN class_rooms r ON te.room_id = r.id
            JOIN users u ON te.teacher_id = u.id
            JOIN subjects s ON te.subject_id = s.id
            WHERE t.week_id = $weekId AND t.class_id IN ($inClause)
        ");
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $conflictsMap = [];

        // 1. Groupement par teacher + day + slot
        $teacherSlot = [];
        // 2. Groupement par room + day + slot
        $roomSlot = [];

        foreach ($entries as $e) {
            $keyT = $e['teacher_id'] . '_' . $e['day_of_week'] . '_' . $e['slot_id'];
            $teacherSlot[$keyT][] = $e;

            $keyR = $e['room_id'] . '_' . $e['day_of_week'] . '_' . $e['slot_id'];
            $roomSlot[$keyR][] = $e;
        }

        // Enseignant : Conflit si l'enseignant a des cours sur des MATIERES DIFFERENTES au même créneau.
        foreach ($teacherSlot as $group) {
            if (count($group) > 1) {
                $subjectIds = array_unique(array_column($group, 'subject_id'));
                if (count($subjectIds) > 1) {
                    foreach ($group as $e) {
                        $cellKey = $e['day_of_week'] . '_' . $e['slot_id'] . '_' . $e['class_id'];
                        $conflictsMap[$cellKey][] = "Enseignant (" . htmlspecialchars($e['teacher_name']) . ") assigné à des matières différentes en même temps.";
                    }
                }
            }
        }

        // Salle : Conflit si la salle est occupée par plusieurs classes sauf s'il s'agit d'un cours mutualisé (même matière ET même enseignant)
        foreach ($roomSlot as $group) {
            if (count($group) > 1) {
                $subjectIds = array_unique(array_column($group, 'subject_id'));
                $teacherIds = array_unique(array_column($group, 'teacher_id'));

                if (count($subjectIds) > 1 || count($teacherIds) > 1) {
                    foreach ($group as $e) {
                        $cellKey = $e['day_of_week'] . '_' . $e['slot_id'] . '_' . $e['class_id'];
                        $conflictsMap[$cellKey][] = "Salle (" . htmlspecialchars($e['room_name']) . ") : Plusieurs cours non mutualisés s'y déroulent au même créneau.";
                    }
                }
            }
        }

        return $conflictsMap;
    }
}
