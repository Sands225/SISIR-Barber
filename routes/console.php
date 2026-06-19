<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sisir:test-key', function () {
    $this->info("KEY: '" . config('sisir.gemini.api_key') . "'");
});

Schedule::call(function () {
    app(\App\Services\CapacityEngine::class)->expireLockedBookings();
})->everyMinute();
