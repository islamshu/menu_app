<?php

namespace App\Http\Middleware;

use App\Services\Branding\BrandingSettings;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $activeRestaurant = app()->bound('active_restaurant')
            ? app('active_restaurant')
            : null;

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'activeRestaurant' => fn () => $activeRestaurant ? [
                'id' => $activeRestaurant->id,
                'name' => $activeRestaurant->name,
                'slug' => $activeRestaurant->slug,
                'role' => $request->user()?->roleIn($activeRestaurant)?->value,
            ] : null,
            'myRestaurants' => fn () => $request->user()
                ? $request->user()
                    ->restaurants()
                    ->orderBy('restaurants.name')
                    ->get(['restaurants.id', 'name', 'slug'])
                    ->map(fn ($restaurant) => [
                        'id' => $restaurant->id,
                        'name' => $restaurant->name,
                        'slug' => $restaurant->slug,
                        'role' => $restaurant->pivot->role,
                    ])
                    ->values()
                : [],
            'locales' => fn () => collect(config('locales.supported'))
                ->map(fn ($locale, $code) => ['code' => $code, 'label' => $locale['label']])
                ->values(),
            'brandingOptions' => fn () => app(BrandingSettings::class)->options(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
