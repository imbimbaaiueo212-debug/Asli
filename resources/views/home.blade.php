@extends('layouts.app')

@section('title', 'Dashboard biMBA')

@section('content')

<style>
    body {
        background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
        font-family: 'Inter', system-ui, sans-serif;
        color: #1f2937;
    }

    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .welcome-section {
        text-align: center;
        margin-bottom: 3rem;
    }

    .welcome-title {
        font-size: 2.5rem;
        font-weight: 800;
        background: linear-gradient(to right, #4f46e5, #7c3aed);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0.75rem;
    }

    .welcome-subtitle {
        font-size: 1.1rem;
        color: #64748b;
        max-width: 700px;
        margin: 0 auto;
    }

    .card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        background: white;
    }

    .card:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 40px rgba(0,0,0,0.12);
    }

    .metric-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 1rem;
    }

    .chart-container {
        height: 380px;
        padding: 1rem 0;
    }

    .list-group-item {
        border: none;
        padding: 1rem 1.5rem;
        transition: background 0.2s;
    }

    .list-group-item:hover {
        background: #f8fafc;
    }

    @media (max-width: 768px) {
        .welcome-title { font-size: 2rem; }
        .dashboard-container { padding: 1.5rem 1rem; }
        .chart-container { height: 320px; }
    }
</style>

<div class="dashboard-container">

    <!-- Welcome Section -->
    <div class="welcome-section">
        <h1 class="welcome-title">Dashboard Overview</h1>
        <p class="welcome-subtitle">
            Selamat datang kembali! Ini ringkasan perkembangan murid dan keuangan biMBA hari ini.
        </p>
    </div>

    <!-- Metrik Cards -->
    <div class="row g-4 mb-5">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="metric-icon bg-success-subtle text-success mx-auto">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <h5 class="text-muted mb-1" title="Berdasarkan tahun masuk (tgl_masuk)">Murid Baru Tahun Ini</h5>
                    <h2 class="fw-bold text-success">{{ number_format($totalBaru) }}</h2>
                    <small class="text-muted">Tahun ini</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="metric-icon bg-primary-subtle text-primary mx-auto">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <h5 class="text-muted mb-1">Murid Aktif</h5>
                    <h2 class="fw-bold text-primary">{{ number_format($totalAktif) }}</h2>
                    <small class="text-muted">Sedang belajar</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="metric-icon bg-danger-subtle text-danger mx-auto">
                        <i class="bi bi-person-dash-fill"></i>
                    </div>
                    <h5 class="text-muted mb-1">Murid Keluar</h5>
                    <h2 class="fw-bold text-danger">{{ number_format($totalKeluar) }}</h2>
                    <small class="text-muted">Tidak aktif</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="metric-icon bg-purple-subtle text-purple mx-auto">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h5 class="text-muted mb-1">Total Murid</h5>
                    <h2 class="fw-bold text-purple">{{ number_format( $totalAktif + $totalKeluar) }}</h2>
                    <small class="text-muted">Keseluruhan</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-4 mb-5">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header bg-gradient-primary text-dark">
                    <h5 class="mb-0">Tren Pendaftaran Murid Baru {{ now()->year }}</h5>
                </div>
                <div class="card-body chart-container">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header bg-gradient-success text-white">
                    <h5 class="mb-0">Omzet vs SPP {{ now()->year }}</h5>
                </div>
                <div class="card-body chart-container">
                    <canvas id="profitChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Registrations & Top Units -->
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Pendaftaran Terbaru</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentMurid as $murid)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                                <div>
                                    <h6 class="mb-0">{{ $murid->nama ?? 'Murid Baru' }}</h6>
                                    <small class="text-muted">
                                        {{ $murid->unit->biMBA_unit ?? '-' }} • 
                                        {{ $murid->tgl_masuk ? $murid->tgl_masuk->diffForHumans() : '-' }}
                                    </small>
                                </div>
                                <span class="badge bg-{{ $murid->status == 'baru' ? 'success' : 'secondary' }} rounded-pill px-3 py-2">
                                    {{ ucfirst($murid->status) }}
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">Belum ada pendaftaran baru</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Unit Teraktif</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($topUnits as $unit)
                            <div class="list-group-item px-4 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                            style="width: 48px; height: 48px; font-size: 1.2rem;">
                                            {{ strtoupper(substr($unit->unit->biMBA_unit ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $unit->unit->biMBA_unit ?? 'Unit' }}</h6>
                                            <small class="text-muted">{{ $unit->total_murid }} murid</small>
                                        </div>
                                    </div>
                                    <span class="text-primary fw-bold fs-5">
                                        {{ number_format($unit->total_murid / ($totalBaru + $totalAktif + $totalKeluar) * 100, 1) }}%
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">Belum ada data unit</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
@if (Auth::check() && in_array(Auth::user()->role, ['admin','pusat','developer','superadmin','owner','direktur']))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Tren Pendaftaran Murid Baru - Line Chart
    const ctxMonthly = document.getElementById('monthlyChart');
    if (ctxMonthly) {
        new Chart(ctxMonthly, {
            type: 'line',
            data: {
                labels: @json($chartData['labels']),
                datasets: [{
                    label: 'Murid Baru',
                    data: @json($chartData['data']),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.15)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#4f46e5',
                    pointBorderWidth: 3,
                    pointRadius: 5,
                    pointHoverRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Omzet vs SPP - Bar Chart
    const ctxProfit = document.getElementById('profitChart');
    if (ctxProfit) {
        new Chart(ctxProfit, {
            type: 'bar',
            data: {
                labels: @json($chartDataPenerimaan['labels']),
                datasets: [
                    {
                        label: 'Omzet',
                        data: @json($chartDataPenerimaan['total']),
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: '#10b981',
                        borderRadius: 6,
                    },
                    {
                        label: 'SPP',
                        data: @json($chartDataPenerimaan['spp']),
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderColor: '#ef4444',
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: { beginAtZero: true },
                    x: { grid: { display: false } }
                }
            }
        });
    }

</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    fetch("{{ url('/system/auto-activate-trial') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.updated > 0) {
            console.log("Auto activated:", data.updated);
            // optional: reload supaya data dashboard update
            location.reload();
        }
    })
    .catch(err => console.error(err));

});
</script>

@endif
@endpush