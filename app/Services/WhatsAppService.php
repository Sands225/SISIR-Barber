<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Waitlist;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class WhatsAppService
{
    private Client $http;
    private string $token;
    private string $phoneNumberId;
    private string $apiVersion;
    private string $baseUrl;

    public function __construct()
    {
        $this->token         = config('sisir.whatsapp.token');
        $this->phoneNumberId = config('sisir.whatsapp.phone_number_id');
        $this->apiVersion    = config('sisir.whatsapp.api_version', 'v19.0');
        $this->baseUrl       = config('sisir.whatsapp.base_url', 'https://graph.facebook.com');
        $this->http          = new Client(['timeout' => 10.0]);
    }

    /**
     * Send a plain text message.
     */
    public function sendText(string $waId, string $message): bool
    {
        return $this->send($waId, [
            'type' => 'text',
            'text' => ['body' => $message, 'preview_url' => false],
        ]);
    }

    /**
     * Send interactive reply buttons.
     *
     * @param array<array{id: string, title: string}> $buttons
     */
    public function sendInteractiveButtons(string $waId, string $body, array $buttons): bool
    {
        $buttonObjects = array_map(fn ($btn) => [
            'type'  => 'reply',
            'reply' => ['id' => $btn['id'], 'title' => $btn['title']],
        ], array_slice($buttons, 0, 3)); // WhatsApp max 3 buttons

        return $this->send($waId, [
            'type'        => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => ['text' => $body],
                'action' => ['buttons' => $buttonObjects],
            ],
        ]);
    }

    /**
     * Send a booking confirmation ticket.
     */
    public function sendBookingTicket(Booking $booking): bool
    {
        $booking->loadMissing(['customer', 'barber.user', 'service']);

        $message = "✅ *Booking Dikonfirmasi!*\n\n"
            . "🪒 *Layanan:* {$booking->service->name}\n"
            . "💈 *Kapster:* {$booking->barber->displayName()}\n"
            . "🗓️ *Jadwal:* {$booking->scheduledAtFormatted()}\n"
            . "💳 *DP:* Rp " . number_format($booking->dp_amount, 0, ',', '.') . " (Lunas)\n"
            . "🔖 *Kode Booking:* #{$booking->id}\n\n"
            . "⚠️ Harap datang tepat waktu. Kami akan mengirim pengingat 2 jam & 30 menit sebelum jadwal.";

        return $this->sendText($booking->customer->wa_id, $message);
    }

    /**
     * Send H-1 reminder.
     */
    public function sendDayBeforeReminder(Booking $booking): bool
    {
        $booking->loadMissing(['customer', 'barber.user', 'service']);

        $message = "⏰ *Pengingat Besok!*\n\n"
            . "Halo {$booking->customer->name}, jangan lupa besok kamu ada jadwal:\n\n"
            . "💈 *{$booking->service->name}* bersama {$booking->barber->displayName()}\n"
            . "🗓️ {$booking->scheduledAtFormatted()}\n\n"
            . "Sampai jumpa! 🪒";

        return $this->sendText($booking->customer->wa_id, $message);
    }

    /**
     * Send 2-hour before reminder.
     */
    public function sendTwoHoursReminder(Booking $booking): bool
    {
        $booking->loadMissing(['customer', 'barber.user', 'service']);

        $message = "⏰ *2 Jam Lagi!*\n\n"
            . "Halo {$booking->customer->name}, jadwalmu mulai dalam 2 jam:\n\n"
            . "💈 *{$booking->service->name}* jam {$booking->scheduled_at->format('H:i')}\n\n"
            . "Pastikan kamu sudah siap ya! 🪒";

        return $this->sendText($booking->customer->wa_id, $message);
    }

    /**
     * Send 30-minute reconfirmation request.
     */
    public function sendReconfirmationRequest(Booking $booking): bool
    {
        $booking->loadMissing(['customer']);

        $body = "⏰ *30 Menit Lagi!*\n\n"
            . "Halo {$booking->customer->name}, jadwalmu 30 menit lagi. Apakah kamu konfirmasi kehadiran?\n\n"
            . "_(Jika tidak ada respons, booking akan dibatalkan otomatis)_";

        return $this->sendInteractiveButtons(
            $booking->customer->wa_id,
            $body,
            [
                ['id' => "confirm_arrival_{$booking->id}", 'title' => '✅ Saya Hadir'],
                ['id' => "cancel_booking_{$booking->id}",  'title' => '❌ Batalkan'],
            ]
        );
    }

    /**
     * Broadcast last-minute discount to active waitlist customers.
     * Rate-limited to prevent Meta API blocks.
     */
    public function broadcastLastMinuteDiscount(string $scheduledAt, int $discountPct, ?int $serviceId = null): int
    {
        $query = Waitlist::active()->with('customer');

        if ($serviceId) {
            $query->where('service_id', $serviceId);
        }

        $waitlist = $query->get();
        $sent     = 0;

        foreach ($waitlist as $entry) {
            $executed = RateLimiter::attempt(
                'whatsapp-broadcast',
                config('sisir.whatsapp.rate_limit_per_min', 30),
                function () use ($entry, $scheduledAt, $discountPct, &$sent) {
                    $message = "🔥 *Slot Baru Tersedia!*\n\n"
                        . "Ada slot yang baru dibuka pada *{$scheduledAt}*.\n"
                        . "Dapatkan diskon *{$discountPct}%* untuk booking sekarang!\n\n"
                        . "Balas pesan ini atau klik link berikut: " . config('app.url');

                    if ($this->sendText($entry->customer->wa_id, $message)) {
                        $entry->update(['notified_at' => now()]);
                        $sent++;
                    }
                }
            );

            if (! $executed) {
                Log::warning('[WhatsAppService] Rate limit hit during broadcast. Stopping.');
                break;
            }
        }

        return $sent;
    }

    // ── Private ──────────────────────────────────────────────────────────────

    private function send(string $waId, array $messagePayload): bool
    {
        if (empty($this->token) || empty($this->phoneNumberId)) {
            Log::warning('[WhatsAppService] Missing credentials — message not sent', ['wa_id' => $waId]);

            return false;
        }

        $url  = "{$this->baseUrl}/{$this->apiVersion}/{$this->phoneNumberId}/messages";
        $body = array_merge(['messaging_product' => 'whatsapp', 'to' => $waId], $messagePayload);

        try {
            $res = $this->http->post($url, [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}",
                    'Content-Type'  => 'application/json',
                ],
                'json' => $body,
            ]);

            $status = $res->getStatusCode();
            $ok     = $status >= 200 && $status < 300;

            if (! $ok) {
                Log::error('[WhatsAppService] Send failed', ['status' => $status, 'body' => $body]);
            }

            return $ok;
        } catch (\Throwable $e) {
            Log::error('[WhatsAppService] HTTP error', ['error' => $e->getMessage(), 'wa_id' => $waId]);

            return false;
        }
    }
}
