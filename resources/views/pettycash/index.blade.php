@extends('layouts.app')

@section('title', 'Petty Cash')

@section('content')
<div class="container py-4">
    <h4 class="mb-4 fw-bold text-primary">Petty Cash Transaksi</h4>

    <a href="{{ route('pettycash.create') }}" class="btn btn-primary mb-3 shadow-sm">
        <i class="fas fa-plus me-2"></i> Tambah Data
    </a>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ==================== Saldo Awal & Ringkasan ==================== --}}
    <div class="row mb-4 g-4">
        {{-- 🔹 FORM SALDO AWAL → HANYA UNTUK ADMIN 🔹 --}}
        @if(auth()->user()->isAdminUser())
            <div class="col-lg-6">
                <div class="card shadow-sm border-primary h-100">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="fas fa-cog me-2"></i> Pengaturan Saldo Awal (Khusus Admin)
                    </div>
                    <div class="card-body">
                        <form action="{{ route('pettycash.saldo-awal') }}" method="POST" class="row g-3 align-items-end">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Pilih Unit</label>
                                <select name="bimba_unit" class="form-select" required>
                                    <option value="">-- Pilih Unit --</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->biMBA_unit }}" {{ $activeUnit == $unit->biMBA_unit ? 'selected' : '' }}>
                                            {{ $unit->biMBA_unit }} ({{ $unit->no_cabang ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Saldo Awal (Rp)</label>
                                <input type="number" name="saldo_awal" class="form-control text-end fw-bold" 
                                       value="{{ $saldoAwal }}" min="0" step="1000" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-1"></i> Simpan
                                </button>
                            </div>
                        </form>
                        <div class="mt-3 small text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Saldo ini digunakan sebagai dasar jika tidak ada transaksi sebelum periode filter.
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- 🔹 RINGKASAN SALDO → TERLIHAT OLEH SEMUA USER 🔹 --}}
        <div class="col-lg-6 {{ auth()->user()->isAdminUser() ? '' : 'offset-lg-3' }}">
            <div class="card shadow-sm h-100 border-info">
                <div class="card-header bg-info text-white fw-bold">
                    <i class="fas fa-wallet me-2"></i> Ringkasan Saldo
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="fw-semibold">Saldo Awal</span>
                            <span class="fs-5 fw-bold text-primary">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 text-success">
                            <span class="fw-semibold">Total Pemasukan</span>
                            <span class="fs-5 fw-bold">+ Rp {{ number_format($totalDebit, 0, ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 text-danger">
                            <span class="fw-semibold">Total Pengeluaran</span>
                            <span class="fs-5 fw-bold">- Rp {{ number_format($totalKredit, 0, ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 bg-light border-top border-3 border-primary">
                            <span class="fw-bold fs-5">Saldo Akhir</span>
                            <span class="fw-black fs-4 text-primary">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== Filter Transaksi ==================== --}}
    <div class="card shadow-sm mb-4 border-secondary">
        <div class="card-header bg-secondary text-white fw-bold">
            <i class="fas fa-filter me-2"></i> Filter Transaksi
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('pettycash.index') }}" class="row g-3 align-items-end" id="filterForm">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Tanggal Awal</label>
                    <input type="date" name="tanggal_awal" class="form-control" value="{{ $tanggalAwal }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Tanggal Akhir</label>
                    <input type="date" name="tanggal_akhir" class="form-control" value="{{ $tanggalAkhir }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Jenis Transaksi</label>
                    <select name="filter_transaksi" class="form-select">
                        <option value="" {{ $filterTransaksi == '' ? 'selected' : '' }}>Semua</option>
                        <option value="debit" {{ $filterTransaksi == 'debit' ? 'selected' : '' }}>Pemasukan</option>
                        <option value="kredit" {{ $filterTransaksi == 'kredit' ? 'selected' : '' }}>Pengeluaran</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Unit</label>
                    <select name="bimba_unit" class="form-select" id="filterUnit">
                        <option value="">Semua Unit</option>
                        @foreach($units as $u)
                            <option value="{{ $u->biMBA_unit }}" {{ $filterUnit == $u->biMBA_unit ? 'selected' : '' }}>
                                {{ strtoupper($u->biMBA_unit) }} @if($u->no_cabang)({{ $u->no_cabang }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success me-2">
                        <i class="fas fa-search me-1"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('pettycash.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-sync me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== Tabel Transaksi ==================== --}}
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white fw-bold">
            <i class="fas fa-list me-2"></i> Daftar Transaksi Petty Cash
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>NO BUKTI</th>
                            <th>TANGGAL</th>
                            <th>UNIT</th>
                            <th>NO CABANG</th>
                            <th>KATEGORI</th>
                            <th>KETERANGAN</th>
                            <th>DEBIT</th>
                            <th>KREDIT</th>
                            <th>SALDO</th>
                            <th>BUKTI</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $runningSaldo = $saldoAwal; @endphp

                        @forelse($pettycash as $item)
                            {{-- Tampilkan baris Saldo Awal hanya pada baris pertama jika tidak ada filter tanggal --}}
                            @if ($loop->first && empty($tanggalAwal))
                                <tr class="table-warning fw-bold">
                                    <td colspan="6" class="text-start ps-4">SALDO AWAL</td>
                                    <td colspan="3"></td>
                                    <td class="text-end pe-4 fw-bold">{{ number_format($saldoAwal, 0, ',', '.') }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            @endif

                            @php $runningSaldo += $item->debit - $item->kredit; @endphp
                            <tr @if(str_starts_with($item->kategori, '500 | Petty Cash')) class="table-info" @endif>
                                <td>{{ $item->no_bukti }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                <td>{{ $item->bimba_unit ?? '-' }}</td>
                                <td>{{ $item->no_cabang ?? '-' }}</td>
                                <td class="text-start">{{ $item->kategori }}</td>
                                <td class="text-start">{{ $item->keterangan }}</td>
                                <td class="text-success fw-bold">{{ number_format($item->debit, 0, ',', '.') }}</td>
                                <td class="text-danger fw-bold">{{ number_format($item->kredit, 0, ',', '.') }}</td>
                                <td class="fw-bold">{{ number_format($runningSaldo, 0, ',', '.') }}</td>
                                <td>
                                    @if($item->bukti)
                                        <a href="{{ asset('storage/bukti/' . $item->bukti) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('pettycash.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('pettycash.destroy', $item->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus transaksi ini?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i><br>
                                    Belum ada transaksi dalam periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Script untuk auto-submit saat pilih Unit --}}
@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form saat pilih Unit
    $('#filterUnit').on('change', function() {
        $('#filterForm').submit();
    });

    // Opsional: Submit saat tekan Enter di input tanggal
    $('input[name="tanggal_awal"], input[name="tanggal_akhir"]').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $('#filterForm').submit();
        }
    });
});
</script>
@endpush