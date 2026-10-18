<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->tenant;

        if (!$tenant) {
            return $next($request);
        }

        // Allow access to public tenant site even if not published during creation preview
        // Admin routes handled separately

        return $next($request);
    }
}
