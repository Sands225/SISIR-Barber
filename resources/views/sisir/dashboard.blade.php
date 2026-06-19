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

  /* Mobile Responsive Spacing overrides */
  @media (max-width: 767px) {
    .db-split-layout {
      padding: 0 12px 24px;
      gap: 16px;
    }
    .db-panel {
      padding: 16px 12px;
    }
    .booking-row {
      padding: 12px;
      gap: 8px;
    }
  }
  @media (max-width: 400px) {
    .btn-wa-soft span {
      display: none;
    }
    .btn-wa-soft {
      padding: 0 10px;
    }
  }

  /* ── Premium Detail Modal Design ── */
  .modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.55); z-index: 200;
    align-items: flex-end; justify-content: center;
  }
  .modal-overlay.open { display: flex; }

  .modal-sheet-premium {
    background: var(--white);
    border-radius: 28px 28px 0 0;
    padding: 24px;
    width: 100%;
    max-width: 450px;
    animation: slideUp .32s cubic-bezier(.34,1.56,.64,1) both;
    display: flex;
    flex-direction: column;
    gap: 16px;
    box-shadow: 0 -8px 30px rgba(0,0,0,0.08);
  }
  @keyframes slideUp {
    from { transform: translateY(100%); }
    to   { transform: translateY(0); }
  }
  @media (min-width: 768px) {
    .modal-overlay {
      align-items: center;
    }
    .modal-sheet-premium {
      border-radius: 24px;
      animation: zoomIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) both;
    }
  }
  @keyframes zoomIn {
    from { opacity: 0; transform: scale(0.9); }
    to   { opacity: 1; transform: scale(1); }
  }

  .modal-header-premium {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 8px;
  }
  .modal-back-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px solid var(--gray-200);
    background: var(--white);
    color: var(--green-700);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background var(--trans);
  }
  .modal-back-btn:hover {
    background: var(--green-50);
  }
  .modal-title-premium {
    font-size: 16px;
    font-weight: 800;
    color: var(--gray-900);
  }
  .modal-avatar-premium {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--green-50);
    color: var(--green-700);
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
  }

  .modal-card-top {
    background: linear-gradient(135deg, var(--green-700), var(--green-600));
    border-radius: 16px;
    padding: 16px 20px;
    color: var(--white);
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 12px rgba(26,107,50,0.15);
  }
  .modal-booking-id {
    font-size: 16px;
    font-weight: 800;
    letter-spacing: 0.5px;
  }
  
  .modal-card-middle {
    background: var(--white);
    border: 1px solid var(--gray-200);
    border-radius: 18px;
    overflow: hidden;
  }
  .modal-info-section {
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  .modal-info-item {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 12px;
    align-items: flex-start;
    font-size: 13px;
  }
  .modal-info-label {
    color: var(--gray-500);
    font-weight: 600;
  }
  .modal-info-value {
    color: var(--gray-900);
    font-weight: 700;
    text-align: right;
    display: flex;
    justify-content: flex-end;
    align-items: center;
  }
  .modal-status-badge-pill {
    padding: 6px 12px;
    border-radius: var(--radius-full);
    font-size: 11px;
    font-weight: 800;
    background: var(--white);
    white-space: nowrap;
    text-align: center;
    flex-shrink: 0;
  }

  .modal-blue-row {
    background: #eef7ff;
    padding: 14px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px dashed rgba(66,133,244,0.2);
  }
  .modal-blue-label {
    color: #1a73e8;
    font-weight: 700;
    font-size: 13px;
  }
  .modal-blue-value {
    color: #1a73e8;
    font-weight: 800;
    font-size: 15px;
  }

  .modal-actions-premium {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 8px;
  }
  .btn-action-premium {
    width: 100%;
    height: 48px;
    border-radius: 12px;
    font-family: var(--font);
    font-size: 14px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    transition: transform var(--trans), opacity var(--trans);
    border: none;
    text-decoration: none;
  }
  .btn-action-premium:active {
    transform: scale(0.98);
  }
  .btn-action-wa {
    background: #128c7e;
    color: var(--white);
  }
  .btn-action-wa:hover {
    background: #0e7265;
  }
  .btn-action-done {
    background: var(--green-600);
    color: var(--white);
  }
  .btn-action-done:hover {
    background: var(--green-700);
  }
  .btn-action-cancel {
    background: #d32f2f;
    color: var(--white);
  }
  .btn-action-cancel:hover {
    background: #c62828;
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
  <div class="modal-sheet-premium" id="detailModalSheet">
    <div id="modalContent">
      <div style="text-align:center;padding:24px;color:var(--gray-400)">Memuat...</div>
    </div>
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
    'CONFIRMED': '#4285f4',
    'IN_SERVICE': '#1e7c3a',
    'COMPLETED': '#1e7c3a',
    'CANCELLED_BY_SYSTEM': '#d93025',
    'NO_SHOW': '#d93025',
  };

  function openDetail(id) {
    const modal = document.getElementById('detailModal');
    const content = document.getElementById('modalContent');
    modal.classList.add('open');
    content.innerHTML = '<div style="text-align:center;padding:32px;color:var(--gray-400)">Memuat...</div>';

    fetch(`/booking/${id}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
      window.currentBookingData = data;
      const color = statusColors[data.status] || '#1a6b32';
      
      let actionsHtml = '';
      if (data.status !== 'COMPLETED' && data.status !== 'CANCELLED_BY_SYSTEM' && data.status !== 'NO_SHOW') {
        actionsHtml = `
          <div class="modal-actions-premium">
            <a href="https://wa.me/${data.customer_phone}" target="_blank" class="btn-action-premium btn-action-wa">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.003 5.324 5.328 0 11.859 0c3.161.001 6.132 1.23 8.367 3.465 2.235 2.235 3.461 5.207 3.46 8.369-.003 6.535-5.328 11.859-11.859 11.859-2.002-.001-3.973-.509-5.717-1.48L0 24zm6.208-4.225l.321.19a10.155 10.155 0 0 0 5.333 1.519c5.688 0 10.316-4.628 10.32-10.317.002-2.755-1.071-5.346-3.023-7.299-1.954-1.954-4.546-3.025-7.299-3.025-5.69 0-10.318 4.629-10.322 10.32-.001 1.996.521 3.945 1.512 5.669l.22.385-1.002 3.66 3.74-.982zm11.536-6.83c-.276-.138-1.636-.807-1.89-.899-.253-.093-.437-.138-.621.138-.184.276-.713.899-.874 1.084-.161.184-.322.207-.598.069-.276-.138-1.168-.43-2.223-1.372-.821-.733-1.375-1.639-1.536-1.915-.161-.276-.017-.426.12-.563.124-.123.276-.322.414-.483.138-.161.184-.276.276-.46.092-.184.046-.345-.023-.483-.069-.138-.621-1.496-.851-2.047-.224-.54-.449-.467-.621-.476-.161-.008-.345-.01-.529-.01-.184 0-.483.069-.736.345-.253.276-.966.943-.966 2.3 0 1.357.989 2.668 1.127 2.852.138.184 1.947 2.973 4.717 4.167.659.284 1.174.453 1.576.58.662.21 1.265.18 1.741.11.531-.077 1.636-.669 1.866-1.314.23-.645.23-1.197.161-1.314-.069-.115-.253-.184-.529-.322z"/>
              </svg>
              Hubungi via WhatsApp
            </a>
            <button onclick="transitionBooking(${data.id}, 'COMPLETED')" class="btn-action-premium btn-action-done">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
              </svg>
              Tandai Selesai
            </button>
            <button onclick="showCancelConfirmation()" class="btn-action-premium btn-action-cancel">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Batalkan Booking
            </button>
          </div>
        `;
      }

      let qrHtml = '';
      if (data.status === 'BOOKED' && data.qr_code_url) {
        qrHtml = `
          <div class="modal-card-middle" style="padding: 16px 20px; text-align: center; border: 1.5px dashed var(--green-300); background: var(--green-50); margin-top: 12px; border-radius: 18px;">
            <div class="text-xs font-bold text-[var(--green-700)] mb-2">Pindai QRIS untuk Uang Muka (DP)</div>
            <div class="modal-qr" style="margin: 8px 0; display: flex; justify-content: center;">
              <img src="${data.qr_code_url}" alt="QR QRIS" style="width:140px; height:140px; border-radius:12px; border:2px solid var(--gray-200);"/>
            </div>
            <div class="text-[10px] text-gray-500 font-semibold mt-1">DP Amount: Rp ${data.dp_amount}</div>
          </div>
        `;
      }

      content.innerHTML = `
        <div class="modal-header-premium">
          <button class="modal-back-btn" onclick="closeDetailModal()">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <span class="modal-title-premium">Detail Booking</span>
          <div class="modal-avatar-premium">
            ${data.customer_name.substring(0, 1).toUpperCase()}
          </div>
        </div>

        <div class="modal-card-top">
          <span class="modal-booking-id">#BK-${data.id_formatted}</span>
          <span class="modal-status-badge-pill" style="color: ${color}">
            ${data.status_label}
          </span>
        </div>

        <div class="modal-card-middle">
          <div class="modal-info-section">
            <div class="modal-info-item">
              <span class="modal-info-label">Pelanggan</span>
              <span class="modal-info-value">${data.customer_name}</span>
            </div>
            <div class="modal-info-item">
              <span class="modal-info-label">Waktu</span>
              <span class="modal-info-value">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="margin-right: 4px;">
                  <circle cx="12" cy="12" r="9" />
                  <path d="M12 6v6l3 3" />
                </svg>
                <span>${data.scheduled_at}</span>
              </span>
            </div>
            <div class="modal-info-item">
              <span class="modal-info-label">Layanan</span>
              <span class="modal-info-value">
                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-[var(--green-50)] text-[var(--green-700)]" style="white-space: nowrap;">
                  ${data.service_name}
                </span>
              </span>
            </div>
            <div class="modal-info-item">
              <span class="modal-info-label">Barber Shop</span>
              <span class="modal-info-value">
                <img src="/ico-sisir.ico" width="16" height="16" style="border-radius:3px; margin-right: 4px;" />
                SISIR Barber Shop
              </span>
            </div>
          </div>

          <div class="modal-info-section" style="border-top: 1px solid var(--gray-100); background: #fbfbfb; display: flex; flex-direction: column; gap: 8px;">
            <div class="modal-info-item">
              <span class="modal-info-label">Total Harga</span>
              <span class="modal-info-value">Rp ${data.service_price}</span>
            </div>
            <div class="modal-info-item">
              <span class="modal-info-label">Uang Muka (DP)</span>
              <span class="modal-info-value">Rp ${data.dp_amount}</span>
            </div>
          </div>

          <div class="modal-blue-row">
            <span class="modal-blue-label">Sisa Pembayaran</span>
            <span class="modal-blue-value">Rp ${data.remaining_pay}</span>
          </div>
        </div>

        ${qrHtml}
        ${actionsHtml}
      `;
    })
    .catch((err) => {
      console.error(err);
      content.innerHTML = '<div style="text-align:center;padding:32px;color:var(--red-500)">Gagal memuat detail.</div>';
    });
  }

  function showCancelConfirmation() {
    const data = window.currentBookingData;
    if (!data) return;
    const content = document.getElementById('modalContent');
    content.innerHTML = `
      <div style="display: flex; flex-direction: column; align-items: center; text-align: center; gap: 20px; padding: 12px 8px;">
        <!-- Warning Icon -->
        <div style="width: 64px; height: 64px; border-radius: 50%; background: #ffebee; display: flex; align-items: center; justify-content: center;">
          <svg class="w-8 h-8 text-[#c62828]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>

        <!-- Title & Subtitle -->
        <div>
          <h3 style="font-size: 20px; font-weight: 800; color: #111827; margin: 0;">Batalkan Booking?</h3>
          <p style="font-size: 13px; color: #6b7280; margin-top: 8px; line-height: 1.5; font-weight: 500;">
            Tindakan ini tidak dapat dibatalkan.<br>Slot jadwal akan kembali tersedia untuk pelanggan lain.
          </p>
        </div>

        <!-- Schedule Card -->
        <div style="width: 100%; background: #eef7ff; border: 1px solid #d0e7ff; border-radius: 16px; padding: 14px 18px; display: flex; align-items: center; gap: 12px; text-align: left;">
          <div style="color: #1a6b32; flex-shrink: 0;">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
          <div>
            <div style="font-size: 11px; color: #6b7280; font-weight: 600;">Jadwal yang akan dihapus:</div>
            <div style="font-size: 14px; color: #111827; font-weight: 700; margin-top: 2px;">${data.scheduled_at}</div>
          </div>
        </div>

        <!-- Buttons -->
        <div style="width: 100%; display: flex; flex-direction: column; gap: 8px; margin-top: 12px;">
          <button onclick="executeCancellation(${data.id})" style="width: 100%; height: 48px; border-radius: 12px; background: #c62828; color: white; font-family: var(--font); font-size: 14px; font-weight: 700; border: none; cursor: pointer; transition: background 0.2s; box-shadow: 0 4px 12px rgba(198,40,40,0.2);">
            Ya, Batalkan
          </button>
          <button onclick="openDetail(${data.id})" style="width: 100%; height: 48px; border-radius: 12px; background: transparent; color: #6b7280; font-family: var(--font); font-size: 14px; font-weight: 700; border: none; cursor: pointer; transition: color 0.2s;">
            Kembali
          </button>
        </div>
      </div>
    `;
  }

  function executeCancellation(id) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/booking/${id}/transition`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ status: 'CANCELLED_BY_SYSTEM' })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        window.location.reload();
      } else {
        alert(data.error || 'Gagal mengubah status booking.');
      }
    })
    .catch(err => {
      console.error(err);
      alert('Terjadi kesalahan koneksi.');
    });
  }

  function transitionBooking(id, status) {
    const label = status === 'COMPLETED' ? 'Selesai' : 'Batal';
    if (!confirm(`Apakah Anda yakin ingin menandai booking ini sebagai ${label}?`)) {
      return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/booking/${id}/transition`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ status: status })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        window.location.reload();
      } else {
        alert(data.error || 'Gagal mengubah status booking.');
      }
    })
    .catch(err => {
      console.error(err);
      alert('Terjadi kesalahan koneksi.');
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
