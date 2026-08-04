<?php

namespace App\Services\Timetable;

use App\Core\Database;
use App\Core\Session;
use App\Models\TimetableAuditLog;
use PDO;

class TimetableLockService
{
    private PDO $db;
    private TimetableAuditLog $auditLogModel;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance()->getConnection();
        $this->auditLogModel = new TimetableAuditLog();
    }

    /**
     * Vérifie et applique le verrouillage automatique des emplois du temps expirés (168h après la date_fin).
     */
    public function checkAutoLock(int $timetableId): bool
    {
        $stmt = $this->db->prepare("
            SELECT t.id, t.statut, t.is_locked, w.date_fin
            FROM timetables t
            JOIN timetable_weeks w ON t.week_id = w.id
            WHERE t.id = ?
        ");
        $stmt->execute([$timetableId]);
        $tt = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tt) {
            return false;
        }

        // Calcule le timestamp limite : date_fin 23:59:59 + 168 heures (7 jours)
        $endDate = new \DateTime($tt['date_fin'] . ' 23:59:59');
        $lockDeadline = clone $endDate;
        $lockDeadline->modify('+168 hours');

        $now = new \DateTime();

        if ($now > $lockDeadline && $tt['statut'] !== 'verrouille') {
            // Verrouillage automatique
            $upd = $this->db->prepare("UPDATE timetables SET statut = 'verrouille', is_locked = 1 WHERE id = ?");
            $upd->execute([$timetableId]);

            // Consignation dans l'audit log
            $this->auditLogModel->logAction(
                $timetableId,
                (int)Session::get('user_id', 1),
                'LOCK',
                'Verrouillage automatique déclenché après 168h (7 jours post-semaine).',
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            );

            return true;
        }

        return (bool)$tt['is_locked'] || ($tt['statut'] === 'verrouille');
    }

    /**
     * Détermine si l'utilisateur connecté peut modifier ou supprimer l'emploi du temps.
     */
    public function canModify(array $timetable): bool
    {
        $isLocked = $this->checkAutoLock((int)$timetable['id']);
        $userRole = Session::get('user_role');

        if ($isLocked) {
            // Seul le Super Administrateur peut modifier un emploi du temps verrouillé
            return $userRole === 'superadmin';
        }

        return true;
    }

    /**
     * Déverrouillage manuel par le Super Administrateur.
     */
    public function unlockBySuperAdmin(int $timetableId, string $reason): bool
    {
        $userRole = Session::get('user_role');
        if ($userRole !== 'superadmin') {
            return false;
        }

        $userId = (int)Session::get('user_id');
        $stmt = $this->db->prepare("UPDATE timetables SET statut = 'publie', is_locked = 0 WHERE id = ?");
        $success = $stmt->execute([$timetableId]);

        if ($success) {
            $this->auditLogModel->logAction(
                $timetableId,
                $userId,
                'UNLOCK',
                "Déverrouillage manuel par le Super Administrateur. Raison : $reason",
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            );
        }

        return $success;
    }
}
