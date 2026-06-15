@extends('layouts.app')

@section('title', 'Rekap Petty Cash — Penerimaan')

@section('content')
<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-body">

            {{-- Header periode + UNIT & CABANG --}}
            <div class="row align-items-center mb-3 border-bottom pb-2">
                <div class="col-md-6 mb-2 mb-md-0">
                    <h5 class="mb-1">Rekapitulasi Transaksi Penerimaan</h5>

                    {{-- INFO UNIT & NO CABANG --}}
                    @php
                        $firstPenerimaan = $penerimaan->first();
                        $selectedUnit = request('unit');
                        $namaUnit = $selectedUnit 
                            ? ($unitList->where('kode_unit', $selectedUnit)->first()->biMBA_unit ?? $selectedUnit)
                            : ($firstPenerimaan->bimba_unit ?? $firstPenerimaan->nama_unit ?? 'Semua Unit');
                        $noCabang = $firstPenerimaan->no_cabang ?? $firstPenerimaan->kode_cabang ?? '-';
                    @endphp

                    <div class="small mb-1">
                        Unit:
                        <strong>{{ $namaUnit }}</strong>
                        @if(!$selectedUnit)
                            <span class="text-muted">(Semua Unit)</span>
                        @endif
                        <span class="ms-2">
                            No. Cabang:
                            <strong>{{ $noCabang }}</strong>
                        </span>
                    </div>

                    <div class="small text-muted d-block text-truncate" style="min-width:0;">
                        Periode Tanggal:
                        <strong class="ms-1">
                            {{ request('start_date')
                                ? \Carbon\Carbon::parse(request('start_date'))->format('d M Y')
                                : ($penerimaan->min('tanggal')
                                    ? \Carbon\Carbon::parse($penerimaan->min('tanggal'))->format('d M Y')
                                    : '-') }}
                        </strong>
                        &nbsp; s.d. &nbsp;
                        <strong>
                            {{ request('end_date')
                                ? \Carbon\Carbon::parse(request('end_date'))->format('d M Y')
                                : ($penerimaan->max('tanggal')
                                    ? \Carbon\Carbon::parse($penerimaan->max('tanggal'))->format('d M Y')
                                    : '-') }}
                        </strong>
                    </div>
                </div>

                <div class="col-md-6">
                    <form method="GET" class="d-flex flex-wrap justify-content-end gap-2" id="filterForm">
                        {{-- Filter Bimba Unit --}}
                        <div class="d-flex align-items-center gap-1">
                            <label class="small mb-0">Unit:</label>
                            <select name="unit" class="form-select form-select-sm" style="width:200px;" id="filterUnit">
                                <option value="">-- Semua Unit --</option>
                                @foreach($unitList as $unit)
                                    <option value="{{ $unit->kode_unit ?? $unit->id }}" 
                                            {{ request('unit') == ($unit->kode_unit ?? $unit->id) ? 'selected' : '' }}>
                                        {{ $unit->biMBA_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter Tanggal Dari --}}
                        <div class="d-flex align-items-center gap-1">
                            <label class="small mb-0">Dari:</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}"
                                   class="form-control form-control-sm" style="width:150px;">
                        </div>

                        {{-- Filter Tanggal Sampai --}}
                        <div class="d-flex align-items-center gap-1">
                            <label class="small mb-0">Sampai:</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}"
                                   class="form-control form-control-sm" style="width:150px;">
                        </div>

                        {{-- Tombol Terapkan & Reset --}}
                        <div class="d-flex align-items-center gap-1">
                            <button type="submit" class="btn btn-sm btn-primary">Terapkan</button>
                            <a href="{{ route('penerimaan.rekap') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- PANEL REKAP --}}
            <div class="row g-3">

                {{-- a. biMBA AIUEO --}}
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white py-2">
                            <strong class="small">a. Transaksi Penerimaan (biMBA-AIUEO)</strong>
                        </div>
                        <div class="card-body p-2" style="max-height:620px; overflow-y:auto;">
                            <table class="table table-sm table-bordered small text-center">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>Kode</th>
                                        <th>Tipe</th>
                                        <th>Ada VA</th>
                                        <th>Tidak Ada VA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rekapAiueo as $row)
                                    <tr>
                                        <td>{{ $row['kode'] }}</td>
                                        <td class="text-start">{{ $row['type'] }}</td>
                                        <td class="text-end">{{ $row['va'] ? 'Rp '.number_format($row['va'],0,',','.') : '' }}</td>
                                        <td class="text-end">{{ $row['non_va'] ? 'Rp '.number_format($row['non_va'],0,',','.') : '' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="fw-bold table-light">
                                    <tr>
                                        <td colspan="2" class="text-end">Total</td>
                                        <td class="text-end">{{ $totalVA ? 'Rp '.number_format($totalVA,0,',','.') : '' }}</td>
                                        <td class="text-end">{{ $totalNonVA ? 'Rp '.number_format($totalNonVA,0,',','.') : '' }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- c. Petty Cash --}}
                <div class="col-lg-4 col-md-12">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header py-2">
                            <strong class="small">c. Transaksi Petty Cash</strong>
                        </div>

                        <div class="card-body p-2">

                            <div class="d-flex justify-content-end gap-3 mb-2">
                                <div>
                                    <div class="small text-muted">Saldo Awal</div>
                                    <div><strong>{{ $saldoAwal ? 'Rp '.number_format($saldoAwal,0,',','.') : '' }}</strong></div>
                                </div>
                                <div>
                                    <div class="small text-muted">Saldo Akhir</div>
                                    <div><strong>{{ $saldoAkhir ? 'Rp '.number_format($saldoAkhir,0,',','.') : '' }}</strong></div>
                                </div>
                            </div>

                            <table class="table table-sm table-bordered small text-center">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>Kode</th>
                                        <th class="text-start">Tipe</th>
                                        <th class="text-end">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($byKategori as $k => $v)
                                    <tr>
                                        <td>-</td>
                                        <td class="text-start">{{ $k }}</td>
                                        <td class="text-end">{{ $v ? 'Rp '.number_format($v,0,',','.') : '' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="fw-bold">
                                    <tr>
                                        <td colspan="2" class="text-end">Total</td>
                                        <td class="text-end">{{ $totalKredit ? 'Rp '.number_format($totalKredit,0,',','.') : '' }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

{{-- Tambahkan script kecil untuk auto-submit saat pilih unit (opsional, lebih responsif) --}}
@push('scripts')
<script>
$(document).ready(function() {
    $('#filterUnit').on('change', function() {
        $('#filterForm').submit();
    });
});
</script>
@endpush