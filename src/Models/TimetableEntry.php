<?php

namespace App\Models;

use PDO;

class TimetableEntry extends BaseModel
{
    public function getByTimetable(int $timetableId): array
    {
        $stmt = $this->db->prepare("
            SELECT te.*, 
                   ts.heure_debut, ts.heure_fin, ts.type_creneau,
                   s.nom as subject_name, COALESCE(s.code_uv, s.code_ue, '') as subject_code,
                   TRIM(CONCAT(COALESCE(u.nom, ''), ' ', COALESCE(u.prenom, ''))) as teacher_name,
                   r.nom as room_name, r.code as room_code
            FROM timetable_entries te
            JOIN timetable_time_slots ts ON te.slot_id = ts.id
            JOIN subjects s ON te.subject_id = s.id
            JOIN users u ON te.teacher_id = u.id
            JOIN class_rooms r ON te.room_id = r.id
            WHERE te.timetable_id = ?
            ORDER BY ts.ordre_affichage ASC, te.day_of_week ASC
        ");
        $stmt->execute([$timetableId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function upsertEntry(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO timetable_entries (timetable_id, slot_id, day_of_week, subject_id, teacher_id, room_id, couleur_hex)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                subject_id = VALUES(subject_id),
                teacher_id = VALUES(teacher_id),
                room_id = VALUES(room_id),
                couleur_hex = VALUES(couleur_hex)
        ");
        return $stmt->execute([
            (int)$data['timetable_id'],
            (int)$data['slot_id'],
            $data['day_of_week'],
            (int)$data['subject_id'],
            (int)$data['teacher_id'],
            (int)$data['room_id'],
            $data['couleur_hex'] ?? '#3b82f6'
        ]);
    }

    public function deleteEntry(int $timetableId, int $slotId, string $dayOfWeek): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM timetable_entries 
            WHERE timetable_id = ? AND slot_id = ? AND day_of_week = ?
        ");
        return $stmt->execute([$timetableId, $slotId, $dayOfWeek]);
    }
}
