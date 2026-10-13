<?php

namespace Tests\Feature\Public;

use App\Models\Restaurant;
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
}
