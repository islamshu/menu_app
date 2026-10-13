<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RestaurantStatus;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Services\ImageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RestaurantController extends Controller
{
    public function __construct(private readonly ImageManager $images) {}

    /**
     * Paginated, filterable list of every tenant on the platform.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        $restaurants = Restaurant::query()
            ->withCount(['menuCategories', 'menuItems', 'subscriptions', 'users'])
            ->with('activeSubscription.plan:id,name')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(
                in_array($status, array_column(RestaurantStatus::cases(), 'value'), true),
                fn ($query) => $query->where('status', $status),
            )
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Restaurant $restaurant) => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'slug' => $restaurant->slug,
                'email' => $restaurant->email,
                'currency' => $restaurant->currency,
                'status' => $restaurant->status->value,
                'status_label' => $restaurant->status->label(),
                'logo' => $this->images->url($restaurant->logo),
                'categories_count' => $restaurant->menu_categories_count,
                'items_count' => $restaurant->menu_items_count,
                'users_count' => $restaurant->users_count,
                'subscriptions_count' => $restaurant->subscriptions_count,
                'plan' => $restaurant->activeSubscription?->plan?->name,
                'created_at' => $restaurant->created_at?->toDateString(),
            ]);

        return Inertia::render('Admin/Restaurants/Index', [
            'restaurants' => $restaurants,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'statuses' => collect(RestaurantStatus::cases())
                ->map(fn (RestaurantStatus $status) => [
                    'value' => $status->value,
                    'label' => $status->label(),
                ])
                ->values(),
        ]);
    }

    /**
     * Full tenant profile: membership, usage and subscription history.
     */
    public function show(Restaurant $restaurant): Response
    {
        $restaurant->loadCount(['menuCategories', 'menuItems', 'subscriptions', 'users']);
        $restaurant->load('activeSubscription.plan');

        $members = $restaurant->users()
            ->orderBy('name')
            ->get(['users.id', 'users.name', 'users.email'])
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->pivot->role,
            ]);

        $subscriptions = $restaurant->subscriptions()
            ->with('plan:id,name')
            ->latest()
            ->get()
            ->map(fn (Subscription $subscription) => [
                'id' => $subscription->id,
                'plan' => $subscription->plan?->name,
                'status' => $subscription->status->value,
                'status_label' => $subscription->status->label(),
                'starts_at' => $subscription->starts_at?->toDateString(),
                'ends_at' => $subscription->ends_at?->toDateString(),
                'created_at' => $subscription->created_at?->toDateString(),
            ]);

        return Inertia::render('Admin/Restaurants/Show', [
            'restaurant' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'slug' => $restaurant->slug,
                'description' => $restaurant->description,
                'email' => $restaurant->email,
                'phone' => $restaurant->phone,
                'address' => $restaurant->address,
                'currency' => $restaurant->currency,
                'default_locale' => $restaurant->default_locale,
                'timezone' => $restaurant->timezone,
                'status' => $restaurant->status->value,
                'status_label' => $restaurant->status->label(),
                'logo' => $this->images->url($restaurant->logo),
                'cover_image' => $this->images->url($restaurant->cover_image),
                'categories_count' => $restaurant->menu_categories_count,
                'items_count' => $restaurant->menu_items_count,
                'users_count' => $restaurant->users_count,
                'subscriptions_count' => $restaurant->subscriptions_count,
                'created_at' => $restaurant->created_at?->toDateString(),
                'active_plan' => $restaurant->activeSubscription?->plan?->name,
                'menu_url' => route('menu.show', $restaurant->slug),
            ],
            'members' => $members,
            'subscriptions' => $subscriptions,
            'plans' => Plan::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($plan) => ['id' => $plan->id, 'name' => $plan->name]),
        ]);
    }

    /**
     * Activate or suspend a tenant.
     */
    public function updateStatus(Request $request, Restaurant $restaurant): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(RestaurantStatus::class)],
        ]);

        $restaurant->update(['status' => $data['status']]);

        return back()->with('success', 'تم تحديث حالة المطعم.');
    }

    /**
     * Permanently remove a tenant and all of its content.
     */
    public function destroy(Restaurant $restaurant): RedirectResponse
    {
        $restaurant->delete();

        return redirect()
            ->route('admin.restaurants.index')
            ->with('success', 'تم حذف المطعم وكل بياناته.');
    }
}
