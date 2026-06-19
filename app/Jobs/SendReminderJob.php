<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Dispatched with delays for:
 *   - H-1 (day before): delay = scheduled_at - 24h
 *   - H-1h (1 hour before): delay = scheduled_at - 1h
 *   - H-30m reconfirm:  delay = scheduled_at - 30m
 *   - H-15m reconfirm:  delay = scheduled_at - 15m
 */
class SendReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly int    $bookingId,
        public readonly string $reminderType, // 'day_before' | 'one_hour' | 'reconfirm_thirty' | 'reconfirm_fifteen'
    ) {}

    public function handle(WhatsAppService $whatsapp): void
    {
        $booking = Booking::with(['customer', 'barber.user', 'service'])->find($this->bookingId);

        if (! $booking) {
            Log::warning('[SendReminderJob] Booking not found', ['id' => $this->bookingId]);
            return;
        }

        // Skip if booking is in a terminal state
        if ($booking->status->isTerminal()) {
            Log::info('[SendReminderJob] Booking is terminal, skipping reminder', [
                'id'     => $this->bookingId,
                'status' => $booking->status->value,
            ]);
            return;
        }

        // Skip reconfirmation requests if booking has already been confirmed
        if ($booking->status === \App\Enums\BookingStatus::CONFIRMED &&
            in_array($this->reminderType, ['reconfirm_thirty', 'reconfirm_fifteen'], true)) {
            Log::info('[SendReminderJob] Booking is already confirmed, skipping reconfirmation request', [
                'id' => $this->bookingId,
            ]);
            return;
        }

        match ($this->reminderType) {
            'day_before'        => $whatsapp->sendDayBeforeReminder($booking),
            'one_hour'          => $whatsapp->sendOneHourReminder($booking),
            'reconfirm_thirty'  => $whatsapp->sendReconfirmationRequest($booking, 30),
            'reconfirm_fifteen' => $whatsapp->sendReconfirmationRequest($booking, 15),
            default             => Log::warning('[SendReminderJob] Unknown reminder type', ['type' => $this->reminderType]),
        };

        Log::info('[SendReminderJob] Reminder sent', [
            'booking_id' => $this->bookingId,
            'type'       => $this->reminderType,
        ]);
    }

    /**
     * Dispatch all scheduled reminders for a booking.
     * Called after successful Midtrans payment (BOOKED status).
     */
    public static function dispatchAll(Booking $booking): void
    {
        $scheduledAt = $booking->scheduled_at;

        // H-1 hari reminder
        $dayBeforeDelay = $scheduledAt->copy()->subDay()->diffInSeconds(now());
        if ($dayBeforeDelay > 0) {
            static::dispatch($booking->id, 'day_before')
                ->delay(now()->addSeconds($dayBeforeDelay))
                ->onQueue('reminders');
        }

        // H-1 jam reminder
        $oneHourDelay = $scheduledAt->copy()->subHour()->diffInSeconds(now());
        if ($oneHourDelay > 0) {
            static::dispatch($booking->id, 'one_hour')
                ->delay(now()->addSeconds($oneHourDelay))
                ->onQueue('reminders');
        }

        // H-30 menit konfirmasi kedatangan
        $reconfirmThirtyDelay = $scheduledAt->copy()->subMinutes(30)->diffInSeconds(now());
        if ($reconfirmThirtyDelay > 0) {
            static::dispatch($booking->id, 'reconfirm_thirty')
                ->delay(now()->addSeconds($reconfirmThirtyDelay))
                ->onQueue('reminders');
        }

        // H-15 menit konfirmasi kedatangan
        $reconfirmFifteenDelay = $scheduledAt->copy()->subMinutes(15)->diffInSeconds(now());
        if ($reconfirmFifteenDelay > 0) {
            static::dispatch($booking->id, 'reconfirm_fifteen')
                ->delay(now()->addSeconds($reconfirmFifteenDelay))
                ->onQueue('reminders');
        }
    }
}
