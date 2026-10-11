<?php

namespace App\Services\WhatsApp;

interface WhatsAppProviderInterface
{
    public function sendText(string $to, string $message): array;

    public function sendTemplate(string $to, string $templateName, array $language = ['code' => 'fr'], array $components = []): array;

    public function isAvailable(): bool;
}
