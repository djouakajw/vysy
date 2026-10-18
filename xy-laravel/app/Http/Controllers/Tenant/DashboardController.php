<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tenant = $user->primaryTenant ?? $user->tenants()->first();

        if (!$tenant) {
            return view('tenant.dashboard-empty', ['user' => $user]);
        }

        $stats = [
            'visits' => $tenant->settings['stats_visits'] ?? 0,
            'whatsapp_clicks' => $tenant->settings['stats_whatsapp'] ?? 0,
            'phone_clicks' => $tenant->settings['stats_phone'] ?? 0,
            'external_clicks' => $tenant->settings['stats_external'] ?? 0,
            'qr_scans' => $tenant->qrCodes()->sum('scans_count'),
            'contacts' => $tenant->contacts()->count(),
            'products' => $tenant->products()->count(),
        ];

        $recentContacts = $tenant->contacts()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $subscription = $tenant->activeSubscription();

        return view('tenant.dashboard', compact(
            'tenant',
            'stats',
            'recentContacts',
            'subscription'
        ));
    }

    public function contactsIndex(Request $request)
    {
        $this->authorize('viewAny', Contact::class);

        $tenant = $request->user()->primaryTenant ?? $request->user()->tenants()->first();
        $contacts = $tenant->contacts()->orderBy('created_at', 'desc')->paginate(20);

        return view('tenant.contacts', compact('tenant', 'contacts'));
    }
}
