<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubscriptionRequest;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    /**
     * Every tenant subscription with its restaurant and plan.
     */
    public function index(Request $request): Response
    {
        $status = (string) $request->query('status', '');

        $subscriptions = Subscription::query()
            ->with(['restaurant:id,name,slug', 'plan:id,name'])
            ->when(
                in_array($status, array_column(SubscriptionStatus::cases(), 'value'), true),
                fn ($query) => $query->where('status', $status),
            )
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Subscription $subscription) => [
                'id' => $subscription->id,
                'restaurant' => $subscription->restaurant ? [
                    'id' => $subscription->restaurant->id,
                    'name' => $subscription->restaurant->name,
                    'slug' => $subscription->restaurant->slug,
                ] : null,
                'plan' => $subscription->plan?->name,
                'status' => $subscription->status->value,
                'status_label' => $subscription->status->label(),
                'starts_at' => $subscription->starts_at?->toDateString(),
                'ends_at' => $subscription->ends_at?->toDateString(),
                'created_at' => $subscription->created_at?->toDateString(),
            ]);

        return Inertia::render('Admin/Subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'filters' => [
                'status' => $status,
            ],
            'statuses' => collect(SubscriptionStatus::cases())
                ->map(fn (SubscriptionStatus $status) => [
                    'value' => $status->value,
                    'label' => $status->label(),
                ])
                ->values(),
            'restaurants' => Restaurant::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Restaurant $restaurant) => [
                    'id' => $restaurant->id,
                    'name' => $restaurant->name,
                ]),
            'plans' => Plan::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Plan $plan) => ['id' => $plan->id, 'name' => $plan->name]),
        ]);
    }

    /**
     * Assign a plan to a restaurant.
     */
    public function store(StoreSubscriptionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Subscription::create([
            'restaurant_id' => $data['restaurant_id'],
            'plan_id' => $data['plan_id'],
            'status' => $data['status'],
            'starts_at' => $data['starts_at'] ?? now(),
            'ends_at' => $data['ends_at'] ?? null,
        ]);

        return back()->with('success', 'تم إسناد الاشتراك بنجاح.');
    }

    /**
     * Change a subscription's status.
     */
    public function updateStatus(Request $request, Subscription $subscription): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(SubscriptionStatus::class)],
        ]);

        $subscription->update(['status' => $data['status']]);

        return back()->with('success', 'تم تحديث حالة الاشتراك.');
    }

    /**
     * Remove a subscription record.
     */
    public function destroy(Subscription $subscription): RedirectResponse
    {
        $subscription->delete();

        return back()->with('success', 'تم حذف الاشتراك.');
    }
}
