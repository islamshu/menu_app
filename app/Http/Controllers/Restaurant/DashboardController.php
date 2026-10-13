<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Landing screen for the restaurant workspace.
     */
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $restaurant = app()->bound('active_restaurant')
            ? app('active_restaurant')
            : null;

        return Inertia::render('Restaurant/Dashboard', [
            'restaurant' => $restaurant ? [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'slug' => $restaurant->slug,
            ] : null,
            'role' => $restaurant ? $user->roleIn($restaurant)?->value : null,
        ]);
    }
}
