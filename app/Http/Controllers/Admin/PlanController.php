<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    /**
     * Every subscription plan with usage counts.
     */
    public function index(): Response
    {
        $plans = Plan::query()
            ->withCount('subscriptions')
            ->orderBy('price')
            ->get()
            ->map(fn (Plan $plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'price' => $plan->price,
                'billing_interval' => $plan->billing_interval->value,
                'billing_interval_label' => $plan->billing_interval->label(),
                'features' => $plan->features ?? [],
                'limits' => $plan->limits ?? [],
                'is_active' => $plan->is_active,
                'subscriptions_count' => $plan->subscriptions_count,
            ]);

        return Inertia::render('Admin/Plans/Index', [
            'plans' => $plans,
            'intervals' => [
                ['value' => 'monthly', 'label' => 'شهري'],
                ['value' => 'yearly', 'label' => 'سنوي'],
            ],
        ]);
    }

    /**
     * Create a new plan.
     */
    public function store(PlanRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Plan::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'price' => $data['price'],
            'billing_interval' => $data['billing_interval'],
            'features' => array_values($data['features'] ?? []),
            'limits' => $this->normalizeLimits($data['limits'] ?? []),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'تم إنشاء الباقة بنجاح.');
    }

    /**
     * Update an existing plan.
     */
    public function update(PlanRequest $request, Plan $plan): RedirectResponse
    {
        $data = $request->validated();

        $plan->fill([
            'name' => $data['name'],
            'price' => $data['price'],
            'billing_interval' => $data['billing_interval'],
            'features' => array_values($data['features'] ?? []),
            'limits' => $this->normalizeLimits($data['limits'] ?? []),
            'is_active' => $request->boolean('is_active', $plan->is_active),
        ])->save();

        return back()->with('success', 'تم تحديث الباقة.');
    }

    /**
     * Toggle a plan's availability.
     */
    public function toggle(Plan $plan): RedirectResponse
    {
        $plan->update(['is_active' => ! $plan->is_active]);

        return back();
    }

    /**
     * Delete a plan that has no attached subscriptions.
     */
    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->exists()) {
            return back()->with('error', 'لا يمكن حذف باقة مرتبطة باشتراكات.');
        }

        $plan->delete();

        return back()->with('success', 'تم حذف الباقة.');
    }

    /**
     * Build a collision-free slug for a plan name.
     */
    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'plan';
        $slug = $base;
        $suffix = 2;

        while (Plan::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @param  array<string, mixed>  $limits
     * @return array<string, int>
     */
    private function normalizeLimits(array $limits): array
    {
        $normalized = [];

        foreach (['restaurants', 'menu_items'] as $key) {
            if (isset($limits[$key]) && $limits[$key] !== '' && $limits[$key] !== null) {
                $normalized[$key] = (int) $limits[$key];
            }
        }

        return $normalized;
    }
}
