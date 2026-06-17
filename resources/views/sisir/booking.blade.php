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

  /* No results */
  .no-results {
    text-align: center; padding: 40px 20px;
    color: var(--gray-400); font-size: 14px;
  }
  .no-results svg { margin: 0 auto 12px; display: block; opacity: .4; }
</style>
@endsection

@section('content')
<!-- App Header -->
<div class="app-header">
  <a href="{{ route('sisir.splash') }}" class="brand">
    <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
      <circle cx="7" cy="7" r="5" fill="none" stroke="#1e7c3a" stroke-width="2"/>
      <circle cx="7" cy="21" r="5" fill="none" stroke="#1e7c3a" stroke-width="2"/>
      <line x1="7" y1="12" x2="24" y2="4" stroke="#1e7c3a" stroke-width="2" stroke-linecap="round"/>
      <line x1="7" y1="16" x2="24" y2="24" stroke="#1e7c3a" stroke-width="2" stroke-linecap="round"/>
      <circle cx="18" cy="14" r="2" fill="#1e7c3a"/>
    </svg>
    <span class="brand-name">SISIR</span>
  </a>
  <div class="avatar-btn"><div class="avatar-fallback">{{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'A' }}</div></div>
</div>

<!-- Page title -->
<div class="bl-page-header anim-fade-up">
  <h1 class="bl-title">Daftar Booking</h1>
  <p class="bl-subtitle">{{ $bookings->total() }} total booking ditemukan.</p>
</div>

<!-- Search (client-side) -->
<div class="search-wrap anim-fade-up delay-1">
  <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
    <circle cx="8" cy="8" r="6" stroke="#9ca3af" stroke-width="2"/>
    <line x1="13" y1="13" x2="17" y2="17" stroke="#9ca3af" stroke-width="2" stroke-linecap="round"/>
  </svg>
  <input class="search-input" id="searchInput" type="search" placeholder="Cari nama pelanggan..." autocomplete="off" oninput="applySearch()" />
</div>

<!-- Filter tabs — server-side via GET param -->
<div class="filter-tabs anim-fade-up delay-1">
  @foreach(['Semua','Menunggu','Sudah DP','Selesai','Batal'] as $tab)
    <a href="{{ route('sisir.booking', ['status' => $tab]) }}"
       class="filter-tab {{ $filterStatus === $tab ? 'active' : '' }}">
      {{ $tab }}
    </a>
  @endforeach
</div>

<!-- Booking list -->
<div class="page-scroll">
  <div class="bli-list" id="bookingList">

    @forelse ($bookings as $booking)
      @php
        $initials = collect(explode(' ', $booking->customer->name ?? 'U'))
          ->take(2)->map(fn($w) => strtoupper(substr($w,0,1)))->join('');
        $isNegative = in_array($booking->status->value, ['CANCELLED_BY_SYSTEM','NO_SHOW']);
        $isDone     = $booking->status->value === 'COMPLETED';
      @endphp
      <div class="booking-list-item anim-slide delay-{{ min($loop->iteration, 4) }}"
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
      {{ $bookings->appends(['status' => $filterStatus])->links() }}
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

<!-- Bottom Nav -->
<nav class="bottom-nav">
  <a href="{{ route('sisir.dashboard') }}" class="nav-item">
    <svg viewBox="0 0 24 24" fill="none">
      <rect x="3" y="3" width="8" height="8" rx="2" stroke="#9ca3af" stroke-width="2"/>
      <rect x="13" y="3" width="8" height="8" rx="2" stroke="#9ca3af" stroke-width="2"/>
      <rect x="3" y="13" width="8" height="8" rx="2" stroke="#9ca3af" stroke-width="2"/>
      <rect x="13" y="13" width="8" height="8" rx="2" stroke="#9ca3af" stroke-width="2"/>
    </svg>
    <span>Dashboard</span>
  </a>
  <a href="{{ route('sisir.booking') }}" class="nav-item active">
    <svg viewBox="0 0 24 24" fill="none">
      <rect x="3" y="5" width="18" height="16" rx="3" stroke="#208a40" stroke-width="2"/>
      <line x1="3" y1="10" x2="21" y2="10" stroke="#208a40" stroke-width="2"/>
      <line x1="8" y1="2" x2="8" y2="8" stroke="#208a40" stroke-width="2" stroke-linecap="round"/>
      <line x1="16" y1="2" x2="16" y2="8" stroke="#208a40" stroke-width="2" stroke-linecap="round"/>
    </svg>
    <span style="color:var(--green-600)">Booking</span>
  </a>
  <a href="{{ route('sisir.promo') }}" class="nav-item">
    <svg viewBox="0 0 24 24" fill="none">
      <path d="M12 2L14.09 8.26L21 9.27L16 14.14L17.18 21L12 18.27L6.82 21L8 14.14L3 9.27L9.91 8.26L12 2Z" stroke="#9ca3af" stroke-width="2" stroke-linejoin="round"/>
    </svg>
    <span>Promo</span>
  </a>
</nav>

<!-- Detail Modal (same as dashboard) -->
<div class="modal-overlay" id="detailModal" onclick="if(event.target===this)closeDetailModal()">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Detail Booking</div>
    <div id="modalContent"><div style="text-align:center;padding:24px;color:var(--gray-400)">Memuat...</div></div>
    <button class="modal-close" onclick="closeDetailModal()">Tutup</button>
  </div>
</div>
<style>
  .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:200;align-items:flex-end;justify-content:center; }
  .modal-overlay.open { display:flex; }
  .modal-sheet { background:var(--white);border-radius:28px 28px 0 0;padding:28px 24px 40px;width:100%;max-width:430px;animation:slideUp .32s cubic-bezier(.34,1.56,.64,1) both; }
  @keyframes slideUp { from{transform:translateY(100%)} to{transform:translateY(0)} }
  .modal-handle { width:40px;height:4px;background:var(--gray-200);border-radius:99px;margin:0 auto 20px; }
  .modal-title { font-size:18px;font-weight:800;color:var(--gray-900);margin-bottom:16px; }
  .modal-row { display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--gray-100);font-size:14px; }
  .modal-row:last-child { border:none; }
  .modal-row-label { color:var(--gray-500);font-weight:500; }
  .modal-row-value { color:var(--gray-900);font-weight:700;text-align:right;max-width:60%; }
  .modal-qr { display:flex;justify-content:center;margin:16px 0; }
  .modal-qr img { width:180px;height:180px;border-radius:12px;border:2px solid var(--gray-200); }
  .modal-close { width:100%;height:48px;background:var(--gray-100);color:var(--gray-700);border:none;border-radius:var(--radius-md);font-family:var(--font);font-size:15px;font-weight:700;cursor:pointer;margin-top:16px; }
</style>
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

  function openDetail(id) {
    const modal = document.getElementById('detailModal');
    const content = document.getElementById('modalContent');
    modal.classList.add('open');
    content.innerHTML = '<div style="text-align:center;padding:24px;color:var(--gray-400)">Memuat...</div>';

    fetch(`/booking/${id}`, { headers: { 'X-Requested-With':'XMLHttpRequest','Accept':'application/json' } })
      .then(r => r.json())
      .then(data => {
        content.innerHTML = `
          ${data.qr_code_url ? `<div class="modal-qr"><img src="${data.qr_code_url}" alt="QR QRIS"/></div>` : ''}
          <div class="modal-row"><span class="modal-row-label">Pelanggan</span><span class="modal-row-value">${data.customer_name}</span></div>
          <div class="modal-row"><span class="modal-row-label">Layanan</span><span class="modal-row-value">${data.service_name}</span></div>
          <div class="modal-row"><span class="modal-row-label">Kapster</span><span class="modal-row-value">${data.barber_name}</span></div>
          <div class="modal-row"><span class="modal-row-label">Jadwal</span><span class="modal-row-value">${data.scheduled_at}</span></div>
          <div class="modal-row"><span class="modal-row-label">Status</span><span class="modal-row-value">${data.status_label}</span></div>
          <div class="modal-row"><span class="modal-row-label">DP</span><span class="modal-row-value">Rp ${data.dp_amount}</span></div>
          ${data.midtrans_order ? `<div class="modal-row"><span class="modal-row-label">Order ID</span><span class="modal-row-value" style="font-size:11px">${data.midtrans_order}</span></div>` : ''}
        `;
      })
      .catch(() => {
        content.innerHTML = '<div style="text-align:center;padding:24px;color:var(--red-500)">Gagal memuat detail.</div>';
      });
  }

  function closeDetailModal() {
    document.getElementById('detailModal').classList.remove('open');
  }
</script>
@endsection
