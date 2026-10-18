<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\SiteController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\ContactController;
use App\Http\Controllers\Tenant\QrCodeController;
use App\Http\Controllers\Tenant\Subscription as TenantSubscriptionController;

// Tenant public site routes (resolved by subdomain middleware)
Route::middleware(['tenant.subdomain'])->group(function () {
    Route::get('/', [SiteController::class, 'show'])->name('tenant.site');
    Route::get('/qr/{qr}', [SiteController::class, 'qrRedirect'])->name('tenant.qr');
});

// Tenant dashboard (authenticated owner)
Route::middleware(['auth', 'tenant.owner'])->prefix('tenant')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');
    Route::get('/site', [SiteController::class, 'edit'])->name('tenant.site.edit');
    Route::put('/site', [SiteController::class, 'update'])->name('tenant.site.update');
    Route::post('/publish', [SiteController::class, 'publish'])->name('tenant.site.publish');

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('tenant.products.index');
        Route::post('/', [ProductController::class, 'store'])->name('tenant.products.store');
        Route::put('/{product}', [ProductController::class, 'update'])->name('tenant.products.update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('tenant.products.destroy');
    });

    Route::prefix('contacts')->group(function () {
        Route::get('/', [ContactController::class, 'index'])->name('tenant.contacts.index');
        Route::patch('/{contact}', [ContactController::class, 'update'])->name('tenant.contacts.update');
    });

    Route::prefix('qr-codes')->group(function () {
        Route::get('/', [QrCodeController::class, 'index'])->name('tenant.qrcodes.index');
        Route::post('/', [QrCodeController::class, 'store'])->name('tenant.qrcodes.store');
        Route::get('/{qr}/download', [QrCodeController::class, 'download'])->name('tenant.qrcodes.download');
    });

    Route::prefix('subscription')->group(function () {
        Route::get('/', [TenantSubscriptionController::class, 'show'])->name('tenant.subscription.show');
        Route::post('/renew', [TenantSubscriptionController::class, 'renew'])->name('tenant.subscription.renew');
    });
});

// Public contact form submission (no auth needed, tenant resolved)
Route::middleware(['tenant.subdomain'])->post('/contact', [ContactController::class, 'store'])->name('tenant.contact.store');
