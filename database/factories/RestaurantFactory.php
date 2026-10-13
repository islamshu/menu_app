<?php

namespace Database\Factories;

use App\Enums\RestaurantStatus;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Restaurant>
 */
class RestaurantFactory extends Factory
{
    protected $model = Restaurant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'description' => fake()->sentence(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->address(),
            'currency' => 'ILS',
            'default_locale' => 'ar',
            'timezone' => 'Asia/Jerusalem',
            'status' => RestaurantStatus::Active,
        ];
    }

    /**
     * Indicate that the restaurant is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RestaurantStatus::Suspended,
        ]);
    }
}
