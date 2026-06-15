@extends('layouts.app')

@section('title', 'Jadwal Detail')

@section('content')
<div class="container-fluid py-4">

    <style>
        /* Semua style tetap sama */
        .table-container {
            position: relative;
            max-height: 75vh;
            overflow: auto;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .table-container::-webkit-scrollbar { width: 10px; height: 10px; }
        .table-container::-webkit-scrollbar-track { background: #f1f5f9; }
        .table-container::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 5px;
            border: 2px solid #f1f5f9;
        }
        .table-container::-webkit-scrollbar-thumb:hover { background: #64748b; }

        .header-srj { background-color: #3b82f6 !important; color: rgb(0, 0, 0); }
        .cell-srj   { background-color: #dbeafe !important; }
        .header-sks { background-color: #f59e0b !important; color: rgb(0, 0, 0); }
        .header-s6  { background-color: #0ea5e9 !important; color: rgb(0, 0, 0); }
        .cell-s6    { background-color: #e0f2fe !important; }

        /* Warna Unit - PASTIKAN INI AKTIF */
        .unit-griya   { background-color: #d1fae5 !important; } /* Hijau muda untuk Griya */
        .unit-sapta   { background-color: #dbeafe !important; } /* Biru muda untuk Sapta */
        .unit-villa{ background-color: #fef3c7 !important; } /* Kuning muda untuk Cilodong */
        .unit-pesona  { background-color: #fce7f3 !important; } /* Pink muda untuk Pesona */
        .unit-default { background-color: #f3f4f6 !important; } /* Abu muda default */

        .table td, .table th { 
            padding: 0.5rem; 
            font-size: 0.875rem; 
            vertical-align: middle; 
            border: 1px solid #dee2e6; 
        }
        .murid-row {
    display: block;
    padding: 5px 6px;
    border-bottom: 1px solid #e5e7eb;
    min-height: 30px;
    white-space: nowrap;
}

.murid-row:last-child { 
    border-bottom: none; 
}

.empty-cell {
    background-color: #f9fafb !important;
    color: #9ca3af;
    font-style: italic;
}
        .murid-no   { font-weight: 600; min-width: 20px; display: inline-block; }
        .murid-name { font-weight: 500; }
        .murid-guru { 
            font-size: 0.75rem; 
            color: #15803d; 
            margin-left: 8px; 
            font-style: italic; 
        }
        /* Debug: Tampilkan info unit di cell (hapus setelah debug) */
        .debug-unit {
            font-size: 0.7rem !important;
            color: #dc2626 !important;
            font-weight: bold;
            margin-top: 2px;
        }
    </style>

    <div class="card shadow border-0">
        <div class="card-body">
            <h4 class="card-title mb-4 text-primary fw-semibold">Jadwal Detail</h4>

            <form action="{{ route('jadwal.index') }}" method="GET" id="filterForm" class="mb-4">
    <div class="row g-3 align-items-end justify-content-start">

        <!-- Unit Bimba: paling kiri di md+ (order-md-1) -->
        @if(Auth::check() && Auth::user()->is_admin)
            <div class="col-12 col-md-4 col-lg-3 order-md-1">
                <label for="unitFilter" class="form-label fw-medium">Pilih Unit Bimba</label>
                <select name="unit" id="unitFilter" class="form-select form-select-lg">
                    <option value="">-- Semua Unit --</option>
                    <option value="SEMUA" {{ $selectedUnit === 'SEMUA' ? 'selected' : '' }}>
                        SEMUA UNIT
                    </option>
                    
                    @foreach($units as $unitKey => $unitName)
                        <option value="{{ $unitKey }}" {{ $selectedUnit === $unitKey ? 'selected' : '' }}>
                            {{ $unitName ?: $unitKey }}  <!-- fallback jika nama kosong -->
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <!-- Motivator / Kepala Unit: tengah (order-md-2) -->
        <div class="col-12 col-md-{{ Auth::check() && Auth::user()->is_admin ? '5' : '9' }} col-lg-{{ Auth::check() && Auth::user()->is_admin ? '6' : '8' }} order-md-2">
            <label for="guruFilter" class="form-label fw-medium">Pilih Motivator / Kepala Unit</label>
            <select name="guru" id="guruFilter" class="form-select form-select-lg guru-select">
                <option value="">-- Semua Motivator / Kepala Unit --</option>
                <option value="SEMUA" {{ $guruNama == 'SEMUA' ? 'selected' : '' }}>
                    SEMUA (Tampilkan Semua Jadwal)
                </option>
                
                @foreach($gurus as $g)
                    @php
                        // Ambil nilai dengan aman (default ke string kosong jika key tidak ada)
                        $nama     = $g['nama'] ?? '';
                        $nik      = $g['nik'] ?? '—';
                        $jabatan  = strtolower(trim($g['jabatan'] ?? ''));
                        $label    = '';

                        if (str_contains($jabatan, 'kepala unit') || str_contains($jabatan, 'ku') || str_contains($jabatan, 'ka unit')) {
                            $label = ' (KU)';
                        } elseif (str_contains($jabatan, 'mtv')) {
                            $label = ' (MTV)';
                        } elseif (str_contains($jabatan, 'guru') || str_contains($jabatan, 'pengajar') || str_contains($jabatan, 'tutor')) {
                            $label = ' (Motivator)';
                        }
                    @endphp
                    
                    @if(!empty($nama))  <!-- skip jika nama kosong / tidak ada -->
                        <option value="{{ $nama }}" {{ $guruNama == $nama ? 'selected' : '' }}>
                            {{ $nik }} | {{ $nama }}{{ $label }}
                        </option>
                    @endif
                @endforeach
            </select>
        </div>

        <!-- Tombol Sinkronisasi: paling kanan (order-md-3) -->
        

    </div>
</form>

            @if($jadwal->isEmpty())
                <div class="alert alert-info text-center py-5 my-4">
                    <i class="bi bi-info-circle-fill fs-3 me-2"></i><br>
                    Tidak ada data jadwal.<br>
                    <small class="text-muted">Pilih guru atau lakukan sinkronisasi terlebih dahulu.</small>
                </div>
                            @else
                                @php 
                    $startJamKe = 1;      // mulai dari jam_ke 1 di database
                    $endJamKe   = 9;      // sesuaikan max jam_ke yang ada (misal sampai 116 → jam_ke 9)
                    $jamMulai   = 8;      // jam pertama = 08:00
                    $rowCounter = 1; 
                @endphp

                <div class="table-container">
                    <table class="table table-bordered table-hover m-0 text-center" style="min-width: 2800px;">
                        <thead class="table-light">
                            <tr c>
                                <th rowspan="2" style="width:50px;">NO</th>
                                <th rowspan="2" style="width:80px;">JAM</th>
                                <th colspan="12" class="header-srj">SHIFT (SRJ)</th>
                                <th colspan="12" class="header-sks">SHIFT (SKS)</th>
                                <th colspan="24" class="header-s6">SHIFT (S6)</th>
                            </tr>
                            <tr>
                                <th colspan="4" class="header-srj">SENIN</th>
                                <th colspan="4" class="header-srj">RABU</th>
                                <th colspan="4" class="header-srj">JUM'AT</th>
                                <th colspan="4" class="header-sks">SELASA</th>
                                <th colspan="4" class="header-sks">KAMIS</th>
                                <th colspan="4" class="header-sks">SABTU</th>
                                <th colspan="4" class="header-s6">SENIN</th>
                                <th colspan="4" class="header-s6">SELASA</th>
                                <th colspan="4" class="header-s6">RABU</th>
                                <th colspan="4" class="header-s6">KAMIS</th>
                                <th colspan="4" class="header-s6">JUM'AT</th>
                                <th colspan="4" class="header-s6">SABTU</th>
                            </tr>
                        </thead>
                        <tbody>
    @php 
        $startJamKe = 1;
        $endJamKe   = 9;
        $jamMulai   = 8; 
    @endphp

    @for($jam = $startJamKe; $jam <= $endJamKe; $jam++)
        <tr>
            <!-- NO (Baris) -->
            <td class="fw-bold">{{ $jam }}</td>
            
            <!-- JAM -->
            <td class="fw-bold text-primary">
                {{ str_pad($jamMulai + ($jam - 1), 2, '0', STR_PAD_LEFT) }}:00
            </td>

            @foreach([
                ['hari'=>'Senin', 'shift'=>'srj'],
                ['hari'=>'Rabu',  'shift'=>'srj'],
                ['hari'=>'Jumat', 'shift'=>'srj'],
                ['hari'=>'Selasa','shift'=>'sks'],
                ['hari'=>'Kamis', 'shift'=>'sks'],
                ['hari'=>'Sabtu', 'shift'=>'sks'],
                ['hari'=>'Senin', 'shift'=>'s6'],
                ['hari'=>'Selasa','shift'=>'s6'],
                ['hari'=>'Rabu',  'shift'=>'s6'],
                ['hari'=>'Kamis', 'shift'=>'s6'],
                ['hari'=>'Jum\'at','shift'=>'s6'],
                ['hari'=>'Sabtu', 'shift'=>'s6'],
            ] as $slot)
                
                @php
                    $list = $jadwal[$jam] ?? collect();
                    $muridHari = $list->where('hari', $slot['hari']);

                    if ($slot['shift'] === 's6') {
                        $muridHari = $muridHari->filter(fn($j) => 
                            data_get($j->murid, 'jenis_kbm') === '6 hari'
                        );
                    }

                    $murids = $muridHari->values();
                    $maxMurid = $murids->count();

                    // === WARNA SHIFT ===
                    $cellClass = match($slot['shift']) {
                        'srj' => 'cell-srj',
                        'sks' => 'cell-sks',
                        's6'  => 'cell-s6',
                        default => '',
                    };

                    $unitColors = [
                        'GRIYA PESONA MADANI' => 'unit-griya',
                        'SAPTA TARUNA IV'     => 'unit-sapta',
                        'VILLA BEKASI INDAH 2'=> 'unit-villa',
                        'PESONA'              => 'unit-pesona',
                    ];
                @endphp

                <!-- NO Urut Murid -->
                <td class="{{ $cellClass }}">
                    @foreach($murids as $item)
                        @php
                            $unit = trim(strtoupper($item->murid->bimba_unit ?? ''));
                            $rowClass = $unitColors[$unit] ?? 'unit-default';
                        @endphp
                        <div class="murid-row {{ $rowClass }}">
                            {{ $loop->iteration }}
                        </div>
                    @endforeach
                    @if($maxMurid == 0)
                        <div class="murid-row empty-cell">—</div>
                    @endif
                </td>

                <!-- NIM -->
                <td class="{{ $cellClass }}">
                    @foreach($murids as $item)
                        @php 
                            $unit = trim(strtoupper($item->murid->bimba_unit ?? '')); 
                        @endphp
                        <div class="murid-row {{ $unitColors[$unit] ?? 'unit-default' }}">
                            {{ $item->murid->nim ?? '-' }}
                        </div>
                    @endforeach
                    @if($maxMurid == 0)<div class="murid-row empty-cell">—</div>@endif
                </td>

                <!-- Nama Murid + Guru -->
                <td class="text-start {{ $cellClass }}">
                    @foreach($murids as $item)
                        @php 
                            $unit = trim(strtoupper($item->murid->bimba_unit ?? ''));
                            $rowClass = $unitColors[$unit] ?? 'unit-default';
                        @endphp
                        <div class="murid-row {{ $rowClass }}">
                            <span class="murid-name">{{ $item->murid->nama ?? 'N/A' }}</span>
                            @if($guruNama === 'SEMUA' && !empty($item->guru))
                                <span class="murid-guru">({{ $item->guru }})</span>
                            @endif
                        </div>
                    @endforeach
                    @if($maxMurid == 0)<div class="murid-row empty-cell">—</div>@endif
                </td>

                <!-- Kode Jadwal -->
                <td class="{{ $cellClass }}">
                    @foreach($murids as $item)
                        @php 
                            $unit = trim(strtoupper($item->murid->bimba_unit ?? '')); 
                        @endphp
                        <div class="murid-row {{ $unitColors[$unit] ?? 'unit-default' }}">
                            {{ $item->murid->kode_jadwal ?? '-' }}
                        </div>
                    @endforeach
                    @if($maxMurid == 0)<div class="murid-row empty-cell">—</div>@endif
                </td>

            @endforeach
        </tr>
    @endfor
</tbody>
                    </table>
                </div>

                <!-- Debug Alert: Cek apakah eager load murid sudah include bimba_unit -->
            @endif
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Init Select2 untuk guru
    $('#guruFilter').select2({
        placeholder: "-- Pilih Motivator / Kepala Unit --",
        allowClear: true,
        width: '100%'
    });

    // Auto submit saat ganti guru
    $('#guruFilter').on('change', function() {
        $('#filterForm').submit();
    });

    // Init Select2 untuk unit (jika admin)
    $('#unitFilter').select2({
        placeholder: "-- Pilih Unit Bimba --",
        allowClear: true,
        width: '100%'
    });

    // Auto submit saat ganti unit
    $('#unitFilter').on('change', function() {
        $('#filterForm').submit();
    });
});

document.addEventListener('keydown', function (e) {

    // Deteksi F5
    if (e.key === 'F5') {

        // hentikan reload default
        e.preventDefault();

        // konfirmasi
        if (confirm('Yakin sinkronisasi jadwal sekarang? Data akan di-update.')) {

            // redirect ke route
            window.location.href = "{{ route('jadwal.generate') }}";
        }
    }

});
</script>
@endpush
@endsection