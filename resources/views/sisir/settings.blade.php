@extends('layouts.sisir')

@section('title', 'Pengaturan – SISIR')
@section('meta_description', 'Kelola profil, jam operasional, kapster, layanan, dan promo WhatsApp barbershop Anda.')

@section('extra_styles')
<style>
  body, .sisir-shell { background: var(--green-bg); }

  .settings-container {
    padding: 0 20px 24px;
  }
  @media (min-width: 768px) {
    .settings-container { padding: 0 32px 32px; }
  }

  /* Header Card */
  .settings-header-card {
    background: linear-gradient(135deg, var(--green-800) 0%, var(--green-500) 100%);
    border-radius: var(--radius-xl);
    padding: 24px 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 28px rgba(20,82,40,.2);
  }
  .settings-header-title {
    font-size: 22px; font-weight: 800; color: var(--white);
    line-height: 1.2; margin-bottom: 8px; position: relative;
  }
  .settings-header-desc {
    font-size: 13px; color: rgba(255,255,255,.8);
    line-height: 1.5; position: relative;
  }

  /* Tabs Bar */
  .tabs-bar {
    display: flex;
    gap: 8px;
    border-bottom: 2px solid var(--gray-200);
    margin-bottom: 24px;
    overflow-x: auto;
    padding-bottom: 2px;
    -webkit-overflow-scrolling: touch;
  }
  .tabs-bar::-webkit-scrollbar {
    display: none;
  }
  .tab-btn {
    padding: 12px 20px;
    font-size: 14px;
    font-weight: 700;
    color: var(--gray-500);
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    white-space: nowrap;
    transition: color var(--trans), border-color var(--trans);
    font-family: var(--font);
  }
  .tab-btn:hover {
    color: var(--gray-900);
  }
  .tab-btn.active {
    color: var(--green-700);
    border-bottom-color: var(--green-700);
  }

  /* Panes */
  .tab-pane {
    display: none;
  }
  .tab-pane.active {
    display: block;
    animation: fadeIn var(--trans);
  }
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* Form and Cards UI */
  .settings-card {
    background: var(--white);
    border-radius: var(--radius-lg);
    padding: 24px;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--gray-200);
    margin-bottom: 24px;
  }
  .settings-card-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--gray-900);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  /* Form elements */
  .form-group {
    margin-bottom: 20px;
  }
  .form-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 8px;
    display: block;
  }
  .form-input, .form-textarea, .form-select {
    width: 100%;
    padding: 12px 16px;
    border: 1.5px solid var(--gray-200);
    border-radius: var(--radius-md);
    font-family: var(--font);
    font-size: 14px;
    color: var(--gray-900);
    background: var(--white);
    outline: none;
    transition: border-color var(--trans), box-shadow var(--trans);
  }
  .form-input:focus, .form-textarea:focus, .form-select:focus {
    border-color: var(--green-500);
    box-shadow: 0 0 0 3px rgba(32,138,64,.12);
  }
  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }
  @media (max-width: 600px) {
    .form-grid {
      grid-template-columns: 1fr;
    }
  }
  .input-hint {
    font-size: 11px;
    color: var(--gray-400);
    margin-top: 6px;
    line-height: 1.4;
  }

  /* Button styles */
  .btn-primary {
    background: var(--green-700);
    color: var(--white);
    border: none;
    border-radius: var(--radius-md);
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    font-family: var(--font);
    transition: background var(--trans), transform var(--trans), box-shadow var(--trans);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }
  .btn-primary:hover {
    background: var(--green-600);
    box-shadow: 0 4px 12px rgba(32,138,64,.2);
  }
  .btn-primary:active {
    transform: scale(.98);
  }

  .btn-secondary {
    background: var(--white);
    border: 1.5px solid var(--gray-200);
    color: var(--gray-700);
    border-radius: var(--radius-md);
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    font-family: var(--font);
    transition: background var(--trans), border-color var(--trans);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    text-decoration: none;
  }
  .btn-secondary:hover {
    background: var(--gray-50);
    border-color: var(--gray-300);
  }

  .btn-danger-outline {
    background: var(--white);
    border: 1.5px solid var(--red-100);
    color: var(--red-500);
    border-radius: var(--radius-md);
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    font-family: var(--font);
    transition: background var(--trans), border-color var(--trans);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
  }
  .btn-danger-outline:hover {
    background: #fdf2f2;
    border-color: #f8b4b4;
  }

  /* Grid layouts */
  .grid-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
  }

  /* Grid details card */
  .grid-detail-card {
    background: var(--white);
    border-radius: var(--radius-lg);
    border: 1px solid var(--gray-200);
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: box-shadow var(--trans), transform var(--trans);
  }
  .grid-detail-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
  }
  .gdc-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
  }
  .gdc-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--green-100);
    color: var(--green-700);
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
  }
  .gdc-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 2px;
  }
  .gdc-meta {
    font-size: 12px;
    color: var(--gray-400);
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .gdc-desc {
    font-size: 13px;
    color: var(--gray-600);
    line-height: 1.5;
    margin-bottom: 16px;
    flex: 1;
  }
  .gdc-badge {
    padding: 4px 10px;
    border-radius: var(--radius-full);
    font-size: 11px;
    font-weight: 700;
    display: inline-block;
  }
  .gdc-badge.active {
    background: var(--green-50);
    color: var(--green-700);
  }
  .gdc-badge.inactive {
    background: var(--gray-100);
    color: var(--gray-500);
  }
  .gdc-actions {
    display: flex;
    gap: 8px;
    border-top: 1px solid var(--gray-100);
    padding-top: 14px;
    margin-top: auto;
  }

  /* Modals */
  .modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,.45);
    backdrop-filter: blur(4px);
    z-index: 100;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
  }
  .modal-overlay.open {
    display: flex;
  }
  .modal-sheet-premium {
    background: var(--white);
    border-radius: var(--radius-lg);
    width: 100%;
    max-width: 500px;
    box-shadow: var(--shadow-lg);
    animation: modalSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    overflow: hidden;
  }
  @keyframes modalSlideUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
  }
  .modal-header-premium {
    padding: 20px;
    border-bottom: 1px solid var(--gray-100);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .modal-title-premium {
    font-size: 16px;
    font-weight: 800;
    color: var(--gray-900);
  }
  .modal-back-btn {
    border: none;
    background: none;
    color: var(--gray-500);
    cursor: pointer;
    padding: 6px;
    border-radius: 50%;
    transition: background var(--trans), color var(--trans);
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .modal-back-btn:hover {
    background: var(--gray-100);
    color: var(--gray-900);
  }
  .modal-body-premium {
    padding: 20px;
  }

  /* Legacy Promo Tab styling compatibility */
  .promo-banner {
    background: linear-gradient(135deg, var(--green-800) 0%, var(--green-500) 100%);
    border-radius: var(--radius-xl);
    padding: 24px 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 28px rgba(20,82,40,.15);
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
    font-size: 20px; font-weight: 800; color: var(--white);
    line-height: 1.2; margin-bottom: 8px; position: relative;
  }
  .promo-banner-desc {
    font-size: 13px; color: rgba(255,255,255,.8);
    line-height: 1.5; position: relative;
  }
  .promo-section-title {
    font-size: 18px; font-weight: 800;
    color: var(--gray-900); margin-bottom: 16px;
    display: flex; align-items: center; gap: 8px;
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
  .btn-send-promo {
    width: 100%; height: 52px;
    background: var(--green-700); color: var(--white);
    border: none; border-radius: var(--radius-md);
    font-family: var(--font); font-size: 15px; font-weight: 700;
    cursor: pointer; display: flex; align-items: center;
    justify-content: center; gap: 8px; margin-top: 20px;
    transition: background var(--trans), transform var(--trans), box-shadow var(--trans);
    box-shadow: 0 4px 16px rgba(20,82,40,.2);
  }
  .btn-send-promo:hover { background: var(--green-600); }
  .btn-send-promo:active { transform: scale(.98); }

  @keyframes spin { to { transform: rotate(360deg); } }
</style>
@endsection

@section('content')
<!-- App Header (mobile only) -->
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
  <div class="settings-container">
    
    <!-- Top banner info -->
    <div class="settings-header-card anim-slide delay-1">
      <div class="promo-banner-deco"></div>
      <div class="promo-banner-deco2"></div>
      <div class="settings-header-title">Pengaturan Barbershop</div>
      <div class="settings-header-desc">Sesuaikan profil operasional, ketersediaan kapster, jenis layanan, dan siaran promo pelanggan.</div>
    </div>

    <!-- Error & Success alerts -->
    @if ($errors->any())
    <div class="alert anim-fade-up" style="background: var(--red-100); color: var(--red-500); padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 13px; font-weight: 600;">
      <ul style="margin: 0; padding-left: 16px;">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif
    @if (session('success'))
    <div class="alert anim-fade-up" style="background: var(--green-100); color: var(--green-800); padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
      <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Tabs Bar -->
    <div class="tabs-bar anim-fade-up delay-1">
      <button class="tab-btn active" onclick="switchTab('operational')">Operasional</button>
      <button class="tab-btn" onclick="switchTab('barbers')">Kapster</button>
      <button class="tab-btn" onclick="switchTab('services')">Layanan</button>
      <button class="tab-btn" onclick="switchTab('promo')">Kirim Promo</button>
    </div>

    <!-- Tab 1: Operasional -->
    <div class="tab-pane active" id="pane-operational">
      <div class="settings-card anim-slide delay-2">
        <div class="settings-card-title">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--green-700)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="9" y1="3" x2="9" y2="21"></line>
            <line x1="15" y1="3" x2="15" y2="21"></line>
            <line x1="3" y1="9" x2="21" y2="9"></line>
            <line x1="3" y1="15" x2="21" y2="15"></line>
          </svg>
          Profil & Jam Operasional
        </div>

        <form action="{{ route('sisir.settings.operational') }}" method="POST">
          @csrf
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Nama Barbershop</label>
              <input type="text" name="shop_name" class="form-input" value="{{ $settings['shop_name'] }}" required />
            </div>
            <div class="form-group">
              <label class="form-label">Nomor WhatsApp Admin</label>
              <input type="text" name="whatsapp_number" class="form-input" value="{{ $settings['whatsapp_number'] }}" required />
              <div class="input-hint">Gunakan kode negara (misal: 628123456789) tanpa spasi atau tanda +.</div>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Alamat Lengkap</label>
            <textarea name="shop_address" rows="3" class="form-textarea" required>{{ $settings['shop_address'] }}</textarea>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Jumlah Kursi (Kapasitas Maksimal per Slot)</label>
              <input type="number" name="chairs_count" class="form-input" min="1" max="50" value="{{ $settings['chairs_count'] }}" required />
              <div class="input-hint">Membatasi jumlah antrean pengerjaan rambut bersamaan pada jam yang sama.</div>
            </div>
            <div class="form-group">
              <label class="form-label">Jeda Waktu Pengerjaan (Menit)</label>
              <input type="number" name="slot_duration" class="form-input" min="30" max="120" step="10" value="{{ $settings['slot_duration'] }}" required />
              <div class="input-hint">Interval kelipatan booking yang tersedia (minimal 30 menit & kelipatan 10 menit).</div>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Jam Buka</label>
              <input type="time" name="opening_time" class="form-input" value="{{ \Carbon\Carbon::parse($settings['opening_time'])->format('H:i') }}" required />
            </div>
            <div class="form-group">
              <label class="form-label">Jam Tutup</label>
              <input type="time" name="closing_time" class="form-input" value="{{ \Carbon\Carbon::parse($settings['closing_time'])->format('H:i') }}" required />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Persentase Down Payment (DP) %</label>
            <input type="number" name="dp_amount" class="form-input" min="0" max="100" value="{{ $settings['dp_amount'] }}" required />
            <div class="input-hint">Persentase pembayaran di muka yang harus dibayarkan pelanggan saat melakukan booking online. Set 0 untuk gratis.</div>
          </div>

          <div style="margin-top: 12px; text-align: right;">
            <button type="submit" class="btn-primary">Simpan Pengaturan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Tab 2: Kapster (Barbers) -->
    <div class="tab-pane" id="pane-barbers">
      <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
        <h3 style="font-size: 16px; font-weight: 800; color: var(--gray-900);">Daftar Kapster</h3>
        <button class="btn-primary" onclick="openAddBarberModal()">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          Tambah Kapster
        </button>
      </div>

      <div class="grid-cards">
        @forelse($barbers as $barber)
          <div class="grid-detail-card">
            <div>
              <div class="gdc-header">
                <div class="gdc-avatar">{{ strtoupper(substr($barber->nickname, 0, 1)) }}</div>
                <div>
                  <div class="gdc-title">{{ $barber->nickname }}</div>
                  <div class="gdc-meta">
                    @if($barber->is_active)
                      <span class="gdc-badge active">Aktif</span>
                    @else
                      <span class="gdc-badge inactive">Nonaktif</span>
                    @endif
                  </div>
                </div>
              </div>
              <p class="gdc-desc">{{ $barber->bio ?: 'Tidak ada deskripsi.' }}</p>
            </div>
            <div class="gdc-actions">
              <button class="btn-secondary" style="flex: 1;" onclick="openEditBarberModal({{ json_encode($barber) }})">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                Edit
              </button>
              <button class="btn-danger-outline" onclick="confirmDeleteBarber({{ $barber->id }}, '{{ $barber->nickname }}')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
              </button>
            </div>
          </div>
        @empty
          <div style="grid-column: 1 / -1; text-align: center; padding: 40px 20px; background: white; border-radius: var(--radius-lg); border: 1.5px dashed var(--gray-200); color: var(--gray-400);">
            Belum ada kapster terdaftar.
          </div>
        @endforelse
      </div>
    </div>

    <!-- Tab 3: Layanan (Services) -->
    <div class="tab-pane" id="pane-services">
      <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
        <h3 style="font-size: 16px; font-weight: 800; color: var(--gray-900);">Daftar Layanan</h3>
        <button class="btn-primary" onclick="openAddServiceModal()">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          Tambah Layanan
        </button>
      </div>

      <div class="grid-cards">
        @forelse($services as $service)
          <div class="grid-detail-card">
            <div>
              <div class="gdc-header">
                <div class="gdc-avatar" style="background: var(--green-50); color: var(--green-600);">
                  ✂️
                </div>
                <div>
                  <div class="gdc-title">{{ $service->name }}</div>
                  <div class="gdc-meta" style="color: var(--green-700); font-weight: 700; font-size: 13px;">
                    Rp {{ number_format($service->price, 0, ',', '.') }}
                    <span style="color: var(--gray-200);">|</span>
                    <span style="font-weight: 400; color: var(--gray-500);">⏱️ {{ $service->duration_minutes }} menit</span>
                  </div>
                </div>
              </div>
              <p class="gdc-desc" style="font-size: 12px; margin-bottom: 12px;">{{ $service->description ?: 'Tidak ada deskripsi.' }}</p>
              <div>
                @if($service->is_active)
                  <span class="gdc-badge active">Aktif</span>
                @else
                  <span class="gdc-badge inactive">Nonaktif</span>
                @endif
              </div>
            </div>
            <div class="gdc-actions">
              <button class="btn-secondary" style="flex: 1;" onclick="openEditServiceModal({{ json_encode($service) }})">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                Edit
              </button>
              <button class="btn-danger-outline" onclick="confirmDeleteService({{ $service->id }}, '{{ $service->name }}')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
              </button>
            </div>
          </div>
        @empty
          <div style="grid-column: 1 / -1; text-align: center; padding: 40px 20px; background: white; border-radius: var(--radius-lg); border: 1.5px dashed var(--gray-200); color: var(--gray-400);">
            Belum ada layanan terdaftar.
          </div>
        @endforelse
      </div>
    </div>

    <!-- Tab 4: Kirim Promo -->
    <div class="tab-pane" id="pane-promo">
      <div class="md:grid md:grid-cols-2 md:gap-8">
        <!-- Left: Input & Banner -->
        <div>
          <!-- Promo Banner -->
          <div class="promo-banner">
            <div class="promo-banner-deco"></div>
            <div class="promo-banner-deco2"></div>
            <div class="promo-banner-title">Ada Promo Discount untuk anda!</div>
            <div class="promo-banner-desc">Tingkatkan kunjungan pelanggan dengan penawaran spesial.</div>
          </div>

          <div class="promo-section-title">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" style="flex-shrink:0;">
              <path d="M3 11L19 3L15 19L11 13L3 11Z" stroke="var(--green-700)" stroke-width="2" stroke-linejoin="round"/>
              <path d="M11 13L19 3" stroke="var(--green-700)" stroke-width="2" stroke-linecap="round"/>
            </svg>
            Kirim Promo Broadcast WhatsApp
          </div>

          <!-- Input card -->
          <div class="settings-card" style="padding: 20px; margin-bottom: 20px;">
            <div style="font-size: 13px; font-weight: 600; color: var(--gray-700); margin-bottom: 12px;">Nilai Potongan Harga</div>
            <div class="promo-input-wrap">
              <div class="promo-currency">Rp</div>
              <input
                class="promo-amount-input"
                id="promoAmount"
                type="text"
                value="5.000"
                inputmode="numeric"
                oninput="updatePromoPreview(this.value)"
              />
            </div>
            <div class="input-hint">Potongan ini akan dikirimkan ke database pelanggan Anda via WhatsApp.</div>
          </div>

          <!-- Send button -->
          <button class="btn-send-promo" id="btnSendPromo" onclick="sendPromoBroadcast()">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
              <path d="M2 9L16 2L12 16L9 11L2 9Z" stroke="white" stroke-width="2" stroke-linejoin="round"/>
            </svg>
            Kirim Promo Sekarang
          </button>
        </div>

        <!-- Right: Preview -->
        <div style="margin-top: 24px; @media (min-width: 768px) { margin-top: 0; }">
          <div class="preview-label">Pratinjau Pesan</div>
          <div class="preview-card" style="height: calc(100% - 35px); display: flex; flex-direction: column; justify-content: space-between;">
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
</div>

<!-- Add/Edit Barber Modal -->
<div class="modal-overlay" id="barberModal" onclick="closeBarberModal(event)">
  <div class="modal-sheet-premium">
    <div class="modal-header-premium">
      <span class="modal-title-premium" id="barberModalTitle">Tambah Kapster</span>
      <button class="modal-back-btn" onclick="closeBarberModalActual()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>
    <div class="modal-body-premium">
      <form action="{{ route('sisir.settings.barbers') }}" method="POST" id="barberForm">
        @csrf
        <input type="hidden" name="id" id="barberId" />
        
        <div class="form-group">
          <label class="form-label">Nama Panggilan (Nickname)</label>
          <input type="text" name="nickname" id="barberNickname" class="form-input" placeholder="Contoh: Budi" required />
        </div>

        <div class="form-group">
          <label class="form-label">Bio Singkat</label>
          <textarea name="bio" id="barberBio" rows="3" class="form-textarea" placeholder="Contoh: Spesialis gaya rambut pompadour & fade."></textarea>
        </div>

        <div class="form-group" id="barberActiveWrapper" style="display: none; align-items: center; gap: 8px;">
          <input type="checkbox" name="is_active" id="barberActive" value="1" style="width: 18px; height: 18px; accent-color: var(--green-700);" />
          <label for="barberActive" class="form-label" style="margin-bottom: 0; cursor: pointer;">Status Aktif (Bisa Menerima Booking)</label>
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px;">
          <button type="button" class="btn-secondary" onclick="closeBarberModalActual()">Batal</button>
          <button type="submit" class="btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Add/Edit Service Modal -->
<div class="modal-overlay" id="serviceModal" onclick="closeServiceModal(event)">
  <div class="modal-sheet-premium">
    <div class="modal-header-premium">
      <span class="modal-title-premium" id="serviceModalTitle">Tambah Layanan</span>
      <button class="modal-back-btn" onclick="closeServiceModalActual()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>
    <div class="modal-body-premium">
      <form action="{{ route('sisir.settings.services') }}" method="POST" id="serviceForm">
        @csrf
        <input type="hidden" name="id" id="serviceId" />

        <div class="form-group">
          <label class="form-label">Nama Layanan</label>
          <input type="text" name="name" id="serviceName" class="form-input" placeholder="Contoh: Premium Haircut & Wash" required />
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Harga (Rp)</label>
            <input type="number" name="price" id="servicePrice" class="form-input" placeholder="Contoh: 50000" min="0" required />
          </div>
          <div class="form-group">
            <label class="form-label">Durasi (Menit)</label>
            <input type="number" name="duration_minutes" id="serviceDuration" class="form-input" placeholder="Contoh: 30" min="30" step="10" required />
            <div class="input-hint">Minimal 30 menit & kelipatan 10.</div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Deskripsi Layanan</label>
          <textarea name="description" id="serviceDescription" rows="3" class="form-textarea" placeholder="Contoh: Termasuk cuci rambut, pijat kepala, dan styling pomade."></textarea>
        </div>

        <div class="form-group" id="serviceActiveWrapper" style="display: none; align-items: center; gap: 8px;">
          <input type="checkbox" name="is_active" id="serviceActive" value="1" style="width: 18px; height: 18px; accent-color: var(--green-700);" />
          <label for="serviceActive" class="form-label" style="margin-bottom: 0; cursor: pointer;">Status Aktif (Tampil di Booking)</label>
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px;">
          <button type="button" class="btn-secondary" onclick="closeServiceModalActual()">Batal</button>
          <button type="submit" class="btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Custom Confirmation Modal -->
<div class="modal-overlay" id="confirmModal" onclick="closeConfirmModal(event)" style="z-index: 1000;">
  <div class="modal-sheet-premium" style="max-width: 400px; padding: 24px; text-align: center;">
    <h3 id="confirmTitle" style="font-size: 16px; font-weight: 800; color: var(--gray-900); margin-bottom: 12px;">Hapus Data</h3>
    <p id="confirmMessage" style="font-size: 14px; color: var(--gray-600); margin-bottom: 24px; line-height: 1.5;">Apakah Anda yakin?</p>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
      <button type="button" class="btn-secondary" onclick="closeConfirmModalActual()" style="padding: 10px;">Batal</button>
      <button type="button" id="confirmActionBtn" class="btn-primary" style="background: var(--red-500); padding: 10px; color: white;">Ya, Hapus</button>
    </div>
  </div>
</div>

<!-- Hidden Delete Forms -->
<form id="deleteBarberForm" method="POST" style="display: none;">
  @csrf
</form>
<form id="deleteServiceForm" method="POST" style="display: none;">
  @csrf
</form>

@endsection

@section('scripts')
<script>
  // Active Tab Handling
  let activeTab = 'operational';
  
  // Try to load active tab from local storage or URL hash
  if (window.location.hash) {
    activeTab = window.location.hash.substring(1);
  } else {
    activeTab = localStorage.getItem('sisir_settings_active_tab') || 'operational';
  }

  function switchTab(tabName) {
    activeTab = tabName;
    localStorage.setItem('sisir_settings_active_tab', tabName);
    window.location.hash = '#' + tabName;
    
    // Update Tab Buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
      const isMatch = btn.getAttribute('onclick').includes(tabName);
      if (isMatch) btn.classList.add('active');
      else btn.classList.remove('active');
    });

    // Update Tab Panes
    document.querySelectorAll('.tab-pane').forEach(pane => {
      if (pane.id === 'pane-' + tabName) pane.classList.add('active');
      else pane.classList.remove('active');
    });
  }

  // Run on page load
  switchTab(activeTab);

  // Barber Modal Actions
  function openAddBarberModal() {
    document.getElementById('barberModalTitle').textContent = 'Tambah Kapster';
    document.getElementById('barberId').value = '';
    document.getElementById('barberNickname').value = '';
    document.getElementById('barberBio').value = '';
    document.getElementById('barberActiveWrapper').style.display = 'none';
    document.getElementById('barberActive').checked = true;
    document.getElementById('barberModal').classList.add('open');
  }

  function openEditBarberModal(barber) {
    document.getElementById('barberModalTitle').textContent = 'Edit Kapster';
    document.getElementById('barberId').value = barber.id;
    document.getElementById('barberNickname').value = barber.nickname;
    document.getElementById('barberBio').value = barber.bio || '';
    document.getElementById('barberActiveWrapper').style.display = 'flex';
    document.getElementById('barberActive').checked = !!barber.is_active;
    document.getElementById('barberModal').classList.add('open');
  }

  function closeBarberModal(e) {
    if (e.target.id === 'barberModal') {
      closeBarberModalActual();
    }
  }

  function closeBarberModalActual() {
    document.getElementById('barberModal').classList.remove('open');
  }

  // Service Modal Actions
  function openAddServiceModal() {
    document.getElementById('serviceModalTitle').textContent = 'Tambah Layanan';
    document.getElementById('serviceId').value = '';
    document.getElementById('serviceName').value = '';
    document.getElementById('servicePrice').value = '';
    document.getElementById('serviceDuration').value = '30';
    document.getElementById('serviceDescription').value = '';
    document.getElementById('serviceActiveWrapper').style.display = 'none';
    document.getElementById('serviceActive').checked = true;
    document.getElementById('serviceModal').classList.add('open');
  }

  function openEditServiceModal(service) {
    document.getElementById('serviceModalTitle').textContent = 'Edit Layanan';
    document.getElementById('serviceId').value = service.id;
    document.getElementById('serviceName').value = service.name;
    document.getElementById('servicePrice').value = service.price;
    document.getElementById('serviceDuration').value = service.duration_minutes;
    document.getElementById('serviceDescription').value = service.description || '';
    document.getElementById('serviceActiveWrapper').style.display = 'flex';
    document.getElementById('serviceActive').checked = !!service.is_active;
    document.getElementById('serviceModal').classList.add('open');
  }

  // Modal backdrop click handler
  function closeServiceModal(e) {
    if (e.target.id === 'serviceModal') {
      closeServiceModalActual();
    }
  }

  function closeServiceModalActual() {
    document.getElementById('serviceModal').classList.remove('open');
  }

  // Confirmation Modals
  let deleteFormToSubmit = null;

  function showConfirmModal(title, msg, onConfirm) {
    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmMessage').textContent = msg;
    
    const btn = document.getElementById('confirmActionBtn');
    btn.onclick = function() {
      onConfirm();
      closeConfirmModalActual();
    };
    
    document.getElementById('confirmModal').classList.add('open');
  }

  function closeConfirmModal(e) {
    if (e.target.id === 'confirmModal') {
      closeConfirmModalActual();
    }
  }

  function closeConfirmModalActual() {
    document.getElementById('confirmModal').classList.remove('open');
  }

  function confirmDeleteBarber(id, nickname) {
    showConfirmModal('Hapus Kapster', `Apakah Anda yakin ingin menghapus kapster "${nickname}"? Data ini tidak dapat dikembalikan secara permanen.`, function() {
      const form = document.getElementById('deleteBarberForm');
      form.action = `/settings/barbers/${id}/delete`;
      form.submit();
    });
  }

  function confirmDeleteService(id, name) {
    showConfirmModal('Hapus Layanan', `Apakah Anda yakin ingin menghapus layanan "${name}"? Data ini tidak dapat dikembalikan secara permanen.`, function() {
      const form = document.getElementById('deleteServiceForm');
      form.action = `/settings/services/${id}/delete`;
      form.submit();
    });
  }

  // Promo Broadcast Logic
  function updatePromoPreview(val) {
    const el = document.getElementById('previewAmount');
    if (el) el.textContent = val || '0';
  }

  function sendPromoBroadcast() {
    const btn = document.getElementById('btnSendPromo');
    const amountInput = document.getElementById('promoAmount');
    const amount = amountInput.value.replace(/\D/g, '') || '0';
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    btn.disabled = true;
    btn.innerHTML = `
      <svg width="18" height="18" viewBox="0 0 18 18" fill="none" style="animation:spin 1s linear infinite">
        <circle cx="9" cy="9" r="7" stroke="white" stroke-width="2" stroke-dasharray="22" stroke-dashoffset="8"/>
      </svg>
      Mengirim...
    `;

    fetch('{{ route("sisir.settings.promo.send") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
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
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
          <path d="M2 9L16 2L12 16L9 11L2 9Z" stroke="white" stroke-width="2" stroke-linejoin="round"/>
        </svg>
        Kirim Promo Sekarang
      `;
    });
  }
</script>
@endsection
