<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\RevenueController;
use Illuminate\Support\Facades\Route;

// ─── Legacy welcome ──────────────────────────────────────────────────────────
Route::get('/welcome', fn () => view('welcome'))->name('welcome');

// ─── SISIR Public UI ─────────────────────────────────────────────────────────
Route::get('/',      fn () => view('sisir.splash'))->name('sisir.splash');
Route::get('/login', fn () => view('sisir.login'))->name('sisir.login');

// Email/Password auth for Admin/Barber
Route::post('/login',  [AuthController::class, 'login'])->name('sisir.login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('sisir.logout');

// Public route to render schedule grid for screenshotting
Route::get('/booking/schedule-image-html', [BookingController::class, 'scheduleImageHtml'])->name('sisir.booking.schedule-image-html');

// ─── SISIR Authenticated UI ───────────────────────────────────────────────────
Route::middleware('sisir.auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('sisir.dashboard');

    // Booking list & management
    Route::get('/booking',             [BookingController::class, 'index'])->name('sisir.booking');
    Route::get('/booking/create',      [BookingController::class, 'create'])->name('sisir.booking.create');
    Route::post('/booking',            [BookingController::class, 'store'])->name('sisir.booking.store');
    Route::get('/booking/slots',       [BookingController::class, 'slots'])->name('sisir.booking.slots');
    Route::get('/booking/{id}',        [BookingController::class, 'show'])->name('sisir.booking.show')
         ->where('id', '[0-9]+');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('sisir.settings');
    Route::post('/settings/operational', [SettingsController::class, 'updateOperational'])->name('sisir.settings.operational');
    Route::post('/settings/barbers', [SettingsController::class, 'saveBarber'])->name('sisir.settings.barbers');
    Route::post('/settings/barbers/{id}/delete', [SettingsController::class, 'deleteBarber'])->name('sisir.settings.barbers.delete');
    Route::post('/settings/services', [SettingsController::class, 'saveService'])->name('sisir.settings.services');
    Route::post('/settings/services/{id}/delete', [SettingsController::class, 'deleteService'])->name('sisir.settings.services.delete');
    Route::post('/settings/promo/send', [SettingsController::class, 'sendPromo'])->name('sisir.settings.promo.send');

    // Revenue / Laporan Penghasilan
    Route::get('/revenue',             [RevenueController::class, 'index'])->name('sisir.revenue');

    // Booking status transitions (Tandai Selesai, Batalkan)
    Route::post('/booking/{id}/transition', [BookingController::class, 'transition'])->name('sisir.booking.transition')
         ->where('id', '[0-9]+');
});
