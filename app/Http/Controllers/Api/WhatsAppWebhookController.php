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
     * Receive incoming WhatsApp messages.
     */
    public function handle(Request $request): Response
    {
        $payload = $request->all();

        Log::debug('[WhatsAppWebhook] Incoming payload', ['payload' => $payload]);

        // Process each entry/change
        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value    = $change['value'] ?? [];
                $messages = $value['messages'] ?? [];

                foreach ($messages as $message) {
                    $this->processMessage($message, $value['contacts'] ?? []);
                }
            }
        }

        // Always return 200 to Meta to prevent retries
        return response('OK', 200);
    }

    // ── Private ──────────────────────────────────────────────────────────────

    private function processMessage(array $message, array $contacts): void
    {
        $messageId = $message['id'] ?? null;

        if (! $messageId) {
            return;
        }

        // ── Idempotency Guard ────────────────────────────────────────────────
        // Prevents double-processing if Meta sends the same message twice
        $cacheKey = "wa_msg_{$messageId}";
        if (Cache::has($cacheKey)) {
            Log::info('[WhatsAppWebhook] Duplicate message, skipping', ['id' => $messageId]);
            return;
        }
        Cache::put($cacheKey, true, now()->addHours(24));
        // ────────────────────────────────────────────────────────────────────

        $waId = $message['from'] ?? null;
        if (! $waId) {
            return;
        }

        $messageType = $message['type'] ?? 'text';

        // Extract message content based on type
        [$text, $resolvedType] = match ($messageType) {
            'text'         => [$message['text']['body'] ?? '', 'text'],
            'interactive'  => $this->parseInteractive($message),
            'button'       => [$message['button']['payload'] ?? '', 'text'],
            default        => ['', 'text'],
        };

        if (empty($text) && $resolvedType !== 'interactive_button') {
            Log::debug('[WhatsAppWebhook] Empty message, ignoring', ['type' => $messageType]);
            return;
        }

        Log::info('[WhatsAppWebhook] Processing message', [
            'wa_id' => $waId,
            'type'  => $resolvedType,
            'text'  => substr($text, 0, 100),
        ]);

        // Dispatch to ConversationService (synchronously for now;
        // could be moved to a ProcessIncomingMessageJob for async)
        $this->conversation->handle($waId, $text, $resolvedType);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function parseInteractive(array $message): array
    {
        $interactive = $message['interactive'] ?? [];
        $type        = $interactive['type'] ?? '';

        if ($type === 'button_reply') {
            $buttonId = $interactive['button_reply']['id'] ?? '';
            return [$buttonId, 'interactive_button'];
        }

        if ($type === 'list_reply') {
            $itemId = $interactive['list_reply']['id'] ?? '';
            return [$itemId, 'interactive_button'];
        }

        return ['', 'text'];
    }
}
