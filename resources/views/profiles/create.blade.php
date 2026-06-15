@extends('layouts.app')

@section('content')
<!-- Beri padding kiri sesuai lebar sidebar (cek inspect → biasanya 250–320px) -->
<div class="container-fluid">
    <div class="card card-body">
    <!-- Atau lebih baik: tambahkan class CSS di app.css seperti di bawah -->

    <h3 class="mb-4">Tambah Profile Relawan</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('profiles.store') }}" method="POST">
        @csrf

        <!-- NIK & Nama -->
        <div class="row">
            <div class="row" id="unit-cabang-row" 
                style="{{ auth()->user()->is_admin ? '' : 'display: none;' }}">

                <div class="col-md-3 mb-3">
                    <label class="form-label">Unit biMBA <span class="text-danger">*</span></label>

                    @if (auth()->user()->is_admin ?? false)
                        <select name="biMBA_unit" id="bimba_unit" class="form-control @error('biMBA_unit') is-invalid @enderror" required>
                            <option value="">-- Pilih Unit biMBA --</option>
                            @foreach($units as $namaUnit => $label)
                                <option value="{{ $namaUnit }}" 
                                        data-nocabang="{{ substr($label, strrpos($label, '(') + 1, -1) }}"
                                        {{ old('bimba_unit') == $namaUnit ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        @php $userUnit = auth()->user()->bimba_unit ?? null; @endphp
                        @if($userUnit)
                            <input type="text" class="form-control" value="{{ $userUnit }}" readonly>
                            <input type="hidden" name="biMBA_unit" value="{{ trim($userUnit) }}">
                        @else
                            <input type="text" class="form-control is-invalid" value="Unit Anda belum diatur" readonly>
                            <div class="invalid-feedback">Hubungi admin untuk mengatur unit Anda</div>
                            <input type="hidden" name="biMBA_unit" value="">
                        @endif
                    @endif
                    @error('bimba_unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label">No Cabang</label>
                    <input type="text" id="no_cabang" name="no_cabang" class="form-control" readonly>
                </div>
    
            <div class="col-md-2 mb-3">
                <label class="form-label">NIK <span class="text-danger">*</span></label>
                <input type="text" name="nik" id="nik" class="form-control @error('nik') is-invalid @enderror"
                    value="{{ old('nik') }}" required readonly placeholder="Otomatis setelah pilih Unit biMBA">
                @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-2 mb-3">
                <label class="form-label">No Urut</label>
                <input type="number" name="no_urut" class="form-control" value="{{ old('no_urut') }}" readonly>
            </div>

            <div class="col-md-5 mb-3">
                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                       value="{{ old('nama') }}" required>
                @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            
        </div>

        <!-- Jabatan, Status, Departemen -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Jabatan</label>
                <select name="jabatan" id="jabatan" class="form-control">
                    <option value="">-- Pilih Jabatan --</option>
                    @foreach($jabatanOptions as $jab)
                        <option value="{{ $jab }}" {{ old('jabatan') == $jab ? 'selected' : '' }}>{{ $jab }}</option>
                    @endforeach
                </select>
            </div>
            <!-- Status Relawan -->
            <div class="col-md-4 mb-3">
                <label class="form-label">Status Relawan <span class="text-danger">*</span></label>
                <select name="status_karyawan" id="status_karyawan" class="form-control @error('status_karyawan') is-invalid @enderror" required>
                    <option value="">-- Pilih Status --</option>
                    @foreach($statusOptions as $status)
                        <option value="{{ $status }}" {{ old('status_karyawan') == $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
                @error('status_karyawan') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Departemen</label>
                <select name="departemen" class="form-control">
                    <option value="">-- Pilih Departemen --</option>
                    @foreach($departemenOptions as $dept)
                        <option value="{{ $dept }}" {{ old('departemen') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- UNIT biMBA & NO CABANG – SELALU MUNCUL UNTUK SEMUA JABATAN --}}
        {{-- UNIT biMBA & NO CABANG – SELALU MUNCUL UNTUK SEMUA JABATAN --}}


    
</div>

        <!-- Contoh yang benar sekarang (pakai name netral) -->
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label" id="tanggal_label">
            Tanggal Mulai Magang / Masuk
        </label>
        <small class="text-muted d-block" id="tanggal_keterangan">
            (isi tanggal mulai magang jika status Magang)
        </small>
       <input type="date" 
       name="tgl_masuk"
       id="tgl_masuk"
       class="form-control">
        @error('tgl_masuk') 
            <div class="invalid-feedback">{{ $message }}</div> 
        @enderror
    </div>
    <div class="col-md-4 mb-3">
    <label class="form-label">Preview Masa Kerja</label>
    <input type="text" 
           id="masa_kerja_preview"
           class="form-control"
           readonly>
</div>

    <!-- Pastikan TIDAK ADA input duplikat dengan name="tgl_masuk" -->
    <!-- Jika masih ada input lama ini, HAPUS atau ganti name-nya -->
    <!-- <input type="date" name="tgl_masuk" id="tgl_masuk" ... > -->
</div>

        <hr class="my-4">

        <!-- DATA KHUSUS GURU (tetap ada, tapi hanya muncul jika jabatan Guru) -->
        <div class="guru-only" style="display: none;">
            <h5 class="text-primary mb-3">Data Khusus Guru</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Jumlah Murid MBA</label>
                    <input type="number" name="jumlah_murid_mba" class="form-control" value="{{ old('jumlah_murid_mba') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Jumlah Murid English</label>
                    <input type="number" name="jumlah_murid_eng" class="form-control" value="{{ old('jumlah_murid_eng') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Total Murid</label>
                    <input type="number" name="total_murid" class="form-control" readonly>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>RB</label>
                    <input type="text" name="rb" class="form-control" value="{{ old('rb') }}" placeholder="RB..">
                </div>
                <div class="col-md-4 mb-3">
                    <label>KTR</label>
                    <input type="text" name="ktr" class="form-control" value="{{ old('ktr') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label>RP</label>
                    <input type="number" name="rp" class="form-control" readonly>
                </div>
            </div>
        </div>

        <!-- DATA KHUSUS KEPALA UNIT (tetap ada, hanya muncul jika Kepala Unit) -->
        <div class="kepala-only" style="display: none;">
            <h5 class="text-success mb-3">Data Kepala Unit</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Total Murid Bawahan</label>
                    <input type="number" class="form-control" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label>RP</label>
                    <input type="number" name="rp" class="form-control" readonly>
                </div>
            </div>
        </div>

        <!-- Tanggal Lahir & Usia -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Tempat Lahir</label>
                <input type="text" name="tempat_lahir" id="tempat_lahir" class="form-control" value="{{ old('tempat_lahir') }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Tanggal Lahir</label>
                <input type="date" name="tgl_lahir" id="tgl_lahir" class="form-control" value="{{ old('tgl_lahir') }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Usia</label>
                <input type="text" id="usia" class="form-control" readonly>
            </div>
        </div>

        <!-- Kontak & Bank -->
        <h4 class="mt-5 mb-3">Data Kontak & Rekening</h4>
        <div class="row">
            <div class="col-md-4 mb-3"><label>No Telepon</label><input type="text" name="no_telp" class="form-control" value="{{ old('no_telp') }}"></div>
            <div class="col-md-4 mb-3"><label>Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}"></div>
            <div class="col-md-4 mb-3"><label>Nomor Rekening</label><input type="text" name="no_rekening" class="form-control" value="{{ old('no_rekening') }}"></div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3"><label>Bank</label><input type="text" name="bank" class="form-control" value="{{ old('bank') }}"></div>
            <div class="col-md-6 mb-3"><label>Atas Nama</label><input type="text" name="atas_nama" class="form-control" value="{{ old('atas_nama') }}"></div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary btn-lg">Simpan Profile</button>
            <a href="{{ route('profiles.index') }}" class="btn btn-secondary btn-lg">Batal</a>
        </div>
    </form>
</div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('[Profile Create] Script dimuat — mulai deteksi mode');

    // Elemen target
    const nikInput        = document.querySelector('input[name="nik"]');
    const noUrutInput     = document.querySelector('input[name="no_urut"]');
    const noCabangInput   = document.getElementById('no_cabang');
    const unitNoCabangMap = @json($unitNoCabang ?? []);

    console.log('[Debug] Elemen NIK ditemukan?', !!nikInput);
    console.log('[Debug] Elemen No Urut ditemukan?', !!noUrutInput);
    console.log('[Debug] Elemen No Cabang ditemukan?', !!noCabangInput);

    // =============================================
    // Fungsi fetch NIK
    // =============================================
    function fetchNikDanNoUrut(unitValue) {
        if (!unitValue?.trim() || !nikInput) {
            console.warn('[Debug] Unit kosong atau input NIK tidak ada → skip fetch');
            if (nikInput) nikInput.value = '(unit tidak valid)';
            return;
        }

        unitValue = unitValue.trim();
        console.log(`[Debug] Memulai fetch untuk unit: "${unitValue}"`);

        fetch(`/profiles/next-nik-nourut?bimba_unit=${encodeURIComponent(unitValue)}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => {
            console.log('[Debug] Status HTTP:', res.status);
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            console.log('[Debug] Response JSON:', data);

            if (data.error) {
                console.warn('[Server] Error:', data.error);
                nikInput.value = `Error: ${data.error}`;
                return;
            }

            nikInput.value    = data.nik       || '(nik kosong)';
            noUrutInput.value = data.no_urut   || '';
            if (noCabangInput) {
                noCabangInput.value = data.no_cabang || unitNoCabangMap[unitValue] || '';
            }
        })
        .catch(err => {
            console.error('[Debug] Fetch error:', err.message);
            nikInput.value = 'Gagal load NIK';
        });
    }

    // =============================================
    // Deteksi unit value (admin atau non-admin)
    // =============================================
    let unitValue = null;

    // 1. Coba dari SELECT (admin)
    const selectUnit = document.getElementById('bimba_unit');
    if (selectUnit && selectUnit.tagName === 'SELECT') {
        console.log('[Debug] Mode: ADMIN (select ditemukan)');
        unitValue = (selectUnit.value || '').trim();

        // Event change
        selectUnit.addEventListener('change', () => {
            const newVal = (selectUnit.value || '').trim();
            console.log('[Debug] Admin mengubah unit ke:', newVal);
            if (newVal) {
                if (noCabangInput) noCabangInput.value = unitNoCabangMap[newVal] || '';
                fetchNikDanNoUrut(newVal);
            }
        });
    }

    // 2. Jika bukan admin → coba hidden input (non-admin)
    if (!unitValue) {
        // Coba beberapa kemungkinan nama (toleran huruf besar/kecil)
        const possibleHidden = [
            document.querySelector('input[name="biMBA_unit"][type="hidden"]'),
            document.querySelector('input[name="bimba_unit"][type="hidden"]'),
            document.querySelector('input[name="BiMBA_unit"][type="hidden"]')
        ].find(el => el && el.value?.trim());

        if (possibleHidden) {
            console.log('[Debug] Mode: NON-ADMIN (hidden input ditemukan)');
            unitValue = possibleHidden.value.trim();
            console.log('[Debug] Nilai unit dari hidden:', unitValue);
        }
    }

    // 3. Jika masih tidak ketemu → coba cari semua input terkait unit
    if (!unitValue) {
        const allUnitInputs = document.querySelectorAll('input[name*="unit" i]');
        if (allUnitInputs.length > 0) {
            console.log('[Debug] Mencoba fallback — menemukan', allUnitInputs.length, 'input terkait unit');
            for (let input of allUnitInputs) {
                if (input.value?.trim() && input.type !== 'hidden') {
                    unitValue = input.value.trim();
                    console.log('[Debug] Fallback: nilai dari input readonly:', unitValue);
                    break;
                }
            }
        }
    }

    // Jalankan fetch jika ada unit
    if (unitValue) {
        // Isi No Cabang dulu (khusus non-admin)
        if (noCabangInput && unitNoCabangMap[unitValue]) {
            noCabangInput.value = unitNoCabangMap[unitValue];
            console.log('[Debug] No Cabang diisi dari map:', unitNoCabangMap[unitValue]);
        }
        fetchNikDanNoUrut(unitValue);
    } else {
        console.warn('[Debug] TIDAK MENEMUKAN NILAI UNIT SAMA SEKALI');
        if (nikInput) nikInput.value = '(pilih unit terlebih dahulu)';
    }

    // =============================================
    // Bagian toggle + hitung masa kerja & usia (tetap)
    // =============================================
    const jabatanSelect   = document.getElementById('jabatan');
    const guruSection     = document.querySelector('.guru-only');
    const kepalaSection   = document.querySelector('.kepala-only');

    function toggleSections() {
        const val = jabatanSelect?.value?.trim();
        if (guruSection)   guruSection.style.display  = val === 'Guru'        ? 'block' : 'none';
        if (kepalaSection) kepalaSection.style.display = val === 'Kepala Unit' ? 'block' : 'none';
    }

    if (jabatanSelect) {
        jabatanSelect.addEventListener('change', toggleSections);
        toggleSections();
    }

    function hitungMasaKerja() {
    const tgl = document.getElementById('tgl_masuk')?.value;
    const el  = document.getElementById('masa_kerja_preview');

    if (!tgl || !el) {
        if (el) el.value = '';
        return;
    }

    const masuk = new Date(tgl);
    const now   = new Date();

    let bln = (now.getFullYear() - masuk.getFullYear()) * 12
              + now.getMonth() - masuk.getMonth();

    if (now.getDate() < masuk.getDate()) bln--;

    if (bln < 0) {
        el.value = '';
        return;
    }

    const tahun = Math.floor(bln / 12);
    const bulan = bln % 12;

    el.value = `${tahun} tahun ${bulan} bulan`;
}

    function hitungUsia() {
        const tgl = document.getElementById('tgl_lahir')?.value;
        const el  = document.getElementById('usia');
        if (!tgl || !el) return;
        const lahir = new Date(tgl);
        const now   = new Date();
        let thn = now.getFullYear() - lahir.getFullYear();
        if (now.getMonth() < lahir.getMonth() || 
            (now.getMonth() === lahir.getMonth() && now.getDate() < lahir.getDate())) thn--;
        el.value = thn >= 0 ? `${thn} tahun` : '';
    }

    document.getElementById('tgl_masuk')?.addEventListener('change', hitungMasaKerja);
    document.getElementById('tgl_lahir')?.addEventListener('change', hitungUsia);
    hitungMasaKerja();
    hitungUsia();

    console.log('[Profile Create] Inisialisasi selesai');
});

document.addEventListener('DOMContentLoaded', function () {
    const statusSelect     = document.getElementById('status_karyawan');
    const tanggalLabel     = document.getElementById('tanggal_label');
    const tanggalKeterangan = document.getElementById('tanggal_keterangan');
    const tglMasukInput    = document.getElementById('tgl_masuk');

    // Fungsi untuk update label dan keterangan berdasarkan status
    function updateTanggalField() {
    const status = document.getElementById('status_karyawan').value.trim();

    let labelText = 'Tanggal Mulai Magang / Masuk';
    let keteranganText = '(isi tanggal mulai magang jika status Magang)';

    switch (status) {
        case 'Magang':
            labelText = 'Tanggal Mulai Magang';
            keteranganText = '(tanggal mulai periode magang)';
            break;
        case 'Non-Aktif':
            labelText = 'Tanggal Non-Aktif';
            keteranganText = '(tanggal status menjadi non-aktif)';
            break;
        case 'Resign':
            labelText = 'Tanggal Resign';
            keteranganText = '(tanggal pengunduran diri)';
            break;
        default:
            labelText = 'Tanggal Masuk / Aktif';
            keteranganText = '(tanggal resmi menjadi karyawan aktif)';
            break;
    }

    document.getElementById('tanggal_label').textContent = labelText;
    document.getElementById('tanggal_keterangan').textContent = keteranganText;
}

// Panggil saat load dan saat status berubah
document.getElementById('status_karyawan').addEventListener('change', updateTanggalField);
updateTanggalField();


    // Update setiap kali status berubah
    statusSelect.addEventListener('change', updateTanggalField);

    // Hitung masa kerja (kode lama kamu tetap)
    function hitungMasaKerja() {
        const tglInput = document.getElementById('tgl_masuk');
        const masaInput = document.getElementById('masa_kerja');

        if (!tglInput?.value) {
            masaInput.value = '';
            return;
        }

        const masuk = new Date(tglInput.value);
        const sekarang = new Date();

        let bulan = (sekarang.getFullYear() - masuk.getFullYear()) * 12
                    + sekarang.getMonth() - masuk.getMonth();

        if (sekarang.getDate() < masuk.getDate()) bulan--;

        masaInput.value = bulan >= 0 ? `${bulan} bulan` : '';
    }

    document.getElementById('tgl_masuk')?.addEventListener('change', hitungMasaKerja);
    hitungMasaKerja();
});
</script>