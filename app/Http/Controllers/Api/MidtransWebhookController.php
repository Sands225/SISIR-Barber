<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Jobs\SendBookingTicketJob;
use App\Jobs\SendReminderJob;
use App\Jobs\SlotRecoveryBroadcastJob;
use App\Models\Booking;
use App\Services\CapacityEngine;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __construct(
        private MidtransService $midtrans,
        private CapacityEngine  $capacity,
    ) {}

    /**
     * POST /api/webhook/midtrans
     * Handles Midtrans payment status notifications.
     */
    public function handle(Request $request): Response
    {
        $payload = $request->all();

        Log::info('[MidtransWebhook] Incoming notification', [
            'order_id' => $payload['order_id'] ?? 'unknown',
            'status'   => $payload['transaction_status'] ?? 'unknown',
        ]);

        // ── Signature Validation ─────────────────────────────────────────────
        try {
            $data = $this->midtrans->parseAndValidateWebhook($payload);
        } catch (\Exception $e) {
            Log::error('[MidtransWebhook] Signature validation failed', ['error' => $e->getMessage()]);
            return response('Unauthorized', 401);
        }

        // ── Idempotency Guard ────────────────────────────────────────────────
        $cacheKey = "midtrans_{$data['order_id']}_{$data['transaction_status']}";
        if (! Cache::add($cacheKey, true, now()->addDay())) {
            Log::info('[MidtransWebhook] Duplicate notification, skipping', ['key' => $cacheKey]);
            return response('OK', 200);
        }

        // ── Find Booking ─────────────────────────────────────────────────────
        $booking = Booking::where('midtrans_order_id', $data['order_id'])->first();

        if (! $booking) {
            Log::warning('[MidtransWebhook] Booking not found for order', ['order_id' => $data['order_id']]);
            return response('OK', 200);
        }

        // ── Process Based on Payment Status ──────────────────────────────────
        if ($this->midtrans->isPaymentSuccess($data['transaction_status'], $data['fraud_status'])) {
            $this->handlePaymentSuccess($booking, $data);
        } elseif ($this->midtrans->isPaymentExpired($data['transaction_status'])) {
            $this->handlePaymentExpired($booking);
        }

        return response('OK', 200);
    }

    // ── Private ──────────────────────────────────────────────────────────────

    private function handlePaymentSuccess(Booking $booking, array $data): void
    {
        if ($booking->status !== BookingStatus::TEMP_LOCKED) {
            Log::info('[MidtransWebhook] Booking not in TEMP_LOCKED state, skipping', [
                'booking_id' => $booking->id,
                'status'     => $booking->status->value,
            ]);
            return;
        }

        // Update Midtrans transaction data
        $booking->update([
            'midtrans_transaction_id' => $data['transaction_id'],
            'midtrans_payment_type'   => $data['payment_type'],
        ]);

        // Transition to BOOKED
        $booking->transitionTo(BookingStatus::BOOKED);

        // Dispatch booking ticket
        SendBookingTicketJob::dispatch($booking->id)->onQueue('notifications');

        // Dispatch all 3 reminder jobs
        SendReminderJob::dispatchAll($booking);

        // Dispatch auto-cancel job for 30-min window
        $autoCancel = $booking->scheduled_at->copy()->subMinutes(30);
        if ($autoCancel->isFuture()) {
            \App\Jobs\AutoCancelUnconfirmedJob::dispatch($booking->id)
                ->delay($autoCancel)
                ->onQueue('reminders');
        }

        Log::info('[MidtransWebhook] Payment success processed', [
            'booking_id' => $booking->id,
            'order_id'   => $data['order_id'],
        ]);
    }

    private function handlePaymentExpired(Booking $booking): void
    {
        if (! in_array($booking->status, [BookingStatus::TEMP_LOCKED], strict: true)) {
            return;
        }

        $booking->transitionTo(
            BookingStatus::CANCELLED_BY_SYSTEM,
            'Waktu pembayaran DP habis.'
        );

        // Release the slot lock
        $this->capacity->releaseSlot($booking->id, $booking->barber_id, $booking->scheduled_at);

        // Trigger Slot Recovery
        SlotRecoveryBroadcastJob::dispatch(
            $booking->scheduled_at->toIso8601String(),
            10,
            $booking->service_id
        )->onQueue('broadcasts');

        Log::info('[MidtransWebhook] Payment expired, slot released', ['booking_id' => $booking->id]);
    }
}
