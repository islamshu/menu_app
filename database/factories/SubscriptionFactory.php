<?php

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'plan_id' => Plan::factory(),
            'status' => SubscriptionStatus::Active,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ];
    }

    /**
     * Indicate that the subscription has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Expired,
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subMonth(),
        ]);
    }

    /**
     * Indicate that the subscription is trialing.
     */
    public function trialing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Trialing,
            'starts_at' => now(),
            'ends_at' => now()->addDays(14),
        ]);
    }
}
