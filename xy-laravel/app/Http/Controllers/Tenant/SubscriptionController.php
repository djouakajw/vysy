<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SubscriptionController extends Controller
{
    public function show(Request $request)
    {
        $this->authorize('viewAny', Subscription::class);

        $tenant = $request->user()->primaryTenant ?? $request->user()->tenants()->first();
        $subscription = $tenant->activeSubscription();
        $payments = $tenant->payments()->orderBy('created_at', 'desc')->get();

        return view('tenant.subscription', compact('tenant', 'subscription', 'payments'));
    }

    public function renew(Request $request)
    {
        $this->authorize('update', Subscription::class);

        // Redirect to subscription creation flow
        return redirect()->route('subscription.create', ['tenant_id' => $tenant->id])
            ->with('info', 'Renouvellement de votre abonnement.');
    }
}
