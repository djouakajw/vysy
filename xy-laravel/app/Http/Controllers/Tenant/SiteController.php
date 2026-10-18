<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\Page;
use App\Models\QrCode;
use App\Models\SocialAccount;
use App\Models\AdvertisingAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class SiteController extends Controller
{
    public function show(Request $request, Tenant $tenant)
    {
        // If not published, only owner can preview
        if (!$tenant->isPublished()) {
            if (!Auth::check() || $tenant->user_id !== Auth::id()) {
                abort(403, 'Ce site n\'est pas encore publié.');
            }
        }

        $products = Product::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $pages = Page::where('tenant_id', $tenant->id)
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->get();

        $primaryAction = $tenant->settings['primary_action'] ?? 'whatsapp';
        $actionValue = $tenant->settings['action_value'] ?? '';
        $deliveryModes = $tenant->settings['delivery_modes'] ?? [];

        // Social networks (accessible to all plans)
        $socialAccounts = SocialAccount::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('created_at', 'asc')
            ->get();

        // Advertising accounts (premium plans only)
        $advertisingAccounts = AdvertisingAccount::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('created_at', 'asc')
            ->get();

        // SEO data
        $seoTitle = $tenant->siteName ?? $tenant->name;
        $seoDescription = $tenant->settings['seo_description'] ?? $tenant->description ?? 'Découvrez ' . $tenant->name . ' sur ' . $tenant->public_url;
        $seoKeywords = $tenant->settings['seo_keywords'] ?? $tenant->name . ', ' . $tenant->city . ', ' . $tenant->country;
        $canonicalUrl = $tenant->public_url;
        $ogImage = $tenant->settings['og_image'] ?? ($tenant->logo_url ?? null);
        $twitterCard = 'summary_large_image';

        return view('tenant.site', compact(
            'tenant',
            'products',
            'pages',
            'primaryAction',
            'actionValue',
            'deliveryModes',
            'socialAccounts',
            'advertisingAccounts',
            'seoTitle',
            'seoDescription',
            'seoKeywords',
            'canonicalUrl',
            'ogImage',
            'twitterCard'
        ));
    }

    public function qrRedirect(Request $request, QrCode $qr)
    {
        $qr->incrementScan();
        return redirect($qr->target_url);
    }

    public function edit(Request $request)
    {
        $tenant = $request->user()->primaryTenant ?? $request->user()->tenants()->first();
        if (!$tenant) {
            return redirect()->route('marketing.dashboard')->with('error', 'Aucune boutique trouvée.');
        }

        $products = Product::where('tenant_id', $tenant->id)
            ->orderBy('sort_order')
            ->get();

        return view('tenant.edit', compact('tenant', 'products'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $this->authorize('update', $tenant);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'language' => 'nullable|string|max:5',
            'primary_color' => 'nullable|string',
            'settings' => 'nullable|array',
        ]);

        $tenant->update([
            'name' => $request->name,
            'description' => $request->description,
            'city' => $request->city,
            'country' => $request->country,
            'language' => $request->language ?? $tenant->language,
            'primary_color' => $request->primary_color ?? $tenant->primary_color,
            'settings' => array_merge($tenant->settings ?? [], $request->settings ?? []),
        ]);

        return back()->with('success', 'Votre boutique a été mise à jour.');
    }

    public function publish(Request $request, Tenant $tenant)
    {
        $this->authorize('publish', $tenant);

        // Check active subscription
        $subscription = $tenant->activeSubscription();
        if (!$subscription || !$subscription->isActive()) {
            return back()->withErrors(['subscription' => 'Vous devez avoir un abonnement actif pour publier votre site.']);
        }

        // Re-verify subdomain availability
        if (Tenant::where('slug', $tenant->slug)
            ->where('id', '!=', $tenant->id)
            ->exists()) {
            return back()->withErrors(['slug' => 'Ce sous-domaine n\'est plus disponible.']);
        }

        $tenant->update(['status' => 'published']);

        return redirect()->route('tenant.dashboard')->with('success', 'Votre site est maintenant publié !');
    }
}
