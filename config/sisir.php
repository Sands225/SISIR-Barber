<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google Gemini AI
    |--------------------------------------------------------------------------
    */
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model'   => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
    ],

    /*
    |--------------------------------------------------------------------------
    | Meta WhatsApp Cloud API
    |--------------------------------------------------------------------------
    */
    'whatsapp' => [
        'token'             => env('WHATSAPP_TOKEN'),
        'phone_number_id'   => env('WHATSAPP_PHONE_NUMBER_ID'),
        'verify_token'      => env('WHATSAPP_VERIFY_TOKEN', 'sisir_verify_token'),
        'api_version'       => env('WHATSAPP_API_VERSION', 'v19.0'),
        'base_url'          => 'https://graph.facebook.com',
        'node_url'          => env('WHATSAPP_NODE_URL', 'http://localhost:3000'),
        'rate_limit_per_min' => (int) env('SISIR_WA_RATE_LIMIT_PER_MINUTE', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Midtrans Payment Gateway
    |--------------------------------------------------------------------------
    */
    'midtrans' => [
        'server_key'    => env('MIDTRANS_SERVER_KEY'),
        'client_key'    => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN),
        'snap_url'      => env('MIDTRANS_IS_PRODUCTION', false)
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js',
    ],

    /*
    |--------------------------------------------------------------------------
    | Business Logic Constants
    |--------------------------------------------------------------------------
    */
    'slot_lock_ttl'    => (int) env('SISIR_SLOT_LOCK_TTL', 600),      // seconds
    'dp_amount'        => (int) env('SISIR_DP_AMOUNT', 20000),         // IDR
    'dp_non_refundable' => true,

    /*
    |--------------------------------------------------------------------------
    | Reminder Windows (minutes before appointment)
    |--------------------------------------------------------------------------
    */
    'reminders' => [
        'day_before_minutes'   => 1440, // 24 hours
        'two_hours_minutes'    => 120,
        'reconfirm_minutes'    => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Conversation State TTL (Redis key lifetime)
    |--------------------------------------------------------------------------
    */
    'conversation_state_ttl' => 86400, // 24 hours in seconds
];
