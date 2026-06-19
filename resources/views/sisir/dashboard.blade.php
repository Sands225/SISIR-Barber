@extends('layouts.sisir')

@section('title', 'Dashboard – SISIR')
@section('meta_description', 'Kelola antrean booking barbershop Anda hari ini.')

@section('extra_styles')
<style>
  body, .sisir-shell { background: var(--green-bg); }

  /* ── Greeting ── */
  .greeting-section {
    padding: 12px 20px 20px;
  }
  .greeting-date {
    font-size: 12px; color: var(--gray-500);
    font-weight: 600; margin-bottom: 4px;
    text-transform: capitalize;
  }
  .greeting-name {
    font-size: 28px; font-weight: 800;
    color: var(--gray-900); line-height: 1.15;
  }
  .greeting-subtitle {
    font-size: 13px; color: var(--gray-500);
  }

  /* ── Summary Cards Grid ── */
  .stats-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
    padding: 0 20px 24px;
  }
  @media (min-width: 640px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
  }
  @media (min-width: 1024px) {
    .stats-grid { grid-template-columns: repeat(4, 1fr); }
  }

  .stat-card {
    background: var(--white);
    border: 1px solid var(--gray-100);
    border-radius: var(--radius-lg);
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: var(--shadow-sm);
    transition: transform var(--trans), box-shadow var(--trans);
  }
  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
  }
  .stat-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 14px;
  }
  .stat-card-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--gray-500);
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .stat-card-value {
    font-size: 24px;
    font-weight: 800;
    color: var(--gray-900);
    margin-top: 4px;
  }
  .stat-icon-box {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .stat-card-bottom {
    font-size: 12px;
    font-weight: 600;
  }
  .stat-link {
    color: var(--gray-400);
    text-decoration: none;
    transition: color var(--trans);
    display: inline-flex;
    align-items: center;
  }
  .stat-link:hover {
    color: var(--green-600);
  }

  /* ── Dashboard Layout Split ── */
  .db-split-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
    padding: 0 20px 32px;
  }
  @media (min-width: 1024px) {
    .db-split-layout {
      grid-template-columns: 2fr 1.1fr;
    }
  }

  /* ── Panel Cards ── */
  .db-panel {
    background: var(--white);
    border: 1px solid var(--gray-100);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    padding: 24px;
  }
  .db-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }
  .db-panel-title-area {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .db-panel-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--gray-900);
  }
  .db-panel-link {
    font-size: 12px;
    font-weight: 700;
    color: var(--green-600);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 2px;
    transition: color var(--trans);
  }
  .db-panel-link:hover {
    color: var(--green-700);
  }

  /* ── Booking List Items ── */
  .booking-list-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  .booking-row {
    background: var(--white);
    border: 1.5px solid var(--gray-100);
    border-radius: 16px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    transition: box-shadow var(--trans), border-color var(--trans);
  }
  @media (min-width: 640px) {
    .booking-row {
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
    }
  }
  .booking-row:hover {
    box-shadow: var(--shadow-sm);
    border-color: var(--green-100);
  }
  .booking-row-profile {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .customer-avatar {
    width: 40px;
    height: 40px;
    background: var(--green-50);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    font-weight: 800;
    color: var(--green-700);
    flex-shrink: 0;
  }
  .customer-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--gray-900);
  }
  .customer-meta {
    font-size: 11.5px;
    color: var(--gray-400);
    font-weight: 500;
    margin-top: 2px;
  }
  .booking-row-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }
  @media (min-width: 640px) {
    .booking-row-actions {
      justify-content: flex-end;
    }
  }
  .btn-wa-soft {
    height: 36px;
    padding: 0 14px;
    background: var(--green-50);
    color: var(--green-700);
    border: 1px solid var(--green-100);
    border-radius: var(--radius-sm);
    font-family: var(--font);
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: background var(--trans), border-color var(--trans), transform var(--trans);
    text-decoration: none;
  }
  .btn-wa-soft:hover {
    background: var(--green-100);
    border-color: var(--green-200);
  }
  .btn-wa-soft:active { transform: scale(.97); }
  
  .btn-detail-arrow {
    height: 36px;
    padding: 0 14px;
    background: transparent;
    color: var(--gray-600);
    border: 1.5px solid var(--gray-200);
    border-radius: var(--radius-sm);
    font-family: var(--font);
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    transition: background var(--trans), border-color var(--trans), transform var(--trans);
  }
  .btn-detail-arrow:hover {
    background: var(--gray-50);
    border-color: var(--gray-300);
  }
  .btn-detail-arrow:active { transform: scale(.97); }

  /* ── Right Column Widgets ── */
  .right-widgets-container {
    display: flex;
    flex-direction: column;
    gap: 24px;
  }



  /* Donut Widget */
  .donut-widget {
    display: flex;
    align-items: center;
    gap: 24px;
    margin-top: 6px;
  }
  @media (max-width: 380px) {
    .donut-widget {
      flex-direction: column;
      align-items: center;
      text-align: center;
    }
  }

  /* Timeline Activities */
  .timeline-container {
    position: relative;
    padding-left: 16px;
    margin-left: 12px;
    border-left: 2px dashed var(--gray-200);
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-top: 6px;
  }
  .timeline-item {
    position: relative;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
  }
  .timeline-marker {
    position: absolute;
    left: -28px;
    top: 0;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: var(--white);
    border: 2px solid var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--shadow-sm);
    z-index: 2;
  }
  .timeline-marker svg {
    width: 12px;
    height: 12px;
  }
  .timeline-content {
    flex: 1;
    min-width: 0;
  }
  .timeline-title {
    font-size: 12.5px;
    font-weight: 700;
    color: var(--gray-900);
  }
  .timeline-subtitle {
    font-size: 11px;
    color: var(--gray-500);
    margin-top: 1px;
  }
  .timeline-time {
    font-size: 10px;
    color: var(--gray-400);
    font-weight: 600;
    white-space: nowrap;
    margin-top: 2px;
  }

  /* QR Modal styles */
  .modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.55); z-index: 200;
    align-items: flex-end; justify-content: center;
  }
  .modal-overlay.open { display: flex; }
  .modal-sheet {
    background: var(--white); border-radius: 28px 28px 0 0;
    padding: 28px 24px 40px; width: 100%; max-width: 430px;
    animation: slideUp .32s cubic-bezier(.34,1.56,.64,1) both;
  }
  @keyframes slideUp {
    from { transform: translateY(100%); }
    to   { transform: translateY(0); }
  }
  @media (min-width: 768px) {
    .modal-overlay {
      align-items: center;
    }
    .modal-sheet {
      border-radius: var(--radius-lg);
      padding: 32px;
      max-width: 480px;
      animation: zoomIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) both;
    }
    .modal-handle {
      display: none;
    }
  }
  @keyframes zoomIn {
    from { opacity: 0; transform: scale(0.9); }
    to   { opacity: 1; transform: scale(1); }
  }
  .modal-handle {
    width: 40px; height: 4px; background: var(--gray-200);
    border-radius: 99px; margin: 0 auto 20px;
  }
  .modal-title { font-size: 18px; font-weight: 800; color: var(--gray-900); margin-bottom: 16px; }
  .modal-row {
    display: flex; justify-content: space-between;
    padding: 10px 0; border-bottom: 1px solid var(--gray-100);
    font-size: 14px;
  }
  .modal-row:last-child { border: none; }
  .modal-row-label { color: var(--gray-500); font-weight: 500; }
  .modal-row-value { color: var(--gray-900); font-weight: 700; text-align: right; max-width: 60%; }
  .modal-qr {
    display: flex; justify-content: center;
    margin: 16px 0;
  }
  .modal-qr img { width: 180px; height: 180px; border-radius: 12px; border: 2px solid var(--gray-200); }
  .modal-close {
    width: 100%; height: 48px; background: var(--gray-100); color: var(--gray-700);
    border: none; border-radius: var(--radius-md); font-family: var(--font);
    font-size: 15px; font-weight: 700; cursor: pointer; margin-top: 16px;
  }
</style>
@endsection

@section('content')
<!-- App Header (Mobile Only) -->
<div class="app-header">
  <a href="{{ route('sisir.splash') }}" class="brand">
    <img src="{{ asset('ico-sisir.ico') }}" width="28" height="28" alt="SISIR" style="border-radius:6px;" />
    <span class="brand-name">SISIR</span>
  </a>
  <div style="display:flex;align-items:center;gap:12px">
    @if(auth()->check())
      <form method="POST" action="{{ route('sisir.logout') }}" style="margin:0">
        @csrf
        <button type="submit" style="background:none;border:none;cursor:pointer;font-size:11px;color:var(--gray-400);font-family:var(--font);font-weight:600">Keluar</button>
      </form>
    @endif
    <div class="avatar-btn">
      <div class="avatar-fallback">{{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'A' }}</div>
    </div>
  </div>
</div>

<div class="page-scroll">
  <!-- Greeting -->
  <div class="greeting-section anim-fade-up md:px-0">
    <div class="greeting-date" id="greetDate">–</div>
    <h1 class="greeting-name">Halo, {{ auth()->check() ? explode(' ', auth()->user()->name)[0] : 'Admin' }}! 👋</h1>
    <p class="greeting-subtitle mt-0.5">Berikut ringkasan booking dan antrean hari ini.</p>
  </div>

  <!-- Summary Cards Grid -->
  <div class="stats-grid anim-fade-up delay-1">
    <!-- Card 1: Booking Hari Ini -->
    <div class="stat-card">
      <div class="stat-card-top">
        <div>
          <span class="stat-card-label">Booking Hari Ini</span>
          <h2 class="stat-card-value">{{ $todayTotalBookings }} booking</h2>
        </div>
        <div class="stat-icon-box bg-[var(--green-50)] text-[var(--green-600)]">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
      </div>
      <div class="stat-card-bottom">
        @if($bookingTrend >= 0)
          <span class="text-[var(--green-600)]">↑ {{ $bookingTrend }}% <span class="text-gray-400 font-medium">dari kemarin</span></span>
        @else
          <span class="text-[var(--red-500)]">↓ {{ abs($bookingTrend) }}% <span class="text-gray-400 font-medium">dari kemarin</span></span>
        @endif
      </div>
    </div>

    <!-- Card 2: Sedang Dilayani -->
    <div class="stat-card">
      <div class="stat-card-top">
        <div>
          <span class="stat-card-label">Sedang Dilayani</span>
          <h2 class="stat-card-value">{{ $todayInServiceCount }} booking</h2>
        </div>
        <div class="stat-icon-box bg-blue-50 text-blue-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>
      <div class="stat-card-bottom">
        <a href="{{ route('sisir.booking') }}" class="stat-link">Lihat detail</a>
      </div>
    </div>

    <!-- Card 3: Selesai Hari Ini -->
    <div class="stat-card">
      <div class="stat-card-top">
        <div>
          <span class="stat-card-label">Selesai Hari Ini</span>
          <h2 class="stat-card-value">{{ $todayCompletedCount }} booking</h2>
        </div>
        <div class="stat-icon-box bg-yellow-50 text-yellow-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
          </svg>
        </div>
      </div>
      <div class="stat-card-bottom">
        <a href="{{ route('sisir.booking') }}" class="stat-link">Lihat detail</a>
      </div>
    </div>

    <!-- Card 4: Total Pelanggan -->
    <div class="stat-card">
      <div class="stat-card-top">
        <div>
          <span class="stat-card-label">Total Pelanggan</span>
          <h2 class="stat-card-value">{{ $totalCustomersCount }} pelanggan</h2>
        </div>
        <div class="stat-icon-box bg-purple-50 text-purple-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </div>
      </div>
      <div class="stat-card-bottom">
        <a href="{{ route('sisir.booking') }}" class="stat-link">Lihat semua</a>
      </div>
    </div>
  </div>

  <!-- Split Layout Area -->
  <div class="db-split-layout anim-fade-up delay-2">
    <!-- Left Column: Booking Hari Ini list -->
    <div class="db-panel">
      <div class="db-panel-header">
        <div class="db-panel-title-area">
          <span class="text-[var(--green-700)]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </span>
          <h3 class="db-panel-title">Booking Hari Ini</h3>
        </div>
        <a href="{{ route('sisir.booking') }}" class="db-panel-link">
          <span>Lihat Semua</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </a>
      </div>

      <div class="booking-list-container">
        @forelse ($todayBookings as $booking)
          <div class="booking-row">
            <!-- Left: Profile & service info -->
            <div class="booking-row-profile">
              <div class="customer-avatar">
                {{ strtoupper(substr($booking->customer->name ?? 'U', 0, 1)) }}
              </div>
              <div>
                <h4 class="customer-name">{{ $booking->customer->name }}</h4>
                <p class="customer-meta">
                  {{ $booking->scheduled_at->timezone('Asia/Jakarta')->format('H:i') }} · 
                  {{ $booking->service->name ?? 'Layanan' }} · 
                  {{ $booking->barber ? $booking->barber->displayName() : 'Kang Andi' }}
                </p>
              </div>
            </div>

            <!-- Right: Status, WA link, Detail -->
            <div class="booking-row-actions">
              <span class="status-badge {{ $booking->status->badgeClass() }}">
                {{ $booking->status->shortLabel() }}
              </span>

              <div class="flex items-center gap-2">
                <a href="https://wa.me/{{ $booking->customer->phone ?? '' }}" target="_blank" class="btn-wa-soft">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.003 5.324 5.328 0 11.859 0c3.161.001 6.132 1.23 8.367 3.465 2.235 2.235 3.461 5.207 3.46 8.369-.003 6.535-5.328 11.859-11.859 11.859-2.002-.001-3.973-.509-5.717-1.48L0 24zm6.208-4.225l.321.19a10.155 10.155 0 0 0 5.333 1.519c5.688 0 10.316-4.628 10.32-10.317.002-2.755-1.071-5.346-3.023-7.299-1.954-1.954-4.546-3.025-7.299-3.025-5.69 0-10.318 4.629-10.322 10.32-.001 1.996.521 3.945 1.512 5.669l.22.385-1.002 3.66 3.74-.982zm11.536-6.83c-.276-.138-1.636-.807-1.89-.899-.253-.093-.437-.138-.621.138-.184.276-.713.899-.874 1.084-.161.184-.322.207-.598.069-.276-.138-1.168-.43-2.223-1.372-.821-.733-1.375-1.639-1.536-1.915-.161-.276-.017-.426.12-.563.124-.123.276-.322.414-.483.138-.161.184-.276.276-.46.092-.184.046-.345-.023-.483-.069-.138-.621-1.496-.851-2.047-.224-.54-.449-.467-.621-.476-.161-.008-.345-.01-.529-.01-.184 0-.483.069-.736.345-.253.276-.966.943-.966 2.3 0 1.357.989 2.668 1.127 2.852.138.184 1.947 2.973 4.717 4.167.659.284 1.174.453 1.576.58.662.21 1.265.18 1.741.11.531-.077 1.636-.669 1.866-1.314.23-.645.23-1.197.161-1.314-.069-.115-.253-.184-.529-.322z"/>
                  </svg>
                  <span>WhatsApp</span>
                </a>
                <button onclick="openDetail({{ $booking->id }})" class="btn-detail-arrow">
                  <span>Detail</span>
                  <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        @empty
          <div style="text-align:center;padding:40px 20px;color:var(--gray-400);">
            <svg style="margin: 0 auto 12px; display: block; opacity: 0.4;" width="48" height="48" viewBox="0 0 48 48" fill="none">
              <circle cx="24" cy="24" r="22" stroke="#9ca3af" stroke-width="2"/>
              <path d="M16 24h16M24 16v16" stroke="#9ca3af" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <p style="font-size:14px;">Belum ada booking hari ini.</p>
            <a href="{{ route('sisir.booking.create') }}" style="
              display:inline-block;margin-top:12px;padding:10px 20px;
              background:var(--green-600);color:white;border-radius:var(--radius-md);
              font-weight:700;font-size:13px;text-decoration:none;
            ">Buat Booking Sekarang</a>
          </div>
        @endforelse
      </div>
    </div>

    <!-- Right Column: Premium Widgets -->
    <div class="right-widgets-container">


      <!-- Widget 2: Ringkasan Status (Donut summary) -->
      <div class="db-panel">
        <div class="db-panel-header" style="margin-bottom:14px;">
          <div class="db-panel-title-area">
            <span class="text-[var(--green-700)]">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.003 9.003 0 1020.945 13H11V3.055z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
              </svg>
            </span>
            <h3 class="db-panel-title">Ringkasan Status</h3>
          </div>
        </div>

        <div class="donut-widget">
          <!-- Conic-gradient Donut wrapper -->
          <div class="relative w-28 h-28 rounded-full flex-shrink-0" style="background: conic-gradient({{ $conicGradient }}); box-shadow: var(--shadow-sm);">
            <div class="absolute inset-4 bg-white rounded-full flex items-center justify-center">
              <div class="text-center">
                <span class="block text-base font-extrabold text-[var(--gray-900)]">{{ $dilayaniCount + $menungguCount + $sudahDpCount + $batalCount + $selesaiCount }}</span>
                <span class="block text-[8px] text-[var(--gray-400)] font-bold uppercase tracking-wider">Total</span>
              </div>
            </div>
          </div>

          <!-- Legends list -->
          <div class="flex-1 space-y-1.5 text-xs text-[var(--gray-700)] w-full">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full" style="background-color: #1e7c3a"></span>
                <span class="font-medium">Dilayani</span>
              </div>
              <span class="font-bold">{{ $dilayaniCount }}</span>
            </div>
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full" style="background-color: #f9a825"></span>
                <span class="font-medium">Menunggu DP</span>
              </div>
              <span class="font-bold">{{ $menungguCount }}</span>
            </div>
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full" style="background-color: #4285f4"></span>
                <span class="font-medium">Sudah DP</span>
              </div>
              <span class="font-bold">{{ $sudahDpCount }}</span>
            </div>
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full" style="background-color: #d93025"></span>
                <span class="font-medium">Batal</span>
              </div>
              <span class="font-bold">{{ $batalCount }}</span>
            </div>
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full" style="background-color: #a3e6b9"></span>
                <span class="font-medium">Selesai</span>
              </div>
              <span class="font-bold">{{ $selesaiCount }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Widget 3: Aktivitas Terbaru (Timeline logs) -->
      <div class="db-panel">
        <div class="db-panel-header" style="margin-bottom:14px;">
          <div class="db-panel-title-area">
            <span class="text-[var(--green-700)]">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
              </svg>
            </span>
            <h3 class="db-panel-title">Aktivitas Terbaru</h3>
          </div>
        </div>

        <div class="timeline-container">
          @forelse ($recentActivities as $activity)
            <div class="timeline-item">
              <div class="timeline-marker {{ $activity['color_class'] }}">
                @if($activity['icon'] == 'calendar')
                  <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                @elseif($activity['icon'] == 'dp')
                  <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                  </svg>
                @elseif($activity['icon'] == 'service')
                  <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                @elseif($activity['icon'] == 'completed')
                  <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                @elseif($activity['icon'] == 'cancelled')
                  <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                @endif
              </div>
              <div class="timeline-content">
                <h4 class="timeline-title">{{ $activity['title'] }}</h4>
                <p class="timeline-subtitle">{{ $activity['subtitle'] }}</p>
              </div>
              <span class="timeline-time">{{ $activity['time'] }}</span>
            </div>
          @empty
            <p class="text-xs text-[var(--gray-400)] text-center py-4">Belum ada aktivitas.</p>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>



<!-- Booking Detail Modal -->
<div class="modal-overlay" id="detailModal" onclick="closeDetail(event)">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Detail Booking</div>
    <div id="modalContent">
      <div style="text-align:center;padding:24px;color:var(--gray-400)">Memuat...</div>
    </div>
    <button class="modal-close" onclick="closeDetailModal()">Tutup</button>
  </div>
</div>
@endsection

@section('scripts')
<script>
  (function setDate() {
    const days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli',
                    'Agustus','September','Oktober','November','Desember'];
    const d = new Date();
    const el = document.getElementById('greetDate');
    if (el) el.textContent = `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
  })();

  const statusColors = {
    'TEMP_LOCKED': '#e87b2b',
    'BOOKED': '#e87b2b',
    'CONFIRMED': '#e87b2b',
    'IN_SERVICE': '#208a40',
    'COMPLETED': '#208a40',
    'CANCELLED_BY_SYSTEM': '#d93025',
    'NO_SHOW': '#d93025',
  };

  function openDetail(id) {
    const modal = document.getElementById('detailModal');
    const content = document.getElementById('modalContent');
    modal.classList.add('open');
    content.innerHTML = '<div style="text-align:center;padding:24px;color:var(--gray-400)">Memuat...</div>';

    fetch(`/booking/${id}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
      const color = statusColors[data.status] || '#6b7280';
      content.innerHTML = `
        ${data.qr_code_url ? `<div class="modal-qr"><img src="${data.qr_code_url}" alt="QR QRIS"/></div>` : ''}
        <div class="modal-row">
          <span class="modal-row-label">Pelanggan</span>
          <span class="modal-row-value">${data.customer_name}</span>
        </div>
        <div class="modal-row">
          <span class="modal-row-label">Layanan</span>
          <span class="modal-row-value">${data.service_name}</span>
        </div>
        <div class="modal-row">
          <span class="modal-row-label">Kapster</span>
          <span class="modal-row-value">${data.barber_name}</span>
        </div>
        <div class="modal-row">
          <span class="modal-row-label">Jadwal</span>
          <span class="modal-row-value">${data.scheduled_at}</span>
        </div>
        <div class="modal-row">
          <span class="modal-row-label">Status</span>
          <span class="modal-row-value" style="color:${color}">${data.status_label}</span>
        </div>
        <div class="modal-row">
          <span class="modal-row-label">DP</span>
          <span class="modal-row-value">Rp ${data.dp_amount}</span>
        </div>
        ${data.midtrans_order ? `<div class="modal-row"><span class="modal-row-label">Order ID</span><span class="modal-row-value" style="font-size:11px">${data.midtrans_order}</span></div>` : ''}
      `;
    })
    .catch(() => {
      content.innerHTML = '<div style="text-align:center;padding:24px;color:var(--red-500)">Gagal memuat detail.</div>';
    });
  }

  function closeDetail(e) {
    if (e.target === document.getElementById('detailModal')) closeDetailModal();
  }

  function closeDetailModal() {
    document.getElementById('detailModal').classList.remove('open');
  }
</script>
@endsection
