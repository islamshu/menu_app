<?php

namespace Tests\Feature\Public;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Database\Seeders\DemoMenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoMenuSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_populates_the_demo_restaurant_with_bilingual_data(): void
    {
        $demo = Restaurant::factory()->create(['slug' => 'demo-restaurant']);

        $this->seed(DemoMenuSeeder::class);

        $this->assertSame(6, $demo->menuCategories()->count());
        $this->assertSame(36, $demo->menuItems()->count());

        foreach ($demo->menuCategories()->get() as $category) {
            $this->assertTrue($category->is_active);
            $this->assertNotEmpty($category->name['ar']);
            $this->assertNotEmpty($category->name['en']);
        }

        foreach ($demo->menuItems()->get() as $item) {
            $this->assertTrue($item->is_available);
            $this->assertNotEmpty($item->name['ar']);
            $this->assertNotEmpty($item->name['en']);
            $this->assertNotNull($item->menu_category_id);
            $this->assertSame($demo->id, $item->restaurant_id);
            $this->assertSame($demo->id, $item->category->restaurant_id);
        }
    }

    public function test_it_marks_featured_popular_and_new_products(): void
    {
        Restaurant::factory()->create(['slug' => 'demo-restaurant']);

        $this->seed(DemoMenuSeeder::class);

        $this->assertGreaterThan(0, MenuItem::where('is_featured', true)->count());
        $this->assertGreaterThan(0, MenuItem::where('is_popular', true)->count());
        $this->assertGreaterThan(0, MenuItem::where('is_new', true)->count());
    }

    public function test_it_is_idempotent_when_rerun(): void
    {
        $demo = Restaurant::factory()->create(['slug' => 'demo-restaurant']);

        $this->seed(DemoMenuSeeder::class);
        $categoryIds = $demo->menuCategories()->pluck('id')->sort()->values()->all();
        $itemIds = $demo->menuItems()->pluck('id')->sort()->values()->all();

        $this->seed(DemoMenuSeeder::class);

        $this->assertSame(6, $demo->menuCategories()->count());
        $this->assertSame(36, $demo->menuItems()->count());
        $this->assertSame($categoryIds, $demo->menuCategories()->pluck('id')->sort()->values()->all());
        $this->assertSame($itemIds, $demo->menuItems()->pluck('id')->sort()->values()->all());
    }

    public function test_it_does_not_touch_other_restaurants(): void
    {
        Restaurant::factory()->create(['slug' => 'demo-restaurant']);
        $other = Restaurant::factory()->create();

        $this->seed(DemoMenuSeeder::class);

        $this->assertSame(0, $other->menuCategories()->count());
        $this->assertSame(0, $other->menuItems()->count());
    }

    public function test_it_noops_when_the_demo_restaurant_is_missing(): void
    {
        $this->seed(DemoMenuSeeder::class);

        $this->assertSame(0, MenuCategory::count());
        $this->assertSame(0, MenuItem::count());
    }
}
