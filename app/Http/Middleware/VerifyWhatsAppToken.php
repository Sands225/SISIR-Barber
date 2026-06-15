<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validates the X-Hub-Signature-256 header on incoming WhatsApp webhook requests.
 * This prevents spoofed payloads from being processed.
 */
class VerifyWhatsAppToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $appSecret = config('sisir.whatsapp.token');

        // In local development without a real token, skip validation
        if (app()->isLocal() && empty($appSecret)) {
            return $next($request);
        }

        $hubSignature = $request->header('X-Hub-Signature-256');

        if (! $hubSignature) {
            Log::warning('[VerifyWhatsAppToken] Missing X-Hub-Signature-256 header');
            return response('Unauthorized', 401);
        }

        // Compute expected signature
        $rawBody  = $request->getContent();
        $expected = 'sha256=' . hash_hmac('sha256', $rawBody, $appSecret);

        if (! hash_equals($expected, $hubSignature)) {
            Log::warning('[VerifyWhatsAppToken] Signature mismatch', [
                'expected' => $expected,
                'received' => $hubSignature,
            ]);
            return response('Unauthorized', 401);
        }

        return $next($request);
    }
}
