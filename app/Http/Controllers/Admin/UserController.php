<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Paginated list of platform users.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $role = (string) $request->query('role', '');

        $users = User::query()
            ->withCount('restaurants')
            ->withCount('ownedRestaurants')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role === 'super_admin', fn ($query) => $query->where('is_super_admin', true))
            ->when($role === 'member', fn ($query) => $query->where('is_super_admin', false))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_super_admin' => $user->isSuperAdmin(),
                'restaurants_count' => $user->restaurants_count,
                'owned_restaurants_count' => $user->owned_restaurants_count,
                'created_at' => $user->created_at?->toDateString(),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'role' => $role,
            ],
            'currentUserId' => $request->user()->id,
        ]);
    }

    /**
     * Create a platform user from the super admin area.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'is_super_admin' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->is_super_admin = $request->boolean('is_super_admin');
        $user->save();

        return back()->with('success', 'تم إنشاء المستخدم.');
    }

    /**
     * Update a platform user from the super admin area.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'confirmed', Rules\Password::defaults()],
            'is_super_admin' => ['boolean'],
        ]);

        $oldEmail = $user->email;

        $user->name = $data['name'];
        $user->email = $data['email'];

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        // Never let an admin revoke their own platform access.
        $user->is_super_admin = $user->is($request->user())
            ? true
            : $request->boolean('is_super_admin');

        $user->save();

        // Keep derived restaurant contact emails in sync with the owner.
        if ($oldEmail !== $user->email) {
            $user->ownedRestaurants()
                ->where('email', $oldEmail)
                ->get()
                ->each(function (Restaurant $restaurant) use ($user) {
                    $restaurant->email = $user->email;
                    $restaurant->save();
                });
        }

        return back()->with('success', 'تم تحديث المستخدم.');
    }

    /**
     * Grant or revoke platform super admin access.
     */
    public function toggleSuperAdmin(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'لا يمكنك تعديل صلاحيات حسابك.');
        }

        $user->is_super_admin = ! $user->isSuperAdmin();
        $user->save();

        return back()->with('success', 'تم تحديث صلاحيات المستخدم.');
    }

    /**
     * Remove a platform user.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'لا يمكنك حذف حسابك الخاص.');
        }

        $ownsRestaurants = $user->ownedRestaurants()->exists();

        if ($ownsRestaurants) {
            return back()->with('error', 'لا يمكن حذف مستخدم يملك مطاعم. انقل الملكية أو احذف المطاعم أولاً.');
        }

        $user->delete();

        return back()->with('success', 'تم حذف المستخدم.');
    }
}
