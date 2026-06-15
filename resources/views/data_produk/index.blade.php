@extends('layouts.app')

@section('title', 'Data Produk Rekapitulasi')

@section('content')
<div class="container-fluid py-4">
    <h1 class="mb-4">Data Produk Rekapitulasi Bulanan</h1>

    {{-- Notifikasi Sukses --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filter Periode, Unit, & Produk --}}
    <div class="card p-4 mb-4 shadow-sm">
        <h6 class="card-title mb-3">Filter Rekapitulasi</h6>
        <form method="GET" id="filterForm" class="row g-3 align-items-end">
            <!-- Unit biMBA -->
           <!-- Filter Unit – hanya tampil untuk admin -->
<div class="col-md-3 col-12">
    @if (auth()->user()->isAdminUser())
        <label class="form-label small text-muted">Unit biMBA</label>
        <select name="unit_id" class="form-select unit-select" onchange="this.form.submit()">
            <option value="">-- Semua Unit --</option>
            @foreach($units as $unit)
                <option value="{{ $unit->id }}" {{ $unitId == $unit->id ? 'selected' : '' }}>
                    {{ $unit->no_cabang }} | {{ strtoupper($unit->biMBA_unit) }}
                </option>
            @endforeach
        </select>
    @else
        <!-- Non-admin: tampilkan info unit saja (readonly) -->
        @if($unitId && $units->find($unitId))
            <label class="form-label small text-muted">Unit biMBA Anda</label>
            <input type="text" class="form-control bg-light" 
                   value="{{ $units->find($unitId)->no_cabang }} | {{ strtoupper($units->find($unitId)->biMBA_unit) }}" 
                   readonly>
            <!-- tetap kirim unit_id agar pagination & filter lain tetap jalan -->
            <input type="hidden" name="unit_id" value="{{ $unitId }}">
        @else
            <label class="form-label small text-muted">Unit biMBA</label>
            <input type="text" class="form-control is-invalid" value="Unit Anda belum diatur" readonly>
        @endif
    @endif
</div>

            <!-- Periode -->
            <div class="col-md-3 col-12">
                <label class="form-label small text-muted">Pilih Bulan Periode</label>
                <input type="month" name="periode" class="form-control periode-input" 
                       value="{{ request('periode', now()->format('Y-m')) }}">
            </div>

            <!-- Cari Produk (Dropdown Select2) -->
            <div class="col-md-4 col-12">
                <label class="form-label small text-muted">Cari Produk</label>
                <select name="search" class="form-select produk-select" style="width: 100%;">
                    <option value="">-- Semua Produk --</option>
                    @foreach($produks as $p)
                        <option value="{{ $p->kode }}" {{ request('search') == $p->kode ? 'selected' : '' }}>
                            {{ $p->kode }} - {{ $p->label }} ({{ $p->jenis }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Aksi -->
            <div class="col-md-2 col-12 text-md-end">
                <a href="{{ route('data_produk.index') }}" class="btn btn-secondary">
                    <i class="fas fa-redo me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    @if(isset($message))
        <div class="alert alert-info">
            {{ $message }}
        </div>
    @endif

    {{-- Informasi Periode Aktif --}}
    @php
        $periodeAktif = request('periode') 
            ? \Carbon\Carbon::createFromFormat('Y-m', request('periode')) 
            : now();
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">
            Rekap Stok Bulan: 
            <strong class="text-primary">{{ $periodeAktif->translatedFormat('F Y') }}</strong>
            @if(request('unit_id'))
                <br><small class="text-muted">
                    Unit: <strong>{{ $units->find(request('unit_id'))?->no_cabang ?? 'Semua' }} | 
                    {{ strtoupper($units->find(request('unit_id'))?->biMBA_unit ?? '') }}</strong>
                </small>
            @endif
        </h3>

        <div>
            <button type="button" class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#generateModal">
                <i class="fas fa-file-alt me-1"></i> Generate Template
            </button>

            <a href="{{ route('data_produk.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah Manual
            </a>
        </div>
    </div>

    {{-- Modal Generate Template --}}
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Generate Template Rekap Stok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Aksi ini akan membuat entri kosong untuk <strong>semua produk</strong> pada:</p>
                <div class="alert alert-info text-center py-3">
                    <strong>{{ $periodeAktif->translatedFormat('F Y') }}</strong> 
                    ({{ $periodeAktif->format('Y-m') }})
                </div>

                {{-- Tampilkan unit yang benar --}}
                @php
                    $currentUnitId = $unitId ?? request('unit_id');
                    $currentUnit   = $units->find($currentUnitId);
                @endphp

                @if($currentUnitId && $currentUnit)
                    <p class="text-center mt-3">
                        <strong>Unit: {{ $currentUnit->no_cabang }} | 
                        {{ strtoupper($currentUnit->biMBA_unit) }}</strong>
                    </p>
                @endif

                <ul class="small text-muted mt-3">
                    <li>Produk yang sudah ada akan dilewati.</li>
                    <li>Semua nilai diisi 0 (kecuali min_stok dari master).</li>
                </ul>
            </div>
            <div class="modal-footer">
                <form action="{{ route('data_produk.generate_template') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="periode" value="{{ $periodeAktif->format('Y-m') }}">
                    <input type="hidden" name="unit_id" value="{{ $currentUnitId }}">
                    
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-check me-1"></i> Ya, Generate
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

    @if(!$hasData)
        <div class="card border-info shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-info-circle fa-4x text-info mb-4"></i>
                <h4>Data Rekap Stok Belum Tersedia</h4>
                <p class="text-muted mb-4">
                    Untuk periode <strong>{{ $periodeAktif->translatedFormat('F Y') }}</strong> belum ada data.
                </p>
                <form action="{{ route('data_produk.generate_template') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="periode" value="{{ $periodeAktif->format('Y-m') }}">
                    <input type="hidden" name="unit_id" value="{{ request('unit_id') }}">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-file-alt me-2"></i> Generate Template Sekarang
                    </button>
                </form>
            </div>
        </div>
    @else
        <div class="table-sticky-wrapper table-responsive">
    <table class="table table-bordered table-striped table-hover align-middle text-center" id="rekapTable">
        <thead class="table-light">
            <!-- Baris 1 (Header Utama) -->
            <tr class="sticky-main-header">
                <th rowspan="2">NO</th>
                <th rowspan="2">KODE</th>
                <th rowspan="2">JENIS</th>
                <th rowspan="2">LABEL</th>
                <th rowspan="2">SATUAN</th>
                <th rowspan="2">HARGA (Rp)</th>
                <th rowspan="2">MIN STOK</th>
                
                <th colspan="5" class="bg-primary text-dark text-center sticky-rekap">
                    REKAPITULASI BULANAN
                </th>
                
                <th rowspan="2">OPNAME</th>
                <th rowspan="2">NILAI</th>
                <th rowspan="2">SELISIH</th>
                <th rowspan="2">AKSI</th>
            </tr>
            
            <!-- Baris 2 (Sub Header) -->
            <tr class="sticky-sub-header">
                <th>SLD AWAL</th>
                <th>TERIMA</th>
                <th>PAKAI</th>
                <th>SLD AKHIR</th>
                <th>STATUS</th>
            </tr>
        </thead>
                <tbody>
    @forelse($items as $index => $item)
        <tr>
            <td>{{ $loop->iteration + $items->firstItem() - 1 }}</td>
            <td class="fw-bold">{{ $item->kode }}</td>
            <td class="text-start">{{ $item->jenis }}</td>
            <td class="text-start fw-semibold">{{ $item->label }}</td>
            <td>{{ $item->satuan }}</td>
            <td class="text-end">
                {{ number_format($item->harga, 0, ',', '.') }}
            </td>
            <td class="text-center {{ $item->sld_akhir < $item->min_stok ? 'text-danger' : 'text-success' }} fw-bold">
                {{ $item->min_stok }}
            </td>

            <!-- SLD AWAL -->
            <td class="text-end fw-bold {{ getSaldoColorClass($item->sld_awal, $item->sld_akhir, $item->min_stok) }}">
                {{ number_format($item->sld_awal, 0, ',', '.') }}
            </td>

            <!-- TERIMA -->
            <td class="text-end text-success fw-bold">{{ number_format($item->terima, 0, ',', '.') }}</td>

            <!-- PAKAI -->
            <td class="text-end text-danger fw-bold">{{ number_format($item->pakai, 0, ',', '.') }}</td>

            <!-- SLD AKHIR -->
            <td class="text-end fw-bold {{ getSaldoColorClass($item->sld_akhir, $item->sld_awal, $item->min_stok) }}">
                {{ number_format($item->sld_akhir, 0, ',', '.') }}
            </td>

            <!-- STATUS -->
            <td class="status-cell text-center align-middle fw-bold">
                @if($item->status === 'STOK AMAN')
                    <span class="text-success" title="STOK AMAN">✔️</span>

                @elseif($item->status === 'STOK KURANG')
                    <span class="text-danger" title="STOK KURANG">⚠️</span>

                @elseif($item->status === 'HABIS_TOTAL')
                    <span class="text-dark" title="HABIS TOTAL">❌</span>

                @else
                    <span class="text-muted">-</span>
                @endif
            </td>

            <!-- OPNAME FISIK -->
            <td class="text-end fw-bold">
                {{ number_format($item->opname, 0, ',', '.') }}
            </td>

            <!-- NILAI FISIK -->
            <td class="text-end fw-bold">
                Rp. {{ number_format($item->nilai, 0, ',', '.') }}
            </td>

            <!-- SELISIH FISIK -->
            <td class="text-center">
                @if($item->opname > 0)
                    <div class="fw-bold fs-4 {{ $item->selisih < 0 ? 'text-danger' : ($item->selisih > 0 ? 'text-success' : 'text-secondary') }}">
                        {{ $item->selisih >= 0 ? '+' : '' }}{{ number_format($item->selisih, 0, ',', '.') }}
                    </div>
                    <small class="badge bg-primary text-wrap d-block">
                        @if($item->selisih > 0) Kelebihan Fisik
                        @elseif($item->selisih < 0) Kekurangan Fisik
                        @else Cocok @endif
                    </small>
                @else
                    <span class="text-muted">-</span>
                    <small class="d-block text-muted">Belum opname</small>
                @endif
            </td>

            <!-- SELISIH (kolom ke-16, kalau ada nilai selisih nilai / rupiah) -->
            

            <!-- AKSI -->
<td class="text-nowrap">

    {{-- EDIT --}}
    <a href="{{ route('data_produk.edit', $item->id) }}"
       class="btn btn-sm btn-warning mb-1"
       title="Edit">
        <i class="fas fa-edit"></i>
    </a>

    {{-- ADJUSTMENT --}}
    @if($item->opname > 0)

        <button type="button"
                class="btn btn-sm btn-info mb-1"
                data-bs-toggle="modal"
                data-bs-target="#adjustModal{{ $item->id }}"
                title="Adjustment">
            <i class="fas fa-balance-scale"></i>
        </button>

    @endif

    {{-- DELETE --}}
    @if(auth()->user()?->role === 'admin')

        <form action="{{ route('data_produk.destroy', $item->id) }}"
              method="POST"
              class="d-inline">

            @csrf
            @method('DELETE')

            <button type="submit"
                    class="btn btn-sm btn-danger mb-1"
                    onclick="return confirm('Yakin hapus data ini?')"
                    title="Hapus">
                <i class="fas fa-trash"></i>
            </button>

        </form>

    @endif

</td>
        </tr>
        {{-- MODAL ADJUSTMENT --}}
@if($item->opname > 0)

<div class="modal fade"
     id="adjustModal{{ $item->id }}"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <form action="{{ route('data_produk.adjustment', $item->id) }}"
                  method="POST">

                @csrf

                <div class="modal-header bg-info text-white">

                    <h5 class="modal-title">
                        Adjustment Stok
                    </h5>

                    <button type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <div class="alert alert-warning">

                        <strong>{{ $item->label }}</strong>

                        <hr>

                        <div>
                            Sistem :
                            <strong>{{ $item->sld_akhir }}</strong>
                        </div>

                        <div>
                            Fisik :
                            <strong>{{ $item->opname }}</strong>
                        </div>

                        <div>
                            Selisih :
                            <strong class="{{ $item->selisih < 0 ? 'text-danger' : 'text-success' }}">
                                {{ $item->selisih }}
                            </strong>
                        </div>

                    </div>

                    {{-- JENIS --}}
                    <div class="mb-3">

                        <label class="form-label">
                            Jenis Adjustment
                        </label>

                        <select name="jenis_adjustment"
                                class="form-select"
                                required>

                            <option value="">
                                -- Pilih --
                            </option>

                            <option value="reject">
                                Reject
                            </option>

                            <option value="hilang">
                                Hilang
                            </option>

                            <option value="selip">
                                Selip
                            </option>

                            <option value="rusak">
                                Rusak
                            </option>

                            <option value="ditemukan_kembali">
                                Ditemukan Kembali
                            </option>

                        </select>

                    </div>

                    {{-- JUMLAH --}}
                    <div class="mb-3">

                        <label class="form-label">
                            Jumlah Adjustment
                        </label>

                        <input type="number"
                               name="qty_adjustment"
                               class="form-control"
                               required
                               min="1">

                    </div>

                    {{-- KETERANGAN --}}
                    <div class="mb-3">

                        <label class="form-label">
                            Keterangan
                        </label>

                        <textarea name="keterangan"
                                  class="form-control"
                                  rows="3"></textarea>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Tutup
                    </button>

                    <button type="submit"
                            class="btn btn-info">

                        <i class="fas fa-save me-1"></i>
                        Simpan Adjustment

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endif
    @empty
        <tr>
            <td colspan="17" class="text-center py-5 text-muted">  <!-- ubah colspan jadi 17 -->
                <i class="fas fa-search fa-3x mb-3"></i><br>
                Tidak ada data yang sesuai filter.
            </td>
        </tr>
        
    @endforelse
</tbody>
            </table>
        </div>

        {{ $items->appends(request()->query())->links() }}

        @if($showGenerateButton)
            <div class="alert alert-warning mt-4 text-center">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Beberapa produk belum ada di periode ini. 
                Gunakan <strong>Generate Template</strong> untuk melengkapi.
            </div>
        @endif
    @endif
</div>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<!-- jQuery (HARUS SEBELUM SELECT2) -->
<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Custom Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Inisialisasi Select2
    $('.produk-select').select2({
        theme: 'bootstrap-5',
        placeholder: "Pilih atau cari produk...",
        allowClear: true,
        width: '100%'
    });

    $('.unit-select').select2({
        theme: 'bootstrap-5',
        placeholder: "-- Semua Unit --",
        allowClear: true,
        width: '100%'
    });

    // Auto submit form saat filter berubah
    const form = document.getElementById('filterForm');

    $('.unit-select, .produk-select, .periode-input').on('change', function() {
        form.submit();
    });
});
</script>

<!-- === Fungsi pembantu warna saldo === -->
@php
    function getSaldoColorClass($nilai, $nilaiPasangan, $minStok) {
        // Prioritas 1: Habis total (keduanya 0) → hitam pekat
        if ($nilai == 0 && $nilaiPasangan == 0) {
            return 'text-dark';
        }

        // Prioritas 2: Keduanya kurang dari 15 → orange
        if ($nilai < 15 && $nilaiPasangan < 15) {
            return 'text-orange';
        }

        // Prioritas 3: Keduanya >= 15 tapi masih di bawah min_stok → kuning
        if ($nilai >= 15 && $nilaiPasangan >= 15 
            && $nilai < $minStok && $nilaiPasangan < $minStok) {
            return 'text-warning';
        }

        // Default: Aman (>= min_stok) → hijau
        return 'text-success';
    }
@endphp

@endsection