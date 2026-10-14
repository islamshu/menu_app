<?php

namespace Tests\Feature\Restaurant;

use App\Enums\RestaurantRole;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\Branding\PaletteExtractor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BrandingPaletteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Authenticate a member of the given restaurant as the session's active tenant.
     */
    private function actingAsRestaurantMember(Restaurant $restaurant, RestaurantRole $role = RestaurantRole::Owner): User
    {
        $user = User::factory()->create();
        $restaurant->users()->attach($user, ['role' => $role->value]);

        $this->actingAs($user)->withSession(['active_restaurant_id' => $restaurant->id]);

        return $user;
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    private function createSolidPng(int $red, int $green, int $blue, int $size = 64): string
    {
        $image = imagecreatetruecolor($size, $size);
        $fill = imagecolorallocate($image, $red, $green, $blue);
        imagefilledrectangle($image, 0, 0, $size, $size, $fill);

        $path = tempnam(sys_get_temp_dir(), 'palette').'.png';
        imagepng($image, $path);
        imagedestroy($image);

        return $path;
    }

    public function test_extractor_suggests_a_palette_from_a_solid_colour_logo(): void
    {
        $path = $this->createSolidPng(30, 111, 217);

        try {
            $palette = (new PaletteExtractor)->extract($path);
        } finally {
            @unlink($path);
        }

        $this->assertIsArray($palette);
        $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/i', $palette['primary']);
        $this->assertArrayHasKey('candidates', $palette);
        $this->assertArrayHasKey('monochrome', $palette);

        [$red, $green, $blue] = $this->rgb($palette['primary']);

        $this->assertGreaterThan($red, $blue, 'Blue dominant source should yield a blue primary.');
        $this->assertGreaterThan($green, $blue, 'Blue dominant source should yield a blue primary.');
    }

    public function test_extractor_ignores_fully_transparent_pixels(): void
    {
        $image = imagecreatetruecolor(64, 64);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefilledrectangle($image, 0, 0, 64, 64, $transparent);

        $red = imagecolorallocatealpha($image, 220, 40, 40, 0);
        imagefilledellipse($image, 32, 32, 28, 28, $red);

        $path = tempnam(sys_get_temp_dir(), 'palette').'.png';
        imagepng($image, $path);
        imagedestroy($image);

        try {
            $palette = (new PaletteExtractor)->extract($path);
        } finally {
            @unlink($path);
        }

        $this->assertIsArray($palette);

        [$red, $green, $blue] = $this->rgb($palette['primary']);

        $this->assertGreaterThan($green, $red);
        $this->assertGreaterThan($blue, $red);
    }

    public function test_extractor_returns_null_for_a_non_image_file(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'palette').'.txt';
        file_put_contents($path, 'not an image at all');

        try {
            $result = (new PaletteExtractor)->extract($path);
        } finally {
            @unlink($path);
        }

        $this->assertNull($result);
    }

    public function test_extractor_returns_null_for_a_missing_file(): void
    {
        $this->assertNull(
            (new PaletteExtractor)->extract(sys_get_temp_dir().'/missing-'.uniqid().'.png'),
        );
    }

    public function test_owner_can_extract_a_palette_from_an_uploaded_logo(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->post(route('branding.palette'), [
            'logo' => UploadedFile::fake()->image('logo.png', 80, 80),
        ])
            ->assertOk()
            ->assertJson(['available' => true])
            ->assertJsonStructure([
                'available',
                'palette' => ['primary', 'secondary', 'accent', 'background', 'text'],
            ]);
    }

    public function test_palette_endpoint_reports_unavailable_without_a_logo(): void
    {
        $restaurant = Restaurant::factory()->create(['logo' => null]);
        $this->actingAsRestaurantMember($restaurant);

        $this->post(route('branding.palette'))
            ->assertStatus(422)
            ->assertJson(['available' => false]);
    }

    public function test_staff_cannot_extract_a_palette(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant, RestaurantRole::Staff);

        $this->post(route('branding.palette'), [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])->assertForbidden();
    }
}
