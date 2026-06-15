<?php

namespace App\Jobs;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Services\CapacityEngine;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Dispatched 30 minutes before a booking's scheduled time.
 * If booking is still BOOKED (not yet CONFIRMED), auto-cancel it.
 * Triggers SlotRecoveryBroadcastJob on cancellation.
 */
class AutoCancelUnconfirmedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public readonly int $bookingId
    ) {}

    public function handle(CapacityEngine $capacity, WhatsAppService $whatsapp): void
    {
        $booking = Booking::with(['customer', 'barber', 'service'])->find($this->bookingId);

        if (! $booking) {
            Log::warning('[AutoCancelUnconfirmedJob] Booking not found', ['id' => $this->bookingId]);
            return;
        }

        // Only cancel if still in BOOKED state (customer did not confirm arrival)
        if ($booking->status !== BookingStatus::BOOKED) {
            Log::info('[AutoCancelUnconfirmedJob] No action needed', [
                'booking_id' => $this->bookingId,
                'status'     => $booking->status->value,
            ]);
            return;
        }

        // Transition to cancelled
        $booking->transitionTo(
            BookingStatus::CANCELLED_BY_SYSTEM,
            'Tidak ada konfirmasi kedatangan 30 menit sebelum jadwal.'
        );

        // Release the slot
        $capacity->releaseSlot($booking->id, $booking->barber_id, $booking->scheduled_at);

        // Notify customer
        $whatsapp->sendText(
            $booking->customer->wa_id,
            "❌ *Booking Dibatalkan Otomatis*\n\n"
            . "Halo {$booking->customer->name}, booking kamu pada {$booking->scheduledAtFormatted()} "
            . "dibatalkan karena tidak ada konfirmasi kehadiran.\n\n"
            . "Jika ingin booking ulang, balas dengan \"booking\"."
        );

        // Trigger Slot Recovery broadcast
        SlotRecoveryBroadcastJob::dispatch(
            $booking->scheduled_at->toIso8601String(),
            15, // 15% flash discount
            $booking->service_id
        )->onQueue('broadcasts');

        Log::info('[AutoCancelUnconfirmedJob] Booking auto-cancelled and slot recovery triggered', [
            'booking_id' => $this->bookingId,
        ]);
    }
}
