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
 *   - 2h before:        delay = scheduled_at - 2h
 *   - 30m reconfirm:    delay = scheduled_at - 30m
 */
class SendReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly int    $bookingId,
        public readonly string $reminderType, // 'day_before' | 'two_hours' | 'reconfirm'
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

        match ($this->reminderType) {
            'day_before' => $whatsapp->sendDayBeforeReminder($booking),
            'two_hours'  => $whatsapp->sendTwoHoursReminder($booking),
            'reconfirm'  => $whatsapp->sendReconfirmationRequest($booking),
            default      => Log::warning('[SendReminderJob] Unknown reminder type', ['type' => $this->reminderType]),
        };

        Log::info('[SendReminderJob] Reminder sent', [
            'booking_id' => $this->bookingId,
            'type'       => $this->reminderType,
        ]);
    }

    /**
     * Dispatch all 3 reminders for a booking.
     * Called after successful Midtrans payment (BOOKED status).
     */
    public static function dispatchAll(Booking $booking): void
    {
        $scheduledAt = $booking->scheduled_at;

        // H-1 reminder
        $dayBeforeDelay = $scheduledAt->copy()->subDay()->diffInSeconds(now());
        if ($dayBeforeDelay > 0) {
            static::dispatch($booking->id, 'day_before')
                ->delay(now()->addSeconds($dayBeforeDelay))
                ->onQueue('reminders');
        }

        // 2 hours before
        $twoHourDelay = $scheduledAt->copy()->subHours(2)->diffInSeconds(now());
        if ($twoHourDelay > 0) {
            static::dispatch($booking->id, 'two_hours')
                ->delay(now()->addSeconds($twoHourDelay))
                ->onQueue('reminders');
        }

        // 30-min reconfirmation
        $reconfirmDelay = $scheduledAt->copy()->subMinutes(30)->diffInSeconds(now());
        if ($reconfirmDelay > 0) {
            static::dispatch($booking->id, 'reconfirm')
                ->delay(now()->addSeconds($reconfirmDelay))
                ->onQueue('reminders');
        }
    }
}
