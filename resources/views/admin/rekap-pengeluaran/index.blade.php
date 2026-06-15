@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4 text-center">Rekap Pengeluaran - Pendapatan Tunjangan Saja</h1>

    <!-- Filter -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Bulan</label>
                    <select name="bulan" class="form-select">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tahun</label>
                    <input type="number" name="tahun" value="{{ $tahun }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Unit biMBA</label>
                    <select name="bimba_unit" class="form-select">
                        <option value="">— Semua Unit —</option>
                        @foreach($units as $u)
                            <option value="{{ $u->biMBA_unit }}" {{ $bimbaUnit == $u->biMBA_unit ? 'selected' : '' }}>
                                {{ $u->biMBA_unit }} ({{ $u->no_cabang ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Informasi Periode -->
    <div class="alert alert-info mb-4">
        Periode: <strong>{{ str_pad($bulan, 2, '0', STR_PAD_LEFT) }} / {{ $tahun }}</strong>
        @if($bimbaUnit)
            • Unit: <strong>{{ $bimbaUnit }}</strong>
        @else
            • Semua Unit
        @endif
    </div>

    <!-- Rekap Tunjangan -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Pendapatan Tunjangan</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped text-center">
                <thead class="table-light">
                    <tr>
                        <th>Jumlah Relawan</th>
                        <th>Total Pengeluaran</th>
                        
                        <th class="bg-success text-white">Total Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold">{{ number_format($rekap['pendapatan_tunj']->jumlah_relawan ?? 0) }}</td>
                        <td>Rp {{ number_format($rekap['pendapatan_tunj']->total_thp ?? 0, 0, ',', '.') }}</td>
                        
                        <td class="bg-success text-white fw-bold fs-5">
                            Rp {{ number_format($rekap['grand_total_pendapatan'] ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>

            @if(($rekap['grand_total_pendapatan'] ?? 0) == 0)
                <div class="alert alert-warning mt-3">
                    <strong>Belum ada data</strong> untuk periode ini di tabel pendapatan_tunjangan.<br>
                    Silakan generate data terlebih dahulu di menu Pendapatan Tunjangan.
                </div>
            @endif
        </div>
    </div>

    <!-- Debug cepat -->
    <div class="mt-4 p-3 bg-light border">
        <small>Debug:</small><br>
        Periode dicari: <strong>{{ $rekap['periode'] ?? 'tidak ada' }}</strong><br>
        Filter bimba_unit: <strong>{{ $bimbaUnit ?? 'kosong' }}</strong>
    </div>
</div>
@endsection