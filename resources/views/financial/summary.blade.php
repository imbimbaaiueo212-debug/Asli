@extends('layouts.app')

@section('title', 'Summary Keuangan')

@section('content')
<style>
    .finance-container { background-color: #e7f1ff; padding: 20px; border-radius: 5px; border: 1px solid #b3ccff; }
    .finance-header { background: linear-gradient(to bottom, #ffffff, #cce0ff); border: 1px solid #b3ccff; text-align: center; font-weight: bold; padding: 8px; margin-bottom: 20px; text-transform: uppercase; }
    .finance-column-title { text-align: center; font-weight: bold; margin-bottom: 15px; border-bottom: 1px solid #b3ccff; padding-bottom: 8px; font-size: 0.85rem; min-height: 40px; display: flex; align-items: center; justify-content: center; }
    .finance-row { display: flex; align-items: center; margin-bottom: 8px; gap: 8px; }
    .finance-label { flex: 1; font-size: 0.8rem; color: #333; }
    .finance-box { background: white; border: 1px solid #b3ccff; min-width: 90px; text-align: right; padding: 3px 7px; font-size: 0.8rem; font-weight: bold; }
    .finance-box-qty { min-width: 35px; background: #f8f9fa; text-align: center; }
</style>

<div class="container-fluid py-4">
    <div class="card mb-3 border-0 shadow-sm p-3 bg-light">
        {{-- FORM FILTER OTOMATIS --}}
        <form method="GET" action="{{ route('summary.keuangan') }}" id="filterForm" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="small fw-bold text-black">Unit biMBA</label>
                @if($isAdmin)
                    <select name="unit_id" id="unitFilter" class="form-select form-select-sm">
                        <option value="">-- Semua Unit --</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ $selectedUnitId == $u->id ? 'selected' : '' }}>{{ $u->biMBA_unit }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="text" class="form-control form-control-sm bg-white" value="{{ Auth::user()->unit->biMBA_unit ?? 'Unit Belum Dipilih' }}" readonly>
                    <input type="hidden" name="unit_id" value="{{ Auth::user()->unit_id }}">
                @endif
            </div>

            <div class="col-6 col-md-3">
                <label class="small fw-bold text-black">Bulan</label>
                <select name="bulan" id="bulanFilter" class="form-select form-select-sm">
                    @foreach ([1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'] as $k => $v)
                        <option value="{{ $k }}" {{ $bulan == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-3">
                <label class="small fw-bold text-black">Tahun</label>
                <select name="tahun" id="tahunFilter" class="form-select form-select-sm">
                    @for ($y = now()->year - 3; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="col-12 col-md-2">
                <a href="{{ route('summary.keuangan') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
            </div>
        </form>
    </div>

    <div class="finance-container shadow-sm">
        <div class="mb-2 fw-bold small text-primary">{{ $data['unit_name'] }} | {{ $data['month_year'] }}</div>
        <div class="finance-header text-black">SUMMARY KEUANGAN</div>

        <div class="row g-3">
            <div class="col-md-4 col-xl-2">
                <div class="finance-column-title text-black">Penerimaan biMBA</div>
                <div class="finance-row"><div class="finance-label">Daftar</div><div class="finance-box text-black">{{ number_format($data['penerimaan_bimba_daftar'], 0, ',', '.') }}</div></div>
                <div class="finance-row"><div class="finance-label">SPP+VHB</div><div class="finance-box text-black">{{ number_format($data['penerimaan_bimba_spp_vhb'], 0, ',', '.') }}</div></div>
                <div class="finance-row"><div class="finance-label">Penjualan</div><div class="finance-box text-black">{{ number_format($data['penerimaan_bimba_penjualan'], 0, ',', '.') }}</div></div>
                <div class="finance-row border-top pt-1"><div class="finance-label fw-bold">Total</div><div class="finance-box bg-warning-subtle text-black">{{ number_format($data['penerimaan_bimba_total'], 0, ',', '.') }}</div></div>
            </div>

            <div class="col-md-4 col-xl-2">
                <div class="finance-column-title">Penerimaan English</div>
                <div class="finance-row"><div class="finance-label">Daftar</div><div class="finance-box text-black">0</div></div>
                <div class="finance-row"><div class="finance-label">SPP+VHB</div><div class="finance-box text-black">0</div></div>
                <div class="finance-row border-top pt-1"><div class="finance-label fw-bold">Total</div><div class="finance-box bg-warning-subtle text-black">0</div></div>
            </div>

            <div class="col-md-4 col-xl-2">
                <div class="finance-column-title">Petty Cash</div>
                <div class="finance-row"><div class="finance-label">Saldo Awal</div><div class="finance-box text-black">{{ number_format($data['petty_cash_saldo_awal'], 0, ',', '.') }}</div></div>
                <div class="finance-row"><div class="finance-label">Debit</div><div class="finance-box text-success">{{ number_format($data['petty_cash_debit'], 0, ',', '.') }}</div></div>
                <div class="finance-row"><div class="finance-label">Kredit</div><div class="finance-box text-danger">{{ number_format($data['petty_cash_kredit'], 0, ',', '.') }}</div></div>
                <div class="finance-row border-top pt-1"><div class="finance-label fw-bold">Saldo Akhir</div><div class="finance-box bg-info-subtle text-black">{{ number_format($data['petty_cash_saldo_akhir'], 0, ',', '.') }}</div></div>
            </div>

            <div class="col-md-4 col-xl-2">
                <div class="finance-column-title">SPP Murid</div>
                <div class="finance-row"><div class="finance-label">SPP biMBA</div><div class="finance-box text-black">{{ number_format($data['spp_bimba'], 0, ',', '.') }}</div></div>
                <div class="finance-row"><div class="finance-label">SPP Eng</div><div class="finance-box text-black">0</div></div>
                <div class="finance-row"><div class="finance-label">Bagi Hasil</div><div class="finance-box text-black">0</div></div>
            </div>

            <div class="col-md-4 col-xl-2">
                <div class="finance-column-title">Lain-lain</div>
                <div class="finance-row"><div class="finance-label">Serah VHB</div><div class="finance-box text-black">{{ number_format($data['lain_penyerahan_vhb'], 0, ',', '.') }}</div></div>
                <div class="finance-row"><div class="finance-label">Pakai VHB</div><div class="finance-box text-black">0</div></div>
            </div>

            <div class="col-md-4 col-xl-2">
                <div class="finance-column-title">Transaksi</div>
                <div class="finance-row"><div class="finance-label">SPP Gabung</div><div class="finance-box text-black">0</div><div class="finance-box-qty text-black">0</div></div>
                <div class="finance-row"><div class="finance-label">Salah Trf</div><div class="finance-box text-black">0</div><div class="finance-box-qty text-black">0</div></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function () {
    // Inisialisasi Select2
    $('#unitFilter').select2({
        width: '100%',
        placeholder: "-- Semua Unit --",
        allowClear: true
    });

    $('#bulanFilter').select2({
        width: '100%',
        minimumResultsForSearch: Infinity // tidak perlu search karena hanya 12 bulan
    });

    $('#tahunFilter').select2({
        width: '100%',
        minimumResultsForSearch: Infinity
    });

    // AUTO FILTER: langsung submit saat pilih Unit, Bulan, atau Tahun
    $('#unitFilter, #bulanFilter, #tahunFilter').on('change', function () {
        $('#filterForm').submit();
    });
});
</script>
@endpush
