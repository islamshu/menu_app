<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\PublicMenuController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Public/Home', [
        'canLogin' => Route::has('login'),
    ]);
})->name('home');

// Public digital menus (active restaurants only).
Route::get('/m/{restaurant:slug}', PublicMenuController::class)->name('menu.show');

// Super admin area.
Route::middleware(['auth', 'verified', 'super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(base_path('routes/admin.php'));

// Restaurant workspace.
Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('app')
    ->group(base_path('routes/restaurant.php'));

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
