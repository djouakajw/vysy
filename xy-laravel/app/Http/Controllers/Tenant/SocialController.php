<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SocialController extends Controller
{
    public function index(Request $request)
    {
        $tenant = $request->user()->primaryTenant ?? $request->user()->tenants()->first();

        if (!$tenant) {
            return redirect()->route('marketing.dashboard')->with('error', 'Aucune boutique trouvée.');
        }

        $socialAccounts = SocialAccount::where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $platforms = SocialAccount::PLATFORMS;

        return view('tenant.social.index', compact('tenant', 'socialAccounts', 'platforms'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $tenant = $user->primaryTenant ?? $user->tenants()->first();

        if (!$tenant) {
            return response()->json(['error' => 'tenant_not_found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'platform' => 'required|in:' . implode(',', array_keys(SocialAccount::PLATFORMS)),
            'account_id' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'url' => 'nullable|url|max:500',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Social networks: accessible to ALL plans
        SocialAccount::updateOrCreate(
            ['tenant_id' => $tenant->id, 'platform' => $request->platform, 'account_id' => $request->account_id],
            [
                'account_name' => $request->account_name,
                'access_token' => $request->url ?? null,
                'refresh_token' => $request->phone ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]
        );

        return redirect()->route('tenant.social.index')->with('success', 'Réseau social ajouté avec succès.');
    }

    public function update(Request $request, SocialAccount $socialAccount)
    {
        $user = $request->user();

        if ($socialAccount->tenant->user_id !== $user->id) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'account_name' => 'sometimes|string|max:255',
            'url' => 'nullable|url|max:500',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [];
        if ($request->has('account_name')) {
            $data['account_name'] = $request->account_name;
        }
        if ($request->has('url')) {
            $data['access_token'] = $request->url;
        }
        if ($request->has('phone')) {
            $data['refresh_token'] = $request->phone;
        }
        if ($request->has('is_active')) {
            $data['is_active'] = $request->is_active;
        }

        $socialAccount->update($data);

        return redirect()->route('tenant.social.index')->with('success', 'Réseau social mis à jour.');
    }

    public function destroy(Request $request, SocialAccount $socialAccount)
    {
        $user = $request->user();

        if ($socialAccount->tenant->user_id !== $user->id) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        $socialAccount->delete();

        return redirect()->route('tenant.social.index')->with('success', 'Réseau social supprimé.');
    }
}
