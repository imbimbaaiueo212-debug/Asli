@extends('layouts.app')

@section('title', 'Tambah Pemesanan Sertifikat')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-11 col-lg-10">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-bottom py-4">
                    <h3 class="mb-0 text-center text-primary fw-bold">
                        <i class="fas fa-certificate me-2"></i>
                        Tambah Pemesanan Sertifikat
                    </h3>
                </div>
                <div class="card-body p-4 p-md-5">

                    @php
                        $isAdmin = auth()->check() && (auth()->user()->is_admin ?? false);
                        $userUnit = auth()->user()->bimba_unit ?? null;
                        $defaultUnitId = null;
                        $unitDisplay = 'Unit belum diatur';

                        if (!$isAdmin && $userUnit) {
                            $unit = \App\Models\Unit::where('biMBA_unit', $userUnit)->first();
                            if ($unit) {
                                $defaultUnitId = $unit->id;
                                $unitDisplay = $unit->no_cabang . ' | ' . strtoupper($unit->biMBA_unit);
                            }
                        }
                    @endphp

                    @if (!$isAdmin && !$defaultUnitId)
                        <div class="alert alert-danger mb-4 text-center">
                            <strong>Unit Anda belum diatur!</strong><br>
                            Hubungi admin untuk mengatur unit di profile agar bisa menambah pemesanan sertifikat.
                        </div>
                    @endif

                    <form action="{{ route('pemesanan_sertifikat.store') }}" method="POST">
                        @csrf

                        <!-- Hidden Unit (otomatis untuk non-admin) -->
                        @if (!$isAdmin)
                            <input type="hidden" name="unit_id" id="unit_id" value="{{ old('unit_id', $defaultUnitId) }}" required>
                        @endif

                        <!-- Unit biMBA – HANYA UNTUK ADMIN -->
                        @if ($isAdmin)
                            <div class="row g-3 align-items-end mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark">Pilih Unit biMBA <span class="text-danger">*</span></label>
                                    <select name="unit_id" id="unit_id" class="form-select" required>
                                        <option value="">-- Pilih Unit --</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->no_cabang }} | {{ strtoupper($unit->biMBA_unit) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-warning mb-0 py-2 border-0 shadow-sm">
                                        <small><i class="fas fa-info-circle me-1"></i> Admin bebas memilih unit manapun.</small>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Non-admin: tampilkan unit sebagai info saja -->
                            @if ($defaultUnitId)
                                <div class="alert alert-info border-0 shadow-sm mb-4">
                                    <div class="row align-items-center text-center text-md-start">
                                        <div class="col-md-8">
                                            <h6 class="mb-0 fw-bold">Unit Anda:</h6>
                                            <span>{{ $unitDisplay }}</span>
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <span class="badge bg-primary">Siswa Aktif Only</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <hr class="opacity-50 mb-4">

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Pilih Siswa <span class="text-danger">*</span></label>
                                <select name="buku_induk_id" id="siswa_id" class="form-select @error('buku_induk_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Siswa --</option>
                                </select>
                                @error('buku_induk_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-success">Tanggal Pemesanan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="date" name="tanggal_pemesanan" id="tanggal_pemesanan" class="form-control" value="{{ old('tanggal_pemesanan', date('Y-m-d')) }}" required onchange="hitungMingguPreview(this.value)">
                                    <span class="input-group-text bg-light text-primary fw-bold" id="preview_minggu_text">Minggu -</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-light p-4 rounded-3 mb-4 border">
                            <h6 class="text-muted text-uppercase mb-3 small fw-bold">Detail Data Siswa</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small text-muted mb-1">NIM</label>
                                    <input type="text" id="nim" class="form-control form-control-sm border-0 bg-white fw-bold text-center" readonly value="-">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small text-muted mb-1">Nama Lengkap</label>
                                    <input type="text" id="nama_murid" class="form-control form-control-sm border-0 bg-white fw-bold" readonly value="-">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-1">Unit Asal</label>
                                    <input type="text" id="bimba_unit" class="form-control form-control-sm border-0 bg-white fw-bold text-primary" readonly value="-">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-1">Tempat Lahir</label>
                                    <input type="text" id="tmpt_lahir" class="form-control form-control-sm border-0 bg-white" readonly value="-">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-1">Tanggal Lahir</label>
                                    <input type="date" id="tgl_lahir" class="form-control form-control-sm border-0 bg-white text-center" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-1">Tanggal Masuk</label>
                                    <input type="date" id="tgl_masuk" class="form-control form-control-sm border-0 bg-white text-center" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4 align-items-end">
                            <div class="col-md-8">
                                <label class="form-label fw-bold text-primary">Level Sertifikat <span class="text-danger">*</span></label>
                                <select name="level" class="form-select form-select-lg border-primary shadow-sm" required>
                                    <option value="">-- Pilih Level Sertifikat --</option>
                                    <option value="Level 1">Level 1</option>
                                    <option value="Level 1 & 2">Level 1 & 2</option>
                                    <option value="Level 1, 2 & 3">Level 1, 2 & 3</option>
                                    <option value="Level 1, 2, 3 & 4">Level 1, 2, 3 & 4</option>
                                    <option value="Level 2">Level 2</option>
                                    <option value="Level 2 & 3">Level 2 & 3</option>
                                    <option value="Level 2, 3 & 4">Level 2, 3 & 4</option>
                                    <option value="Level 3">Level 3</option>
                                    <option value="Level 3 & 4">Level 3 & 4</option>
                                    <option value="Level 4">Level 4</option>
                                    <option value="Membaca Cerita Pendek">Membaca Cerita Pendek</option>
                                    <option value="Menulis Karangan Sederhana">Menulis Karangan Sederhana</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-success">Minggu Ke</label>
                                <input type="text" id="minggu_display" name="minggu_pemesanan" class="form-control form-control-lg text-center fw-bold text-success bg-white border-success" readonly value="-">
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label fw-bold">Keterangan Tambahan</label>
                            <textarea name="keterangan" class="form-control" rows="3" placeholder="Opsional: beri catatan tambahan di sini...">{{ old('keterangan') }}</textarea>
                        </div>

                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 pt-3 border-top">
                            <a href="{{ route('pemesanan_sertifikat.index') }}" class="btn btn-light btn-lg px-4 border">
                                <i class="fas fa-times me-2"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                                <i class="fas fa-save me-2"></i> Simpan Pemesanan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const unitIdInput     = document.getElementById('unit_id'); // hidden atau dropdown
    const siswaSelect     = document.getElementById('siswa_id');
    const nim             = document.getElementById('nim');
    const namaMurid       = document.getElementById('nama_murid');
    const tmptLahir       = document.getElementById('tmpt_lahir');
    const tglLahir        = document.getElementById('tgl_lahir');
    const tglMasuk        = document.getElementById('tgl_masuk');
    const bimbaUnit       = document.getElementById('bimba_unit');
    const mingguDisplay   = document.getElementById('minggu_display');
    const previewMingguText = document.getElementById('preview_minggu_text');

    function updateSiswaInfo() {
        const option = siswaSelect.options[siswaSelect.selectedIndex];
        if (option && option.value) {
            nim.value = option.dataset.nim || '-';
            namaMurid.value = option.dataset.nama || '-';
            tmptLahir.value = option.dataset.tmptLahir || '-';
            tglLahir.value = option.dataset.tglLahir || '';
            tglMasuk.value = option.dataset.tglMasuk || '';
            bimbaUnit.value = option.dataset.bimbaUnit || '-';
        } else {
            nim.value = '-';
            namaMurid.value = '-';
            tmptLahir.value = '-';
            tglLahir.value = '';
            tglMasuk.value = '';
            bimbaUnit.value = '-';
        }
    }

    window.hitungMingguPreview = function(tanggal) {
        if (!tanggal) {
            mingguDisplay.value = '-';
            previewMingguText.textContent = 'Minggu -';
            return;
        }
        const date = new Date(tanggal);
        const day = date.getDate();
        const minggu = Math.min(Math.ceil(day / 7), 5);
        mingguDisplay.value = minggu;
        previewMingguText.textContent = 'Minggu ' + minggu;
    }

    // Load siswa otomatis saat halaman load (pakai unit profile untuk non-admin)
    function loadSiswa() {
        const unitId = unitIdInput ? unitIdInput.value : '{{ $defaultUnitId ?? '' }}';

        if (!unitId) {
            siswaSelect.innerHTML = '<option value="">-- Unit tidak terdeteksi --</option>';
            return;
        }

        fetch(`/pemesanan-sertifikat/siswa-by-unit/${unitId}`)
            .then(response => response.json())
            .then(data => {
                siswaSelect.innerHTML = '<option value="">-- Pilih Siswa --</option>';
                data.forEach(siswa => {
                    const opt = document.createElement('option');
                    opt.value = siswa.id;
                    opt.textContent = `${siswa.nim} - ${siswa.nama}`;
                    opt.dataset.nim = siswa.nim;
                    opt.dataset.nama = siswa.nama;
                    opt.dataset.tmptLahir = siswa.tmpt_lahir || '';
                    opt.dataset.tglLahir = siswa.tgl_lahir || '';
                    opt.dataset.tglMasuk = siswa.tgl_masuk || '';
                    opt.dataset.bimbaUnit = siswa.bimba_unit || '';
                    siswaSelect.appendChild(opt);
                });
            })
            .catch(() => {
                siswaSelect.innerHTML = '<option>Gagal memuat data</option>';
            });
    }

    @if($isAdmin)
        const unitSelect = document.getElementById('unit_id');
        unitSelect.addEventListener('change', function() {
            const unitId = this.value;
            if (!unitId) {
                siswaSelect.innerHTML = '<option value="">-- Pilih Unit dulu --</option>';
                siswaSelect.disabled = true;
                updateSiswaInfo();
                return;
            }

            siswaSelect.innerHTML = '<option>-- Loading... --</option>';
            siswaSelect.disabled = true;

            fetch(`/pemesanan-sertifikat/siswa-by-unit/${unitId}`)
                .then(response => response.json())
                .then(data => {
                    siswaSelect.innerHTML = '<option value="">-- Pilih Siswa --</option>';
                    data.forEach(siswa => {
                        const opt = document.createElement('option');
                        opt.value = siswa.id;
                        opt.textContent = `${siswa.nim} - ${siswa.nama}`;
                        opt.dataset.nim = siswa.nim;
                        opt.dataset.nama = siswa.nama;
                        opt.dataset.tmptLahir = siswa.tmpt_lahir || '';
                        opt.dataset.tglLahir = siswa.tgl_lahir || '';
                        opt.dataset.tglMasuk = siswa.tgl_masuk || '';
                        opt.dataset.bimbaUnit = siswa.bimba_unit || '';
                        siswaSelect.appendChild(opt);
                    });
                    siswaSelect.disabled = false;
                })
                .catch(() => {
                    siswaSelect.innerHTML = '<option>Gagal memuat data</option>';
                });
        });
    @endif

    siswaSelect.addEventListener('change', updateSiswaInfo);
    updateSiswaInfo();
    hitungMingguPreview(document.getElementById('tanggal_pemesanan').value);

    // Load siswa otomatis saat halaman dibuka
    loadSiswa();
});
</script>
@endsection