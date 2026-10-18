<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\GeniusPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GeniusPayController extends Controller
{
    public function webhook(Request $request, GeniusPayService $geniusPay)
    {
        try {
            $webhookData = $geniusPay->verifyWebhook($request->headers->all(), $request->getContent());
            $geniusPay->handleWebhook($webhookData);

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('GeniusPay webhook error: ' . $e->getMessage(), [
                'headers' => $request->headers->all(),
                'body' => $request->getContent(),
            ]);

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function return(Request $request, GeniusPayService $geniusPay)
    {
        $reference = $request->query('reference');

        $payment = Payment::where('geniuspay_reference', $reference)->first();

        if (!$payment) {
            return redirect('/dashboard?payment=not_found');
        }

        // Never consider browser return as confirmation; show status only
        $status = $payment->status;

        $tenant = $payment->tenant;

        return redirect('/dashboard?payment=' . $status . '&reference=' . $reference . '&tenant=' . ($tenant?->slug ?? ''));
    }
}
