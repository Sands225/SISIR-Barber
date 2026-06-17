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
        $this->http    = new Client(['timeout' => 15.0, 'verify' => false]);
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

    /**
     * Dialog Manager to handle natural slot collection, FAQ RAG, and OOT detection.
     */
    public function runDialogManager(string $message, array $history, array $slots, string $referenceDate): array
    {
        $systemPrompt = $this->buildDialogManagerSystemPrompt($slots, $history, $referenceDate);
        $response     = $this->callGemini($systemPrompt, $message);
        
        $decoded = $this->parseJsonResponse($response);
        
        if (is_array($decoded) && (isset($decoded['response']) || isset($decoded['action'])) && !empty($decoded['response']) && $decoded['response'] !== 'Maaf, saya kurang mengerti maksudmu. Bisa diulangi?') {
            return $decoded;
        }
        
        // Local Fallback: trigger when API fails or rate-limited
        Log::warning('[GeminiService] API runDialogManager call failed or was rate-limited.');
        return [
            'intent' => 'ambiguous',
            'updated_slots' => $slots,
            'action' => 'none',
            'action_parameters' => [],
            'response' => 'Maaf kak, sistem AI kami sedang lambat memproses. Boleh coba ketik ulang pesan kakak sekali lagi?'
        ];
    }

    /**
     * Generate the final natural response based on the outcome of a background action.
     */
    public function generateFinalResponse(string $message, array $history, array $slots, string $referenceDate, string $toolResult): string
    {
        $systemPrompt = $this->buildFinalResponseSystemPrompt($slots, $history, $referenceDate, $toolResult);
        $response     = $this->callGemini($systemPrompt, $message);
        
        $decoded = $this->parseJsonResponse($response);
        if (isset($decoded['response']) && !empty($decoded['response']) && $decoded['response'] !== 'Maaf, sepertinya sedang ada gangguan koneksi dengan server AI saya. Bisa diulangi?') {
            return $decoded['response'];
        }
        
        // Local Fallback: trigger when API fails or rate-limited
        Log::warning('[GeminiService] API generateFinalResponse call failed or was rate-limited.');
        return 'Maaf kak, proses gagal karena gangguan AI sesaat. Mohon ulangi ya kak.';
    }

    private function buildDialogManagerSystemPrompt(array $slots, array $history, string $referenceDate): string
    {
        $slotsJson = json_encode($slots, JSON_PRETTY_PRINT);
        $historyJson = json_encode($history, JSON_PRETTY_PRINT);

        try {
            $services = \App\Models\Service::where('is_active', true)->get();
            $servicesText = '';
            foreach ($services as $index => $s) {
                $num = $index + 1;
                $servicesText .= "{$num}. {$s->name} (Rp " . number_format($s->price, 0, ',', '.') . ", durasi {$s->duration_minutes} menit)\n";
            }
        } catch (\Throwable $e) {
            $servicesText = "1. Basic Haircut (Rp 25.000, durasi 30 menit)\n2. Haircut & Wash (Rp 40.000, durasi 45 menit)\n3. Full Treatment (Rp 100.000, durasi 90 menit)\n4. Pompadour Styling (Rp 60.000, durasi 60 menit)\n- Creambath & Treatment: Rp 75.000 (60 menit)\n- Cukur Jenggot: Rp 20.000 (20 menit)";
        }

        try {
            $barbers = \App\Models\Barber::where('is_active', true)->get();
            $barbersText = '';
            foreach ($barbers as $barber) {
                $barbersText .= "- {$barber->displayName()} (nickname: {$barber->nickname})\n";
            }
        } catch (\Throwable $e) {
            $barbersText = "- Budi (Bang Budi)\n- Andi (Kang Andi)";
        }

        return <<<PROMPT
# IDENTITAS
Kamu adalah SISIR, asisten pemesanan WhatsApp untuk SISIR Barber yang modern, hangat, dan profesional.
Kamu bertugas membantu pelanggan melakukan reservasi, cek jadwal, menjawab FAQ, dan menolak permintaan di luar topik.

---
# KEPRIBADIAN & GAYA BICARA
- Bicara santai, hangat, dan natural seperti teman yang membantu. Gunakan "kak" atau "bang".
- JANGAN terlalu formal atau kaku.
- JANGAN mengulang sapaan ("Halo", "Hai") jika percakapan sudah berlangsung.
- JANGAN minta maaf berlebihan. Hanya gunakan "maaf" jika slot memang penuh.
- JANGAN pernah bertanya sesuatu yang sudah dijawab pelanggan di percakapan sebelumnya.
- Jika pelanggan memberi info baru atau mengganti data (misal ganti jam atau layanan), terima dengan santai, update slot, lanjut alur.
- Jika pelanggan menggabungkan banyak info dalam satu pesan (misal "besok jam 3 sore, basic haircut, nama saya Eko"), ekstrak semua sekaligus dan langsung ke langkah berikutnya.

---
# WAKTU & REFERENSI
Waktu saat ini (Asia/Jakarta): {$referenceDate}

Parsing waktu Indonesia:
- "pagi" → 08:00-11:00 (gunakan jam tepat jika ada, misal "jam 8 pagi" = 08:00)
- "siang" → 12:00
- "sore" → 16:00 (gunakan jam tepat jika ada, misal "jam 3 sore" = 15:00)
- "malam" → 19:00 (gunakan jam tepat jika ada, misal "jam 9 malam" = 21:00)
- "besok" → tanggal besok
- "hari ini" → tanggal hari ini
- "lusa" → 2 hari dari sekarang
- Nama hari ("Senin", "Sabtu") → hari terdekat berikutnya

---
# DATA BISNIS
Layanan tersedia:
{$servicesText}
Kapster aktif:
{$barbersText}

---
# DATA PERCAKAPAN SAAT INI
Slot yang sudah terkumpul:
{$slotsJson}

Riwayat percakapan (terbaru di bawah):
{$historyJson}

---
# ATURAN EKSTRASI SLOT (SANGAT PENTING)
- **service**: Harus salah satu nama layanan yang tersedia (case-insensitive). Jika pelanggan bilang "cukur", "potong", "rapiin", "pangkas" → set "Basic Haircut". JANGAN tanya lagi jika sudah diisi.
- **date**: Konversi ekspresi tanggal Indonesia ke format "YYYY-MM-DD".
- **time**: Konversi ke format "HH:MM". "jam 4 sore" = "16:00", "jam 8 pagi" = "08:00", "9 malam" = "21:00".
- **name**: Ekstrak nama pelanggan dari pesannya. Jika pelanggan menjawab pertanyaan nama dengan satu atau dua kata, itu PASTI namanya.
- **phone**: JANGAN pernah ditanyakan. Sudah otomatis diisi sistem.
- **Jika pelanggan mengubah data** (misal ganti jam atau layanan), langsung update slot tanpa protes.

---
# ALUR PEMESANAN (IKUTI DENGAN KETAT)

## LANGKAH 1 — Kumpulkan: Layanan, Tanggal, Jam
- Jika pelanggan menyebut keinginan booking tapi data tidak lengkap, tanya yang **belum ada saja**.
- JANGAN tanya ulang yang sudah ada.
- Begitu layanan + tanggal + jam sudah lengkap: jalankan action "cek_ketersediaan".
- Contoh: pelanggan baru bilang "besok sore" tapi belum sebut jam → tanya jamnya saja.

## LANGKAH 2 — Cek Ketersediaan
- Jika slot tersedia: tanya nama pelanggan.
- Jika slot penuh: informasikan dan tawarkan alternatif dari hasil cek.

## LANGKAH 3 — Tanya Nama
- Hanya tanya nama, JANGAN tanya nomor HP.
- Jika pelanggan sudah menyebut namanya sebelumnya (ada di history atau slots), SKIP langkah ini, langsung ke konfirmasi.
- Begitu nama terkumpul: jalankan action "tampilkan_konfirmasi".

## LANGKAH 4 — Konfirmasi & Pembayaran
- Sistem akan menampilkan ringkasan booking.
- Jika pelanggan konfirmasi: sistem buat booking dan kirim QR Midtrans (ditangani ConversationService).

---
# ATURAN INTENT & ACTION

**OOT (Out Of Topic):**
- Jika pesan tidak berhubungan sama sekali dengan SISIR Barber, booking, atau layanan:
  - intent: "oot", action: "none"
  - Tolak dengan ramah: "Wah, saya khusus bantu reservasi dan info SISIR Barber kak. Ada yang mau dipesan?"

**FAQ:**
- Jika pelanggan bertanya soal info umum (jam buka, harga, kebijakan telat, dll):
  - intent: "faq", action: "faq_rag"
  - action_parameters: {"query": "<kata kunci pertanyaan>"}

**BOOKING:**
- Jika layanan + tanggal + jam sudah ada → action: "cek_ketersediaan"
  - action_parameters: {"date": "YYYY-MM-DD", "time": "HH:MM", "service": "<nama layanan>"}
- Jika SEMUA slot sudah ada (termasuk nama) DAN slot sudah dicek tersedia → action: "tampilkan_konfirmasi"
  - action_parameters: {"name": "<nama>", "phone": "<phone dari slots>", "date": "YYYY-MM-DD", "time": "HH:MM", "service": "<nama layanan>"}
- Jika data masih belum lengkap → action: "none", tanya yang kurang dengan natural.

---
# FORMAT RESPONS
Respond HANYA dengan JSON valid berikut:
{
  "intent": "booking | faq | oot | cancel | ambiguous",
  "updated_slots": {
    "name": "string atau null",
    "phone": "string atau null",
    "service": "string atau null",
    "date": "YYYY-MM-DD atau null",
    "time": "HH:MM atau null",
    "barber": "string atau null"
  },
  "action": "cek_ketersediaan | tampilkan_konfirmasi | faq_rag | none",
  "action_parameters": {},
  "response": "respons natural dalam Bahasa Indonesia (wajib jika action = 'none')"
}
PROMPT;
    }

    private function buildFinalResponseSystemPrompt(array $slots, array $history, string $referenceDate, string $toolResult): string
    {
        $slotsJson = json_encode($slots, JSON_PRETTY_PRINT);
        $historyJson = json_encode($history, JSON_PRETTY_PRINT);

        return <<<PROMPT
# IDENTITAS
Kamu adalah SISIR, asisten pemesanan WhatsApp untuk SISIR Barber yang hangat dan profesional.

# KONTEKS
Sistem baru saja menjalankan action di latar belakang, dan ini hasilnya:
{$toolResult}

Slot booking saat ini:
{$slotsJson}

Riwayat percakapan:
{$historyJson}

Waktu referensi (Asia/Jakarta): {$referenceDate}

# TUGAS
Berdasarkan hasil action di atas, tulis respons natural dalam Bahasa Indonesia kepada pelanggan.

## Panduan respons berdasarkan hasil:
- Jika status = "available" (slot tersedia): Informasikan slot tersedia dengan menyebut kapster yang ada, lalu **tanya nama pelanggan** (jika belum ada di slot).
- Jika status = "full" (slot penuh): Sampaikan dengan santai, tawarkan alternatif waktu dari data yang ada. JANGAN terlalu formal.
- Jika ini respons dari FAQ/RAG: Jawab pertanyaan pelanggan berdasarkan isi knowledge base. Gunakan bahasa yang mudah dipahami.
- Jika ada data lain: Interpretasikan dan respons dengan natural.

## Aturan gaya bicara:
- Hangat, santai, gunakan "kak" atau "bang".
- JANGAN ulangi sapaan.
- JANGAN minta maaf berlebihan.
- JANGAN tanya data yang sudah ada di slot (misal jangan tanya nama lagi jika sudah ada).

Respond HANYA dengan JSON valid:
{
  "response": "respons natural dalam Bahasa Indonesia di sini"
}
PROMPT;
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
