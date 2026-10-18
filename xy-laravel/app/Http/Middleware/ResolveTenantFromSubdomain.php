<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantFromSubdomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $appDomain = config('app.domain', 'xy-cm.com');

        // Skip tenant resolution for API, auth, and www routes
        if ($request->is('api/*') || $request->is('auth/*') || $request->is('geniuspay/*')) {
            $request->tenant = null;
            return $next($request);
        }

        // Skip tenant resolution for www, app, api, auth routes
        $excluded = ['www', 'app', 'api', 'auth', 'dashboard', 'mail', 'ftp'];
        $subdomain = $request->input('tenant_subdomain');

        if ($host === $appDomain || in_array(explode('.', $host)[0] ?? '', $excluded, true)) {
            $request->tenant = null;
            return $next($request);
        }

        // Extract subdomain from host (e.g., monboutique.xy-cm.com -> monboutique)
        $parts = explode('.', $host);
        if (count($parts) >= 3 && ends_with($host, '.' . $appDomain)) {
            $subdomain = array_shift($parts);
            $tenant = Tenant::where('slug', $subdomain)
                ->where('status', 'published')
                ->first();

            if (!$tenant) {
                abort(404, 'Boutique introuvable.');
            }

            $request->tenant = $tenant;
            config(['app.tenant' => $tenant]);
            view()->share('tenant', $tenant);

            return $next($request);
        }

        $request->tenant = null;
        return $next($request);
    }
}
