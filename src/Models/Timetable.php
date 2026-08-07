<?php

namespace App\Models;

use PDO;

class Timetable extends BaseModel
{
    public function getAllFiltered(?int $yearId = null, ?int $classId = null, ?int $weekId = null): array
    {
        $sql = "
            SELECT t.*, 
                   ay.nom as academic_year_name,
                   tt.nom as teaching_type_name,
                   c.nom as cycle_name,
                   cl.nom as class_name,
                   w.libelle as week_libelle,
                   w.date_debut as week_start,
                   w.date_fin as week_end,
                   TRIM(CONCAT(COALESCE(u.nom, ''), ' ', COALESCE(u.prenom, ''))) as author_name
            FROM timetables t
            LEFT JOIN academic_years ay ON t.academic_year_id = ay.id
            LEFT JOIN teaching_types tt ON t.teaching_type_id = tt.id
            LEFT JOIN cycles c ON t.cycle_id = c.id
            LEFT JOIN classes cl ON t.class_id = cl.id
            LEFT JOIN timetable_weeks w ON t.week_id = w.id
            LEFT JOIN users u ON t.created_by = u.id
            WHERE (tt.code = 'LMD' OR tt.nom LIKE '%Supérieur%' OR tt.nom LIKE '%LMD%' OR t.teaching_type_id = 9 OR cl.teaching_type_id = 9 OR t.teaching_type_id IS NULL)
        ";
        $params = [];

        if ($yearId) {
            $sql .= " AND t.academic_year_id = ?";
            $params[] = $yearId;
        }
        if ($classId) {
            $sql .= " AND t.class_id = ?";
            $params[] = $classId;
        }
        if ($weekId) {
            $sql .= " AND t.week_id = ?";
            $params[] = $weekId;
        }

        $sql .= " ORDER BY t.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, 
                   ay.nom as academic_year_name,
                   tt.nom as teaching_type_name,
                   c.nom as cycle_name,
                   cl.nom as class_name,
                   w.libelle as week_libelle,
                   w.date_debut as week_start,
                   w.date_fin as week_end,
                   TRIM(CONCAT(COALESCE(u.nom, ''), ' ', COALESCE(u.prenom, ''))) as author_name
            FROM timetables t
            LEFT JOIN academic_years ay ON t.academic_year_id = ay.id
            LEFT JOIN teaching_types tt ON t.teaching_type_id = tt.id
            LEFT JOIN cycles c ON t.cycle_id = c.id
            LEFT JOIN classes cl ON t.class_id = cl.id
            LEFT JOIN timetable_weeks w ON t.week_id = w.id
            LEFT JOIN users u ON t.created_by = u.id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByClassAndWeek(int $classId, int $weekId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM timetables WHERE class_id = ? AND week_id = ?");
        $stmt->execute([$classId, $weekId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO timetables (academic_year_id, teaching_type_id, cycle_id, class_id, week_id, titre, statut, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (int)$data['academic_year_id'],
            (int)$data['teaching_type_id'],
            (int)$data['cycle_id'],
            (int)$data['class_id'],
            (int)$data['week_id'],
            $data['titre'],
            $data['statut'] ?? 'brouillon',
            (int)$data['created_by']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateStatut(int $id, string $statut, bool $isLocked = false): bool
    {
        $stmt = $this->db->prepare("UPDATE timetables SET statut = ?, is_locked = ? WHERE id = ?");
        return $stmt->execute([$statut, $isLocked ? 1 : 0, $id]);
    }

    public function delete(int $id): bool
    {
        try {
            $this->db->beginTransaction();

            $stmtEntries = $this->db->prepare("DELETE FROM timetable_entries WHERE timetable_id = ?");
            $stmtEntries->execute([$id]);

            $stmtLogs = $this->db->prepare("DELETE FROM timetable_audit_logs WHERE timetable_id = ?");
            $stmtLogs->execute([$id]);

            $stmt = $this->db->prepare("DELETE FROM timetables WHERE id = ?");
            $res = $stmt->execute([$id]);

            $this->db->commit();
            return $res;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }
}
