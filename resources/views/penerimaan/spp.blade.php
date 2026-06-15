@extends('layouts.app')
@section('title', 'Rekap SPP')

@section('content')
<div class="card card-body">
    <h1 class="mb-4">Rekap SPP</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" class="card-body row g-3 align-items-end mb-4 bg-light p-3 rounded shadow-sm" id="filterForm">
        {{-- Bimba Unit --}}
       @if (auth()->check() && (auth()->user()->is_admin ?? false))
    <div class="col-md-3">
        <label for="unit" class="form-label small text-muted">Bimba Unit</label>
        <select name="unit" id="unit" class="form-select">
            <option value="">-- Semua Unit --</option>
            @foreach($unitList as $unit)
                <option value="{{ $unit->kode_unit ?? $unit->id }}" {{ request('unit') == ($unit->kode_unit ?? $unit->id) ? 'selected' : '' }}>
                    {{ $unit->biMBA_unit }}
                </option>
            @endforeach
        </select>
    </div>
@endif

        {{-- NIM | Nama Murid --}}
        <div class="col-md-4">
            <label for="searchMurid" class="form-label small text-muted">NIM | Nama Murid</label>
            <select id="searchMurid" name="search" class="form-select">
                <option value="">-- Ketik NIM atau Nama Murid --</option>
                @foreach($muridList as $m)
                    <option value="{{ $m->nim }}" {{ request('search') == $m->nim ? 'selected' : '' }}>
                        {{ $m->nim }} | {{ $m->nama_murid }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Periode Dari --}}
        <div class="col-md-2">
            <label for="periode_dari" class="form-label small text-muted">Periode Dari</label>
            <input type="month" id="periode_dari" name="periode_dari" class="form-control"
                   value="{{ request('periode_dari') }}">
        </div>

        {{-- Periode Sampai --}}
        <div class="col-md-2">
            <label for="periode_sampai" class="form-label small text-muted">Periode Sampai</label>
            <input type="month" id="periode_sampai" name="periode_sampai" class="form-control"
                   value="{{ request('periode_sampai') }}">
        </div>

        {{-- Tombol Reset (opsional, karena sudah auto) --}}
        <div class="col-auto d-flex align-items-end">
            <a href="{{ route('spp.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle text-center rounded card-body shadow-sm">
            <thead class="table-light">
                <tr>
                    <th>Kwitansi</th>
                    <th>NIM</th>
                    <th>Nama Murid</th>
                    <th>Bulan</th>
                    <th>Tahun</th>
                    <th>SPP (Rp)</th>
                    <th>Voucher (Rp)</th>
                    <th>Total (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($penerimaan as $row)
                <tr>
                    <td>{{ $row->kwitansi }}</td>
                    <td>{{ $row->nim }}</td>
                    <td class="text-start">{{ $row->nama_murid }}</td>
                    <td>{{ $row->bulan }}</td>
                    <td>{{ $row->tahun }}</td>
                    <td class="text-end">{{ number_format($row->spp ?? 0, 0, ',', '.') }}</td>
                     <td>
                                        @if($row->voucher)
                                            {{ number_format(50000, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                    <td class="text-end fw-bold">
    {{ number_format(
        ($row->spp ?? 0) + (!empty($row->voucher) && $row->voucher != '0' ? 50000 : 0),
        0,
        ',',
        '.'
    ) }}
</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        Tidak ada data penerimaan SPP yang ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mb-4">
        {{ $penerimaan->appends(request()->query())->links() }}
    </div>

    <div class="mt-4 p-3 bg-light rounded shadow-sm">
        <div class="row fw-bold fs-5">
            <div class="col-12 col-md-6">Total SPP Diterima:</div>
            <div class="col-12 col-md-6 text-md-end text-primary">
                Rp {{ number_format($totalSPP ?? 0, 0, ',', '.') }}
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12 col-md-6">Total Voucher Terpakai:</div>
            <div class="col-12 col-md-6 text-md-end text-danger">
                Rp {{ number_format($totalVoucher ?? 0, 0, ',', '.') }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function () {
    // Inisialisasi Select2
    $('#searchMurid').select2({
        width: '100%',
        placeholder: '-- Ketik NIM atau Nama Murid --',
        allowClear: true,
        tags: true  // Izinkan input manual jika NIM/nama tidak ada di list
    });

    $('#unit').select2({
        width: '100%',
        placeholder: '-- Semua Unit --',
        allowClear: true
    });

    // AUTO FILTER: langsung submit saat ada perubahan
    $('#unit, #searchMurid, #periode_dari, #periode_sampai').on('change', function () {
        // Saat ganti Unit, reset pilihan murid
        if ($(this).attr('id') === 'unit') {
            $('#searchMurid').val(null).trigger('change');
        }
        $('#filterForm').submit();
    });
});
</script>
@endpush