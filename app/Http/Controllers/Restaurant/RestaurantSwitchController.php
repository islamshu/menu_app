<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class RestaurantSwitchController extends Controller
{
    /**
     * List the restaurants the current user belongs to.
     */
    public function index(Request $request): Response
    {
        $restaurants = $request->user()
            ->restaurants()
            ->orderBy('restaurants.name')
            ->get()
            ->map(fn (Restaurant $restaurant) => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'slug' => $restaurant->slug,
                'role' => $restaurant->pivot->role,
            ]);

        return Inertia::render('Restaurant/Restaurants/Index', [
            'restaurants' => $restaurants,
        ]);
    }

    /**
     * Switch the active restaurant for the current session.
     */
    public function store(Request $request, Restaurant $restaurant): RedirectResponse
    {
        Gate::authorize('view', $restaurant);

        $request->session()->put('active_restaurant_id', $restaurant->getKey());

        return redirect()->route('dashboard');
    }
}
