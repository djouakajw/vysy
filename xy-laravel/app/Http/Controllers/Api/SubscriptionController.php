<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Payment;
use App\Services\GeniusPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    public function plans()
    {
        $plans = [
            [
                'id' => 'free',
                'name' => 'Découverte',
                'price' => 0,
                'currency' => 'XOF',
                'period' => 'month',
                'sites_allowed' => 1,
                'products_limit' => 5,
                'features' => ['sous-domaine', 'qr-code', 'whatsapp-bouton'],
                'description' => 'Pour tester votre boutique gratuitement',
            ],
            [
                'id' => 'starter',
                'name' => 'Starter',
                'price' => 2000,
                'currency' => 'XOF',
                'period' => 'month',
                'sites_allowed' => 1,
                'products_limit' => 50,
                'features' => ['sous-domaine-personnalise', 'qr-code', 'whatsapp-bouton', 'appel', 'email', 'stats-basiques'],
                'description' => 'Pour les petits commerçants',
            ],
            [
                'id' => 'business',
                'name' => 'Business',
                'price' => 5000,
                'currency' => 'XOF',
                'period' => 'month',
                'sites_allowed' => 3,
                'products_limit' => 500,
                'features' => ['sous-domaine-personnalise', 'qr-code', 'whatsapp-bouton', 'appel', 'email', 'lien-externe', 'stats-avancees', 'marketing'],
                'description' => 'Pour les boutiques en croissance',
            ],
            [
                'id' => 'premium',
                'name' => 'Premium',
                'price' => 10000,
                'currency' => 'XOF',
                'period' => 'month',
                'sites_allowed' => 10,
                'products_limit' => null,
                'features' => ['tout-inclus', 'prioritaire', 'support', 'ia', 'campagnes', 'domaine-personnalise'],
                'description' => 'Pour les grandes activités',
            ],
        ];

        // Annual discounts: 1 month free for 12 months commitment, etc.
        $annualDiscounts = [
            'starter' => ['months' => 12, 'discount_percent' => 17, 'label' => '2 mois offerts'],
            'business' => ['months' => 12, 'discount_percent' => 20, 'label' => '2 mois offerts'],
            'premium' => ['months' => 12, 'discount_percent' => 25, 'label' => '3 mois offerts'],
        ];

        return response()->json([
            'plans' => $plans,
            'annual_discounts' => $annualDiscounts,
            'default_currency' => 'XOF',
        ]);
    }

    public function createPayment(Request $request, GeniusPayService $geniusPay)
    {
        $validator = Validator::make($request->all(), [
            'plan' => 'required|string|in:starter,business,premium',
            'period' => 'nullable|string|in:month,year',
            'tenant_id' => 'required|exists:tenants,id',
            'currency' => 'nullable|string|in:XOF,XAF,USD,EUR',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $tenant = Tenant::where('id', $request->tenant_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $plans = [
            'starter' => ['price' => 2000, 'sites_allowed' => 1, 'products_limit' => 50],
            'business' => ['price' => 5000, 'sites_allowed' => 3, 'products_limit' => 500],
            'premium' => ['price' => 10000, 'sites_allowed' => 10, 'products_limit' => null],
        ];

        $planConfig = $plans[$request->plan] ?? $plans['starter'];
        $period = $request->period ?? 'month';
        $currency = $request->currency ?? 'XOF';
        $price = $planConfig['price'];

        // Annual discount calculation
        $months = 1;
        $discountPercent = 0;
        if ($period === 'year') {
            $months = 12;
            $discounts = [
                'starter' => 17,
                'business' => 20,
                'premium' => 25,
            ];
            $discountPercent = $discounts[$request->plan] ?? 0;
            $price = (int) round($price * 12 * (1 - $discountPercent / 100));
        }

        $reference = 'XY-' . Str::uuid()->toString();

        // Create pending payment record
        $payment = Payment::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'geniuspay_reference' => $reference,
            'amount' => $price,
            'currency' => $currency,
            'status' => Payment::STATUS_PENDING,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone,
            'raw_response' => [
                'plan' => $request->plan,
                'period' => $period,
                'months' => $months,
                'discount_percent' => $discountPercent,
                'created_at' => now()->toISOString(),
            ],
        ]);

        // Call GeniusPay to create payment session
        try {
            $geniuspayResponse = $geniusPay->createPayment([
                'amount' => $price,
                'currency' => $currency,
                'customer_email' => $user->email,
                'customer_phone' => $user->phone,
                'reference' => $reference,
                'description' => 'Abonnement XY - ' . ucfirst($request->plan) . ' (' . $months . ' mois)',
                'return_url' => route('geniuspay.return') . '?reference=' . $reference,
                'webhook_url' => route('geniuspay.webhook'),
                'metadata' => [
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'plan' => $request->plan,
                    'period' => $period,
                    'months' => $months,
                    'price' => $price,
                    'currency' => $currency,
                    'sites_allowed' => $planConfig['sites_allowed'],
                    'products_limit' => $planConfig['products_limit'],
                    'auto_renew' => true,
                    'expires_at' => $period === 'year' ? now()->addYear() : now()->addMonth(),
                    'features' => match ($request->plan) {
                        'starter' => ['sous-domaine-personnalise', 'qr-code', 'whatsapp-bouton', 'appel', 'email', 'stats-basiques'],
                        'business' => ['sous-domaine-personnalise', 'qr-code', 'whatsapp-bouton', 'appel', 'email', 'lien-externe', 'stats-avancees', 'marketing'],
                        'premium' => ['tout-inclus', 'prioritaire', 'support', 'ia', 'campagnes', 'domaine-personnalise'],
                        default => ['sous-domaine', 'qr-code', 'whatsapp-bouton'],
                    },
                ],
            ]);

            $payment->update([
                'raw_response' => array_merge($payment->raw_response, [
                    'geniuspay_create_response' => $geniuspayResponse,
                ]),
            ]);

            return response()->json([
                'payment_id' => $payment->id,
                'reference' => $reference,
                'amount' => $price,
                'currency' => $currency,
                'geniuspay_url' => $geniuspayResponse['payment_url'] ?? $geniuspayResponse['url'] ?? null,
                'geniuspay_response' => $geniuspayResponse,
                'requires_redirect' => true,
            ]);
        } catch (\Exception $e) {
            $payment->update(['status' => Payment::STATUS_FAILED]);
            return response()->json([
                'error' => 'payment_creation_failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
