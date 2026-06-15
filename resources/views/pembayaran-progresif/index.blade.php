@extends('layouts.app')

@section('title', 'Pembayaran Progresif')

@section('content')
<div class="container-fluid py-4 card card-body">

    <h2 class="mb-4">Data Pembayaran Progresif</h2>

    <!-- FORM FILTER -->
    <form method="GET" action="{{ route('pembayaran-progresif.index') }}" class="row g-3 mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Filter Data Pembayaran Progresif</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">

                        <!-- Unit biMBA – HANYA UNTUK ADMIN -->
                        @if (auth()->check() && (auth()->user()->is_admin ?? false))
                            <div class="col-md-3">
                                <label for="bimba_unit" class="form-label fw-semibold">Unit biMBA</label>
                                <select name="bimba_unit" id="bimba_unit" class="form-select">
                                    <option value="">-- Semua Unit --</option>
                                    @foreach($bimbaUnitList ?? [] as $unit)
                                        <option value="{{ $unit }}" {{ request('bimba_unit') == $unit ? 'selected' : '' }}>
                                            {{ $unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Bulan -->
                        <div class="col-md-3">
                            <label for="bulan" class="form-label fw-semibold">Bulan</label>
                            <select name="bulan" id="bulan" class="form-select">
                                <option value="">-- Semua Bulan --</option>
                                @php
                                    $daftarBulan = [
                                        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                                    ];
                                @endphp
                                @foreach($daftarBulan as $bulan)
                                    <option value="{{ $bulan }}" {{ request('bulan') == $bulan ? 'selected' : '' }}>
                                        {{ $bulan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Relawan (Nama Karyawan) -->
                        <div class="col-md-3">
                            <label for="nama" class="form-label fw-semibold">Relawan</label>
                            <select name="nama" id="nama" class="form-select">
                                <option value="">-- Semua Relawan --</option>
                                @foreach($karyawanList ?? [] as $nama)
                                    <option value="{{ $nama }}" {{ request('nama') == $nama ? 'selected' : '' }}>
                                        {{ $nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-filter me-1"></i> Tampilkan
                            </button>
                            @if(request()->hasAny(['bimba_unit', 'bulan', 'nama']))
                                <a href="{{ route('pembayaran-progresif.index') }}" class="btn btn-outline-secondary flex-grow-1">
                                    <i class="bi bi-arrow-repeat me-1"></i> Reset
                                </a>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Pesan Sukses -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tabel Data -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle table-hover card-body">
            <thead class="table-light card-body text-center">
                <tr>
                    <th>NO</th>
                    <th>NAMA</th>

                    {{-- KOLOM UNIT BIMBA & NO CABANG – HANYA UNTUK ADMIN --}}
                    @if (auth()->check() && (auth()->user()->is_admin ?? false))
                        <th>UNIT BIMBA</th>
                        <th>NO CABANG</th>
                    @endif

                    <th>JABATAN</th>
                    <th>STATUS</th>
                    <th>DEPARTEMEN</th>
                    <th>MASA KERJA</th>
                    <th>NO REKENING</th>
                    <th>BANK</th>
                    <th>ATAS NAMA</th>
                    <th>THP</th>
                    <th>KURANG</th>
                    <th>LEBIH</th>
                    <th>BULAN</th>
                    <th>TRANSFER</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembayaran as $key => $row)
                    <tr>
                        <td class="text-center">{{ $key + 1 }}</td>
                        <td>{{ $row->nama }}</td>

                        {{-- Hanya admin yang melihat kolom ini --}}
                        @if (auth()->check() && (auth()->user()->is_admin ?? false))
                            <td>{{ $row->bimba_unit ?? '-' }}</td>
                            <td>{{ $row->no_cabang ?? '-' }}</td>
                        @endif

                        <td>{{ $row->jabatan }}</td>
                        <td>{{ $row->status }}</td>
                        <td>{{ $row->departemen ?? '-' }}</td>
                        <td>{{ $row->masa_kerja ?? '-' }}</td>
                        <td>{{ $row->no_rekening ?? '-' }}</td>
                        <td>{{ $row->bank ?? '-' }}</td>
                        <td>{{ $row->atas_nama ?? '-' }}</td>
                        <td class="text-end">{{ number_format($row->thp, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($row->kurang, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($row->lebih, 0, ',', '.') }}</td>
                        <td>{{ $row->bulan }}</td>
                        <td class="text-end">{{ number_format($row->transfer, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->check() && (auth()->user()->is_admin ?? false) ? '16' : '14' }}" class="text-center py-4 text-muted">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            Belum ada data yang sesuai dengan filter
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection