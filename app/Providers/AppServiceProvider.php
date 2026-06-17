<?php

namespace App\Providers;

use App\Services\CapacityEngine;
use App\Services\ConversationService;
use App\Services\GeminiService;
use App\Services\MidtransService;
use App\Services\WhatsAppService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind services as singletons for DI container
        $this->app->singleton(GeminiService::class);
        $this->app->singleton(WhatsAppService::class);
        $this->app->singleton(MidtransService::class);
        $this->app->singleton(CapacityEngine::class);
        $this->app->singleton(ConversationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {


        // ── Rate Limiters ────────────────────────────────────────────────────

        // Broadcast rate limiter: prevents Meta API blocks on mass WA sends
        RateLimiter::for('whatsapp-broadcast', function () {
            return Limit::perMinute(
                config('sisir.whatsapp.rate_limit_per_min', 30)
            );
        });

        // General WA API rate limiter
        RateLimiter::for('whatsapp-api', function () {
            return Limit::perMinute(80);
        });
    }
}

