<?php

use App\Http\Controllers\Api\MidtransWebhookController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

// ─── WhatsApp Cloud API Webhooks ─────────────────────────────────────────────

// Meta challenge verification (GET)
Route::get('/webhook/whatsapp', [WhatsAppWebhookController::class, 'verify'])
    ->name('webhook.whatsapp.verify');

// Incoming messages (POST) — middleware handles WA signature validation
Route::post('/webhook/whatsapp', [WhatsAppWebhookController::class, 'handle'])
    ->middleware('verify.whatsapp')
    ->name('webhook.whatsapp.handle');

// ─── Midtrans Payment Webhooks ────────────────────────────────────────────────

Route::post('/webhook/midtrans', [MidtransWebhookController::class, 'handle'])
    ->middleware('verify.midtrans')
    ->name('webhook.midtrans.handle');
