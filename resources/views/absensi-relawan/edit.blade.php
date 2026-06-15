@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-primary fw-bold">Edit Absensi Relawan</h2>
    <a href="{{ route('absensi-relawan.index') }}" class="btn btn-secondary mb-3">
        Kembali ke Daftar Absensi
    </a>

    @if($errors->any())
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
        <form action="{{ route('absensi-relawan.update', $absensiRelawan->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <!-- KOLOM KIRI -->
                <div class="col-lg-6">

                    <!-- Nama Relawan (readonly + info lengkap) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Relawan</label>
                        <input type="text" class="form-control bg-light" 
                               value="{{ $absensiRelawan->nama_relawaan }} (NIK: {{ $absensiRelawan->nik }})" readonly>
                        <input type="hidden" name="nama_relawaan" value="{{ $absensiRelawan->nama_relawaan }}">
                    </div>

                    <!-- Unit biMBA - BISA DIUBAH! -->
                    <div class="mb-3">
                        <label class="form-label text-primary fw-bold">
                            Unit biMBA <span class="text-danger">*</span>
                        </label>
                        <select name="bimba_unit" id="bimba_unit" class="form-select fw-bold" required>
                            <option value="">-- Pilih Unit biMBA --</option>
                            @foreach($units as $value => $label)
                                <option value="{{ $value }}"
                                    {{ old('bimba_unit', $absensiRelawan->bimba_unit) == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-primary">Contoh: 01045 - SAPTA TARUNA IV</div>
                    </div>

                    <!-- No Cabang - Otomatis dari unit -->
                    <div class="mb-3">
                        <label class="form-label">No Cabang</label>
                        <input type="text" name="no_cabang" id="no_cabang" class="form-control text-center fw-bold"
                               value="{{ old('no_cabang', $absensiRelawan->no_cabang) }}"
                               readonly style="background-color:#f0f8ff;">
                        <small class="text-muted">Otomatis terisi dari unit</small>
                    </div>

                    <!-- Jabatan / Posisi -->
                    <div class="mb-3">
                        <label class="form-label">Jabatan / Posisi</label>
                        <input type="text" name="posisi" class="form-control" 
                               value="{{ old('posisi', $absensiRelawan->posisi) }}" readonly>
                    </div>

                    <!-- Status Relawan -->
                    <div class="mb-3">
                        <label class="form-label">Status Relawan</label>
                        <input type="text" name="status_relawaan" class="form-control"
                               value="{{ old('status_relawaan', $absensiRelawan->status_relawaan) }}" readonly>
                    </div>

                </div>

                <!-- KOLOM KANAN -->
                <div class="col-lg-6">

                    <!-- Departemen -->
                    <div class="mb-3">
                        <label class="form-label">Departemen</label>
                        <input type="text" name="departemen" class="form-control"
                               value="{{ old('departemen', $absensiRelawan->departemen) }}" readonly>
                    </div>

                    <!-- Tanggal Absensi -->
                    <div class="mb-3">
                        <label class="form-label">Tanggal Absensi <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control"
                               value="{{ old('tanggal', \Carbon\Carbon::parse($absensiRelawan->tanggal ?? today())->format('Y-m-d')) }}">
                    </div>

                    <!-- Jenis Absensi -->
                    <div class="mb-3">
                        <label class="form-label">Jenis Absensi <span class="text-danger">*</span></label>
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
                                <option value="{{ $abs }}"
                                    {{ old('absensi', $absensiRelawan->absensi) == $abs ? 'selected' : '' }}>
                                    {{ $abs }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Sistem (otomatis) -->
                    <div class="mb-3">
                        <label class="form-label text-success fw-bold">Status Sistem</label>
                        <input type="text" id="status" class="form-control text-center fw-bold"
                               value="{{ $absensiRelawan->status }}" readonly
                               style="background-color:#d4edda; color:#155724;">
                        <small class="text-muted">Diperbarui otomatis saat simpan</small>
                    </div>

                    <!-- Keterangan -->
                    <div class="mb-3">
                        <label class="form-label">Keterangan (Opsional)</label>
                        <textarea name="keterangan" id="keterangan" class="form-control" rows="3"
                                  placeholder="Tulis keterangan jika perlu...">{{ old('keterangan', $absensiRelawan->keterangan) }}</textarea>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-3 justify-content-end">
                <button type="submit" class="btn btn-success btn-lg px-5">
                    Update Absensi
                </button>
                <a href="{{ route('absensi-relawan.index') }}" class="btn btn-secondary btn-lg px-4">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Data no_cabang dari controller
const unitNoCabang = @json($unitNoCabang);

document.addEventListener('DOMContentLoaded', function () {
    const unitSelect    = document.getElementById('bimba_unit');
    const noCabangInput = document.getElementById('no_cabang');
    const absensiSelect = document.getElementById('absensi');
    const statusInput   = document.getElementById('status');

    // Update no_cabang saat unit berubah
    function updateNoCabang() {
        noCabangInput.value = unitNoCabang[unitSelect.value] || '';
    }

    // Update status sistem berdasarkan absensi
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
        statusInput.value = map[val] || 'Hadir';
    }

    // Event listeners
    unitSelect.addEventListener('change', updateNoCabang);
    absensiSelect.addEventListener('change', updateStatusSistem);

    // Jalankan saat halaman dibuka
    updateNoCabang();
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
.form-control[readonly], .form-select[readonly] {
    background-color: #e9ecef !important;
    opacity: 0.8;
}
</style>
@endsection