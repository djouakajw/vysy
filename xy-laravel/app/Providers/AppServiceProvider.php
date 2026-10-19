<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Cache\RateLimitingEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return [
                \Illuminate\Cache\RateLimitingLimit::perMinute(60)->by($request->user()?->id ?: $request->ip()),
            ];
        });

        RateLimiter::for('subdomain-check', function (Request $request) {
            return [
                \Illuminate\Cache\RateLimitingLimit::perMinute(30)->by($request->ip()),
            ];
        });

        RateLimiter::for('payment', function (Request $request) {
            return [
                \Illuminate\Cache\RateLimitingLimit::perMinute(10)->by($request->user()?->id ?: $request->ip()),
            ];
        });

        // Gate d'accès admin : seuls les utilisateurs avec is_admin = true ou les super-admins
        Gate::define('admin.access', function (User $user) {
            return $user && ($user->is_admin ?? false);
        });
    }
}
