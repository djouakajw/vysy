<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class SubdomainController extends Controller
{
    public function check(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2|max:63',
        ]);

        $raw = $request->string('name')->trim();
        $slug = Str::slug($raw);

        // Reject reserved names
        $reserved = ['admin', 'app', 'api', 'support', 'www', 'xy', 'auth', 'dashboard', 'login', 'signup', 'blog', 'help', 'contact', 'about', 'shop', 'store', 'mail', 'ftp'];
        if (in_array($slug, $reserved, true)) {
            return response()->json([
                'available' => false,
                'slug' => $slug,
                'reason' => 'reserved',
                'message' => 'Ce nom est réservé et ne peut pas être utilisé.',
                'suggestions' => $this->suggest($slug),
            ], 409);
        }

        // Format validation
        if (!preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/', $slug)) {
            return response()->json([
                'available' => false,
                'slug' => $slug,
                'reason' => 'invalid',
                'message' => 'Ce nom contient des caractères interdits ou est trop long.',
                'suggestions' => [],
            ], 422);
        }

        // Availability check
        $taken = Tenant::where('slug', $slug)->exists();

        if ($taken) {
            return response()->json([
                'available' => false,
                'slug' => $slug,
                'reason' => 'taken',
                'message' => 'Ce nom est déjà utilisé.',
                'suggestions' => $this->suggest($slug),
            ], 409);
        }

        return response()->json([
            'available' => true,
            'slug' => $slug,
            'full_url' => 'https://' . $slug . '.' . config('app.domain', 'xy-cm.com'),
            'message' => 'Ce nom est disponible.',
        ]);
    }

    protected function suggest(string $slug): array
    {
        $suffixes = ['boutique', 'shop', 'store', 'pro', '2024', '2025', 'cm', 'online', 'site'];
        $suggestions = [];

        foreach ($suffixes as $suffix) {
            $candidate = $slug . '-' . $suffix;
            if (!Tenant::where('slug', $candidate)->exists()) {
                $suggestions[] = $candidate;
            }
            if (count($suggestions) >= 5) {
                break;
            }
        }

        return $suggestions;
    }
}
