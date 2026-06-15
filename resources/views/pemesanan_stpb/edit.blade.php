@extends('layouts.app')

@section('title', 'Edit Pemesanan STPB')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Edit Pemesanan STPB
                    </h4>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('pemesanan_stpb.update', $order->id) }}" method="POST" id="formEditPemesanan">
                        @csrf
                        @method('PUT')

                        <!-- Unit biMBA -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Unit biMBA</label>
                            @if(auth()->user()->isAdminUser())
                                <select name="unit_id" id="unit_id" class="form-select" required>
                                    <option value="">-- Pilih Unit --</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ old('unit_id', $order->unit_id) == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->biMBA_unit }} - {{ $unit->no_cabang }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="form-control" 
                                       value="{{ $order->unit?->biMBA_unit ?? 'Tidak terdeteksi' }}" readonly>
                                <input type="hidden" name="unit_id" value="{{ $order->unit_id }}">
                            @endif
                        </div>

                        <!-- Pilih Siswa (opsional ganti) -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Pilih Siswa (jika ingin ganti)</label>
                            <select name="siswa_id" id="siswa_id" class="form-select">
                                <option value="">-- Kosongkan untuk tetap pakai data saat ini --</option>
                            </select>
                            <small class="text-muted">Biarkan kosong jika tidak ingin mengganti data siswa</small>
                        </div>

                        <!-- Informasi Siswa -->
                        <h5 class="mb-3 text-primary border-bottom pb-2">
                            <i class="fas fa-user-graduate me-2"></i>Informasi Siswa
                        </h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">NIM</label>
                                <input type="text" name="nim" id="nim" class="form-control" 
                                       value="{{ old('nim', $order->nim) }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Murid</label>
                                <input type="text" name="nama_murid" id="nama_murid" class="form-control" 
                                       value="{{ old('nama_murid', $order->nama_murid) }}" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Tanggal Masuk</label>
                                <input type="date" name="tgl_masuk" id="tgl_masuk" class="form-control" 
                                       value="{{ old('tgl_masuk', $order->tgl_masuk?->format('Y-m-d')) }}" readonly>
                                <small class="text-muted">Diambil otomatis dari buku induk (kosong jika invalid)</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Kelas</label>
                                <input type="text" name="kelas" id="kelas" class="form-control" 
                                       value="{{ old('kelas', $order->kelas) }}" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Tempat Lahir</label>
                                <input type="text" name="tmpt_lahir" id="tmpt_lahir" class="form-control" 
                                       value="{{ old('tmpt_lahir', $order->tmpt_lahir) }}" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="date" name="tgl_lahir" id="tgl_lahir" class="form-control" 
                                       value="{{ old('tgl_lahir', $order->tgl_lahir?->format('Y-m-d')) }}" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Nama Orang Tua</label>
                                <input type="text" name="nama_orang_tua" id="nama_orang_tua" class="form-control" 
                                       value="{{ old('nama_orang_tua', $order->nama_orang_tua) }}" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Tanggal Lulus</label>
                                <input type="date" name="tgl_lulus" id="tgl_lulus" class="form-control" 
                                       value="{{ old('tgl_lulus', $order->tgl_lulus?->format('Y-m-d')) }}" readonly>
                                <small class="text-muted">Diambil otomatis dari tanggal keluar (kosong jika invalid)</small>
                            </div>
                        </div>

                        <!-- Informasi Pemesanan STPB -->
                        <h5 class="mb-3 mt-5 text-primary border-bottom pb-2">
                            <i class="fas fa-book me-2"></i>Informasi Pemesanan STPB
                        </h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Level <span class="text-danger">*</span></label>
                                <input type="text" name="level" id="level" class="form-control @error('level') is-invalid @enderror"
                                       value="{{ old('level', $order->level) }}" required>
                                @error('level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tanggal Pemesanan (bisa diubah) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Pemesanan <span class="text-danger">*</span></label>
                                <input type="date" name="tgl_pemesanan" id="tgl_pemesanan" 
                                       class="form-control @error('tgl_pemesanan') is-invalid @enderror"
                                       value="{{ old('tgl_pemesanan', $order->tgl_pemesanan?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                                @error('tgl_pemesanan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Ubah untuk mengupdate minggu otomatis</small>
                            </div>

                            <!-- Minggu dalam Bulan (otomatis) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Minggu dalam Bulan</label>
                                <div id="display_minggu" class="form-control bg-success text-white fw-bold text-center py-3" style="font-size: 1.5rem;">
                                    MINGGU KE-{{ $order->minggu_dalam_bulan }}
                                </div>
                                <small class="text-muted">Otomatis dihitung (1 bulan = 5 minggu)</small>
                            </div>

                            <!-- Hidden field untuk update minggu -->
                            <input type="hidden" name="minggu" id="hidden_minggu" value="{{ $order->minggu_dalam_bulan }}">
                        </div>

                        <div class="mb-4 mt-4">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3">{{ old('keterangan', $order->keterangan) }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="d-flex justify-content-end gap-3 pt-4 border-top">
                            <a href="{{ route('pemesanan_stpb.index') }}" class="btn btn-secondary btn-lg px-5">
                                <i class="fas fa-arrow-left me-2"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-warning btn-lg px-5 text-dark">
                                <i class="fas fa-save me-2"></i> Update Pemesanan
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
    // Hitung minggu
    function hitungMinggu(tanggal) {
        if (!tanggal) return 1;
        const hari = new Date(tanggal).getDate();
        return Math.ceil(hari / 7);
    }

    // Update display & hidden
    function updateMinggu() {
        const tgl = $('#tgl_pemesanan').val();
        const minggu = hitungMinggu(tgl);
        $('#display_minggu').text(`MINGGU KE-${minggu}`);
        $('#hidden_minggu').val(minggu);
    }

    $('#tgl_pemesanan').on('change', updateMinggu);
    updateMinggu(); // Init

    // AJAX load siswa
    function loadSiswa(unitId) {
        if (!unitId) return;
        $.ajax({
            url: '{{ route('pemesanan_stpb.siswa_by_unit') }}',
            type: 'GET',
            data: { unit_id: unitId },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(data) {
                let options = '<option value="">-- Kosongkan untuk tetap --</option>';
                data.forEach(siswa => {
                    options += `<option value="${siswa.id}">${siswa.nim} - ${siswa.nama}</option>`;
                });
                $('#siswa_id').html(options);
            }
        });
    }

    loadSiswa($('#unit_id').val() || {{ $order->unit_id }});

    $('#unit_id').on('change', function() {
        loadSiswa($(this).val());
    });

    $('#siswa_id').on('change', function() {
        const siswaId = $(this).val();
        if (!siswaId) return;

        const unitId = $('#unit_id').val();
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
                if (!$('#level').val().trim()) $('#level').val(siswa.tahap || '');
            }
        });
    });
});
</script>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection 