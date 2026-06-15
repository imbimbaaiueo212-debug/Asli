@extends('layouts.app')

@section('title', 'Absensi Relawan')

@section('content')
    <main>
        <div class="container-fluid px-4">
            <h2 class="mt-4 mb-4 fw-bold">Absensi Relawan</h2>

            <div class="card shadow-sm mb-4">
                <div class="card-body">

                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                        <h5 class="mb-0 fw-semibold">Daftar Absensi Relawan</h5>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Import & Export -->
                    <div class="card shadow-sm mb-4 border-0 bg-light">
                        <div class="card-body py-3">
                            <h6 class="card-title mb-3 fw-semibold">Import / Export Data Absensi</h6>
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <form action="{{ route('absensi-relawan.import.store') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="input-group input-group-sm">
                                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                                            <button type="submit" class="btn btn-success">
                                                <i class="bi bi-upload me-1"></i> Import Data
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            Format: xlsx, xls, csv. Data akan di-normalisasi otomatis.
                                        </small>
                                    </form>
                                </div>

                                <div class="col-md-6 text-md-end">
                                    <a href="{{ route('absensi-relawan.export') . '?' . http_build_query($filters ?? []) }}"
                                       class="btn btn-primary btn-sm">
                                        <i class="bi bi-download me-1"></i> Export ke Excel
                                    </a>
                                    <small class="text-muted d-block mt-1">
                                        Ekspor sesuai filter saat ini
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter -->
                    <form method="GET" action="{{ route('absensi-relawan.index') }}" id="filterForm" class="mb-4">
                        <div class="row g-3 align-items-end">

                            @if (auth()->check() && (auth()->user()->is_admin ?? false))
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label fw-bold small">Unit biMBA</label>
                                    <select name="bimba_unit" id="unitFilter" class="form-select form-select-sm">
                                        <option value="">-- Semua Unit --</option>
                                        @foreach($unitOptions as $u)
                                            <option value="{{ $u }}" {{ ($filters['bimba_unit'] ?? '') == $u ? 'selected' : '' }}>
                                                {{ $u }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="col-md-{{ auth()->check() && (auth()->user()->is_admin ?? false) ? '4' : '5' }} col-lg-4">
                                <label class="form-label fw-bold small">NIK | Nama Relawan</label>
                                <select name="nik" id="nikFilter" class="form-select form-select-sm">
                                    <option value="">-- Cari NIK atau Nama --</option>
                                    @foreach($muridOptions as $m)
                                        <option value="{{ $m->nim }}" {{ ($filters['nik'] ?? '') == $m->nim ? 'selected' : '' }}>
                                            {{ $m->nim }} | {{ $m->nama_murid }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 col-lg-2">
                                <label class="form-label fw-bold small">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">-- Semua Status --</option>
                                    @foreach(['Hadir','Sakit','Izin','Alpa','DT','PC','Cuti','Tidak Aktif'] as $st)
                                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>
                                            {{ $st }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 col-lg-2">
                                <label class="form-label fw-bold small">Tanggal</label>
                                <input type="date" name="tanggal" class="form-control form-control-sm"
                                       value="{{ $filters['tanggal'] ?? '' }}">
                            </div>

                            <div class="col-md-auto d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                                <a href="{{ route('absensi-relawan.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                            </div>

                        </div>
                    </form>

                    <!-- TABEL DENGAN MODAL -->
                    @if($absensi->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 60px;">No</th>
                                        <th style="width: 120px;">NIK</th>
                                        <th>Nama Relawan</th>
                                        <th style="min-width: 20px; text-align: center;">Info</th>
                                        <th>Status Sistem</th>
                                        <th>Keterangan</th>
                                        <th>Alasan</th>
                                        <th>Lembur</th>
                                        <th class="text-center" style="width: 110px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($absensi as $index => $item)
                                        @php
                                            $isManual = property_exists($item, 'id') && $item->id !== null;
                                        @endphp
                                        <tr class="{{ 
                                            $item->status == 'Hadir' ? 'table-success' : 
                                            ($item->status == 'Alpa' ? 'table-danger' : '') 
                                        }}">
                                            <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                                            <td><span class="badge bg-secondary">{{ $item->nik }}</span></td>
                                            <td class="fw-bold">{{ $item->nama_relawaan }}</td>

                                            <!-- TOMBOL INFO + MODAL -->
                                            <td class="text-center">
                                                <button class="btn btn-outline-primary btn-sm info-relawan-btn"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#infoModal"
                                                        data-nama="{{ $item->nama_relawaan }}"
                                                        data-nik="{{ $item->nik }}"
                                                        data-jabatan="{{ $item->profile->jabatan ?? $item->posisi ?? '-' }}"
                                                        data-status="{{ $item->status_relawaan ?? 'Aktif' }}"
                                                        data-departemen="{{ $item->departemen ?? '-' }}"
                                                        data-unit="{{ $item->bimba_unit ?? '-' }}"
                                                        data-cabang="{{ $item->no_cabang ?? '-' }}"
                                                        data-tanggal="{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}"
                                                        data-absensi="{{ $item->absensi ?? '-' }}"
                                                        data-status-sistem="{{ $item->status }}"
                                                        data-keterangan="{{ $item->keterangan ?? '-' }}"
                                                        data-alasan="{{ $item->volunteer_alasan ?? '-' }}">
                                                    <i class="bi bi-info-circle"></i> Info
                                                </button>
                                            </td>                                           
                                            <td class="text-center">
                                                @switch($item->status)
                                                    @case('Hadir')    <span class="badge bg-success">Hadir</span> @break
                                                    @case('Sakit')    <span class="badge bg-info">Sakit</span> @break
                                                    @case('Izin')     <span class="badge bg-warning text-dark">Izin</span> @break
                                                    @case('Alpa')     <span class="badge bg-danger">Alpa</span> @break
                                                    @case('DT')       <span class="badge bg-warning text-dark">DT</span> @break
                                                    @case('PC')       <span class="badge bg-purple text-white">PC</span> @break
                                                    @case('Cuti')     <span class="badge bg-primary">Cuti</span> @break
                                                    @default          <span class="badge bg-secondary">{{ $item->status }}</span>
                                                @endswitch
                                            </td>
                                            <td><small class="text-muted">{{ $item->keterangan ?? '-' }}</small></td>
                                            <td class="{{ $item->alasan ? 'text-danger fw-bold' : 'text-muted' }}">
                                                {{ $item->alasan ?? '-' }}
                                            </td>
                                            <td class="text-center">
                                                {{ floor(($item->jam_lembur ?? 0) / 60) }}j {{ ($item->jam_lembur ?? 0) % 60 }}m
                                            </td>
                                            <td class="text-center text-nowrap">
                                                @if($isManual && (auth()->user()->is_admin ?? false))
                                                    <a href="{{ route('absensi-relawan.edit', $item->id) }}"
                                                       class="btn btn-warning btn-sm me-1" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>

                                                    <form action="{{ route('absensi-relawan.destroy', $item->id) }}"
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('Yakin ingin menghapus absensi ini?');">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-muted small">Otomatis</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
                            <div class="text-muted small">
                                Menampilkan {{ $absensi->firstItem() }}–{{ $absensi->lastItem() }} dari {{ $absensi->total() }} data
                            </div>
                            {{ $absensi->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    @else
                        <div class="text-center py-5 my-5 text-muted">
                            <h5>Belum ada data absensi untuk periode ini</h5>
                            <small class="d-block mt-2">Coba ubah filter atau tambahkan data manual</small>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </main>

    <!-- ==================== MODAL DETAIL INFO ==================== -->
    <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-circle fa-2x me-3"></i>
                        <div>
                            <h5 class="modal-title fw-bold" id="infoModalLabel">Detail Relawan</h5>
                            <small id="modal-nama-header" class="opacity-90"></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <small class="text-muted">Nama</small>
                                            <h5 id="modal-nama" class="fw-bold mb-0"></h5>
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <small class="text-muted">NIK</small>
                                            <h6 id="modal-nik" class="fw-medium text-primary"></h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                            <small class="text-muted">Unit</small>
                            <div id="modal-unit" class="fw-semibold"></div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Cabang</small>
                            <div id="modal-cabang" class="fw-semibold"></div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Tanggal Absen</small>
                            <div id="modal-tanggal" class="fw-semibold"></div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Absensi</small>
                            <div id="modal-absensi" class="fw-semibold"></div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3">
                        <div class="col-12">
                            <small class="text-muted">Keterangan</small>
                            <div id="modal-keterangan" class="fw-medium"></div>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Alasan</small>
                            <div id="modal-alasan" class="fw-medium text-danger"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Absen Masuk (tetap sama) -->
    <div class="modal fade" id="absenMasukModal" tabindex="-1">
        <!-- ... isi modal absen masuk kamu tetap sama ... -->
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function () {
                $('#nikFilter').select2({
                    width: '100%',
                    placeholder: '-- Cari NIK atau Nama --',
                    allowClear: true
                });

                $('#unitFilter').select2({
                    width: '100%',
                    placeholder: '-- Semua Unit --',
                    allowClear: true
                });

                // Modal Info Relawan
                $('#infoModal').on('show.bs.modal', function (event) {
                    const button = $(event.relatedTarget);

                    $('#modal-nama').text(button.data('nama'));
                    $('#modal-nama-header').text(button.data('nama'));
                    $('#modal-nik').text(button.data('nik'));
                    $('#modal-jabatan').text(button.data('jabatan'));
                    $('#modal-status').text(button.data('status'));
                    $('#modal-departemen').text(button.data('departemen'));
                    $('#modal-unit').text(button.data('unit'));
                    $('#modal-cabang').text(button.data('cabang'));
                    $('#modal-tanggal').text(button.data('tanggal'));
                    $('#modal-absensi').text(button.data('status-sistem'));
                    $('#modal-keterangan').text(button.data('keterangan'));
                    $('#modal-alasan').text(button.data('alasan'));
                });
            });

            // Toggle Alasan (jika ada)
            document.addEventListener('DOMContentLoaded', function () {
                const selectKehadiran = document.querySelector('select[name="kehadiran"]');
                const alasanContainer = document.getElementById('alasanContainer');
                if (!selectKehadiran || !alasanContainer) return;

                function toggleAlasan() {
                    const value = selectKehadiran.value.trim();
                    alasanContainer.style.display = (value && value !== 'Hadir') ? 'block' : 'none';
                }

                selectKehadiran.addEventListener('change', toggleAlasan);
                toggleAlasan();
            });
        </script>
    @endpush
@endsection