@extends('layouts.sisir')

@section('title', 'Daftar Booking – SISIR')
@section('meta_description', 'Kelola semua antrean booking pelanggan barbershop Anda.')

@section('extra_styles')
<style>
  body, .sisir-shell { background: var(--white); }

  /* ── Page header ── */
  .bl-page-header {
    padding: 8px 20px 4px;
    background: var(--white);
  }
  .bl-title  { font-size: 28px; font-weight: 800; color: var(--gray-900); margin-bottom: 2px; }
  .bl-subtitle { font-size: 13px; color: var(--gray-500); margin-bottom: 16px; }

  /* ── Search ── */
  .search-wrap {
    display: flex; align-items: center; gap: 10px;
    background: var(--gray-100);
    border-radius: var(--radius-full);
    padding: 10px 16px;
    margin: 0 20px 16px;
    transition: box-shadow var(--trans);
  }
  .search-wrap:focus-within {
    box-shadow: 0 0 0 2px var(--green-300);
  }
  .search-input {
    border: none; background: transparent; outline: none;
    font-family: var(--font); font-size: 14px; color: var(--gray-700); width: 100%;
  }
  .search-input::placeholder { color: var(--gray-400); }

  /* ── Filter tabs ── */
  .filter-tabs {
    display: flex; gap: 8px;
    padding: 0 20px 16px;
    overflow-x: auto; flex-shrink: 0;
    scrollbar-width: none;
  }
  .filter-tabs::-webkit-scrollbar { display: none; }
  .filter-tab {
    padding: 8px 18px;
    border-radius: var(--radius-full);
    font-family: var(--font); font-size: 13px; font-weight: 600;
    border: 1.5px solid var(--gray-200);
    background: transparent; color: var(--gray-600);
    cursor: pointer; white-space: nowrap;
    transition: all var(--trans); flex-shrink: 0;
    text-decoration: none; display: inline-block;
  }
  .filter-tab.active {
    background: var(--green-700); border-color: var(--green-700); color: var(--white);
    box-shadow: 0 2px 8px rgba(30,124,58,.3);
  }
  .filter-tab:not(.active):hover { background: var(--green-50); border-color: var(--green-300); color: var(--green-700); }

  /* ── List items ── */
  .bli-list { padding: 0 20px 24px; }
  .booking-list-item {
    background: var(--white);
    border: 1.5px solid var(--gray-100);
    border-radius: var(--radius-lg);
    padding: 16px;
    margin-bottom: 12px;
    box-shadow: var(--shadow-sm);
    transition: box-shadow var(--trans), transform var(--trans);
  }
  .booking-list-item:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
  .bli-top {
    display: flex; align-items: center; gap: 12px; margin-bottom: 10px;
  }
  .bli-avatar {
    width: 44px; height: 44px;
    background: linear-gradient(135deg, var(--green-300), var(--green-500));
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 800; color: var(--white); flex-shrink: 0;
  }
  .bli-name { font-size: 14px; font-weight: 700; color: var(--gray-900); margin-bottom: 2px; }
  .bli-meta { font-size: 12px; color: var(--gray-500); display: flex; align-items: center; gap: 4px; }
  .bli-bottom {
    display: flex; align-items: center; justify-content: space-between;
    padding-top: 10px; border-top: 1px solid var(--gray-100);
  }
  .bli-payment {
    display: flex; align-items: center; gap: 6px;
    font-size: 12px; font-weight: 600;
  }
  .bli-payment.dp    { color: var(--orange-500); }
  .bli-payment.lunas { color: var(--green-600); }
  .bli-payment.batal { color: var(--red-500); }
  .bli-detail-btn {
    font-size: 13px; font-weight: 700; color: var(--green-600);
    cursor: pointer; display: flex; align-items: center; gap: 2px;
    border: none; background: none; font-family: var(--font);
    transition: color var(--trans);
  }
  .bli-detail-btn:hover { color: var(--green-700); }

  /* New booking FAB */
  .new-booking-fab {
    position: fixed;
    right: calc(50% - 215px + 20px);
    bottom: 88px;
    width: 52px; height: 52px;
    background: var(--green-600);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 16px rgba(20,82,40,.45);
    cursor: pointer; z-index: 50;
    transition: transform var(--trans), box-shadow var(--trans);
    text-decoration: none;
  }
  .new-booking-fab:hover { transform: scale(1.08); box-shadow: 0 6px 22px rgba(20,82,40,.55); }
  @media (max-width: 430px) { .new-booking-fab { right: 20px; } }
  @media (min-width: 768px) {
    .new-booking-fab {
      right: 48px;
      bottom: 48px;
    }
  }

  /* No results */
  .no-results {
    text-align: center; padding: 40px 20px;
    color: var(--gray-400); font-size: 14px;
  }
  .no-results svg { margin: 0 auto 12px; display: block; opacity: .4; }

  /* ── Date Filter Form ── */
  .date-filter-form {
    padding: 0 20px 16px;
  }
  .filter-dropdowns {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
  }
  .filter-select {
    padding: 8px 16px;
    border-radius: var(--radius-full);
    border: 1.5px solid var(--gray-200);
    background: var(--white);
    color: var(--gray-700);
    font-family: var(--font);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    outline: none;
    transition: all var(--trans);
  }
  .filter-select:hover {
    border-color: var(--green-300);
    color: var(--green-700);
  }
  .filter-select:focus {
    border-color: var(--green-500);
    box-shadow: 0 0 0 2px var(--green-100);
  }
  .clear-filter-btn {
    font-size: 13px;
    font-weight: 700;
    color: var(--red-500);
    text-decoration: none;
    transition: color var(--trans);
    padding: 8px 12px;
  }
  .clear-filter-btn:hover {
    color: var(--red-700);
  }

  /* Mobile Responsive Spacing overrides */
  @media (max-width: 767px) {
    .bli-list {
      padding: 0 12px 24px;
    }
    .booking-list-item {
      padding: 12px;
    }
    .date-filter-form {
      padding: 0 12px 16px;
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
<!-- App Header -->
<div class="app-header">
  <a href="{{ route('sisir.splash') }}" class="brand">
    <img src="{{ asset('ico-sisir.ico') }}" width="28" height="28" alt="SISIR" style="border-radius:6px;" />
    <span class="brand-name">SISIR</span>
  </a>
  <div class="avatar-btn"><div class="avatar-fallback">{{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'A' }}</div></div>
</div>

<!-- Page title -->
<div class="bl-page-header anim-fade-up md:px-0">
  <h1 class="bl-title">Daftar Booking</h1>
  <p class="bl-subtitle">{{ $bookings->total() }} total booking ditemukan.</p>
</div>

<!-- Search & Filters Wrapper -->
<div class="md:flex md:flex-row md:items-center md:justify-between md:gap-4 md:px-0 md:mb-6">
  <!-- Search (client-side) -->
  <div class="search-wrap anim-fade-up delay-1 md:mx-0 md:mb-0 flex-1 md:max-w-xs">
    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
      <circle cx="8" cy="8" r="6" stroke="#9ca3af" stroke-width="2"/>
      <line x1="13" y1="13" x2="17" y2="17" stroke="#9ca3af" stroke-width="2" stroke-linecap="round"/>
    </svg>
    <input class="search-input" id="searchInput" type="search" placeholder="Cari nama pelanggan..." autocomplete="off" oninput="applySearch()" />
  </div>

  <!-- Filter tabs — server-side via GET param -->
  <div class="filter-tabs anim-fade-up delay-1 md:px-0 md:pb-0 md:mb-0">
    @foreach(['Semua','Menunggu','Sudah DP','Selesai','Batal'] as $tab)
      <a href="{{ route('sisir.booking', ['status' => $tab]) }}"
         class="filter-tab {{ $filterStatus === $tab ? 'active' : '' }}">
        {{ $tab }}
      </a>
    @endforeach
  </div>
</div>

<!-- Date Filter Form -->
<form method="GET" action="{{ route('sisir.booking') }}" class="date-filter-form anim-fade-up delay-2 md:px-0 md:mb-6">
  <input type="hidden" name="status" value="{{ request('status', $filterStatus) }}">
  @if(request('search'))
    <input type="hidden" name="search" value="{{ request('search') }}">
  @endif

  <div class="filter-dropdowns">
    <select name="day" onchange="this.form.submit()" class="filter-select">
      <option value="">Tanggal</option>
      @for($d = 1; $d <= 31; $d++)
        <option value="{{ $d }}" {{ request('day') == $d ? 'selected' : '' }}>{{ $d }}</option>
      @endfor
    </select>

    <select name="month" onchange="this.form.submit()" class="filter-select">
      <option value="">Bulan</option>
      @foreach([
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
      ] as $num => $name)
        <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>{{ $name }}</option>
      @endforeach
    </select>

    <select name="year" onchange="this.form.submit()" class="filter-select">
      <option value="">Tahun</option>
      @php
        $currentYear = date('Y');
      @endphp
      @for($y = $currentYear + 1; $y >= $currentYear - 3; $y--)
        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
      @endfor
    </select>

    @if(request('day') || request('month') || request('year'))
      <a href="{{ route('sisir.booking', ['status' => request('status', $filterStatus)]) }}" class="clear-filter-btn">
        Hapus Filter
      </a>
    @endif
  </div>
</form>

<!-- Booking list -->
<div class="page-scroll">
  <div class="bli-list grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:px-0" id="bookingList">

    @forelse ($bookings as $booking)
      @php
        $initials = collect(explode(' ', $booking->customer->name ?? 'U'))
          ->take(2)->map(fn($w) => strtoupper(substr($w,0,1)))->join('');
        $isNegative = in_array($booking->status->value, ['CANCELLED_BY_SYSTEM','NO_SHOW']);
        $isDone     = $booking->status->value === 'COMPLETED';
      @endphp
      <div class="booking-list-item anim-slide delay-{{ min($loop->iteration, 4) }} md:mx-0 md:mb-0"
           data-name="{{ strtolower($booking->customer->name ?? '') }}">
        <div class="bli-top">
          <div class="bli-avatar" style="{{ $isNegative ? 'background:linear-gradient(135deg,#fca5a5,#ef4444)' : '' }}">{{ $initials }}</div>
          <div style="flex:1;min-width:0">
            <div class="bli-name">{{ $booking->customer->name }}</div>
            <div class="bli-meta">
              <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><circle cx="6" cy="6" r="5" stroke="#9ca3af" stroke-width="1.5"/><path d="M6 3v3l2 1" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round"/></svg>
              {{ $booking->scheduled_at->timezone('Asia/Jakarta')->format('H:i') }}
              @if($booking->service)
                – {{ $booking->service->name }}
              @endif
              @if($booking->barber)
                · {{ $booking->barber->displayName() }}
              @endif
            </div>
          </div>
          <span class="status-badge {{ $booking->status->badgeClass() }}">
            {{ $booking->status->shortLabel() }}
          </span>
        </div>
        <div class="bli-bottom">
          @if($isNegative)
            <span class="bli-payment batal">
              <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><circle cx="7" cy="7" r="6" stroke="#d93025" stroke-width="1.5"/><path d="M5 5l4 4M9 5l-4 4" stroke="#d93025" stroke-width="1.5" stroke-linecap="round"/></svg>
              {{ $booking->status->label() }}
            </span>
          @elseif($isDone)
            <span class="bli-payment lunas">
              <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><circle cx="7" cy="7" r="6" stroke="#1a6b32" stroke-width="1.5"/><path d="M4 7l2.5 2.5L10 5" stroke="#1a6b32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Selesai
            </span>
          @else
            <span class="bli-payment dp">
              <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="3" width="12" height="8" rx="2" stroke="#e87b2b" stroke-width="1.5"/><line x1="1" y1="6" x2="13" y2="6" stroke="#e87b2b" stroke-width="1.5"/></svg>
              Rp {{ number_format($booking->dp_amount, 0, ',', '.') }} DP
            </span>
          @endif
          <button class="bli-detail-btn" onclick="openDetail({{ $booking->id }})">
            Detail <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M5 3l4 4-4 4" stroke="#208a40" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
        </div>
      </div>
    @empty
      <div class="no-results">
        <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
          <circle cx="24" cy="24" r="22" stroke="#9ca3af" stroke-width="2"/>
          <path d="M16 24h16M24 16v16" stroke="#9ca3af" stroke-width="2" stroke-linecap="round"/>
        </svg>
        Tidak ada booking ditemukan
      </div>
    @endforelse

  </div>

  {{-- Pagination --}}
  @if($bookings->hasPages())
    <div style="padding: 0 20px 24px;">
      {{ $bookings->appends(request()->query())->links() }}
    </div>
  @endif
</div>

<!-- New Booking FAB -->
<a href="{{ route('sisir.booking.create') }}" class="new-booking-fab" title="Booking Baru">
  <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
    <line x1="12" y1="5" x2="12" y2="19" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
    <line x1="5" y1="12" x2="19" y2="12" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
  </svg>
</a>



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
  // Client-side name search on the current page
  function applySearch() {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    document.querySelectorAll('#bookingList .booking-list-item').forEach(item => {
      const name = item.dataset.name;
      item.style.display = !q || name.includes(q) ? '' : 'none';
    });
  }

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
