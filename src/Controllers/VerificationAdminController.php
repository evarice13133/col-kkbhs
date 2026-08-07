<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\PermissionManager;
use App\Models\ReceiptVerification;
use App\Services\AcademicYearService;

class VerificationAdminController
{
    private \PDO $db;
    private AcademicYearService $academicYearService;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->academicYearService = new AcademicYearService($this->db);
        
        \App\Core\PermissionManager::requirePermission('manage_payments');
    }

    public function index()
    {
        $verifier = new ReceiptVerification();
        $stats = $verifier->getStats();
        
        $filters = [
            'academic_year_id' => $_GET['academic_year_id'] ?? null,
            'status' => $_GET['status'] ?? null,
            'q' => $_GET['q'] ?? null
        ];
        
        $history = $verifier->getHistory($filters);
        
        $academicYears = $this->academicYearService->getAllYears();

        include __DIR__ . '/../Views/admin/verifications/index.php';
    }
}
