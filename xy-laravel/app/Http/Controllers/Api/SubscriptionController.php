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
        // Prix actuels (déjà réduits de 50%)
        $currentPrices = [
            'entry' => ['price' => 2000, 'original' => 4000, 'sites_allowed' => 1, 'products_limit' => 50],
            'business' => ['price' => 3500, 'original' => 7000, 'sites_allowed' => 3, 'products_limit' => 500],
            'prime' => ['price' => 5000, 'original' => 10000, 'sites_allowed' => 5, 'products_limit' => 1000],
            'prime-plus' => ['price' => 7000, 'original' => 14000, 'sites_allowed' => 10, 'products_limit' => null],
        ];

        $plans = [];
        $planNames = [
            'entry' => 'Entrée',
            'business' => 'Chiffre d\'affaires',
            'prime' => 'Prime',
            'prime-plus' => 'Panneau',
        ];

        foreach ($currentPrices as $id => $p) {
            $plans[] = [
                'id' => $id,
                'name' => $planNames[$id] ?? ucfirst($id),
                'price' => $p['price'],
                'original_price' => $p['original'],
                'discount_percent' => 50,
                'currency' => 'XOF',
                'period' => 'month',
                'sites_allowed' => $p['sites_allowed'],
                'products_limit' => $p['products_limit'],
                'features' => $this->getPlanFeatures($id),
                'description' => 'Abonnement mensuel - ' . ($planNames[$id] ?? ucfirst($id)),
            ];
        }

        // Réduction supplémentaire de 50% sur l'engagement annuel (déjà réduit de 50%)
        $annualDiscounts = [
            'entry' => ['months' => 12, 'discount_percent' => 50, 'label' => '-50% annuel'],
            'business' => ['months' => 12, 'discount_percent' => 50, 'label' => '-50% annuel'],
            'prime' => ['months' => 12, 'discount_percent' => 50, 'label' => '-50% annuel'],
            'prime-plus' => ['months' => 12, 'discount_percent' => 50, 'label' => '-50% annuel'],
        ];

        return response()->json([
            'plans' => $plans,
            'annual_discounts' => $annualDiscounts,
            'default_currency' => 'XOF',
        ]);
    }

    private function getPlanFeatures(string $id): array
    {
        return match ($id) {
            'entry' => ['sous-domaine', 'qr-code', 'whatsapp-bouton', 'appel', 'email', 'stats-basiques'],
            'business' => ['sous-domaine-personnalise', 'qr-code', 'whatsapp-bouton', 'appel', 'email', 'lien-externe', 'stats-avancees', 'marketing'],
            'prime' => ['tout-inclus', 'prioritaire', 'support', 'ia', 'campagnes', 'domaine-personnalise'],
            'prime-plus' => ['tout-inclus', 'prioritaire', 'support-premium', 'ia-avancee', 'multi-domaines', 'domaine-personnalise'],
            default => ['sous-domaine', 'qr-code', 'whatsapp-bouton'],
        };
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
            'entry' => ['price' => 2000, 'original' => 4000, 'sites_allowed' => 1, 'products_limit' => 50],
            'business' => ['price' => 3500, 'original' => 7000, 'sites_allowed' => 3, 'products_limit' => 500],
            'prime' => ['price' => 5000, 'original' => 10000, 'sites_allowed' => 5, 'products_limit' => 1000],
            'prime-plus' => ['price' => 7000, 'original' => 14000, 'sites_allowed' => 10, 'products_limit' => null],
        ];

        $planConfig = $plans[$request->plan] ?? $plans['entry'];
        $period = $request->period ?? 'month';
        $currency = $request->currency ?? 'XOF';
        $price = $planConfig['price'];

        // Annual discount calculation (50% sur l'engagement annuel, déjà réduit de 50%)
        $months = 1;
        $discountPercent = 0;
        if ($period === 'year') {
            $months = 12;
            $discountPercent = 50;
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
                        'entry' => ['sous-domaine', 'qr-code', 'whatsapp-bouton', 'appel', 'email', 'stats-basiques'],
                        'business' => ['sous-domaine-personnalise', 'qr-code', 'whatsapp-bouton', 'appel', 'email', 'lien-externe', 'stats-avancees', 'marketing'],
                        'prime' => ['tout-inclus', 'prioritaire', 'support', 'ia', 'campagnes', 'domaine-personnalise'],
                        'prime-plus' => ['tout-inclus', 'prioritaire', 'support-premium', 'ia-avancee', 'multi-domaines', 'domaine-personnalise'],
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
