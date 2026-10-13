<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::get('restaurants', [RestaurantController::class, 'index'])->name('restaurants.index');
Route::get('restaurants/{restaurant}', [RestaurantController::class, 'show'])->name('restaurants.show');
Route::patch('restaurants/{restaurant}/status', [RestaurantController::class, 'updateStatus'])->name('restaurants.status');
Route::delete('restaurants/{restaurant}', [RestaurantController::class, 'destroy'])->name('restaurants.destroy');

Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
Route::post('plans', [PlanController::class, 'store'])->name('plans.store');
Route::put('plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
Route::patch('plans/{plan}/toggle', [PlanController::class, 'toggle'])->name('plans.toggle');
Route::delete('plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');

Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
Route::post('subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
Route::patch('subscriptions/{subscription}/status', [SubscriptionController::class, 'updateStatus'])->name('subscriptions.status');
Route::delete('subscriptions/{subscription}', [SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');

Route::get('users', [UserController::class, 'index'])->name('users.index');
Route::patch('users/{user}/super-admin', [UserController::class, 'toggleSuperAdmin'])->name('users.super-admin');
