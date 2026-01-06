<?php

use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\PackageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::get('packages/{package:slug}', [PackageController::class, 'show'])
    ->name('packages.show');

Route::get('p/{slug}', [PublicProfileController::class, 'show'])
    ->name('profile.public');

Route::get('p/{slug}/vcard', [PublicProfileController::class, 'downloadVCard'])
    ->name('profile.vcard');

// Dashboard routes - protected with auth
Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {
    // QR Codes
    Route::prefix('qr-codes')->name('qr-codes.')->group(function () {
        Route::get('/', [App\Http\Controllers\Dashboard\QrCodeController::class, 'index'])
            ->name('index');
    });
    
    // Cards & Packages
    Route::prefix('cards')->name('cards.')->group(function () {
        Route::get('/packages', [App\Http\Controllers\Dashboard\CardController::class, 'packages'])
            ->name('packages');
        Route::get('/checkout/{package:slug}', [App\Http\Controllers\Dashboard\CardController::class, 'checkout'])
            ->name('checkout');
        Route::post('/order', [App\Http\Controllers\Dashboard\CardController::class, 'store'])
            ->name('store');
    });
    
    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [App\Http\Controllers\Dashboard\OrderController::class, 'index'])
            ->name('index');
        Route::get('/{order}', [App\Http\Controllers\Dashboard\OrderController::class, 'show'])
            ->name('show');
        Route::get('/{order}/payment', [App\Http\Controllers\Dashboard\OrderController::class, 'payment'])
            ->name('payment');
        Route::post('/{order}/process-payment', [App\Http\Controllers\Dashboard\OrderController::class, 'processPayment'])
            ->name('process-payment');
    });
    
    // Subscriptions
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Dashboard\SubscriptionController::class, 'index'])
            ->name('index');
        Route::get('/{profile}', [App\Http\Controllers\Dashboard\SubscriptionController::class, 'show'])
            ->name('show');
        Route::post('/{profile}/renew', [App\Http\Controllers\Dashboard\SubscriptionController::class, 'renew'])
            ->name('renew');
        Route::post('/bulk-renew', [App\Http\Controllers\Dashboard\SubscriptionController::class, 'bulkRenew'])
            ->name('bulk-renew');
    });
});

Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Profile Builder routes - CRUD style
Route::prefix('profile/builder')->name('profile.builder.')->middleware(['auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\ProfileBuilderController::class, 'index'])
        ->name('index');
    Route::get('/create', [App\Http\Controllers\ProfileBuilderController::class, 'create'])
        ->name('create');
    Route::get('/{id}/edit', [App\Http\Controllers\ProfileBuilderController::class, 'edit'])
        ->name('edit');
});

// Profile publishing routes
Route::middleware(['auth'])->group(function () {
    Route::post('profile/publish', [App\Http\Controllers\ProfileController::class, 'publish'])
        ->name('profile.publish');
    Route::post('profile/unpublish', [App\Http\Controllers\ProfileController::class, 'unpublish'])
        ->name('profile.unpublish');
    
    // QR Code routes
    Route::get('profile/{slug}/qr/download', [App\Http\Controllers\QrCodeController::class, 'download'])
        ->name('profile.qr.download');
    Route::get('profile/{slug}/qr/card', [App\Http\Controllers\QrCodeController::class, 'generateCard'])
        ->name('profile.qr.card');
});

// Webhook routes (no auth - verified via signature)
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('/mpesa', [App\Http\Controllers\Webhook\PaymentWebhookController::class, 'mpesa'])
        ->name('mpesa');
    Route::post('/stripe', [App\Http\Controllers\Webhook\PaymentWebhookController::class, 'stripe'])
        ->name('stripe');
});

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
    
    // Order management routes
    Route::get('orders', [App\Http\Controllers\Admin\OrderController::class, 'index'])
        ->name('orders.index');
    Route::get('orders/{order}', [App\Http\Controllers\Admin\OrderController::class, 'show'])
        ->name('orders.show');
    Route::patch('orders/{order}/status', [App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])
        ->name('orders.update-status');
    Route::post('orders/{order}/mark-paid', [App\Http\Controllers\Admin\OrderController::class, 'markAsPaid'])
        ->name('orders.mark-paid');
    Route::post('orders/{order}/refund', [App\Http\Controllers\Admin\OrderController::class, 'refund'])
        ->name('orders.refund');
    
    // NFC Card management routes
    Route::prefix('nfc-cards')->name('nfc-cards.')->group(function () {
        Route::get('/production-queue', [App\Http\Controllers\Admin\NfcCardController::class, 'productionQueue'])
            ->name('production-queue');
        Route::get('/{card}', [App\Http\Controllers\Admin\NfcCardController::class, 'show'])
            ->name('show');
        Route::post('/{card}/start-production', [App\Http\Controllers\Admin\NfcCardController::class, 'startProduction'])
            ->name('start-production');
        Route::post('/{card}/mark-produced', [App\Http\Controllers\Admin\NfcCardController::class, 'markProduced'])
            ->name('mark-produced');
        Route::post('/{card}/ship', [App\Http\Controllers\Admin\NfcCardController::class, 'ship'])
            ->name('ship');
        Route::post('/{card}/mark-delivered', [App\Http\Controllers\Admin\NfcCardController::class, 'markDelivered'])
            ->name('mark-delivered');
        Route::post('/bulk-update', [App\Http\Controllers\Admin\NfcCardController::class, 'bulkUpdateStatus'])
            ->name('bulk-update');
    });
    
    // Manual payment confirmation
    Route::post('/payment/manual-confirm', [App\Http\Controllers\Webhook\PaymentWebhookController::class, 'manualConfirm'])
        ->name('payment.manual-confirm');
});

require __DIR__.'/auth.php';
