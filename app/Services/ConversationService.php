<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Jobs\SendBookingTicketJob;
use App\Jobs\SendReminderJob;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ConversationService
{
    public function __construct(
        private GeminiService  $gemini,
        private CapacityEngine $capacity,
        private WhatsAppService $whatsapp,
        private MidtransService $midtrans,
    ) {}

    /**
     * Main entry point for incoming WhatsApp messages.
     * Routes based on current conversation state and Gemini intent.
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

        // Route interactive button responses differently
        if ($messageType === 'interactive_button') {
            $this->handleButtonReply($customer, $messageText);
            return;
        }

        if ($customer->conversation_state === 'awaiting_confirm') {
            $this->handleBookingConfirmation($customer, $messageText);
            return;
        }

        // Direct all text messages to the dynamic Gemini Dialog Manager
        $this->handleIncomingTextMessage($customer, $messageText);
    }

    // ── State Handlers ───────────────────────────────────────────────────────

    private function handleIdle(Customer $customer, string $message): void
    {
        $context = $customer->conversation_context ?? [];
        $intent  = $this->gemini->parseIntent($message, $customer->wa_id, $context);

        Log::info('[ConversationService] Intent parsed', ['intent' => $intent]);

        if ($this->gemini->isAmbiguous($intent)) {
            $this->whatsapp->sendInteractiveButtons(
                $customer->wa_id,
                "Halo! Saya SISIR Bot 🪒\n\nMaaf, saya kurang mengerti maksudmu. Pilih salah satu:",
                [
                    ['id' => 'action_booking',    'title' => '📅 Booking Sekarang'],
                    ['id' => 'action_manual',     'title' => '📋 Pilih Manual'],
                    ['id' => 'action_admin',      'title' => '💬 Bicara Admin'],
                ]
            );
            return;
        }

        match ($intent['intent'] ?? 'faq') {
            'booking'       => $this->startBookingFlow($customer, $intent),
            'reschedule'    => $this->handleReschedule($customer, $intent),
            'cancel'        => $this->handleCancellation($customer, $intent),
            'status_check'  => $this->handleStatusCheck($customer),
            'waitlist'      => $this->handleWaitlistJoin($customer),
            default         => $this->sendWelcomeMessage($customer),
        };
    }

    private function startBookingFlow(Customer $customer, array $intent): void
    {
        $services = Service::where('is_active', true)->get();

        if ($services->isEmpty()) {
            $this->whatsapp->sendText($customer->wa_id, 'Maaf, belum ada layanan tersedia saat ini.');
            return;
        }

        $serviceList = $services->map(fn ($s, $i) => ($i + 1) . ". {$s->name} - {$s->formattedPrice()} ({$s->duration_minutes} menit)")->join("\n");

        $this->whatsapp->sendText(
            $customer->wa_id,
            "Pilih layanan yang diinginkan:\n\n{$serviceList}\n\nBalas dengan nomor atau nama layanan."
        );

        $customer->updateConversationState('awaiting_service', [
            'intent'   => $intent,
            'services' => $services->pluck('name', 'id')->toArray(),
        ]);
    }

    private function handleServiceSelection(Customer $customer, string $message): void
    {
        $services = Service::where('is_active', true)->get();
        $selected = null;

        // Try numeric selection first
        if (is_numeric(trim($message))) {
            $index    = (int) trim($message) - 1;
            $selected = $services->values()->get($index);
        }

        // Try name matching
        if (! $selected) {
            $selected = $services->first(fn ($s) => str_contains(
                strtolower($s->name), strtolower(trim($message))
            ));
        }

        if (! $selected) {
            $this->whatsapp->sendText($customer->wa_id, 'Layanan tidak ditemukan. Coba lagi ya.');
            return;
        }

        $barbers      = Barber::where('is_active', true)->with('user')->get();
        $barberList   = $barbers->map(fn ($b, $i) => ($i + 1) . ". {$b->displayName()}")->join("\n");

        $this->whatsapp->sendText(
            $customer->wa_id,
            "Pilih kapster:\n\n{$barberList}\n\nBalas dengan nomor atau nama kapster."
        );

        $customer->updateConversationState('awaiting_barber', [
            'service_id' => $selected->id,
            'service'    => $selected->name,
        ]);
    }

    private function handleBarberSelection(Customer $customer, string $message): void
    {
        $barbers  = Barber::where('is_active', true)->with('user')->get();
        $selected = null;

        if (is_numeric(trim($message))) {
            $selected = $barbers->values()->get((int) trim($message) - 1);
        }

        if (! $selected) {
            $selected = $barbers->first(fn ($b) => str_contains(
                strtolower($b->displayName()), strtolower(trim($message))
            ));
        }

        if (! $selected) {
            $this->whatsapp->sendText($customer->wa_id, 'Kapster tidak ditemukan. Coba lagi ya.');
            return;
        }

        $this->whatsapp->sendText(
            $customer->wa_id,
            "Mau booking jam berapa? 🕐\n\n_(Contoh: besok jam 2 siang, abis dzuhur, Senin pagi)_"
        );

        $customer->updateConversationState('awaiting_time', [
            'barber_id' => $selected->id,
            'barber'    => $selected->displayName(),
        ]);
    }

    private function handleTimeSelection(Customer $customer, string $message): void
    {
        $parsedTime = $this->gemini->parseTime($message);

        if (! $parsedTime) {
            $this->whatsapp->sendInteractiveButtons(
                $customer->wa_id,
                'Maaf, saya tidak bisa memahami waktu yang dimaksud. Mau pilih cara lain?',
                [
                    ['id' => 'action_manual', 'title' => '📋 Pilih Jam Manual'],
                    ['id' => 'action_admin',  'title' => '💬 Bicara Admin'],
                ]
            );
            return;
        }

        $context   = $customer->conversation_context ?? [];
        $barberId  = $context['barber_id'];
        $slots     = $this->capacity->getAvailableSlots($barberId, $parsedTime);
        $available = $slots->firstWhere('time', $parsedTime->format('H:i'));

        if (! $available || ! $available['available']) {
            $nextSlots = $slots->where('available', true)->take(3);
            $options   = $nextSlots->map(fn ($s) => "- {$s['time']}")->join("\n");

            $this->whatsapp->sendText(
                $customer->wa_id,
                "Slot jam {$parsedTime->format('H:i')} sudah terisi 😔\n\nSlot tersedia berikutnya:\n{$options}\n\nBalas dengan jam yang diinginkan."
            );
            return;
        }

        if (empty($customer->name) || $customer->name === 'Pelanggan Baru') {
            $this->whatsapp->sendText($customer->wa_id, 'Siapa nama kamu? 😊');
            $customer->updateConversationState('awaiting_name', [
                'scheduled_at' => $parsedTime->toDateTimeString(),
            ]);
            return;
        }

        $this->showBookingSummary($customer, $parsedTime);
    }

    private function handleNameCollection(Customer $customer, string $message): void
    {
        $customer->update(['name' => trim($message)]);
        $context      = $customer->conversation_context ?? [];
        $scheduledAt  = Carbon::parse($context['scheduled_at']);
        $this->showBookingSummary($customer, $scheduledAt);
    }

    private function showBookingSummary(Customer $customer, Carbon $scheduledAt): void
    {
        $context   = $customer->conversation_context ?? [];
        $service   = Service::find($context['service_id']);
        $barber    = Barber::with('user')->find($context['barber_id']);

        $dpAmount = (int) ceil($service->price * 0.5); // 50% dari harga layanan

        $summary = "📋 *Ringkasan Booking*\n\n"
            . "👤 Nama: {$customer->name}\n"
            . "🪒 Layanan: {$service->name}\n"
            . "💈 Kapster: {$barber->displayName()}\n"
            . "📅 Jadwal: {$scheduledAt->locale('id')->isoFormat('dddd, D MMMM YYYY [pukul] HH:mm')}\n"
            . "💳 DP: Rp " . number_format($dpAmount, 0, ',', '.') . " (50% dari Rp " . number_format($service->price, 0, ',', '.') . ", non-refundable)\n\n"
            . "Konfirmasi booking?";

        $this->whatsapp->sendInteractiveButtons(
            $customer->wa_id,
            $summary,
            [
                ['id' => 'confirm_booking', 'title' => '✅ Konfirmasi'],
                ['id' => 'cancel_flow',     'title' => '❌ Batalkan'],
            ]
        );

        $customer->updateConversationState('awaiting_confirm', [
            'scheduled_at' => $scheduledAt->toDateTimeString(),
        ]);
    }

    private function handleBookingConfirmation(Customer $customer, string $message): void
    {
        if (! str_contains(strtolower($message), 'ya') && ! str_contains($message, '1')) {
            $customer->resetConversation();
            $this->whatsapp->sendText($customer->wa_id, 'Booking dibatalkan. Ketik "booking" untuk mulai lagi.');
            return;
        }

        $this->createBookingAndCharge($customer);
    }

    private function createBookingAndCharge(Customer $customer): void
    {
        $context      = $customer->conversation_context ?? [];
        $scheduledAt  = Carbon::parse($context['scheduled_at']);

        $service = Service::find($context['service_id']);
        $dpAmount = $service ? (int) ceil($service->price * 0.5) : (int) config('sisir.dp_amount');

        $booking = Booking::create([
            'customer_id'  => $customer->id,
            'barber_id'    => $context['barber_id'],
            'service_id'   => $context['service_id'],
            'scheduled_at' => $scheduledAt,
            'status'       => BookingStatus::TEMP_LOCKED->value,
            'dp_amount'    => $dpAmount,
        ]);

        // Lock the slot
        $locked = $this->capacity->lockSlot($booking->id, $booking->barber_id, $scheduledAt);

        if (! $locked) {
            $booking->delete();
            $this->whatsapp->sendText(
                $customer->wa_id,
                'Maaf, slot tersebut baru saja diambil orang lain 😔 Silakan pilih waktu lain.'
            );
            $customer->updateConversationState('awaiting_time');
            return;
        }

        // Generate Midtrans QRIS
        try {
            $charge = $this->midtrans->createDPCharge($booking);
            $qrUrl  = $charge['actions'][0]['url'] ?? null;

            $message = "💳 *Bayar DP sekarang!*\n\n"
                . "Scan QR di bawah untuk membayar DP sebesar *Rp " . number_format($booking->dp_amount, 0, ',', '.') . "*\n\n"
                . ($qrUrl ? "🔗 Link QR: {$qrUrl}\n\n" : '')
                . "⏰ QR berlaku 10 menit. Booking akan otomatis dibatalkan jika DP belum dibayar.";

            $this->whatsapp->sendText($customer->wa_id, $message);
        } catch (\Throwable $e) {
            Log::error('[ConversationService] Midtrans charge failed', ['error' => $e->getMessage()]);
            $this->whatsapp->sendText($customer->wa_id, 'Ada masalah saat membuat tagihan. Coba lagi atau hubungi admin.');
        }

        $customer->resetConversation();
    }

    private function handleStatusCheck(Customer $customer): void
    {
        $booking = $customer->activeBooking();

        if (! $booking) {
            $this->whatsapp->sendText($customer->wa_id, 'Kamu tidak memiliki booking aktif saat ini.');
            return;
        }

        $booking->loadMissing(['service', 'barber.user']);
        $this->whatsapp->sendText(
            $customer->wa_id,
            "📋 *Status Booking #" . $booking->id . "*\n\n"
            . "Status: *{$booking->status->label()}*\n"
            . "Layanan: {$booking->service->name}\n"
            . "Kapster: {$booking->barber->displayName()}\n"
            . "Jadwal: {$booking->scheduledAtFormatted()}"
        );
    }

    private function handleReschedule(Customer $customer, array $intent): void
    {
        $this->whatsapp->sendText(
            $customer->wa_id,
            'Untuk reschedule, silakan hubungi admin kami langsung. Terima kasih!'
        );
    }

    private function handleCancellation(Customer $customer, array $intent): void
    {
        $booking = $customer->activeBooking();

        if (! $booking) {
            $this->whatsapp->sendText($customer->wa_id, 'Tidak ada booking aktif yang bisa dibatalkan.');
            return;
        }

        $this->whatsapp->sendInteractiveButtons(
            $customer->wa_id,
            "⚠️ Yakin ingin membatalkan booking #{$booking->id}?\n\nDP yang sudah dibayar tidak dapat dikembalikan.",
            [
                ['id' => "confirm_cancel_{$booking->id}", 'title' => '✅ Ya, Batalkan'],
                ['id' => 'keep_booking',                  'title' => '❌ Tidak, Lanjutkan'],
            ]
        );
    }

    private function handleWaitlistJoin(Customer $customer): void
    {
        \App\Models\Waitlist::create([
            'customer_id' => $customer->id,
            'is_active'   => true,
        ]);

        $this->whatsapp->sendText(
            $customer->wa_id,
            '✅ Kamu telah ditambahkan ke daftar tunggu! Kami akan notifikasi jika ada slot tersedia.'
        );
    }

    private function handleButtonReply(Customer $customer, string $buttonId): void
    {
        match (true) {
            str_starts_with($buttonId, 'confirm_arrival_') => $this->handleArrivalConfirmation($customer, $buttonId),
            str_starts_with($buttonId, 'cancel_booking_')  => $this->handleCustomerCancel($customer, $buttonId),
            $buttonId === 'confirm_booking'                 => $this->createBookingAndCharge($customer),
            $buttonId === 'cancel_flow'                     => $this->cancelFlow($customer),
            $buttonId === 'action_booking'                  => $this->startBookingFlow($customer, []),
            $buttonId === 'action_admin'                    => $this->whatsapp->sendText($customer->wa_id, 'Menghubungkan ke admin... 👋'),
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

    private function cancelFlow(Customer $customer): void
    {
        $customer->resetConversation();
        $this->whatsapp->sendText($customer->wa_id, 'Booking dibatalkan. Ketik "booking" kapan saja untuk mulai lagi.');
    }

    private function sendWelcomeMessage(Customer $customer): void
    {
        $this->whatsapp->sendInteractiveButtons(
            $customer->wa_id,
            "Halo! 👋 Selamat datang di *SISIR Barber* 🪒\n\nAda yang bisa saya bantu?",
            [
                ['id' => 'action_booking', 'title' => '📅 Booking Sekarang'],
                ['id' => 'action_status',  'title' => '📋 Cek Status Booking'],
                ['id' => 'action_admin',   'title' => '💬 Bicara Admin'],
            ]
        );
    }

    private function handleIncomingTextMessage(Customer $customer, string $messageText): void
    {
        $context = $customer->conversation_context ?? [];
        $slots = $context['slots'] ?? [
            'name' => null,
            'phone' => $customer->wa_id,
            'service' => null,
            'date' => null,
            'time' => null,
            'barber' => null,
        ];
        
        if (empty($slots['phone'])) {
            $slots['phone'] = $customer->wa_id;
        }
        $history = $context['history'] ?? [];

        // Keep last 10 turns of history (25 elements max)
        if (count($history) > 20) {
            $history = array_slice($history, -20);
        }

        $refDateTime = now('Asia/Jakarta')->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm:ss');
        $referenceDateOnly = now('Asia/Jakarta')->toDateString();

        // Run Gemini Dialog Manager
        $result = $this->gemini->runDialogManager($messageText, $history, $slots, $refDateTime);

        $slots = $result['updated_slots'] ?? $slots;
        $action = $result['action'] ?? 'none';
        $actionParams = $result['action_parameters'] ?? [];
        $response = $result['response'] ?? '';

        $toolResult = null;

        if ($action !== 'none') {
            if ($action === 'cek_ketersediaan') {
                $toolResult = $this->executeCekKetersediaan($actionParams);
            } elseif ($action === 'tampilkan_konfirmasi') {
                $this->executeTampilkanKonfirmasi($customer, $actionParams, $history, $slots);
                return; // Early return to let user confirm via button/text
            } elseif ($action === 'faq_rag') {
                $toolResult = $this->executeFaqRag($actionParams);
            }

            if ($toolResult) {
                // Call Gemini second turn to get the natural response
                $response = $this->gemini->generateFinalResponse($messageText, $history, $slots, $refDateTime, $toolResult);
            }
        }

        // Append to history
        $history[] = ['role' => 'user', 'message' => $messageText];
        $history[] = ['role' => 'bot', 'message' => $response];

        // Save context
        $customer->update([
            'conversation_context' => [
                'slots' => $slots,
                'history' => $history,
            ]
        ]);

        // Send message to WhatsApp
        $this->whatsapp->sendText($customer->wa_id, $response);
    }

    private function executeCekKetersediaan(array $params): string
    {
        $date = $params['date'] ?? null;
        $time = $params['time'] ?? null;
        $serviceName = $params['service'] ?? null;

        if (!$date) {
            $date = now('Asia/Jakarta')->toDateString();
        }

        $relativeDate = $date;
        try {
            $refDate = Carbon::parse($date);
            if ($refDate->isToday()) {
                $relativeDate = "hari ini";
            } elseif ($refDate->isTomorrow()) {
                $relativeDate = "besok";
            }
        } catch (\Throwable $e) {}

        // Log the background check requested by the user
        Log::info("[Sistem Latar Belakang: AI menjalankan cek_ketersediaan()]");
        Log::info("[Sistem Latar Belakang: AI menjalankan fungsi cek_ketersediaan(tanggal=\"{$relativeDate}\", jam=\"{$time}\", layanan=\"{$serviceName}\") ke database]");

        if (!$time) {
            return json_encode([
                'status' => 'error',
                'message' => 'Mohon tentukan jam booking yang diinginkan.'
            ]);
        }

        $dateTime = Carbon::parse("$date $time", 'Asia/Jakarta');
        $barbers = Barber::where('is_active', true)->get();
        $availableBarber = null;

        foreach ($barbers as $barber) {
            $slots = $this->capacity->getAvailableSlots($barber->id, $dateTime);
            $slot = $slots->firstWhere('time', $dateTime->format('H:i'));
            if ($slot && ($slot['available'] ?? false)) {
                $availableBarber = $barber;
                break;
            }
        }

        if ($availableBarber) {
            return json_encode([
                'status' => 'available',
                'barber_name' => $availableBarber->displayName(),
                'message' => "Slot jam {$time} untuk {$serviceName} pada tanggal {$relativeDate} masih kosong dengan Kapster {$availableBarber->displayName()}."
            ]);
        }

        // Slot is full, search for alternatives
        $altToday = null;
        $altTomorrow = null;

        // 1. Next available slot today after the requested hour
        $todayDate = Carbon::today('Asia/Jakarta');
        $earliestTime = null;
        
        foreach ($barbers as $barber) {
            $slotsToday = $this->capacity->getAvailableSlots($barber->id, $todayDate);
            foreach ($slotsToday as $slot) {
                if ($slot['available'] ?? false) {
                    $slotTime = Carbon::parse($todayDate->toDateString() . ' ' . $slot['time'], 'Asia/Jakarta');
                    if ($slotTime->gt($dateTime)) {
                        if ($earliestTime === null || $slotTime->lt($earliestTime)) {
                            $earliestTime = $slotTime;
                        }
                    }
                }
            }
        }
        if ($earliestTime) {
            $altToday = $earliestTime->format('H:i');
        }

        // 2. Check if tomorrow at the same requested time is free
        $tomorrowDate = Carbon::tomorrow('Asia/Jakarta');
        $tomorrowDateTime = Carbon::parse($tomorrowDate->toDateString() . ' ' . $time, 'Asia/Jakarta');
        $tomorrowAvailable = false;
        foreach ($barbers as $barber) {
            $slotsTomorrow = $this->capacity->getAvailableSlots($barber->id, $tomorrowDate);
            $slot = $slotsTomorrow->firstWhere('time', $tomorrowDateTime->format('H:i'));
            if ($slot && ($slot['available'] ?? false)) {
                $tomorrowAvailable = true;
                break;
            }
        }
        if ($tomorrowAvailable) {
            $altTomorrow = $tomorrowDateTime->format('H:i');
        }

        // Construct alternatives message
        if ($altToday && $altTomorrow) {
            $message = "Slot jam {$time} {$relativeDate} sudah penuh. Yang tersisa hari ini ada di jam {$altToday}, atau besok jam {$altTomorrow}.";
        } elseif ($altToday) {
            $message = "Slot jam {$time} {$relativeDate} sudah penuh. Yang tersisa hari ini ada di jam {$altToday}.";
        } elseif ($altTomorrow) {
            $message = "Slot jam {$time} {$relativeDate} sudah penuh. Alternatif yang tersedia besok jam {$altTomorrow}.";
        } else {
            // Fallback: get any available slots tomorrow
            $altSlots = [];
            foreach ($barbers as $barber) {
                $slotsTomorrow = $this->capacity->getAvailableSlots($barber->id, $tomorrowDate);
                foreach ($slotsTomorrow as $slot) {
                    if ($slot['available'] ?? false) {
                        $altSlots[] = "besok jam " . $slot['time'];
                        if (count($altSlots) >= 2) break 2;
                    }
                }
            }
            $altString = count($altSlots) > 0 ? implode(" atau ", $altSlots) : "tidak ada slot terdekat";
            $message = "Slot jam {$time} {$relativeDate} sudah penuh. Alternatif yang tersedia: {$altString}.";
        }

        return json_encode([
            'status' => 'full',
            'message' => $message
        ]);
    }

    private function executeTampilkanKonfirmasi(Customer $customer, array $params, array $history, array $slots): void
    {
        $name = $params['name'] ?? 'Pelanggan';
        $phone = $params['phone'] ?? $customer->wa_id;
        $serviceName = $params['service'] ?? '';
        $date = $params['date'] ?? now('Asia/Jakarta')->toDateString();
        $time = $params['time'] ?? '16:00';

        $customer->update([
            'name' => $name,
            'phone' => $phone
        ]);

        $service = Service::where('name', $serviceName)->first() ?? Service::where('is_active', true)->first();
        $scheduledAt = Carbon::parse("$date $time", 'Asia/Jakarta');

        $barbers = Barber::where('is_active', true)->get();
        $availableBarber = $barbers->first(); // Default fallback
        foreach ($barbers as $b) {
            $slotsData = $this->capacity->getAvailableSlots($b->id, $scheduledAt);
            $slot = $slotsData->firstWhere('time', $scheduledAt->format('H:i'));
            if ($slot && ($slot['available'] ?? false)) {
                $availableBarber = $b;
                break;
            }
        }

        // Use the existing slots and history so we don't lose them if cancelled
        $customer->updateConversationState('awaiting_confirm', [
            'service_id' => $service->id,
            'barber_id' => $availableBarber->id,
            'scheduled_at' => $scheduledAt->toDateTimeString(),
            'slots' => $slots,
            'history' => $history,
        ]);

        $this->showBookingSummary($customer, $scheduledAt);
    }

    private function executeFaqRag(array $params): string
    {
        $query = $params['query'] ?? 'kebijakan';

        // Log background RAG action
        Log::info("[Sistem Latar Belakang: AI mendeteksi pertanyaan informasi umum. AI mencari dokumen di Vector Database tentang '{$query}' (RAG)]");

        $filePath = storage_path('app/knowledge_base/policies.txt');
        if (!file_exists($filePath)) {
            return "Batas toleransi keterlambatan adalah 15 menit. Jika lewat dari itu, slot hangus dan otomatis dialihkan ke walk-in.";
        }

        return file_get_contents($filePath);
    }
}
