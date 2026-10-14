<?php

namespace App\Support;

/**
 * Small, dependency-free colour helper used by the smart branding pipeline.
 *
 * All operations work on 6-digit hex strings and are intentionally side-effect
 * free so they can be reused by colour extraction, contrast checks and the
 * theme-default derivation without pulling in a third-party library.
 */
class Color
{
    /**
     * Convert a hex colour into an [r, g, b] tuple (0-255).
     *
     * @return array{0: int, 1: int, 2: int}|null
     */
    public static function hexToRgb(string $hex): ?array
    {
        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return null;
        }

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    public static function rgbToHex(array $rgb): string
    {
        return sprintf('#%02x%02x%02x', ...array_map(
            fn (int $channel) => max(0, min(255, $channel)),
            $rgb,
        ));
    }

    /**
     * Relative luminance (WCAG 2.1) for an [r, g, b] tuple, clamped to [0, 1].
     *
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    public static function luminance(array $rgb): float
    {
        $channels = array_map(function (int $channel): float {
            $value = $channel / 255;

            return $value <= 0.03928
                ? $value / 12.92
                : (($value + 0.055) / 1.055) ** 2.4;
        }, $rgb);

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }

    /**
     * WCAG contrast ratio between two colours (1-21).
     *
     * @param  array{0: int, 1: int, 2: int}  $a
     * @param  array{0: int, 1: int, 2: int}  $b
     */
    public static function contrastRatio(array $a, array $b): float
    {
        $l1 = self::luminance($a);
        $l2 = self::luminance($b);

        $light = max($l1, $l2);
        $dark = min($l1, $l2);

        return ($light + 0.05) / ($dark + 0.05);
    }

    /**
     * Pick a readable foreground (near-black or near-white) for a background.
     */
    public static function bestTextColor(string $hex): string
    {
        $rgb = self::hexToRgb($hex) ?? [255, 255, 255];

        $light = [248, 246, 241];
        $dark = [17, 17, 20];

        return self::contrastRatio($rgb, $light) >= self::contrastRatio($rgb, $dark)
            ? self::rgbToHex($light)
            : self::rgbToHex($dark);
    }

    /**
     * @return array{0: float, 1: float, 2: float}
     */
    public static function rgbToHsl(array $rgb): array
    {
        [$r, $g, $b] = array_map(fn (int $c): float => $c / 255, $rgb);

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            return [0.0, 0.0, $l];
        }

        $delta = $max - $min;
        $s = $l > 0.5 ? $delta / (2 - $max - $min) : $delta / ($max + $min);

        $h = match ($max) {
            $r => ($g - $b) / $delta + ($g < $b ? 6 : 0),
            $g => ($b - $r) / $delta + 2,
            default => ($r - $g) / $delta + 4,
        };

        return [$h * 60, $s, $l];
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    public static function hslToRgb(float $h, float $s, float $l): array
    {
        $h = fmod(($h % 360 + 360), 360) / 360;

        if ($s <= 0) {
            $value = (int) round($l * 255);

            return [$value, $value, $value];
        }

        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;

        $channels = array_map(function (float $t) use ($p, $q): float {
            if ($t < 0) {
                $t += 1;
            }
            if ($t > 1) {
                $t -= 1;
            }
            if ($t < 1 / 6) {
                return $p + ($q - $p) * 6 * $t;
            }
            if ($t < 1 / 2) {
                return $q;
            }
            if ($t < 2 / 3) {
                return $p + ($q - $p) * (2 / 3 - $t) * 6;
            }

            return $p;
        }, [$h + 1 / 3, $h, $h - 1 / 3]);

        return array_map(fn (float $c): int => (int) round($c * 255), $channels);
    }

    /**
     * Shift a colour in HSL space. Luminosity delta and hue are absolute while
     * saturation is a multiplier (1 keeps the original saturation).
     */
    public static function adjust(
        string $hex,
        float $lightnessDelta = 0.0,
        float $saturationFactor = 1.0,
        float $hueShift = 0.0,
    ): string {
        $rgb = self::hexToRgb($hex);

        if ($rgb === null) {
            return $hex;
        }

        [$h, $s, $l] = self::rgbToHsl($rgb);

        $l = max(0.0, min(1.0, $l + $lightnessDelta));
        $s = max(0.0, min(1.0, $s * $saturationFactor));

        return self::rgbToHex(self::hslToRgb($h + $hueShift, $s, $l));
    }

    /**
     * Linear blend between two colours; $t = 0 returns $a, $t = 1 returns $b.
     */
    public static function mix(string $a, string $b, float $t): string
    {
        $rgbA = self::hexToRgb($a);
        $rgbB = self::hexToRgb($b);

        if ($rgbA === null || $rgbB === null) {
            return $a;
        }

        $t = max(0.0, min(1.0, $t));

        return self::rgbToHex([
            (int) round($rgbA[0] + ($rgbB[0] - $rgbA[0]) * $t),
            (int) round($rgbA[1] + ($rgbB[1] - $rgbA[1]) * $t),
            (int) round($rgbA[2] + ($rgbB[2] - $rgbA[2]) * $t),
        ]);
    }

    /**
     * Whether a colour reads as "light" (useful to derive surface tints).
     */
    public static function isLight(string $hex): bool
    {
        $rgb = self::hexToRgb($hex) ?? [255, 255, 255];

        return self::luminance($rgb) > 0.5;
    }
}
