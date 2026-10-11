<?php

namespace App\Services\WhatsApp;

use App\Core\Database;

class MetaCloudProvider implements WhatsAppProviderInterface
{
    private string $apiBaseUrl;
    private string $accessToken;
    private string $phoneNumberId;
    private string $businessPhoneNumber;
    private bool $testMode;

    public function __construct(?string $apiBaseUrl = null, ?string $accessToken = null, ?string $phoneNumberId = null, ?bool $testMode = null)
    {
        $resolvedApiBaseUrl = $apiBaseUrl ?: (defined('WHATSAPP_API_BASE_URL') ? WHATSAPP_API_BASE_URL : 'https://graph.facebook.com/v19.0');
        $resolvedAccessToken = $accessToken ?: (defined('WHATSAPP_ACCESS_TOKEN') ? WHATSAPP_ACCESS_TOKEN : '');
        $resolvedPhoneNumberId = $phoneNumberId ?: (defined('WHATSAPP_PHONE_NUMBER_ID') ? WHATSAPP_PHONE_NUMBER_ID : '');
        $resolvedBusinessPhoneNumber = defined('WHATSAPP_BUSINESS_PHONE_NUMBER') ? WHATSAPP_BUSINESS_PHONE_NUMBER : '+237679164801';
        $resolvedTestMode = $testMode ?? (defined('WHATSAPP_TEST_MODE') ? (bool) WHATSAPP_TEST_MODE : true);

        $dbSettings = $this->loadStoredSettings();
        if (!empty($dbSettings)) {
            if (getenv('WHATSAPP_ACCESS_TOKEN') === false && getenv('WHATSAPP_TOKEN') === false && !empty($dbSettings['whatsapp_access_token'])) {
                $resolvedAccessToken = $dbSettings['whatsapp_access_token'];
            }
            if (getenv('WHATSAPP_API_BASE_URL') === false && getenv('WHATSAPP_API_VERSION') === false && !empty($dbSettings['whatsapp_api_base_url'])) {
                $resolvedApiBaseUrl = $dbSettings['whatsapp_api_base_url'];
            }
            if (getenv('WHATSAPP_PHONE_NUMBER_ID') === false && getenv('WHATSAPP_PHONE_ID') === false && !empty($dbSettings['whatsapp_phone_number_id'])) {
                $resolvedPhoneNumberId = $dbSettings['whatsapp_phone_number_id'];
            }
            if (getenv('WHATSAPP_BUSINESS_PHONE_NUMBER') === false && !empty($dbSettings['whatsapp_business_phone_number'])) {
                $resolvedBusinessPhoneNumber = $dbSettings['whatsapp_business_phone_number'];
            }
            if (isset($dbSettings['whatsapp_test_mode'])) {
                $resolvedTestMode = (bool) filter_var($dbSettings['whatsapp_test_mode'], FILTER_VALIDATE_BOOLEAN);
            }
        }

        $apiParts = parse_url($resolvedApiBaseUrl);
        if (
            !is_array($apiParts)
            || ($apiParts['scheme'] ?? '') !== 'https'
            || ($apiParts['host'] ?? '') !== 'graph.facebook.com'
            || !preg_match('#^/v[0-9]+\.[0-9]+$#', $apiParts['path'] ?? '')
        ) {
            $resolvedApiBaseUrl = 'https://graph.facebook.com/v20.0';
        }

        $this->apiBaseUrl = rtrim($resolvedApiBaseUrl, '/');
        $this->accessToken = (string) $resolvedAccessToken;
        $this->phoneNumberId = (string) $resolvedPhoneNumberId;
        $this->businessPhoneNumber = (string) $resolvedBusinessPhoneNumber;
        $this->testMode = (bool) $resolvedTestMode;
    }

    private function loadStoredSettings(): array
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT setting_key, setting_value FROM whatsapp_settings");
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }

        $settings = [];
        foreach ($rows as $row) {
            $settings[(string) $row['setting_key']] = (string) $row['setting_value'];
        }

        return $settings;
    }

    public function getBusinessPhoneNumber(): string
    {
        return $this->businessPhoneNumber;
    }

    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    public function isAvailable(): bool
    {
        return !$this->testMode && $this->accessToken !== '' && $this->phoneNumberId !== '';
    }

    public function verifyConnection(): array
    {
        if ($this->accessToken === '' || $this->phoneNumberId === '') {
            return ['success' => false, 'error' => 'Les identifiants Meta sont incomplets.'];
        }
        if (!function_exists('curl_init')) {
            return ['success' => false, 'error' => 'L’extension PHP cURL est indisponible sur le serveur.'];
        }

        $url = $this->apiBaseUrl . '/' . rawurlencode($this->phoneNumberId) . '?fields=id,display_phone_number,verified_name';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Accept: application/json',
            ],
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
        ]);

        $response = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['success' => false, 'error' => $curlError !== '' ? 'Connexion à Meta impossible.' : 'Échec de la requête Meta.'];
        }
        if ($statusCode < 200 || $statusCode >= 300) {
            return ['success' => false, 'error' => 'Meta a refusé la vérification (HTTP ' . $statusCode . ').'];
        }

        $data = json_decode($response, true);
        if (!is_array($data) || !isset($data['id'])) {
            return ['success' => false, 'error' => 'Réponse de vérification Meta invalide.'];
        }

        return [
            'success' => true,
            'phone_number_id' => (string) $data['id'],
            'display_phone_number' => (string) ($data['display_phone_number'] ?? ''),
            'verified_name' => (string) ($data['verified_name'] ?? ''),
        ];
    }

    public function sendText(string $to, string $message): array
    {
        if ($this->testMode) {
            return ['success' => true, 'mode' => 'test', 'message_id' => 'simulated-' . bin2hex(random_bytes(8)), 'to' => $to, 'body' => $message];
        }
        if (!$this->isAvailable()) {
            return ['success' => false, 'error' => 'whatsapp_configuration_incomplete'];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $message],
        ];

        return $this->post('/' . rawurlencode($this->phoneNumberId) . '/messages', $payload);
    }

    public function sendTemplate(string $to, string $templateName, array $language = ['code' => 'fr'], array $components = []): array
    {
        if ($this->testMode) {
            return ['success' => true, 'mode' => 'test', 'message_id' => 'simulated-template-' . bin2hex(random_bytes(8)), 'to' => $to, 'template' => $templateName];
        }
        if (!$this->isAvailable()) {
            return ['success' => false, 'error' => 'whatsapp_configuration_incomplete'];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => $language,
            ],
        ];

        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }

        return $this->post('/' . rawurlencode($this->phoneNumberId) . '/messages', $payload);
    }

    private function post(string $endpoint, array $payload): array
    {
        $ch = curl_init($this->apiBaseUrl . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $error !== '') {
            return ['success' => false, 'error' => $error ?: 'curl_failed', 'status' => $statusCode];
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return ['success' => false, 'error' => 'invalid_json_response', 'status' => $statusCode, 'raw' => $response];
        }

        if ($statusCode >= 400) {
            return ['success' => false, 'status' => $statusCode, 'error' => $decoded['error']['message'] ?? 'meta_api_error', 'details' => $decoded];
        }

        return ['success' => true, 'status' => $statusCode, 'data' => $decoded];
    }
}
