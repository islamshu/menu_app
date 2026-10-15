<?php

use App\Http\Controllers\Restaurant\BrandingController;
use App\Http\Controllers\Restaurant\CategoryController;
use App\Http\Controllers\Restaurant\DashboardController;
use App\Http\Controllers\Restaurant\MenuItemController;
use App\Http\Controllers\Restaurant\QrStudioController;
use App\Http\Controllers\Restaurant\RestaurantSwitchController;
use App\Http\Controllers\Restaurant\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::get('/restaurants', [RestaurantSwitchController::class, 'index'])->name('restaurants.index');
Route::post('/restaurants/{restaurant}/switch', [RestaurantSwitchController::class, 'store'])->name('restaurants.switch');

Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);
Route::patch('categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');

Route::resource('items', MenuItemController::class)
    ->except(['show', 'create', 'edit'])
    ->parameters(['items' => 'item']);
Route::patch('items/{item}/toggle', [MenuItemController::class, 'toggle'])->name('items.toggle');
Route::post('items/reorder', [MenuItemController::class, 'reorder'])->name('items.reorder');

Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

Route::get('branding', [BrandingController::class, 'edit'])->name('branding.edit');
Route::put('branding', [BrandingController::class, 'update'])->name('branding.update');
Route::post('branding/publish', [BrandingController::class, 'publish'])->name('branding.publish');
Route::delete('branding/draft', [BrandingController::class, 'discardDraft'])->name('branding.discard');
Route::post('branding/palette', [BrandingController::class, 'extractPalette'])->name('branding.palette');

Route::get('qr-studio', [QrStudioController::class, 'edit'])->name('qr.edit');
Route::get('qr-studio/preview', [QrStudioController::class, 'preview'])->name('qr.preview');
Route::get('qr-studio/download', [QrStudioController::class, 'download'])->name('qr.download');
Route::put('qr-studio', [QrStudioController::class, 'update'])->name('qr.update');
Route::post('qr-studio/publish', [QrStudioController::class, 'publish'])->name('qr.publish');
Route::delete('qr-studio/draft', [QrStudioController::class, 'discardDraft'])->name('qr.discard');
