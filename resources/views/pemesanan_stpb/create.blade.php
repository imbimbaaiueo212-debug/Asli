@extends('layouts.app')

@section('title', 'Tambah Pemesanan STPB')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Tambah Pemesanan STPB
                    </h4>
                </div>

                <div class="card-body p-4">
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
                            Hubungi admin untuk mengatur unit di profile agar bisa menambah pemesanan STPB.
                        </div>
                    @endif

                    <form action="{{ route('pemesanan_stpb.store') }}" method="POST" id="formPemesanan">
                        @csrf

                        <!-- Hidden Unit (otomatis untuk non-admin) -->
                        @if (!$isAdmin)
                            <input type="hidden" name="unit_id" id="unit_id" value="{{ old('unit_id', $defaultUnitId) }}" required>
                        @endif

                        <!-- Unit biMBA – HANYA UNTUK ADMIN -->
                        @if ($isAdmin)
                            <div class="mb-4">
                                <label class="form-label fw-bold">Unit biMBA <span class="text-danger">*</span></label>
                                <select name="unit_id" id="unit_id" class="form-select" required>
                                    <option value="">-- Pilih Unit --</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ $defaultUnitId == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->biMBA_unit }} - {{ $unit->no_cabang }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <!-- Non-admin: tampilkan unit sebagai info saja -->
                            @if ($defaultUnitId)
                                <div class="alert alert-info mb-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <strong>Unit Anda:</strong> {{ $unitDisplay }}
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <span class="badge bg-primary">Siswa Keluar Only</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <!-- Pilih Siswa -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Pilih Siswa <span class="text-danger">*</span></label>
                            <select name="siswa_id" id="siswa_id" class="form-select" required>
                                <option value="">-- Pilih Siswa --</option>
                            </select>
                            <div class="text-muted small mt-1">
                                Pilih siswa yang sudah keluar dari unit Anda ({{ $unitDisplay ?? 'tidak terdeteksi' }}).
                            </div>
                        </div>

                        <!-- Informasi Siswa -->
                        <h5 class="mb-3 text-primary border-bottom pb-2">
                            <i class="fas fa-user-graduate me-2"></i>Informasi Siswa
                        </h5>

                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">NIM</label><input type="text" name="nim" id="nim" class="form-control" readonly></div>
                            <div class="col-md-6"><label class="form-label">Nama Murid</label><input type="text" name="nama_murid" id="nama_murid" class="form-control" readonly></div>
                            <div class="col-md-4"><label class="form-label fw-bold">Tanggal Masuk</label><input type="date" name="tgl_masuk" id="tgl_masuk" class="form-control" readonly><small class="text-muted">Dari buku induk</small></div>
                            <div class="col-md-4"><label class="form-label">Kelas</label><input type="text" name="kelas" id="kelas" class="form-control" readonly></div>
                            <div class="col-md-4"><label class="form-label">Tempat Lahir</label><input type="text" name="tmpt_lahir" id="tmpt_lahir" class="form-control" readonly></div>
                            <div class="col-md-4"><label class="form-label">Tanggal Lahir</label><input type="date" name="tgl_lahir" id="tgl_lahir" class="form-control" readonly></div>
                            <div class="col-md-4"><label class="form-label">Nama Orang Tua</label><input type="text" name="nama_orang_tua" id="nama_orang_tua" class="form-control" readonly></div>
                            <div class="col-md-4"><label class="form-label fw-bold">Tanggal Lulus</label><input type="date" name="tgl_lulus" id="tgl_lulus" class="form-control" readonly><small class="text-muted">Dari tanggal keluar</small></div>
                        </div>

                        <!-- Informasi Pemesanan STPB -->
                        <h5 class="mb-3 mt-5 text-primary border-bottom pb-2">
                            <i class="fas fa-book me-2"></i>Informasi Pemesanan STPB
                        </h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Level <span class="text-danger">*</span></label>
                                <input type="text" name="level" id="level" class="form-control @error('level') is-invalid @enderror"
                                       value="{{ old('level') }}" placeholder="contoh: B1A, T2, M3B" required>
                                @error('level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Otomatis terisi dari tahap siswa jika ada</small>
                            </div>

                            <!-- Tanggal Pemesanan (manual) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Pemesanan <span class="text-danger">*</span></label>
                                <input type="date" name="tgl_pemesanan" id="tgl_pemesanan" class="form-control @error('tgl_pemesanan') is-invalid @enderror"
                                       value="{{ old('tgl_pemesanan', now()->format('Y-m-d')) }}" required>
                                @error('tgl_pemesanan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Default hari ini: {{ now()->translatedFormat('d F Y') }}</small>
                            </div>

                            <!-- Minggu dalam Bulan (otomatis & real-time) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Minggu dalam Bulan</label>
                                <div id="display_minggu" class="form-control bg-success text-white fw-bold text-center py-3" style="font-size: 1.5rem;">
                                    MINGGU KE-{{ ceil(now()->day / 7) }}
                                </div>
                                <small class="text-muted">Otomatis dihitung: 1 bulan selalu dibagi 5 minggu</small>
                            </div>

                            <!-- Hidden field untuk simpan minggu otomatis -->
                            <input type="hidden" name="minggu" id="hidden_minggu" value="{{ ceil(now()->day / 7) }}">
                        </div>

                        <div class="mb-4 mt-4">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3">{{ old('keterangan') }}</textarea>
                            @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Tombol -->
                        <div class="d-flex justify-content-end gap-3 pt-4 border-top">
                            <a href="{{ route('pemesanan_stpb.index') }}" class="btn btn-secondary btn-lg px-5">
                                <i class="fas fa-arrow-left me-2"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-success btn-lg px-5" id="submitBtn"
                                    {{ !$defaultUnitId && !$isAdmin ? 'disabled' : '' }}>
                                <i class="fas fa-save me-2"></i> Simpan Pemesanan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Hitung minggu dari tanggal
    function hitungMinggu(tanggal) {
        if (!tanggal) return 1;
        const hari = new Date(tanggal).getDate();
        return Math.ceil(hari / 7);
    }

    // Update display + hidden field
    function updateMinggu() {
        const tgl = $('#tgl_pemesanan').val();
        const minggu = hitungMinggu(tgl);
        $('#display_minggu').text(`MINGGU KE-${minggu}`);
        $('#hidden_minggu').val(minggu);
    }

    // Event change tanggal
    $('#tgl_pemesanan').on('change', updateMinggu);

    // Inisialisasi
    updateMinggu();

    // === AJAX Siswa (otomatis load sesuai unit profile untuk non-admin) ===
    function clearFields() {
        $('#nim, #nama_murid, #tmpt_lahir, #nama_orang_tua, #kelas, #tgl_lahir, #tgl_masuk, #tgl_lulus, #level').val('');
    }

    function loadSiswa() {
        const unitId = $('#unit_id').val() || '{{ $defaultUnitId ?? "" }}';

        if (!unitId) {
            $('#siswa_id').html('<option value="">-- Unit tidak terdeteksi --</option>');
            clearFields();
            return;
        }

        $.ajax({
            url: '{{ route('pemesanan_stpb.siswa_by_unit') }}',
            type: 'GET',
            data: { unit_id: unitId },
            success: function(data) {
                let options = '<option value="">-- Pilih Siswa --</option>';
                if (data.length === 0) {
                    options += '<option disabled>- Tidak ada siswa keluar -</option>';
                } else {
                    data.forEach(siswa => {
                        options += `<option value="${siswa.id}">${siswa.nim} - ${siswa.nama} <span class="badge bg-danger ms-2">Keluar</span></option>`;
                    });
                }
                $('#siswa_id').html(options);
                clearFields();
            },
            error: function() {
                $('#siswa_id').html('<option>Gagal memuat siswa</option>');
            }
        });
    }

    // Load siswa otomatis saat halaman dibuka
    loadSiswa();

    // Saat pilih siswa
    $('#siswa_id').on('change', function() {
        const siswaId = $(this).val();
        if (!siswaId) { clearFields(); return; }

        const unitId = $('#unit_id').val() || '{{ $defaultUnitId ?? "" }}';
        $.get('{{ route('pemesanan_stpb.siswa_by_unit') }}', { unit_id: unitId }, function(data) {
            const siswa = data.find(s => s.id == siswaId);
            if (siswa) {
                $('#nim').val(siswa.nim || '');
                $('#nama_murid').val(siswa.nama || '');
                $('#tmpt_lahir').val(siswa.tmpt_lahir || '');
                $('#nama_orang_tua').val(siswa.nama_orang_tua || '');
                $('#kelas').val(siswa.kelas || '');
                $('#tgl_lahir').val(siswa.tgl_lahir || '');
                $('#tgl_masuk').val(siswa.tgl_masuk || '');
                $('#tgl_lulus').val(siswa.tgl_lulus || '');
                if (siswa.tahap && !$('#level').val().trim()) $('#level').val(siswa.tahap);
            }
        });
    });
});
</script>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection