<?php

use App\Http\Controllers\Restaurant\DashboardController;
use App\Http\Controllers\Restaurant\RestaurantSwitchController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::get('/restaurants', [RestaurantSwitchController::class, 'index'])->name('restaurants.index');
Route::post('/restaurants/{restaurant}/switch', [RestaurantSwitchController::class, 'store'])->name('restaurants.switch');
