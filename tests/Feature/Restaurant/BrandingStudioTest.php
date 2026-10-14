<?php

namespace Tests\Feature\Restaurant;

use App\Enums\RestaurantRole;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BrandingStudioTest extends TestCase
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
     * A complete, valid branding payload based on a theme's defaults.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge(
            config('branding.themes.noir-luxe.defaults'),
            $overrides,
        );
    }

    public function test_owner_can_view_the_studio(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->get(route('branding.edit'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Restaurant/Branding/Studio')
                ->has('themes', count(config('branding.themes')))
                ->has('options.fonts')
                ->has('options.radii')
                ->has('published')
                ->has('draft')
                ->where('draft.theme_key', config('branding.default_theme'))
                ->where('menu_url', route('menu.show', $restaurant->slug)));
    }

    public function test_manager_can_view_the_studio(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant, RestaurantRole::Manager);

        $this->get(route('branding.edit'))->assertOk();
    }

    public function test_staff_cannot_view_or_update_the_studio(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant, RestaurantRole::Staff);

        $this->get(route('branding.edit'))->assertForbidden();

        $this->put(route('branding.update'), $this->validPayload())
            ->assertForbidden();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('branding.edit'))->assertRedirect(route('login'));
    }

    public function test_saving_a_draft_does_not_change_the_published_settings(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->put(route('branding.update'), $this->validPayload([
            'theme_key' => 'pulse',
            'primary_color' => '#123456',
        ]))->assertSessionHas('success');

        $branding = $restaurant->branding()->first();

        $this->assertNotNull($branding);
        $this->assertSame('pulse', $branding->draft['theme_key']);
        $this->assertSame('#123456', $branding->draft['primary_color']);
        $this->assertNull($branding->published);
    }

    public function test_publishing_sets_the_published_settings_and_clears_the_draft(): void
    {
        $restaurant = Restaurant::factory()->create();
        $restaurant->branding()->create([
            'draft' => array_merge(config('branding.themes.pulse.defaults'), ['theme_key' => 'pulse']),
        ]);
        $this->actingAsRestaurantMember($restaurant);

        $this->post(route('branding.publish'), $this->validPayload([
            'theme_key' => 'atelier',
        ]))->assertSessionHas('success');

        $branding = $restaurant->branding()->first();

        $this->assertSame('atelier', $branding->published['theme_key']);
        $this->assertNull($branding->draft);
    }

    public function test_discarding_a_draft_reverts_to_the_published_settings(): void
    {
        $restaurant = Restaurant::factory()->create();
        $branding = $restaurant->branding()->create([
            'published' => config('branding.themes.noir-luxe.defaults'),
            'draft' => array_merge(config('branding.themes.pulse.defaults'), ['theme_key' => 'pulse']),
        ]);
        $this->actingAsRestaurantMember($restaurant);

        $this->delete(route('branding.discard'))->assertSessionHas('success');

        $this->assertNull($branding->fresh()->draft);
    }

    public function test_unknown_theme_keys_are_rejected(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->put(route('branding.update'), $this->validPayload([
            'theme_key' => 'not-a-theme',
        ]))->assertSessionHasErrors('theme_key');
    }

    public function test_invalid_colours_are_rejected(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->put(route('branding.update'), $this->validPayload([
            'primary_color' => 'red',
        ]))->assertSessionHasErrors('primary_color');
    }

    public function test_unknown_fonts_radii_and_styles_are_rejected(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->put(route('branding.update'), $this->validPayload([
            'heading_font' => 'comic-sans',
        ]))->assertSessionHasErrors('heading_font');

        $this->put(route('branding.update'), $this->validPayload([
            'border_radius' => 'huge',
        ]))->assertSessionHasErrors('border_radius');

        $this->put(route('branding.update'), $this->validPayload([
            'card_style' => 'sparkly',
        ]))->assertSessionHasErrors('card_style');
    }

    public function test_only_known_keys_are_persisted(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->put(route('branding.update'), $this->validPayload([
            'injected' => 'malicious',
        ]))->assertSessionHas('success');

        $draft = $restaurant->branding()->first()->draft;

        $this->assertArrayNotHasKey('injected', $draft);
    }

    public function test_branding_is_isolated_between_tenants(): void
    {
        $restaurant = Restaurant::factory()->create();
        $other = Restaurant::factory()->create();
        $otherBranding = $other->branding()->create([
            'published' => config('branding.themes.pulse.defaults'),
        ]);

        $this->actingAsRestaurantMember($restaurant);

        $this->put(route('branding.update'), $this->validPayload([
            'theme_key' => 'atelier',
        ]))->assertSessionHas('success');

        $this->assertSame('atelier', $restaurant->branding()->first()->draft['theme_key']);
        $this->assertSame('pulse', $otherBranding->fresh()->published['theme_key']);
        $this->assertNull($otherBranding->fresh()->draft);
    }
}
