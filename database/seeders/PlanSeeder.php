<?php

namespace Database\Seeders;

use App\Enums\BillingInterval;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Seed the platform subscription plans.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'price' => 0,
                'billing_interval' => BillingInterval::Monthly->value,
                'features' => ['qr_menu', 'single_restaurant'],
                'limits' => ['restaurants' => 1, 'menu_items' => 50],
                'is_active' => true,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'price' => 49,
                'billing_interval' => BillingInterval::Monthly->value,
                'features' => ['qr_menu', 'multi_language', 'custom_branding'],
                'limits' => ['restaurants' => 1, 'menu_items' => 500],
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'price' => 149,
                'billing_interval' => BillingInterval::Monthly->value,
                'features' => ['qr_menu', 'multi_language', 'custom_branding', 'priority_support'],
                'limits' => ['restaurants' => 5, 'menu_items' => null],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
