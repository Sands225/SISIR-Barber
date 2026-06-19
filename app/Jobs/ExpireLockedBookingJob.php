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
 * Dispatched immediately after the Midtrans QRIS is sent to the customer.
 * Delayed by slot_lock_ttl seconds (default: 10 minutes).
 *
 * If the booking is still TEMP_LOCKED when this job runs, it means
 * Midtrans never sent a successful payment webhook — so we cancel
 * the booking, release the slot, and notify the customer.
 */
class ExpireLockedBookingJob implements ShouldQueue
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
            Log::warning('[ExpireLockedBookingJob] Booking not found', ['id' => $this->bookingId]);
            return;
        }

        // If payment was already confirmed (status moved past TEMP_LOCKED), do nothing.
        if ($booking->status !== BookingStatus::TEMP_LOCKED) {
            Log::info('[ExpireLockedBookingJob] Booking already processed, skipping.', [
                'booking_id' => $this->bookingId,
                'status'     => $booking->status->value,
            ]);
            return;
        }

        // Transition to cancelled
        $booking->transitionTo(
            BookingStatus::CANCELLED_BY_SYSTEM,
            'Waktu pembayaran DP (10 menit) habis tanpa konfirmasi dari Midtrans.'
        );

        // Release the capacity slot
        $capacity->releaseSlot($booking->id, $booking->barber_id, $booking->scheduled_at);

        // Notify the customer via WhatsApp
        $whatsapp->sendPaymentExpiredNotification($booking);

        // Trigger slot recovery broadcast so waitlist customers can take the slot
        SlotRecoveryBroadcastJob::dispatch(
            $booking->scheduled_at->toIso8601String(),
            10,
            $booking->service_id
        )->onQueue('broadcasts');

        Log::info('[ExpireLockedBookingJob] Booking expired and slot released.', [
            'booking_id' => $this->bookingId,
        ]);
    }
}
