<?php

use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::get('p/{slug}', [PublicProfileController::class, 'show'])
    ->name('profile.public');

// Package routes
Route::get('packages/{package:slug}', [App\Http\Controllers\PackageController::class, 'show'])
    ->name('packages.show');

// Order routes - protected with auth
Route::middleware(['auth'])->group(function () {
    Route::post('orders', [App\Http\Controllers\OrderController::class, 'store'])
        ->name('orders.store');
    Route::get('orders', [App\Http\Controllers\OrderController::class, 'index'])
        ->name('orders.index');
    Route::get('orders/{order}', [App\Http\Controllers\OrderController::class, 'show'])
        ->name('orders.show');
});

Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('profile/builder', 'profile-builder')
    ->middleware(['auth'])
    ->name('profile.builder');

// Admin routes - protected with admin middleware and rate limiting
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])
        ->name('dashboard');
    
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::resource('profiles', App\Http\Controllers\Admin\ProfileController::class)->except(['create', 'store']);
    
    // Package management routes
    Route::resource('packages', App\Http\Controllers\Admin\PackageController::class);
    Route::post('packages/{package}/toggle-active', [App\Http\Controllers\Admin\PackageController::class, 'toggleActive'])
        ->name('packages.toggle-active');
    Route::post('packages/{package}/pricing-tiers', [App\Http\Controllers\Admin\PackageController::class, 'storePricingTier'])
        ->name('packages.pricing-tiers.store');
    Route::delete('packages/{package}/pricing-tiers/{tier}', [App\Http\Controllers\Admin\PackageController::class, 'destroyPricingTier'])
        ->name('packages.pricing-tiers.destroy');
});

require __DIR__.'/auth.php';
