<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AdvertisingAccount;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdvertisingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tenant = $user->primaryTenant ?? $user->tenants()->first();

        if (!$tenant) {
            return redirect()->route('marketing.dashboard')->with('error', 'Aucune boutique trouvée.');
        }

        // Premium feature: only plans >= 5000 FCFA (prime, prime-plus)
        $premiumPlans = ['prime', 'prime-plus'];
        $canAccessAds = in_array($tenant->plan, $premiumPlans);

        $advertisingAccounts = $canAccessAds
            ? AdvertisingAccount::where('tenant_id', $tenant->id)
                ->orderBy('created_at', 'desc')
                ->get()
            : [];

        $platforms = AdvertisingAccount::PLATFORMS;

        return view('tenant.advertising.index', compact(
            'tenant',
            'advertisingAccounts',
            'platforms',
            'canAccessAds'
        ));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $tenant = $user->primaryTenant ?? $user->tenants()->first();

        if (!$tenant) {
            return redirect()->route('marketing.dashboard')->with('error', 'Aucune boutique trouvée.');
        }

        // Premium check: only plans >= 5000 FCFA
        $premiumPlans = ['prime', 'prime-plus'];
        if (!in_array($tenant->plan, $premiumPlans)) {
            return redirect()->route('tenant.advertising.index')->with('error', 'Les identifiants publicitaires sont réservés aux abonnements à partir de 5 000 FCFA/mois.');
        }

        $validator = Validator::make($request->all(), [
            'platform' => 'required|in:' . implode(',', array_keys(AdvertisingAccount::PLATFORMS)),
            'account_id' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'pixel_id' => 'nullable|string|max:255',
            'conversion_id' => 'nullable|string|max:255',
            'access_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        AdvertisingAccount::updateOrCreate(
            ['tenant_id' => $tenant->id, 'platform' => $request->platform, 'account_id' => $request->account_id],
            [
                'account_name' => $request->account_name,
                'pixel_id' => $request->pixel_id,
                'conversion_id' => $request->conversion_id,
                'access_token' => $request->access_token,
                'is_active' => true,
            ]
        );

        return redirect()->route('tenant.advertising.index')->with('success', 'Compte publicitaire ajouté avec succès.');
    }

    public function update(Request $request, AdvertisingAccount $advertisingAccount)
    {
        $user = $request->user();

        if ($advertisingAccount->tenant->user_id !== $user->id) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        // Premium check
        $premiumPlans = ['prime', 'prime-plus'];
        if (!in_array($advertisingAccount->tenant->plan, $premiumPlans)) {
            return response()->json(['error' => 'plan_required'], 403);
        }

        $validator = Validator::make($request->all(), [
            'account_name' => 'sometimes|string|max:255',
            'pixel_id' => 'sometimes|string|max:255',
            'conversion_id' => 'sometimes|string|max:255',
            'access_token' => 'sometimes|string',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $advertisingAccount->update($request->only([
            'account_name', 'pixel_id', 'conversion_id', 'access_token', 'is_active',
        ]));

        return redirect()->route('tenant.advertising.index')->with('success', 'Compte publicitaire mis à jour.');
    }

    public function destroy(Request $request, AdvertisingAccount $advertisingAccount)
    {
        $user = $request->user();

        if ($advertisingAccount->tenant->user_id !== $user->id) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        // Premium check
        $premiumPlans = ['prime', 'prime-plus'];
        if (!in_array($advertisingAccount->tenant->plan, $premiumPlans)) {
            return response()->json(['error' => 'plan_required'], 403);
        }

        $advertisingAccount->delete();

        return redirect()->route('tenant.advertising.index')->with('success', 'Compte publicitaire supprimé.');
    }
}
