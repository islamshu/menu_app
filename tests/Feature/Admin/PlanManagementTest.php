<?php

namespace Tests\Feature\Admin;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PlanManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Professional',
            'price' => 49.99,
            'billing_interval' => 'monthly',
            'is_active' => true,
            'features' => ['QR menu', 'Multi-language'],
            'limits' => ['restaurants' => 3, 'menu_items' => 200],
        ], $overrides);
    }

    public function test_super_admin_can_list_plans(): void
    {
        Plan::factory()->count(2)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.plans.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Plans/Index')
                ->has('plans', 2)
                ->has('intervals'));
    }

    public function test_super_admin_can_create_a_plan(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.plans.store'), $this->validPayload())
            ->assertSessionHas('success');

        $plan = Plan::where('name', 'Professional')->first();

        $this->assertNotNull($plan);
        $this->assertSame('professional', $plan->slug);
        $this->assertSame(['QR menu', 'Multi-language'], $plan->features);
        $this->assertSame(['restaurants' => 3, 'menu_items' => 200], $plan->limits);
        $this->assertTrue($plan->is_active);
    }

    public function test_plan_creation_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.plans.store'), ['name' => '', 'price' => -5])
            ->assertSessionHasErrors(['name', 'price', 'billing_interval']);
    }

    public function test_super_admin_can_update_a_plan(): void
    {
        $plan = Plan::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.plans.update', $plan), $this->validPayload([
                'name' => 'Business',
                'price' => 99,
                'billing_interval' => 'yearly',
            ]))
            ->assertSessionHas('success');

        $plan->refresh();

        $this->assertSame('Business', $plan->name);
        $this->assertSame('99.00', $plan->price);
        $this->assertSame('yearly', $plan->billing_interval->value);
    }

    public function test_super_admin_can_toggle_a_plan(): void
    {
        $plan = Plan::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin())
            ->patch(route('admin.plans.toggle', $plan))
            ->assertSessionHasNoErrors();

        $this->assertFalse($plan->fresh()->is_active);
    }

    public function test_plan_with_subscriptions_cannot_be_deleted(): void
    {
        $plan = Plan::factory()->create();
        Subscription::factory()->create(['plan_id' => $plan->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.plans.destroy', $plan))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('plans', ['id' => $plan->id]);
    }

    public function test_unused_plan_can_be_deleted(): void
    {
        $plan = Plan::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.plans.destroy', $plan))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('plans', ['id' => $plan->id]);
    }
}
