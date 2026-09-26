<?php

namespace App\Controllers;

use App\Core\Session;
use App\Services\ImpactAnalysisService;
use App\Services\SmartDeleteService;

class ImpactAnalysisController
{
    private ImpactAnalysisService $analysisService;
    private SmartDeleteService $deleteService;

    public function __construct()
    {
        $this->analysisService = new ImpactAnalysisService();
        $this->deleteService = new SmartDeleteService();
    }

    /**
     * Endpoint GET : Analyse dynamique d'impact
     */
    public function getAnalysis(): void
    {
        header('Content-Type: application/json');

        if (!Session::isLogged()) {
            http_response_code(401);
            echo json_encode(['error' => true, 'message' => __('impact_session_expired')]);
            return;
        }

        $type = $_GET['type'] ?? $_GET['entity'] ?? '';
        $id = (int)($_GET['id'] ?? 0);

        if (empty($type) || $id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => __('impact_invalid_parameters')]);
            return;
        }

        try {
            $analysis = $this->analysisService->analyze($type, $id);
            echo json_encode($analysis);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => __('impact_analysis_server_failure')]);
        }
    }

    /**
     * Endpoint POST : Exécution de la suppression / transfert / archivage
     */
    public function executeDelete(): void
    {
        header('Content-Type: application/json');

        if (!Session::isLogged()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => __('impact_unauthenticated')]);
            return;
        }

        // CSRF Check
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
        if (!empty($csrfToken) && !Session::verifyCsrfToken($csrfToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => __('impact_invalid_csrf')]);
            return;
        }

        $type = $_POST['type'] ?? $_POST['entity_type'] ?? '';
        $id = (int)($_POST['id'] ?? $_POST['entity_id'] ?? 0);
        $scenario = $_POST['scenario'] ?? 'direct'; // 'transfer', 'archive', 'deactivate', 'direct'
        $targetId = (int)($_POST['target_id'] ?? 0);
        $confirmationName = trim($_POST['confirm_name'] ?? '');

        if (empty($type) || $id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => __('impact_missing_entity')]);
            return;
        }

        try {
            $options = [
                'target_id' => $targetId,
                'confirm_name' => $confirmationName
            ];

            $result = $this->deleteService->execute($type, $id, $scenario, $options);
            echo json_encode($result);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => __('impact_delete_server_failure')]);
        }
    }
}
