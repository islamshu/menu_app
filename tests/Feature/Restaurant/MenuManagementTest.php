<?php

namespace Tests\Feature\Restaurant;

use App\Enums\RestaurantRole;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MenuManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Authenticate a member of the given restaurant as the session's active tenant.
     */
    private function actingAsRestaurantMember(Restaurant $restaurant, RestaurantRole $role = RestaurantRole::Owner): User
    {
        $user = User::factory()->create();
        $restaurant->users()->attach($user, ['role' => $role->value]);

        $this->actingAs($user)->withSession(['active_restaurant_id' => $restaurant->id]);

        return $user;
    }

    public function test_owner_can_list_categories(): void
    {
        $restaurant = Restaurant::factory()->create();
        MenuCategory::factory()->count(2)->for($restaurant)->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->get(route('categories.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Restaurant/Categories/Index')
                ->has('categories.data', 2));
    }

    public function test_owner_can_create_a_category(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->post(route('categories.store'), [
            'name' => ['ar' => 'المقبلات', 'en' => 'Appetizers'],
            'description' => ['ar' => 'مقبلات باردة', 'en' => 'Cold starters'],
        ])->assertSessionHas('success');

        $category = $restaurant->menuCategories()->first();

        $this->assertNotNull($category);
        $this->assertSame('المقبلات', $category->name['ar']);
        $this->assertSame('Appetizers', $category->name['en']);
        $this->assertNotNull($category->slug);
        $this->assertSame(1, $category->sort_order);
    }

    public function test_category_requires_a_name_in_every_supported_locale(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->post(route('categories.store'), [
            'name' => ['ar' => 'مقبلات'],
        ])->assertSessionHasErrors(['name.en']);
    }

    public function test_owner_can_update_a_category(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);
        $category = MenuCategory::factory()->for($restaurant)->create();

        $this->put(route('categories.update', $category), [
            'name' => ['ar' => 'الحلويات', 'en' => 'Desserts'],
            'is_active' => false,
        ])->assertSessionHas('success');

        $this->assertSame(
            'الحلويات',
            $restaurant->menuCategories()->find($category->id)->name['ar'],
        );
        $this->assertFalse($restaurant->menuCategories()->find($category->id)->is_active);
    }

    public function test_owner_can_toggle_a_category(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);
        $category = MenuCategory::factory()->for($restaurant)->create(['is_active' => false]);

        $this->patch(route('categories.toggle', $category), [
            'field' => 'is_active',
            'value' => true,
        ])->assertSessionHasNoErrors();

        $this->assertTrue($restaurant->menuCategories()->find($category->id)->is_active);
    }

    public function test_owner_can_reorder_categories(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);
        [$first, $second] = MenuCategory::factory()->count(2)->for($restaurant)->create();

        $this->post(route('categories.reorder'), [
            'items' => [$second->id, $first->id],
        ])->assertSessionHas('success');

        $this->assertSame(1, $first->fresh()->sort_order);
        $this->assertSame(0, $second->fresh()->sort_order);
    }

    public function test_owner_can_delete_a_category_and_detach_its_items(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);
        $category = MenuCategory::factory()->for($restaurant)->create();
        $item = MenuItem::factory()->for($restaurant)->create(['menu_category_id' => $category->id]);

        $this->delete(route('categories.destroy', $category))->assertSessionHas('success');

        $this->assertDatabaseMissing('menu_categories', ['id' => $category->id]);
        $this->assertNull($item->fresh()->menu_category_id);
    }

    public function test_categories_of_other_restaurants_are_out_of_scope(): void
    {
        $restaurant = Restaurant::factory()->create();
        $other = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);
        $foreignCategory = MenuCategory::factory()->for($other)->create();

        $this->put(route('categories.update', $foreignCategory), [
            'name' => ['ar' => 'x', 'en' => 'x'],
        ])->assertNotFound();

        $this->patch(route('categories.toggle', $foreignCategory), [
            'field' => 'is_active',
            'value' => true,
        ])->assertNotFound();

        $this->delete(route('categories.destroy', $foreignCategory))->assertNotFound();

        $this->assertDatabaseHas('menu_categories', ['id' => $foreignCategory->id]);
    }

    public function test_staff_cannot_manage_categories(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant, RestaurantRole::Staff);

        $this->post(route('categories.store'), [
            'name' => ['ar' => 'مقبلات', 'en' => 'Appetizers'],
            'description' => ['ar' => null, 'en' => null],
        ])->assertForbidden();
    }

    public function test_guests_cannot_access_category_pages(): void
    {
        $this->get(route('categories.index'))->assertRedirect('/login');
    }

    public function test_category_index_respects_search(): void
    {
        $restaurant = Restaurant::factory()->create();
        MenuCategory::factory()->for($restaurant)->create([
            'name' => ['ar' => 'المشويات', 'en' => 'Grills'],
        ]);
        MenuCategory::factory()->for($restaurant)->create([
            'name' => ['ar' => 'الحلويات', 'en' => 'Desserts'],
        ]);
        $this->actingAsRestaurantMember($restaurant);

        $this->get(route('categories.index', ['search' => 'حلويات']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Restaurant/Categories/Index')
                ->has('categories.data', 1)
                ->where('filters.search', 'حلويات'));
    }

    public function test_owner_can_create_a_category_with_an_image(): void
    {
        Storage::fake('public');
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->post(route('categories.store'), [
            'name' => ['ar' => 'مقبلات', 'en' => 'Appetizers'],
            'description' => ['ar' => null, 'en' => null],
            'image' => UploadedFile::fake()->image('appetizers.jpg'),
        ])->assertSessionHas('success');

        $category = $restaurant->menuCategories()->first();

        $this->assertNotNull($category->image);
        Storage::disk('public')->assertExists($category->image);
    }

    public function test_owner_can_remove_a_category_image(): void
    {
        Storage::fake('public');
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);
        $category = MenuCategory::factory()->for($restaurant)->create([
            'image' => 'restaurants/'.$restaurant->id.'/categories/example.jpg',
        ]);
        Storage::disk('public')->put($category->image, 'binary');

        $this->put(route('categories.update', $category), [
            'name' => ['ar' => 'مقبلات', 'en' => 'Appetizers'],
            'description' => ['ar' => null, 'en' => null],
            'remove_image' => true,
        ])->assertSessionHas('success');

        Storage::disk('public')->assertMissing($category->image);
        $this->assertNull($category->fresh()->image);
    }

    public function test_owner_can_list_items_with_filters(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);
        $available = MenuItem::factory()->for($restaurant)->create(['is_available' => true]);
        MenuItem::factory()->for($restaurant)->create(['is_available' => false]);

        $this->get(route('items.index', ['availability' => 'available']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Restaurant/Items/Index')
                ->has('items.data', 1)
                ->where('items.data.0.id', $available->id)
                ->where('filters.availability', 'available'));
    }

    public function test_owner_can_create_an_item(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);
        $category = MenuCategory::factory()->for($restaurant)->create();

        $this->post(route('items.store'), [
            'name' => ['ar' => 'شاورما دجاج', 'en' => 'Chicken Shawarma'],
            'description' => ['ar' => null, 'en' => null],
            'menu_category_id' => $category->id,
            'price' => '12.50',
            'is_available' => true,
            'is_featured' => true,
        ])->assertSessionHas('success');

        $item = $restaurant->menuItems()->first();

        $this->assertNotNull($item);
        $this->assertSame('شاورما دجاج', $item->name['ar']);
        $this->assertSame('12.50', $item->price);
        $this->assertSame($category->id, $item->menu_category_id);
        $this->assertTrue($item->is_featured);
        $this->assertNotNull($item->slug);
    }

    public function test_item_requires_a_price_and_a_name_in_every_locale(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->post(route('items.store'), [
            'name' => ['ar' => 'صنف'],
        ])->assertSessionHasErrors(['price', 'name.en']);
    }

    public function test_owner_cannot_link_an_item_to_a_foreign_category(): void
    {
        $restaurant = Restaurant::factory()->create();
        $other = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);
        $foreignCategory = MenuCategory::factory()->for($other)->create();

        $this->post(route('items.store'), [
            'name' => ['ar' => 'صنف', 'en' => 'Item'],
            'description' => ['ar' => null, 'en' => null],
            'menu_category_id' => $foreignCategory->id,
            'price' => '5.00',
        ])->assertSessionHasNoErrors()
            ->assertStatus(422);

        $this->assertDatabaseCount('menu_items', 0);
    }

    public function test_owner_can_update_an_item_including_flags(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);
        $item = MenuItem::factory()->for($restaurant)->create(['price' => '9.00']);

        $this->put(route('items.update', $item), [
            'name' => ['ar' => 'كباب', 'en' => 'Kebab'],
            'description' => ['ar' => null, 'en' => null],
            'price' => '19.99',
            'compare_at_price' => '24.00',
            'is_available' => false,
            'is_popular' => true,
            'is_new' => true,
        ])->assertSessionHas('success');

        $fresh = $item->fresh();

        $this->assertSame('19.99', $fresh->price);
        $this->assertSame('24.00', $fresh->compare_at_price);
        $this->assertFalse($fresh->is_available);
        $this->assertTrue($fresh->is_popular);
        $this->assertTrue($fresh->is_new);
    }

    public function test_owner_can_toggle_item_flags(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);
        $item = MenuItem::factory()->for($restaurant)->create(['is_featured' => false]);

        $this->patch(route('items.toggle', $item), [
            'field' => 'is_featured',
            'value' => true,
        ])->assertSessionHasNoErrors();

        $this->assertTrue($item->fresh()->is_featured);
    }

    public function test_owner_can_reorder_items(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);
        [$first, $second] = MenuItem::factory()->count(2)->for($restaurant)->create();

        $this->post(route('items.reorder'), [
            'items' => [$second->id, $first->id],
        ])->assertSessionHas('success');

        $this->assertSame(1, $first->fresh()->sort_order);
        $this->assertSame(0, $second->fresh()->sort_order);
    }

    public function test_owner_can_delete_an_item(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);
        $item = MenuItem::factory()->for($restaurant)->create();

        $this->delete(route('items.destroy', $item))->assertSessionHas('success');

        $this->assertDatabaseMissing('menu_items', ['id' => $item->id]);
    }

    public function test_items_of_other_restaurants_are_out_of_scope(): void
    {
        $restaurant = Restaurant::factory()->create();
        $other = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);
        $foreignItem = MenuItem::factory()->for($other)->create();

        $this->put(route('items.update', $foreignItem), [
            'name' => ['ar' => 'x', 'en' => 'x'],
            'description' => ['ar' => null, 'en' => null],
            'price' => '1.00',
        ])->assertNotFound();

        $this->patch(route('items.toggle', $foreignItem), [
            'field' => 'is_available',
            'value' => true,
        ])->assertNotFound();

        $this->delete(route('items.destroy', $foreignItem))->assertNotFound();

        $this->assertDatabaseHas('menu_items', ['id' => $foreignItem->id]);
    }

    public function test_staff_cannot_manage_items(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant, RestaurantRole::Staff);

        $this->post(route('items.store'), [
            'name' => ['ar' => 'صنف', 'en' => 'Item'],
            'description' => ['ar' => null, 'en' => null],
            'price' => '5.00',
        ])->assertForbidden();
    }

    public function test_owner_can_upload_an_item_image(): void
    {
        Storage::fake('public');
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->post(route('items.store'), [
            'name' => ['ar' => 'برجر', 'en' => 'Burger'],
            'description' => ['ar' => null, 'en' => null],
            'price' => '25.00',
            'image' => UploadedFile::fake()->image('burger.jpg'),
        ])->assertSessionHas('success');

        $item = $restaurant->menuItems()->first();

        $this->assertNotNull($item->image);
        Storage::disk('public')->assertExists($item->image);
    }

    public function test_manager_can_manage_the_menu(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant, RestaurantRole::Manager);

        $this->post(route('categories.store'), [
            'name' => ['ar' => 'مشروبات', 'en' => 'Drinks'],
            'description' => ['ar' => null, 'en' => null],
        ])->assertSessionHas('success');

        $this->assertSame(1, $restaurant->menuCategories()->count());
    }

    public function test_owner_can_view_settings(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->get(route('settings.edit'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Restaurant/Settings/Edit')
                ->where('restaurant.id', $restaurant->id));
    }

    public function test_staff_cannot_view_settings(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant, RestaurantRole::Staff);

        $this->get(route('settings.edit'))->assertForbidden();
    }

    public function test_owner_can_update_settings(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->put(route('settings.update'), [
            'name' => 'مطعم الأصيلة',
            'description' => 'مطعم عائلي',
            'phone' => '+972599000000',
            'email' => 'hello@alaseela.example',
            'address' => 'رام الله، شارع المدرسة',
            'currency' => 'USD',
            'default_locale' => 'en',
            'timezone' => 'Asia/Jerusalem',
        ])->assertSessionHas('success');

        $fresh = $restaurant->fresh();

        $this->assertSame('مطعم الأصيلة', $fresh->name);
        $this->assertSame('USD', $fresh->currency);
        $this->assertSame('en', $fresh->default_locale);
        $this->assertSame('Asia/Jerusalem', $fresh->timezone);
    }

    public function test_settings_validates_timezone_and_locale(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->put(route('settings.update'), [
            'name' => 'مطعم',
            'description' => null,
            'currency' => 'USD',
            'default_locale' => 'fr',
            'timezone' => 'Narnia/Neverland',
        ])->assertSessionHasErrors(['timezone', 'default_locale']);
    }

    public function test_owner_can_upload_a_logo(): void
    {
        Storage::fake('public');
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        $this->put(route('settings.update'), [
            'name' => $restaurant->name,
            'description' => null,
            'currency' => 'ILS',
            'default_locale' => 'ar',
            'timezone' => 'UTC',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])->assertSessionHas('success');

        $fresh = $restaurant->fresh();

        $this->assertNotNull($fresh->logo);
        Storage::disk('public')->assertExists($fresh->logo);
    }

    public function test_dashboard_includes_menu_statistics(): void
    {
        $restaurant = Restaurant::factory()->create();
        $this->actingAsRestaurantMember($restaurant);

        MenuCategory::factory()->count(2)->for($restaurant)->create();
        MenuItem::factory()->count(3)->for($restaurant)->create(['is_available' => true]);
        MenuItem::factory()->for($restaurant)->create(['is_available' => false]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Restaurant/Dashboard')
                ->where('stats.categories', 2)
                ->where('stats.items', 4)
                ->where('stats.available', 3)
                ->where('stats.unavailable', 1));
    }
}
