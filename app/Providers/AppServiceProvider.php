<?php

namespace App\Providers;

use App\Models\Restaurant;
use App\Models\User;
use App\Policies\RestaurantPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Gate::policy(Restaurant::class, RestaurantPolicy::class);

        Gate::before(function (User $user): ?bool {
            return $user->isSuperAdmin() ? true : null;
        });
    }
}
