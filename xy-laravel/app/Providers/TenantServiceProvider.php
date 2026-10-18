<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;

class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        View::composer('tenant.*', function ($view) {
            $tenant = request()->tenant;
            $view->with('tenant', $tenant);
        });

        View::share('appName', 'XY');
        View::share('appDomain', config('app.domain', 'xy-cm.com'));
    }
}
