@extends('layouts.sisir')

@section('title', 'SISIR')
@section('meta_description', 'SISIR – Sistem Informasi Salon dan Barbershop.')

@section('extra_styles')
<style>
  body, .sisir-shell { background: var(--green-700); }

  .splash-screen {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    min-height: 100vh;
    background: var(--green-700);
  }

  /* Decorative elements */
  .splash-deco {
    position: absolute;
    opacity: .18;
    pointer-events: none;
  }
  .splash-deco.top-right  { top: 60px; right: -20px; }
  .splash-deco.bottom-left { bottom: 80px; left: -20px; }

  /* Logo group */
  .splash-logo-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 24px;
    animation: splashPop .7s cubic-bezier(.34,1.56,.64,1) .2s both;
  }
  @keyframes splashPop {
    from { opacity: 0; transform: scale(.65); }
    to   { opacity: 1; transform: scale(1); }
  }

  .splash-icon-box {
    width: 110px;
    height: 110px;
    background: var(--white);
    border-radius: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 24px 48px rgba(0,0,0,.28);
  }

  .splash-brand-name {
    font-size: 30px;
    font-weight: 800;
    color: var(--white);
    letter-spacing: 8px;
    text-transform: uppercase;
  }

  .splash-tagline {
    font-size: 13px;
    color: rgba(255,255,255,.65);
    letter-spacing: 1px;
    margin-top: -18px;
    font-weight: 400;
  }
</style>
@endsection

@section('content')
<div class="splash-screen" id="splashScreen">
  <!-- Deco: grid top-right -->
  <div class="splash-deco top-right">
    <svg width="110" height="80" viewBox="0 0 110 80" fill="none">
      @foreach(range(0,4) as $col)
        @foreach(range(0,3) as $row)
          <rect x="{{ $col * 20 + 2 }}" y="{{ $row * 20 + 2 }}" width="14" height="14" rx="2"
                stroke="white" stroke-width="2.5" fill="none"/>
        @endforeach
      @endforeach
    </svg>
  </div>

  <!-- Deco: calendar bottom-left -->
  <div class="splash-deco bottom-left">
    <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
      <rect x="4" y="14" width="72" height="62" rx="10" stroke="white" stroke-width="3" fill="none"/>
      <line x1="4" y1="34" x2="76" y2="34" stroke="white" stroke-width="3"/>
      <line x1="22" y1="4" x2="22" y2="26" stroke="white" stroke-width="3" stroke-linecap="round"/>
      <line x1="58" y1="4" x2="58" y2="26" stroke="white" stroke-width="3" stroke-linecap="round"/>
      <circle cx="24" cy="50" r="4" fill="white"/>
      <circle cx="40" cy="50" r="4" fill="white"/>
      <circle cx="56" cy="50" r="4" fill="white"/>
      <circle cx="24" cy="64" r="4" fill="white"/>
      <circle cx="40" cy="64" r="4" fill="white"/>
    </svg>
  </div>

  <!-- Logo -->
  <div class="splash-logo-wrap">
    <div class="splash-icon-box">
      <img src="{{ asset('ico-sisir.ico') }}" width="72" height="72" alt="SISIR Logo" style="border-radius:16px;" />
    </div>
    <div class="splash-brand-name">SISIR</div>
    <div class="splash-tagline">Reclaim Your Time, Recover Your Revenue</div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  // Auto-redirect after 2.5s
  setTimeout(function () {
    window.location.href = "{{ route('sisir.login') }}";
  }, 2500);
</script>
@endsection
