<?php

namespace App\Http\Controllers\Restaurant\Concerns;

use App\Models\Restaurant;

trait ResolvesActiveRestaurant
{
    /**
     * The active restaurant for the current request, if any.
     */
    protected function activeRestaurant(): ?Restaurant
    {
        $restaurant = app()->bound('active_restaurant')
            ? app('active_restaurant')
            : null;

        return $restaurant instanceof Restaurant ? $restaurant : null;
    }

    /**
     * The active restaurant, or abort when none is resolved.
     *
     * This is the single source of truth for tenant scoping. The active
     * restaurant is always resolved from the authenticated user's
     * memberships by the tenant middleware — never from request input.
     */
    protected function restaurant(): Restaurant
    {
        $restaurant = $this->activeRestaurant();

        abort_unless($restaurant !== null, 403, 'لا يوجد مطعم نشط.');

        return $restaurant;
    }
}
