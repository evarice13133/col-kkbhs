<?php

namespace App\Services\WhatsApp;

use App\Core\Database;
use App\Core\Security;
use PDO;

class WhatsAppService
{
    private PDO $db;
    private WhatsAppProviderInterface $provider;

    public function __construct(?WhatsAppProviderInterface $provider = null)
    {
        $this->db = Database::getInstance()->getConnection();
        $this->provider = $provider ?? new MetaCloudProvider();
    }

    public function getEligibleRecipients(array $students): array
    {
        $numbers = [];
        foreach ($students as $student) {
            $candidate = $student['parent_contact'] ?? $student['guardian_contact'] ?? $student['contact'] ?? null;
            if (!is_string($candidate) && !is_numeric($candidate)) {
                continue;
            }

            $normalized = PhoneNumberNormalizer::normalize((string) $candidate);
            if ($normalized !== '') {
                $numbers[] = $normalized;
            }
        }

        return PhoneNumberNormalizer::unique($numbers);
    }

    public function saveConsent(int $studentId, string $phone, string $status, ?string $source = null): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO whatsapp_consents (student_id, phone_number, consent_status, source)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE consent_status = VALUES(consent_status), source = VALUES(source), updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$studentId, $phone, $status, $source]);
    }

    public function getConsentStatus(int $studentId, string $phone): string
    {
        try {
            $stmt = $this->db->prepare("SELECT consent_status FROM whatsapp_consents WHERE student_id = ? AND phone_number = ? LIMIT 1");
            $stmt->execute([$studentId, $phone]);
            $value = $stmt->fetchColumn();
            return is_string($value) && $value !== '' ? strtolower($value) : 'unknown';
        } catch (\Throwable $e) {
            return 'unknown';
        }
    }

    public function logMessage(string $type, string $targetPhone, array $context = []): void
    {
        try {
            unset($context['body']);
            $this->db->prepare("
                CREATE TABLE IF NOT EXISTS whatsapp_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    message_type VARCHAR(50) NOT NULL,
                    phone_number VARCHAR(32) NOT NULL,
                    payload LONGTEXT NULL,
                    status VARCHAR(30) NOT NULL DEFAULT 'queued',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ")->execute();

            $success = !empty($context['success']);
            $status = !empty($context['mode']) && $context['mode'] === 'test'
                ? 'simulated'
                : ($success ? 'sent' : 'failed');
            $stmt = $this->db->prepare("INSERT INTO whatsapp_logs (message_type, phone_number, payload, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $type,
                $targetPhone,
                json_encode($context, JSON_THROW_ON_ERROR),
                $status
            ]);
        } catch (\Throwable $e) {
            Security::log('WhatsApp log creation failed: ' . $e->getMessage());
        }
    }

    public function sendBulk(array $recipients, string $templateName, array $components = []): array
    {
        $result = ['success' => true, 'sent' => 0, 'simulated' => 0, 'failed' => 0, 'items' => []];

        foreach ($recipients as $recipient) {
            $phone = is_array($recipient) ? ($recipient['phone'] ?? '') : '';
            $studentId = is_array($recipient) ? (int) ($recipient['student_id'] ?? 0) : 0;
            $normalized = PhoneNumberNormalizer::normalize((string) $phone);
            if ($studentId <= 0 || $normalized === '') {
                $result['failed']++;
                $result['items'][] = ['to' => $normalized ?: null, 'success' => false, 'reason' => 'invalid_recipient'];
                $this->logMessage('template', $normalized, ['success' => false, 'error' => 'invalid_recipient']);
                continue;
            }

            $consentStatus = $this->getConsentStatus($studentId, $normalized);
            if ($consentStatus !== 'granted') {
                $result['failed']++;
                $result['items'][] = ['to' => $normalized, 'success' => false, 'reason' => 'consent_' . $consentStatus];
                $this->logMessage('template', $normalized, ['success' => false, 'error' => 'consent_' . $consentStatus]);
                continue;
            }

            $response = $this->provider->sendTemplate($normalized, $templateName, ['code' => 'fr'], $components);
            $this->logMessage('template', $normalized, $response);
            if (!empty($response['success'])) {
                if (($response['mode'] ?? '') === 'test') {
                    $result['simulated']++;
                } else {
                    $result['sent']++;
                }
                $result['items'][] = [
                    'to' => $normalized,
                    'success' => true,
                    'mode' => $response['mode'] ?? 'live',
                    'message_id' => $response['message_id'] ?? $response['data']['messages'][0]['id'] ?? null,
                ];
            } else {
                $result['failed']++;
                $result['items'][] = [
                    'to' => $normalized,
                    'success' => false,
                    'reason' => $response['error'] ?? 'send_failed',
                    'status' => $response['status'] ?? null,
                    'error_code' => $response['details']['error']['code'] ?? null,
                    'error_subcode' => $response['details']['error']['error_subcode'] ?? null,
                ];
            }
        }

        return $result;
    }

    public function sendTest(string $phone, string $message): array
    {
        $normalized = PhoneNumberNormalizer::normalize($phone);
        if ($normalized === '') {
            return ['success' => false, 'message' => 'Numéro WhatsApp invalide.'];
        }

        $response = $this->provider->sendText($normalized, $message);
        $this->logMessage('test', $normalized, $response);
        return $response['success'] ? ['success' => true, 'message' => 'Test WhatsApp programmé / simulé avec succès.', 'to' => $normalized] : ['success' => false, 'message' => $response['error'] ?? 'Erreur WhatsApp inconnue'];
    }
}
