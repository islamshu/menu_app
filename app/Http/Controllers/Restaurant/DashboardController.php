<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Restaurant\Concerns\ResolvesActiveRestaurant;
use App\Models\MenuItem;
use App\Services\ImageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    use ResolvesActiveRestaurant;

    public function __construct(private readonly ImageManager $images) {}

    /**
     * Landing screen for the restaurant workspace.
     */
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $restaurant = $this->activeRestaurant();

        if ($restaurant === null) {
            return Inertia::render('Restaurant/Dashboard', [
                'restaurant' => null,
                'role' => null,
                'stats' => null,
                'recentItems' => [],
                'subscription' => null,
                'restaurantStatus' => null,
            ]);
        }

        $subscription = $restaurant->activeSubscription;
        $subscription = $subscription?->loadMissing('plan');

        $recentItems = $restaurant->menuItems()
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'price', 'is_available', 'image', 'created_at'])
            ->map(fn (MenuItem $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
                'is_available' => $item->is_available,
                'image' => $this->images->url($item->image),
                'created_at' => $item->created_at?->toDateString(),
            ]);

        return Inertia::render('Restaurant/Dashboard', [
            'restaurant' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'slug' => $restaurant->slug,
            ],
            'role' => $user->roleIn($restaurant)?->value,
            'stats' => [
                'categories' => $restaurant->menuCategories()->count(),
                'items' => $restaurant->menuItems()->count(),
                'available' => $restaurant->menuItems()->where('is_available', true)->count(),
                'unavailable' => $restaurant->menuItems()->where('is_available', false)->count(),
                'featured' => $restaurant->menuItems()->where('is_featured', true)->count(),
            ],
            'recentItems' => $recentItems,
            'subscription' => $subscription ? [
                'plan' => $subscription->plan?->name,
                'status' => $subscription->status->value,
                'status_label' => $subscription->status->label(),
                'ends_at' => $subscription->ends_at?->toDateString(),
            ] : null,
            'restaurantStatus' => [
                'value' => $restaurant->status->value,
                'label' => $restaurant->status->label(),
            ],
        ]);
    }
}
