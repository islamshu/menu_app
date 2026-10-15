<?php

namespace Tests\Feature;

use App\Services\Qr\QrImage;
use chillerlan\QRCode\QRCode;
use Tests\TestCase;

/**
 * Renders the QR code with the real server-side renderer and decodes the
 * resulting PNG with a ZXing-based reader to guarantee every preset (with and
 * without an embedded logo) remains scannable.
 */
class QrDecodeTest extends TestCase
{
    private const URL = 'https://example.com/m/demo-restaurant';

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function settings(array $overrides = []): array
    {
        return array_merge(config('qr.defaults'), $overrides);
    }

    private function decode(string $png): ?string
    {
        return (new QRCode)->readFromBlob($png)->data;
    }

    public function test_png_without_logo_decodes(): void
    {
        $png = app(QrImage::class)->png(self::URL, $this->settings())->getString();

        $this->assertSame(self::URL, $this->decode($png));
    }

    public function test_png_with_logo_decodes(): void
    {
        $logo = $this->makeLogo();

        try {
            $png = app(QrImage::class)
                ->png(self::URL, $this->settings(['logo_enabled' => true, 'logo_ratio' => 0.15]), $logo)
                ->getString();

            $this->assertSame(self::URL, $this->decode($png));
        } finally {
            @unlink($logo);
        }
    }

    public function test_svg_renders(): void
    {
        $svg = app(QrImage::class)->svg(self::URL, $this->settings(), null, 512)->getString();

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('<path', $svg);
    }

    public function test_every_preset_decodes_with_logo(): void
    {
        $logo = $this->makeLogo();
        $image = app(QrImage::class);

        try {
            foreach (array_keys(config('qr.presets')) as $key) {
                $settings = array_merge(
                    config("qr.presets.{$key}.defaults"),
                    ['logo_enabled' => true, 'logo_ratio' => config('qr.logo.max_ratio')],
                );

                $png = $image->png(self::URL, $settings, $logo)->getString();

                $this->assertSame(
                    self::URL,
                    $this->decode($png),
                    "Preset {$key} did not decode with an embedded logo.",
                );
            }
        } finally {
            @unlink($logo);
        }
    }

    private function makeLogo(): string
    {
        $img = imagecreatetruecolor(160, 160);
        imagefill($img, 0, 0, imagecolorallocate($img, 220, 40, 30));
        imagefilledellipse($img, 80, 80, 120, 120, imagecolorallocate($img, 255, 255, 255));

        $path = tempnam(sys_get_temp_dir(), 'qrlogo').'.png';
        imagepng($img, $path);
        imagedestroy($img);

        return $path;
    }
}
