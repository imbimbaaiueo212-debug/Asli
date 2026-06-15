@extends('layouts.app')
@section('title', 'Perkembangan Unit')
@section('content')
<div class="container-fluid py-4">

    {{-- ================= HEADER ================= --}}
    <div class="card border-0 rounded-4 shadow-sm mb-4">
        <div class="card-body border-0 rounded-2 card">

            {{-- Logo & Judul --}}
            <div class="d-flex align-items-center mb-4">
    <img src="{{ asset('template/img/logoslip.png') }}"
         alt="Logo"
         class="me-3"
         style="max-width:80px;height:auto;">
    <div>
        @php
            $userUnit = auth()->user()?->bimba_unit ?? 'Semua Unit';
            // Kalau ada relasi ke model Unit, bisa ambil nama lengkap:
            // $userUnit = auth()->user()?->unit?->biMBA_unit ?? 'Semua Unit';
        @endphp

        <h4 class="mb-1 fw-bold text-primary">
            Perkembangan Murid {{ $userUnit }}
        </h4>
        <small class="text-muted">Filter data berdasarkan unit dan tahun</small>
    </div>
</div>

            {{-- ================= FORM FILTER ================= --}}
            <form method="GET"
                  action="{{ route('perkembangan_units.index') }}"
                  class="row g-3 align-items-end"
                  id="filterForm">

                {{-- Unit biMBA --}}
                @if (auth()->check() && (auth()->user()->is_admin ?? false))
                <div class="col-lg-5 col-md-6">
                    <label class="form-label fw-bold">Unit biMBA</label>
                    <select name="bimba_unit"
                            class="form-select form-select-lg"
                            id="unitSelect"
                            required>
                        <option value="">-- Pilih Unit biMBA --</option>
                        @foreach(
                            \App\Models\Unit::withoutGlobalScope(\App\Models\Scopes\UnitScope::class)
                                ->orderBy('bimba_unit')
                                ->get() as $unit
                        )
                            <option value="{{ $unit->biMBA_unit }}"
                                data-cabang="{{ $unit->no_cabang }}"
                                {{ strtoupper($bimba_unit ?? '') === strtoupper($unit->biMBA_unit) ? 'selected' : '' }}>
                                {{ strtoupper($unit->biMBA_unit) }} ({{ $unit->no_cabang }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Tahun --}}
                <div class="col-lg-2 col-md-3">
                    <label class="form-label fw-bold">Tahun</label>
                    <select name="tahun_mulai" class="form-select">
                        @for($y = date('Y') + 1; $y >= date('Y') - 10; $y--)
                            <option value="{{ $y }}"
                                {{ ($tahunMulai ?? date('Y')) == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                {{-- Tombol --}}
                <div class="col-lg-3 col-md-12 d-flex gap-2">
                    <div class="flex-grow-1">
                        <label class="form-label invisible d-block">Tampilkan</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Tampilkan
                        </button>
                    </div>
                    <div class="flex-grow-1">
                        <label class="form-label invisible d-block">Reset</label>
                        <a href="{{ route('perkembangan_units.index') }}"
                           class="btn btn-outline-secondary w-100">
                            <i class="fas fa-sync me-1"></i> Reset
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- ================= JAVASCRIPT FILTER (AMAN) ================= --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const select = document.getElementById('unitSelect');
            const form   = document.getElementById('filterForm');

            if (!select || !form) return;

            select.addEventListener('change', function () {
                if (select.value !== '') {
                    form.submit();
                }
            });
        });
    </script>

    {{-- ================= ALERT JIKA BELUM PILIH UNIT ================= --}}
    @if(!$bimba_unit)
        <div class="alert alert-info d-flex align-items-center mt-4" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <div>
                Silakan pilih <strong>Unit biMBA</strong> terlebih dahulu
                untuk menampilkan laporan perkembangan unit.
            </div>
        </div>
    @else

    {{-- ================= DATA PREPARATION ================= --}}
    @php
        $start = \Carbon\Carbon::createFromDate($tahunMulai, 1, 1)->locale('id');

        $months    = [];
        $baruIni   = [];
        $keluarIni = [];
        $aktifIni  = [];
        $dhuafaIni = [];

        for ($m = 1; $m <= 12; $m++) {
            $date = $start->copy()->month($m);
            $months[] = $date->translatedFormat('F');

            $idx = $m - 1;
            $baruIni[]   = $mb[$idx] ?? 0;
            $keluarIni[] = $mk[$idx] ?? 0;
            $aktifIni[]  = $ma[$idx] ?? 0;
            $dhuafaIni[] = ($bnf[$idx] ?? 0) + ($d[$idx] ?? 0);
        }

        if ($bulan !== null) {
            foreach (range(0, 11) as $i) {
                if ($i !== ($bulan - 1)) {
                    $baruIni[$i]   = 0;
                    $keluarIni[$i] = 0;
                    $aktifIni[$i]  = 0;
                    $dhuafaIni[$i] = 0;
                }
            }
        }
    @endphp

    {{-- ================= TABEL ================= --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary text-dark">
        <h5 class="mb-0">
            Perkembangan Murid 
            {{ $bulan ? 'Bulan ' . \Carbon\Carbon::create()->month($bulan)->locale('id')->monthName : 'Tahun' }}
            {{ $tahunMulai }} - {{ strtoupper($bimba_unit) }}
        </h5>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm text-center align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2">BULAN</th>
                        <th rowspan="2">SPP</th>
                        <th colspan="8">MURID</th>
                    </tr>
                    <tr>
                        <th>MA1<br><small>(Bulan Lalu)</small></th>
                        <th>MTB<br><small>(Trial Baru)</small></th>
                        <th>MTA<br><small>(Trial Aktif)</small></th>
                        <th>MB<br><small>(Murid Baru)</small></th>
                        <th>MK<br><small>(Murid Keluar)</small></th>
                        <th>MA2<br><small>(Bulan Berjalan)</small></th>
                        <th>BNF<br><small>(S3B1,S3B2,S3B3)</small></th>
                        <th>D<br><small>(Dhuafa)</small></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $i => $bulanNama)
                    <tr>
                        <td class="text-start fw-bold">{{ $bulanNama }}</td>
                        <td>Rp {{ number_format($sppPerBulan[$i]['total_spp'] ?? 0, 0, ',', '.') }}</td>
                        
                        <!-- MA1 - Murid Aktif Bulan Lalu -->
                        <td class="table-info fw-bold">{{ $ma1[$i] ?? 0 }}</td>
                        
                        <!-- MTB - Murid Trial Baru -->
                        <td class="table-success fw-bold">{{ $mtb[$i] ?? 0 }}</td>
                        
                        <!-- MTA - Murid Trial Aktif -->
                        <td class="table-warning fw-bold">{{ $mta[$i] ?? 0 }}</td>
                        
                        <!-- MB - Murid Baru -->
                        <td class="table-success fw-bold">{{ $mb[$i] ?? 0 }}</td>
                        
                        <!-- MK - Murid Keluar -->
                        <td class="table-danger fw-bold">{{ $mk[$i] ?? 0 }}</td>
                        
                        <!-- MA2 - Murid Aktif Bulan Berjalan -->
                        <td class="table-primary fw-bold">{{ $ma[$i] ?? 0 }}</td>
                        
                        <!-- BNF -->
                        <td class="table-warning">{{ $bnf[$i] ?? 0 }}</td>
                        
                        <!-- D -->
                        <td class="table-warning">{{ $d[$i] ?? 0 }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
    <th class="text-start">TOTAL</th>
    <th class="text-success">
        Rp {{ number_format(array_sum(array_column($sppPerBulan, 'total_spp')), 0, ',', '.') }}
    </th>
    <th>{{ $total_ma1 ?? 0 }}</th>           {{-- MA1 = Kumulatif Akhir --}}
    <th>{{ $total_mtb ?? 0 }}</th>
    <th>{{ $total_mta ?? 0 }}</th>
    <th class="text-success">{{ $total_mb ?? 0 }}</th>
    <th class="text-danger">{{ $total_mk ?? 0 }}</th>
    <th class="text-primary">{{ $total_ma ?? 0 }}</th>
    <th class="text-dark">{{ $totalBnfAllTime ?? 0 }}</th>
    <th class="text-dark">{{ $totalDhuafaAllTime ?? 0 }}</th>
</tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

    {{-- ================= GRAFIK ================= --}}
    <div class="card border-0 shadow">
        <div class="card-header bg-white text-center border-bottom border-primary border-4">
            <h4 class="fw-bold text-primary mb-0">GRAFIK PERKEMBANGAN MURID</h4>
            <small class="text-muted">{{ strtoupper($bimba_unit) }} - {{ $tahunMulai }}</small>
        </div>
        <div class="card-body bg-light" style="height:350px">
            <canvas id="chartPerkembangan"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('chartPerkembangan');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($months),
                    datasets: [
                        {
                            label: 'Murid Baru',
                            data: @json($baruIni),
                            backgroundColor: 'rgba(40,167,69,.6)'
                        },
                        {
                            label: 'Murid Aktif',
                            data: @json($aktifIni),
                            type: 'line',
                            borderColor: '#0d6efd',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Murid Keluar',
                            data: @json($keluarIni),
                            type: 'line',
                            borderColor: '#dc3545',
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
    </script>

    @endif
</div>
@endsection
