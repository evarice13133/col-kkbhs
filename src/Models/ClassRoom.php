<?php

namespace App\Models;

use PDO;

class ClassRoom extends BaseModel
{
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM class_rooms ORDER BY nom ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM class_rooms WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO class_rooms (nom, code, capacite, description, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['nom'],
            strtoupper(trim($data['code'])),
            (int)($data['capacite'] ?? 30),
            $data['description'] ?? null,
            (int)($data['status'] ?? 1)
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE class_rooms 
            SET nom = ?, code = ?, capacite = ?, description = ?, status = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['nom'],
            strtoupper(trim($data['code'])),
            (int)($data['capacite'] ?? 30),
            $data['description'] ?? null,
            (int)($data['status'] ?? 1),
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM class_rooms WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Calcul dynamique du statut (Disponible / Occupée) pour une salle sur une semaine, un jour et un créneau donnés.
     */
    public function getDynamicStatus(int $roomId, int $weekId, string $dayOfWeek, int $slotId): string
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM timetable_entries te
            JOIN timetables t ON te.timetable_id = t.id
            WHERE te.room_id = ?
              AND t.week_id = ?
              AND te.day_of_week = ?
              AND te.slot_id = ?
        ");
        $stmt->execute([$roomId, $weekId, $dayOfWeek, $slotId]);
        $count = (int)$stmt->fetchColumn();

        return $count > 0 ? 'Occupée' : 'Disponible';
    }
}
