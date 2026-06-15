<?php

use Illuminate\Support\Facades\Route;

// ─── Legacy welcome ─────────────────────────────────────────────────────────
Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');

// ─── SISIR UI ────────────────────────────────────────────────────────────────
Route::get('/',          fn () => view('sisir.splash'))->name('sisir.splash');
Route::get('/login',     fn () => view('sisir.login'))->name('sisir.login');
Route::get('/dashboard', fn () => view('sisir.dashboard'))->name('sisir.dashboard');
Route::get('/booking',   fn () => view('sisir.booking'))->name('sisir.booking');
Route::get('/promo',     fn () => view('sisir.promo'))->name('sisir.promo');
