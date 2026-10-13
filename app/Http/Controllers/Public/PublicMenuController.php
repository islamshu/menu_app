<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Inertia\Inertia;
use Inertia\Response;

class PublicMenuController extends Controller
{
    /**
     * Display the public digital menu for an active restaurant.
     */
    public function __invoke(Restaurant $restaurant): Response
    {
        abort_unless($restaurant->isActive(), 404);

        return Inertia::render('Public/Menu', [
            'restaurant' => [
                'name' => $restaurant->name,
                'slug' => $restaurant->slug,
                'description' => $restaurant->description,
                'logo' => $restaurant->logo,
                'cover_image' => $restaurant->cover_image,
                'currency' => $restaurant->currency,
                'default_locale' => $restaurant->default_locale,
            ],
        ]);
    }
}
