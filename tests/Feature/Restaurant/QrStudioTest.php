<?php

namespace Tests\Feature\Restaurant;

use App\Enums\RestaurantRole;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class QrStudioTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsRestaurantMember(Restaurant $restaurant, RestaurantRole $role = RestaurantRole::Owner): User
    {
        $user = User::factory()->create();
        $restaurant->users()->attach($user, ['role' => $role->value]);

        $this->actingAs($user)->withSession(['active_restaurant_id' => $restaurant->id]);

        return $user;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge(
            config('qr.defaults'),
            $overrides,
        );
    }

    public function test_owner_can_view_studio(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->get(route('qr.edit'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Restaurant/Qr/Studio')
                ->has('presets')
                ->has('options')
                ->has('published')
                ->has('draft')
                ->has('has_draft')
                ->where('menu_url', route('menu.show', $restaurant->slug)));
    }

    public function test_manager_can_view_studio(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant, RestaurantRole::Manager);

        $this->get(route('qr.edit'))->assertOk();
    }

    public function test_staff_cannot_access(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant, RestaurantRole::Staff);

        $this->get(route('qr.edit'))->assertForbidden();
        $this->put(route('qr.update'), $this->validPayload())->assertForbidden();
        $this->post(route('qr.publish'), $this->validPayload())->assertForbidden();
    }

    public function test_guest_redirects(): void
    {
        $this->get(route('qr.edit'))->assertRedirect(route('login'));
    }

    public function test_save_draft_does_not_change_published(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->put(route('qr.update'), $this->validPayload([
            'module_style' => 'dots',
            'foreground_color' => '#123456',
        ]))->assertSessionHas('success');

        $qr = $restaurant->qrCode()->first();
        $this->assertNotNull($qr);
        $this->assertSame('dots', $qr->draft['module_style']);
        $this->assertNull($qr->published);
    }

    public function test_publish_sets_published_and_clears_draft(): void
    {
        $restaurant = Restaurant::factory()->create();
        $restaurant->qrCode()->create([
            'draft' => array_merge(config('qr.presets.pulse.defaults'), ['preset_key' => 'pulse']),
        ]);
        $this->actingAsRestaurantMember($restaurant);

        $this->post(route('qr.publish'), $this->validPayload([
            'preset_key' => 'atelier',
            'module_style' => 'square',
            'eye_style' => 'square',
            'frame_style' => 'label',
            'error_level' => 'M',
        ]))->assertSessionHas('success');

        $qr = $restaurant->qrCode()->first();
        $this->assertSame('atelier', $qr->published['preset_key']);
        $this->assertNull($qr->draft);
    }

    public function test_discard_reverts(): void
    {
        $restaurant = Restaurant::factory()->create();
        $qr = $restaurant->qrCode()->create([
            'published' => config('qr.presets.noir-luxe.defaults'),
            'draft' => array_merge(config('qr.presets.pulse.defaults'), ['preset_key' => 'pulse']),
        ]);
        $this->actingAsRestaurantMember($restaurant);

        $this->delete(route('qr.discard'))->assertSessionHas('success');
        $this->assertNull($qr->fresh()->draft);
    }

    public function test_invalid_colors_rejected(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->put(route('qr.update'), $this->validPayload([
            'foreground_color' => 'red',
        ]))->assertSessionHasErrors('foreground_color');
    }

    public function test_unknown_styles_rejected(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->put(route('qr.update'), $this->validPayload([
            'module_style' => 'hexagons',
        ]))->assertSessionHasErrors('module_style');
    }

    public function test_quiet_zone_bounds(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->put(route('qr.update'), $this->validPayload([
            'quiet_zone' => 9,
        ]))->assertSessionHasErrors('quiet_zone');

        $this->put(route('qr.update'), $this->validPayload([
            'quiet_zone' => -1,
        ]))->assertSessionHasErrors('quiet_zone');
    }

    public function test_logo_ratio_bounds(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->put(route('qr.update'), $this->validPayload([
            'logo_ratio' => 0.5,
        ]))->assertSessionHasErrors('logo_ratio');

        $this->put(route('qr.update'), $this->validPayload([
            'logo_ratio' => 0.05,
        ]))->assertSessionHasErrors('logo_ratio');
    }

    public function test_frame_label_max_length(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->put(route('qr.update'), $this->validPayload([
            'frame_label' => str_repeat('a', 61),
        ]))->assertSessionHasErrors('frame_label');
    }

    public function test_only_known_keys_persisted(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->put(route('qr.update'), $this->validPayload([
            'injected' => 'bad',
        ]))->assertSessionHas('success');

        $qr = $restaurant->qrCode()->first();
        $this->assertArrayNotHasKey('injected', $qr->draft);
    }

    public function test_tenant_isolation(): void
    {
        $restaurant = Restaurant::factory()->create();
        $other = Restaurant::factory()->create();
        $otherQr = $other->qrCode()->create([
            'published' => array_merge(config('qr.presets.pulse.defaults'), ['preset_key' => 'pulse']),
        ]);

        $this->actingAsRestaurantMember($restaurant);
        $this->put(route('qr.update'), $this->validPayload([
            'preset_key' => 'atelier',
        ]))->assertSessionHas('success');

        $this->assertSame('atelier', $restaurant->qrCode()->first()->draft['preset_key']);
        $this->assertSame('pulse', $otherQr->fresh()->published['preset_key']);
    }
}
