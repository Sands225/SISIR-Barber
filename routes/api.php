<?php

use App\Http\Controllers\Api\MidtransWebhookController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

// ─── WhatsApp Webhook (From Local Node.js) ───────────────────────────────────

Route::post('/webhook/whatsapp', [WhatsAppWebhookController::class, 'handle'])
    ->name('webhook.whatsapp.handle');

// ─── Midtrans Payment Webhooks ────────────────────────────────────────────────

Route::post('/webhook/midtrans', [MidtransWebhookController::class, 'handle'])
    ->middleware('verify.midtrans')
    ->name('webhook.midtrans.handle');
