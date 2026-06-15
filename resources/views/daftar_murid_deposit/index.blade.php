@extends('layouts.app')

@section('title', 'Murid Deposit')

@section('content')
<div class="card card-body">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="mb-4">Daftar Murid Deposit</h1>

            <!-- Notifikasi sukses -->
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <!-- FILTER BULAN & TAHUN OTOMATIS -->
            <form method="GET" action="{{ route('daftar_murid_deposit.index') }}" id="filterForm" class="row g-3 mb-4 align-items-end">

    <!-- Unit biMBA: paling kiri di md+ (hanya untuk admin) -->
    @if (auth()->check() && (auth()->user()->is_admin ?? false))
        <div class="col-md-3 order-md-1">
            <label for="unitFilter" class="form-label fw-bold">Unit biMBA</label>
            <select name="unit" id="unitFilter" class="form-select">
                <option value="">— Semua Unit —</option>
                @foreach ($unitOptions as $unit)
                    <option value="{{ $unit }}" {{ request('unit') == $unit ? 'selected' : '' }}>
                        {{ $unit }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    <!-- Bulan: urutan kedua -->
    <div class="col-md-{{ auth()->check() && (auth()->user()->is_admin ?? false) ? '3' : '4' }} order-md-2">
        <label for="bulanFilter" class="form-label fw-bold">Bulan</label>
        <select name="bulan" id="bulanFilter" class="form-select">
            @php
                $namaBulan = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                ];
            @endphp
            @foreach($namaBulan as $b => $nama)
                <option value="{{ str_pad($b, 2, '0', STR_PAD_LEFT) }}" {{ (int)$bulan === $b ? 'selected' : '' }}>
                    {{ $nama }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Tahun: urutan ketiga -->
    <div class="col-md-{{ auth()->check() && (auth()->user()->is_admin ?? false) ? '3' : '4' }} order-md-3">
        <label for="tahunFilter" class="form-label fw-bold">Tahun</label>
        <select name="tahun" id="tahunFilter" class="form-select">
            @foreach(range(date('Y')-3, date('Y')+1) as $t)
                <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>
                    {{ $t }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Tombol Reset: paling kanan -->
    <div class="col-md-{{ auth()->check() && (auth()->user()->is_admin ?? false) ? '3' : '4' }} order-md-4 d-flex align-items-end">
        <a href="{{ route('daftar_murid_deposit.index') }}" class="btn btn-outline-secondary w-100">
            Reset
        </a>
    </div>

</form>

            <!-- TABEL DAFTAR MURID DEPOSIT -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal Transaksi</th>
                            <th>NIM</th>
                            <th>Nama Murid</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Nama Guru</th>
                            <th>biMBA Unit</th>
                            <th>No Cabang</th>
                            <th class="text-end">Jumlah Deposit (Rp)</th>
                            <th>Kategori Deposit</th>
                            <th>Status Deposit</th>
                            <th>Keterangan Deposit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($muridDeposits as $index => $deposit)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $deposit->tanggal_transaksi }}</td>
                                <td>{{ $deposit->nim }}</td>
                                <td>{{ $deposit->nama_murid }}</td>
                                <td>{{ $deposit->kelas }}</td>
                                <td>{{ $deposit->status }}</td>
                                <td>{{ $deposit->nama_guru }}</td>
                                <td>{{ $deposit->bimba_unit ?? '-' }}</td>
                                <td>{{ $deposit->no_cabang ?? '-' }}</td>
                                <td class="text-end">{{ number_format((int) ($deposit->jumlah_deposit ?? 0), 0, ',', '.') }}</td>
                                <td>{{ $deposit->kategori_deposit }}</td>
                                <td>{{ $deposit->status_deposit }}</td>
                                <td>{{ $deposit->keterangan_deposit }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center py-4">Tidak ada data deposit.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
    // Inisialisasi Select2 untuk Bulan & Tahun
    $('#bulanFilter').select2({
        width: '100%',
        placeholder: "Pilih Bulan",
        allowClear: false
    });

    $('#tahunFilter').select2({
        width: '100%',
        placeholder: "Pilih Tahun",
        allowClear: false
    });

    // AUTO FILTER: langsung tampilkan saat pilih Bulan atau Tahun
    $('#bulanFilter, #tahunFilter').on('change', function () {
        $('#filterForm').submit();
    });
});
$(document).ready(function () {
    $('#bulanFilter, #tahunFilter, #unitFilter').select2({
        width: '100%',
        placeholder: function() {
            return $(this).data('placeholder') || "Pilih...";
        },
        allowClear: false
    });

    // Auto submit saat berubah
    $('#bulanFilter, #tahunFilter, #unitFilter').on('change', function () {
        $('#filterForm').submit();
    });
});
</script>
@endpush
