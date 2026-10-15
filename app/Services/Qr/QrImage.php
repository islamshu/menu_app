<?php

namespace App\Services\Qr;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Storage;

/**
 * Server-side QR renderer built on endroid/qr-code (GD based, no Imagick).
 *
 * Only settings that the library can render losslessly are honoured:
 * foreground/background colours, error-correction level, quiet-zone margin
 * and an optional centre logo. Decorative module/eye/frame shapes are
 * intentionally not supported so every generated code stays scannable.
 */
class QrImage
{
    /**
     * Resolve a stored (public disk) path to an absolute local path.
     */
    public function logoPath(?string $path): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        $absolute = Storage::disk('public')->path($path);

        return is_file($absolute) ? $absolute : null;
    }

    public function png(string $data, array $settings, ?string $logoPath = null, ?int $size = null): ResultInterface
    {
        return $this->render($data, $settings, $logoPath, 'png', $size);
    }

    public function svg(string $data, array $settings, ?string $logoPath = null, ?int $size = null): ResultInterface
    {
        return $this->render($data, $settings, $logoPath, 'svg', $size);
    }

    public function render(string $data, array $settings, ?string $logoPath = null, string $format = 'png', ?int $size = null): ResultInterface
    {
        $format = $format === 'svg' ? 'svg' : 'png';
        $size = $this->size($size);

        $logo = ($settings['logo_enabled'] ?? false) && is_string($logoPath) && is_file($logoPath)
            ? $logoPath
            : null;

        $builder = (new Builder)
            ->writer($format === 'svg' ? new SvgWriter : new PngWriter)
            ->data($data)
            ->encoding(new Encoding('UTF-8'))
            // A centre logo always forces the highest recovery level so the
            // covered modules can be reconstructed by scanners.
            ->errorCorrectionLevel($logo !== null ? ErrorCorrectionLevel::High : $this->errorLevel($settings))
            ->size($size)
            ->margin($this->margin($size, $settings))
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->foregroundColor($this->color($settings['foreground_color'] ?? '#000000', '#000000'))
            ->backgroundColor($this->color($settings['background_color'] ?? '#ffffff', '#ffffff'));

        if ($logo !== null) {
            $builder
                ->logoPath($logo)
                ->logoResizeToWidth($this->logoWidth($size, $settings))
                ->logoPunchoutBackground(true);
        }

        return $builder->build();
    }

    private function size(?int $size): int
    {
        $size = $size ?? (int) config('qr.render.download_size', 1024);

        return max(128, min((int) config('qr.render.max_size', 2048), $size));
    }

    private function margin(int $size, array $settings): int
    {
        $quietZone = max(0, min(8, (int) ($settings['quiet_zone'] ?? 4)));

        return (int) round($size * 0.02 * $quietZone);
    }

    private function logoWidth(int $size, array $settings): int
    {
        $min = (float) config('qr.logo.min_ratio', 0.08);
        $max = (float) config('qr.logo.max_ratio', 0.15);
        $ratio = (float) ($settings['logo_ratio'] ?? 0.12);
        $ratio = max($min, min($max, $ratio));

        return max(16, (int) round($size * $ratio));
    }

    private function errorLevel(array $settings): ErrorCorrectionLevel
    {
        return match (strtoupper((string) ($settings['error_level'] ?? 'Q'))) {
            'L' => ErrorCorrectionLevel::Low,
            'M' => ErrorCorrectionLevel::Medium,
            'H' => ErrorCorrectionLevel::High,
            default => ErrorCorrectionLevel::Quartile,
        };
    }

    private function color(string $hex, string $fallback): Color
    {
        if (! preg_match('/^#?([0-9a-fA-F]{6})$/', $hex, $matches)) {
            $hex = $fallback;
            preg_match('/^#?([0-9a-fA-F]{6})$/', $hex, $matches);
        }

        $rgb = hexdec($matches[1]);

        return new Color(($rgb >> 16) & 0xFF, ($rgb >> 8) & 0xFF, $rgb & 0xFF);
    }
}
