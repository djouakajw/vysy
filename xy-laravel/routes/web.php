<?php

use Illuminate\Support\Facades\Route;

// ============================================================
// PUBLIC / MARKETING ROUTES
// ============================================================

Route::get('/', function () {
    return view('marketing.home');
})->name('home');

Route::get('/pricing', function () {
    return view('marketing.pricing');
})->name('pricing');

Route::get('/dashboard', function () {
    return view('marketing.dashboard');
})->name('marketing.dashboard')->middleware('auth');

// ============================================================
// TENANT PUBLIC SITE (subdomain middleware resolves $tenant)
// ============================================================

Route::middleware(['tenant.subdomain'])->group(function () {
    Route::get('/', \App\Http\Controllers\Tenant\SiteController::class . '@show')->name('tenant.site');
    Route::get('/qr/{qr}', \App\Http\Controllers\Tenant\SiteController::class . '@qrRedirect')->name('tenant.qr');
    Route::post('/contact', \App\Http\Controllers\Tenant\ContactController::class . '@store')->name('tenant.contact.store');
});

// ============================================================
// TENANT DASHBOARD (authenticated owner)
// ============================================================

Route::middleware(['auth', 'tenant.owner'])->prefix('tenant')->name('tenant.')->group(function () {
    Route::get('/dashboard', \App\Http\Controllers\Tenant\DashboardController::class . '@index')->name('dashboard');

    Route::get('/site', \App\Http\Controllers\Tenant\SiteController::class . '@edit')->name('site.edit');
    Route::put('/site', \App\Http\Controllers\Tenant\SiteController::class . '@update')->name('site.update');
    Route::post('/site/publish', \App\Http\Controllers\Tenant\SiteController::class . '@publish')->name('site.publish');

    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', \App\Http\Controllers\Tenant\ProductController::class . '@index')->name('index');
        Route::post('/', \App\Http\Controllers\Tenant\ProductController::class . '@store')->name('store');
        Route::put('/{product}', \App\Http\Controllers\Tenant\ProductController::class . '@update')->name('update');
        Route::delete('/{product}', \App\Http\Controllers\Tenant\ProductController::class . '@destroy')->name('destroy');
    });

    Route::prefix('contacts')->name('contacts.')->group(function () {
        Route::get('/', \App\Http\Controllers\Tenant\DashboardController::class . '@contactsIndex')->name('index');
        Route::patch('/{contact}', \App\Http\Controllers\Tenant\ContactController::class . '@update')->name('update');
    });

    Route::prefix('qr-codes')->name('qrcodes.')->group(function () {
        Route::get('/', \App\Http\Controllers\Tenant\QrCodeController::class . '@index')->name('index');
        Route::post('/', \App\Http\Controllers\Tenant\QrCodeController::class . '@store')->name('store');
        Route::get('/{qr}/download', \App\Http\Controllers\Tenant\QrCodeController::class . '@download')->name('download');
    });

    Route::prefix('subscription')->name('subscription.')->group(function () {
        Route::get('/', \App\Http\Controllers\Tenant\SubscriptionController::class . '@show')->name('show');
        Route::post('/renew', \App\Http\Controllers\Tenant\SubscriptionController::class . '@renew')->name('renew');
    });
});

// ============================================================
// ADMIN ROUTES (protected)
// ============================================================

Route::middleware(['auth', 'admin.access'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/pricing', \App\Http\Controllers\Admin\PricingController::class . '@index')->name('pricing.index');
    Route::post('/pricing/{planId}/{regionCode}', \App\Http\Controllers\Admin\PricingController::class . '@update')->name('pricing.update');
    Route::get('/pricing/stats', \App\Http\Controllers\Admin\PricingController::class . '@stats')->name('pricing.stats');

    Route::get('/social', \App\Http\Controllers\Admin\SocialController::class . '@index')->name('social.index');
    Route::post('/social', \App\Http\Controllers\Admin\SocialController::class . '@store')->name('social.store');
    Route::delete('/social/{socialAccount}', \App\Http\Controllers\Admin\SocialController::class . '@destroy')->name('social.destroy');
    Route::post('/social/{socialAccount}/toggle', \App\Http\Controllers\Admin\SocialController::class . '@toggle')->name('social.toggle');
});
