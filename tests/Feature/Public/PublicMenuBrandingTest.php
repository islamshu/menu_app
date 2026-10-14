<?php

namespace Tests\Feature\Public;

use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicMenuBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_exposes_theme_defaults_when_nothing_is_published(): void
    {
        $restaurant = Restaurant::factory()->create();

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Menu')
                ->where('branding.theme_key', config('branding.default_theme'))
                ->where(
                    'branding.primary_color',
                    config('branding.themes.'.config('branding.default_theme').'.defaults.primary_color'),
                )
                ->has('branding.heading_font')
                ->has('branding.body_font')
                ->has('branding.border_radius')
                ->has('branding.card_style')
                ->has('branding.logo_display_mode'));
    }

    public function test_menu_reflects_the_published_branding(): void
    {
        $restaurant = Restaurant::factory()->create();
        $restaurant->branding()->create([
            'published' => array_merge(config('branding.themes.atelier.defaults'), [
                'theme_key' => 'atelier',
                'primary_color' => '#b4552d',
            ]),
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('branding.theme_key', 'atelier')
                ->where('branding.primary_color', '#b4552d'));
    }

    public function test_unpublished_draft_branding_is_never_exposed(): void
    {
        $restaurant = Restaurant::factory()->create();
        $restaurant->branding()->create([
            'published' => config('branding.themes.noir-luxe.defaults'),
            'draft' => array_merge(config('branding.themes.pulse.defaults'), [
                'theme_key' => 'pulse',
            ]),
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('branding.theme_key', 'noir-luxe')
                ->where('branding.theme_key', fn ($key) => $key !== 'pulse'));
    }

    public function test_changing_the_theme_keeps_the_menu_url_stable(): void
    {
        $restaurant = Restaurant::factory()->create();

        $url = route('menu.show', $restaurant->slug);

        $restaurant->branding()->create([
            'published' => config('branding.themes.pulse.defaults'),
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('meta.canonical', $url));

        $this->assertSame($url, route('menu.show', $restaurant->slug));
    }

    public function test_branding_is_scoped_to_the_requested_restaurant(): void
    {
        $restaurant = Restaurant::factory()->create();
        $other = Restaurant::factory()->create();
        $other->branding()->create([
            'published' => config('branding.themes.pulse.defaults'),
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('branding.theme_key', 'noir-luxe')
                ->where('branding.theme_key', fn ($key) => $key !== 'pulse'));
    }
}
