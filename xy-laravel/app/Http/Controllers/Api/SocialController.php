<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SocialController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tenantId = $request->query('tenant_id', $user->primaryTenant?->id);

        $tenant = Tenant::where('id', $tenantId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $socialAccounts = SocialAccount::where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $platforms = SocialAccount::PLATFORMS;

        return response()->json([
            'tenant' => $tenant,
            'social_accounts' => $socialAccounts,
            'platforms' => $platforms,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required|exists:tenants,id',
            'platform' => 'required|in:' . implode(',', array_keys(SocialAccount::PLATFORMS)),
            'account_id' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'url' => 'nullable|url|max:500',
            'phone' => 'nullable|string|max:50',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tenant = Tenant::where('id', $request->tenant_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Social networks: accessible to ALL plans, no restriction
        $socialAccount = SocialAccount::updateOrCreate(
            ['tenant_id' => $tenant->id, 'platform' => $request->platform, 'account_id' => $request->account_id],
            [
                'account_name' => $request->account_name,
                'access_token' => $request->url ?? null,
                'refresh_token' => $request->phone ?? null,
                'scopes' => null,
                'is_active' => $request->boolean('is_active', true),
            ]
        );

        return response()->json([
            'success' => true,
            'social_account' => $socialAccount,
        ]);
    }

    public function update(Request $request, SocialAccount $socialAccount)
    {
        $user = $request->user();

        // Verify ownership
        if ($socialAccount->tenant->user_id !== $user->id) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'account_name' => 'sometimes|string|max:255',
            'url' => 'nullable|url|max:500',
            'phone' => 'nullable|string|max:50',
            'display_order' => 'sometimes|integer|min:0',
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
        if ($request->has('display_order')) {
            // Note: display_order would need a column; storing in scopes for now
            $data['scopes'] = array_merge($socialAccount->scopes ?? [], ['display_order' => $request->display_order]);
        }
        if ($request->has('is_active')) {
            $data['is_active'] = $request->is_active;
        }

        $socialAccount->update($data);

        return response()->json([
            'success' => true,
            'social_account' => $socialAccount,
        ]);
    }

    public function destroy(Request $request, SocialAccount $socialAccount)
    {
        $user = $request->user();

        if ($socialAccount->tenant->user_id !== $user->id) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        $socialAccount->delete();

        return response()->json(['success' => true]);
    }

    public function toggle(Request $request, SocialAccount $socialAccount)
    {
        $user = $request->user();

        if ($socialAccount->tenant->user_id !== $user->id) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        $socialAccount->update(['is_active' => !$socialAccount->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $socialAccount->is_active,
        ]);
    }

    public function reorder(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:social_accounts,id',
            'orders.*.display_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        foreach ($request->orders as $order) {
            $socialAccount = SocialAccount::find($order['id']);
            if ($socialAccount && $socialAccount->tenant->user_id === $user->id) {
                $socialAccount->update([
                    'scopes' => array_merge($socialAccount->scopes ?? [], ['display_order' => $order['display_order']]),
                ]);
            }
        }

        return response()->json(['success' => true]);
    }
}
