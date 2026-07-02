<?php

namespace App\Controllers;

use App\Core\Security;
use App\Core\Session;
use App\Core\Database;
use App\Services\AIAssistantService;

/**
 * Contrôleur pour l'API de l'Assistant IA
 */
class AIAssistantController
{
    private $db;
    private $aiService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->aiService = new AIAssistantService($this->db);
    }

    /**
     * Gère les requêtes de l'assistant IA
     */
    public function handleRequest()
    {
        // Vérifier l'authentification
        if (!Session::isLogged()) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Non authentifié'
            ], 401);
            return;
        }

        // Vérifier la méthode HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Méthode non autorisée'
            ], 405);
            return;
        }

        // Vérifier si c'est une requête streaming
        $isStreaming = isset($_GET['stream']) && $_GET['stream'] === 'true';

        // Récupérer les données JSON
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['message']) || empty(trim($input['message']))) {
            if ($isStreaming) {
                $this->sendSSE(['type' => 'error', 'message' => 'Message requis']);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Message requis'
                ], 400);
            }
            return;
        }

        // Traiter la question avec ou sans streaming
        if ($isStreaming) {
            $this->processQuestionWithStreaming($input['message']);
        } else {
            $result = $this->aiService->processQuestion($input['message']);
            $this->jsonResponse($result);
        }
    }

    /**
     * Traite la question avec streaming des étapes
     */
    private function processQuestionWithStreaming($message)
    {
        // Configuration pour le streaming
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        // Callback pour envoyer les étapes
        $stepCallback = function($step, $detail) {
            $this->sendSSE([
                'type' => 'step',
                'step' => $step,
                'detail' => $detail
            ]);
        };

        // Traiter la question avec callback
        $result = $this->aiService->processQuestionWithSteps($message, $stepCallback);

        // Envoyer le résultat final
        $this->sendSSE([
            'type' => 'complete',
            'response' => $result['response'],
            'actions' => $result['actions'] ?? []
        ]);
    }

    /**
     * Envoie un événement SSE
     */
    private function sendSSE($data)
    {
        echo "data: " . json_encode($data) . "\n\n";
        ob_flush();
        flush();
    }

    /**
     * Envoie une réponse JSON
     */
    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
