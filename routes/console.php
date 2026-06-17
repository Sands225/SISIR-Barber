<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sisir:test-key', function () {
    $this->info("KEY: '" . config('sisir.gemini.api_key') . "'");
});
