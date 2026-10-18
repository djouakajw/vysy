<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SubdomainController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CreationController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\GeniusPayController;
use App\Http\Controllers\Api\SocialController;
use App\Http\Controllers\Api\AdvertisingController;

// Public entry: test subdomain availability
Route::get('/api/subdomain/check', [SubdomainController::class, 'check'])
    ->middleware('throttle:subdomain-check');

// Auth
Route::prefix('auth')->group(function () {
    Route::post('/email', [AuthController::class, 'emailLogin']);
    Route::post('/google', [AuthController::class, 'googleLogin']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// Creation wizard (requires auth)
Route::middleware('auth:sanctum')->prefix('creation')->group(function () {
    Route::get('/draft', [CreationController::class, 'getDraft']);
    Route::post('/draft', [CreationController::class, 'saveDraft']);
    Route::post('/step/{step}', [CreationController::class, 'saveStep']);
    Route::post('/preview', [CreationController::class, 'preview']);
});

// Subscriptions & GeniusPay
Route::middleware('auth:sanctum')->prefix('subscription')->group(function () {
    Route::get('/plans', [SubscriptionController::class, 'plans']);
    Route::post('/create-payment', [SubscriptionController::class, 'createPayment']);
});

// Social networks - accessible to ALL plans
Route::middleware('auth:sanctum')->prefix('social')->group(function () {
    Route::get('/', [SocialController::class, 'index']);
    Route::post('/', [SocialController::class, 'store']);
    Route::put('/{socialAccount}', [SocialController::class, 'update']);
    Route::delete('/{socialAccount}', [SocialController::class, 'destroy']);
    Route::post('/{socialAccount}/toggle', [SocialController::class, 'toggle']);
    Route::post('/reorder', [SocialController::class, 'reorder']);
});

// Advertising accounts - reserved to plans >= 5000 FCFA
Route::middleware('auth:sanctum')->prefix('advertising')->group(function () {
    Route::get('/', [AdvertisingController::class, 'index']);
    Route::post('/', [AdvertisingController::class, 'store']);
    Route::put('/{advertisingAccount}', [AdvertisingController::class, 'update']);
    Route::delete('/{advertisingAccount}', [AdvertisingController::class, 'destroy']);
    Route::post('/{advertisingAccount}/toggle', [AdvertisingController::class, 'toggle']);
});

// GeniusPay webhooks (no auth - verified by signature)
Route::post('/geniuspay/webhook', [GeniusPayController::class, 'webhook']);
Route::get('/geniuspay/return', [GeniusPayController::class, 'return'])->name('geniuspay.return');
