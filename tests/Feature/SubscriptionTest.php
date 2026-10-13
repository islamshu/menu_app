<?php

namespace Tests\Feature;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_subscription_belongs_to_a_restaurant_and_a_plan(): void
    {
        $restaurant = Restaurant::factory()->create();
        $plan = Plan::factory()->create();

        $subscription = Subscription::factory()->create([
            'restaurant_id' => $restaurant->id,
            'plan_id' => $plan->id,
        ]);

        $this->assertTrue($restaurant->subscriptions()->whereKey($subscription->id)->exists());
        $this->assertTrue($subscription->restaurant->is($restaurant));
        $this->assertTrue($subscription->plan->is($plan));
        $this->assertSame(SubscriptionStatus::Active, $subscription->status);
        $this->assertTrue($subscription->isUsable());
    }

    public function test_expired_subscriptions_are_not_usable(): void
    {
        $subscription = Subscription::factory()->expired()->create();

        $this->assertFalse($subscription->isUsable());
    }

    public function test_active_subscription_relation_prefers_usable_statuses(): void
    {
        $restaurant = Restaurant::factory()->create();

        Subscription::factory()->for($restaurant)->expired()->create();

        $active = Subscription::factory()->for($restaurant)->create([
            'status' => SubscriptionStatus::Active,
        ]);

        $this->assertTrue($restaurant->activeSubscription->is($active));
    }
}
