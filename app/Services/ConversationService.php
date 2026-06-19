<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Jobs\ExpireLockedBookingJob;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ConversationService
{
    public function __construct(
        private GeminiService   $gemini,
        private CapacityEngine  $capacity,
        private WhatsAppService $whatsapp,
        private MidtransService $midtrans,
    ) {}

    /**
     * Main entry point for incoming WhatsApp messages.
     *
     * Flow:
     *  1. If paused (admin takeover) → ignore or resume after timeout
     *  2. If interactive button → handle arrival/cancel
     *  3. If idle OR session timed out (>10 min) → send greeting
     *  4. Otherwise → run Gemini dialog manager
     */
    public function handle(string $waId, string $messageText, string $messageType = 'text'): void
    {
        $customer = Customer::firstOrCreate(
            ['wa_id' => $waId],
            ['name' => 'Pelanggan Baru', 'phone' => $waId]
        );

        Log::info('[ConversationService] Incoming message', [
            'wa_id' => $waId,
            'state' => $customer->conversation_state,
            'type'  => $messageType,
        ]);

        // ── 1. Paused / Admin takeover ───────────────────────────────────────
        if ($customer->conversation_state === 'paused') {
            $context     = $customer->conversation_context ?? [];
            $pausedUntil = $context['paused_until'] ?? null;

            if ($pausedUntil && Carbon::parse($pausedUntil)->isFuture()) {
                Log::info('[ConversationService] Message ignored, customer is paused (admin takeover).');
                return;
            }

            // Pause expired → resume
            $customer->resetConversation();
        }

        // ── 2. Interactive button (arrival confirm / booking cancel) ─────────
        if ($messageType === 'interactive_button') {
            $this->handleButtonReply($customer, $messageText);
            return;
        }

        // ── 3. Session timeout (10 minutes) or fresh idle state → greeting ───
        $isTimedOut = $customer->conversation_state !== 'idle'
            && $customer->updated_at
            && now()->diffInMinutes($customer->updated_at) >= 10;

        if ($customer->conversation_state === 'idle' || $isTimedOut) {
            if ($isTimedOut) {
                Log::info('[ConversationService] Session timed out, resetting and greeting.');
                $customer->resetConversation();
            }
            $this->sendGreeting($customer);
            return;
        }

        // ── 4. Active conversation → Gemini dialog manager ───────────────────
        $this->handleIncomingMessage($customer, $messageText);
    }

    // ── Private Handlers ─────────────────────────────────────────────────────

    /**
     * Send the standard greeting/welcome template and transition to 'collecting'.
     */
    private function sendGreeting(Customer $customer): void
    {
        try {
            $services    = Service::where('is_active', true)->get();
            $serviceList = $services->map(fn($s) => "- {$s->name}")->join("\n");
        } catch (\Throwable $e) {
            $serviceList = "- Cukur Anak-anak\n- Cukur Dewasa\n- Cukur Gundul\n- Potong Jenggot & Kumis";
        }

        $shopName = \App\Models\Setting::get('shop_name', 'SISIR Barber');

        $greeting = "Halo Kak! 👋 Terima kasih sudah menghubungi {$shopName} 🪒\n\n"
            . "Ingin reservasi cukur? Berikut adalah layanan kami:\n"
            . $serviceList . "\n\n"
            . "Kakak tinggal kirim *Nama*, *Layanan*, serta *Hari & Jam* kedatangan Kakak. Nanti aku bantu reservasi ya! ✂️";

        $this->whatsapp->sendText($customer->wa_id, $greeting);

        $customer->updateConversationState('collecting', [
            'slots'   => $this->emptySlots($customer),
            'history' => [],
        ]);
    }

    /**
     * Handle incoming text message via Gemini dialog manager.
     * Routes based on intent returned by Gemini.
     */
    private function handleIncomingMessage(Customer $customer, string $messageText): void
    {
        $context = $customer->conversation_context ?? [];
        $slots   = $context['slots']   ?? $this->emptySlots($customer);
        $history = $context['history'] ?? [];

        // Keep last 10 exchanges (20 entries) to avoid token bloat
        if (count($history) > 20) {
            $history = array_slice($history, -20);
        }

        $refDateTime = now('Asia/Jakarta')->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm:ss');

        $result = $this->gemini->runDialogManager($messageText, $history, $slots, $refDateTime);

        $intent      = $result['intent']             ?? 'booking';
        $slots       = $result['updated_slots']      ?? $slots;
        $allComplete = $result['all_slots_complete'] ?? false;
        $response    = $result['response']           ?? '';

        Log::info('[ConversationService] Gemini result', [
            'intent'       => $intent,
            'all_complete' => $allComplete,
            'slots'        => $slots,
        ]);

        // ── Handover to admin ────────────────────────────────────────────────
        if ($intent === 'handover') {
            $this->whatsapp->sendText(
                $customer->wa_id,
                "Baik Kak, mohon ditunggu sebentar ya. Chat Kakak sedang aku sambungkan ke Admin kami. 👨‍💼"
            );
            $customer->updateConversationState('paused', [
                'paused_until' => now('Asia/Jakarta')->addHours(1)->toDateTimeString(),
            ]);
            return;
        }

        // ── All booking slots complete → create booking ──────────────────────
        if ($intent === 'booking' && $allComplete) {
            // Send confirmation response first, then process
            if ($response) {
                $this->whatsapp->sendText($customer->wa_id, $response);
            }
            $this->createBookingFromSlots($customer, $slots);
            return;
        }

        // ── API error → send fallback, don't change state ───────────────────
        if ($intent === 'error') {
            if ($response) {
                $this->whatsapp->sendText($customer->wa_id, $response);
            }
            // Do NOT update context on error, preserve existing slots/history
            return;
        }

        // ── Send Gemini response (faq / oot / booking collecting) ────────────
        if ($response) {
            $this->whatsapp->sendText($customer->wa_id, $response);
        }

        $isAskingForSchedule = (bool) preg_match('/(jam.*kosong|slot.*kosong|jadwal|ketersediaan|buka.*jam|jam.*buka)/i', $messageText);
        if ($isAskingForSchedule) {
            $date = $slots['day'] ?? now('Asia/Jakarta')->toDateString();
            \App\Jobs\SendScheduleImageJob::dispatch($customer->wa_id, $date);
        }

        // Append to history and persist
        $history[] = ['role' => 'user', 'message' => $messageText];
        $history[] = ['role' => 'bot',  'message' => $response];

        $customer->update([
            'conversation_context' => [
                'slots'   => $slots,
                'history' => $history,
            ],
        ]);
    }

    /**
     * Create booking when all 4 slots are complete, then generate Midtrans payment link.
     */
    private function createBookingFromSlots(Customer $customer, array $slots): void
    {
        // Update customer name if collected
        if (!empty($slots['name'])) {
            $customer->update(['name' => $slots['name']]);
            $customer->refresh();
        }

        // ── Resolve service ──────────────────────────────────────────────────
        $serviceName = $slots['service'] ?? '';
        $service = Service::where('is_active', true)->where('name', $serviceName)->first()
            ?? Service::where('is_active', true)->where('name', 'like', "%{$serviceName}%")->first()
            ?? Service::where('is_active', true)->first();

        if (!$service) {
            $this->whatsapp->sendText($customer->wa_id, 'Maaf Kak, layanan tidak ditemukan. Bisa sebutkan ulang layanannya?');
            return;
        }

        // ── Parse scheduled datetime ─────────────────────────────────────────
        try {
            $scheduledAt = Carbon::parse(($slots['day'] ?? '') . ' ' . ($slots['time'] ?? ''), 'Asia/Jakarta');
            if (!$scheduledAt->isValid()) {
                throw new \Exception('Invalid date');
            }
        } catch (\Throwable $e) {
            Log::warning('[ConversationService] Could not parse scheduled time', ['slots' => $slots]);
            $this->whatsapp->sendText($customer->wa_id, 'Maaf Kak, ada masalah dengan waktu yang dipilih. Bisa disebutkan ulang hari dan jamnya?');
            return;
        }

        // ── Find available barber ────────────────────────────────────────────
        // Iterasi semua barber yang aktif, pilih barber pertama yang slotnya kosong.
        // Dengan 2+ barber, sistem otomatis support 2+ booking paralel di waktu yang sama.
        $barbers         = Barber::where('is_active', true)->get();
        $availableBarber = null;

        foreach ($barbers as $barber) {
            $barberSlots = $this->capacity->getAvailableSlots($barber->id, $scheduledAt);
            $slot        = $barberSlots->firstWhere('time', $scheduledAt->format('H:i'));
            if ($slot && ($slot['available'] ?? false)) {
                $availableBarber = $barber;
                break;
            }
        }

        if (!$availableBarber) {
            $this->whatsapp->sendText($customer->wa_id, 'Maaf Kak, semua kapster sudah penuh di waktu itu 😔 Boleh coba waktu lain?');
            return;
        }

        $dpPercent = (int) \App\Models\Setting::get('dp_amount', 50);
        $dpAmount  = (int) ceil($service->price * ($dpPercent / 100));

        // ── Create booking record ────────────────────────────────────────────
        $booking = Booking::create([
            'customer_id'  => $customer->id,
            'barber_id'    => $availableBarber->id,
            'service_id'   => $service->id,
            'scheduled_at' => $scheduledAt,
            'status'       => BookingStatus::TEMP_LOCKED->value,
            'dp_amount'    => $dpAmount,
        ]);

        $locked = $this->capacity->lockSlot($booking->id, $booking->barber_id, $scheduledAt);

        if (!$locked) {
            $booking->delete();
            $this->whatsapp->sendText(
                $customer->wa_id,
                'Maaf Kak, slot jam tersebut baru saja diambil orang lain 😔 Boleh pilih waktu lain?'
            );
            return;
        }

        // ── Generate Midtrans QRIS and send payment request ──────────────────
        $dayFormatted  = $scheduledAt->locale('id')->isoFormat('dddd, D MMMM YYYY');
        $timeFormatted = $scheduledAt->format('H:i');
        $ttlMinutes    = (int) ceil(config('sisir.slot_lock_ttl', 600) / 60);

        // ── Step 1: Create Midtrans QRIS charge ──────────────────────────────
        try {
            $this->midtrans->createDPCharge($booking);
        } catch (\Throwable $e) {
            Log::error('[ConversationService] Midtrans charge creation failed', ['error' => $e->getMessage()]);

            // Booking slot is locked but payment link could not be generated.
            // Notify customer to contact admin, do NOT imply booking is confirmed.
            $this->whatsapp->sendText(
                $customer->wa_id,
                "⚠️ Maaf Kak, ada kendala teknis saat membuat link pembayaran.\n\n"
                . "Reservasi untuk layanan *{$service->name}* pada *{$dayFormatted}* jam *{$timeFormatted}* "
                . "sudah tercatat namun *belum terkonfirmasi*.\n\n"
                . "Mohon hubungi admin kami langsung untuk menyelesaikan pembayaran DP "
                . "sebesar *Rp " . number_format($dpAmount, 0, ',', '.') . "* ya Kak 🙏"
            );
            $customer->resetConversation();
            return;
        }

        $qrUrl = $booking->midtrans_qr_code_url;

        // ── Step 2: Send QRIS to customer ─────────────────────────────────────
        // IMPORTANT: This message must NOT imply the booking is confirmed.
        // The actual booking confirmation (with booking code) is only sent by
        // SendBookingTicketJob AFTER Midtrans confirms payment via webhook.
        $paymentMessage = "🧾 *Permintaan Pembayaran DP*\n\n"
            . "Halo Kak *{$customer->name}*! Reservasi berikut sedang menunggu pembayaran:\n\n"
            . "💈 *Layanan:* {$service->name}\n"
            . "🗓️ *Jadwal:* {$dayFormatted}, pukul {$timeFormatted}\n"
            . "💳 *DP:* Rp " . number_format($dpAmount, 0, ',', '.') . "\n\n"
            . "Silakan *scan QRIS* di bawah ini untuk menyelesaikan pembayaran.\n"
            . "⏳ Batas waktu: *{$ttlMinutes} menit*.\n\n"
            . "_Kode booking dan konfirmasi akan dikirim otomatis setelah pembayaran berhasil._";

        if ($qrUrl) {
            $this->whatsapp->sendMedia($customer->wa_id, $qrUrl, $paymentMessage);
        } else {
            $this->whatsapp->sendText($customer->wa_id, $paymentMessage);
        }

        Log::info('[ConversationService] QRIS payment request sent', [
            'booking_id' => $booking->id,
            'qr_url'     => $qrUrl,
        ]);

        // ── Step 3: Schedule auto-expiry job (isolated) ───────────────────────
        // Wrapped separately so a queue connection error does NOT trigger a
        // fallback message that falsely implies booking confirmation.
        try {
            $ttlSeconds = (int) config('sisir.slot_lock_ttl', 600);
            ExpireLockedBookingJob::dispatch($booking->id)
                ->delay(now()->addSeconds($ttlSeconds))
                ->onQueue('reminders');

            Log::info('[ConversationService] ExpireLockedBookingJob scheduled', [
                'booking_id' => $booking->id,
                'expires_in' => "{$ttlSeconds}s",
            ]);
        } catch (\Throwable $e) {
            // Queue connection issue — log only, do NOT send any WA message.
            // Midtrans will still expire the transaction on its own after TTL.
            Log::error('[ConversationService] Failed to schedule ExpireLockedBookingJob', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
        }

        $customer->resetConversation();
    }

    /**
     * Handle interactive button replies (arrival confirmation, booking cancellation).
     * These are triggered by reminder messages sent by the system.
     */
    private function handleButtonReply(Customer $customer, string $buttonId): void
    {
        match (true) {
            str_starts_with($buttonId, 'confirm_arrival_') => $this->handleArrivalConfirmation($customer, $buttonId),
            str_starts_with($buttonId, 'cancel_booking_')  => $this->handleCustomerCancel($customer, $buttonId),
            default                                         => null,
        };
    }

    private function handleArrivalConfirmation(Customer $customer, string $buttonId): void
    {
        $bookingId = (int) str_replace('confirm_arrival_', '', $buttonId);
        $booking   = Booking::find($bookingId);

        if ($booking && $booking->customer_id === $customer->id) {
            $booking->transitionTo(BookingStatus::CONFIRMED);
            $this->whatsapp->sendText($customer->wa_id, '✅ Kedatangan dikonfirmasi! Sampai jumpa segera 🪒');
        }
    }

    private function handleCustomerCancel(Customer $customer, string $buttonId): void
    {
        $bookingId = (int) str_replace('cancel_booking_', '', $buttonId);
        $booking   = Booking::find($bookingId);

        if ($booking && $booking->customer_id === $customer->id) {
            $booking->transitionTo(BookingStatus::CANCELLED_BY_SYSTEM, 'Pelanggan membatalkan via WhatsApp.');
            $this->capacity->releaseSlot($booking->id, $booking->barber_id, $booking->scheduled_at);
            $this->whatsapp->sendText($customer->wa_id, '❌ Booking dibatalkan. DP tidak dapat dikembalikan.');
        }
    }

    /**
     * Empty slot template for a new conversation session.
     */
    private function emptySlots(Customer $customer): array
    {
        return [
            'name'    => null,
            'service' => null,
            'day'     => null,
            'time'    => null,
            'phone'   => $customer->wa_id,
        ];
    }
}
