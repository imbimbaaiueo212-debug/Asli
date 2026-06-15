@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-primary fw-bold">Tambah Absensi Relawan</h2>
    <a href="{{ route('absensi-relawan.index') }}" class="btn btn-secondary mb-3">
        Kembali ke Daftar
    </a>

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

    <div class="card glass-card p-4 shadow-lg">
        <form action="{{ route('absensi-relawan.store') }}" method="POST">
            @csrf

            <div class="row g-4">
                <!-- KOLOM KIRI -->
                <div class="col-lg-6">

                    <!-- Nama Relawan -->
                    <div class="mb-3">
                        <label for="nama_relawaan" class="form-label fw-bold">Nama Relawan <span class="text-danger">*</span></label>
                        <select name="nama_relawaan" id="nama_relawaan" class="form-select" required>
                            <option value="">-- Pilih Relawan --</option>
                            @foreach($profiles as $profile)
                                <option value="{{ $profile->nama }}"
                                    data-nik="{{ $profile->nik }}"
                                    data-jabatan="{{ $profile->jabatan }}"
                                    data-status="{{ $profile->status_karyawan }}"
                                    data-departemen="{{ $profile->departemen }}"
                                    data-bimba-unit="{{ $profile->bimba_unit }}"
                                    data-no-cabang="{{ $profile->no_cabang }}">
                                    {{ $profile->nama }} ({{ $profile->nik }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- NIK (readonly) -->
                    <div class="mb-3">
                        <label for="nik" class="form-label">NIK</label>
                        <input type="text" id="nik" class="form-control text-center fw-bold" readonly>
                    </div>

                    <!-- Unit biMBA - DROPDOWN DARI TABEL UNIT -->
                    <div class="mb-3">
                        <label for="bimba_unit" class="form-label text-primary fw-bold">
                            Unit biMBA <span class="text-danger">*</span>
                        </label>
                        <select name="bimba_unit" id="bimba_unit" class="form-select fw-bold" required>
                            <option value="">-- Pilih Unit biMBA --</option>
                            @foreach($units as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="form-text text-primary">Contoh: 01045 - SAPTA TARUNA IV</div>
                    </div>

                    <!-- No Cabang - AUTO FILL -->
                    <div class="mb-3">
                        <label for="no_cabang" class="form-label">No Cabang</label>
                        <input type="text" name="no_cabang" id="no_cabang" class="form-control text-center fw-bold"
                               readonly style="background-color:#f0f8ff;">
                        <small class="text-muted">Otomatis terisi dari unit</small>
                    </div>

                    <!-- Jabatan -->
                    <div class="mb-3">
                        <label for="posisi" class="form-label">Jabatan / Posisi</label>
                        <input type="text" name="posisi" id="posisi" class="form-control" readonly required>
                    </div>

                </div>

                <!-- KOLOM KANAN -->
                <div class="col-lg-6">

                    <!-- Status Karyawan -->
                    <div class="mb-3">
                        <label for="status_relawaan" class="form-label">Status Relawan</label>
                        <input type="text" name="status_relawaan" id="status_relawaan" class="form-control" readonly required>
                    </div>

                    <!-- Departemen -->
                    <div class="mb-3">
                        <label for="departemen" class="form-label">Departemen</label>
                        <input type="text" name="departemen" id="departemen" class="form-control" readonly required>
                    </div>

                    <!-- Tanggal Absensi -->
                    <div class="mb-3">
                        <label for="tanggal" class="form-label">Tanggal Absensi <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', today()->format('Y-m-d')) }}" required>
                    </div>

                    <!-- Jenis Absensi -->
                    <div class="mb-3">
                        <label for="absensi" class="form-label">Jenis Absensi <span class="text-danger">*</span></label>
                        <select name="absensi" id="absensi" class="form-select" required>
                            <option value="">-- Pilih Jenis Absensi --</option>
                            @foreach([
                                'Hadir',
                                'Sakit Dengan Keterangan Dokter',
                                'Sakit Tanpa Keterangan Dokter',
                                'Izin Dengan Form di ACC',
                                'Izin Tanpa Form di ACC',
                                'Tidak Masuk Tanpa Form',
                                'Tidak Mengisi Absensi Mingguan',
                                'Tidak Aktif',
                                'Cuti',
                                'Cuti Melahirkan',
                                'Datang Terlambat',
                                'Pulang Cepat',
                                'Lainnya'
                            ] as $abs)
                                <option value="{{ $abs }}">{{ $abs }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Hasil Mapping -->
                    <div class="mb-3">
                        <label for="status" class="form-label text-success fw-bold">Status Sistem</label>
                        <input type="text" id="status" class="form-control text-center fw-bold" readonly
                               style="background-color:#d4edda; color:#155724;">
                        <small class="text-muted">Otomatis dari jenis absensi</small>
                    </div>

                    <!-- Keterangan -->
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                        <textarea name="keterangan" id="keterangan" class="form-control" rows="3"
                                  placeholder="Contoh: Sakit demam, izin keluarga, dll...">{{ old('keterangan') }}</textarea>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-3 justify-content-end">
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    Simpan Absensi
                </button>
                <a href="{{ route('absensi-relawan.index') }}" class="btn btn-secondary btn-lg px-4">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Data dari Laravel
const unitNoCabang = @json($unitNoCabang);

document.addEventListener('DOMContentLoaded', function () {
    const namaSelect        = document.getElementById('nama_relawaan');
    const nikInput          = document.getElementById('nik');
    const jabatanInput      = document.getElementById('posisi');
    const statusInput       = document.getElementById('status_relawaan');
    const departemenInput   = document.getElementById('departemen');
    const unitSelect        = document.getElementById('bimba_unit');
    const noCabangInput     = document.getElementById('no_cabang');
    const absensiSelect     = document.getElementById('absensi');
    const statusSistemInput = document.getElementById('status');

    // 1. Saat pilih nama → isi otomatis NIK, jabatan, status, departemen, unit, cabang
    function fillFromProfile() {
        const option = namaSelect.selectedOptions[0];
        if (!option || option.value === '') {
            nikInput.value = jabatanInput.value = statusInput.value = departemenInput.value = '';
            unitSelect.value = '';
            noCabangInput.value = '';
            return;
        }

        nikInput.value        = option.dataset.nik || '';
        jabatanInput.value    = option.dataset.jabatan || '';
        statusInput.value     = option.dataset.status || '';
        departemenInput.value = option.dataset.departemen || '';

        // Auto-select unit jika ada di profile
        const unitProfile = option.dataset.bimbaUnit;
        if (unitProfile && unitSelect.querySelector(`option[value="${unitProfile}"]`)) {
            unitSelect.value = unitProfile;
            noCabangInput.value = option.dataset.noCabang || '';
        } else {
            unitSelect.value = '';
            noCabangInput.value = '';
        }
    }

    // 2. Saat pilih unit manual → isi no cabang
    function fillNoCabang() {
        noCabangInput.value = unitNoCabang[unitSelect.value] || '';
    }

    // 3. Mapping absensi → status sistem
    function updateStatusSistem() {
        const val = absensiSelect.value.toLowerCase();
        const map = {
            'hadir'                            : 'Hadir',
            'sakit dengan keterangan dokter'   : 'Sakit',
            'sakit tanpa keterangan dokter'    : 'Izin',
            'izin dengan form di acc'          : 'Izin',
            'izin tanpa form di acc'           : 'Izin',
            'tidak masuk tanpa form'           : 'Alpa',
            'tidak mengisi absensi mingguan'   : 'Alpa',
            'tidak aktif'                      : 'Tidak Aktif',
            'cuti'                             : 'Cuti',
            'cuti melahirkan'                  : 'Cuti',
            'datang terlambat'                 : 'DT',
            'pulang cepat'                     : 'PC',
            'lainnya'                          : 'Lainnya'
        };
        statusSistemInput.value = map[val] || 'Hadir';
    }

    // Event listeners
    namaSelect.addEventListener('change', fillFromProfile);
    unitSelect.addEventListener('change', fillNoCabang);
    absensiSelect.addEventListener('change', updateStatusSistem);

    // Jalankan saat pertama kali load
    fillFromProfile();
    fillNoCabang();
    updateStatusSistem();
});
</script>

<style>
.glass-card {
    background: rgba(255, 255, 255, 0.25);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}
.form-control[readonly] {
    background-color: #e9ecef !important;
}
</style>
@endsection