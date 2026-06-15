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
      position: sticky;
      bottom: 0;
    }
    .nav-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
      cursor: pointer;
      padding: 6px 22px;
      border-radius: var(--radius-md);
      transition: background var(--trans);
      border: none;
      background: transparent;
      font-family: var(--font);
      text-decoration: none;
    }
    .nav-item:active { background: var(--green-50); }
    .nav-item svg { width: 24px; height: 24px; }
    .nav-item span { font-size: 11px; font-weight: 500; color: var(--gray-400); }
    .nav-item.active span { color: var(--green-600); font-weight: 700; }

    /* ──────────────────────────────
       SCROLL AREA
    ────────────────────────────── */
    .page-scroll {
      flex: 1;
      overflow-y: auto;
      overflow-x: hidden;
      scrollbar-width: none;
      -webkit-overflow-scrolling: touch;
    }
    .page-scroll::-webkit-scrollbar { display: none; }

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
<div class="sisir-shell">
  @yield('content')
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
</script>
@yield('scripts')
</body>
</html>
