<?php

namespace Database\Seeders;

use App\Enums\RestaurantRole;
use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Seed development accounts and a demo restaurant.
     */
    public function run(): void
    {
        $password = env('DEMO_PASSWORD', 'password');

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Platform Admin', 'password' => $password],
        );
        $admin->forceFill([
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ])->save();

        $owner = User::firstOrCreate(
            ['email' => 'owner@example.com'],
            ['name' => 'Demo Owner', 'password' => $password],
        );
        $owner->forceFill(['email_verified_at' => now()])->save();

        $restaurant = Restaurant::firstOrCreate(
            ['slug' => 'demo-restaurant'],
            [
                'name' => 'Demo Restaurant',
                'description' => 'A sample restaurant for local development.',
                'currency' => 'ILS',
                'default_locale' => 'ar',
                'timezone' => 'Asia/Jerusalem',
                'status' => 'active',
            ],
        );

        $restaurant->users()->syncWithoutDetaching([
            $owner->id => ['role' => RestaurantRole::Owner->value],
        ]);

        $starter = Plan::where('slug', 'starter')->first();

        if ($starter) {
            Subscription::firstOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'plan_id' => $starter->id,
                ],
                [
                    'status' => SubscriptionStatus::Active,
                    'starts_at' => now(),
                    'ends_at' => now()->addYear(),
                ],
            );
        }
    }
}
