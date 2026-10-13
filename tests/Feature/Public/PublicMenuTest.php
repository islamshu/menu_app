<?php

namespace Tests\Feature\Public;

use App\Enums\RestaurantRole;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_restaurant_menu_is_publicly_accessible(): void
    {
        $restaurant = Restaurant::factory()->create();

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Menu')
                ->where('restaurant.slug', $restaurant->slug));
    }

    public function test_suspended_restaurant_menu_returns_not_found(): void
    {
        $restaurant = Restaurant::factory()->suspended()->create();

        $this->get('/m/'.$restaurant->slug)->assertNotFound();
    }

    public function test_unknown_slug_returns_not_found(): void
    {
        $this->get('/m/does-not-exist')->assertNotFound();
    }

    public function test_guests_are_not_redirected_to_login(): void
    {
        $restaurant = Restaurant::factory()->create();

        $this->get('/m/'.$restaurant->slug)->assertOk();
    }

    public function test_visible_menu_exposes_active_categories_with_available_items(): void
    {
        $restaurant = Restaurant::factory()->create();
        $category = MenuCategory::factory()->for($restaurant)->create();
        MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $category->id,
            'is_available' => true,
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Menu')
                ->has('categories', 1)
                ->has('categories.0.items', 1));
    }

    public function test_hidden_categories_are_excluded(): void
    {
        $restaurant = Restaurant::factory()->create();
        $hidden = MenuCategory::factory()->for($restaurant)->inactive()->create();
        MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $hidden->id,
            'is_available' => true,
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Menu')
                ->has('categories', 0));
    }

    public function test_unavailable_items_are_excluded(): void
    {
        $restaurant = Restaurant::factory()->create();
        $category = MenuCategory::factory()->for($restaurant)->create();
        MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $category->id,
            'is_available' => false,
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Menu')
                ->has('categories', 0));
    }

    public function test_category_with_only_unavailable_items_is_hidden(): void
    {
        $restaurant = Restaurant::factory()->create();
        $visible = MenuCategory::factory()->for($restaurant)->create();
        $empty = MenuCategory::factory()->for($restaurant)->create();

        MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $visible->id,
            'is_available' => true,
        ]);
        MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $empty->id,
            'is_available' => false,
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Menu')
                ->has('categories', 1)
                ->where('categories.0.id', $visible->id));
    }

    public function test_menu_is_isolated_to_the_resolved_restaurant(): void
    {
        $restaurant = Restaurant::factory()->create();
        $other = Restaurant::factory()->create();

        $category = MenuCategory::factory()->for($restaurant)->create();
        $item = MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $category->id,
        ]);

        $otherCategory = MenuCategory::factory()->for($other)->create();
        $otherItem = MenuItem::factory()->for($other)->create([
            'menu_category_id' => $otherCategory->id,
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Menu')
                ->has('categories', 1)
                ->where('categories.0.id', $category->id)
                ->where('categories.0.items.0.id', $item->id)
                ->where('categories.0.items.0.id', fn ($id) => $id === $item->id && $id !== $otherItem->id));
    }

    public function test_categories_are_ordered_by_sort_order(): void
    {
        $restaurant = Restaurant::factory()->create();
        $second = MenuCategory::factory()->for($restaurant)->create(['sort_order' => 20]);
        $first = MenuCategory::factory()->for($restaurant)->create(['sort_order' => 5]);

        foreach ([$second, $first] as $category) {
            MenuItem::factory()->for($restaurant)->create([
                'menu_category_id' => $category->id,
            ]);
        }

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Menu')
                ->where('categories.0.id', $first->id)
                ->where('categories.1.id', $second->id));
    }

    public function test_featured_items_are_surfaced_separately(): void
    {
        $restaurant = Restaurant::factory()->create();
        $category = MenuCategory::factory()->for($restaurant)->create();
        $featured = MenuItem::factory()->for($restaurant)->featured()->create([
            'menu_category_id' => $category->id,
        ]);
        MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $category->id,
            'is_featured' => false,
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Menu')
                ->has('featured', 1)
                ->where('featured.0.id', $featured->id));
    }

    public function test_unavailable_featured_items_are_not_surfaced(): void
    {
        $restaurant = Restaurant::factory()->create();
        $category = MenuCategory::factory()->for($restaurant)->create();
        MenuItem::factory()->for($restaurant)->featured()->create([
            'menu_category_id' => $category->id,
            'is_available' => false,
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Menu')
                ->has('featured', 0));
    }

    public function test_localization_payload_supports_the_switcher(): void
    {
        $restaurant = Restaurant::factory()->create([
            'default_locale' => 'en',
            'currency' => 'USD',
        ]);
        $category = MenuCategory::factory()->for($restaurant)->create([
            'name' => ['ar' => 'المقبلات', 'en' => 'Appetizers'],
        ]);
        MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $category->id,
            'name' => ['ar' => 'حمص', 'en' => 'Hummus'],
            'description' => ['ar' => 'حمص بالطحينة', 'en' => 'Hummus with tahini'],
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Menu')
                ->where('restaurant.locale', 'en')
                ->where('restaurant.dir', 'ltr')
                ->where('restaurant.currency', 'USD')
                ->has('restaurant.locales', 2)
                ->where('categories.0.name.ar', 'المقبلات')
                ->where('categories.0.name.en', 'Appetizers')
                ->where('categories.0.items.0.name.ar', 'حمص')
                ->where('categories.0.items.0.name.en', 'Hummus')
                ->where('categories.0.items.0.description.en', 'Hummus with tahini'));
    }

    public function test_default_locale_falls_back_to_configured_default(): void
    {
        $restaurant = Restaurant::factory()->create([
            'default_locale' => 'fr',
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('restaurant.locale', config('locales.default')));
    }

    public function test_empty_menu_renders_without_error(): void
    {
        $restaurant = Restaurant::factory()->create();

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Menu')
                ->has('categories', 0)
                ->has('featured', 0)
                ->where('restaurant.name', $restaurant->name));
    }

    public function test_prices_and_discount_flags_are_exposed(): void
    {
        $restaurant = Restaurant::factory()->create();
        $category = MenuCategory::factory()->for($restaurant)->create();
        $item = MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $category->id,
            'price' => '18.00',
            'compare_at_price' => '24.00',
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('categories.0.items.0.id', $item->id)
                ->where('categories.0.items.0.price', '18.00')
                ->where('categories.0.items.0.compare_at_price', '24.00')
                ->where('categories.0.items.0.is_available', true));
    }

    public function test_meta_payload_is_generated_for_seo(): void
    {
        $restaurant = Restaurant::factory()->create([
            'description' => 'مطعم عائلي يقدم المأكولات الشرقية.',
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('meta.title', $restaurant->name)
                ->where('meta.canonical', route('menu.show', $restaurant->slug))
                ->where('meta.description', 'مطعم عائلي يقدم المأكولات الشرقية.'));
    }

    public function test_meta_description_has_a_fallback_when_missing(): void
    {
        $restaurant = Restaurant::factory()->create(['description' => null]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('meta.description', fn ($value) => is_string($value) && $value !== ''));
    }

    public function test_public_menu_works_for_authenticated_members(): void
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $restaurant->users()->attach($user, ['role' => RestaurantRole::Owner->value]);

        $this->actingAs($user)
            ->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Public/Menu'));
    }

    public function test_restaurant_workspace_still_functions(): void
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $restaurant->users()->attach($user, ['role' => RestaurantRole::Owner->value]);

        $this->actingAs($user)
            ->withSession(['active_restaurant_id' => $restaurant->id])
            ->get('/app')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Restaurant/Dashboard'));
    }

    public function test_most_popular_virtual_category_appears_first(): void
    {
        $restaurant = Restaurant::factory()->create();
        $category = MenuCategory::factory()->for($restaurant)->create();
        MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $category->id,
            'is_popular' => true,
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Menu')
                ->where('categories.0.id', 'most-popular')
                ->where('categories.0.slug', 'most-popular')
                ->where('categories.0.virtual', true)
                ->where('categories.0.name.ar', 'الأكثر طلباً')
                ->where('categories.0.name.en', 'Most Popular')
                ->where('categories.1.id', $category->id));
    }

    public function test_most_popular_collects_popular_items_from_multiple_categories(): void
    {
        $restaurant = Restaurant::factory()->create();
        $first = MenuCategory::factory()->for($restaurant)->create();
        $second = MenuCategory::factory()->for($restaurant)->create();
        $a = MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $first->id,
            'is_popular' => true,
        ]);
        $b = MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $second->id,
            'is_popular' => true,
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('categories.0.items', 2)
                ->where('categories.0.items', fn ($items) => collect($items)
                    ->pluck('id')
                    ->sort()
                    ->values()
                    ->all() === collect([$a->id, $b->id])->sort()->values()->all()));
    }

    public function test_most_popular_excludes_non_popular_products(): void
    {
        $restaurant = Restaurant::factory()->create();
        $category = MenuCategory::factory()->for($restaurant)->create();
        $popular = MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $category->id,
            'is_popular' => true,
        ]);
        MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $category->id,
            'is_popular' => false,
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('categories.0.items', 1)
                ->where('categories.0.items.0.id', $popular->id));
    }

    public function test_most_popular_excludes_unavailable_products(): void
    {
        $restaurant = Restaurant::factory()->create();
        $category = MenuCategory::factory()->for($restaurant)->create();
        MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $category->id,
            'is_popular' => true,
            'is_available' => false,
        ]);
        MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $category->id,
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('categories.0.id', $category->id)
                ->missing('categories.0.virtual'));
    }

    public function test_most_popular_excludes_items_from_inactive_categories(): void
    {
        $restaurant = Restaurant::factory()->create();
        $inactive = MenuCategory::factory()->for($restaurant)->inactive()->create();
        MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $inactive->id,
            'is_popular' => true,
        ]);
        $active = MenuCategory::factory()->for($restaurant)->create();
        MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $active->id,
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('categories.0.id', $active->id)
                ->missing('categories.0.virtual'));
    }

    public function test_most_popular_never_includes_cross_tenant_products(): void
    {
        $restaurant = Restaurant::factory()->create();
        $category = MenuCategory::factory()->for($restaurant)->create();
        $mine = MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $category->id,
            'is_popular' => true,
        ]);

        $other = Restaurant::factory()->create();
        $otherCategory = MenuCategory::factory()->for($other)->create();
        MenuItem::factory()->for($other)->create([
            'menu_category_id' => $otherCategory->id,
            'is_popular' => true,
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('categories.0.items', 1)
                ->where('categories.0.items.0.id', $mine->id));
    }

    public function test_most_popular_section_is_hidden_when_no_popular_products(): void
    {
        $restaurant = Restaurant::factory()->create();
        $category = MenuCategory::factory()->for($restaurant)->create();
        MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $category->id,
            'is_popular' => false,
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('categories', 1)
                ->where('categories.0.id', $category->id)
                ->missing('categories.0.virtual'));
    }

    public function test_popular_products_remain_in_their_original_categories(): void
    {
        $restaurant = Restaurant::factory()->create();
        $category = MenuCategory::factory()->for($restaurant)->create();
        $item = MenuItem::factory()->for($restaurant)->create([
            'menu_category_id' => $category->id,
            'is_popular' => true,
        ]);

        $this->get('/m/'.$restaurant->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('categories.0.id', 'most-popular')
                ->where('categories.0.items.0.id', $item->id)
                ->where('categories.1.id', $category->id)
                ->where('categories.1.items.0.id', $item->id));
    }
}
