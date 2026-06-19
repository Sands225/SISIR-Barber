<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendScheduleImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public readonly string $waId,
        public readonly string $date
    ) {}

    public function handle(WhatsAppService $whatsapp): void
    {
        $sent = $whatsapp->sendScheduleImage($this->waId, $this->date);

        Log::info('[SendScheduleImageJob] Schedule image sent', [
            'wa_id'   => $this->waId,
            'date'    => $this->date,
            'success' => $sent,
        ]);
    }
}
