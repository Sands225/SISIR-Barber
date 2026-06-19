<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PromoController;
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

    // Promo
    Route::get('/promo',               [PromoController::class, 'index'])->name('sisir.promo');
    Route::post('/promo/send',         [PromoController::class, 'send'])->name('sisir.promo.send');
    Route::post('/promo/waitlist',     [PromoController::class, 'joinWaitlist'])->name('sisir.promo.waitlist');

    // Revenue / Laporan Penghasilan
    Route::get('/revenue',             [RevenueController::class, 'index'])->name('sisir.revenue');
});
