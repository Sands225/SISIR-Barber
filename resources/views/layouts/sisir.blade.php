<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>@yield('title', 'SISIR – Barber Management') | SISIR</title>
  <meta name="description" content="@yield('meta_description', 'SISIR – Sistem Informasi Salon dan Barbershop. Reclaim Your Time, Recover Your Revenue.')" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    /* ──────────────────────────────
       DESIGN TOKENS
    ────────────────────────────── */
    :root {
      --green-900: #0a3d1a;
      --green-800: #145228;
      --green-700: #1a6b32;
      --green-600: #1e7c3a;
      --green-500: #208a40;
      --green-400: #27a84e;
      --green-300: #4ec97a;
      --green-200: #a3e6b9;
      --green-100: #d4f4e0;
      --green-50:  #edfaf2;
      --green-bg:  #f0faf4;

      --orange-100: #fde8d0;
      --orange-500: #e87b2b;

      --red-100: #fde0e0;
      --red-500: #d93025;

      --gray-900: #111827;
      --gray-700: #374151;
      --gray-600: #4b5563;
      --gray-500: #6b7280;
      --gray-400: #9ca3af;
      --gray-200: #e5e7eb;
      --gray-100: #f3f4f6;
      --gray-50:  #f9fafb;
      --white:    #ffffff;

      --radius-sm:   8px;
      --radius-md:   14px;
      --radius-lg:   20px;
      --radius-xl:   28px;
      --radius-full: 9999px;

      --shadow-sm: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
      --shadow-md: 0 4px 12px rgba(0,0,0,.10), 0 2px 4px rgba(0,0,0,.06);
      --shadow-lg: 0 8px 24px rgba(0,0,0,.12), 0 4px 8px rgba(0,0,0,.06);

      --font: 'Plus Jakarta Sans', sans-serif;
      --trans: 200ms cubic-bezier(.4,0,.2,1);
    }

    /* ──────────────────────────────
       RESET
    ────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { height: 100%; }
    body {
      font-family: var(--font);
      min-height: 100vh;
      background: var(--green-bg);
      -webkit-font-smoothing: antialiased;
      display: flex;
      flex-direction: column;
    }

    /* ──────────────────────────────
       MOBILE-FIRST CONTAINER
    ────────────────────────────── */
    .sisir-shell {
      width: 100%;
      max-width: 430px;
      min-height: 100vh;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      background: var(--green-bg);
      position: relative;
      overflow: hidden;
    }
    @media (min-width: 768px) {
      .sisir-shell {
        max-width: 100%;
        min-height: auto;
        margin: 0;
        overflow: visible;
        background: transparent;
      }
      .app-header, .bottom-nav {
        display: none !important;
      }
    }

    /* ──────────────────────────────
       APP HEADER (shared)
    ────────────────────────────── */
    .app-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 20px;
      background: transparent;
      flex-shrink: 0;
    }
    .brand {
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
    }
    .brand-name {
      font-size: 22px;
      font-weight: 800;
      color: var(--green-700);
      letter-spacing: -.5px;
    }
    .avatar-btn {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      overflow: hidden;
      border: 2.5px solid var(--green-300);
      cursor: pointer;
      flex-shrink: 0;
    }
    .avatar-btn img { width: 100%; height: 100%; object-fit: cover; }
    .avatar-fallback {
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, #4ec97a, #1e7c3a);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      color: white;
      font-size: 16px;
      font-family: var(--font);
    }

    /* ──────────────────────────────
       BOTTOM NAV (shared)
    ────────────────────────────── */
    .bottom-nav {
      display: flex;
      align-items: center;
      justify-content: space-around;
      padding: 10px 0 env(safe-area-inset-bottom, 16px);
      padding-bottom: max(env(safe-area-inset-bottom, 0px), 16px);
      background: var(--white);
      border-top: 1px solid var(--gray-200);
      flex-shrink: 0;
      position: fixed;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 100%;
      max-width: 430px;
      z-index: 50;
    }
    .nav-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
      cursor: pointer;
      padding: 6px 12px;
      border-radius: var(--radius-md);
      transition: background var(--trans);
      border: none;
      background: transparent;
      font-family: var(--font);
      text-decoration: none;
      color: var(--gray-400);
    }
    .nav-item:active { background: var(--green-50); }
    .nav-item svg { width: 24px; height: 24px; }
    .nav-item span { font-size: 11px; font-weight: 500; color: inherit; }
    .nav-item.active { color: var(--green-600); }
    .nav-item.active span { font-weight: 700; }

    /* ──────────────────────────────
       SCROLL AREA
    ────────────────────────────── */
    .page-scroll {
      flex: 1;
      overflow-y: auto;
      overflow-x: hidden;
      scrollbar-width: none;
      -webkit-overflow-scrolling: touch;
      padding-bottom: 96px;
    }
    .page-scroll::-webkit-scrollbar { display: none; }
    @media (min-width: 768px) {
      .page-scroll {
        padding-bottom: 24px;
      }
    }

    /* ──────────────────────────────
       ANIMATIONS
    ────────────────────────────── */
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(16px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes slideInCard {
      from { opacity: 0; transform: translateY(12px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .anim-fade-up  { animation: fadeInUp .45s ease both; }
    .anim-slide    { animation: slideInCard .4s ease both; }
    .delay-1 { animation-delay: .1s; }
    .delay-2 { animation-delay: .2s; }
    .delay-3 { animation-delay: .3s; }
    .delay-4 { animation-delay: .4s; }

    /* ──────────────────────────────
       SHARED COMPONENTS
    ────────────────────────────── */
    .status-badge {
      padding: 4px 10px;
      border-radius: var(--radius-full);
      font-size: 10px;
      font-weight: 800;
      letter-spacing: .5px;
      text-transform: uppercase;
      flex-shrink: 0;
    }
    .badge-selesai { background: var(--green-600); color: var(--white); }
    .badge-batal   { background: var(--red-100);   color: var(--red-500); }
    .badge-sudahdp { background: var(--orange-100); color: var(--orange-500); }
    .badge-lunas   { background: var(--green-100); color: var(--green-700); }

    /* Toast */
    .toast {
      position: fixed;
      bottom: 90px;
      left: 50%;
      transform: translateX(-50%) translateY(20px);
      background: var(--gray-900);
      color: var(--white);
      padding: 10px 20px;
      border-radius: var(--radius-full);
      font-size: 13px;
      font-weight: 600;
      white-space: nowrap;
      opacity: 0;
      transition: all .3s ease;
      pointer-events: none;
      z-index: 999;
      max-width: 90%;
    }
    .toast.show {
      opacity: 1;
      transform: translateX(-50%) translateY(0);
    }
  </style>
  @yield('extra_styles')
  @yield('head')
</head>
<body>
<div class="min-h-screen bg-[var(--green-bg)] md:flex md:flex-row">
  <!-- Desktop & Tablet Sidebar -->
  @if(!in_array(Route::currentRouteName(), ['sisir.splash', 'sisir.login']))
  <div id="sidebar-backdrop" class="fixed inset-0 bg-black/40 z-40 hidden transition-opacity duration-300 opacity-0 lg:hidden" onclick="toggleSidebar()"></div>
  <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-[var(--gray-200)] h-screen flex flex-col transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 lg:static lg:z-0 lg:flex-shrink-0">
    <!-- Centered Sidebar Brand Header -->
    <div class="px-6 h-20 flex items-center justify-center border-b border-[var(--gray-100)] flex-shrink-0 relative">
      <a href="{{ route('sisir.splash') }}" class="brand">
        <img src="{{ asset('ico-sisir.ico') }}" width="28" height="28" alt="SISIR Logo" style="border-radius:6px;" />
        <span class="brand-name">SISIR</span>
      </a>
      <button onclick="toggleSidebar()" class="lg:hidden absolute right-4 top-1/2 -translate-y-1/2 p-1.5 rounded-lg text-[var(--gray-400)] hover:bg-[var(--gray-100)] hover:text-[var(--gray-700)] focus:outline-none" aria-label="Close Menu">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Navigation Menu with spacing and premium gap -->
    <nav class="flex-1 p-6 flex flex-col gap-3">
      <a href="{{ route('sisir.dashboard') }}" class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-sm font-bold transition-colors {{ Route::currentRouteName() == 'sisir.dashboard' ? 'bg-[var(--green-50)] text-[var(--green-700)]' : 'text-[var(--gray-600)] hover:bg-[var(--gray-50)] hover:text-[var(--gray-900)]' }}">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
          <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
        </svg>
        Dashboard
      </a>
      <a href="{{ route('sisir.booking') }}" class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-sm font-bold transition-colors {{ Str::startsWith(Route::currentRouteName(), 'sisir.booking') ? 'bg-[var(--green-50)] text-[var(--green-700)]' : 'text-[var(--gray-600)] hover:bg-[var(--gray-50)] hover:text-[var(--gray-900)]' }}">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/>
          <line x1="8" y1="2" x2="8" y2="6"/>
          <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        Booking
      </a>
      <a href="{{ route('sisir.revenue') }}" class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-sm font-bold transition-colors {{ Route::currentRouteName() == 'sisir.revenue' ? 'bg-[var(--green-50)] text-[var(--green-700)]' : 'text-[var(--gray-600)] hover:bg-[var(--gray-50)] hover:text-[var(--gray-900)]' }}">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
        </svg>
        Penghasilan
      </a>
      <a href="{{ route('sisir.promo') }}" class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-sm font-bold transition-colors {{ Route::currentRouteName() == 'sisir.promo' ? 'bg-[var(--green-50)] text-[var(--green-700)]' : 'text-[var(--gray-600)] hover:bg-[var(--gray-50)] hover:text-[var(--gray-900)]' }}">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
          <line x1="7" y1="7" x2="7.01" y2="7"/>
        </svg>
        Promo
      </a>
    </nav>
  </aside>
  @endif

  <!-- Main Content Area -->
  <main class="flex-1 flex flex-col min-w-0 md:h-screen md:overflow-y-auto">
    <!-- Top Navbar — tablet & desktop -->
    @if(!in_array(Route::currentRouteName(), ['sisir.splash', 'sisir.login']))
    <header class="hidden md:flex items-center bg-white border-b border-[var(--gray-200)] h-20 flex-shrink-0">
      <div class="w-full max-w-6xl mx-auto px-8 flex items-center justify-between">
        <!-- Left: Burger Toggle Menu -->
        <div>
          <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl text-[var(--gray-600)] hover:bg-[var(--gray-50)] focus:outline-none" aria-label="Toggle Sidebar">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>

        <!-- Right: Account Profile & Logout -->
        @if(auth()->check())
          <div class="flex items-center gap-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-[var(--green-600)] flex items-center justify-center font-bold text-white text-base">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
              </div>
              <div class="min-w-0">
                <div class="text-sm font-bold text-[var(--gray-900)] leading-tight">{{ auth()->user()->name }}</div>
                <div class="text-xs text-[var(--gray-500)] leading-tight mt-0.5">{{ auth()->user()->email }}</div>
              </div>
            </div>
            <div class="h-6 w-px bg-[var(--gray-200)]"></div>
            <form method="POST" action="{{ route('sisir.logout') }}" class="m-0">
              @csrf
              <button type="submit" class="flex items-center gap-2 px-4 py-2 rounded-xl border border-[var(--gray-200)] text-sm font-bold text-[var(--gray-700)] hover:bg-[var(--gray-50)] transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Keluar
              </button>
            </form>
          </div>
        @endif
      </div>
    </header>
    @endif

    <div class="w-full @if(!in_array(Route::currentRouteName(), ['sisir.splash', 'sisir.login'])) md:max-w-6xl md:mx-auto md:px-8 md:py-6 @endif flex-grow flex flex-col">
      <div class="sisir-shell">
        @yield('content')
        @if(!in_array(Route::currentRouteName(), ['sisir.splash', 'sisir.login']))
          <!-- Bottom Nav -->
          <nav class="bottom-nav">
            <a href="{{ route('sisir.dashboard') }}" class="nav-item {{ Route::currentRouteName() == 'sisir.dashboard' ? 'active' : '' }}">
              <svg viewBox="0 0 24 24" fill="none">
                <rect x="3" y="3" width="8" height="8" rx="2" stroke="currentColor" stroke-width="2"/>
                <rect x="13" y="3" width="8" height="8" rx="2" stroke="currentColor" stroke-width="2"/>
                <rect x="3" y="13" width="8" height="8" rx="2" stroke="currentColor" stroke-width="2"/>
                <rect x="13" y="13" width="8" height="8" rx="2" stroke="currentColor" stroke-width="2"/>
              </svg>
              <span>Dashboard</span>
            </a>
            <a href="{{ route('sisir.booking') }}" class="nav-item {{ Str::startsWith(Route::currentRouteName(), 'sisir.booking') ? 'active' : '' }}">
              <svg viewBox="0 0 24 24" fill="none">
                <rect x="3" y="5" width="18" height="16" rx="3" stroke="currentColor" stroke-width="2"/>
                <line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2"/>
                <line x1="8" y1="2" x2="8" y2="8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <line x1="16" y1="2" x2="16" y2="8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <span>Booking</span>
            </a>
            <a href="{{ route('sisir.revenue') }}" class="nav-item {{ Route::currentRouteName() == 'sisir.revenue' ? 'active' : '' }}">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <span>Penghasilan</span>
            </a>
            <a href="{{ route('sisir.promo') }}" class="nav-item {{ Route::currentRouteName() == 'sisir.promo' ? 'active' : '' }}">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M12 2L14.09 8.26L21 9.27L16 14.14L17.18 21L12 18.27L6.82 21L8 14.14L3 9.27L9.91 8.26L12 2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              </svg>
              <span>Promo</span>
            </a>
          </nav>
        @endif
      </div>
    </div>
  </main>
</div>


<div class="toast" id="globalToast"></div>

<script>
  let _toastTimer;
  function showToast(msg) {
    const t = document.getElementById('globalToast');
    if (!t) return;
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(_toastTimer);
    _toastTimer = setTimeout(() => t.classList.remove('show'), 2800);
  }

  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    if (!sidebar || !backdrop) return;
    
    const isOpen = !sidebar.classList.contains('-translate-x-full');
    if (isOpen) {
      sidebar.classList.add('-translate-x-full');
      backdrop.classList.remove('opacity-100');
      backdrop.classList.add('opacity-0');
      setTimeout(() => {
        if (sidebar.classList.contains('-translate-x-full')) {
          backdrop.classList.add('hidden');
        }
      }, 300);
    } else {
      backdrop.classList.remove('hidden');
      backdrop.offsetHeight; // force layout reflow
      sidebar.classList.remove('-translate-x-full');
      backdrop.classList.remove('opacity-0');
      backdrop.classList.add('opacity-100');
    }
  }
</script>
@yield('scripts')
</body>
</html>
