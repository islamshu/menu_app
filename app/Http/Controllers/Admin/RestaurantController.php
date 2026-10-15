<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RestaurantRole;
use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use App\Services\ImageManager;
use App\Services\Qr\QrImage;
use App\Services\Qr\QrSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RestaurantController extends Controller
{
    /**
     * @var array<int, array{code: string, label: string}>
     */
    private const CURRENCIES = [
        ['code' => 'ILS', 'label' => 'شيكل إسرائيلي (₪)'],
        ['code' => 'USD', 'label' => 'دولار أمريكي ($)'],
        ['code' => 'EUR', 'label' => 'يورو (€)'],
        ['code' => 'GBP', 'label' => 'جنيه إسترليني (£)'],
        ['code' => 'SAR', 'label' => 'ريال سعودي (ر.س)'],
        ['code' => 'AED', 'label' => 'درهم إماراتي (د.إ)'],
        ['code' => 'EGP', 'label' => 'جنيه مصري (ج.م)'],
        ['code' => 'JOD', 'label' => 'دينار أردني (د.أ)'],
        ['code' => 'KWD', 'label' => 'دينار كويتي (د.ك)'],
        ['code' => 'QAR', 'label' => 'ريال قطري (ر.ق)'],
        ['code' => 'TRY', 'label' => 'ليرة تركية (₺)'],
    ];

    public function __construct(
        private readonly ImageManager $images,
        private readonly QrSettings $qrSettings,
        private readonly QrImage $qrImage,
    ) {}

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
                ->map(fn (RestaurantStatus $case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                ])
                ->values(),
            'plans' => Plan::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Plan $plan) => ['id' => $plan->id, 'name' => $plan->name]),
            'ownerOptions' => User::query()
                ->orderBy('name')
                ->get(['id', 'name', 'email'])
                ->map(fn (User $user) => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email]),
            'currencies' => self::CURRENCIES,
            'locales' => collect(config('locales.supported'))
                ->map(fn ($locale, $code) => ['code' => $code, 'label' => $locale['label']])
                ->values(),
            'timezones' => timezone_identifiers_list(),
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
                'qr_image' => $this->qrDataUri($restaurant),
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
     * Render the restaurant's published QR settings as a PNG data URI for the
     * admin profile widget.
     */
    private function qrDataUri(Restaurant $restaurant): string
    {
        return $this->qrImage
            ->png(
                route('menu.show', $restaurant->slug),
                $this->qrSettings->resolve($restaurant),
                $this->qrImage->logoPath($restaurant->logo),
                (int) config('qr.render.preview_size', 512),
            )
            ->getDataUri();
    }

    /**
     * Create a new tenant from the super admin area.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', 'unique:restaurants,slug'],
            'description' => ['nullable', 'string', 'max:2000'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3'],
            'default_locale' => ['required', 'string', 'max:5'],
            'timezone' => ['required', 'string', 'max:255', 'timezone'],
            'status' => ['required', Rule::enum(RestaurantStatus::class)],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
            'plan_id' => ['nullable', 'integer', 'exists:plans,id'],
        ]);

        $email = $data['email'] ?? null;

        if (empty($email) && ! empty($data['owner_id'])) {
            $email = User::whereKey($data['owner_id'])->value('email');
        }

        $restaurant = Restaurant::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['slug'] ?? $data['name']),
            'description' => $data['description'] ?? null,
            'email' => $email,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'currency' => strtoupper($data['currency']),
            'default_locale' => $data['default_locale'],
            'timezone' => $data['timezone'],
            'status' => $data['status'],
        ]);

        if (! empty($data['owner_id'])) {
            $restaurant->users()->syncWithoutDetaching([
                $data['owner_id'] => ['role' => RestaurantRole::Owner->value],
            ]);
        }

        if (! empty($data['plan_id'])) {
            Subscription::create([
                'restaurant_id' => $restaurant->id,
                'plan_id' => $data['plan_id'],
                'status' => SubscriptionStatus::Active,
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
            ]);
        }

        return redirect()
            ->route('admin.restaurants.show', $restaurant)
            ->with('success', 'تم إنشاء المطعم.');
    }

    /**
     * Build a unique slug from the given source.
     */
    private function uniqueSlug(string $source): string
    {
        $base = Str::slug($source) ?: 'restaurant';
        $slug = $base;
        $suffix = 2;

        while (Restaurant::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
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
