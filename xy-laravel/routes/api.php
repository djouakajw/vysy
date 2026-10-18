<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SubdomainController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CreationController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\GeniusPayController;

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

// GeniusPay webhooks (no auth - verified by signature)
Route::post('/geniuspay/webhook', [GeniusPayController::class, 'webhook']);
Route::get('/geniuspay/return', [GeniusPayController::class, 'return'])->name('geniuspay.return');
