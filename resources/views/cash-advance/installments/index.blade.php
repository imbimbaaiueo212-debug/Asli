@extends('layouts.app')

@section('title', 'Cicilan Cash Advance')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h3 class="mb-4 fw-bold text-primary">Cicilan Cash Advance</h3>

            <!-- Filter -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="small text-muted">Nama Relawan</label>
                            <input type="text" name="nama" class="form-control form-control-sm"
                                   value="{{ request('nama') }}" placeholder="Cari nama...">
                        </div>
                        <div class="col-md-2">
                            <label class="small text-muted">Bulan</label>
                            <select name="bulan" class="form-select form-select-sm">
                                <option value="">-- Semua Bulan --</option>
                                @foreach([
                                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                                    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                                    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                ] as $k => $v)
                                    <option value="{{ $k }}" {{ request('bulan') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="small text-muted">Tahun</label>
                            <select name="tahun" class="form-select form-select-sm">
                                <option value="">-- Semua Tahun --</option>
                                @foreach($years as $y)
                                    <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="small text-muted">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">-- Semua Status --</option>
                                <option value="belum" {{ request('status') == 'belum' ? 'selected' : '' }}>Belum Dibayar</option>
                                <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Sudah Dibayar</option>
                            </select>
                        </div>
                        <div class="col-md-3 text-end">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            <a href="{{ route('cash-advance.installments.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel -->
            <div class="card shadow">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th class="ps-4">Nama Relawan</th>

                                    {{-- KOLOM biMBA UNIT & JABATAN – HANYA UNTUK ADMIN --}}
                                    @if (auth()->check() && (auth()->user()->is_admin ?? false))
                                        <th>biMBA Unit</th>
                                        <th>Jabatan</th>
                                    @endif

                                    <th>Cicilan Ke</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Nominal Angsuran</th>
                                    <th>Status</th>
                                    <th>Nominal Pengajuan</th>
                                    <th>Sisa Tenor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($installments as $item)
                                    <tr>
                                        <td class="ps-4 fw-medium">{{ $item->cashAdvance->nama }}</td>

                                        {{-- Hanya admin yang melihat kolom ini --}}
                                        @if (auth()->check() && (auth()->user()->is_admin ?? false))
                                            <td class="text-center">
                                                {{ $item->cashAdvance->profile?->unit?->biMBA_unit ?? '-' }}
                                            </td>
                                            <td class="text-center">{{ $item->cashAdvance->jabatan ?? '-' }}</td>
                                        @endif

                                        <td class="text-center">{{ $item->cicilan_ke }}</td>
                                        <td class="text-center">
                                            {{ \Carbon\Carbon::parse($item->jatuh_tempo)->locale('id')->translatedFormat('F Y') }}
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            Rp {{ number_format($item->nominal_angsuran, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $item->status == 'lunas' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ $item->status == 'lunas' ? 'Sudah Dibayar' : 'Belum Dibayar' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            Rp {{ number_format($item->cashAdvance->nominal_pinjam, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center fw-bold text-info">
                                            {{ $item->cashAdvance->jangka_waktu - $item->cicilan_ke + 1 }} bulan tersisa
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->check() && (auth()->user()->is_admin ?? false) ? '9' : '7' }}" class="text-center py-5 text-muted">
                                            Belum ada jadwal cicilan Cash Advance.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($installments->hasPages())
                    <div class="card-footer bg-light">
                        {{ $installments->links('vendor.pagination.bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection