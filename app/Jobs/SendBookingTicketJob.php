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
 * Dispatched immediately after Midtrans webhook confirms payment settlement.
 * Sends the booking ticket and receipt to the customer via WhatsApp.
 */
class SendBookingTicketJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public readonly int $bookingId
    ) {}

    public function handle(WhatsAppService $whatsapp): void
    {
        $booking = Booking::with(['customer', 'barber.user', 'service'])->find($this->bookingId);

        if (! $booking) {
            Log::warning('[SendBookingTicketJob] Booking not found', ['id' => $this->bookingId]);
            return;
        }

        $sent = $whatsapp->sendBookingTicket($booking);

        Log::info('[SendBookingTicketJob] Ticket sent', [
            'booking_id' => $this->bookingId,
            'success'    => $sent,
        ]);
    }
}
