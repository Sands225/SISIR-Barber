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
    private string $nodeUrl;

    public function __construct()
    {
        $this->nodeUrl = config('sisir.whatsapp.node_url', 'http://localhost:3000');
        $this->http    = new Client(['timeout' => 15.0]);
    }

    /**
     * Send a plain text message.
     */
    public function sendText(string $waId, string $message): bool
    {
        return $this->send($waId, $message);
    }

    /**
     * Send a media message (like an image).
     */
    public function sendMedia(string $waId, string $mediaUrl, string $caption = ''): bool
    {
        $url = "{$this->nodeUrl}/send-media";

        try {
            $res = $this->http->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'to'       => $waId,
                    'mediaUrl' => $mediaUrl,
                    'message'  => $caption,
                ],
            ]);

            $status = $res->getStatusCode();
            $ok     = $status >= 200 && $status < 300;

            if (! $ok) {
                Log::error('[WhatsAppService] Send media failed', ['status' => $status, 'wa_id' => $waId]);
            }

            return $ok;
        } catch (\Throwable $e) {
            Log::error('[WhatsAppService] HTTP error connecting to Node.js server (Media)', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Send interactive reply buttons.
     *
     * @param array<array{id: string, title: string}> $buttons
     */
    public function sendInteractiveButtons(string $waId, string $body, array $buttons): bool
    {
        // whatsapp-web.js does not reliably support Meta's Interactive Buttons.
        // Fallback to plain text options:
        $text = $body . "\n\n*Pilihan:*";
        foreach ($buttons as $index => $btn) {
            $num = $index + 1;
            $text .= "\nKetik *{$num}* untuk {$btn['title']}";
        }

        // We also need a way for the webhook to map "1" back to the button's ID.
        // For now, we will prefix the text with a hidden identifier or rely on the conversational state.
        // Or simply add a note:
        $text .= "\n\n_Balas dengan angka pilihan Anda._";

        return $this->sendText($waId, $text);
    }

    /**
     * Send a booking confirmation ticket.
     */
    public function sendBookingTicket(Booking $booking): bool
    {
        $booking->loadMissing(['customer', 'barber.user', 'service']);

        $sisaBayar = $booking->service->price - $booking->dp_amount;

        $message = "✅ *Booking Dikonfirmasi!*\n\n"
            . "🔖 *Kode Booking:* #{$booking->id}\n"
            . "🪒 *Layanan:* {$booking->service->name}\n"
            . "💈 *Kapster:* {$booking->barber->displayName()}\n"
            . "🗓️ *Jadwal:* {$booking->scheduledAtFormatted()}\n\n"
            . "💰 *Rincian Pembayaran:*\n"
            . "   ├ Harga Layanan : Rp " . number_format($booking->service->price, 0, ',', '.') . "\n"
            . "   ├ DP Dibayar    : Rp " . number_format($booking->dp_amount, 0, ',', '.') . " ✅\n"
            . "   └ *Sisa Bayar  : Rp " . number_format($sisaBayar, 0, ',', '.') . "* (bayar di tempat)\n\n"
            . "⚠️ Harap datang tepat waktu. Kami akan mengirim pengingat 1 jam, 30 menit & 15 menit sebelum jadwal.\n"
            . "Sampai jumpa di SISIR Barber! 🪒";

        return $this->sendText($booking->customer->wa_id, $message);
    }

    /**
     * Notify customer that their payment window has expired (10-minute QRIS timeout).
     * Booking has been cancelled and the slot released.
     */
    public function sendPaymentExpiredNotification(Booking $booking): bool
    {
        $booking->loadMissing(['customer', 'service']);

        $message = "❌ *Pembayaran Kadaluarsa*\n\n"
            . "Halo Kak *{$booking->customer->name}*, batas waktu pembayaran DP untuk reservasi berikut telah habis (10 menit):\n\n"
            . "💈 *Layanan:* {$booking->service->name}\n"
            . "🗓️ *Jadwal:* {$booking->scheduledAtFormatted()}\n"
            . "💳 *DP:* Rp " . number_format($booking->dp_amount, 0, ',', '.') . "\n\n"
            . "Reservasi Kakak telah *dibatalkan otomatis* oleh sistem.\n\n"
            . "Silakan kirim pesan baru jika ingin membuat reservasi ulang ya Kak! 👋";

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
     * Send 1-hour before reminder.
     */
    public function sendOneHourReminder(Booking $booking): bool
    {
        $booking->loadMissing(['customer', 'barber.user', 'service']);

        $message = "⏰ *1 Jam Lagi!*\n\n"
            . "Halo Kak {$booking->customer->name}, jadwalmu mulai dalam 1 jam:\n\n"
            . "💈 *{$booking->service->name}* bersama {$booking->barber->displayName()} pada pukul {$booking->scheduled_at->format('H:i')}\n\n"
            . "Pastikan kamu sudah bersiap-siap menuju lokasi ya! 🪒";

        return $this->sendText($booking->customer->wa_id, $message);
    }

    /**
     * Send reconfirmation request.
     */
    public function sendReconfirmationRequest(Booking $booking, int $minutes = 30): bool
    {
        $booking->loadMissing(['customer']);

        $body = "⏰ *{$minutes} Menit Lagi!*\n\n"
            . "Halo {$booking->customer->name}, jadwalmu {$minutes} menit lagi. Apakah kamu konfirmasi kehadiran untuk datang ke lokasi?\n\n"
            . "_(Jika tidak ada respons hingga waktu jadwal, booking akan dibatalkan otomatis)_";

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

    private function send(string $waId, string $message): bool
    {
        $url = "{$this->nodeUrl}/send";

        try {
            $res = $this->http->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'to'      => $waId,
                    'message' => $message,
                ],
            ]);

            $status = $res->getStatusCode();
            $ok     = $status >= 200 && $status < 300;

            if (! $ok) {
                Log::error('[WhatsAppService] Send failed', ['status' => $status, 'wa_id' => $waId]);
            }

            return $ok;
        } catch (\Throwable $e) {
            Log::error('[WhatsAppService] HTTP error connecting to Node.js server', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
