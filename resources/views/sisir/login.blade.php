@extends('layouts.sisir')

@section('title', 'Masuk – SISIR')
@section('meta_description', 'Masuk ke SISIR – Sistem manajemen barbershop Anda.')

@section('extra_styles')
<style>
  body, .sisir-shell { background: var(--green-50); }

  /* ── Header ── */
  .login-header {
    padding: 16px 20px 8px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  /* ── Body wrapper ── */
  .login-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px 28px 0;
    overflow-y: auto;
    scrollbar-width: none;
  }
  .login-body::-webkit-scrollbar { display: none; }

  /* ── Icon circle ── */
  .login-icon-circle {
    width: 76px; height: 76px;
    background: var(--green-600);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 22px;
    box-shadow: 0 8px 28px rgba(30,124,58,.38);
  }

  /* ── Typography ── */
  .login-title {
    font-size: 28px; font-weight: 800;
    color: var(--gray-900); text-align: center;
    line-height: 1.15; margin-bottom: 8px;
  }
  .login-subtitle {
    font-size: 13px; color: var(--gray-500);
    text-align: center; margin-bottom: 36px;
  }

  /* ── Form ── */
  .form-group { width: 100%; margin-bottom: 16px; }
  .form-label {
    font-size: 13px; font-weight: 600;
    color: var(--gray-700); margin-bottom: 8px;
    display: block;
  }
  .phone-input-wrap {
    display: flex; align-items: center;
    background: var(--white);
    border: 1.5px solid var(--gray-200);
    border-radius: var(--radius-md);
    overflow: hidden;
    transition: border-color var(--trans), box-shadow var(--trans);
  }
  .phone-input-wrap:focus-within {
    border-color: var(--green-500);
    box-shadow: 0 0 0 3px rgba(32,138,64,.12);
  }
  .phone-prefix {
    padding: 0 14px; font-size: 15px; font-weight: 600;
    color: var(--gray-700); border-right: 1.5px solid var(--gray-200);
    height: 52px; display: flex; align-items: center;
    background: var(--gray-50); flex-shrink: 0;
  }
  .phone-input {
    flex: 1; border: none; outline: none;
    padding: 0 16px; font-size: 15px;
    font-family: var(--font); color: var(--gray-900);
    background: transparent; height: 52px;
  }
  .phone-input::placeholder { color: var(--gray-400); }

  /* ── Buttons ── */
  .btn-primary {
    width: 100%; height: 52px;
    background: var(--green-800); color: var(--white);
    border: none; border-radius: var(--radius-md);
    font-family: var(--font); font-size: 15px; font-weight: 700;
    cursor: pointer; display: flex; align-items: center;
    justify-content: center; gap: 8px;
    transition: background var(--trans), transform var(--trans), box-shadow var(--trans);
    box-shadow: 0 4px 16px rgba(20,82,40,.35);
    margin-bottom: 20px; text-decoration: none;
  }
  .btn-primary:hover { background: var(--green-700); box-shadow: 0 6px 22px rgba(20,82,40,.45); }
  .btn-primary:active { transform: scale(.98); }

  /* ── Divider ── */
  .divider {
    display: flex; align-items: center;
    gap: 12px; width: 100%; margin-bottom: 20px;
  }
  .divider-line { flex: 1; height: 1px; background: var(--gray-200); }
  .divider-text { font-size: 12px; color: var(--gray-400); font-weight: 500; white-space: nowrap; }

  /* ── Google button ── */
  .btn-google {
    width: 160px; height: 48px;
    background: var(--white);
    border: 1.5px solid var(--gray-200);
    border-radius: var(--radius-md);
    font-family: var(--font); font-size: 14px;
    font-weight: 600; color: var(--gray-700);
    cursor: pointer; display: flex;
    align-items: center; justify-content: center;
    gap: 8px; transition: background var(--trans), box-shadow var(--trans), transform var(--trans);
    box-shadow: var(--shadow-sm);
  }
  .btn-google:hover { background: var(--gray-50); box-shadow: var(--shadow-md); }
  .btn-google:active { transform: scale(.98); }

  /* ── Footer ── */
  .login-footer {
    padding: 24px 28px 48px;
    text-align: center; font-size: 13px; color: var(--gray-500);
  }
  .login-footer a { color: var(--green-600); font-weight: 700; text-decoration: none; }

  /* ── Help FAB ── */
  .help-fab {
    position: fixed; right: calc(50% - 215px + 20px); bottom: 32px;
    width: 44px; height: 44px;
    background: var(--green-500); border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(32,138,64,.45);
    transition: transform var(--trans), box-shadow var(--trans);
    z-index: 50;
  }
  @media (max-width: 430px) {
    .help-fab { right: 20px; }
  }
  .help-fab:hover { transform: scale(1.08); }
</style>
@endsection

@section('content')
<div class="login-header">
  <a href="{{ route('sisir.splash') }}" class="brand" style="text-decoration:none">
    <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
      <circle cx="7" cy="7" r="5" fill="none" stroke="#1e7c3a" stroke-width="2"/>
      <circle cx="7" cy="21" r="5" fill="none" stroke="#1e7c3a" stroke-width="2"/>
      <line x1="7" y1="12" x2="24" y2="4" stroke="#1e7c3a" stroke-width="2" stroke-linecap="round"/>
      <line x1="7" y1="16" x2="24" y2="24" stroke="#1e7c3a" stroke-width="2" stroke-linecap="round"/>
      <circle cx="18" cy="14" r="2" fill="#1e7c3a"/>
    </svg>
    <span class="brand-name">SISIR</span>
  </a>
</div>

<div class="login-body">
  <!-- Scissors icon circle -->
  <div class="login-icon-circle anim-fade-up">
    <svg width="38" height="38" viewBox="0 0 38 38" fill="none">
      <circle cx="9.5" cy="9.5" r="7" fill="none" stroke="white" stroke-width="2.5"/>
      <circle cx="9.5" cy="28.5" r="7" fill="none" stroke="white" stroke-width="2.5"/>
      <line x1="9.5" y1="16.5" x2="9.5" y2="21.5" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
      <line x1="13.5" y1="16.5" x2="32" y2="31" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
      <line x1="13.5" y1="21.5" x2="32" y2="7" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
    </svg>
  </div>

  <h1 class="login-title anim-fade-up delay-1">Selamat Datang<br/>Kembali!</h1>
  <p class="login-subtitle anim-fade-up delay-1">Reclaim Your Time, Recover Your Revenue</p>

  <!-- Phone input -->
  <div class="form-group anim-fade-up delay-2">
    <label class="form-label" for="phoneInput">Nomor Telepon</label>
    <div class="phone-input-wrap">
      <div class="phone-prefix">+62</div>
      <input
        class="phone-input"
        id="phoneInput"
        type="tel"
        placeholder="812-3456-7890"
        autocomplete="tel"
        inputmode="numeric"
      />
    </div>
  </div>

  <!-- Lanjutkan button -->
  <a href="{{ route('sisir.dashboard') }}" class="btn-primary anim-fade-up delay-3">
    Lanjutkan
    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
      <path d="M3 9h12M10 4l5 5-5 5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  </a>

  <!-- Divider -->
  <div class="divider anim-fade-up delay-3">
    <div class="divider-line"></div>
    <span class="divider-text">Atau masuk dengan</span>
    <div class="divider-line"></div>
  </div>

  <!-- Google -->
  <button class="btn-google anim-fade-up delay-4" onclick="showToast('Login dengan Google segera hadir!')">
    <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
      <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z" fill="#4285F4"/>
      <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z" fill="#34A853"/>
      <path d="M3.964 10.71c-.18-.54-.282-1.117-.282-1.71 0-.593.102-1.17.282-1.71V4.958H.957C.347 6.173 0 7.548 0 9s.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
      <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.958L3.964 6.29C4.672 4.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
    </svg>
    Google
  </button>
</div>

<!-- Footer -->
<div class="login-footer anim-fade-up delay-4">
  Belum punya akun? <a href="#" onclick="showToast('Fitur pendaftaran segera hadir!')">Daftar Sekarang</a>
</div>

<!-- Help FAB -->
<div class="help-fab" onclick="showToast('Hubungi kami di support@sisir.id')">
  <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
    <circle cx="10" cy="10" r="9" stroke="white" stroke-width="2"/>
    <path d="M7.5 7.5a2.5 2.5 0 0 1 5 0c0 1.5-2.5 2-2.5 3.5" stroke="white" stroke-width="2" stroke-linecap="round"/>
    <circle cx="10" cy="14.5" r="1" fill="white"/>
  </svg>
</div>
@endsection
