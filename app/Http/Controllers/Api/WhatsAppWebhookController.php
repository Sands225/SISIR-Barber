<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ConversationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        private ConversationService $conversation
    ) {}

    /**
     * GET /api/webhook/whatsapp
     * Meta webhook verification challenge.
     */
    public function verify(Request $request): Response
    {
        $mode      = $request->query('hub_mode');
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === config('sisir.whatsapp.verify_token')) {
            Log::info('[WhatsAppWebhook] Verification successful');
            return response($challenge, 200);
        }

        Log::warning('[WhatsAppWebhook] Verification failed', ['token' => $token]);
        return response('Forbidden', 403);
    }

    /**
     * POST /api/webhook/whatsapp
     * Receive incoming WhatsApp messages from local Node.js server.
     */
    public function handle(Request $request): Response
    {
        $payload = $request->all();

        Log::debug('[WhatsAppWebhook] Incoming payload from Node', ['payload' => $payload]);

        $from = $payload['from'] ?? null;
        $text = $payload['text'] ?? null;
        $id   = $payload['id'] ?? null;

        if ($from && $text && $id) {
            $this->processMessage($from, $text, $id);
        }

        return response('OK', 200);
    }

    // ── Private ──────────────────────────────────────────────────────────────

    private function processMessage(string $waId, string $text, string $messageId): void
    {
        // ── Idempotency Guard ────────────────────────────────────────────────
        $cacheKey = "wa_msg_{$messageId}";
        if (Cache::has($cacheKey)) {
            Log::info('[WhatsAppWebhook] Duplicate message, skipping', ['id' => $messageId]);
            return;
        }
        Cache::put($cacheKey, true, now()->addHours(24));
        // ────────────────────────────────────────────────────────────────────

        // If the user replied '1', we translate it to the confirm_arrival format
        // This is a naive heuristic just for the Hackathon
        $resolvedType = 'text';
        $resolvedText = trim($text);

        if ($resolvedText === '1') {
            // Find the most recent upcoming booking for this user
            $booking = \App\Models\Booking::whereHas('customer', fn ($q) => $q->where('wa_id', $waId))
                ->where('status', \App\Enums\BookingStatus::BOOKED)
                ->where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at', 'asc')
                ->first();

            if ($booking) {
                $resolvedType = 'interactive_button';
                $resolvedText = "confirm_arrival_{$booking->id}";
            }
        } elseif ($resolvedText === '2') {
            $booking = \App\Models\Booking::whereHas('customer', fn ($q) => $q->where('wa_id', $waId))
                ->where('status', \App\Enums\BookingStatus::BOOKED)
                ->where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at', 'asc')
                ->first();

            if ($booking) {
                $resolvedType = 'interactive_button';
                $resolvedText = "cancel_booking_{$booking->id}";
            }
        }

        Log::info('[WhatsAppWebhook] Processing message', [
            'wa_id' => $waId,
            'type'  => $resolvedType,
            'text'  => substr($resolvedText, 0, 100),
        ]);

        $this->conversation->handle($waId, $resolvedText, $resolvedType);
    }
}
