@extends('layouts.app')

@section('title', isset($profile) ? 'Edit Profile' : 'Tambah Profile')

@section('content')
<div class="container py-4">
    <h3 class="mb-4 text-primary fw-bold">
        {{ isset($profile) ? 'Edit Profile' : 'Tambah Profile Baru' }}
    </h3>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    

    <form id="profileForm"
          action="{{ isset($profile) ? route('profiles.update', $profile->id) : route('profiles.store') }}"
          method="POST">
        @csrf
        @if(isset($profile)) @method('PUT') @endif

        <div class="row g-4">
            <!-- KOLOM KIRI -->
            <div class="col-lg-6">

                <!-- NIK & Nama -->
                <div class="mb-3">
                    <label class="form-label">NIK <span class="text-danger">*</span></label>
                    <input type="text" name="nik" class="form-control" value="{{ old('nik', $profile->nik ?? '') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control" value="{{ old('nama', $profile->nama ?? '') }}" required>
                </div>

                <!-- JABATAN -->
                <div class="mb-3">
                    <label class="form-label">Jabatan</label>
                    <select name="jabatan" id="jabatan" class="form-select">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach($jabatanOptions as $jab)
                            <option value="{{ $jab }}" {{ old('jabatan', $profile->jabatan ?? '') == $jab ? 'selected' : '' }}>
                                {{ $jab }}
                            </option>
                        @endforeach
                    </select>
                </div>

                

                <!-- PERHITUNGAN RB, KTR, RP – AKAN BERUBAH OTOMATIS SAAT JABATAN DIPILIH -->
                <div class="card border-primary mb-4">
                    <div class="card-header bg-primary text-white">
                        <strong>Perhitungan RB & KTR (Otomatis Berdasarkan Jabatan)</strong>
                    </div>
                    <div class="card-body">
                        @if($isAdmin ?? false)
                            <!-- Hanya admin yang bisa edit -->
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-primary">RB</label>
                                    <select name="rb" id="rb" class="form-select">
                                        <option value="">-- Otomatis / Manual --</option>
                                        @foreach($rbOptions as $rb)
                                            <option value="{{ $rb }}" {{ old('rb', $profile->rb ?? '') == $rb ? 'selected' : '' }}>
                                                {{ $rb }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Kepala Unit → RB40</small>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-info">KTR</label>
                                    <select name="ktr" id="ktr" class="form-select">
                                        <option value="">-- Otomatis / Manual --</option>
                                        @foreach($ktrOptions as $ktrVal)
                                            <option value="{{ $ktrVal }}" {{ old('ktr', $profile->ktr ?? $profile->ktr_tambahan ?? '') == $ktrVal ? 'selected' : '' }}>
                                                {{ $ktrVal }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Kepala Unit → KTR 1B</small>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-success">Imbalan Pokok (RP)</label>
                                    <input type="number" name="rp" id="rp" class="form-control" 
                                        value="{{ old('rp', $profile->rp ?? '') }}" 
                                        placeholder="Otomatis dihitung">
                                    <small class="form-text text-muted">Kepala Unit → Rp 1.350.000</small>
                                </div>
                            </div>

                            <div class="mt-3 alert alert-info small">
                                <strong>Info:</strong> 
                                Untuk <b>Kepala Unit</b>, nilai di atas akan otomatis terisi saat Anda memilih jabatan.<br>
                                Untuk <b>Guru</b>, kosongkan agar sistem hitung otomatis berdasarkan jumlah murid.
                            </div>

                        @else
                            <!-- User biasa: hanya tampil nilai (read-only) -->
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-primary">RB</label>
                                    <div class="form-control bg-light text-muted">
                                        {{ $profile->rb ?: '—' }}
                                    </div>
                                    <!-- Tetap kirim nilai ke server -->
                                    <input type="hidden" name="rb" value="{{ old('rb', $profile->rb ?? '') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-info">KTR</label>
                                    <div class="form-control bg-light text-muted">
                                        {{ $profile->ktr ?: $profile->ktr_tambahan ?: '—' }}
                                    </div>
                                    <input type="hidden" name="ktr" value="{{ old('ktr', $profile->ktr ?? $profile->ktr_tambahan ?? '') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-success">Imbalan Pokok (RP)</label>
                                    <div class="form-control bg-light text-muted">
                                        {{ number_format($profile->rp ?? 0) ?: '—' }}
                                    </div>
                                    <input type="hidden" name="rp" value="{{ old('rp', $profile->rp ?? '') }}">
                                </div>
                            </div>

                            <div class="mt-3 alert alert-warning small">
                                <strong>Perhatian:</strong> Bagian ini hanya dapat diubah oleh Admin.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- STATUS KARYAWAN -->
                <div class="mb-3">
                    <label class="form-label">Status Relawan</label>
                    <select name="status_karyawan" id="status_karyawan" class="form-select">
                        <option value="">-- Pilih Status --</option>
                        @foreach($statusOptions as $status)
                            <option value="{{ $status }}" {{ old('status_karyawan', $profile->status_karyawan ?? '') == $status ? 'selected' : '' }}>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- DEPARTEMEN -->
                <div class="mb-3">
                    <label class="form-label">Departemen</label>
                    <select name="departemen" class="form-select">
                        <option value="">-- Pilih Departemen --</option>
                        @foreach($departemenOptions as $dept)
                            <option value="{{ $dept }}" {{ old('departemen', $profile->departemen ?? '') == $dept ? 'selected' : '' }}>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- UNIT biMBA -->
                <div class="mb-3">
                    <label class="form-label fw-bold text-primary">
                        Unit biMBA <span class="text-danger">*</span>
                    </label>
                    <select name="biMBA_unit" id="biMBA_unit" class="form-select fw-bold @error('biMBA_unit') is-invalid @enderror" required>
                        <option value="">-- Pilih Unit biMBA --</option>
                        @foreach($units as $value => $label)
                            <option value="{{ $value }}" 
                                    {{ old('biMBA_unit', $profile->bimba_unit ?? '') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('biMBA_unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- NO CABANG -->
                <div class="mb-3">
                    <label class="form-label">No Cabang</label>
                    <input type="text" name="no_cabang" id="no_cabang" class="form-control text-center fw-bold"
                           value="{{ old('no_cabang', $profile->no_cabang ?? '') }}" readonly style="background-color:#f8f9fa;">
                </div>

                <!-- TANGGAL MASUK & TANGGAL SELESAI MAGANG -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Masuk Awal</label>
                        <input type="date" name="tgl_masuk" id="tgl_masuk" class="form-control"
                               value="{{ old('tgl_masuk', optional($profile)->tgl_masuk?->format('Y-m-d')) }}">
                        <small class="text-muted">Tanggal pertama kali masuk (tidak akan diubah otomatis)</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Selesai Magang</label>
                        <input type="date" name="tgl_selesai_magang" id="tgl_selesai_magang" class="form-control"
                               value="{{ old('tgl_selesai_magang', optional($profile)->tgl_selesai_magang?->format('Y-m-d')) }}">
                        <small class="text-muted">Jika diisi, masa kerja akan dihitung dari tanggal ini</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-danger">Tanggal Keluar</label>
                        <input type="date" name="tgl_keluar" class="form-control"
                                value="{{ old('tgl_keluar', optional($profile)->tgl_keluar?->format('Y-m-d')) }}">
                        <small class="text-muted">Jika diisi, Status otomatis berubah</small>
                    </div>

                    <!-- Keterangan Keluar -->
                    <div class="col-6">
                        <label class="form-label">Keterangan Keluar</label>
                        <textarea name="keterangan_keluar" class="form-control" rows="3" 
                               placeholder="Masukan Alasan">{{ old('keterangan_keluar', $profile->keterangan_keluar ?? '') }}</textarea>
                    </div>                    
                </div>

                

                <!-- MASA KERJA -->
            <div class="mb-3">
                <label class="form-label">Masa Kerja (Otomatis)</label>

                <input type="text" 
                    id="masa_kerja_display" 
                    class="form-control bg-light fw-bold" 
                    value="{{ isset($profile) ? $profile->masa_kerja_format : '' }}"
                    readonly>

                <input type="hidden" 
                    name="masa_kerja" 
                    id="masa_kerja"
                    value="{{ old('masa_kerja', $profile->masa_kerja ?? 0) }}">
            </div>

            </div>

            <!-- KOLOM KANAN -->
            <div class="col-lg-6">

                <div class="mb-3">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" id="tempat_lahir" 
                        class="form-control" 
                        value="{{ old('tempat_lahir', $profile->tempat_lahir) }}">
                </div>
                <!-- TGL LAHIR & USIA -->
                <div class="mb-3">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tgl_lahir" id="tgl_lahir" class="form-control"
                           value="{{ old('tgl_lahir', $profile->tgl_lahir?->format('Y-m-d')) }}">
                </div>
<div class="mb-3">
    <label class="form-label">Usia</label>

    <input type="text"
        id="usia_display"
        class="form-control bg-light"
        value="{{ old('usia', $profile->usia_format ?? '0 tahun 0 bulan') }}"
        readonly>

    <input type="hidden"
        name="usia"
        id="usia_hidden"
        value="{{ old('usia', $profile->usia_format ?? '0 tahun 0 bulan') }}">
</div>

                <!-- KONTAK -->
                <div class="mb-3">
                    <label class="form-label">No Telepon</label>
                    <input type="text" name="no_telp" class="form-control" value="{{ old('no_telp', $profile->no_telp ?? '') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $profile->email ?? '') }}">
                </div>

                <!-- REKENING -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Bank</label>
                        <input type="text" name="bank" class="form-control" value="{{ old('bank', $profile->bank ?? '') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">No Rekening</label>
                        <input type="text" name="no_rekening" class="form-control" value="{{ old('no_rekening', $profile->no_rekening ?? '') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Atas Nama</label>
                    <input type="text" name="atas_nama" class="form-control" value="{{ old('atas_nama', $profile->atas_nama ?? '') }}">
                </div>

                <!-- DETAIL SERAGAM -->
            <div class="card border-info mt-4">
                <div class="card-header bg-info text-white">
                    <strong>Detail Pengambilan Seragam</strong>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Ambil Seragam</label>
                            <input type="date" name="tgl_ambil_seragam" class="form-control"
                                value="{{ old('tgl_ambil_seragam', $profile->tgl_ambil_seragam?->format('Y-m-d') ?? '') }}">
                            @error('tgl_ambil_seragam')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status_lain" class="form-select">
                                <option value="">-- Pilih Status --</option>
                                <option value="Belum Terima" {{ old('status_lain', $profile->status_lain ?? '') == 'Belum Terima' ? 'selected' : '' }}>Belum Terima</option>
                                <option value="Sudah Terima" {{ old('status_lain', $profile->status_lain ?? '') == 'Sudah Terima' ? 'selected' : '' }}>Sudah Terima</option>
                            </select>
                            @error('status_lain')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan', $profile->keterangan ?? '') }}</textarea>
                            </div>
                        

                    </div>

                    <!-- Ukuran detail seragam (sudah pakai select supaya aman dari error "selected is invalid") -->
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Kaos Kuning-Hitam</label>
                            <select name="kaos_kuning_hitam" class="form-select">
                                <option value="">-</option>
                                @foreach(['S','M','L','XL','XXL'] as $size)
                                    <option value="{{ $size }}" {{ old('kaos_kuning_hitam', $profile->kaos_kuning_hitam ?? '') == $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Kaos Merah-Kuning-Biru</label>
                            <select name="kaos_merah_kuning_biru" class="form-select">
                                <option value="">-</option>
                                @foreach(['S','M','L','XL','XXL'] as $size)
                                    <option value="{{ $size }}" {{ old('kaos_merah_kuning_biru', $profile->kaos_merah_kuning_biru ?? '') == $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Kemeja Kuning-Hitam</label>
                            <select name="kemeja_kuning_hitam" class="form-select">
                                <option value="">-</option>
                                @foreach(['S','M','L','XL','XXL'] as $size)
                                    <option value="{{ $size }}" {{ old('kemeja_kuning_hitam', $profile->kemeja_kuning_hitam ?? '') == $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Blazer Merah</label>
                            <select name="blazer_merah" class="form-select">
                                <option value="">-</option>
                                @foreach(['S','M','L','XL','XXL'] as $size)
                                    <option value="{{ $size }}" {{ old('blazer_merah', $profile->blazer_merah ?? '') == $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mt-3">
                            <label class="form-label">Blazer Biru</label>
                            <select name="blazer_biru" class="form-select">
                                <option value="">-</option>
                                @foreach(['S','M','L','XL','XXL'] as $size)
                                    <option value="{{ $size }}" {{ old('blazer_biru', $profile->blazer_biru ?? '') == $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <small class="form-text text-muted mt-3">
                        Isi ukuran yang diberikan atau biarkan "-" jika belum diterima.
                    </small>
                </div>
            </div>

               <!-- MUTASI & DATA LAIN -->
            <h5 class="mt-4 mb-3 text-primary">Mutasi & Data Lainnya</h5>

            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Mutasi</label>
                    <select name="jenis_mutasi" class="form-select">
                        <option value="">-- Pilih Jenis Mutasi / Jabatan --</option>
                        @foreach ($jabatanOptions as $value => $label)
                            <option value="{{ $value }}"
                                {{ old('jenis_mutasi', $profile->jenis_mutasi ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('jenis_mutasi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Tgl Mutasi Jabatan</label>
                    <input type="date" name="tgl_mutasi_jabatan" id="tgl_mutasi_jabatan" class="form-control"
                        value="{{ old('tgl_mutasi_jabatan', $profile->tgl_mutasi_jabatan?->format('Y-m-d') ?? '') }}">
                    @error('tgl_mutasi_jabatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Masa Kerja Jabatan (bulan)</label>
                    <input type="number" id="masa_kerja_jabatan_preview" class="form-control bg-light" 
                        value="{{ old('masa_kerja_jabatan', $profile->masa_kerja_jabatan ?? 0) }}"
                        readonly disabled>
                    <input type="hidden" name="masa_kerja_jabatan" id="masa_kerja_jabatan_hidden"
                        value="{{ old('masa_kerja_jabatan', $profile->masa_kerja_jabatan ?? 0) }}">
                    <small class="form-text text-muted">
                        Dihitung otomatis dari Tanggal Mutasi Jabatan (live preview)
                    </small>
                    @error('masa_kerja_jabatan')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

                
            </div>
        </div>

        <hr class="my-5">

        <div class="d-flex gap-3 justify-content-start">
            <button type="submit" class="btn btn-primary btn-lg px-5">
                {{ isset($profile) ? 'Update Profile' : 'Simpan Profile' }}
            </button>
            <a href="{{ route('profiles.index') }}" class="btn btn-secondary btn-lg px-4">Batal</a>
        </div>
    </form>
</div>

<script>
// Data dari controller
const unitNoCabang = @json($unitNoCabang ?? []);

document.addEventListener('DOMContentLoaded', function () {

    // ==================== ELEMENT REFERENCES ====================
    const jabatanSelect         = document.getElementById('jabatan');
    const rbSelect              = document.getElementById('rb');
    const ktrSelect             = document.getElementById('ktr');
    const rpInput               = document.getElementById('rp');
    const unitSelect            = document.getElementById('biMBA_unit');
    const noCabangInput         = document.getElementById('no_cabang');
    
    const tglMasuk              = document.getElementById('tgl_masuk');
    const tglSelesaiMagang      = document.getElementById('tgl_selesai_magang');
    const tglLahir              = document.getElementById('tgl_lahir');
    const usiaDisplay           = document.getElementById('usia_display');
    const usiaHidden            = document.getElementById('usia_hidden'); // tambahan

    const masaKerjaDisplay      = document.getElementById('masa_kerja_display');
    const masaKerjaHidden       = document.getElementById('masa_kerja');

    const tglMutasiJabatan      = document.getElementById('tgl_mutasi_jabatan');
    const previewMasaJab        = document.getElementById('masa_kerja_jabatan_preview');
    const hiddenMasaJab         = document.getElementById('masa_kerja_jabatan_hidden');

    const statusSelect          = document.getElementById('status_karyawan');
    const tglKeluar             = document.getElementById('tgl_keluar');
    const tglResign             = document.getElementById('tgl_resign');

    // ==================== AUTO UPDATE STATUS KELUAR ====================
    let originalStatus = statusSelect ? statusSelect.value : '';

    function autoUpdateStatus() {
        if (!statusSelect || !tglKeluar) return;

        if (tglKeluar.value) {
            const keywords = ['resign', 'keluar', 'non aktif', 'nonaktif', 'phk', 'non-aktif'];
            for (let option of statusSelect.options) {
                const text = option.text.toLowerCase();
                if (keywords.some(kw => text.includes(kw))) {
                    statusSelect.value = option.value;
                    return;
                }
            }
        } else {
            if (originalStatus) statusSelect.value = originalStatus;
        }
    }

    // ==================== HITUNG USIA TAHUN & BULAN ====================
function hitungUsia() {
    if (!tglLahir || !usiaDisplay) return;

    const tglValue = tglLahir.value.trim();

    if (!tglValue) {
        usiaDisplay.value = '0 tahun 0 bulan';
        
        if (usiaHidden) {
            usiaHidden.value = '0 tahun 0 bulan';
        }

        return;
    }

    const lahir = new Date(tglValue);

    if (isNaN(lahir.getTime())) {
        usiaDisplay.value = 'Tanggal tidak valid';
        return;
    }

    const sekarang = new Date();

    let tahun = sekarang.getFullYear() - lahir.getFullYear();
    let bulan = sekarang.getMonth() - lahir.getMonth();

    // Jika tanggal sekarang belum melewati tanggal lahir
    if (sekarang.getDate() < lahir.getDate()) {
        bulan--;
    }

    // Jika bulan negatif
    if (bulan < 0) {
        tahun--;
        bulan += 12;
    }

    // Hindari minus
    if (tahun < 0) tahun = 0;
    if (bulan < 0) bulan = 0;

    const hasil = `${tahun} tahun ${bulan} bulan`;

    usiaDisplay.value = hasil;

    if (usiaHidden) {
        usiaHidden.value = hasil;
    }
}

    // ==================== HITUNG MASA KERJA ====================
    function hitungMasaKerja() {
        let startDate = null;

        if (tglSelesaiMagang && tglSelesaiMagang.value) {
            startDate = new Date(tglSelesaiMagang.value);
        } else if (tglMasuk && tglMasuk.value) {
            startDate = new Date(tglMasuk.value);
        }

        if (!startDate || isNaN(startDate.getTime())) {
            if (masaKerjaDisplay) masaKerjaDisplay.value = '-';
            if (masaKerjaHidden) masaKerjaHidden.value = '';
            return;
        }

        const today = new Date();
        startDate.setHours(0, 0, 0, 0);
        today.setHours(0, 0, 0, 0);

        let years = today.getFullYear() - startDate.getFullYear();
        let months = today.getMonth() - startDate.getMonth();

        if (months < 0 || (months === 0 && today.getDate() < startDate.getDate())) {
            months += 12;
            years--;
        }

        let totalMonths = years * 12 + months;
        if (totalMonths < 0) totalMonths = 0;

        let th = Math.floor(totalMonths / 12);
        let bl = totalMonths % 12;

        if (masaKerjaDisplay) masaKerjaDisplay.value = `${th} th ${bl} bl`;
        if (masaKerjaHidden) masaKerjaHidden.value = totalMonths;
    }

    // ==================== UPDATE RB, KTR, RP ====================
    function updateRbKtrRp() {
        const jabatan = (jabatanSelect?.value || '').trim();
        if (jabatan === 'Guru') {
            rbSelect.value = 'RB30';
            ktrSelect.value = 'KTR 1A';
            rpInput.value = '900000';
        } else if (jabatan === 'Kepala Unit') {
            rbSelect.value = 'RB40';
            ktrSelect.value = 'KTR 1B';
            rpInput.value = '1350000';
        } else {
            rbSelect.selectedIndex = 0;
            ktrSelect.selectedIndex = 0;
            rpInput.value = '';
        }
    }

    // ==================== UPDATE NO CABANG ====================
    function updateNoCabang() {
        if (unitSelect && noCabangInput) {
            noCabangInput.value = unitNoCabang[unitSelect.value] || '';
        }
    }

    // ==================== HITUNG MASA KERJA JABATAN ====================
    function hitungMasaKerjaJabatan() {
        if (!tglMutasiJabatan || !tglMutasiJabatan.value || !previewMasaJab || !hiddenMasaJab) return;

        const start = new Date(tglMutasiJabatan.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (start > today) {
            previewMasaJab.value = '0';
            hiddenMasaJab.value = '0';
            return;
        }

        let months = (today.getFullYear() - start.getFullYear()) * 12 + (today.getMonth() - start.getMonth());
        if (today.getDate() < start.getDate()) months--;

        const projected = new Date(start);
        projected.setMonth(projected.getMonth() + months);
        if (Math.floor((today - projected) / (86400000)) >= 15) months++;

        const final = Math.max(0, months);
        previewMasaJab.value = final;
        hiddenMasaJab.value = final;
    }

    // ==================== EVENT LISTENERS ====================
    if (tglLahir) {
        tglLahir.addEventListener('change', hitungUsia);
        tglLahir.addEventListener('input', hitungUsia);
    }
    if (jabatanSelect) jabatanSelect.addEventListener('change', updateRbKtrRp);
    if (unitSelect) unitSelect.addEventListener('change', updateNoCabang);
    if (tglMasuk) tglMasuk.addEventListener('change', hitungMasaKerja);
    if (tglSelesaiMagang) tglSelesaiMagang.addEventListener('change', hitungMasaKerja);
    if (tglMutasiJabatan) tglMutasiJabatan.addEventListener('change', hitungMasaKerjaJabatan);
    if (tglKeluar) tglKeluar.addEventListener('change', autoUpdateStatus);
    if (tglResign) tglResign.addEventListener('change', autoUpdateStatus);

    // ==================== JALANKAN SAAT HALAMAN LOAD (PENTING UNTUK EDIT) ====================
    updateRbKtrRp();
    updateNoCabang();
    hitungMasaKerja();
    hitungMasaKerjaJabatan();
    autoUpdateStatus();

    // Khusus Usia - Jalankan berkali-kali supaya pasti muncul di Edit
    hitungUsia();
    setTimeout(hitungUsia, 100);
    setTimeout(hitungUsia, 400);
    setTimeout(hitungUsia, 800);
    window.addEventListener('load', hitungUsia);

});
</script>
@endsection