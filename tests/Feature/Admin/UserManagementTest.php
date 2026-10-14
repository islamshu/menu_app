<?php

namespace Tests\Feature\Admin;

use App\Enums\RestaurantRole;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    public function test_super_admin_can_list_users(): void
    {
        $admin = $this->admin();
        User::factory()->count(2)->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Index')
                ->has('users.data', 3));
    }

    public function test_users_can_be_searched(): void
    {
        $this->admin();
        User::factory()->create(['name' => 'Findable Person']);
        User::factory()->create(['name' => 'Other Guy']);

        $this->actingAs($this->admin())
            ->get(route('admin.users.index', ['search' => 'Findable']))
            ->assertInertia(fn (Assert $page) => $page->has('users.data', 1));
    }

    public function test_super_admin_can_promote_a_member(): void
    {
        $admin = $this->admin();
        $member = User::factory()->create(['is_super_admin' => false]);

        $this->actingAs($admin)
            ->patch(route('admin.users.super-admin', $member))
            ->assertSessionHas('success');

        $this->assertTrue($member->fresh()->isSuperAdmin());
    }

    public function test_super_admin_can_demote_another_admin(): void
    {
        $admin = $this->admin();
        $other = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.super-admin', $other))
            ->assertSessionHas('success');

        $this->assertFalse($other->fresh()->isSuperAdmin());
    }

    public function test_super_admin_cannot_change_their_own_access(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch(route('admin.users.super-admin', $admin))
            ->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->isSuperAdmin());
    }

    public function test_super_admin_can_create_a_user(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'New Person',
                'email' => 'new@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'is_super_admin' => true,
            ])
            ->assertSessionHas('success');

        $user = User::where('email', 'new@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_user_creation_validates_input(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => '',
                'email' => 'not-an-email',
                'password' => 'short',
                'password_confirmation' => 'different',
            ])
            ->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_super_admin_can_update_a_user(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), [
                'name' => 'New Name',
                'email' => 'new@example.com',
                'password' => 'brand-new-pass',
                'password_confirmation' => 'brand-new-pass',
                'is_super_admin' => false,
            ])
            ->assertSessionHas('success');

        $user->refresh();

        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertTrue(Hash::check('brand-new-pass', $user->password));
        $this->assertNull($user->email_verified_at);
    }

    public function test_user_update_keeps_the_password_when_left_blank(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['password' => bcrypt('original-pass')]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'password' => '',
                'is_super_admin' => false,
            ])
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('original-pass', $user->fresh()->password));
    }

    public function test_user_update_validates_unique_email(): void
    {
        $admin = $this->admin();
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create(['email' => 'mine@example.com']);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => 'taken@example.com',
                'is_super_admin' => false,
            ])
            ->assertSessionHasErrors('email');

        $this->assertSame('mine@example.com', $user->fresh()->email);
    }

    public function test_super_admin_cannot_demote_their_own_account_via_update(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $admin), [
                'name' => 'Renamed Admin',
                'email' => $admin->email,
                'is_super_admin' => false,
            ])
            ->assertSessionHas('success');

        $admin->refresh();

        $this->assertSame('Renamed Admin', $admin->name);
        $this->assertTrue($admin->isSuperAdmin());
    }

    public function test_changing_owner_email_updates_derived_restaurant_email(): void
    {
        $admin = $this->admin();
        $owner = User::factory()->create(['email' => 'owner@example.com']);
        $restaurant = Restaurant::factory()->create(['email' => 'owner@example.com']);
        $restaurant->users()->attach($owner, ['role' => RestaurantRole::Owner->value]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $owner), [
                'name' => $owner->name,
                'email' => 'new-owner@example.com',
                'is_super_admin' => false,
            ])
            ->assertSessionHas('success');

        $this->assertSame('new-owner@example.com', $owner->fresh()->email);
        $this->assertSame('new-owner@example.com', $restaurant->fresh()->email);
    }

    public function test_changing_owner_email_keeps_a_custom_restaurant_email(): void
    {
        $admin = $this->admin();
        $owner = User::factory()->create(['email' => 'owner@example.com']);
        $restaurant = Restaurant::factory()->create(['email' => 'info@restaurant.com']);
        $restaurant->users()->attach($owner, ['role' => RestaurantRole::Owner->value]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $owner), [
                'name' => $owner->name,
                'email' => 'new-owner@example.com',
                'is_super_admin' => false,
            ])
            ->assertSessionHas('success');

        $this->assertSame('info@restaurant.com', $restaurant->fresh()->email);
    }
}
