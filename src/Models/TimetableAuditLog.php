<?php

namespace App\Models;

use PDO;

class TimetableAuditLog extends BaseModel
{
    public function logAction(int $timetableId, int $userId, string $actionType, string $details, string $ipAddress): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO timetable_audit_logs (timetable_id, user_id, action_type, details, ip_address)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$timetableId, $userId, $actionType, $details, $ipAddress]);
    }

    public function getLogsByTimetable(int $timetableId): array
    {
        $stmt = $this->db->prepare("
            SELECT al.*, TRIM(CONCAT(COALESCE(u.nom, ''), ' ', COALESCE(u.prenom, ''))) as user_name, u.role as user_role
            FROM timetable_audit_logs al
            JOIN users u ON al.user_id = u.id
            WHERE al.timetable_id = ?
            ORDER BY al.created_at DESC
        ");
        $stmt->execute([$timetableId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
