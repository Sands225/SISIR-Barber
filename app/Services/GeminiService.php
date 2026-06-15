<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private Client $http;
    private string $apiKey;
    private string $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('sisir.gemini.api_key');
        $this->model   = config('sisir.gemini.model', 'gemini-2.0-flash');
        $this->baseUrl = config('sisir.gemini.base_url');
        $this->http    = new Client(['timeout' => 15.0]);
    }

    /**
     * Zero-shot intent classification.
     * Returns structured array with intent and extracted entities.
     *
     * Possible intents: booking, reschedule, cancel, status_check, waitlist, faq, ambiguous
     */
    public function parseIntent(string $message, string $waId, array $context = []): array
    {
        $systemPrompt = $this->buildIntentSystemPrompt($context);
        $response     = $this->callGemini($systemPrompt, $message);

        return $this->parseJsonResponse($response) ?? [
            'intent'  => 'ambiguous',
            'options' => ['manual_pick', 'talk_to_admin'],
            'raw'     => $response,
        ];
    }

    /**
     * Natural language time parsing engine.
     * Converts Indonesian time expressions to Carbon instance.
     *
     * Examples:
     *   "abis dzuhur"   → Carbon(13:00)
     *   "besok sore"    → Carbon(tomorrow, 16:00)
     *   "jam 3 siang"   → Carbon(today, 15:00)
     */
    public function parseTime(string $naturalTime, ?Carbon $referenceDate = null): ?Carbon
    {
        $reference = $referenceDate ?? now()->timezone('Asia/Jakarta');
        $prompt    = $this->buildTimeParsingSystemPrompt($reference->toDateString());
        $response  = $this->callGemini($prompt, $naturalTime);
        $parsed    = $this->parseJsonResponse($response);

        if (! $parsed || ! isset($parsed['datetime'])) {
            return null;
        }

        try {
            return Carbon::parse($parsed['datetime'], 'Asia/Jakarta');
        } catch (\Throwable $e) {
            Log::warning('[GeminiService] Time parse failed', ['response' => $parsed, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Check if a message has high ambiguity (Gemini returns AMBIGUOUS intent).
     */
    public function isAmbiguous(array $intentResult): bool
    {
        return ($intentResult['intent'] ?? '') === 'ambiguous';
    }

    // ── Private Helpers ──────────────────────────────────────────────────────

    private function callGemini(string $systemPrompt, string $userMessage): string
    {
        $url  = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";
        $body = [
            'system_instruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [['text' => $userMessage]],
                ],
            ],
            'generationConfig' => [
                'temperature'     => 0.1,
                'responseMimeType' => 'application/json',
            ],
        ];

        try {
            $res  = $this->http->post($url, ['json' => $body]);
            $data = json_decode($res->getBody()->getContents(), true);

            return $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        } catch (\Throwable $e) {
            Log::error('[GeminiService] API call failed', ['error' => $e->getMessage()]);

            return '{}';
        }
    }

    private function parseJsonResponse(string $raw): ?array
    {
        // Strip markdown code fences if present
        $clean = preg_replace('/```json?\s*/i', '', $raw);
        $clean = preg_replace('/```/', '', $clean ?? $raw);
        $clean = trim($clean ?? '');

        $decoded = json_decode($clean, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function buildIntentSystemPrompt(array $context): string
    {
        $contextJson = json_encode($context);

        return <<<PROMPT
        You are SISIR, a smart WhatsApp booking assistant for a barber shop.
        
        Analyze the customer's message and extract the intent and entities.
        
        Conversation context (previous turns): {$contextJson}
        
        Respond ONLY with valid JSON in this exact schema:
        {
          "intent": "<one of: booking|reschedule|cancel|status_check|waitlist|faq|ambiguous>",
          "entities": {
            "date": "<ISO date YYYY-MM-DD or null>",
            "time": "<HH:MM or natural expression or null>",
            "service": "<service name or null>",
            "barber": "<barber name or null>"
          },
          "confidence": <0.0-1.0>,
          "options": ["manual_pick", "talk_to_admin"]
        }
        
        Rules:
        - If confidence < 0.6, set intent to "ambiguous"
        - For time, preserve natural expressions (e.g., "abis dzuhur") as-is; the system will parse them
        - If the message is a greeting or small talk, set intent to "faq"
        - Never invent entities that are not mentioned
        PROMPT;
    }

    private function buildTimeParsingSystemPrompt(string $referenceDate): string
    {
        return <<<PROMPT
        You are a time-parsing engine for an Indonesian barber shop booking system.
        Today's date is: {$referenceDate} (Asia/Jakarta timezone).
        
        Convert the given natural-language time expression to an exact datetime.
        
        Common Indonesian time references:
        - "subuh" = 04:30
        - "pagi" = 08:00-11:00 (use 09:00 if unspecified)
        - "siang" = 12:00-14:00 (use 12:00)
        - "abis dzuhur" / "habis dzuhur" = 13:00
        - "ashar" / "asar" = 15:30
        - "sore" = 16:00
        - "abis ashar" = 16:00
        - "maghrib" = 18:00
        - "malam" = 19:00-21:00 (use 19:00)
        - "besok" = tomorrow
        - "lusa" = day after tomorrow
        - "Senin/Selasa/Rabu/Kamis/Jumat/Sabtu/Minggu" = next occurrence of that weekday
        
        Respond ONLY with valid JSON:
        {
          "datetime": "<ISO 8601 datetime, e.g. 2026-06-17T13:00:00+07:00>",
          "confidence": <0.0-1.0>,
          "interpreted_as": "<human-readable explanation>"
        }
        
        If you cannot determine a valid datetime, set datetime to null.
        PROMPT;
    }
}
