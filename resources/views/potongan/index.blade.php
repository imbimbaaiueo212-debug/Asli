@extends('layouts.app')

@section('title', 'Potongan Tunjangan')

@section('content')
<div class="card card-body shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Data Potongan Tunjangan</h2>
        <div>
            <a href="{{ route('potongan.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Tambah Data
            </a>
        </div>
    </div>

    <!-- Form Filter Bulan -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body rounded-2">
            <form method="GET" action="{{ route('potongan.index') }}" class="row g-3 align-items-end">
                <!-- Bulan Mulai -->
                <div class="col-lg-3 col-md-4">
                    <label class="form-label fw-semibold small">Bulan Mulai</label>
                    <input type="month" name="month_from" class="form-control"
                           value="{{ old('month_from', $filter_month_from ?? request('month_from')) }}">
                </div>

                <!-- Bulan Selesai -->
                <div class="col-lg-3 col-md-4">
                    <label class="form-label fw-semibold small">Bulan Selesai</label>
                    <input type="month" name="month_to" class="form-control"
                           value="{{ old('month_to', $filter_month_to ?? request('month_to')) }}">
                </div>

                <!-- Tombol -->
                <div class="col-lg-3 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('potongan.index') }}" class="btn btn-outline-secondary w-100">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tampilan Filter Aktif -->
    @if(!empty($filter_month_from) || !empty($filter_month_to))
        @php
            function _monthLabel($m) {
                if (!$m) return '-';
                try {
                    return \Carbon\Carbon::createFromFormat('Y-m', $m)->translatedFormat('M Y');
                } catch (\Throwable $e) {
                    try {
                        return \Carbon\Carbon::parse($m)->translatedFormat('M Y');
                    } catch (\Throwable $e2) {
                        return $m;
                    }
                }
            }
        @endphp
        <div class="mb-3">
            <strong>Filter aktif:</strong>
            Bulan {{ _monthLabel($filter_month_from) }} s/d {{ _monthLabel($filter_month_to) }}
        </div>
    @endif

    <!-- Notifikasi -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tabel Data Potongan Tunjangan -->
    <div class="table-responsive">
        <table class="table table-sm table-bordered text-center align-middle">
            <thead class="table-light">
                <tr>
                    <th>NO</th>
                    <th>NIK</th>
                    <th>NAMA</th>
                    <th class="text-center" style="min-width: 130px;">INFO</th>
                    <th>SAKIT</th>
                    <th>IZIN</th>
                    <th>ALPA</th>
                    <th>TIDAK AKTIF</th>
                    <th>KELEBIHAN (Rp)</th>
                    <th>ASAL KELEBIHAN</th>
                    <th>LAIN-LAIN</th>
                    <th>CASH ADVANCE (Rp)</th>
                    <th>TOTAL</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($potonganTunjangans as $p)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $p->nik ?? '-' }}</td>
                        <td class="text-start">{{ $p->nama }}</td>

                        <!-- ==================== KOLOM INFO + MODAL ==================== -->
                        <td class="text-center">
                            <button type="button" 
                                    class="btn btn-outline-info btn-sm info-pegawai-btn"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#infoModal"
                                    data-nama="{{ $p->nama ?? '-' }}"
                                    data-nik="{{ $p->nik ?? '-' }}"
                                    data-jabatan="{{ $p->jabatan ?? '-' }}"
                                    data-status="{{ $p->status ?? '-' }}"
                                    data-departemen="{{ $p->departemen ?? '-' }}"
                                    data-unit="{{ $p->bimba_unit ?? '-' }}"
                                    data-cabang="{{ $p->no_cabang ?? '-' }}">
                                <i class="bi bi-info-circle"></i> Info
                            </button>
                        </td>

                        <td>{{ number_format($p->sakit ?? 0, 0, ',', '.') }}</td>
                        <td>{{ number_format($p->izin ?? 0, 0, ',', '.') }}</td>
                        <td>{{ number_format($p->alpa ?? 0, 0, ',', '.') }}</td>
                        <td>{{ number_format($p->tidak_aktif ?? 0, 0, ',', '.') }}</td>

                        <td>{{ number_format($p->kelebihan_nominal ?? $p->kelebihan ?? 0, 0, ',', '.') }}</td>

                        <td>
                            @if(!empty($p->kelebihan_bulan) && preg_match('/^\d{4}-\d{2}$/', $p->kelebihan_bulan))
                                {{ \Carbon\Carbon::parse($p->kelebihan_bulan)->translatedFormat('M Y') }}
                            @else
                                {{ $p->kelebihan_bulan ?? '-' }}
                            @endif
                        </td>

                        <td>{{ number_format($p->lain_lain ?? 0, 0, ',', '.') }}</td>
                        <td>{{ number_format($p->cash_advance_nominal ?? 0, 0, ',', '.') }}</td>

                        <!-- TOTAL -->
                        <td>
                            <strong>
                                {{ number_format(
                                    ($p->sakit ?? 0) +
                                    ($p->izin ?? 0) +
                                    ($p->alpa ?? 0) +
                                    ($p->tidak_aktif ?? 0) +
                                    ($p->kelebihan_nominal ?? $p->kelebihan ?? 0) +
                                    ($p->lain_lain ?? 0) +
                                    ($p->cash_advance_nominal ?? 0),
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </strong>
                        </td>

                       <td class="text-nowrap">
    @if($p->id)
        <!-- Data sudah ada -->
        @if (auth()->user()?->role === 'admin')
        <a href="{{ route('potongan.show', $p->id) }}" class="btn btn-info btn-sm">Detail</a>
        
        
            <a href="{{ route('potongan.edit', $p->id) }}" class="btn btn-warning btn-sm">Edit</a>
            
            <form action="{{ route('potongan.destroy', $p->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Yakin hapus data ini?')" 
                        class="btn btn-danger btn-sm">Hapus</button>
            </form>
        @endif

    @else
        <!-- Belum ada data potongan -->
        
    @endif
</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="text-center py-4">
                            Belum ada data
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <style>
        .table td, .table th {
            vertical-align: middle;
        }
    </style>
</div>

<!-- ==================== MODAL INFO PEGAWAI ==================== -->
<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold" id="infoModalLabel">Informasi Pegawai</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <small class="text-muted">Nama</small>
                        <h5 id="modal-nama" class="fw-bold mb-1"></h5>
                        <small class="text-muted">NIK</small>
                        <p id="modal-nik" class="fw-semibold text-primary mb-0"></p>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Jabatan</small>
                        <div id="modal-jabatan" class="fw-semibold"></div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Status</small>
                        <div id="modal-status" class="fw-semibold"></div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Departemen</small>
                        <div id="modal-departemen" class="fw-semibold"></div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Unit biMBA</small>
                        <div id="modal-unit" class="fw-semibold"></div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">No. Cabang</small>
                        <div id="modal-cabang" class="fw-semibold"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const infoModal = document.getElementById('infoModal');

        if (infoModal) {
            infoModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;

                document.getElementById('modal-nama').textContent = button.getAttribute('data-nama');
                document.getElementById('modal-nik').textContent = button.getAttribute('data-nik');
                document.getElementById('modal-jabatan').textContent = button.getAttribute('data-jabatan');
                document.getElementById('modal-status').textContent = button.getAttribute('data-status');
                document.getElementById('modal-departemen').textContent = button.getAttribute('data-departemen');
                document.getElementById('modal-unit').textContent = button.getAttribute('data-unit');
                document.getElementById('modal-cabang').textContent = button.getAttribute('data-cabang');
            });
        }
    });
</script>
@endpush
@endsection