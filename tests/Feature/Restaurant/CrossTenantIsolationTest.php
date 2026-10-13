<?php

namespace Tests\Feature\Restaurant;

use App\Enums\RestaurantRole;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_cannot_switch_into_a_restaurant_they_do_not_belong_to(): void
    {
        $restaurantA = Restaurant::factory()->create();
        $restaurantB = Restaurant::factory()->create();
        $user = User::factory()->create();
        $restaurantA->users()->attach($user, ['role' => RestaurantRole::Owner->value]);

        $this->actingAs($user)
            ->post(route('restaurants.switch', $restaurantB))
            ->assertForbidden();

        $this->assertNotEquals($restaurantB->id, session('active_restaurant_id'));
    }

    public function test_policies_deny_access_to_other_tenants(): void
    {
        $restaurantA = Restaurant::factory()->create();
        $restaurantB = Restaurant::factory()->create();
        $user = User::factory()->create();
        $restaurantA->users()->attach($user, ['role' => RestaurantRole::Owner->value]);

        $this->assertTrue($user->can('view', $restaurantA));
        $this->assertTrue($user->can('update', $restaurantA));
        $this->assertFalse($user->can('view', $restaurantB));
        $this->assertFalse($user->can('update', $restaurantB));
    }

    public function test_staff_cannot_update_restaurant_settings(): void
    {
        $restaurant = Restaurant::factory()->create();
        $staff = User::factory()->create();
        $restaurant->users()->attach($staff, ['role' => RestaurantRole::Staff->value]);

        $this->assertTrue($staff->can('view', $restaurant));
        $this->assertFalse($staff->can('update', $restaurant));
        $this->assertFalse($staff->can('delete', $restaurant));
    }

    public function test_super_admins_bypass_restaurant_policies(): void
    {
        $restaurant = Restaurant::factory()->create();
        $admin = User::factory()->superAdmin()->create();

        $this->assertTrue($admin->can('view', $restaurant));
        $this->assertTrue($admin->can('update', $restaurant));
        $this->assertTrue($admin->can('delete', $restaurant));
    }
}
