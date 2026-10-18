<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class GeniusPayService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $secretKey;
    protected string $webhookSecret;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('geniuspay.base_url', 'https://api.geniuspay.com'), '/');
        $this->apiKey = config('geniuspay.api_key', '');
        $this->secretKey = config('geniuspay.secret_key', '');
        $this->webhookSecret = config('geniuspay.webhook_secret', '');
        $this->timeout = config('geniuspay.timeout', 30);
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->secretKey);
    }

    public function createPayment(array $data): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('GeniusPay is not configured. Set GENIUSPAY_API_KEY and GENIUSPAY_SECRET_KEY.');
        }

        $payload = [
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'XOF',
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'] ?? null,
            'reference' => $data['reference'] ?? 'XY-' . Str::uuid()->toString(),
            'description' => $data['description'] ?? 'Abonnement XY',
            'return_url' => $data['return_url'] ?? route('geniuspay.return'),
            'webhook_url' => $data['webhook_url'] ?? route('geniuspay.webhook'),
            'metadata' => $data['metadata'] ?? [],
        ];

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
                'X-Signature: ' . $this->sign($payload),
            ])
            ->post($this->baseUrl . '/v1/payments', $payload);

        if ($response->failed()) {
            Log::error('GeniusPay payment creation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception('GeniusPay payment creation failed: ' . $response->body());
        }

        return $response->json();
    }

    public function verifyWebhook(array $headers, string $body): array
    {
        $signature = $headers['x-signature'] ?? $headers['X-Signature'] ?? '';
        $expected = hash_hmac('sha256', $body, $this->webhookSecret);

        if (!hash_equals($signature, $expected)) {
            throw new Exception('Invalid GeniusPay webhook signature');
        }

        $data = json_decode($body, true);

        if (!isset($data['event_id'], $data['event_type'], $data['data'])) {
            throw new Exception('Invalid GeniusPay webhook payload');
        }

        return $data;
    }

    public function handleWebhook(array $webhookData): void
    {
        $eventId = $webhookData['event_id'];
        $eventType = $webhookData['event_type'];
        $data = $webhookData['data'];

        // Idempotence: skip already processed events
        if (Payment::where('geniuspay_reference', $data['reference'] ?? null)
            ->where('raw_response->event_id', $eventId)
            ->exists()) {
            Log::info('GeniusPay webhook already processed', ['event_id' => $eventId]);
            return;
        }

        $reference = $data['reference'] ?? null;
        $status = $data['status'] ?? 'pending';
        $amount = $data['amount'] ?? null;
        $currency = $data['currency'] ?? 'XOF';
        $customerEmail = $data['customer_email'] ?? null;
        $customerPhone = $data['customer_phone'] ?? null;
        $transactionId = $data['transaction_id'] ?? null;
        $paymentMethod = $data['payment_method'] ?? null;

        $payment = Payment::updateOrCreate(
            ['geniuspay_reference' => $reference],
            [
                'geniuspay_transaction_id' => $transactionId,
                'amount' => $amount,
                'currency' => $currency,
                'status' => $this->mapStatus($status),
                'payment_method' => $paymentMethod,
                'customer_email' => $customerEmail,
                'customer_phone' => $customerPhone,
                'raw_response' => array_merge($webhookData, ['event_id' => $eventId]),
                'paid_at' => in_array($this->mapStatus($status), [Payment::STATUS_CONFIRMED]) ? now() : null,
            ]
        );

        Log::info('GeniusPay webhook processed', [
            'event_id' => $eventId,
            'event_type' => $eventType,
            'reference' => $reference,
            'status' => $payment->status,
        ]);

        // If confirmed, activate subscription & publish tenant
        if ($payment->isConfirmed() && $reference) {
            $this->activateSubscriptionFromPayment($payment, $data);
        }
    }

    protected function activateSubscriptionFromPayment(Payment $payment, array $data): void
    {
        $metadata = $data['metadata'] ?? [];
        $tenantId = $metadata['tenant_id'] ?? null;
        $plan = $metadata['plan'] ?? null;
        $price = $metadata['price'] ?? null;
        $currency = $metadata['currency'] ?? 'XOF';
        $sitesAllowed = $metadata['sites_allowed'] ?? 1;
        $productsLimit = $metadata['products_limit'] ?? null;
        $features = $metadata['features'] ?? [];

        if (!$tenantId || !$plan) {
            Log::warning('GeniusPay payment metadata missing tenant_id or plan', [
                'reference' => $payment->geniuspay_reference,
            ]);
            return;
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            Log::error('GeniusPay tenant not found', ['tenant_id' => $tenantId]);
            return;
        }

        // Mark payment linked to tenant & user
        $payment->update([
            'tenant_id' => $tenant->id,
            'user_id' => $tenant->user_id,
        ]);

        // Activate or create subscription
        $subscription = Subscription::updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'user_id' => $tenant->user_id,
                'plan' => $plan,
                'price' => $price,
                'currency' => $currency,
                'status' => 'active',
                'geniuspay_reference' => $payment->geniuspay_reference,
                'geniuspay_customer_id' => $data['customer_id'] ?? null,
                'geniuspay_subscription_id' => $data['subscription_id'] ?? null,
                'starts_at' => now(),
                'expires_at' => $metadata['expires_at'] ?? null,
                'auto_renew' => $metadata['auto_renew'] ?? true,
                'sites_allowed' => $sitesAllowed,
                'products_limit' => $productsLimit,
                'features' => $features,
            ]
        );

        // Publish the tenant site
        $tenant->update([
            'status' => 'published',
            'plan' => $plan,
            'subscription_expires_at' => $subscription->expires_at,
        ]);

        Log::info('Tenant activated after GeniusPay confirmation', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
        ]);
    }

    protected function mapStatus(string $status): string
    {
        return match (strtolower($status)) {
            'paid', 'completed', 'success', 'confirmed' => Payment::STATUS_CONFIRMED,
            'failed', 'declined', 'error' => Payment::STATUS_FAILED,
            'cancelled', 'refunded' => Payment::STATUS_CANCELLED,
            default => Payment::STATUS_PENDING,
        };
    }

    protected function sign(array $payload): string
    {
        ksort($payload);
        return hash_hmac('sha256', json_encode($payload), $this->secretKey);
    }

    public function getPaymentMethods(): array
    {
        return [
            'mobile_money' => 'Mobile Money',
            'card' => 'Carte bancaire',
            'bank_transfer' => 'Virement bancaire',
            'cash' => 'Espèces / Agence',
        ];
    }

    public function getSupportedCurrencies(): array
    {
        return [
            'XOF' => 'FCFA (UEMOA)',
            'XAF' => 'FCFA (CEMAC)',
            'USD' => 'Dollar US',
            'EUR' => 'Euro',
        ];
    }
}
