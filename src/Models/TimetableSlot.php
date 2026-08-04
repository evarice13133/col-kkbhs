<?php

namespace App\Models;

use PDO;

class TimetableSlot extends BaseModel
{
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM timetable_time_slots ORDER BY ordre_affichage ASC, heure_debut ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM timetable_time_slots WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $duree = $this->calculateDuration($data['heure_debut'], $data['heure_fin']);
        $stmt = $this->db->prepare("
            INSERT INTO timetable_time_slots (heure_debut, heure_fin, type_creneau, duree_minutes, ordre_affichage)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['heure_debut'],
            $data['heure_fin'],
            $data['type_creneau'] ?? 'cours',
            $duree,
            (int)($data['ordre_affichage'] ?? 1)
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $duree = $this->calculateDuration($data['heure_debut'], $data['heure_fin']);
        $stmt = $this->db->prepare("
            UPDATE timetable_time_slots 
            SET heure_debut = ?, heure_fin = ?, type_creneau = ?, duree_minutes = ?, ordre_affichage = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['heure_debut'],
            $data['heure_fin'],
            $data['type_creneau'] ?? 'cours',
            $duree,
            (int)($data['ordre_affichage'] ?? 1),
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM timetable_time_slots WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function hasOverlap(string $heureDebut, string $heureFin, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM timetable_time_slots WHERE (heure_debut < ? AND heure_fin > ?)";
        $params = [$heureFin, $heureDebut];

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    private function calculateDuration(string $start, string $end): int
    {
        $t1 = strtotime("1970-01-01 $start");
        $t2 = strtotime("1970-01-01 $end");
        if ($t2 <= $t1) return 0;
        return (int)round(($t2 - $t1) / 60);
    }
}
