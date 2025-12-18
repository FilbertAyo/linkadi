<?php

use App\Http\Controllers\PublicProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');

Route::get('p/{slug}', [PublicProfileController::class, 'show'])
    ->name('profile.public');

Route::view('dashboard', 'dashboard')
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
});

require __DIR__.'/auth.php';
