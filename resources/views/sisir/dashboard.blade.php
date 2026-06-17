@extends('layouts.sisir')

@section('title', 'Dashboard – SISIR')
@section('meta_description', 'Kelola antrean booking barbershop Anda hari ini.')

@section('extra_styles')
<style>
  body, .sisir-shell { background: var(--green-bg); }

  /* ── Greeting ── */
  .greeting-section {
    padding: 4px 20px 16px;
  }
  .greeting-date {
    font-size: 12px; color: var(--gray-500);
    font-weight: 500; margin-bottom: 2px;
  }
  .greeting-name {
    font-size: 28px; font-weight: 800;
    color: var(--gray-900); line-height: 1.15;
  }

  /* ── Antrean card ── */
  .antrean-card {
    margin: 0 20px 24px;
    background: var(--white);
    border-radius: var(--radius-lg);
    padding: 20px 20px 20px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: var(--shadow-md);
    border-left: 4px solid var(--green-600);
  }
  .antrean-label {
    font-size: 10px; font-weight: 700;
    color: var(--gray-400); letter-spacing: 1.5px;
    text-transform: uppercase; margin-bottom: 6px;
  }
  .antrean-count {
    font-size: 28px; font-weight: 800; color: var(--gray-900);
  }
  .antrean-icon-box {
    width: 46px; height: 46px;
    background: var(--green-50);
    border-radius: var(--radius-md);
    display: flex; align-items: center; justify-content: center;
  }

  /* ── Section header ── */
  .section-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 20px; margin-bottom: 14px;
  }
  .section-title {
    font-size: 18px; font-weight: 800; color: var(--gray-900);
  }
  .section-link {
    font-size: 13px; font-weight: 600; color: var(--green-600);
    text-decoration: none;
    transition: color var(--trans);
  }
  .section-link:hover { color: var(--green-700); }

  /* ── Booking card ── */
  .booking-card {
    margin: 0 20px 12px;
    background: var(--white);
    border-radius: var(--radius-lg);
    padding: 16px;
    box-shadow: var(--shadow-sm);
  }
  .booking-card-top {
    display: flex; align-items: center;
    gap: 12px; margin-bottom: 14px;
  }
  .customer-avatar {
    width: 40px; height: 40px;
    background: var(--green-100);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; font-weight: 800;
    color: var(--green-700); flex-shrink: 0;
  }
  .customer-info { flex: 1; min-width: 0; }
  .customer-name {
    font-size: 14px; font-weight: 700;
    color: var(--gray-900); margin-bottom: 2px;
  }
  .customer-meta {
    font-size: 12px; color: var(--gray-500);
    display: flex; align-items: center; gap: 4px;
  }

  /* ── Action buttons ── */
  .booking-actions {
    display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
  }
  .btn-wa {
    height: 42px; background: var(--green-600); color: var(--white);
    border: none; border-radius: var(--radius-md);
    font-family: var(--font); font-size: 13px; font-weight: 700;
    cursor: pointer; display: flex; align-items: center;
    justify-content: center; gap: 6px;
    transition: background var(--trans), transform var(--trans);
    text-decoration: none;
  }
  .btn-wa:hover { background: var(--green-500); }
  .btn-wa:active { transform: scale(.97); }
  .btn-detail {
    height: 42px; background: transparent; color: var(--gray-700);
    border: 1.5px solid var(--gray-200); border-radius: var(--radius-md);
    font-family: var(--font); font-size: 13px; font-weight: 700;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: background var(--trans), border-color var(--trans), transform var(--trans);
    text-decoration: none;
  }
  .btn-detail:hover { background: var(--gray-50); border-color: var(--gray-300); }
  .btn-detail:active { transform: scale(.97); }

  /* Empty state */
  .empty-state {
    text-align: center; padding: 40px 20px;
    color: var(--gray-400);
  }
  .empty-state svg { margin: 0 auto 12px; display: block; opacity: .4; }

  /* QR modal */
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

  /* Padding for scroll under nav */
  .dash-scroll-content { padding-bottom: 24px; }
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

<!-- Scrollable content -->
<div class="page-scroll">
  <div class="dash-scroll-content">

    <!-- Greeting -->
    <div class="greeting-section anim-fade-up">
      <div class="greeting-date" id="greetDate">–</div>
      <div class="greeting-name">
        Halo, {{ auth()->check() ? explode(' ', auth()->user()->name)[0] : 'Admin' }}! 👋
      </div>
    </div>

    <!-- Antrean card — real count from DB -->
    <div class="antrean-card anim-slide delay-1">
      <div>
        <div class="antrean-label">Antrean Hari Ini</div>
        <div class="antrean-count">{{ $todayTotal }} Booking Online</div>
      </div>
      <div class="antrean-icon-box">
        <svg width="26" height="26" viewBox="0 0 26 26" fill="none">
          <rect x="2" y="6" width="22" height="18" rx="4" fill="none" stroke="#1e7c3a" stroke-width="2"/>
          <line x1="2" y1="12" x2="24" y2="12" stroke="#1e7c3a" stroke-width="2"/>
          <line x1="8" y1="2" x2="8" y2="10" stroke="#1e7c3a" stroke-width="2" stroke-linecap="round"/>
          <line x1="18" y1="2" x2="18" y2="10" stroke="#1e7c3a" stroke-width="2" stroke-linecap="round"/>
          <circle cx="8" cy="18" r="1.5" fill="#1e7c3a"/>
          <circle cx="13" cy="18" r="1.5" fill="#1e7c3a"/>
          <circle cx="18" cy="18" r="1.5" fill="#1e7c3a"/>
        </svg>
      </div>
    </div>

    <!-- Daftar Booking header -->
    <div class="section-header anim-fade-up delay-2">
      <span class="section-title">Booking Saya</span>
      <a href="{{ route('sisir.booking') }}" class="section-link">Lihat Semua</a>
    </div>

    {{-- Real Bookings from DB --}}
    @forelse ($recentBookings as $booking)
      <div class="booking-card anim-slide delay-{{ min($loop->iteration + 1, 4) }}">
        <div class="booking-card-top">
          <div class="customer-avatar">
            {{ strtoupper(substr($booking->customer->name ?? 'U', 0, 1)) }}
          </div>
          <div class="customer-info">
            <div class="customer-name">{{ $booking->customer->name }}</div>
            <div class="customer-meta">
              <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                <circle cx="6" cy="6" r="5" stroke="#9ca3af" stroke-width="1.5"/>
                <path d="M6 3v3l2 1" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
              {{ $booking->scheduled_at->timezone('Asia/Jakarta')->format('H:i') }}
              – {{ $booking->service->name ?? '–' }}
              @if($booking->barber)
                · {{ $booking->barber->displayName() }}
              @endif
            </div>
          </div>
          <span class="status-badge {{ $booking->status->badgeClass() }}">
            {{ $booking->status->shortLabel() }}
          </span>
        </div>
        <div class="booking-actions">
          <a href="https://wa.me/{{ $booking->customer->phone ?? '' }}"
             target="_blank" class="btn-wa">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
              <path d="M8 1.5C4.41 1.5 1.5 4.41 1.5 8c0 1.19.33 2.3.9 3.25L1.5 14.5l3.36-.88A6.46 6.46 0 0 0 8 14.5c3.59 0 6.5-2.91 6.5-6.5S11.59 1.5 8 1.5z" stroke="white" stroke-width="1.5"/>
            </svg>
            WhatsApp
          </a>
          <button class="btn-detail" onclick="openDetail({{ $booking->id }})">Detail</button>
        </div>
      </div>
    @empty
      <div class="empty-state anim-fade-up delay-2">
        <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
          <circle cx="24" cy="24" r="22" stroke="#9ca3af" stroke-width="2"/>
          <path d="M16 24h16M24 16v16" stroke="#9ca3af" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <p style="font-size:14px;margin-top:8px">Belum ada booking.</p>
        <a href="{{ route('sisir.booking.create') }}" style="
          display:inline-block;margin-top:12px;padding:10px 20px;
          background:var(--green-600);color:white;border-radius:var(--radius-md);
          font-weight:700;font-size:13px;text-decoration:none;
        ">Buat Booking Sekarang</a>
      </div>
    @endforelse

  </div>
</div>

<!-- Bottom Nav -->
<nav class="bottom-nav">
  <a href="{{ route('sisir.dashboard') }}" class="nav-item active" id="nav-home">
    <svg viewBox="0 0 24 24" fill="none">
      <rect x="3" y="3" width="8" height="8" rx="2" stroke="#208a40" stroke-width="2"/>
      <rect x="13" y="3" width="8" height="8" rx="2" stroke="#208a40" stroke-width="2"/>
      <rect x="3" y="13" width="8" height="8" rx="2" stroke="#208a40" stroke-width="2"/>
      <rect x="13" y="13" width="8" height="8" rx="2" stroke="#208a40" stroke-width="2"/>
    </svg>
    <span style="color:var(--green-600)">Dashboard</span>
  </a>
  <a href="{{ route('sisir.booking') }}" class="nav-item">
    <svg viewBox="0 0 24 24" fill="none">
      <rect x="3" y="5" width="18" height="16" rx="3" stroke="#9ca3af" stroke-width="2"/>
      <line x1="3" y1="10" x2="21" y2="10" stroke="#9ca3af" stroke-width="2"/>
      <line x1="8" y1="2" x2="8" y2="8" stroke="#9ca3af" stroke-width="2" stroke-linecap="round"/>
      <line x1="16" y1="2" x2="16" y2="8" stroke="#9ca3af" stroke-width="2" stroke-linecap="round"/>
    </svg>
    <span>Booking</span>
  </a>
  <a href="{{ route('sisir.promo') }}" class="nav-item">
    <svg viewBox="0 0 24 24" fill="none">
      <path d="M12 2L14.09 8.26L21 9.27L16 14.14L17.18 21L12 18.27L6.82 21L8 14.14L3 9.27L9.91 8.26L12 2Z" stroke="#9ca3af" stroke-width="2" stroke-linejoin="round"/>
    </svg>
    <span>Promo</span>
  </a>
</nav>

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
