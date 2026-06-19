@extends('layouts.sisir')

@section('title', 'Promo – SISIR')
@section('meta_description', 'Kirim promo diskon ke pelanggan barbershop Anda via WhatsApp.')

@section('extra_styles')
<style>
  body, .sisir-shell { background: var(--green-bg); }

  /* ── Banner ── */
  .promo-banner {
    background: linear-gradient(135deg, var(--green-800) 0%, var(--green-500) 100%);
    border-radius: var(--radius-xl);
    padding: 24px 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 28px rgba(20,82,40,.38);
  }
  .promo-banner-deco {
    position: absolute; right: -30px; top: -30px;
    width: 140px; height: 140px;
    background: rgba(255,255,255,.08); border-radius: 50%;
    pointer-events: none;
  }
  .promo-banner-deco2 {
    position: absolute; right: 20px; bottom: -40px;
    width: 100px; height: 100px;
    background: rgba(255,255,255,.05); border-radius: 50%;
    pointer-events: none;
  }
  .promo-banner-title {
    font-size: 22px; font-weight: 800; color: var(--white);
    line-height: 1.2; margin-bottom: 8px; position: relative;
  }
  .promo-banner-desc {
    font-size: 13px; color: rgba(255,255,255,.8);
    line-height: 1.5; position: relative;
  }

  /* ── Section title ── */
  .promo-section-title {
    font-size: 20px; font-weight: 800;
    color: var(--gray-900); margin-bottom: 16px;
    display: flex; align-items: center; gap: 8px;
  }

  /* ── Input card ── */
  .input-card {
    background: var(--white); border-radius: var(--radius-lg);
    padding: 20px; margin-bottom: 20px; box-shadow: var(--shadow-sm);
  }
  .input-card-label {
    font-size: 13px; font-weight: 600; color: var(--gray-700); margin-bottom: 12px;
  }
  .promo-input-wrap {
    display: flex; align-items: center;
    border: 1.5px solid var(--gray-200);
    border-radius: var(--radius-md); overflow: hidden;
    transition: border-color var(--trans), box-shadow var(--trans);
  }
  .promo-input-wrap:focus-within {
    border-color: var(--green-500);
    box-shadow: 0 0 0 3px rgba(32,138,64,.12);
  }
  .promo-currency {
    padding: 0 14px; font-size: 14px; font-weight: 700;
    color: var(--gray-700); border-right: 1.5px solid var(--gray-200);
    height: 52px; display: flex; align-items: center;
    background: var(--gray-50); flex-shrink: 0;
  }
  .promo-amount-input {
    flex: 1; border: none; outline: none;
    padding: 0 16px; font-size: 22px; font-weight: 700;
    font-family: var(--font); color: var(--gray-900);
    background: transparent; height: 52px; width: 100%;
  }
  .input-hint { font-size: 12px; color: var(--gray-400); margin-top: 10px; line-height: 1.5; }

  /* ── Preview ── */
  .preview-label { font-size: 15px; font-weight: 700; color: var(--gray-900); margin-bottom: 12px; }
  .preview-card {
    background: var(--white); border-radius: var(--radius-lg);
    padding: 20px; box-shadow: var(--shadow-sm);
    border: 1px solid var(--green-200);
  }
  .preview-brand {
    display: flex; align-items: center; gap: 6px;
    font-size: 13px; font-weight: 800; color: var(--green-600);
    letter-spacing: 1px; margin-bottom: 12px;
  }
  .preview-text { font-size: 14px; color: var(--gray-700); line-height: 1.75; }
  .preview-highlight { color: var(--green-600); font-weight: 700; }

  /* ── Send button ── */
  .btn-send-promo {
    width: 100%; height: 52px;
    background: var(--green-700); color: var(--white);
    border: none; border-radius: var(--radius-md);
    font-family: var(--font); font-size: 15px; font-weight: 700;
    cursor: pointer; display: flex; align-items: center;
    justify-content: center; gap: 8px; margin-top: 20px;
    transition: background var(--trans), transform var(--trans), box-shadow var(--trans);
    box-shadow: 0 4px 16px rgba(20,82,40,.32);
  }
  .btn-send-promo:hover { background: var(--green-600); box-shadow: 0 6px 20px rgba(20,82,40,.42); }
  .btn-send-promo:active { transform: scale(.98); }

  .promo-scroll-content { padding: 0 20px 24px; }
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

<!-- Scrollable content -->
<div class="page-scroll">
  <div class="promo-scroll-content">
    <div class="md:grid md:grid-cols-2 md:gap-8">
      
      <!-- Left Column: Input and Banner -->
      <div class="flex flex-col">
        <!-- Banner -->
        <div class="promo-banner anim-slide delay-1">
          <div class="promo-banner-deco"></div>
          <div class="promo-banner-deco2"></div>
          <div class="promo-banner-title">Ada Promo Discount untuk anda!</div>
          <div class="promo-banner-desc">Tingkatkan kunjungan pelanggan dengan penawaran spesial.</div>
        </div>

        <!-- Section title -->
        <div class="promo-section-title anim-fade-up delay-2">
          <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
            <path d="M3 11L19 3L15 19L11 13L3 11Z" stroke="#1e7c3a" stroke-width="2" stroke-linejoin="round"/>
            <path d="M11 13L19 3" stroke="#1e7c3a" stroke-width="2" stroke-linecap="round"/>
          </svg>
          Kirim Promo
        </div>

        <!-- Input card -->
        <div class="input-card anim-slide delay-2">
          <div class="input-card-label">Nilai Potongan Harga</div>
          <div class="promo-input-wrap">
            <div class="promo-currency">Rp</div>
            <input
              class="promo-amount-input"
              id="promoAmount"
              type="text"
              value="5.000"
              inputmode="numeric"
              oninput="updatePreview(this.value)"
            />
          </div>
          <div class="input-hint">Potongan ini akan dikirimkan ke database pelanggan Anda via WhatsApp.</div>
        </div>

        <!-- Send button -->
        <button class="btn-send-promo anim-slide delay-4 md:mt-2" id="btnSendPromo" onclick="sendPromo()">
          <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
            <path d="M2 9L16 2L12 16L9 11L2 9Z" stroke="white" stroke-width="2" stroke-linejoin="round"/>
          </svg>
          Kirim Promo Sekarang
        </button>
      </div>

      <!-- Right Column: WhatsApp message preview -->
      <div class="flex flex-col pt-6 md:pt-0">
        <!-- Preview -->
        <div class="preview-label anim-fade-up delay-3">Pratinjau Pesan</div>
        <div class="preview-card anim-slide delay-3 flex-1 flex flex-col justify-between">
          <div>
            <div class="preview-brand">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                <circle cx="4" cy="4" r="3" fill="none" stroke="#208a40" stroke-width="1.5"/>
                <circle cx="4" cy="12" r="3" fill="none" stroke="#208a40" stroke-width="1.5"/>
                <line x1="4" y1="7" x2="14" y2="2" stroke="#208a40" stroke-width="1.5" stroke-linecap="round"/>
                <line x1="4" y1="9" x2="14" y2="14" stroke="#208a40" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
              SISIR BARBER
            </div>
            <div class="preview-text" id="previewText">
              Halo Sobat Sisir! 👋<br/><br/>
              Kabar gembira! Gunakan kode promo <span class="preview-highlight">SISIRHEMAT</span>
              untuk mendapatkan potongan sebesar:<br/><br/>
              💰 <strong>Rp <span id="previewAmount">5.000</span></strong><br/><br/>
              Berlaku hari ini saja! Jangan sampai ketinggalan ya! ✂️<br/><br/>
              Booking sekarang 👉 sisir.id/booking
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>


@endsection

@section('scripts')
<script>
  const CSRF = document.querySelector('meta[name="csrf-token"]').content;

  function updatePreview(val) {
    const el = document.getElementById('previewAmount');
    if (el) el.textContent = val || '0';
  }

  function sendPromo() {
    const btn    = document.getElementById('btnSendPromo');
    const amount = document.getElementById('promoAmount').value.replace(/\D/g,'') || '0';

    btn.disabled = true;
    btn.innerHTML = `
      <svg width="18" height="18" viewBox="0 0 18 18" fill="none" style="animation:spin 1s linear infinite">
        <circle cx="9" cy="9" r="7" stroke="white" stroke-width="2" stroke-dasharray="22" stroke-dashoffset="8"/>
      </svg>
      Mengirim...
    `;

    fetch('{{ route("sisir.promo.send") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': CSRF,
      },
      body: JSON.stringify({ discount_amount: parseInt(amount) }),
    })
    .then(r => r.json())
    .then(data => {
      showToast(data.message || '✅ Promo berhasil dikirim!');
    })
    .catch(() => {
      showToast('❌ Gagal mengirim promo. Coba lagi.');
    })
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = `
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M2 9L16 2L12 16L9 11L2 9Z" stroke="white" stroke-width="2" stroke-linejoin="round"/></svg>
        Kirim Promo Sekarang
      `;
    });
  }
</script>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
@endsection
