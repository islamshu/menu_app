<?php

namespace App\Services\Branding;

use App\Support\Color;

/**
 * Extracts a harmonised colour palette from a restaurant logo using GD.
 *
 * The extraction runs locally (no external services) and is fully defensive:
 * every failure mode — missing GD, unreadable file, fully transparent or
 * monochrome artwork — returns a usable result or null so the caller can fall
 * back to the theme defaults.
 */
class PaletteExtractor
{
    /**
     * Alpha threshold (GD uses 0 = opaque, 127 = transparent) above which a
     * pixel is treated as effectively invisible and ignored.
     */
    private const ALPHA_CUTOFF = 100;

    /**
     * Sample canvas edge used to keep the pass fast for any input size.
     */
    private const SAMPLE_EDGE = 96;

    /**
     * Analyse an image on disk and return a palette suggestion.
     *
     * @return array{
     *     primary: string,
     *     secondary: string,
     *     accent: string,
     *     background: string,
     *     text: string,
     *     candidates: array<int, string>,
     *     monochrome: bool
     * }|null
     */
    public function extract(string $path): ?array
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        if (! is_file($path) || ! is_readable($path)) {
            return null;
        }

        $contents = @file_get_contents($path);

        if ($contents === false || $contents === '') {
            return null;
        }

        $source = @imagecreatefromstring($contents);

        if ($source === false) {
            return null;
        }

        try {
            $buckets = $this->histogram($source);
        } finally {
            imagedestroy($source);
        }

        if ($buckets === []) {
            return null;
        }

        return $this->palette($buckets);
    }

    /**
     * Build a weighted colour histogram from a downscaled sample of the image.
     *
     * @param  \GdImage  $source
     * @return array<int, array{r: int, g: int, b: int, count: int}>
     */
    protected function histogram($source): array
    {
        $width = imagesx($source);
        $height = imagesy($source);

        if ($width < 1 || $height < 1) {
            return [];
        }

        $sample = imagecreatetruecolor(self::SAMPLE_EDGE, self::SAMPLE_EDGE);
        imagealphablending($sample, false);
        imagesavealpha($sample, true);
        $transparent = imagecolorallocatealpha($sample, 0, 0, 0, 127);
        imagefilledrectangle($sample, 0, 0, self::SAMPLE_EDGE, self::SAMPLE_EDGE, $transparent);

        imagecopyresampled(
            $sample,
            $source,
            0,
            0,
            0,
            0,
            self::SAMPLE_EDGE,
            self::SAMPLE_EDGE,
            $width,
            $height,
        );

        $buckets = [];

        for ($x = 0; $x < self::SAMPLE_EDGE; $x++) {
            for ($y = 0; $y < self::SAMPLE_EDGE; $y++) {
                $rgba = imagecolorat($sample, $x, $y);

                $alpha = ($rgba >> 24) & 0x7F;

                if ($alpha >= self::ALPHA_CUTOFF) {
                    continue;
                }

                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;

                // Quantise into 24-wide bins to group perceptually close colours.
                $key = (intdiv($r, 24) << 8) | (intdiv($g, 24) << 4) | intdiv($b, 24);

                if (! isset($buckets[$key])) {
                    $buckets[$key] = ['r' => 0, 'g' => 0, 'b' => 0, 'count' => 0];
                }

                $buckets[$key]['r'] += $r;
                $buckets[$key]['g'] += $g;
                $buckets[$key]['b'] += $b;
                $buckets[$key]['count']++;
            }
        }

        imagedestroy($sample);

        return array_values(array_map(function (array $bucket): array {
            return [
                'r' => (int) round($bucket['r'] / $bucket['count']),
                'g' => (int) round($bucket['g'] / $bucket['count']),
                'b' => (int) round($bucket['b'] / $bucket['count']),
                'count' => $bucket['count'],
            ];
        }, $buckets));
    }

    /**
     * Turn a histogram into a harmonised palette.
     *
     * @param  array<int, array{r: int, g: int, b: int, count: int}>  $buckets
     * @return array{
     *     primary: string,
     *     secondary: string,
     *     accent: string,
     *     background: string,
     *     text: string,
     *     candidates: array<int, string>,
     *     monochrome: bool
     * }
     */
    protected function palette(array $buckets): array
    {
        $analysed = array_map(function (array $bucket): array {
            $rgb = [$bucket['r'], $bucket['g'], $bucket['b']];
            [$hue, $saturation] = Color::rgbToHsl($rgb);

            return [
                'rgb' => $rgb,
                'hex' => Color::rgbToHex($rgb),
                'hue' => $hue,
                'saturation' => $saturation,
                'luminance' => Color::luminance($rgb),
                'count' => $bucket['count'],
            ];
        }, $buckets);

        $total = array_sum(array_column($analysed, 'count')) ?: 1;

        $scored = array_map(function (array $entry) use ($total): array {
            $penalty = 1.0;

            if ($entry['luminance'] > 0.92 || $entry['luminance'] < 0.06) {
                $penalty = 0.15;
            }

            $entry['score'] = $entry['count'] * (0.35 + $entry['saturation']) * $penalty;
            $entry['weight'] = $entry['count'] / $total;

            return $entry;
        }, $analysed);

        usort($scored, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        $meaningful = array_values(array_filter(
            $scored,
            fn (array $entry) => $entry['saturation'] >= 0.12
                && $entry['luminance'] > 0.06
                && $entry['luminance'] < 0.92,
        ));

        $distinctHues = [];

        foreach ($meaningful as $entry) {
            $distinctHues[intdiv((int) round($entry['hue']), 30)] = true;
        }

        $monochrome = count($distinctHues) <= 1;
        $hasAccent = count($distinctHues) >= 2;

        $primary = $meaningful[0] ?? $this->usable($scored[0]);
        $primaryHex = $primary['hex'];

        $secondary = $this->pickByHue($meaningful, $primary['hue'], 25, 90)
            ?? $this->derive($primaryHex, -0.22, 0.9, 30);

        $accent = $hasAccent
            ? ($this->pickByHue($meaningful, $primary['hue'], 90, 180)
                ?? $this->pickByHue($meaningful, $primary['hue'], 30, 90)
                ?? $this->derive($primaryHex, 0.18, 1.1, 150))
            : $this->derive($primaryHex, 0.28, 1.2, 0);

        $background = Color::isLight($primaryHex)
            ? Color::mix($primaryHex, '#ffffff', 0.9)
            : Color::mix($primaryHex, '#0a0a0c', 0.86);

        if (Color::contrastRatio(Color::hexToRgb($primaryHex), Color::hexToRgb($background)) < 3) {
            $primaryHex = Color::isLight($background)
                ? Color::adjust($primaryHex, -0.2, 1.05)
                : Color::adjust($primaryHex, 0.2, 1.05);
        }

        $text = Color::bestTextColor($background);

        if (Color::contrastRatio(Color::hexToRgb($text), Color::hexToRgb($background)) < 4.5) {
            $text = Color::isLight($background) ? '#141414' : '#f7f5f0';
        }

        $candidates = [];

        foreach ($scored as $entry) {
            if (! $this->isDistinct($entry['hex'], $candidates)) {
                continue;
            }

            $candidates[] = $entry['hex'];

            if (count($candidates) >= 6) {
                break;
            }
        }

        return [
            'primary' => $primaryHex,
            'secondary' => $secondary,
            'accent' => $accent,
            'background' => $background,
            'text' => $text,
            'candidates' => $candidates,
            'monochrome' => $monochrome,
        ];
    }

    /**
     * Promote a low-saturation bucket into something that still works as a
     * brand colour (used when artwork is nearly grey / black / white).
     *
     * @param  array{rgb: array{0:int,1:int,2:int}, hex: string, luminance: float}  $entry
     * @return array{hex: string, hue: float}
     */
    protected function usable(array $entry): array
    {
        $hex = $entry['luminance'] > 0.5
            ? Color::adjust($entry['hex'], -0.3, 1.6)
            : Color::adjust($entry['hex'], 0.25, 1.4);

        [, $hue] = Color::rgbToHsl(Color::hexToRgb($hex) ?? [0, 0, 0]);

        return ['hex' => $hex, 'hue' => $hue];
    }

    /**
     * Find the strongest meaningful colour whose hue differs from the base.
     *
     * @param  array<int, array{hex: string, hue: float, score: float}>  $candidates
     */
    protected function pickByHue(array $candidates, float $hue, float $min, float $max): ?string
    {
        foreach ($candidates as $entry) {
            $distance = $this->hueDistance($hue, $entry['hue']);

            if ($distance >= $min && $distance <= $max) {
                return $entry['hex'];
            }
        }

        return null;
    }

    /**
     * Derive a variant of a base colour in HSL space.
     */
    protected function derive(string $hex, float $lightnessDelta, float $saturationFactor, float $hueShift): string
    {
        return Color::adjust($hex, $lightnessDelta, $saturationFactor, $hueShift);
    }

    /**
     * Circular distance between two hues in degrees (0-180).
     */
    protected function hueDistance(float $a, float $b): float
    {
        $diff = abs(fmod($a - $b, 360));

        return $diff > 180 ? 360 - $diff : $diff;
    }

    /**
     * Whether a colour is visually distinct from those already collected.
     *
     * @param  array<int, string>  $candidates
     */
    protected function isDistinct(string $hex, array $candidates): bool
    {
        $rgb = Color::hexToRgb($hex);

        if ($rgb === null) {
            return false;
        }

        foreach ($candidates as $existing) {
            $other = Color::hexToRgb($existing);

            if ($other === null) {
                continue;
            }

            $distance = abs($rgb[0] - $other[0]) + abs($rgb[1] - $other[1]) + abs($rgb[2] - $other[2]);

            if ($distance < 40) {
                return false;
            }
        }

        return true;
    }
}
