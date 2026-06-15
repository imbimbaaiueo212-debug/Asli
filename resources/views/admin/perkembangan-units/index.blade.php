@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
<div class="mb-4">
    <h3 class="fw-bold text-primary mb-1">
        REKAP PERKEMBANGAN SEMUA UNIT biMBA
    </h3>
    <p class="text-success mb-0">
        Data dari Buku Induk •
        Periode: <strong class="text-primary">{{ $periode }}</strong>
    </p>
</div>
{{-- =========================
    FILTER RANGE (LINTAS TAHUN)
========================== --}}
<div class="card shadow-lg hover:shadow-2xl transition-shadow duration-300 border-0 rounded-4 p-4 mb-4 card-body">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">

            {{-- BULAN MULAI --}}
            <div class="col-md-3">
                <label class="form-label fw-bold">Mulai Bulan</label>
                <select name="bulan_mulai" class="form-select">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == $bulanMulai ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- TAHUN MULAI --}}
            <div class="col-md-3">
                <label class="form-label fw-bold">Mulai Tahun</label>
                <select name="tahun_mulai" class="form-select">
                    @for ($y = date('Y') + 1; $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ $y == $tahunMulai ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- BULAN AKHIR --}}
            <div class="col-md-3">
                <label class="form-label fw-bold">Sampai Bulan</label>
                <select name="bulan_akhir" class="form-select">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == $bulanAkhir ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- TAHUN AKHIR --}}
            <div class="col-md-3">
                <label class="form-label fw-bold">Sampai Tahun</label>
                <select name="tahun_akhir" class="form-select">
                    @for ($y = date('Y') + 1; $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ $y == $tahunAkhir ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="col-md-12">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Tampilkan
                </button>
            </div>

        </form>
    </div>
</div>

{{-- =========================
    HEADER
========================== --}}


{{-- =========================
    TABEL REKAP
========================== --}}
<div class="card border-0 shadow mb-5">
    <div class="card-header bg-primary text-dark text-center">
        <h5 class="mb-0 fw-bold">
            REKAP PERKEMBANGAN PERIODE {{ strtoupper($periode) }}
        </h5>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0 text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2">NAMA UNIT</th>
                        <th rowspan="2">CABANG</th>
                        <th colspan="5">PERKEMBANGAN PERIODE</th>
                    </tr>
                    <tr class="table-secondary">
                        <th>Aktif Awal</th>
                        <th>Baru</th>
                        <th>Keluar</th>
                        <th>Aktif Akhir</th>
                        <th>Dhuafa</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($rekap as $d)
                    <tr>
                        <td class="text-start fw-bold text-uppercase">
                            {{ $d['nama_unit'] }}
                        </td>
                        <td>{{ $d['no_cabang'] }}</td>
                        <td>{{ number_format($d['aktif_lalu']) }}</td>
                        <td class="text-success fw-bold">
                            +{{ number_format($d['baru_periode']) }}
                        </td>
                        <td class="text-danger fw-bold">
                            -{{ number_format($d['keluar_periode']) }}
                        </td>
                        <td class="text-primary fw-bold">
                            {{ number_format($d['aktif_akhir']) }}
                        </td>
                        <td class="text-warning fw-bold">
                            {{ number_format($d['dhuafa']) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Tidak ada data unit
                        </td>
                    </tr>
                @endforelse
                </tbody>

                @if(count($rekap))
                <tfoot class="table-primary fw-bold">
                    <tr>
                        <td colspan="2" class="text-end pe-3">
                            TOTAL SEMUA UNIT
                        </td>
                        <td>{{ number_format(collect($rekap)->sum('aktif_lalu')) }}</td>
                        <td class="text-success">
                            +{{ number_format(collect($rekap)->sum('baru_periode')) }}
                        </td>
                        <td class="text-danger">
                            -{{ number_format(collect($rekap)->sum('keluar_periode')) }}
                        </td>
                        <td class="text-primary">
                            {{ number_format(collect($rekap)->sum('aktif_akhir')) }}
                        </td>
                        <td class="text-warning">
                            {{ number_format(collect($rekap)->sum('dhuafa')) }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

{{-- =========================
    GRAFIK
========================== --}}
<div class="card border-0 shadow-sm mb-5">
    <div class="card-header bg-primary text-dark text-center py-2">
        <h6 class="mb-0 fw-bold">
            GRAFIK PERKEMBANGAN MURID SELURUH UNIT
            <small class="d-block opacity-75">{{ $periode }}</small>
        </h6>
    </div>

    <div class="card-body p-3 bg-light">
        <canvas id="chartTahunan" height="220"></canvas>
    </div>
</div>

</div>

{{-- =========================
    CHART.JS
========================== --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const ctx = document.getElementById('chartTahunan').getContext('2d');

    new Chart(ctx, {
        data: {
            labels: @json($namaBulanArr),
            datasets: [
                {
                    type: 'bar',
                    label: 'Murid Baru',
                    data: @json($grafikBaru),
                    backgroundColor: 'rgba(40,167,69,0.85)',
                    borderRadius: 6,
                },
                {
                    type: 'bar',
                    label: 'Murid Keluar',
                    data: @json(array_map(fn($v) => -$v, $grafikKeluar)),
                    backgroundColor: 'rgba(220,53,69,0.85)',
                    borderRadius: 6,
                },
                {
                    type: 'line',
                    label: 'Murid Aktif',
                    data: @json($grafikAktif),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13,110,253,0.15)',
                    borderWidth: 3,
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => v.toLocaleString()
                    }
                }
            }
        }
    });
});
</script>
@endsection
