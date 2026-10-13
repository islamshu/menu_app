<?php

namespace Database\Factories;

use App\Models\MenuCategory;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MenuCategory>
 */
class MenuCategoryFactory extends Factory
{
    protected $model = MenuCategory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $en = fake()->unique()->words(2, true);
        $ar = 'قسم '.fake()->unique()->word();

        return [
            'restaurant_id' => Restaurant::factory(),
            'name' => ['ar' => $ar, 'en' => Str::title($en)],
            'slug' => Str::slug($en).'-'.Str::lower(Str::random(4)),
            'description' => [
                'ar' => fake()->sentence(),
                'en' => fake()->sentence(),
            ],
            'image' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
