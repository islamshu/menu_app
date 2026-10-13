<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Platform overview for super admins.
     */
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => $this->stats(),
            'subscriptionBreakdown' => $this->subscriptionBreakdown(),
            'recentRestaurants' => $this->recentRestaurants(),
            'recentUsers' => $this->recentUsers(),
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function stats(): array
    {
        return [
            'restaurants' => Restaurant::count(),
            'activeRestaurants' => Restaurant::where('status', RestaurantStatus::Active)->count(),
            'suspendedRestaurants' => Restaurant::where('status', RestaurantStatus::Suspended)->count(),
            'users' => User::count(),
            'superAdmins' => User::where('is_super_admin', true)->count(),
            'plans' => Plan::count(),
            'activePlans' => Plan::where('is_active', true)->count(),
            'subscriptions' => Subscription::count(),
            'activeSubscriptions' => Subscription::whereIn('status', [
                SubscriptionStatus::Active,
                SubscriptionStatus::Trialing,
            ])->count(),
            'categories' => MenuCategory::count(),
            'items' => MenuItem::count(),
        ];
    }

    /**
     * Subscription counts grouped by status.
     *
     * @return array<int, array{status: string, label: string, count: int}>
     */
    private function subscriptionBreakdown(): array
    {
        $counts = Subscription::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return collect(SubscriptionStatus::cases())
            ->map(fn (SubscriptionStatus $status) => [
                'status' => $status->value,
                'label' => $status->label(),
                'count' => (int) ($counts[$status->value] ?? 0),
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function recentRestaurants(): array
    {
        return Restaurant::query()
            ->withCount(['menuCategories', 'menuItems'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Restaurant $restaurant) => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'slug' => $restaurant->slug,
                'status' => $restaurant->status->value,
                'status_label' => $restaurant->status->label(),
                'categories_count' => $restaurant->menu_categories_count,
                'items_count' => $restaurant->menu_items_count,
                'created_at' => $restaurant->created_at?->toDateString(),
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function recentUsers(): array
    {
        return User::query()
            ->withCount('restaurants')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_super_admin' => $user->isSuperAdmin(),
                'restaurants_count' => $user->restaurants_count,
                'created_at' => $user->created_at?->toDateString(),
            ])
            ->all();
    }
}
