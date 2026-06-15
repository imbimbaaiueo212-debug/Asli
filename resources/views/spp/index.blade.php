@extends('layouts.app')

@section('title', 'Laporan Data SPP')

@section('content')
    <div class="container-fluid">
        <div class="card w-100">
            <div class="card-body">
                <h3 class="mb-4">Laporan Pembayaran SPP</h3>

                {{-- Filter Tanggal --}}
                {{-- Filter Tanggal --}}
<form method="GET" action="{{ route('spp.index') }}" class="card card-body shadow-sm border border-light rounded-3 mb-4">
    <div class="row g-3 align-items-end">

        <!-- Unit -->
        @if (auth()->check() && (auth()->user()->is_admin ?? false))
        <div class="col-12 col-md-2">
            <label class="form-label small text-muted mb-1">Unit</label>
            <select name="bimba_unit" class="form-select form-select-sm">
                @foreach($units as $unit)
                    <option value="{{ $unit->biMBA_unit }}" {{ $filterUnit === $unit->biMBA_unit ? 'selected' : '' }}>
                        {{ $unit->label ?? $unit->biMBA_unit }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <!-- Bulan Awal -->
        <div class="col-12 col-md-2">
            <label class="form-label small text-muted mb-1">Bulan Awal</label>
            <select name="bulan_awal" class="form-select form-select-sm">
                @foreach (['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember'] as $b)
                    <option value="{{ $b }}" {{ $bulanAwal === $b ? 'selected' : '' }}>
                        {{ ucfirst($b) }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Bulan Akhir -->
        <div class="col-12 col-md-2">
            <label class="form-label small text-muted mb-1">Bulan Akhir</label>
            <select name="bulan_akhir" class="form-select form-select-sm">
                @foreach (['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember'] as $b)
                    <option value="{{ $b }}" {{ $bulanAkhir === $b ? 'selected' : '' }}>
                        {{ ucfirst($b) }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Tahun -->
        <div class="col-12 col-md-2">
            <label class="form-label small text-muted mb-1">Tahun</label>
            <input type="number" name="tahun" value="{{ $tahun }}" min="2020" max="{{ now()->year + 5 }}" class="form-control form-control-sm" required>
        </div>

        <!-- Status Bayar (BARU) -->
        <div class="col-12 col-md-2">
            <label class="form-label small text-muted mb-1">Status Bayar</label>
            <select name="status" class="form-select form-select-sm">
                <option value="semua" {{ $statusFilter === 'semua' ? 'selected' : '' }}>Semua</option>
                <option value="sudah_bayar" {{ $statusFilter === 'sudah_bayar' ? 'selected' : '' }}>Sudah Bayar</option>
                <option value="belum_bayar" {{ $statusFilter === 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
            </select>
        </div>

        <!-- Tombol -->
        <div class="col-12 col-md-2 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                <i class="fas fa-filter me-1"></i> Filter
            </button>
            <a href="{{ route('spp.index') }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
        </div>
    </div>
</form>

                {{-- Header Info + Sync Button --}}
                <div class="card card-body shadow-sm border border-light rounded-3 mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">

        <div>
            <h4 class="mb-0 fw-semibold text-dark">
                Daftar Lengkap SPP
            </h4>

            <small class="text-muted">

                <span class="text-success">
                    Sudah Bayar:
                    <strong>
                        Rp {{ number_format($totalSudahBayar ?? 0, 0, ',', '.') }}
                    </strong>
                    ({{ $jumlahSudahBayar ?? 0 }} murid)
                </span>

                <span class="mx-2">|</span>

                <span class="text-danger">
                    Belum Bayar:
                    <strong>
                        Rp {{ number_format($totalBelumBayar ?? 0, 0, ',', '.') }}
                    </strong>
                    ({{ $belumBayar->count() }} murid)
                </span>

            </small>
        </div>

        <button id="btnSync"
                class="btn btn-success btn-sm px-3 py-2 d-flex align-items-center gap-1">
            <span>🔄</span>
            Update SPKB
        </button>

    </div>
</div>

                {{-- TABEL GABUNGAN SEMUA MURID --}}
                <div class="card shadow-sm border border-light rounded-3">
                    <div class="card-header bg-primary text-white fw-semibold">
                        📋 Semua Murid (Sudah & Belum Bayar) — {{ $allSpp->count() }} Data
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover align-middle mb-0" id="allSppTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">NIM</th>
                                        <th class="text-start">NAMA MURID</th>
                                        <th class="text-start">INFO MURID</th>
                                        <th class="text-center">STATUS BAYAR</th>
                                        <th class="text-end">NOMINAL</th>
                                        <th class="text-start">KETERANGAN</th>
                                        <th class="text-center">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($allSpp as $p)
                                        <tr @class([
                                            'table-danger' => $p->status_bayar === 'Belum Bayar',
                                            'table-success' => $p->status_bayar === 'Sudah Bayar',
                                        ])>
                                            <td class="text-center fw-bold">
                                                {{ $p->nim_padded ?? str_pad($p->nim ?? '', 5, '0', STR_PAD_LEFT) }}
                                            </td>
                                            <td class="text-start fw-medium">
                                                {{ $p->nama_murid ?? $p->nama ?? '-' }}
                                            </td>
                                            <td class="text-start small">
                                                <strong>Kelas:</strong> {{ $p->kelas ?? '-' }} | 
                                                <strong>Tahap:</strong> {{ $tahapMapping[$p->nim_padded ?? $p->nim] ?? '-' }}<br>
                                                <strong>Gol:</strong> {{ $p->gol ?? '-' }} | 
                                                <strong>Guru:</strong> {{ $p->guru ?? '-' }}
                                            </td>
                                            <td class="text-center">
                                                @if ($p->status_bayar === 'Sudah Bayar')
                                                    <span class="badge bg-success">✅ Sudah Bayar</span>
                                                @else
                                                    <span class="badge bg-danger">⛔ Belum Bayar</span>
                                                @endif
                                            </td>
                                            <td class="text-end fw-medium">
                                                Rp {{ number_format($p->nilai_bayar ?? 0, 0, ',', '.') }}
                                            </td>
                                            <td class="text-start small">
                                                {{ $p->keterangan ?? $p->deposit_keterangan ?? '-' }}
                                                @if ($p->tanggal && $p->tanggal !== '-')
                                                    <br><small class="text-muted">Bayar: {{ $p->tanggal }} ({{ ucfirst($p->bulan_pakai ?? '-') }})</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($p->status_bayar === 'Belum Bayar')
                                                    @if ($p->sudahIsiForm ?? false)
                                                        <a href="{{ route('spp.surat-keterlambatan', ['nim' => $p->nim_padded]) }}" 
                                                           target="_blank" 
                                                           class="btn btn-sm btn-primary">
                                                            Lihat Surat
                                                        </a>
                                                    @else
                                                        <a href="https://docs.google.com/forms/d/e/1FAIpQLSeaM6e-0kL0ks5eJ_hvSz5JJZDGVGyiq6cfRJa2JZV7Zezb7w/viewform?entry.123456={{ $p->nim_padded }}" 
                                                           target="_blank" 
                                                           class="btn btn-sm btn-warning">
                                                            Isi Form
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted fst-italic">
                                                Tidak ada data ditemukan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#btnSync').click(function() {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Sinkronisasi...');

        $.ajax({
            url: '{{ route("spp.sync-form") }}',
            method: 'GET',
            data: {
                bimba_unit: '{{ $filterUnit ?? "" }}',
                tahun: '{{ $tahun }}'
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                let msg = 'Terjadi kesalahan server';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
            },
            complete: function() {
                $btn.prop('disabled', false).html('🔄 Update SPKB');
            }
        });
    });
});
</script>
@endpush