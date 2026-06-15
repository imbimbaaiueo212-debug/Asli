@extends('layouts.app')

@section('title', 'Edit Pemesanan Sertifikat')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-11 col-lg-10">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-bottom py-4">
                    <h3 class="mb-0 text-center text-warning fw-bold">
                        <i class="fas fa-edit me-2"></i>
                        Edit Pemesanan Sertifikat
                    </h3>
                </div>
                <div class="card-body p-4 p-md-5">

                    <form action="{{ route('pemesanan_sertifikat.update', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Informasi Unit (Admin vs User Biasa) -->
                        @if($isAdmin)
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
                            <div class="alert alert-info border-0 shadow-sm mb-4">
                                <div class="row align-items-center text-center text-md-start">
                                    <div class="col-md-8">
                                        <h6 class="mb-0 fw-bold">Unit Anda:</h6>
                                        <span>{{ auth()->user()->unit?->no_cabang ?? 'N/A' }} | {{ strtoupper(auth()->user()->unit?->biMBA_unit ?? '-') }}</span>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <span class="badge bg-primary">Siswa Aktif Only</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <hr class="opacity-50 mb-4">

                        <!-- Tanggal Pemesanan -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Pemesanan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="date" name="tanggal_pemesanan" id="tanggal_pemesanan" 
                                           class="form-control @error('tanggal_pemesanan') is-invalid @enderror"
                                           value="{{ old('tanggal_pemesanan', $order->tanggal_pemesanan?->format('Y-m-d')) }}" 
                                           required onchange="hitungMingguPreview(this.value)">
                                    <span class="input-group-text bg-light text-primary fw-bold" id="preview_minggu_text">
                                        Minggu {{ $order->minggu }}
                                    </span>
                                </div>
                                @error('tanggal_pemesanan')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="bg-light p-4 rounded-3 mb-4 border">
                            <h6 class="text-muted text-uppercase mb-3 small fw-bold">Detail Data Siswa</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small text-muted mb-1">NIM</label>
                                    <input type="text" id="nim" class="form-control form-control-sm border-0 bg-white fw-bold text-center" 
                                           readonly value="{{ old('nim', $order->nim) }}">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small text-muted mb-1">Nama Lengkap</label>
                                    <input type="text" id="nama_murid" class="form-control form-control-sm border-0 bg-white fw-bold" 
                                           readonly value="{{ old('nama_murid', $order->nama_murid) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-1">Unit Asal</label>
                                    <input type="text" id="bimba_unit" class="form-control form-control-sm border-0 bg-white fw-bold text-primary" 
                                           readonly value="{{ old('bimba_unit', $order->bimba_unit) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-1">Tempat Lahir</label>
                                    <input type="text" id="tmpt_lahir" class="form-control form-control-sm border-0 bg-white" 
                                           readonly value="{{ old('tmpt_lahir', $order->tmpt_lahir ?? '-') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-1">Tanggal Lahir</label>
                                    <input type="date" id="tgl_lahir" class="form-control form-control-sm border-0 bg-white text-center" 
                                           readonly value="{{ old('tgl_lahir', $order->tgl_lahir?->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-1">Tanggal Masuk</label>
                                    <input type="date" id="tgl_masuk" class="form-control form-control-sm border-0 bg-white text-center" 
                                           readonly value="{{ old('tgl_masuk', $order->tgl_masuk?->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-6">
            <label class="form-label small text-muted mb-1">Tanggal Order Lama</label>
            <input type="date" class="form-control form-control-sm border-0 bg-white text-center fw-bold text-secondary" 
                   readonly value="{{ $order->tanggal_pemesanan?->format('Y-m-d') }}">
        </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4 align-items-end">
                            <div class="col-md-8">
                                <label class="form-label fw-bold text-primary">Level Sertifikat <span class="text-danger">*</span></label>
                                <select name="level" class="form-select form-select-lg border-primary shadow-sm" required>
                                    <option value="">-- Pilih Level Sertifikat --</option>
                                    <option value="Level 1" {{ old('level', $order->level) == 'Level 1' ? 'selected' : '' }}>Level 1</option>
                                    <option value="Level 1 & 2" {{ old('level', $order->level) == 'Level 1 & 2' ? 'selected' : '' }}>Level 1 & 2</option>
                                    <option value="Level 1, 2 & 3" {{ old('level', $order->level) == 'Level 1, 2 & 3' ? 'selected' : '' }}>Level 1, 2 & 3</option>
                                    <option value="Level 1, 2, 3 & 4" {{ old('level', $order->level) == 'Level 1, 2, 3 & 4' ? 'selected' : '' }}>Level 1, 2, 3 & 4</option>
                                    <option value="Level 2" {{ old('level', $order->level) == 'Level 2' ? 'selected' : '' }}>Level 2</option>
                                    <option value="Level 2 & 3" {{ old('level', $order->level) == 'Level 2 & 3' ? 'selected' : '' }}>Level 2 & 3</option>
                                    <option value="Level 2, 3 & 4" {{ old('level', $order->level) == 'Level 2, 3 & 4' ? 'selected' : '' }}>Level 2, 3 & 4</option>
                                    <option value="Level 3" {{ old('level', $order->level) == 'Level 3' ? 'selected' : '' }}>Level 3</option>
                                    <option value="Level 3 & 4" {{ old('level', $order->level) == 'Level 3 & 4' ? 'selected' : '' }}>Level 3 & 4</option>
                                    <option value="Level 4" {{ old('level', $order->level) == 'Level 4' ? 'selected' : '' }}>Level 4</option>
                                    <option value="Membaca Cerita Pendek" {{ old('level', $order->level) == 'Membaca Cerita Pendek' ? 'selected' : '' }}>Membaca Cerita Pendek</option>
                                    <option value="Menulis Karangan Sederhana" {{ old('level', $order->level) == 'Menulis Karangan Sederhana' ? 'selected' : '' }}>Menulis Karangan Sederhana</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-success">Minggu Ke</label>
                                <input type="text" id="minggu_display" class="form-control form-control-lg text-center fw-bold text-success bg-white border-success" 
                                       readonly value="{{ $order->minggu }}">
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label fw-bold">Keterangan Tambahan</label>
                            <textarea name="keterangan" class="form-control" rows="3" placeholder="Opsional: beri catatan tambahan di sini...">{{ old('keterangan', $order->keterangan) }}</textarea>
                        </div>

                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 pt-3 border-top">
                            <a href="{{ route('pemesanan_sertifikat.index') }}" class="btn btn-light btn-lg px-4 border">
                                <i class="fas fa-times me-2"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-warning btn-lg px-5 shadow">
                                <i class="fas fa-save me-2"></i> Update Pemesanan
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
    const mingguDisplay = document.getElementById('minggu_display');
    const previewMingguText = document.getElementById('preview_minggu_text');

    window.hitungMingguPreview = function(tanggal) {
        if (!tanggal) {
            mingguDisplay.value = '-';
            if (previewMingguText) previewMingguText.textContent = 'Minggu -';
            return;
        }
        const date = new Date(tanggal);
        const day = date.getDate();
        const minggu = Math.min(Math.ceil(day / 7), 5);
        mingguDisplay.value = minggu;
        if (previewMingguText) previewMingguText.textContent = 'Minggu ' + minggu;
    }

    // Update minggu saat tanggal diubah
    document.getElementById('tanggal_pemesanan').addEventListener('change', function() {
        hitungMingguPreview(this.value);
    });

    // Inisialisasi
    hitungMingguPreview(document.getElementById('tanggal_pemesanan').value);
});
</script>
@endsection