<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\RestaurantRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    /**
     * Restaurants this user is a member of (with their role).
     *
     * @return BelongsToMany<Restaurant, $this>
     */
    public function restaurants(): BelongsToMany
    {
        return $this->belongsToMany(Restaurant::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    /**
     * Whether the user is a member of the given restaurant.
     */
    public function belongsToRestaurant(Restaurant $restaurant): bool
    {
        if ($this->relationLoaded('restaurants')) {
            return $this->restaurants->contains('id', $restaurant->getKey());
        }

        return $this->restaurants()
            ->whereKey($restaurant->getKey())
            ->exists();
    }

    /**
     * The user's role within the given restaurant, if any.
     */
    public function roleIn(Restaurant $restaurant): ?RestaurantRole
    {
        if ($this->relationLoaded('restaurants')) {
            $role = $this->restaurants
                ->firstWhere('id', $restaurant->getKey())
                ?->pivot?->role;
        } else {
            $role = $this->restaurants()
                ->whereKey($restaurant->getKey())
                ->first()
                ?->pivot?->role;
        }

        return $role ? RestaurantRole::tryFrom($role) : null;
    }
}
