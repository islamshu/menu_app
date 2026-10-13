<?php

namespace App\Policies;

use App\Enums\RestaurantRole;
use App\Models\Restaurant;
use App\Models\User;

class RestaurantPolicy
{
    /**
     * Super admins are granted everything via Gate::before.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Members may view a restaurant they belong to.
     */
    public function view(User $user, Restaurant $restaurant): bool
    {
        return $user->belongsToRestaurant($restaurant);
    }

    /**
     * Owners and managers may update restaurant settings.
     */
    public function update(User $user, Restaurant $restaurant): bool
    {
        return in_array($user->roleIn($restaurant), RestaurantRole::adminRoles(), true);
    }

    /**
     * Owners and managers may manage restaurant members.
     */
    public function manageMembers(User $user, Restaurant $restaurant): bool
    {
        return in_array($user->roleIn($restaurant), RestaurantRole::adminRoles(), true);
    }

    /**
     * Owners and managers may manage menu content (categories and items).
     */
    public function manageMenu(User $user, Restaurant $restaurant): bool
    {
        return in_array($user->roleIn($restaurant), RestaurantRole::adminRoles(), true);
    }

    /**
     * Only owners may delete a restaurant.
     */
    public function delete(User $user, Restaurant $restaurant): bool
    {
        return $user->roleIn($restaurant) === RestaurantRole::Owner;
    }
}
