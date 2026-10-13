<?php

namespace Tests\Feature\Restaurant;

use App\Enums\RestaurantRole;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RestaurantAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_the_workspace(): void
    {
        $this->get('/app')->assertRedirect('/login');
    }

    public function test_members_can_access_the_workspace(): void
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $restaurant->users()->attach($user, ['role' => RestaurantRole::Owner->value]);

        $this->actingAs($user)
            ->get('/app')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Restaurant/Dashboard')
                ->where('restaurant.id', $restaurant->id));
    }

    public function test_users_without_membership_see_an_empty_workspace(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Restaurant/Dashboard')
                ->where('restaurant', null));
    }

    public function test_super_admins_are_redirected_to_the_admin_area(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get('/app')
            ->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_members_can_switch_into_a_restaurant(): void
    {
        $restaurant = Restaurant::factory()->create();
        $member = User::factory()->create();
        $restaurant->users()->attach($member, ['role' => RestaurantRole::Staff->value]);

        $this->actingAs($member)
            ->post(route('restaurants.switch', $restaurant))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertEquals($restaurant->id, session('active_restaurant_id'));
    }
}
