<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveActiveRestaurant
{
    /**
     * Resolve the restaurant the authenticated user is currently working in.
     *
     * The selection is persisted on the session and always validated against
     * the user's memberships, so it can never leak across tenants.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $restaurant = null;

        if ($user) {
            $activeId = $request->session()->get('active_restaurant_id');

            if ($activeId) {
                $restaurant = $user->restaurants()->whereKey($activeId)->first();
            }

            if (! $restaurant) {
                $restaurant = $user->restaurants()
                    ->orderBy('restaurants.id')
                    ->first();

                if ($restaurant) {
                    $request->session()->put('active_restaurant_id', $restaurant->getKey());
                }
            }
        }

        app()->instance('active_restaurant', $restaurant);

        return $next($request);
    }
}
