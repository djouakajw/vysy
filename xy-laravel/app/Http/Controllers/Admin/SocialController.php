<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdvertisingAccount;
use App\Models\SocialAccount;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SocialController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin.access');

        $socialAccounts = SocialAccount::with('tenant')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $platforms = SocialAccount::PLATFORMS;

        return view('admin.social.index', compact('socialAccounts', 'platforms'));
    }

    public function store(Request $request)
    {
        $this->authorize('admin.access');

        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required|exists:tenants,id',
            'platform' => 'required|in:meta,google,tiktok,whatsapp,instagram',
            'account_id' => 'required|string',
            'account_name' => 'required|string',
            'access_token' => 'nullable|string',
            'refresh_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tenant = Tenant::find($request->tenant_id);

        // Les réseaux sociaux sont accessibles à TOUS les plans (pas de restriction)
        $socialAccount = SocialAccount::updateOrCreate(
            ['tenant_id' => $tenant->id, 'platform' => $request->platform, 'account_id' => $request->account_id],
            [
                'account_name' => $request->account_name,
                'access_token' => $request->access_token,
                'refresh_token' => $request->refresh_token,
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'social_account' => $socialAccount,
        ]);
    }

    public function destroy(SocialAccount $socialAccount)
    {
        $this->authorize('admin.access');

        $socialAccount->delete();

        return response()->json(['success' => true]);
    }

    public function toggle(SocialAccount $socialAccount)
    {
        $this->authorize('admin.access');

        $socialAccount->update(['is_active' => !$socialAccount->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $socialAccount->is_active,
        ]);
    }

    // ============================================================
    // GESTION DES COMPTES PUBLICITAIRES (Admin)
    // ============================================================

    public function advertisingIndex(Request $request)
    {
        $this->authorize('admin.access');

        $advertisingAccounts = AdvertisingAccount::with('tenant')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $platforms = AdvertisingAccount::PLATFORMS;

        return view('admin.advertising.index', compact('advertisingAccounts', 'platforms'));
    }

    public function advertisingToggle(AdvertisingAccount $advertisingAccount)
    {
        $this->authorize('admin.access');

        $advertisingAccount->update(['is_active' => !$advertisingAccount->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $advertisingAccount->is_active,
        ]);
    }

    public function advertisingDestroy(AdvertisingAccount $advertisingAccount)
    {
        $this->authorize('admin.access');

        $advertisingAccount->delete();

        return response()->json(['success' => true]);
    }
}
