<?php

namespace App\Models;

use PDO;

class CourseWeek extends BaseModel
{
    public function getByAcademicYear(int $academicYearId): array
    {
        $stmt = $this->db->prepare("
            SELECT w.*, ay.nom as academic_year_libelle
            FROM timetable_weeks w
            JOIN academic_years ay ON w.academic_year_id = ay.id
            WHERE w.academic_year_id = ?
            ORDER BY w.date_debut ASC
        ");
        $stmt->execute([$academicYearId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT w.*, ay.nom as academic_year_libelle
            FROM timetable_weeks w
            LEFT JOIN academic_years ay ON w.academic_year_id = ay.id
            ORDER BY w.date_debut DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT w.*, ay.nom as academic_year_libelle
            FROM timetable_weeks w
            LEFT JOIN academic_years ay ON w.academic_year_id = ay.id
            WHERE w.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO timetable_weeks (academic_year_id, libelle, date_debut, date_fin)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            (int)$data['academic_year_id'],
            $data['libelle'],
            $data['date_debut'],
            $data['date_fin']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE timetable_weeks 
            SET academic_year_id = ?, libelle = ?, date_debut = ?, date_fin = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            (int)$data['academic_year_id'],
            $data['libelle'],
            $data['date_debut'],
            $data['date_fin'],
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM timetable_weeks WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function hasOverlap(int $academicYearId, string $dateDebut, string $dateFin, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM timetable_weeks WHERE academic_year_id = ? AND (date_debut <= ? AND date_fin >= ?)";
        $params = [$academicYearId, $dateFin, $dateDebut];

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    /**
     * Suggère la prochaine date de début et de fin de semaine disponible pour une année académique.
     */
    public function suggestNextWeek(int $academicYearId): array
    {
        $stmt = $this->db->prepare("
            SELECT date_fin, libelle 
            FROM timetable_weeks 
            WHERE academic_year_id = ? 
            ORDER BY date_fin DESC 
            LIMIT 1
        ");
        $stmt->execute([$academicYearId]);
        $lastWeek = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($lastWeek) {
            $lastEnd = new \DateTime($lastWeek['date_fin']);
            // Lundi suivant (ex: date_fin + 3 jours si c'est un Vendredi)
            $nextStart = clone $lastEnd;
            $nextStart->modify('+2 days');
            if ($nextStart->format('N') != 1) { // 1 = Lundi
                $nextStart->modify('next monday');
            }
        } else {
            $nextStart = new \DateTime();
            if ($nextStart->format('N') != 1) {
                $nextStart->modify('next monday');
            }
        }

        $nextEnd = clone $nextStart;
        $nextEnd->modify('+6 days'); // 7ᵉ jour (semaine de 7 jours)

        return [
            'suggested_start' => $nextStart->format('Y-m-d'),
            'suggested_end' => $nextEnd->format('Y-m-d'),
            'suggested_libelle' => 'Semaine du ' . $nextStart->format('d/m/Y')
        ];
    }
}
