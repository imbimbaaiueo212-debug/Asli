{{-- resources/views/rekap-progresif/edit.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <h3 class="mb-4 text-primary">
                <i class="fas fa-edit"></i> Edit Data Rekap Progresif
            </h3>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('rekap-progresif.update', $rekap_progresif) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            {{-- DATA UTAMA (readonly) --}}
                            <div class="col-12">
                                <h5 class="text-secondary border-bottom pb-2">Informasi Karyawan</h5>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nama</label>
                                <input type="text" class="form-control bg-light" value="{{ $rekap_progresif->nama }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jabatan</label>
                                <input type="text" class="form-control bg-light" value="{{ $rekap_progresif->jabatan }}" readonly>
                            </div>

                            {{-- 🔹 TAMBAHAN: biMBA UNIT & NO CABANG --}}
                            <div class="col-md-6">
                                <label class="form-label">biMBA Unit</label>
                                <input type="text"
                                       class="form-control bg-light"
                                       value="{{ $rekap_progresif->bimba_unit ?? $rekap_progresif->biMBA_unit ?? '-' }}"
                                       readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No Cabang</label>
                                <input type="text"
                                       class="form-control bg-light"
                                       value="{{ $rekap_progresif->no_cabang ?? '-' }}"
                                       readonly>
                            </div>
                            {{-- 🔹 END TAMBAHAN --}}

                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control bg-light" value="{{ $rekap_progresif->status }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Departemen</label>
                                <input type="text" class="form-control bg-light" value="{{ $rekap_progresif->departemen }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Masa Kerja</label>
                                <input type="text" class="form-control bg-light" value="{{ $rekap_progresif->masa_kerja }}" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Periode</label>
                                <input type="text" class="form-control bg-light" value="{{ $rekap_progresif->bulan }} {{ $rekap_progresif->tahun }}" readonly>
                            </div>

                            {{-- DATA YANG BISA DIEDIT --}}
                            <div class="col-12 mt-4">
                                <h5 class="text-secondary border-bottom pb-2">Data Keuangan (Bisa Diedit)</h5>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">SPP biMBA</label>
                                <input type="number" name="spp_bimba" class="form-control" value="{{ old('spp_bimba', $rekap_progresif->spp_bimba) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">SPP English</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="spp_english" id="spp_english" class="form-control text-end" 
                                           value="{{ old('spp_english', $rekap_progresif->spp_english ?? 0) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Total FM</label>
                                <input type="number" step="0.01" name="total_fm" class="form-control" 
                                       value="{{ old('total_fm', $rekap_progresif->total_fm) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Progresif</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="progresif" id="progresif" class="form-control text-end fw-bold" 
                                           value="{{ old('progresif', $rekap_progresif->progresif) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Komisi Tambahan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="komisi" id="komisi" class="form-control text-end text-primary" 
                                           value="{{ old('komisi', $rekap_progresif->komisi ?? 0) }}">
                                </div>
                            </div>

                            {{-- TOTAL DIBAYARKAN (OTOMATIS) --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-success fs-4">Total Dibayarkan</label>
                                <div class="input-group">
                                    <span class="input-group-text fs-5">Rp</span>
                                    <input type="number" name="dibayarkan" id="dibayarkan" 
                                           class="form-control fs-4 fw-bold text-success bg-success-subtle text-end" 
                                           value="{{ old('dibayarkan', $rekap_progresif->dibayarkan) }}" readonly>
                                </div>
                                <small class="text-muted">Otomatis = Progresif + SPP English + Komisi</small>
                            </div>
                        </div>

                        <hr class="my-5">

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-success btn-lg px-5">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('rekap-progresif.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JAVASCRIPT: Otomatis hitung Total Dibayarkan --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const progresif   = document.getElementById('progresif');
    const sppEnglish  = document.getElementById('spp_english');
    const komisi      = document.getElementById('komisi');
    const dibayarkan  = document.getElementById('dibayarkan');

    function hitungTotal() {
        const p = parseFloat(progresif.value) || 0;
        const e = parseFloat(sppEnglish.value) || 0;
        const k = parseFloat(komisi.value) || 0;
        const total = p + e + k;
        dibayarkan.value = total;
    }

    [progresif, sppEnglish, komisi].forEach(input => {
        input.addEventListener('input', hitungTotal);
        input.addEventListener('change', hitungTotal);
    });

    hitungTotal();
});
</script>
@endpush
@endsection
