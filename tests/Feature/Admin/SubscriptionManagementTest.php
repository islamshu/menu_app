<?php

namespace Tests\Feature\Admin;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SubscriptionManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    public function test_super_admin_can_list_subscriptions(): void
    {
        Subscription::factory()->count(2)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.subscriptions.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Subscriptions/Index')
                ->has('subscriptions.data', 2)
                ->has('statuses')
                ->has('restaurants')
                ->has('plans'));
    }

    public function test_super_admin_can_assign_a_subscription(): void
    {
        $restaurant = Restaurant::factory()->create();
        $plan = Plan::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.subscriptions.store'), [
                'restaurant_id' => $restaurant->id,
                'plan_id' => $plan->id,
                'status' => 'trialing',
                'starts_at' => '2026-01-01',
                'ends_at' => '2026-02-01',
            ])
            ->assertSessionHas('success');

        $subscription = Subscription::first();

        $this->assertNotNull($subscription);
        $this->assertSame($restaurant->id, $subscription->restaurant_id);
        $this->assertSame($plan->id, $subscription->plan_id);
        $this->assertSame(SubscriptionStatus::Trialing, $subscription->status);
    }

    public function test_assignment_requires_existing_restaurant_and_plan(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.subscriptions.store'), [
                'restaurant_id' => 999,
                'plan_id' => 999,
                'status' => 'active',
            ])
            ->assertSessionHasErrors(['restaurant_id', 'plan_id']);
    }

    public function test_super_admin_can_change_a_subscription_status(): void
    {
        $subscription = Subscription::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.subscriptions.status', $subscription), ['status' => 'canceled'])
            ->assertSessionHas('success');

        $this->assertSame(SubscriptionStatus::Canceled, $subscription->fresh()->status);
    }

    public function test_status_change_rejects_invalid_values(): void
    {
        $subscription = Subscription::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.subscriptions.status', $subscription), ['status' => 'nope'])
            ->assertSessionHasErrors('status');

        $this->assertSame(SubscriptionStatus::Active, $subscription->fresh()->status);
    }

    public function test_super_admin_can_delete_a_subscription(): void
    {
        $subscription = Subscription::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.subscriptions.destroy', $subscription))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('subscriptions', ['id' => $subscription->id]);
    }
}
