<?php

namespace App\Services;

final class AccessCardPalette
{
    private const FLAG_COLORS = [
        'green' => '#007A5E',
        'red' => '#CE1126',
        'yellow' => '#FCD116',
    ];

    private const DEFAULTS = [
        'background' => '#FFF2BF',
        'accent' => '#795600',
        'border' => '#8A6A1D',
        'text' => '#29230F',
        'muted' => '#625936',
        'tranche1' => '#FFEDB0',
        'tranche2' => '#F9E3A0',
        'tranche3' => '#F3D781',
    ];

    private const CSS_VARIABLES = [
        'background' => '--card-background',
        'accent' => '--card-accent',
        'border' => '--card-border',
        'text' => '--card-text',
        'muted' => '--card-muted',
        'tranche1' => '--card-tranche-1',
        'tranche2' => '--card-tranche-2',
        'tranche3' => '--card-tranche-3',
    ];

    public static function defaults(): array
    {
        return self::DEFAULTS;
    }

    public static function fromArray($values): array
    {
        if (!is_array($values)) {
            return self::DEFAULTS;
        }

        $palette = self::DEFAULTS;
        foreach (self::DEFAULTS as $key => $default) {
            $value = $values[$key] ?? null;
            if (is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
                $palette[$key] = strtoupper($value);
            }
        }

        return $palette;
    }

    public static function cssVariables(array $palette): string
    {
        $styles = [];
        foreach (self::CSS_VARIABLES as $key => $variable) {
            $color = $palette[$key] ?? self::DEFAULTS[$key];
            $styles[] = $variable . ':' . $color;
        }
        foreach (self::FLAG_COLORS as $color => $value) {
            $styles[] = '--flag-' . $color . ':' . $value;
        }

        return implode(';', $styles);
    }
}