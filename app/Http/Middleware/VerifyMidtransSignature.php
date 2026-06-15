<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validates the Midtrans webhook signature key.
 * Signature = SHA512(order_id + status_code + gross_amount + server_key)
 */
class VerifyMidtransSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $serverKey = config('sisir.midtrans.server_key');

        // Skip in local development when server key is not set
        if (app()->isLocal() && empty($serverKey)) {
            return $next($request);
        }

        $payload = $request->all();

        $orderId     = $payload['order_id'] ?? '';
        $statusCode  = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $incoming    = $payload['signature_key'] ?? '';

        if (empty($incoming)) {
            Log::warning('[VerifyMidtransSignature] Missing signature_key in payload');
            return response()->json(['error' => 'Missing signature'], 401);
        }

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if (! hash_equals($expected, $incoming)) {
            Log::warning('[VerifyMidtransSignature] Signature mismatch', [
                'order_id' => $orderId,
                'received' => $incoming,
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        return $next($request);
    }
}
