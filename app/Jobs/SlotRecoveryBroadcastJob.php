<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Broadcasts a Last-Minute Flash Discount to all active waitlist customers
 * whenever a slot is freed (auto-cancel or manual cancel).
 *
 * Rate-limited to prevent Meta WhatsApp API blocks on mass sends.
 */
class SlotRecoveryBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public readonly string $scheduledAt,
        public readonly int    $discountPct = 15,
        public readonly ?int   $serviceId = null,
    ) {}

    /**
     * Apply rate limiter middleware to the queue job.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new RateLimited('whatsapp-broadcast')];
    }

    public function handle(WhatsAppService $whatsapp): void
    {
        Log::info('[SlotRecoveryBroadcastJob] Starting broadcast', [
            'scheduled_at' => $this->scheduledAt,
            'discount'     => $this->discountPct . '%',
            'service_id'   => $this->serviceId,
        ]);

        $sent = $whatsapp->broadcastLastMinuteDiscount(
            $this->scheduledAt,
            $this->discountPct,
            $this->serviceId
        );

        Log::info('[SlotRecoveryBroadcastJob] Broadcast complete', ['sent' => $sent]);
    }
}
