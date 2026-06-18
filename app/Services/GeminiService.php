<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private Client $http;
    private array  $apiKeys;  // Pool of API keys for rotation
    private string $model;
    private string $baseUrl;

    /** Max retries per key before rotating to the next one */
    private const MAX_RETRIES_PER_KEY = 2;

    /** Base delay in seconds for exponential backoff */
    private const BACKOFF_BASE_SECONDS = 2;

    public function __construct()
    {
        $this->apiKeys = $this->resolveApiKeys();
        $this->model   = config('sisir.gemini.model', 'gemini-2.0-flash');
        $this->baseUrl = config('sisir.gemini.base_url');
        $this->http    = new Client(['timeout' => 20.0, 'verify' => false]);
    }

    /**
     * Dialog Manager: deteksi intent, ekstrak slot, dan hasilkan respons natural.
     * Mengembalikan array terstruktur untuk ConversationService.
     *
     * Intent yang mungkin: booking | faq | handover | oot | error
     */
    public function runDialogManager(string $message, array $history, array $slots, string $referenceDate): array
    {
        $systemPrompt = $this->buildDialogManagerSystemPrompt($slots, $history, $referenceDate);
        $response     = $this->callGemini($systemPrompt, $message);
        $decoded      = $this->parseJsonResponse($response);

        if (
            is_array($decoded)
            && isset($decoded['intent'], $decoded['updated_slots'], $decoded['response'])
            && !empty($decoded['response'])
        ) {
            // Pastikan all_slots_complete default ke false jika tidak ada
            $decoded['all_slots_complete'] = $decoded['all_slots_complete'] ?? false;
            return $decoded;
        }

        Log::warning('[GeminiService] Dialog manager returned invalid or empty response, using fallback.');

        return [
            'intent'             => 'error',
            'updated_slots'      => $slots,
            'all_slots_complete' => false,
            'response'           => 'Mohon maaf Kak, sistem kami sedang sibuk sebentar 🙏 Boleh coba kirim pesan lagi dalam beberapa saat?',
        ];
    }

    // ── Private ──────────────────────────────────────────────────────────────

    private function buildDialogManagerSystemPrompt(array $slots, array $history, string $referenceDate): string
    {
        $slotsJson   = json_encode($slots,   JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $historyJson = json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $today    = now('Asia/Jakarta')->toDateString();
        $tomorrow = now('Asia/Jakarta')->addDay()->toDateString();

        try {
            $services     = \App\Models\Service::where('is_active', true)->get();
            $servicesText = $services->map(
                fn($s) => "- {$s->name} (Rp " . number_format($s->price, 0, ',', '.') . ", {$s->duration_minutes} menit)"
            )->join("\n");
        } catch (\Throwable $e) {
            $servicesText = "- Cukur Anak-anak (Rp 25.000, 30 menit)\n"
                . "- Cukur Dewasa (Rp 35.000, 30 menit)\n"
                . "- Cukur Gundul (Rp 20.000, 20 menit)\n"
                . "- Potong Jenggot & Kumis (Rp 15.000, 20 menit)";
        }

        try {
            $faqs     = \App\Models\Faq::where('is_active', true)->get();
            $faqsText = $faqs->map(fn($f) => "T: {$f->question}\nJ: {$f->answer}")->join("\n\n");
        } catch (\Throwable $e) {
            $faqsText = "T: Jam buka?\nJ: Setiap hari 08:00 - 20:00 WIB.";
        }

        return <<<PROMPT
# ROLE
Kamu adalah SISIR, AI Customer Service Virtual untuk "SISIR Barber".
Tugas kamu: menjawab pertanyaan seputar layanan barbershop, mengumpulkan data reservasi, dan mengarahkan ke pembayaran.

---
# WAKTU REFERENSI
Waktu saat ini (Asia/Jakarta): {$referenceDate}
Hari ini: {$today}
Besok: {$tomorrow}

---
# DATA LAYANAN TERSEDIA
{$servicesText}

---
# FAQ / PENGETAHUAN UMUM
{$faqsText}

---
# DATA PERCAKAPAN SAAT INI
Slot reservasi yang sudah terkumpul (JANGAN tanya ulang data yang sudah ada):
{$slotsJson}

Riwayat percakapan (terbaru di bawah):
{$historyJson}

---
# KEPRIBADIAN & GAYA BICARA
- Ramah, asyik, sopan, dan helpful.
- Gunakan sapaan "Kak" kepada pelanggan.
- Gunakan emoji secukupnya untuk menghidupkan suasana.
- JANGAN ulangi sapaan "Halo" / "Hai" jika percakapan sudah berlangsung.
- JANGAN tanya data yang sudah ada di slot (tidak null).
- JANGAN PERNAH tanya nomor HP atau telepon.
- Jika pelanggan mengubah data yang sudah ada, terima dengan santai dan update slot.

---
# ANALISIS INTENT & TINDAKAN

## A. INTENT: BERTANYA (faq)
- Jika pelanggan menanyakan informasi (harga, lokasi, jam buka, durasi cukur, dll).
- Jawab dengan ramah dan informatif berdasarkan data layanan dan FAQ di atas.
- Setelah menjawab, tawarkan kembali untuk melakukan reservasi.
- Set intent: "faq"

## B. INTENT: MINTA BICARA DENGAN ADMIN (handover)
- Jika pelanggan meminta admin/manusia, mengeluh hal kompleks, atau menggunakan kata kunci:
  "admin", "manusia", "cs", "sambungkan", "operator", "minta tolong", dll.
- Set intent: "handover"
- Response WAJIB: "Baik Kak, mohon ditunggu sebentar ya. Chat Kakak sedang aku sambungkan ke Admin kami. 👨‍💼"

## C. INTENT: RESERVASI (booking)
- Set intent: "booking"
- 4 data yang WAJIB dikumpulkan secara bertahap:
  1. **name**: nama pelanggan
  2. **service**: salah satu dari layanan di atas (nama persis)
  3. **day**: tanggal kedatangan → konversi ke format **"YYYY-MM-DD"**
  4. **time**: jam kedatangan → konversi ke format **"HH:MM"**

- Jika data BELUM lengkap (ada yang null):
  - Tanyakan HANYA data yang masih null, dengan ramah.
  - Gunakan template jika perlu: "Baik Kak [nama], untuk layanannya mau pilih yang mana?"
  - Set all_slots_complete: false

- Jika data SUDAH LENGKAP (semua 4 data bukan null):
  - Set all_slots_complete: true
  - Response: konfirmasi ringkas bahwa data sudah diterima dan akan diproses.
  - Contoh: "Oke Kak [nama]! Data reservasinya sudah lengkap, aku proses sekarang ya ✂️"

## D. OUT OF TOPIC (oot)
- Jika pesan tidak berhubungan sama sekali dengan SISIR Barber atau layanan barbershop.
- Set intent: "oot"
- Response: "Wah, aku khusus bantu reservasi dan info SISIR Barber aja Kak 😊 Ada yang mau dipesan?"

---
# ATURAN KONVERSI SLOT (PENTING)
- **day** (konversi ke YYYY-MM-DD):
  - "hari ini" → {$today}
  - "besok" → {$tomorrow}
  - "lusa" → hari sesudah besok
  - Nama hari (Senin/Selasa/Rabu/Kamis/Jumat/Sabtu/Minggu) → tanggal terdekat hari tersebut dari sekarang
  - Jika sudah format tanggal, gunakan apa adanya

- **time** (konversi ke HH:MM):
  - "pagi" (tanpa jam spesifik) → "09:00"
  - "siang" → "12:00"
  - "sore" (tanpa jam spesifik) → "16:00"
  - "malam" (tanpa jam spesifik) → "19:00"
  - "jam 3 sore" → "15:00", "jam 8 pagi" → "08:00", "9 malam" → "21:00"
  - "abis dzuhur" / "habis dzuhur" → "13:00"
  - "ashar" / "asar" → "15:30"

- **service** (cocokkan fleksibel):
  - "cukur biasa", "cukur pria", "potong rambut", "cukur dewasa", "cukur" (tanpa keterangan) → "Cukur Dewasa"
  - "cukur anak", "cukur anak-anak", "anak" → "Cukur Anak-anak"
  - "gundul", "botak", "cukur gundul" → "Cukur Gundul"
  - "jenggot", "kumis", "brewok", "potong jenggot" → "Potong Jenggot & Kumis"

---
# FORMAT RESPONS (WAJIB JSON VALID, TIDAK ADA TEKS DI LUAR JSON)
{
  "intent": "booking | faq | handover | oot",
  "updated_slots": {
    "name": "string atau null",
    "service": "nama layanan persis sesuai daftar, atau null",
    "day": "YYYY-MM-DD atau null",
    "time": "HH:MM atau null",
    "phone": null
  },
  "all_slots_complete": false,
  "response": "respons natural dalam Bahasa Indonesia"
}
PROMPT;
    }

    /**
     * Call Gemini API with automatic key rotation and exponential backoff retry.
     *
     * Strategy:
     *  - Try each key up to MAX_RETRIES_PER_KEY times on 429 rate-limit.
     *  - If all retries for a key are exhausted, rotate to the next key.
     *  - If ALL keys are exhausted, return '{}' (triggers fallback response).
     */
    private function callGemini(string $systemPrompt, string $userMessage): string
    {
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
                'temperature'      => 0.1,
                'responseMimeType' => 'application/json',
            ],
        ];

        foreach ($this->apiKeys as $keyIndex => $apiKey) {
            // Skip keys that are cooling down from daily quota exhaustion
            if ($this->isKeyCoolingDown($apiKey)) {
                Log::info("[GeminiService] Skipping key #{$keyIndex} (cooling down).");
                continue;
            }

            for ($attempt = 1; $attempt <= self::MAX_RETRIES_PER_KEY; $attempt++) {
                try {
                    $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$apiKey}";
                    $res  = $this->http->post($url, ['json' => $body]);
                    $data = json_decode($res->getBody()->getContents(), true);

                    Log::debug("[GeminiService] Success with key #{$keyIndex}, attempt {$attempt}.");
                    return $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

                } catch (ClientException $e) {
                    $statusCode = $e->getResponse()->getStatusCode();
                    $body429    = json_decode($e->getResponse()->getBody()->getContents(), true);
                    $errMsg     = $body429['error']['message'] ?? $e->getMessage();

                    if ($statusCode === 429) {
                        // Detect if this is a daily QUOTA exhaustion (not just rate limit)
                        $isQuotaExhausted = str_contains($errMsg, 'quota') || str_contains($errMsg, 'billing');

                        if ($isQuotaExhausted) {
                            // Cool down this key for 1 hour, rotate to next immediately
                            $this->coolDownKey($apiKey, 3600);
                            Log::warning("[GeminiService] Key #{$keyIndex} daily quota exhausted. Rotating to next key.");
                            break; // Break retry loop, try next key
                        }

                        // Rate limit (per-minute): backoff and retry same key
                        $delay = self::BACKOFF_BASE_SECONDS ** $attempt;
                        Log::warning("[GeminiService] Key #{$keyIndex} rate-limited (429). Retrying in {$delay}s (attempt {$attempt}).");
                        sleep($delay);
                        continue;
                    }

                    // Other client error (400, 403, etc.) — don't retry
                    Log::error("[GeminiService] Client error {$statusCode} with key #{$keyIndex}.", ['error' => $errMsg]);
                    break;

                } catch (\Throwable $e) {
                    // Network error, timeout, etc. — log and try next key
                    Log::error('[GeminiService] API call failed.', ['error' => $e->getMessage()]);
                    break;
                }
            }
        }

        Log::error('[GeminiService] All API keys exhausted or failed. Returning empty response.');
        return '{}';
    }

    /**
     * Resolve the pool of API keys from config.
     * Supports GEMINI_API_KEYS (comma-separated) or falls back to GEMINI_API_KEY.
     */
    private function resolveApiKeys(): array
    {
        $multiKeys = config('sisir.gemini.api_keys', '');

        if (!empty($multiKeys)) {
            $keys = array_filter(array_map('trim', explode(',', $multiKeys)));
            if (count($keys) > 0) {
                return array_values($keys);
            }
        }

        // Fallback to single key
        $single = config('sisir.gemini.api_key', '');
        return $single ? [$single] : [];
    }

    /**
     * Mark an API key as cooling down (quota exhausted) for a given TTL.
     */
    private function coolDownKey(string $apiKey, int $ttlSeconds): void
    {
        $cacheKey = 'gemini_key_cooldown_' . md5($apiKey);
        Cache::put($cacheKey, true, now()->addSeconds($ttlSeconds));
    }

    /**
     * Check if an API key is currently cooling down.
     */
    private function isKeyCoolingDown(string $apiKey): bool
    {
        $cacheKey = 'gemini_key_cooldown_' . md5($apiKey);
        return Cache::has($cacheKey);
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
}