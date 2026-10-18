<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegionalPricing;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PricingController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin.access');

        $plans = ['entry', 'business', 'prime', 'prime-plus'];
        $regions = RegionalPricing::REGIONS;

        $pricing = [];
        foreach ($plans as $planId) {
            $pricing[$planId] = [];
            foreach ($regions as $regionCode => $regionName) {
                $rp = RegionalPricing::where('plan_id', $planId)
                    ->where('region_code', $regionCode)
                    ->where('is_active', true)
                    ->first();
                $pricing[$planId][$regionCode] = $rp ?: [
                    'plan_id' => $planId,
                    'region_code' => $regionCode,
                    'region_name' => $regionName,
                    'price_monthly' => null,
                    'price_yearly' => null,
                    'currency' => 'XOF',
                    'original_price_monthly' => null,
                    'original_price_yearly' => null,
                    'discount_percent' => 0,
                ];
            }
        }

        return view('admin.pricing.index', compact('plans', 'regions', 'pricing'));
    }

    public function update(Request $request, $planId, $regionCode)
    {
        $this->authorize('admin.access');

        $validator = Validator::make($request->all(), [
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'original_price_monthly' => 'nullable|numeric|min:0',
            'original_price_yearly' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        RegionalPricing::updateOrCreate(
            ['plan_id' => $planId, 'region_code' => $regionCode],
            [
                'region_name' => RegionalPricing::REGIONS[$regionCode] ?? $regionCode,
                'price_monthly' => $request->price_monthly,
                'price_yearly' => $request->price_yearly,
                'currency' => $request->currency,
                'original_price_monthly' => $request->original_price_monthly,
                'original_price_yearly' => $request->original_price_yearly,
                'discount_percent' => $request->discount_percent ?? 0,
                'is_active' => $request->boolean('is_active', true),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Prix mis à jour avec succès']);
    }

    public function stats(Request $request)
    {
        $this->authorize('admin.access');

        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('status', 'published')->count();
        $draftTenants = Tenant::where('status', 'draft')->count();
        $suspendedTenants = Tenant::where('status', 'suspended')->count();

        $planDistribution = Tenant::select('plan', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->whereNotNull('plan')
            ->groupBy('plan')
            ->get();

        $recentPayments = \App\Models\Payment::with('tenant')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.pricing.stats', compact(
            'totalTenants',
            'activeTenants',
            'draftTenants',
            'suspendedTenants',
            'planDistribution',
            'recentPayments'
        ));
    }
}
