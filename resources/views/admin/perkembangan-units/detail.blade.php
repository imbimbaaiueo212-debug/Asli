@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="{{ asset('template/img/logoslip.png') }}" style="max-width:80px;">
                </div>
                <div class="col">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td><strong>Unit biMBA</strong></td><td>:</td><td><strong>{{ strtoupper($unit) }}</strong></td></tr>
                        <tr><td><strong>No Cabang</strong></td><td>:</td><td><strong>{{ $no_cabang }}</strong></td></tr>
                        <tr><td><strong>Tahun</strong></td><td>:</td><td><strong>{{ $tahun }}</strong></td></tr>
                    </table>
                </div>
                <div class="col-auto">
                    <a href="{{ url('/admin/perkembangan-units') }}" class="btn btn-secondary">
                        ← Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Perkembangan -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm text-center mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th rowspan="2">BULAN</th>
                            <th colspan="5">MURID</th>
                        </tr>
                        <tr class="table-secondary">
                            <th>Aktif Lalu</th>
                            <th>Baru</th>
                            <th>Keluar</th>
                            <th>Aktif Ini</th>
                            <th>Dhuafa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($months as $i => $bulan)
                        <tr>
                            <td class="text-start fw-bold">{{ $bulan }}</td>
                            <td>{{ number_format($aktifLalu[$i]) }}</td>
                            <td class="text-success fw-bold">{{ number_format($baruIni[$i]) }}</td>
                            <td class="text-danger fw-bold">{{ number_format($keluarIni[$i]) }}</td>
                            <td class="text-primary fw-bold">{{ number_format($aktifIni[$i]) }}</td>
                            <td class="text-warning">{{ number_format($dhuafaIni[$i]) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-hover">
                        <tr>
                            <th>TOTAL</th>
                            <th>-</th>
                            <th class="text-success">{{ number_format(array_sum($baruIni)) }}</th>
                            <th class="text-danger">{{ number_format(array_sum($keluarIni)) }}</th>
                            <th class="text-primary">{{ number_format(end($aktifIni)) }}</th>
                            <th class="text-warning">{{ number_format(array_sum($dhuafaIni)) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Grafik -->
    <div class="card border-0 shadow">
        <div class="card-header bg-primary text-white text-center">
            <h5>GRAFIK PERKEMBANGAN MURID AKTIF - {{ strtoupper($unit) }} ({{ $tahun }})</h5>
        </div>
        <div class="card-body">
            <canvas id="chartPerkembangan" height="120"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('chartPerkembangan'), {
            type: 'bar',
            data: {
                labels: @json($months),
                datasets: [{
                    label: 'Murid Aktif',
                    data: @json($aktifIni),
                    backgroundColor: '#0d6efd',
                    borderRadius: 10,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
</div>
@endsection