<?php

namespace Tests\Feature\Admin;

use App\Enums\RestaurantRole;
use App\Enums\RestaurantStatus;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RestaurantManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    public function test_super_admin_can_list_restaurants(): void
    {
        Restaurant::factory()->count(2)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.restaurants.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Restaurants/Index')
                ->has('restaurants.data', 2)
                ->has('statuses'));
    }

    public function test_restaurants_can_be_searched_and_filtered(): void
    {
        Restaurant::factory()->create(['name' => 'Pizza Palace']);
        Restaurant::factory()->suspended()->create(['name' => 'Sushi Spot']);

        // Search by name.
        $this->actingAs($this->admin())
            ->get(route('admin.restaurants.index', ['search' => 'Pizza']))
            ->assertInertia(fn (Assert $page) => $page->has('restaurants.data', 1));

        // Filter by status.
        $this->actingAs($this->admin())
            ->get(route('admin.restaurants.index', ['status' => 'suspended']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('restaurants.data', 1)
                ->where('restaurants.data.0.name', 'Sushi Spot'));
    }

    public function test_super_admin_can_view_a_restaurant_profile(): void
    {
        $restaurant = Restaurant::factory()->create();
        $owner = User::factory()->create();
        $restaurant->users()->attach($owner, ['role' => RestaurantRole::Owner->value]);
        Subscription::factory()->for($restaurant)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.restaurants.show', $restaurant))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Restaurants/Show')
                ->where('restaurant.id', $restaurant->id)
                ->has('members', 1)
                ->has('subscriptions', 1)
                ->has('plans'));
    }

    public function test_super_admin_can_suspend_and_activate_a_restaurant(): void
    {
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.restaurants.status', $restaurant), ['status' => 'suspended'])
            ->assertSessionHas('success');

        $this->assertSame(RestaurantStatus::Suspended, $restaurant->fresh()->status);

        $this->actingAs($this->admin())
            ->patch(route('admin.restaurants.status', $restaurant), ['status' => 'active'])
            ->assertSessionHas('success');

        $this->assertSame(RestaurantStatus::Active, $restaurant->fresh()->status);
    }

    public function test_status_update_rejects_invalid_values(): void
    {
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.restaurants.status', $restaurant), ['status' => 'bogus'])
            ->assertSessionHasErrors('status');

        $this->assertSame(RestaurantStatus::Active, $restaurant->fresh()->status);
    }

    public function test_super_admin_can_delete_a_restaurant(): void
    {
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.restaurants.destroy', $restaurant))
            ->assertRedirect(route('admin.restaurants.index'));

        $this->assertDatabaseMissing('restaurants', ['id' => $restaurant->id]);
    }

    public function test_plan_list_for_assignment_only_contains_active_plans(): void
    {
        Plan::factory()->create(['name' => 'Active Plan']);
        Plan::factory()->inactive()->create(['name' => 'Hidden Plan']);
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.restaurants.show', $restaurant))
            ->assertInertia(fn (Assert $page) => $page->has('plans', 1));
    }
}
