@extends('layouts.sisir')

@section('title', 'Booking Baru – SISIR')
@section('meta_description', 'Buat reservasi barbershop baru di SISIR.')

@section('extra_styles')
<style>
  body, .sisir-shell { background: var(--green-bg); }

  .create-header {
    padding: 8px 20px 20px;
  }
  .create-back {
    display: flex; align-items: center; gap: 8px;
    font-size: 14px; font-weight: 700; color: var(--green-600);
    text-decoration: none; margin-bottom: 16px;
  }
  .create-title { font-size: 26px; font-weight: 800; color: var(--gray-900); margin-bottom: 4px; }
  .create-subtitle { font-size: 13px; color: var(--gray-500); }

  /* Form cards */
  .form-card {
    background: var(--white); border-radius: var(--radius-lg);
    padding: 20px; margin: 0 20px 16px;
    box-shadow: var(--shadow-sm);
  }
  .form-card-title {
    font-size: 11px; font-weight: 800; color: var(--gray-400);
    letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 14px;
  }
  .form-field { margin-bottom: 14px; }
  .form-field:last-child { margin-bottom: 0; }
  .field-label {
    display: block; font-size: 13px; font-weight: 600;
    color: var(--gray-700); margin-bottom: 8px;
  }
  .field-input {
    width: 100%; height: 50px;
    border: 1.5px solid var(--gray-200);
    border-radius: var(--radius-md);
    padding: 0 16px;
    font-family: var(--font); font-size: 15px; color: var(--gray-900);
    background: var(--white); outline: none;
    transition: border-color var(--trans), box-shadow var(--trans);
    appearance: none;
  }
  .field-input:focus {
    border-color: var(--green-500);
    box-shadow: 0 0 0 3px rgba(32,138,64,.12);
  }
  .field-input.has-error { border-color: var(--red-500); }

  /* Service grid */
  .service-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
  }
  .service-card {
    border: 1.5px solid var(--gray-200);
    border-radius: var(--radius-md);
    padding: 14px 12px;
    cursor: pointer;
    transition: all var(--trans);
    position: relative;
  }
  .service-card.selected {
    border-color: var(--green-500);
    background: var(--green-50);
    box-shadow: 0 0 0 2px rgba(32,138,64,.18);
  }
  .service-name { font-size: 13px; font-weight: 700; color: var(--gray-900); margin-bottom: 4px; }
  .service-dur  { font-size: 11px; color: var(--gray-500); }
  .service-price { font-size: 13px; font-weight: 800; color: var(--green-600); margin-top: 6px; }
  .service-check {
    position: absolute; top: 10px; right: 10px;
    width: 18px; height: 18px; border-radius: 50%;
    background: var(--green-600); display: none;
    align-items: center; justify-content: center;
  }
  .service-card.selected .service-check { display: flex; }

  /* Slots grid */
  .slots-grid {
    display: flex; flex-wrap: wrap; gap: 8px;
  }
  .slot-btn {
    padding: 8px 14px;
    border: 1.5px solid var(--gray-200);
    border-radius: var(--radius-full);
    font-family: var(--font); font-size: 13px; font-weight: 600;
    cursor: pointer; background: transparent; color: var(--gray-700);
    transition: all var(--trans);
  }
  .slot-btn.selected { background: var(--green-700); border-color: var(--green-700); color: white; }
  .slot-btn:disabled { opacity: .38; cursor: not-allowed; }

  /* Submit */
  .btn-book {
    width: calc(100% - 40px); margin: 4px 20px 32px; height: 54px;
    background: var(--green-800); color: var(--white);
    border: none; border-radius: var(--radius-md);
    font-family: var(--font); font-size: 16px; font-weight: 700;
    cursor: pointer; display: flex; align-items: center;
    justify-content: center; gap: 10px;
    box-shadow: 0 4px 16px rgba(20,82,40,.38);
    transition: background var(--trans), transform var(--trans);
  }
  .btn-book:hover { background: var(--green-700); }
  .btn-book:active { transform: scale(.98); }
  .btn-book:disabled { opacity: .6; cursor: not-allowed; }

  /* QR result */
  .qr-result {
    display: none; margin: 0 20px 24px;
    background: var(--white); border-radius: var(--radius-lg);
    padding: 24px; text-align: center;
    box-shadow: var(--shadow-md);
    border: 2px solid var(--green-200);
  }
  .qr-result.show { display: block; }
  .qr-result h3 { font-size: 18px; font-weight: 800; color: var(--green-700); margin-bottom: 8px; }
  .qr-result p  { font-size: 13px; color: var(--gray-500); margin-bottom: 16px; }
  .qr-result img { width: 200px; height: 200px; border-radius: 12px; border: 2px solid var(--gray-200); }
  .qr-result .qr-fallback {
    width: 200px; height: 200px; border-radius: 12px; border: 2px dashed var(--gray-200);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto; color: var(--gray-400); font-size: 13px;
  }
</style>
@endsection

@section('content')
<!-- App Header -->
<div class="app-header">
  <a href="{{ route('sisir.booking') }}" class="brand">
    <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
      <path d="M14 5l-6 6 6 6" stroke="#1e7c3a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <span class="brand-name" style="font-size:17px">Booking Baru</span>
  </a>
  <div class="avatar-btn"><div class="avatar-fallback">{{ session('customer_name') ? strtoupper(substr(session('customer_name'),0,1)) : 'G' }}</div></div>
</div>

<div class="page-scroll">

  <!-- 1. Customer info -->
  <div class="form-card anim-fade-up">
    <div class="form-card-title">Informasi Pelanggan</div>
    <div class="form-field">
      <label class="field-label" for="fieldName">Nama Lengkap</label>
      <input id="fieldName" name="name" class="field-input" type="text"
             placeholder="Masukkan nama kamu"
             value="{{ session('customer_name', '') }}" required />
    </div>
    <div class="form-field">
      <label class="field-label" for="fieldPhone">Nomor WhatsApp</label>
      <input id="fieldPhone" name="phone" class="field-input" type="tel"
             placeholder="08xx-xxxx-xxxx" inputmode="numeric"
             value="{{ session('customer_phone', '') }}" required />
    </div>
  </div>

  <!-- 2. Service -->
  <div class="form-card anim-fade-up delay-1">
    <div class="form-card-title">Pilih Layanan</div>
    <div class="service-grid" id="serviceGrid">
      @foreach($services as $svc)
        <div class="service-card" data-id="{{ $svc->id }}" onclick="selectService(this, {{ $svc->id }})">
          <div class="service-name">{{ $svc->name }}</div>
          <div class="service-dur">⏱ {{ $svc->duration_minutes }} menit</div>
          <div class="service-price">Rp {{ number_format($svc->price, 0, ',', '.') }}</div>
          <div class="service-check">
            <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
              <path d="M2 5l2.5 2.5L8 3" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
        </div>
      @endforeach
    </div>
    <input type="hidden" id="selectedServiceId" />
  </div>

  <!-- 3. Barber -->
  <div class="form-card anim-fade-up delay-1">
    <div class="form-card-title">Pilih Kapster</div>
    <div class="form-field">
      <select id="barberSelect" class="field-input" onchange="loadSlots()">
        <option value="">-- Pilih kapster --</option>
        @foreach($barbers as $barber)
          <option value="{{ $barber->id }}">{{ $barber->displayName() }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <!-- 4. Date & Slot -->
  <div class="form-card anim-fade-up delay-2">
    <div class="form-card-title">Tanggal & Waktu</div>
    <div class="form-field">
      <label class="field-label" for="dateInput">Tanggal</label>
      <input id="dateInput" class="field-input" type="date"
             min="{{ today()->toDateString() }}"
             value="{{ today()->toDateString() }}"
             onchange="loadSlots()" />
    </div>
    <div class="form-field">
      <label class="field-label">Slot Tersedia</label>
      <div class="slots-grid" id="slotsGrid">
        <p style="font-size:13px;color:var(--gray-400)">Pilih kapster dan tanggal terlebih dahulu.</p>
      </div>
      <input type="hidden" id="selectedSlot" />
    </div>
  </div>

  <!-- 5. Notes -->
  <div class="form-card anim-fade-up delay-2">
    <div class="form-card-title">Catatan (opsional)</div>
    <textarea id="notesInput" class="field-input" style="height:80px;padding-top:12px;resize:none"
              placeholder="Permintaan khusus, contoh: model spesifik..."></textarea>
  </div>

  <!-- Submit -->
  <button class="btn-book anim-fade-up delay-3" id="btnBook" onclick="submitBooking()">
    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
      <rect x="2" y="4" width="16" height="14" rx="3" stroke="white" stroke-width="2"/>
      <line x1="2" y1="9" x2="18" y2="9" stroke="white" stroke-width="2"/>
      <line x1="7" y1="1" x2="7" y2="7" stroke="white" stroke-width="2" stroke-linecap="round"/>
      <line x1="13" y1="1" x2="13" y2="7" stroke="white" stroke-width="2" stroke-linecap="round"/>
    </svg>
    Buat Booking & Bayar DP
  </button>

  <!-- QR Result Card -->
  <div class="qr-result" id="qrResult">
    <h3>✅ Booking Dibuat!</h3>
    <p id="qrSubtitle">Scan QRIS di bawah untuk membayar DP.</p>
    <div id="qrDisplay"></div>
    <a href="{{ route('sisir.dashboard') }}" style="
      display:inline-block;margin-top:16px;padding:12px 24px;
      background:var(--green-600);color:white;border-radius:var(--radius-md);
      font-weight:700;font-size:14px;text-decoration:none;
    ">Lihat Dashboard →</a>
  </div>

</div>
@endsection

@section('scripts')
<script>
  const CSRF = document.querySelector('meta[name="csrf-token"]').content;
  let selectedServiceId = null;
  let selectedSlotTime  = null;

  // ── Service selection ────────────────────────────────────────────────────
  function selectService(el, id) {
    document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    selectedServiceId = id;
    document.getElementById('selectedServiceId').value = id;
  }

  // ── Load available time slots ────────────────────────────────────────────
  function loadSlots() {
    const barberId = document.getElementById('barberSelect').value;
    const date     = document.getElementById('dateInput').value;
    const grid     = document.getElementById('slotsGrid');

    if (!barberId || !date) {
      grid.innerHTML = '<p style="font-size:13px;color:var(--gray-400)">Pilih kapster dan tanggal terlebih dahulu.</p>';
      return;
    }

    grid.innerHTML = '<p style="font-size:13px;color:var(--gray-400)">Memuat slot...</p>';

    fetch(`/booking/slots?barber_id=${barberId}&date=${date}`, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
      const slots = data.slots || [];
      if (!slots.length) {
        grid.innerHTML = '<p style="font-size:13px;color:var(--red-500)">Tidak ada slot tersedia untuk tanggal ini.</p>';
        return;
      }
      grid.innerHTML = slots.map(slot => `
        <button class="slot-btn" onclick="selectSlot(this, '${slot.datetime}')" ${slot.available ? '' : 'disabled'}>
          ${slot.time}
        </button>
      `).join('');
    })
    .catch(() => {
      grid.innerHTML = '<p style="font-size:13px;color:var(--red-500)">Gagal memuat slot.</p>';
    });
  }

  // ── Slot selection ────────────────────────────────────────────────────────
  function selectSlot(el, datetime) {
    document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    selectedSlotTime = datetime;
    document.getElementById('selectedSlot').value = datetime;
  }

  // ── Submit booking ────────────────────────────────────────────────────────
  function submitBooking() {
    const name    = document.getElementById('fieldName').value.trim();
    const phone   = document.getElementById('fieldPhone').value.trim();
    const barber  = document.getElementById('barberSelect').value;
    const notes   = document.getElementById('notesInput').value.trim();

    if (!name || !phone) { showToast('⚠️ Isi nama dan nomor WhatsApp.'); return; }
    if (!selectedServiceId) { showToast('⚠️ Pilih layanan terlebih dahulu.'); return; }
    if (!barber) { showToast('⚠️ Pilih kapster.'); return; }
    if (!selectedSlotTime) { showToast('⚠️ Pilih slot waktu.'); return; }

    const btn = document.getElementById('btnBook');
    btn.disabled = true;
    btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" style="animation:spin 1s linear infinite"><circle cx="10" cy="10" r="8" stroke="white" stroke-width="2" stroke-dasharray="25" stroke-dashoffset="10"/></svg> Memproses...';

    fetch('/booking', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': CSRF,
      },
      body: JSON.stringify({
        name,
        phone,
        barber_id:    barber,
        service_id:   selectedServiceId,
        scheduled_at: selectedSlotTime,
        notes,
      }),
    })
    .then(r => r.json())
    .then(data => {
      if (data.error) {
        showToast('❌ ' + data.error);
        btn.disabled = false;
        btn.innerHTML = 'Buat Booking & Bayar DP';
        return;
      }

      // Show QR result card
      const qrDisplay  = document.getElementById('qrDisplay');
      const qrSubtitle = document.getElementById('qrSubtitle');

      if (data.qr_code_url) {
        qrDisplay.innerHTML = `<img src="${data.qr_code_url}" alt="QRIS QR Code" />`;
        qrSubtitle.textContent = `Booking #${data.booking_id} dibuat. Bayar DP Rp ${Number(data.dp_amount).toLocaleString('id')} dengan QRIS.`;
      } else {
        qrDisplay.innerHTML = `<div class="qr-fallback">QR tidak tersedia.<br>Hubungi admin.</div>`;
        qrSubtitle.textContent = data.warning || 'Booking berhasil dibuat!';
      }

      document.getElementById('qrResult').classList.add('show');
      document.getElementById('qrResult').scrollIntoView({ behavior: 'smooth' });
      btn.style.display = 'none';
    })
    .catch(() => {
      showToast('❌ Terjadi kesalahan. Coba lagi.');
      btn.disabled = false;
      btn.innerHTML = 'Buat Booking & Bayar DP';
    });
  }
</script>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
@endsection
