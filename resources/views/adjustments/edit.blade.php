@extends('layouts.app')

@section('title', 'Edit Potongan / Tambahan')

@section('content')
<div class="d-flex flex-column vh-100 bg-light">
    <!-- Header tetap di atas -->
    <div class="bg-white border-bottom shadow-sm flex-shrink-0">
        <div class="d-flex justify-content-between align-items-center px-3 py-3">
            <h5 class="mb-0 fw-bold text-primary">Edit Potongan / Tambahan</h5>
            <a href="{{ route('adjustments.index') }}" class="btn btn-secondary btn-sm rounded-pill px-4">
                Kembali
            </a>
        </div>
    </div>

    <!-- Konten Form - Scrollable Area -->
    <div class="flex-grow-1 overflow-auto bg-light">
        <div class="h-100">
            <div class="card border-0 h-100 mx-0">
                <div class="card-body p-4">
                    <form action="{{ route('adjustments.update', $adjustment) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Pilih Karyawan -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Pilih Karyawan/Relawan <span class="text-danger">*</span></label>
                            <select name="nik" id="nik" class="form-select @error('nik') is-invalid @enderror" required>
                                <option value="">-- Pilih Nama / NIK --</option>
                                @foreach($profiles as $profile)
                                    <option value="{{ $profile->nik }}"
                                        data-nama="{{ $profile->nama }}"
                                        data-jabatan="{{ $profile->jabatan ?? '' }}"
                                        data-tgl-masuk="{{ $profile->tgl_masuk?->format('Y-m-d') }}"
                                        data-bimba-unit="{{ $profile->bimba_unit ?? '' }}"
                                        data-no-cabang="{{ $profile->no_cabang ?? '' }}"
                                        {{ old('nik', $adjustment->nik) == $profile->nik ? 'selected' : '' }}>
                                        {{ $profile->nik }} - {{ $profile->nama }}
                                        {{ $profile->jabatan ? '(' . $profile->jabatan . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Hidden Fields -->
                        <input type="hidden" name="nama" id="nama" value="{{ old('nama', $adjustment->nama) }}">
                        <input type="hidden" name="jabatan" id="jabatan" value="{{ old('jabatan', $adjustment->jabatan) }}">
                        <input type="hidden" name="tanggal_masuk" id="tanggal_masuk" value="{{ old('tanggal_masuk', $adjustment->tanggal_masuk?->format('Y-m-d')) }}">
                        <input type="hidden" name="bimba_unit" id="bimba_unit" value="{{ old('bimba_unit', $adjustment->bimba_unit) }}">
                        <input type="hidden" name="no_cabang" id="no_cabang" value="{{ old('no_cabang', $adjustment->no_cabang) }}">

                        <!-- Info Karyawan Readonly -->
                        <div class="bg-white border rounded-3 p-3 mb-4 shadow-sm">
                            <div class="row g-3 small">
                                <div class="col-6">
                                    <label class="text-muted mb-1">BIMBA Unit</label>
                                    <input type="text" id="display_bimba_unit" class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted mb-1">No Cabang</label>
                                    <input type="text" id="display_no_cabang" class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-12">
                                    <label class="text-muted mb-1">Nama Lengkap</label>
                                    <input type="text" id="display_nama" class="form-control form-control-sm fw-bold" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted mb-1">Jabatan</label>
                                    <input type="text" id="display_jabatan" class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted mb-1">Tanggal Masuk</label>
                                    <input type="text" id="display_tgl_masuk" class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-12">
                                    <label class="text-muted mb-1">Masa Kerja</label>
                                    <input type="text" id="display_masa_kerja" class="form-control form-control-sm fw-bold text-success" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Nominal - BENAR: value kosong, JS yang isi -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Nominal <span class="text-danger">*</span></label>
                            <input type="text" id="nominal_input" class="form-control @error('nominal') is-invalid @enderror"
                                   value="" placeholder="0" required>
                            <input type="hidden" name="nominal" id="nominal_hidden" value="{{ old('nominal', $adjustment->nominal) }}">
                            @error('nominal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Contoh: 300.000 atau 1.500.000</small>
                        </div>

                        <!-- Bulan & Tahun -->
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Bulan <span class="text-danger">*</span></label>
                                <select name="month" class="form-select @error('month') is-invalid @enderror" required>
                                    <option value="">-- Pilih Bulan --</option>
                                    @foreach($months as $key => $name)
                                        <option value="{{ $key }}" {{ old('month', $adjustment->month) == $key ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('month') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Tahun <span class="text-danger">*</span></label>
                                <select name="year" class="form-select @error('year') is-invalid @enderror" required>
                                    <option value="">-- Pilih Tahun --</option>
                                    @foreach($years as $year)
                                        <option value="{{ $year }}" {{ old('year', $adjustment->year) == $year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endforeach
                                </select>
                                @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Tipe -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Tipe <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">-- Pilih Tipe --</option>
                                <option value="potongan" {{ old('type', $adjustment->type) == 'potongan' ? 'selected' : '' }}>Potongan</option>
                                <option value="tambahan" {{ old('type', $adjustment->type) == 'tambahan' ? 'selected' : '' }}>Tambahan</option>
                            </select>
                            @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Keterangan -->
                        <div class="mb-5">
                            <label class="form-label fw-semibold">Keterangan (Opsional)</label>
                            <textarea name="keterangan" rows="5" class="form-control">{{ old('keterangan', $adjustment->keterangan) }}</textarea>
                        </div>

                        <!-- Tombol Update - Sticky di Bawah -->
                        <div class="position-sticky bottom-0 start-0 end-0 bg-light pt-3 border-top">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg rounded-pill shadow">
                                    Update Data
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript: Auto-fill + Format Nominal + Trigger Saat Load -->
<script>
    // Auto-fill karyawan saat pilih NIK
    document.getElementById('nik').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];

        document.getElementById('nama').value = opt.dataset.nama || '';
        document.getElementById('jabatan').value = opt.dataset.jabatan || '';
        document.getElementById('tanggal_masuk').value = opt.dataset.tglMasuk || '';
        document.getElementById('bimba_unit').value = opt.dataset.bimbaUnit || '';
        document.getElementById('no_cabang').value = opt.dataset.noCabang || '';

        document.getElementById('display_nama').value = opt.dataset.nama || '';
        document.getElementById('display_jabatan').value = opt.dataset.jabatan || '';
        document.getElementById('display_bimba_unit').value = opt.dataset.bimbaUnit || '';
        document.getElementById('display_no_cabang').value = opt.dataset.noCabang || '';

        const tglMasuk = opt.dataset.tglMasuk;
        if (tglMasuk) {
            const date = new Date(tglMasuk);
            document.getElementById('display_tgl_masuk').value = date.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });

            const now = new Date();
            let years = now.getFullYear() - date.getFullYear();
            let months = now.getMonth() - date.getMonth();
            if (months < 0) { years--; months += 12; }
            if (now.getDate() < date.getDate()) { months--; if (months < 0) { years--; months = 11; } }

            let masa = '';
            if (years > 0) masa += years + ' tahun ';
            masa += months + ' bulan';
            document.getElementById('display_masa_kerja').value = masa.trim() || '0 bulan';
        } else {
            document.getElementById('display_tgl_masuk').value = '-';
            document.getElementById('display_masa_kerja').value = '-';
        }
    });

    // Format Nominal Rupiah
    const nominalInput = document.getElementById('nominal_input');
    const nominalHidden = document.getElementById('nominal_hidden');

    if (nominalInput && nominalHidden) {
        nominalInput.addEventListener('input', function () {
            let value = this.value.replace(/\./g, '').replace(/[^0-9]/g, '');
            this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            nominalHidden.value = value || '0';
        });
    }

    // Saat halaman load: format nominal + trigger auto-fill karyawan
    document.addEventListener('DOMContentLoaded', function () {
        // Format nominal dari database
        const rawNominal = nominalHidden.value;
        if (rawNominal && rawNominal !== '0') {
            nominalInput.value = parseInt(rawNominal).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // Trigger auto-fill karyawan agar Tanggal Masuk & Masa Kerja muncul
        const nikSelect = document.getElementById('nik');
        if (nikSelect && nikSelect.value) {
            nikSelect.dispatchEvent(new Event('change'));
        }
    });
</script>

<!-- CSS Full Screen Mobile -->
<style>
    @media (max-width: 768px) {
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        .vh-100 { height: 100vh !important; }
        .flex-grow-1.overflow-auto {
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .card {
            margin: 0 !important;
            border-radius: 0 !important;
            height: 100%;
        }
        .card-body { padding: 16px !important; }
        .position-sticky {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
        }
    }
</style>
@endsection