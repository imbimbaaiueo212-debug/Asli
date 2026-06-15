@extends('layouts.app')

@section('title', 'Tambah Potongan / Tambahan')

@section('content')
<div class="d-flex flex-column vh-100 bg-light">
    <!-- Header -->
    <div class="bg-white border-bottom shadow-sm flex-shrink-0">
        <div class="d-flex justify-content-between align-items-center px-3 py-3">
            <h5 class="mb-0 fw-bold text-primary">Tambah Potongan / Tambahan</h5>
            <a href="{{ route('adjustments.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-4">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Form Content -->
    <div class="flex-grow-1 overflow-auto bg-light">
        <div class="container-fluid py-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('adjustments.store') }}" method="POST" id="adjustmentForm">
                        @csrf

                        <!-- Pilih Karyawan / Relawan -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Pilih Karyawan/Relawan <span class="text-danger">*</span></label>
                            <select name="nik" id="nik" class="form-select form-select-lg @error('nik') is-invalid @enderror" required>
                                <option value="">-- Pilih NIK / Nama --</option>
                                @foreach($profiles as $profile)
                                    <option value="{{ $profile->nik }}"
                                        data-nama="{{ $profile->nama }}"
                                        data-jabatan="{{ $profile->jabatan ?? '' }}"
                                        data-tanggal-masuk="{{ $profile->tgl_masuk?->format('Y-m-d') ?? '' }}"
                                        data-bimba-unit="{{ $profile->biMBA_unit ?? $profile->bimba_unit ?? $profile->unit ?? '' }}"
                                        data-no-cabang="{{ $profile->no_cabang ?? '' }}"
                                        {{ old('nik') == $profile->nik ? 'selected' : '' }}>
                                        {{ $profile->nik }} - {{ $profile->nama }}
                                        {{ $profile->jabatan ? ' (' . $profile->jabatan . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('nik')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Informasi Karyawan (Auto-filled) -->
                        <div class="card bg-light border-0 mb-4" id="employeeInfo" style="display: none;">
                            <div class="card-body">
                                <div class="row g-3 small">

                                    {{-- BIMBA UNIT & NO CABANG – HANYA MUNcul JIKA ADMIN --}}
                                    @if (auth()->user()->isAdminUser())
                                        <div class="col-md-6">
                                            <label class="text-muted mb-1 fw-medium">BIMBA Unit</label>
                                            <input type="text" id="display_bimba_unit" class="form-control form-control-sm" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="text-muted mb-1 fw-medium">No Cabang</label>
                                            <input type="text" id="display_no_cabang" class="form-control form-control-sm" readonly>
                                        </div>
                                    @endif

                                    <div class="col-12">
                                        <label class="text-muted mb-1 fw-medium">Nama Lengkap</label>
                                        <input type="text" id="display_nama" class="form-control form-control-sm fw-bold" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted mb-1 fw-medium">Jabatan</label>
                                        <input type="text" id="display_jabatan" class="form-control form-control-sm" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted mb-1 fw-medium">Tanggal Masuk</label>
                                        <input type="text" id="display_tgl_masuk" class="form-control form-control-sm" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label class="text-muted mb-1 fw-medium">Masa Kerja</label>
                                        <input type="text" id="display_masa_kerja" class="form-control form-control-sm fw-bold text-success" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden fields (selalu ada untuk simpan ke DB) -->
                        <input type="hidden" name="nama" id="nama">
                        <input type="hidden" name="jabatan" id="jabatan">
                        <input type="hidden" name="tanggal_masuk" id="tanggal_masuk">

                        <!-- Hidden BIMBA UNIT & NO CABANG – tetap ada untuk semua user -->
                        <input type="hidden" name="bimba_unit" id="bimba_unit">
                        <input type="hidden" name="no_cabang" id="no_cabang">

                        <!-- Nominal -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Nominal <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="nominal_input" class="form-control @error('nominal') is-invalid @enderror"
                                       placeholder="0" value="{{ old('nominal') ? number_format(old('nominal'), 0, ',', '.') : '' }}" required>
                                <input type="hidden" name="nominal" id="nominal_hidden" value="{{ old('nominal', 0) }}">
                            </div>
                            @error('nominal')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">Contoh: 300000 atau 1500000</small>
                        </div>

                        <!-- Bulan & Tahun -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bulan <span class="text-danger">*</span></label>
                                <select name="month" class="form-select form-select-lg @error('month') is-invalid @enderror" required>
                                    <option value="">-- Pilih Bulan --</option>
                                    @foreach($months as $key => $name)
                                        <option value="{{ $key }}" {{ old('month') == $key ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('month') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tahun <span class="text-danger">*</span></label>
                                <select name="year" class="form-select form-select-lg @error('year') is-invalid @enderror" required>
                                    <option value="">-- Pilih Tahun --</option>
                                    @foreach($years as $y)
                                        <option value="{{ $y }}" {{ old('year', date('Y')) == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Tipe -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Tipe <span class="text-danger">*</span></label>
                            <select name="type" class="form-select form-select-lg @error('type') is-invalid @enderror" required>
                                <option value="">-- Pilih Tipe --</option>
                                <option value="potongan" {{ old('type') == 'potongan' ? 'selected' : '' }}>Potongan</option>
                                <option value="tambahan" {{ old('type') == 'tambahan' ? 'selected' : '' }}>Tambahan</option>
                            </select>
                            @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Keterangan -->
                        <div class="mb-5">
                            <label class="form-label fw-semibold">Keterangan (opsional)</label>
                            <textarea name="keterangan" rows="4" class="form-control @error('keterangan') is-invalid @enderror"
                                      placeholder="Masukkan keterangan jika ada...">{{ old('keterangan') }}</textarea>
                            @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg rounded-pill shadow-lg" id="submitBtn">
                                <i class="bi bi-save me-2"></i> Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const nikSelect       = document.getElementById('nik');
    const employeeInfo    = document.getElementById('employeeInfo');
    const nominalInput    = document.getElementById('nominal_input');
    const nominalHidden   = document.getElementById('nominal_hidden');

    // Format nominal (ribuan separator)
    function formatRupiah(value) {
        value = value.replace(/\D/g, '');
        return value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    nominalInput.addEventListener('input', function () {
        let rawValue = this.value.replace(/\./g, '');
        this.value = formatRupiah(rawValue);
        nominalHidden.value = rawValue || '0';
    });

    // Trigger jika ada old value
    if (nominalInput.value) {
        nominalInput.dispatchEvent(new Event('input'));
    }

    // Update info karyawan saat pilih NIK
    function updateEmployeeInfo() {
        const selectedOption = nikSelect.options[nikSelect.selectedIndex];

        if (!selectedOption.value) {
            employeeInfo.style.display = 'none';
            return;
        }

        const dataset = selectedOption.dataset;

        // Isi field hidden (selalu ada untuk simpan ke DB)
        document.getElementById('nama').value         = dataset.nama || '';
        document.getElementById('jabatan').value      = dataset.jabatan || '';
        document.getElementById('tanggal_masuk').value = dataset.tanggalMasuk || '';
        document.getElementById('bimba_unit').value   = dataset.bimbaUnit || '';
        document.getElementById('no_cabang').value    = dataset.noCabang || '';

        // Isi tampilan (nama, jabatan, tgl masuk, masa kerja – unit & cabang khusus admin)
        document.getElementById('display_nama').value       = dataset.nama || '-';
        document.getElementById('display_jabatan').value    = dataset.jabatan || '-';

        // Khusus admin: isi display BIMBA Unit & No Cabang
        @if (auth()->user()->isAdminUser())
            document.getElementById('display_bimba_unit').value = dataset.bimbaUnit || '-';
            document.getElementById('display_no_cabang').value  = dataset.noCabang || '-';
        @endif

        // Tanggal masuk & masa kerja
        const tglMasukStr = dataset.tanggalMasuk;
        if (tglMasukStr) {
            const tglMasuk = new Date(tglMasukStr);
            document.getElementById('display_tgl_masuk').value = tglMasuk.toLocaleDateString('id-ID', {
                day: '2-digit', month: 'long', year: 'numeric'
            });

            // Hitung masa kerja
            const now = new Date();
            let years  = now.getFullYear() - tglMasuk.getFullYear();
            let months = now.getMonth() - tglMasuk.getMonth();
            if (months < 0) { years--; months += 12; }
            if (now.getDate() < tglMasuk.getDate()) months--;

            let masa = '';
            if (years > 0) masa += years + ' tahun ';
            if (months >= 0) masa += months + ' bulan';
            document.getElementById('display_masa_kerja').value = masa.trim() || 'Kurang dari 1 bulan';
        } else {
            document.getElementById('display_tgl_masuk').value = '-';
            document.getElementById('display_masa_kerja').value = '-';
        }

        employeeInfo.style.display = 'block';
    }

    nikSelect.addEventListener('change', updateEmployeeInfo);

    // Trigger awal jika ada nilai lama (validation fail)
    if (nikSelect.value) {
        updateEmployeeInfo();
    }
});
</script>
@endsection
