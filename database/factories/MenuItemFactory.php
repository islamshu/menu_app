<?php

namespace Database\Factories;

use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $en = fake()->unique()->words(2, true);
        $ar = 'صنف '.fake()->unique()->word();
        $price = fake()->randomFloat(2, 5, 120);

        return [
            'restaurant_id' => Restaurant::factory(),
            'menu_category_id' => null,
            'name' => ['ar' => $ar, 'en' => Str::title($en)],
            'slug' => Str::slug($en).'-'.Str::lower(Str::random(4)),
            'description' => [
                'ar' => fake()->sentence(),
                'en' => fake()->sentence(),
            ],
            'price' => $price,
            'compare_at_price' => null,
            'image' => null,
            'is_available' => true,
            'is_featured' => false,
            'is_popular' => false,
            'is_new' => false,
            'sort_order' => 0,
        ];
    }

    public function unavailable(): static
    {
        return $this->state(fn () => ['is_available' => false]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }
}
