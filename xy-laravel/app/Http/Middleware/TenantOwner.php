<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class TenantOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $tenant = $request->tenant;

        if (!$user) {
            abort(401, 'Authentification requise.');
        }

        $owned = $user->tenants()->where('id', $tenant?->id)->exists()
            || $user->primaryTenant?->id === ($tenant?->id);

        if (!$owned) {
            abort(403, 'Vous n\'êtes pas le propriétaire de cette boutique.');
        }

        return $next($request);
    }
}
