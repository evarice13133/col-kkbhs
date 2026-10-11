<?php

namespace App\Services\WhatsApp;

class PhoneNumberNormalizer
{
    public static function normalize(string $value): string
    {
        $digits = preg_replace('/\D+/', '', trim((string) $value));
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '237')) {
            $digits = substr($digits, 3);
        }

        if (strlen($digits) === 9 && preg_match('/^[0-9]{9}$/', $digits)) {
            return '+237' . $digits;
        }

        if (strlen($digits) === 12 && preg_match('/^[0-9]{12}$/', $digits)) {
            return '+237' . substr($digits, 3);
        }

        if (preg_match('/^237[0-9]{9}$/', $digits)) {
            return '+237' . substr($digits, 3);
        }

        return '';
    }

    public static function unique(array $numbers): array
    {
        $normalized = [];
        foreach ($numbers as $number) {
            $formatted = self::normalize((string) $number);
            if ($formatted !== '') {
                $normalized[$formatted] = $formatted;
            }
        }

        return array_values($normalized);
    }
}
