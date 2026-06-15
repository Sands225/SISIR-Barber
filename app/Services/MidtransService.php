<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Midtrans\Config as MidtransConfig;
use Midtrans\CoreApi;
use Midtrans\Notification;

class MidtransService
{
    public function __construct()
    {
        MidtransConfig::$serverKey    = config('sisir.midtrans.server_key');
        MidtransConfig::$clientKey    = config('sisir.midtrans.client_key');
        MidtransConfig::$isProduction = config('sisir.midtrans.is_production', false);
        MidtransConfig::$isSanitized  = true;
        MidtransConfig::$is3ds        = true;
    }

    /**
     * Generate a QRIS charge via Midtrans Core API.
     * Returns charge response including QR code URL.
     *
     * @throws \Exception if charge creation fails
     */
    public function createDPCharge(Booking $booking): array
    {
        $booking->loadMissing(['customer', 'service']);

        $orderId = Booking::generateOrderId($booking->id);

        $params = [
            'payment_type'  => 'qris',
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $booking->dp_amount,
            ],
            'customer_details' => [
                'first_name' => $booking->customer->name,
                'phone'      => $booking->customer->phone,
            ],
            'item_details' => [
                [
                    'id'       => "dp-{$booking->service_id}",
                    'price'    => $booking->dp_amount,
                    'quantity' => 1,
                    'name'     => "DP Booking - {$booking->service->name}",
                ],
            ],
            'custom_expiry' => [
                'expiry_duration' => (int) ceil(config('sisir.slot_lock_ttl', 600) / 60),
                'unit'            => 'minute',
            ],
        ];

        try {
            $response = CoreApi::charge($params);

            // Persist Midtrans order ID and QR URL to booking
            $qrUrl = $response->actions[0]->url ?? null;
            $booking->update([
                'midtrans_order_id'   => $orderId,
                'midtrans_payment_type' => 'qris',
                'midtrans_qr_code_url'  => $qrUrl,
            ]);

            Log::info('[MidtransService] QRIS charge created', [
                'booking_id' => $booking->id,
                'order_id'   => $orderId,
                'qr_url'     => $qrUrl,
            ]);

            return (array) $response;
        } catch (\Throwable $e) {
            Log::error('[MidtransService] Charge failed', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Parse and validate an incoming Midtrans webhook notification.
     * Validates SHA512 signature to prevent spoofing.
     *
     * @param array $payload  Raw POST payload from Midtrans
     * @return array          Validated and parsed notification data
     * @throws \Exception     If signature is invalid
     */
    public function parseAndValidateWebhook(array $payload): array
    {
        if (! $this->validateSignature($payload)) {
            throw new \Exception('Invalid Midtrans webhook signature.');
        }

        return [
            'order_id'           => $payload['order_id'] ?? null,
            'transaction_id'     => $payload['transaction_id'] ?? null,
            'transaction_status' => $payload['transaction_status'] ?? null,
            'payment_type'       => $payload['payment_type'] ?? null,
            'fraud_status'       => $payload['fraud_status'] ?? null,
            'gross_amount'       => $payload['gross_amount'] ?? null,
        ];
    }

    /**
     * Determines if a webhook status means payment is successful.
     */
    public function isPaymentSuccess(string $transactionStatus, ?string $fraudStatus): bool
    {
        return $transactionStatus === 'settlement'
            || ($transactionStatus === 'capture' && $fraudStatus === 'accept');
    }

    /**
     * Determines if a webhook status means payment expired/cancelled.
     */
    public function isPaymentExpired(string $transactionStatus): bool
    {
        return in_array($transactionStatus, ['expire', 'cancel', 'deny'], strict: true);
    }

    // ── Private ──────────────────────────────────────────────────────────────

    /**
     * Validate Midtrans SHA512 signature.
     * signature_key = SHA512(order_id + status_code + gross_amount + server_key)
     */
    private function validateSignature(array $payload): bool
    {
        $orderId      = $payload['order_id'] ?? '';
        $statusCode   = $payload['status_code'] ?? '';
        $grossAmount  = $payload['gross_amount'] ?? '';
        $serverKey    = config('sisir.midtrans.server_key');
        $incoming     = $payload['signature_key'] ?? '';

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return hash_equals($expected, $incoming);
    }
}
