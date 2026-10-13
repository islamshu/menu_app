<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_users_are_redirected_to_the_workspace(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_super_admins_are_redirected_to_the_admin_area(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard', absolute: false));
    }
}
