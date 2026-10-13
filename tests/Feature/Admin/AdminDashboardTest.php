<?php

namespace Tests\Feature\Admin;

use App\Models\MenuItem;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_platform_overview(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $restaurants = Restaurant::factory()->count(3)->create();
        $plans = Plan::factory()->count(2)->create();
        Subscription::factory()->count(2)->create([
            'restaurant_id' => $restaurants->first()->id,
            'plan_id' => $plans->first()->id,
        ]);
        MenuItem::factory()->for($restaurants->first())->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard')
                ->where('stats.restaurants', 3)
                ->where('stats.plans', 2)
                ->where('stats.subscriptions', 2)
                ->where('stats.users', 1)
                ->has('subscriptionBreakdown')
                ->has('recentRestaurants', 3)
                ->has('recentUsers', 1));
    }
}
