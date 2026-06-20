@extends('layouts.sisir')

@section('title', 'Laporan Penghasilan – SISIR')
@section('meta_description', 'Pantau pendapatan dan transaksi booking SISIR Barber secara real-time.')

@section('extra_styles')
<style>
  body, .sisir-shell { background: var(--green-bg); }

  /* ── Page Header ── */
  .page-header {
    padding: 4px 20px 20px;
  }
  .page-header-label {
    font-size: 12px; color: var(--gray-500);
    font-weight: 500; margin-bottom: 2px;
  }
  .page-header-title {
    font-size: 26px; font-weight: 800;
    color: var(--gray-900); line-height: 1.2;
  }

  /* ── Period Filter ── */
  .period-filter {
    display: flex; gap: 8px;
    padding: 0 20px 20px;
    overflow-x: auto; scrollbar-width: none;
  }
  .period-filter::-webkit-scrollbar { display: none; }
  .period-btn {
    flex-shrink: 0;
    padding: 8px 16px;
    border-radius: 99px;
    font-size: 13px; font-weight: 600;
    border: 1.5px solid var(--gray-200);
    background: var(--white); color: var(--gray-600);
    cursor: pointer; text-decoration: none;
    transition: all var(--trans);
  }
  .period-btn.active {
    background: var(--green-600);
    border-color: var(--green-600);
    color: white;
  }
  .period-btn:hover:not(.active) {
    background: var(--gray-50);
    border-color: var(--gray-300);
  }

  /* ── Hero Revenue Card ── */
  .hero-card {
    margin: 0 20px 16px;
    background: linear-gradient(135deg, #1e7c3a 0%, #208a40 60%, #27a34b 100%);
    border-radius: var(--radius-lg);
    padding: 24px;
    color: white;
    position: relative; overflow: hidden;
    box-shadow: 0 8px 24px rgba(30,124,58,.35);
  }
  .hero-card::before {
    content: '';
    position: absolute; top: -40px; right: -40px;
    width: 160px; height: 160px;
    background: rgba(255,255,255,.07);
    border-radius: 50%;
  }
  .hero-card::after {
    content: '';
    position: absolute; bottom: -30px; right: 40px;
    width: 100px; height: 100px;
    background: rgba(255,255,255,.05);
    border-radius: 50%;
  }
  .hero-label {
    font-size: 11px; font-weight: 700;
    letter-spacing: 1.5px; text-transform: uppercase;
    opacity: .75; margin-bottom: 8px;
  }
  .hero-amount {
    font-size: 36px; font-weight: 900;
    letter-spacing: -1px; line-height: 1;
    margin-bottom: 6px;
  }
  .hero-sub {
    font-size: 13px; opacity: .75;
  }
  .hero-badge {
    display: inline-flex; align-items: center; gap: 4px;
    background: rgba(255,255,255,.15);
    border-radius: 99px;
    padding: 4px 10px;
    font-size: 12px; font-weight: 600;
    margin-top: 12px;
  }

  /* ── Stats Grid ── */
  .stats-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 12px; margin: 0 20px 20px;
  }
  .stat-card {
    background: var(--white);
    border-radius: var(--radius-lg);
    padding: 16px;
    box-shadow: var(--shadow-sm);
  }
  .stat-card-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 10px;
  }
  .icon-blue  { background: #eff6ff; }
  .icon-amber { background: #fffbeb; }
  .icon-purple { background: #f5f3ff; }
  .stat-card-value {
    font-size: 20px; font-weight: 800;
    color: var(--gray-900); line-height: 1;
    margin-bottom: 4px;
  }
  .stat-card-label {
    font-size: 11px; color: var(--gray-500);
    font-weight: 500;
  }

  /* ── Section Card ── */
  .section-card {
    margin: 0 20px 16px;
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
  }
  .section-card-header {
    padding: 16px 16px 12px;
    border-bottom: 1px solid var(--gray-100);
    display: flex; align-items: center;
    justify-content: space-between;
  }
  .section-card-title {
    font-size: 15px; font-weight: 800;
    color: var(--gray-900);
  }

  /* ── Chart ── */
  .chart-container {
    padding: 16px;
    height: 180px;
  }

  /* ── Bar Chart ── */
  .bar-list { padding: 8px 16px 16px; }
  .bar-item { margin-bottom: 14px; }
  .bar-item:last-child { margin-bottom: 0; }
  .bar-item-header {
    display: flex; justify-content: space-between;
    margin-bottom: 6px;
  }
  .bar-item-name {
    font-size: 13px; font-weight: 600;
    color: var(--gray-700);
  }
  .bar-item-value {
    font-size: 13px; font-weight: 700;
    color: var(--gray-900);
  }
  .bar-track {
    height: 8px; background: var(--gray-100);
    border-radius: 99px; overflow: hidden;
  }
  .bar-fill {
    height: 100%; border-radius: 99px;
    background: linear-gradient(90deg, #1e7c3a, #27a34b);
    transition: width .6s cubic-bezier(.34,1.56,.64,1);
  }
  .bar-fill.bar-blue {
    background: linear-gradient(90deg, #1d4ed8, #3b82f6);
  }

  /* ── Transaction List ── */
  .tx-list { padding: 0 16px 16px; }
  .tx-item {
    display: flex; align-items: center;
    gap: 12px; padding: 12px 0;
    border-bottom: 1px solid var(--gray-50);
  }
  .tx-item:last-child { border: none; }
  .tx-avatar {
    width: 38px; height: 38px;
    background: var(--green-100);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 800;
    color: var(--green-700); flex-shrink: 0;
  }
  .tx-info { flex: 1; min-width: 0; }
  .tx-name {
    font-size: 13px; font-weight: 700;
    color: var(--gray-900); margin-bottom: 2px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  }
  .tx-meta {
    font-size: 11px; color: var(--gray-500);
  }
  .tx-amount {
    font-size: 14px; font-weight: 800;
    color: var(--green-700); flex-shrink: 0;
  }

  /* ── Empty State ── */
  .empty-state {
    text-align: center; padding: 32px 20px;
    color: var(--gray-400);
  }

  /* ── Date Range ── */
  .date-range-badge {
    font-size: 11px; color: var(--gray-500);
    font-weight: 500;
    background: var(--gray-100);
    padding: 3px 8px; border-radius: 6px;
  }

  .rev-scroll { padding-bottom: 100px; }
</style>
@endsection

@section('content')
<!-- App Header -->
<div class="app-header">
  <a href="{{ route('sisir.splash') }}" class="brand">
    <img src="{{ asset('ico-sisir.ico') }}" width="28" height="28" alt="SISIR" style="border-radius:6px;" />
    <span class="brand-name">SISIR</span>
  </a>
  <div class="avatar-btn">
    <div class="avatar-fallback">{{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'A' }}</div>
  </div>
</div>

<div class="page-scroll">
  <div class="rev-scroll">

    <!-- Page Header -->
    <div class="page-header anim-fade-up">
      <div class="page-header-label" id="dateRangeLabel">
        {{ $startDate->translatedFormat('d M') }} – {{ $endDate->translatedFormat('d M Y') }}
      </div>
      <div class="page-header-title">Laporan Penghasilan</div>
    </div>

    <!-- Period Filter -->
    <div class="period-filter">
      @foreach(['today' => 'Hari Ini', 'week' => 'Minggu Ini', 'month' => 'Bulan Ini', 'year' => 'Tahun Ini'] as $key => $label)
        <a href="{{ route('sisir.revenue', ['period' => $key]) }}"
           class="period-btn {{ $period === $key ? 'active' : '' }}">
          {{ $label }}
        </a>
      @endforeach
    </div>

    <!-- Hero Revenue Card -->
    <div class="hero-card anim-fade-up delay-1">
      <div class="hero-label">Penghasilan — {{ $periodLabels[$period] }}</div>
      <div class="hero-amount">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
      <div class="hero-sub">Estimasi total dari {{ $totalTransactions }} transaksi berbayar</div>

      <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap">
        <div class="hero-badge">
          <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
            <circle cx="6" cy="6" r="5" stroke="white" stroke-width="1.2"/>
            <path d="M6 3.5v2.5l1.5 1" stroke="white" stroke-width="1.2" stroke-linecap="round"/>
          </svg>
          DP Masuk: Rp {{ number_format($totalRevenue, 0, ',', '.') }}
        </div>
        @if($fullServiceRevenue > 0)
        <div class="hero-badge">
          <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
            <path d="M2 6l3 3 5-5" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Layanan Selesai: Rp {{ number_format($fullServiceRevenue, 0, ',', '.') }}
        </div>
        @endif
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid anim-fade-up delay-2" style="grid-template-columns:1fr 1fr">
      <div class="stat-card" style="grid-column:span 2;background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1.5px solid #bbf7d0">
        <div style="display:flex;align-items:center;justify-content:space-between">
          <div>
            <div class="stat-card-label" style="color:#15803d;margin-bottom:4px">Grand Total (DP + Sisa Layanan Selesai)</div>
            <div class="stat-card-value" style="font-size:22px;color:#15803d">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
          </div>
          <div class="stat-card-icon" style="background:#bbf7d0;width:44px;height:44px">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
              <path d="M11 2v18M15.5 5H9a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6H5.5" stroke="#15803d" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
        </div>
        <div style="display:flex;gap:16px;margin-top:10px;flex-wrap:wrap">
          <div style="font-size:12px;color:#166534">
            DP Terkumpul: <strong>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong>
          </div>
          <div style="font-size:12px;color:#166534">
            Nilai Layanan Selesai: <strong>Rp {{ number_format($fullServiceRevenue, 0, ',', '.') }}</strong>
          </div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-card-icon icon-blue">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
            <path d="M10 2a8 8 0 1 0 0 16A8 8 0 0 0 10 2zm0 3v5l3 2" stroke="#1d4ed8" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
        </div>
        <div class="stat-card-value">{{ $totalTransactions }}</div>
        <div class="stat-card-label">Total Transaksi</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-icon icon-amber">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
            <circle cx="10" cy="10" r="8" stroke="#d97706" stroke-width="1.5"/>
            <path d="M10 6v4l2.5 2.5" stroke="#d97706" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
        </div>
        <div class="stat-card-value" style="font-size:16px">Rp {{ number_format($avgPerTransaction, 0, ',', '.') }}</div>
        <div class="stat-card-label">Rata-rata per Booking</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-icon" style="background:#dcfce7">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
            <path d="M4 14l4-4 3 3 5-6" stroke="#16a34a" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="stat-card-value">{{ $countCompleted }}</div>
        <div class="stat-card-label">Booking Selesai</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-icon icon-purple">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
            <rect x="3" y="5" width="14" height="12" rx="2" stroke="#7c3aed" stroke-width="1.5"/>
            <path d="M3 9h14M7 5V3M13 5V3" stroke="#7c3aed" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
        </div>
        <div class="stat-card-value">{{ $countCancelled }}</div>
        <div class="stat-card-label">Batal / No Show</div>
      </div>
    </div>

    <!-- Chart Pendapatan -->
    <div class="section-card anim-fade-up delay-2">
      <div class="section-card-header">
        <span class="section-card-title">📈 Grafik Pendapatan</span>
        <span class="date-range-badge">{{ $period === 'year' ? '12 Bulan' : count($chartLabels) . ' Hari' }}</span>
      </div>
      <div class="chart-container">
        <canvas id="revenueChart"></canvas>
      </div>
    </div>

    <!-- Revenue by Service -->
    @if($revenueByService->isNotEmpty())
    <div class="section-card anim-fade-up delay-3">
      <div class="section-card-header">
        <span class="section-card-title">✂️ Penghasilan per Layanan</span>
      </div>
      <div class="bar-list">
        @php $maxService = $revenueByService->max('total') ?: 1; @endphp
        @foreach($revenueByService as $item)
          <div class="bar-item">
            <div class="bar-item-header">
              <span class="bar-item-name">{{ $item->service->name ?? '–' }} <span style="font-size:11px;color:var(--gray-400);font-weight:500">({{ $item->count }}x)</span></span>
              <span class="bar-item-value">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
            </div>
            <div class="bar-track">
              <div class="bar-fill" style="width: {{ round(($item->total / $maxService) * 100) }}%"></div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
    @endif

    <!-- Revenue by Barber -->
    @if($revenueByBarber->isNotEmpty())
    <div class="section-card anim-fade-up delay-3">
      <div class="section-card-header">
        <span class="section-card-title">💈 Penghasilan per Kapster</span>
      </div>
      <div class="bar-list">
        @php $maxBarber = $revenueByBarber->max('total') ?: 1; @endphp
        @foreach($revenueByBarber as $item)
          <div class="bar-item">
            <div class="bar-item-header">
              <span class="bar-item-name">{{ $item->barber->displayName() ?? '–' }} <span style="font-size:11px;color:var(--gray-400);font-weight:500">({{ $item->count }}x)</span></span>
              <span class="bar-item-value">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
            </div>
            <div class="bar-track">
              <div class="bar-fill bar-blue" style="width: {{ round(($item->total / $maxBarber) * 100) }}%"></div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
    @endif

    <!-- Riwayat Transaksi -->
    <div class="section-card anim-fade-up delay-4">
      <div class="section-card-header">
        <span class="section-card-title">📋 Riwayat Transaksi</span>
        <span class="date-range-badge">{{ $recentTransactions->count() }} data</span>
      </div>
      @if($recentTransactions->isEmpty())
        <div class="empty-state">
          <svg width="48" height="48" viewBox="0 0 48 48" fill="none" style="margin:0 auto 12px;display:block;opacity:.35">
            <circle cx="24" cy="24" r="22" stroke="#9ca3af" stroke-width="2"/>
            <path d="M16 24h16M24 16v16" stroke="#9ca3af" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <p style="font-size:14px">Belum ada transaksi di periode ini.</p>
        </div>
      @else
        <div class="tx-list">
          @foreach($recentTransactions as $tx)
            <div class="tx-item">
              <div class="tx-avatar">{{ strtoupper(substr($tx->customer->name ?? 'U', 0, 1)) }}</div>
              <div class="tx-info">
                <div class="tx-name">{{ $tx->customer->name ?? '–' }}</div>
                <div class="tx-meta">
                  {{ $tx->service->name ?? '–' }}
                  · {{ $tx->scheduled_at->timezone('Asia/Jakarta')->format('d M, H:i') }}
                  @if($tx->barber)
                    · {{ $tx->barber->displayName() }}
                  @endif
                </div>
              </div>
              <div class="tx-amount">+Rp {{ number_format($tx->dp_amount, 0, ',', '.') }}</div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

  </div>
</div>


@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
  const labels  = @json($chartLabels);
  const data    = @json($chartData);
  const maxVal  = Math.max(...data, 1);

  const ctx = document.getElementById('revenueChart').getContext('2d');

  const gradient = ctx.createLinearGradient(0, 0, 0, 180);
  gradient.addColorStop(0, 'rgba(30,124,58,.35)');
  gradient.addColorStop(1, 'rgba(30,124,58,.02)');

  new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Pendapatan (Rp)',
        data,
        fill: true,
        backgroundColor: gradient,
        borderColor: '#1e7c3a',
        borderWidth: 2.5,
        pointRadius: data.length <= 14 ? 4 : 2,
        pointBackgroundColor: '#1e7c3a',
        tension: 0.4,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => 'Rp ' + ctx.raw.toLocaleString('id-ID'),
          },
          backgroundColor: '#1a1a2e',
          padding: 10,
          cornerRadius: 8,
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { size: 11 }, color: '#9ca3af' }
        },
        y: {
          grid: { color: 'rgba(0,0,0,.05)' },
          ticks: {
            font: { size: 11 }, color: '#9ca3af',
            callback: v => 'Rp ' + (v >= 1000 ? (v/1000).toFixed(0) + 'k' : v),
          }
        }
      }
    }
  });
</script>
@endsection
