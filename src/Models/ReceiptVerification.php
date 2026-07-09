<?php

namespace App\Models;

use PDO;

class ReceiptVerification extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Log a verification attempt.
     */
    public function logVerification(array $data): void
    {
        $sql = "
            INSERT INTO receipt_verifications_log (
                verification_code, payment_id, receipt_type, student_id, academic_year_id, is_valid, error_case, ip_address, user_agent
            ) VALUES (
                :verification_code, :payment_id, :receipt_type, :student_id, :academic_year_id, :is_valid, :error_case, :ip_address, :user_agent
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'verification_code' => $data['verification_code'] ?? '',
            'payment_id' => $data['payment_id'] ?? null,
            'receipt_type' => $data['receipt_type'] ?? null,
            'student_id' => $data['student_id'] ?? null,
            'academic_year_id' => $data['academic_year_id'] ?? null,
            'is_valid' => $data['is_valid'] ?? 0,
            'error_case' => $data['error_case'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }

    /**
     * Get statistics for the dashboard.
     */
    public function getStats(): array
    {
        $total = $this->db->query("SELECT COUNT(*) FROM receipt_verifications_log")->fetchColumn();
        $successful = $this->db->query("SELECT COUNT(*) FROM receipt_verifications_log WHERE is_valid = 1")->fetchColumn();
        $failed = $total - $successful;
        
        $mostViewed = $this->db->query("
            SELECT verification_code, COUNT(*) as views
            FROM receipt_verifications_log
            WHERE is_valid = 1
            GROUP BY verification_code
            ORDER BY views DESC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        return [
            'total' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'most_viewed' => $mostViewed
        ];
    }

    /**
     * Get verification history with filters.
     */
    public function getHistory(array $filters = []): array
    {
        $sql = "
            SELECT r.*, 
                   s.nom as student_nom, s.prenom as student_prenom, s.email as matricule,
                   ay.nom as annee_scolaire
            FROM receipt_verifications_log r
            LEFT JOIN students s ON r.student_id = s.id
            LEFT JOIN academic_years ay ON r.academic_year_id = ay.id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters['academic_year_id'])) {
            $sql .= " AND r.academic_year_id = ?";
            $params[] = $filters['academic_year_id'];
        }

        if (!empty($filters['student_id'])) {
            $sql .= " AND r.student_id = ?";
            $params[] = $filters['student_id'];
        }
        
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'valid') {
                $sql .= " AND r.is_valid = 1";
            } elseif ($filters['status'] === 'invalid') {
                $sql .= " AND r.is_valid = 0";
            }
        }

        if (!empty($filters['q'])) {
            $sql .= " AND (r.verification_code LIKE ? OR s.nom LIKE ? OR s.prenom LIKE ?)";
            $q = '%' . $filters['q'] . '%';
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        $sql .= " ORDER BY r.verified_at DESC LIMIT 500";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
