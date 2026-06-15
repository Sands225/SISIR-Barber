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

  /* No results */
  .no-results {
    text-align: center; padding: 40px 20px;
    color: var(--gray-400); font-size: 14px; display: none;
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
  <div class="avatar-btn"><div class="avatar-fallback">A</div></div>
</div>

<!-- Page title -->
<div class="bl-page-header anim-fade-up">
  <h1 class="bl-title">Daftar Booking</h1>
  <p class="bl-subtitle">Kelola antrean pelanggan Anda hari ini.</p>
</div>

<!-- Search -->
<div class="search-wrap anim-fade-up delay-1">
  <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
    <circle cx="8" cy="8" r="6" stroke="#9ca3af" stroke-width="2"/>
    <line x1="13" y1="13" x2="17" y2="17" stroke="#9ca3af" stroke-width="2" stroke-linecap="round"/>
  </svg>
  <input class="search-input" id="searchInput" type="search" placeholder="Cari nama pelanggan..." autocomplete="off" oninput="applyFilters()" />
</div>

<!-- Filter tabs -->
<div class="filter-tabs anim-fade-up delay-1" id="filterTabs">
  <button class="filter-tab active" data-filter="Semua"    onclick="setFilter(this)">Semua</button>
  <button class="filter-tab"        data-filter="Sudah DP" onclick="setFilter(this)">Sudah DP</button>
  <button class="filter-tab"        data-filter="Selesai"  onclick="setFilter(this)">Selesai</button>
  <button class="filter-tab"        data-filter="Batal"    onclick="setFilter(this)">Batal</button>
</div>

<!-- Booking list -->
<div class="page-scroll">
  <div class="bli-list" id="bookingList">

    {{-- Item 1 --}}
    <div class="booking-list-item anim-slide delay-1" data-status="Sudah DP" data-name="adit saputra">
      <div class="bli-top">
        <div class="bli-avatar">AS</div>
        <div style="flex:1;min-width:0">
          <div class="bli-name">Adit Saputra</div>
          <div class="bli-meta">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><circle cx="6" cy="6" r="5" stroke="#9ca3af" stroke-width="1.5"/><path d="M6 3v3l2 1" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round"/></svg>
            10:00 – 10:45
          </div>
        </div>
        <span class="status-badge badge-sudahdp">SUDAH DP</span>
      </div>
      <div class="bli-bottom">
        <span class="bli-payment dp">
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="3" width="12" height="8" rx="2" stroke="#e87b2b" stroke-width="1.5"/><line x1="1" y1="6" x2="13" y2="6" stroke="#e87b2b" stroke-width="1.5"/></svg>
          Sudah Bayar DP
        </span>
        <button class="bli-detail-btn" onclick="showToast('Detail Adit Saputra')">
          Detail <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M5 3l4 4-4 4" stroke="#208a40" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </div>
    </div>

    {{-- Item 2 --}}
    <div class="booking-list-item anim-slide delay-2" data-status="Sudah DP" data-name="adit saputra">
      <div class="bli-top">
        <div class="bli-avatar">AS</div>
        <div style="flex:1;min-width:0">
          <div class="bli-name">Adit Saputra</div>
          <div class="bli-meta">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><circle cx="6" cy="6" r="5" stroke="#9ca3af" stroke-width="1.5"/><path d="M6 3v3l2 1" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round"/></svg>
            10:00 – 10:45
          </div>
        </div>
        <span class="status-badge badge-sudahdp">SUDAH DP</span>
      </div>
      <div class="bli-bottom">
        <span class="bli-payment dp">
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="3" width="12" height="8" rx="2" stroke="#e87b2b" stroke-width="1.5"/><line x1="1" y1="6" x2="13" y2="6" stroke="#e87b2b" stroke-width="1.5"/></svg>
          Sudah Bayar DP
        </span>
        <button class="bli-detail-btn" onclick="showToast('Detail Adit Saputra')">
          Detail <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M5 3l4 4-4 4" stroke="#208a40" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </div>
    </div>

    {{-- Item 3 --}}
    <div class="booking-list-item anim-slide delay-3" data-status="Selesai" data-name="adit saputra">
      <div class="bli-top">
        <div class="bli-avatar">AS</div>
        <div style="flex:1;min-width:0">
          <div class="bli-name">Adit Saputra</div>
          <div class="bli-meta">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><circle cx="6" cy="6" r="5" stroke="#9ca3af" stroke-width="1.5"/><path d="M6 3v3l2 1" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round"/></svg>
            10:00 – 10:45
          </div>
        </div>
        <span class="status-badge badge-selesai">SELESAI</span>
      </div>
      <div class="bli-bottom">
        <span class="bli-payment lunas">
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><circle cx="7" cy="7" r="6" stroke="#1a6b32" stroke-width="1.5"/><path d="M4 7l2.5 2.5L10 5" stroke="#1a6b32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Lunas
        </span>
        <button class="bli-detail-btn" onclick="showToast('Detail Adit Saputra')">
          Detail <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M5 3l4 4-4 4" stroke="#208a40" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </div>
    </div>

    {{-- Item 4 --}}
    <div class="booking-list-item anim-slide delay-4" data-status="Batal" data-name="budi santoso">
      <div class="bli-top">
        <div class="bli-avatar" style="background:linear-gradient(135deg,#fca5a5,#ef4444);">BS</div>
        <div style="flex:1;min-width:0">
          <div class="bli-name">Budi Santoso</div>
          <div class="bli-meta">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><circle cx="6" cy="6" r="5" stroke="#9ca3af" stroke-width="1.5"/><path d="M6 3v3l2 1" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round"/></svg>
            11:00 – 11:30
          </div>
        </div>
        <span class="status-badge badge-batal">BATAL</span>
      </div>
      <div class="bli-bottom">
        <span class="bli-payment batal">
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><circle cx="7" cy="7" r="6" stroke="#d93025" stroke-width="1.5"/><path d="M5 5l4 4M9 5l-4 4" stroke="#d93025" stroke-width="1.5" stroke-linecap="round"/></svg>
          Booking Dibatalkan
        </span>
        <button class="bli-detail-btn" onclick="showToast('Detail Budi Santoso')">
          Detail <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M5 3l4 4-4 4" stroke="#208a40" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </div>
    </div>

    {{-- Item 5 --}}
    <div class="booking-list-item anim-slide" style="animation-delay:.45s" data-status="Selesai" data-name="cahyo prabowo">
      <div class="bli-top">
        <div class="bli-avatar" style="background:linear-gradient(135deg,#6ee7b7,#059669);">CP</div>
        <div style="flex:1;min-width:0">
          <div class="bli-name">Cahyo Prabowo</div>
          <div class="bli-meta">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><circle cx="6" cy="6" r="5" stroke="#9ca3af" stroke-width="1.5"/><path d="M6 3v3l2 1" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round"/></svg>
            12:00 – 12:45
          </div>
        </div>
        <span class="status-badge badge-selesai">SELESAI</span>
      </div>
      <div class="bli-bottom">
        <span class="bli-payment lunas">
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><circle cx="7" cy="7" r="6" stroke="#1a6b32" stroke-width="1.5"/><path d="M4 7l2.5 2.5L10 5" stroke="#1a6b32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Lunas
        </span>
        <button class="bli-detail-btn" onclick="showToast('Detail Cahyo Prabowo')">
          Detail <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M5 3l4 4-4 4" stroke="#208a40" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </div>
    </div>

    {{-- No results message --}}
    <div class="no-results" id="noResults">
      <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
        <circle cx="24" cy="24" r="22" stroke="#9ca3af" stroke-width="2"/>
        <path d="M16 24h16M24 16v16" stroke="#9ca3af" stroke-width="2" stroke-linecap="round"/>
      </svg>
      Tidak ada booking ditemukan
    </div>

  </div>{{-- /bli-list --}}
</div>

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
@endsection

@section('scripts')
<script>
  let activeFilter = 'Semua';

  function setFilter(el) {
    activeFilter = el.dataset.filter;
    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    applyFilters();
  }

  function applyFilters() {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    const items = document.querySelectorAll('#bookingList .booking-list-item');
    let visible = 0;
    items.forEach(item => {
      const name   = item.dataset.name;
      const status = item.dataset.status;
      const matchFilter = activeFilter === 'Semua' || status === activeFilter;
      const matchSearch = !q || name.includes(q);
      const show = matchFilter && matchSearch;
      item.style.display = show ? '' : 'none';
      if (show) visible++;
    });
    document.getElementById('noResults').style.display = visible === 0 ? 'block' : 'none';
  }
</script>
@endsection
