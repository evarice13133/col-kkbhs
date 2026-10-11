<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\PermissionManager;
use App\Core\Session;
use App\Models\InsolventStudent;
use App\Services\AcademicYearService;
use App\Services\WhatsApp\MetaCloudProvider;
use App\Services\WhatsApp\WhatsAppService;
use PDO;

class WhatsAppController
{
    private PDO $db;
    private WhatsAppService $service;

    public function __construct()
    {
        PermissionManager::requirePermission('manage_settings');
        $this->db = Database::getInstance()->getConnection();
        $this->service = new WhatsAppService();
        $this->ensureWhatsAppTables();
    }

    private function ensureWhatsAppTables(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS whatsapp_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) NOT NULL UNIQUE,
                setting_value TEXT NULL,
                is_enabled TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO whatsapp_settings (setting_key, setting_value, is_enabled) VALUES ('whatsapp_module_enabled', '1', 1)"
        );
        $stmt->execute();

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS whatsapp_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                message_type VARCHAR(50) NOT NULL,
                phone_number VARCHAR(32) NOT NULL,
                payload LONGTEXT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'queued',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_whatsapp_logs_phone (phone_number),
                INDEX idx_whatsapp_logs_type (message_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS whatsapp_consents (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT NOT NULL,
                phone_number VARCHAR(32) NOT NULL,
                consent_status VARCHAR(20) NOT NULL DEFAULT 'unknown',
                source VARCHAR(50) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_whatsapp_consent_student_phone (student_id, phone_number),
                INDEX idx_whatsapp_consent_status (consent_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function index(): void
    {
        $whatsappModuleEnabled = $this->isModuleEnabled();
        include __DIR__ . '/../Views/pilotage/whatsapp.php';
    }

    public function isModuleEnabled(): bool
    {
        $stmt = $this->db->prepare(
            "SELECT setting_value FROM whatsapp_settings WHERE setting_key = 'whatsapp_module_enabled' LIMIT 1"
        );
        $stmt->execute();
        return (string) $stmt->fetchColumn() !== '0';
    }

    public function requireModuleAccess(bool $isApiRequest): void
    {
        if ($this->isModuleEnabled() || Session::get('user_role') === 'superadmin') {
            return;
        }

        if ($isApiRequest) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Le module WhatsApp a été désactivé par le superadministrateur.',
            ], 403);
        }

        http_response_code(403);
        echo 'Le module WhatsApp a été désactivé par le superadministrateur.';
        exit;
    }

    public function updateModuleStatus(): void
    {
        if (Session::get('user_role') !== 'superadmin') {
            $this->jsonResponse(['success' => false, 'message' => 'Action réservée au superadministrateur.'], 403);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $payload = is_array($input) ? $input : [];
        $enabled = $payload['enabled'] ?? null;
        if (!in_array($enabled, [true, false, 0, 1, '0', '1'], true)) {
            $this->jsonResponse(['success' => false, 'message' => 'État du module invalide.'], 400);
        }

        $stmt = $this->db->prepare(
            "UPDATE whatsapp_settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_key = 'whatsapp_module_enabled'"
        );
        $stmt->execute([$enabled === true || $enabled === 1 || $enabled === '1' ? '1' : '0']);

        $moduleEnabled = $this->isModuleEnabled();
        $this->jsonResponse([
            'success' => true,
            'enabled' => $moduleEnabled,
            'message' => $moduleEnabled
                ? 'Le module WhatsApp est activé.'
                : 'Le module WhatsApp est désactivé pour les utilisateurs.',
        ]);
    }

    public function getConfig(): void
    {
        $this->ensureWhatsAppTables();
        $stmt = $this->db->query("SELECT setting_key, setting_value FROM whatsapp_settings");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $settings = [];
        foreach ($rows as $row) {
            if ($row['setting_value'] !== '' || $row['setting_key'] === 'whatsapp_test_mode') {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }
        $settings += [
            'whatsapp_test_mode' => WHATSAPP_TEST_MODE ? '1' : '0',
            'whatsapp_business_phone_number' => WHATSAPP_BUSINESS_PHONE_NUMBER,
            'whatsapp_phone_number_id' => WHATSAPP_PHONE_NUMBER_ID,
            'whatsapp_api_base_url' => WHATSAPP_API_BASE_URL,
            'whatsapp_template_name' => WHATSAPP_TEMPLATE_NAME,
        ];
        $settings['whatsapp_token_configured'] = !empty($settings['whatsapp_access_token']) || WHATSAPP_ACCESS_TOKEN !== '';
        $settings['whatsapp_webhook_token_configured'] = !empty($settings['whatsapp_webhook_verify_token']) || WHATSAPP_WEBHOOK_VERIFY_TOKEN !== '';
        unset($settings['whatsapp_access_token'], $settings['whatsapp_webhook_verify_token'], $settings['whatsapp_app_secret']);
        $this->jsonResponse(['success' => true, 'data' => $settings]);
    }

    public function searchStudents(): void
    {
        $query = trim((string) ($_GET['q'] ?? ''));
        if (strlen($query) < 2) {
            $this->jsonResponse(['success' => true, 'data' => []]);
            return;
        }

        $term = '%' . $query . '%';
        $stmt = $this->db->prepare("
            SELECT id, nom, prenom
            FROM students
            WHERE actif = 1 AND (nom LIKE ? OR prenom LIKE ? OR CONCAT(prenom, ' ', nom) LIKE ? OR CAST(id AS CHAR) LIKE ?)
            ORDER BY nom, prenom
            LIMIT 20
        ");
        $stmt->execute([$term, $term, $term, $term]);
        $this->jsonResponse(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    public function targetOptions(): void
    {
        $yearId = (new AcademicYearService($this->db))->getActiveYearId();
        if ($yearId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Aucune année scolaire active n’est configurée.'], 422);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT DISTINCT tt.id AS teaching_type_id, tt.nom AS teaching_type_name,
                   c.id AS class_id, c.nom AS class_name
            FROM teaching_types tt
            JOIN classes c ON c.teaching_type_id = tt.id AND c.status = 1
            JOIN enrollments e ON e.class_id = c.id AND e.academic_year_id = ?
            JOIN students s ON s.id = e.student_id
            WHERE tt.actif = 1 AND s.actif = 1 AND COALESCE(s.is_withdrawn, 0) = 0 AND s.status = 'Inscrit'
            ORDER BY tt.position, tt.nom
        ");
        $stmt->execute([$yearId]);
        $types = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $typeId = (int) $row['teaching_type_id'];
            if (!isset($types[$typeId])) {
                $types[$typeId] = [
                    'id' => $typeId,
                    'nom' => $row['teaching_type_name'],
                    'classes' => [],
                ];
            }
            $types[$typeId]['classes'][] = [
                'id' => (int) $row['class_id'],
                'nom' => $row['class_name'],
            ];
        }
        $this->jsonResponse([
            'success' => true,
            'academic_year_id' => $yearId,
            'teaching_types' => array_values($types),
        ]);
    }

    public function searchTargets(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $payload = is_array($input) ? $input : [];
        $result = $this->resolveTargetStudents(
            is_array($payload['filters'] ?? null) ? $payload['filters'] : [],
            [],
            trim((string) ($payload['query'] ?? ''))
        );
        if (isset($result['error'])) {
            $this->jsonResponse(['success' => false, 'message' => $result['error']], $result['status'] ?? 400);
            return;
        }

        $this->jsonResponse([
            'success' => true,
            'count' => count($result['students']),
            'truncated' => $result['truncated'],
            'data' => $result['students'],
        ]);
    }

    public function previewRecipients(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $payload = is_array($input) ? $input : $_POST;
        $studentIds = $this->normalizeStudentIds($payload['student_ids'] ?? []);

        if (empty($studentIds)) {
            $this->jsonResponse(['success' => false, 'message' => 'Aucun élève sélectionné.'], 400);
            return;
        }
        if (count($studentIds) > 500) {
            $this->jsonResponse(['success' => false, 'message' => 'La prévisualisation est limitée à 500 élèves à la fois.'], 400);
            return;
        }

        $targetResult = $this->resolveTargetStudents(
            is_array($payload['filters'] ?? null) ? $payload['filters'] : [],
            $studentIds
        );
        if (isset($targetResult['error'])) {
            $this->jsonResponse(['success' => false, 'message' => $targetResult['error']], $targetResult['status'] ?? 400);
            return;
        }

        $recipients = [];
        foreach ($targetResult['students'] as $student) {
            $contacts = [$student['parent_contact'] ?? '', $student['guardian_contact'] ?? ''];
            foreach ($contacts as $contact) {
                $phone = trim((string) $contact);
                if ($phone === '') {
                    continue;
                }
                $normalized = \App\Services\WhatsApp\PhoneNumberNormalizer::normalize($phone);
                if ($normalized !== '') {
                    $recipients[] = [
                        'student_id' => (int) $student['id'],
                        'student_name' => trim((string) ($student['prenom'] ?? '') . ' ' . ($student['nom'] ?? '')),
                        'phone' => $normalized,
                    ];
                }
            }
        }

        $recipients = array_values(array_reduce($recipients, static function ($carry, $item) {
            $key = $item['student_id'] . ':' . $item['phone'];
            $carry[$key] = $item;
            return $carry;
        }, []));

        if ($recipients !== []) {
            $ids = array_values(array_unique(array_column($recipients, 'student_id')));
            $idPlaceholders = implode(',', array_fill(0, count($ids), '?'));
            $consentStmt = $this->db->prepare(
                "SELECT student_id, phone_number, consent_status FROM whatsapp_consents WHERE student_id IN ($idPlaceholders)"
            );
            $consentStmt->execute($ids);
            $consents = [];
            foreach ($consentStmt->fetchAll(PDO::FETCH_ASSOC) as $consent) {
                $consents[$consent['student_id'] . ':' . $consent['phone_number']] = $consent['consent_status'];
            }
            foreach ($recipients as &$recipient) {
                $recipient['consent_status'] = $consents[$recipient['student_id'] . ':' . $recipient['phone']] ?? 'unknown';
                $recipient['eligible'] = $recipient['consent_status'] === 'granted';
            }
            unset($recipient);
        }

        $this->jsonResponse([
            'success' => true,
            'count' => count($recipients),
            'eligible_count' => count(array_filter($recipients, static fn($recipient) => $recipient['eligible'] ?? false)),
            'data' => $recipients,
        ]);
    }

    public function saveConfig(): void
    {
        $this->ensureWhatsAppTables();

        $input = json_decode(file_get_contents('php://input'), true);
        $payload = is_array($input) ? $input : $_POST;
        if (isset($payload['whatsapp_test_mode']) && !in_array((string) $payload['whatsapp_test_mode'], ['0', '1'], true)) {
            $this->jsonResponse(['success' => false, 'message' => 'Le mode test doit être Oui ou Non.'], 400);
            return;
        }
        if (isset($payload['whatsapp_api_base_url'])) {
            $apiUrl = trim((string) $payload['whatsapp_api_base_url']);
            $apiParts = parse_url($apiUrl);
            if (
                !is_array($apiParts)
                || ($apiParts['scheme'] ?? '') !== 'https'
                || ($apiParts['host'] ?? '') !== 'graph.facebook.com'
                || !preg_match('#^/v[0-9]+\.[0-9]+$#', $apiParts['path'] ?? '')
            ) {
                $this->jsonResponse(['success' => false, 'message' => 'L’adresse API doit utiliser https://graph.facebook.com/vX.Y.'], 400);
                return;
            }
        }
        if (isset($payload['whatsapp_template_name']) && !preg_match('/^[a-zA-Z0-9_]+$/', trim((string) $payload['whatsapp_template_name']))) {
            $this->jsonResponse(['success' => false, 'message' => 'Le nom du modèle Meta est invalide.'], 400);
            return;
        }
        if (isset($payload['whatsapp_phone_number_id']) && !preg_match('/^[0-9]+$/', trim((string) $payload['whatsapp_phone_number_id']))) {
            $this->jsonResponse(['success' => false, 'message' => 'Le Phone Number ID doit contenir uniquement des chiffres.'], 400);
            return;
        }
        if (isset($payload['whatsapp_access_token']) && strlen(trim((string) $payload['whatsapp_access_token'])) > 10000) {
            $this->jsonResponse(['success' => false, 'message' => 'Le jeton Meta dépasse la longueur autorisée.'], 400);
            return;
        }

        $configKeys = [
            'whatsapp_test_mode',
            'whatsapp_business_phone_number',
            'whatsapp_phone_number_id',
            'whatsapp_access_token',
            'whatsapp_webhook_verify_token',
            'whatsapp_api_base_url',
            'whatsapp_template_name',
            'whatsapp_app_id',
            'whatsapp_app_secret',
        ];

        foreach ($configKeys as $key) {
            if (!array_key_exists($key, $payload)) {
                continue;
            }

            $value = trim((string) ($payload[$key] ?? ''));
            if ($key === 'whatsapp_access_token' && $value === '') {
                continue;
            }
            $stmt = $this->db->prepare(
                "INSERT INTO whatsapp_settings (setting_key, setting_value, is_enabled) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP"
            );
            $stmt->execute([$key, $value]);
        }

        $this->jsonResponse(['success' => true, 'message' => 'Configuration WhatsApp enregistrée.']);
    }

    public function sendTest(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $payload = is_array($input) ? $input : $_POST;
        $phone = trim((string) ($payload['phone'] ?? ''));
        $message = trim((string) ($payload['message'] ?? ''));

        if ($phone === '' || $message === '') {
            $this->jsonResponse(['success' => false, 'message' => 'Numéro et message requis.'], 400);
            return;
        }

        $readinessError = $this->livePhoneReadinessError();
        if ($readinessError !== null) {
            $this->jsonResponse(['success' => false, 'message' => $readinessError], 422);
            return;
        }

        $result = $this->service->sendTest($phone, $message);
        if ($result['success']) {
            $this->jsonResponse(['success' => true, 'message' => $result['message'] ?? 'Test WhatsApp envoyé.']);
        }

        $this->jsonResponse(['success' => false, 'message' => $result['message'] ?? 'Échec du test WhatsApp.'], 500);
    }

    public function verifyMetaConnection(): void
    {
        $provider = new MetaCloudProvider();
        $result = $provider->verifyConnection();
        if (!$result['success']) {
            $this->jsonResponse(['success' => false, 'message' => $result['error'] ?? 'Vérification Meta échouée.'], 422);
            return;
        }

        $configuredPhone = \App\Services\WhatsApp\PhoneNumberNormalizer::normalize($provider->getBusinessPhoneNumber());
        $metaPhone = \App\Services\WhatsApp\PhoneNumberNormalizer::normalize((string) $result['display_phone_number']);
        $phoneMatches = $configuredPhone !== '' && $configuredPhone === $metaPhone;
        $this->jsonResponse([
            'success' => true,
            'message' => $phoneMatches ? 'Connexion Meta et numéro vérifiés.' : 'Connexion Meta valide, mais le numéro ne correspond pas à celui configuré.',
            'data' => [
                'phone_number_id' => $result['phone_number_id'],
                'display_phone_number' => $result['display_phone_number'],
                'verified_name' => $result['verified_name'],
                'configured_phone_number' => $provider->getBusinessPhoneNumber(),
                'phone_number_matches' => $phoneMatches,
            ],
        ]);
    }

    public function sendBulk(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $payload = is_array($input) ? $input : $_POST;
        $studentIds = $this->normalizeStudentIds($payload['student_ids'] ?? []);
        $templateName = trim((string) ($payload['template_name'] ?? WHATSAPP_TEMPLATE_NAME));
        $components = $payload['components'] ?? [];

        if ($studentIds === []) {
            $this->jsonResponse(['success' => false, 'message' => 'Sélectionne au moins un élève.'], 400);
            return;
        }
        if ($templateName === '') {
            $this->jsonResponse(['success' => false, 'message' => 'Le nom du modèle Meta est requis.'], 400);
            return;
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $templateName) || !is_array($components)) {
            $this->jsonResponse(['success' => false, 'message' => 'Le nom du modèle ou ses paramètres sont invalides.'], 400);
            return;
        }

        $parameterCount = 0;
        foreach ($components as $component) {
            if (!is_array($component) || (isset($component['parameters']) && !is_array($component['parameters']))) {
                $this->jsonResponse(['success' => false, 'message' => 'Les variables du modèle sont mal formatées.'], 400);
                return;
            }
            foreach ($component['parameters'] ?? [] as $parameter) {
                if (!is_array($parameter)) {
                    $this->jsonResponse(['success' => false, 'message' => 'Les paramètres du modèle sont mal formatés.'], 400);
                    return;
                }
                if (array_key_exists('text', $parameter) && (
                    !is_string($parameter['text'])
                    || trim($parameter['text']) === ''
                    || strlen($parameter['text']) > 1024
                )) {
                    $this->jsonResponse(['success' => false, 'message' => 'Chaque texte de variable doit contenir entre 1 et 1 024 caractères.'], 400);
                    return;
                }
                $parameterCount++;
            }
        }

        if (count($studentIds) > 500 || count($components) > 10 || $parameterCount > 10) {
            $this->jsonResponse(['success' => false, 'message' => 'Limite dépassée : 500 élèves et 10 paramètres de modèle maximum par opération.'], 400);
            return;
        }

        $readinessError = $this->livePhoneReadinessError();
        if ($readinessError !== null) {
            $this->jsonResponse(['success' => false, 'message' => $readinessError], 422);
            return;
        }

        $targetResult = $this->resolveTargetStudents(
            is_array($payload['filters'] ?? null) ? $payload['filters'] : [],
            $studentIds
        );
        if (isset($targetResult['error'])) {
            $this->jsonResponse(['success' => false, 'message' => $targetResult['error']], $targetResult['status'] ?? 400);
            return;
        }

        $recipients = [];
        foreach ($targetResult['students'] as $student) {
            foreach ([$student['parent_contact'] ?? '', $student['guardian_contact'] ?? ''] as $contact) {
                $phone = \App\Services\WhatsApp\PhoneNumberNormalizer::normalize((string) $contact);
                if ($phone !== '') {
                    $recipients[$student['id'] . ':' . $phone] = ['student_id' => (int) $student['id'], 'phone' => $phone];
                }
            }
        }
        if ($recipients === []) {
            $this->jsonResponse(['success' => false, 'message' => 'Aucun numéro de téléphone valide trouvé pour cette sélection.'], 400);
            return;
        }

        $result = $this->service->sendBulk(array_values($recipients), $templateName, $components);
        $summary = $result['sent'] . ' envoi(s) réel(s) réussi(s), '
            . $result['simulated'] . ' simulation(s), '
            . $result['failed'] . ' échec(s).';
        $this->jsonResponse([
            'success' => $result['failed'] === 0,
            'message' => $summary,
            'data' => $result,
        ]);
    }

    public function saveConsent(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $payload = is_array($input) ? $input : $_POST;
        $studentId = (int) ($payload['student_id'] ?? 0);
        $phone = trim((string) ($payload['phone'] ?? ''));
        $status = trim((string) ($payload['status'] ?? 'granted'));
        $source = trim((string) ($payload['source'] ?? 'manual'));

        if ($studentId <= 0 || $phone === '') {
            $this->jsonResponse(['success' => false, 'message' => 'Paramètres étudiant et téléphone requis.'], 400);
            return;
        }

        $normalized = \App\Services\WhatsApp\PhoneNumberNormalizer::normalize($phone);
        if ($normalized === '') {
            $this->jsonResponse(['success' => false, 'message' => 'Numéro WhatsApp invalide.'], 400);
            return;
        }

        if (!in_array($status, ['granted', 'denied', 'revoked', 'unknown'], true)) {
            $this->jsonResponse(['success' => false, 'message' => 'Statut de consentement invalide.'], 400);
            return;
        }

        $studentStmt = $this->db->prepare("SELECT parent_contact, guardian_contact FROM students WHERE id = ? LIMIT 1");
        $studentStmt->execute([$studentId]);
        $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
        if (!$student) {
            $this->jsonResponse(['success' => false, 'message' => 'Élève introuvable.'], 404);
            return;
        }
        $studentPhones = \App\Services\WhatsApp\PhoneNumberNormalizer::unique([
            (string) ($student['parent_contact'] ?? ''),
            (string) ($student['guardian_contact'] ?? ''),
        ]);
        if (!in_array($normalized, $studentPhones, true)) {
            $this->jsonResponse(['success' => false, 'message' => 'Ce numéro ne correspond pas aux contacts enregistrés pour cet élève.'], 400);
            return;
        }

        $this->service->saveConsent($studentId, $normalized, $status, $source);
        $this->jsonResponse(['success' => true, 'message' => 'Consentement WhatsApp enregistré.']);
    }

    public function logs(): void
    {
        $this->ensureWhatsAppTables();
        $limit = max(1, min(200, (int) ($_GET['limit'] ?? 50)));
        $stmt = $this->db->prepare("SELECT * FROM whatsapp_logs ORDER BY id DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $this->jsonResponse(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function livePhoneReadinessError(): ?string
    {
        $provider = new MetaCloudProvider();
        if ($provider->isTestMode()) {
            return null;
        }

        $verification = $provider->verifyConnection();
        if (empty($verification['success'])) {
            return $verification['error'] ?? 'Impossible de vérifier la configuration Meta.';
        }

        $configuredPhone = \App\Services\WhatsApp\PhoneNumberNormalizer::normalize($provider->getBusinessPhoneNumber());
        $metaPhone = \App\Services\WhatsApp\PhoneNumberNormalizer::normalize((string) ($verification['display_phone_number'] ?? ''));
        if ($configuredPhone === '' || $metaPhone !== $configuredPhone) {
            return 'Envoi bloqué : le numéro d’entreprise configuré ne correspond pas au numéro associé au Phone Number ID Meta.';
        }

        return null;
    }

    private function normalizeStudentIds($studentIds): array
    {
        if (!is_array($studentIds)) {
            $studentIds = array_filter(array_map('intval', explode(',', (string) $studentIds)));
        }

        return array_values(array_unique(array_filter(
            array_map('intval', $studentIds),
            static fn($id) => $id > 0
        )));
    }

    private function resolveTargetStudents(array $filters, array $requestedIds = [], string $query = ''): array
    {
        $yearId = (new AcademicYearService($this->db))->getActiveYearId();
        $teachingTypeId = (int) ($filters['teaching_type_id'] ?? 0);
        $classId = (int) ($filters['class_id'] ?? 0);
        $criterion = (string) ($filters['criterion'] ?? 'all');
        $allowedCriteria = ['all', 'unpaid_1', 'unpaid_2', 'unpaid_3', 'bulletin_available'];

        if ($yearId <= 0) {
            return ['error' => 'Aucune année scolaire active n’est configurée.', 'status' => 422];
        }
        if ($teachingTypeId <= 0 || !in_array($criterion, $allowedCriteria, true)) {
            return ['error' => 'Choisis un type d’enseignement et un critère de ciblage valides.'];
        }

        $classStmt = $this->db->prepare("
            SELECT DISTINCT c.id
            FROM classes c
            JOIN teaching_types tt ON tt.id = c.teaching_type_id AND tt.actif = 1
            JOIN enrollments e ON e.class_id = c.id AND e.academic_year_id = ?
            JOIN students s ON s.id = e.student_id
            WHERE c.status = 1
              AND c.teaching_type_id = ?
              AND s.actif = 1
              AND COALESCE(s.is_withdrawn, 0) = 0
              AND s.status = 'Inscrit'
              " . ($classId > 0 ? 'AND c.id = ?' : '') . "
            ORDER BY c.id
        ");
        $classParams = [$yearId, $teachingTypeId];
        if ($classId > 0) {
            $classParams[] = $classId;
        }
        $classStmt->execute($classParams);
        $classIds = array_map('intval', $classStmt->fetchAll(PDO::FETCH_COLUMN));
        if ($classIds === []) {
            return ['error' => 'Aucune classe active avec des élèves inscrits ne correspond à ces filtres.'];
        }

        $criterionStudentIds = [];
        if (str_starts_with($criterion, 'unpaid_')) {
            $installmentNumber = (int) substr($criterion, -1);
            $insolventModel = new InsolventStudent();
            foreach ($classIds as $eligibleClassId) {
                foreach ($insolventModel->getInsolventsForTranche($yearId, $eligibleClassId, $installmentNumber) as $student) {
                    $criterionStudentIds[] = (int) $student['student_id'];
                }
            }
            $criterionStudentIds = array_values(array_unique($criterionStudentIds));
            if ($criterionStudentIds === []) {
                return ['students' => [], 'truncated' => false];
            }
        }

        $classPlaceholders = implode(',', array_fill(0, count($classIds), '?'));
        $sql = "
            SELECT DISTINCT s.id, s.nom, s.prenom, e.class_id, c.nom AS class_name,
                   s.parent_contact, s.guardian_contact
            FROM enrollments e
            JOIN students s ON s.id = e.student_id
            JOIN classes c ON c.id = e.class_id AND c.status = 1
            JOIN teaching_types tt ON tt.id = c.teaching_type_id AND tt.actif = 1
            WHERE e.academic_year_id = ?
              AND c.id IN ($classPlaceholders)
              AND s.actif = 1
              AND COALESCE(s.is_withdrawn, 0) = 0
              AND s.status = 'Inscrit'
        ";
        $params = array_merge([$yearId], $classIds);

        if ($criterion === 'bulletin_available') {
            $sql .= " AND EXISTS (
                SELECT 1 FROM grades g
                WHERE g.student_id = s.id AND g.academic_year_id = e.academic_year_id
            )";
        } elseif ($criterionStudentIds !== []) {
            $studentPlaceholders = implode(',', array_fill(0, count($criterionStudentIds), '?'));
            $sql .= " AND s.id IN ($studentPlaceholders)";
            $params = array_merge($params, $criterionStudentIds);
        }

        if ($query !== '') {
            $term = '%' . $query . '%';
            $sql .= " AND (s.nom LIKE ? OR s.prenom LIKE ? OR CONCAT(s.prenom, ' ', s.nom) LIKE ? OR CAST(s.id AS CHAR) LIKE ?)";
            array_push($params, $term, $term, $term, $term);
        }

        $sql .= ' ORDER BY c.nom, s.nom, s.prenom LIMIT 501';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $truncated = count($students) > 500;
        if ($truncated) {
            $students = array_slice($students, 0, 500);
        }

        if ($requestedIds !== []) {
            $requestedIdMap = array_fill_keys($requestedIds, true);
            $matchedStudents = array_values(array_filter(
                $students,
                static fn($student) => isset($requestedIdMap[(int) $student['id']])
            ));
            if (count($matchedStudents) !== count($requestedIds)) {
                return ['error' => 'La sélection a changé ou contient des élèves hors des filtres actifs. Recharge les résultats avant l’envoi.'];
            }
            $students = $matchedStudents;
        }

        return ['students' => $students, 'truncated' => $truncated];
    }
}
