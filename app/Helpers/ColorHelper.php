<?php

namespace App\Helpers;

class ColorHelper
{
    /**
     * Convert a hex color to [r, g, b] (0–255).
     */
    public static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Convert [r, g, b] to a hex string.
     */
    public static function rgbToHex(int $r, int $g, int $b): string
    {
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Darken a hex color by $percent (0–100).
     */
    public static function darken(string $hex, int $percent): string
    {
        [$r, $g, $b] = self::hexToRgb($hex);
        $factor = 1 - ($percent / 100);
        return self::rgbToHex(
            (int) round($r * $factor),
            (int) round($g * $factor),
            (int) round($b * $factor),
        );
    }

    /**
     * Lighten a hex color by mixing toward white by $percent (0–100).
     */
    public static function lighten(string $hex, int $percent): string
    {
        [$r, $g, $b] = self::hexToRgb($hex);
        $factor = $percent / 100;
        return self::rgbToHex(
            (int) round($r + (255 - $r) * $factor),
            (int) round($g + (255 - $g) * $factor),
            (int) round($b + (255 - $b) * $factor),
        );
    }

    /**
     * Return '#ffffff' or '#1a1a1a' depending on which has better contrast with $hex.
     * Uses WCAG relative luminance formula.
     */
    public static function contrastColor(string $hex): string
    {
        [$r, $g, $b] = self::hexToRgb($hex);

        // Linearise
        $linearise = fn(int $c): float => ($c / 255 <= 0.03928)
            ? $c / 255 / 12.92
            : (($c / 255 + 0.055) / 1.055) ** 2.4;

        $L = 0.2126 * $linearise($r)
           + 0.7152 * $linearise($g)
           + 0.0722 * $linearise($b);

        // If luminance > 0.179, use dark text; otherwise use light text
        return $L > 0.179 ? '#1a1a1a' : '#FBF4F8';
    }
}
