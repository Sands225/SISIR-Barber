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
  <div class="avatar-btn">
    <div class="avatar-fallback">A</div>
  </div>
</div>

<!-- Scrollable content -->
<div class="page-scroll">
  <div class="dash-scroll-content">

    <!-- Greeting -->
    <div class="greeting-section anim-fade-up">
      <div class="greeting-date" id="greetDate">Senin, 14 Oktober 2023</div>
      <div class="greeting-name">Halo, Admin! 👋</div>
    </div>

    <!-- Antrean card -->
    <div class="antrean-card anim-slide delay-1">
      <div>
        <div class="antrean-label">Antrean Hari Ini</div>
        <div class="antrean-count">8 Booking Online</div>
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
      <span class="section-title">Daftar Booking</span>
      <a href="{{ route('sisir.booking') }}" class="section-link">Lihat Semua</a>
    </div>

    <!-- Booking: Selesai -->
    <div class="booking-card anim-slide delay-2">
      <div class="booking-card-top">
        <div class="customer-avatar">R</div>
        <div class="customer-info">
          <div class="customer-name">Rizky Ramadhan</div>
          <div class="customer-meta">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
              <circle cx="6" cy="6" r="5" stroke="#9ca3af" stroke-width="1.5"/>
              <path d="M6 3v3l2 1" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            10:00 – Haircut + Wash
          </div>
        </div>
        <span class="status-badge badge-selesai">SELESAI</span>
      </div>
      <div class="booking-actions">
        <button class="btn-wa" onclick="showToast('Membuka WhatsApp...')">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M8 1.5C4.41 1.5 1.5 4.41 1.5 8c0 1.19.33 2.3.9 3.25L1.5 14.5l3.36-.88A6.46 6.46 0 0 0 8 14.5c3.59 0 6.5-2.91 6.5-6.5S11.59 1.5 8 1.5z" stroke="white" stroke-width="1.5"/>
          </svg>
          WhatsApp
        </button>
        <button class="btn-detail" onclick="showToast('Detail Rizky Ramadhan')">Detail</button>
      </div>
    </div>

    <!-- Booking: Batal -->
    <div class="booking-card anim-slide delay-3">
      <div class="booking-card-top">
        <div class="customer-avatar">R</div>
        <div class="customer-info">
          <div class="customer-name">Rizky Ramadhan</div>
          <div class="customer-meta">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
              <circle cx="6" cy="6" r="5" stroke="#9ca3af" stroke-width="1.5"/>
              <path d="M6 3v3l2 1" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            10:00 – Haircut + Wash
          </div>
        </div>
        <span class="status-badge badge-batal">BATAL</span>
      </div>
      <div class="booking-actions">
        <button class="btn-wa" onclick="showToast('Membuka WhatsApp...')">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M8 1.5C4.41 1.5 1.5 4.41 1.5 8c0 1.19.33 2.3.9 3.25L1.5 14.5l3.36-.88A6.46 6.46 0 0 0 8 14.5c3.59 0 6.5-2.91 6.5-6.5S11.59 1.5 8 1.5z" stroke="white" stroke-width="1.5"/>
          </svg>
          WhatsApp
        </button>
        <button class="btn-detail" onclick="showToast('Detail Rizky Ramadhan')">Detail</button>
      </div>
    </div>

    <!-- Booking: Sudah DP -->
    <div class="booking-card anim-slide delay-4">
      <div class="booking-card-top">
        <div class="customer-avatar">R</div>
        <div class="customer-info">
          <div class="customer-name">Rizky Ramadhan</div>
          <div class="customer-meta">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
              <circle cx="6" cy="6" r="5" stroke="#9ca3af" stroke-width="1.5"/>
              <path d="M6 3v3l2 1" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            10:00 – Haircut + Wash
          </div>
        </div>
        <span class="status-badge badge-sudahdp">SUDAH DP</span>
      </div>
      <div class="booking-actions">
        <button class="btn-wa" onclick="showToast('Membuka WhatsApp...')">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M8 1.5C4.41 1.5 1.5 4.41 1.5 8c0 1.19.33 2.3.9 3.25L1.5 14.5l3.36-.88A6.46 6.46 0 0 0 8 14.5c3.59 0 6.5-2.91 6.5-6.5S11.59 1.5 8 1.5z" stroke="white" stroke-width="1.5"/>
          </svg>
          WhatsApp
        </button>
        <button class="btn-detail" onclick="showToast('Detail Rizky Ramadhan')">Detail</button>
      </div>
    </div>

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
</script>
@endsection
