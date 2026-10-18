<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdvertisingAccount;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdvertisingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tenantId = $request->query('tenant_id', $user->primaryTenant?->id);

        $tenant = Tenant::where('id', $tenantId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Premium feature: only plans >= 5000 FCFA (prime, prime-plus)
        $premiumPlans = ['prime', 'prime-plus'];
        $canAccessAds = in_array($tenant->plan, $premiumPlans);

        $advertisingAccounts = AdvertisingAccount::where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $platforms = AdvertisingAccount::PLATFORMS;

        return response()->json([
            'tenant' => $tenant,
            'can_access_ads' => $canAccessAds,
            'min_plan_price' => AdvertisingAccount::minPlanPrice(),
            'current_plan' => $tenant->plan ?? null,
            'advertising_accounts' => $canAccessAds ? $advertisingAccounts : [],
            'platforms' => $platforms,
            'upgrade_message' => $canAccessAds ? null : 'Cette fonctionnalité est réservée aux abonnements à partir de 5 000 FCFA/mois.',
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required|exists:tenants,id',
            'platform' => 'required|in:' . implode(',', array_keys(AdvertisingAccount::PLATFORMS)),
            'account_id' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'pixel_id' => 'nullable|string|max:255',
            'conversion_id' => 'nullable|string|max:255',
            'access_token' => 'nullable|string',
            'config' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tenant = Tenant::where('id', $request->tenant_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Premium check: only plans >= 5000 FCFA
        $premiumPlans = ['prime', 'prime-plus'];
        if (!in_array($tenant->plan, $premiumPlans)) {
            return response()->json([
                'error' => 'plan_required',
                'message' => 'Les identifiants publicitaires et le suivi des campagnes sont réservés aux abonnements à partir de 5 000 FCFA/mois.',
                'min_plan_price' => 5000,
                'current_plan' => $tenant->plan ?? null,
                'upgrade_message' => 'Passez à un plan Prime (5 000 FCFA) ou Panneau (7 000 FCFA) pour activer la publicité et le suivi des campagnes.',
            ], 403);
        }

        $advertisingAccount = AdvertisingAccount::updateOrCreate(
            ['tenant_id' => $tenant->id, 'platform' => $request->platform, 'account_id' => $request->account_id],
            [
                'account_name' => $request->account_name,
                'pixel_id' => $request->pixel_id,
                'conversion_id' => $request->conversion_id,
                'access_token' => $request->access_token,
                'config' => $request->config,
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'advertising_account' => $advertisingAccount,
        ]);
    }

    public function update(Request $request, AdvertisingAccount $advertisingAccount)
    {
        $user = $request->user();

        if ($advertisingAccount->tenant->user_id !== $user->id) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        // Verify premium access
        $premiumPlans = ['prime', 'prime-plus'];
        if (!in_array($advertisingAccount->tenant->plan, $premiumPlans)) {
            return response()->json([
                'error' => 'plan_required',
                'message' => 'Cette fonctionnalité est réservée aux abonnements à partir de 5 000 FCFA/mois.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'account_name' => 'sometimes|string|max:255',
            'pixel_id' => 'sometimes|string|max:255',
            'conversion_id' => 'sometimes|string|max:255',
            'access_token' => 'sometimes|string',
            'config' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $advertisingAccount->update($request->only([
            'account_name', 'pixel_id', 'conversion_id', 'access_token', 'config', 'is_active',
        ]));

        return response()->json([
            'success' => true,
            'advertising_account' => $advertisingAccount,
        ]);
    }

    public function destroy(Request $request, AdvertisingAccount $advertisingAccount)
    {
        $user = $request->user();

        if ($advertisingAccount->tenant->user_id !== $user->id) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        // Verify premium access
        $premiumPlans = ['prime', 'prime-plus'];
        if (!in_array($advertisingAccount->tenant->plan, $premiumPlans)) {
            return response()->json([
                'error' => 'plan_required',
                'message' => 'Cette fonctionnalité est réservée aux abonnements à partir de 5 000 FCFA/mois.',
            ], 403);
        }

        $advertisingAccount->delete();

        return response()->json(['success' => true]);
    }

    public function toggle(Request $request, AdvertisingAccount $advertisingAccount)
    {
        $user = $request->user();

        if ($advertisingAccount->tenant->user_id !== $user->id) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        // Verify premium access
        $premiumPlans = ['prime', 'prime-plus'];
        if (!in_array($advertisingAccount->tenant->plan, $premiumPlans)) {
            return response()->json([
                'error' => 'plan_required',
                'message' => 'Cette fonctionnalité est réservée aux abonnements à partir de 5 000 FCFA/mois.',
            ], 403);
        }

        $advertisingAccount->update(['is_active' => !$advertisingAccount->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $advertisingAccount->is_active,
        ]);
    }
}
