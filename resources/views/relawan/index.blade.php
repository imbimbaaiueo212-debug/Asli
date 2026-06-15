@extends('layouts.app')

@section('title', 'Absensi Relawan')

@section('content')
    <main>
        <div class="container-fluid px-4">
            <h2 class="mt-4 mb-4 fw-bold">Absensi Relawan</h2>

            <div class="card shadow-sm mb-4">
                <div class="card-body">

                    <!-- Header + Tombol Absen Masuk -->
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                        <h5 class="mb-0 fw-semibold">Daftar Absensi Relawan</h5>
                        <button type="button" class="btn btn-primary" 
                                data-bs-toggle="modal" data-bs-target="#absenMasukModal">
                            <i class="fa-solid fa-plus me-2"></i> Tambah Data
                        </button>
                    </div>

                    <!-- Notifikasi -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error') || session('danger') || session('warning'))
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            {{ session('error') ?? session('danger') ?? session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Import & Export -->
                    <div class="card shadow-sm mb-4 border-0 bg-light">
                        <div class="card-body py-3">
                            <h6 class="card-title mb-3">Import / Export Data Absensi</h6>
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <form action="{{ route('relawan.import') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="input-group input-group-sm">
                                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                                            <button type="submit" class="btn btn-primary">Import Excel</button>
                                        </div>
                                        <small class="text-muted d-block mt-1">File akan difilter sesuai unit Anda</small>
                                    </form>
                                </div>

                                <div class="col-md-6 text-md-end">
                                    <a href="{{ route('relawan.export') . '?' . http_build_query($filters ?? []) }}"
                                       class="btn btn-success btn-sm">
                                        <i class="bi bi-download me-1"></i> Export ke Excel
                                    </a>
                                    <small class="text-muted d-block mt-1">Ekspor sesuai filter saat ini</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter -->
                    <form method="GET" action="{{ route('relawan.index') }}" id="filterForm" class="mb-4">
                        <div class="row g-3 align-items-end">

                            @if (auth()->check() && (auth()->user()->is_admin ?? false))
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label fw-bold small">Unit BIMBA</label>
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

                            <div class="col-md-3 col-lg-{{ auth()->check() && (auth()->user()->is_admin ?? false) ? '2' : '3' }}">
                                <label class="form-label fw-bold small">Tgl Mulai</label>
                                <input type="date" name="date_from" class="form-control form-control-sm"
                                       value="{{ $filters['date_from'] ?? '' }}">
                            </div>

                            <div class="col-md-3 col-lg-{{ auth()->check() && (auth()->user()->is_admin ?? false) ? '2' : '3' }}">
                                <label class="form-label fw-bold small">Tgl Akhir</label>
                                <input type="date" name="date_to" class="form-control form-control-sm"
                                       value="{{ $filters['date_to'] ?? '' }}">
                            </div>

                            <div class="col-md-{{ auth()->check() && (auth()->user()->is_admin ?? false) ? '4' : '5' }} col-lg-{{ auth()->check() && (auth()->user()->is_admin ?? false) ? '4' : '5' }}">
                                <label class="form-label fw-bold small">Nama Relawan (NIK)</label>
                                <select name="nik" id="nikFilter" class="form-select form-select-sm">
                                    <option value="">-- Pilih Nama Relawan --</option>
                                    @foreach($relawanOptions as $r)
                                        <option value="{{ $r->nik }}" {{ ($filters['nik'] ?? '') == $r->nik ? 'selected' : '' }}>
                                            {{ $r->nik }} | {{ $r->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 col-lg-2">
                                <label class="form-label fw-bold small">Status Kehadiran</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">-- Semua Status --</option>
                                    @foreach(['Izin', 'Sakit', 'Alpa', 'DT', 'PC', 'Tidak Aktif', 'Cuti', 'Minggu', 'Libur Nasional'] as $s)
                                        <option value="{{ $s }}" {{ ($filters['status'] ?? '') == $s ? 'selected' : '' }}>
                                            {{ $s }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-auto d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                                <a href="{{ route('relawan.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                            </div>

                        </div>
                    </form>

                    <!-- TABEL ABSENSI RELAWAN -->
<div class="table-responsive">
    <table class="table table-bordered table-hover table-sm">
        <thead class="table-light">
            <tr>
                <th class="text-center" style="width: 60px;">No</th>
                <th style="width: 120px;">NIK</th>
                <th>Nama Relawan</th>
                
                <!-- Kolom Info dengan Tombol Modal -->
                <th style="min-width: 20px; text-align: center;">Info</th>

                <th class="text-center">Tanggal</th>
                <th class="text-center">Jam Masuk</th>
                <th class="text-center">Jam Keluar</th>
                <th class="text-center">Status</th>
                <th class="text-center">Absen Pulang</th>
                <th class="text-center" style="width: 110px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($absensi as $data)
                @php
                    $redDates = ['2025-01-01', '2025-01-27', '2025-01-29', '2025-03-01', '2025-03-28', '2025-03-29', '2025-03-31', '2025-04-01', '2025-04-02', '2025-04-03', '2025-04-04', '2025-04-07', '2025-05-01', '2025-05-12', '2025-05-13', '2025-05-29', '2025-05-30', '2025-06-06', '2025-06-27', '2025-09-05', '2025-12-26'];
                    $isRed = in_array($data->tanggal, $redDates);

                    $lembur = $data->jam_lembur ?? 0;
                    $jam = floor($lembur / 60);
                    $menit = $lembur % 60;
                @endphp
                <tr class="{{ $isRed ? 'table-danger' : '' }}">
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $data->nik ?? '-' }}</td>
                    <td><strong>{{ $data->nama_relawan }}</strong></td>

                    <!-- TOMBOL INFO → MODAL -->
                    <td class="text-center">
                        <button class="btn btn-outline-primary btn-sm info-relawan-btn"
                                data-bs-toggle="modal" 
                                data-bs-target="#infoModal"
                                data-nama="{{ $data->nama_relawan }}"
                                data-nik="{{ $data->nik }}"
                                data-jabatan="{{ $data->jabatan_profile ?? $data->posisi ?? '-' }}"
                                data-status="{{ $data->status_relawaan ?? 'Aktif' }}"
                                data-departemen="{{ $data->departemen_profile ?? '-' }}"
                                data-unit="{{ $data->bimba_unit ?? '-' }}"
                                data-cabang="{{ $data->no_cabang ?? '-' }}"
                                data-tanggal="{{ \Carbon\Carbon::parse($data->tanggal)->format('d M Y') }}"
                                data-jam-masuk="{{ $data->jam_masuk ? \Carbon\Carbon::parse($data->jam_masuk)->format('H:i') : '-' }}"
                                data-jam-keluar="{{ $data->jam_keluar ? \Carbon\Carbon::parse($data->jam_keluar)->format('H:i') : '-' }}"
                                data-status-absen="{{ $data->status }}"
                                data-keterangan="{{ $data->keterangan ?? '-' }}"
                                data-alasan="{{ $data->alasan ?? '-' }}">
                            <i class="bi bi-info-circle"></i> Info
                        </button>
                    </td>

                    <td class="{{ $isRed ? 'text-danger fw-bold' : '' }}">
                        {{ \Carbon\Carbon::parse($data->tanggal)->format('d M Y') }}
                    </td>
                    <td class="text-center">{{ $data->jam_masuk ? \Carbon\Carbon::parse($data->jam_masuk)->format('H:i') : '-' }}</td>
                    <td class="text-center">{{ $data->jam_keluar ? \Carbon\Carbon::parse($data->jam_keluar)->format('H:i') : '-' }}</td>
                    <td>
                        @if(auth()->user()->is_admin ?? false)
                            <form action="{{ route('relawan.updateStatus', $data->id) }}" method="POST" class="d-inline">
                                @csrf @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                                    @foreach(['Hadir', 'Izin', 'Sakit', 'Alpa', 'Libur Nasional', 'Minggu', 'DT', 'PC', 'SKD', 'TSKD', 'TS', 'FF', 'Tidak Aktif'] as $s)
                                        <option value="{{ $s }}" {{ $data->status == $s ? 'selected' : '' }}>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </form>
                        @else
                            <span class="badge {{ in_array($data->status, ['Minggu', 'Libur Nasional']) ? 'bg-danger' : ($data->status == 'Hadir' ? 'bg-success' : 'bg-secondary') }}">
                                {{ $data->status }}
                            </span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if(in_array($data->status, ['Hadir', 'DT', 'PC']))
                            @if(is_null($data->jam_keluar))
                                <button type="button" class="btn btn-warning btn-sm" onclick="absenPulang({{ $data->id }})">
                                    Belum Pulang
                                </button>
                            @else
                                <span class="badge bg-success">Sudah</span>
                            @endif
                        @else
                            <span class="badge bg-secondary">Selesai</span>
                        @endif
                    </td>
                    <td class="text-nowrap">
                        <a href="{{ route('relawan.edit', $data->id) }}" class="btn btn-primary btn-sm">Edit</a>
                        @if(auth()->user()->is_admin ?? false)
                            <form action="{{ route('relawan.destroy', $data->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus data ini?')">Hapus</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted fst-italic">
                        Belum ada data absensi relawan
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

                    <div class="mt-4 d-flex justify-content-center">
                        {{ $absensi->links('pagination::bootstrap-5') }}
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- Modal Absen Masuk (tetap sama seperti sebelumnya) -->
    <div class="modal fade" id="absenMasukModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('relawan.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Absen Masuk Relawan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if(auth()->user()->is_admin ?? false)
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="absen_all" id="absenAllCheckbox">
                                <label class="form-check-label" for="absenAllCheckbox">
                                    Absen Semua Relawan (Admin Only)
                                </label>
                            </div>
                        @endif

                        <div class="mb-3" id="pegawaiSelectBox">
                            <label class="form-label">Pilih Relawan</label>
                            <select name="nik" class="form-control" required>
                                <option value="" disabled {{ old('nik') ? '' : 'selected' }}>-- Pilih Nama Relawan --</option>
                                @foreach($relawanOptions as $item)
                                    <option value="{{ $item->nik }}" {{ old('nik') == $item->nik ? 'selected' : '' }}>
                                        {{ $item->nama }} ({{ $item->bimba_unit }}{{ $item->no_cabang ? ' - ' . $item->no_cabang : '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Tanggal Absen</label>
                            <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                        </div>

                        <div class="mb-3">
                            <label>Kehadiran</label>
                            <select name="kehadiran" class="form-control" required>
                                <option value="">-- Pilih Kehadiran --</option>
                                @foreach([
                                    'Hadir', 'Datang Terlambat', 'Pulang Cepat', 'Sakit Dengan Keterangan Dokter',
                                    'Sakit Tanpa Keterangan Dokter', 'Izin Dengan Form di ACC', 'Izin Tanpa Form di ACC',
                                    'Tidak Mengisi', 'Tidak Masuk Tanpa Form', 'Cuti', 'Tidak Aktif',
                                    'Hari Minggu', 'Libur Nasional'
                                ] as $status)
                                    <option value="{{ $status }}" {{ old('kehadiran') == $status ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Alasan (wajib jika bukan Hadir) -->
                        <div class="mb-3" id="alasanContainer" style="display: none;">
                            <label class="form-label">Alasan <span class="text-danger">*</span></label>
                            <textarea name="alasan" class="form-control" rows="3" placeholder="Jelaskan alasan secara singkat dan jelas (wajib untuk status selain Hadir)"></textarea>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Absen</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- MODAL DETAIL INFO RELAWAN -->
<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0">
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
                                        <h5 id="modal-nama" class="fw-bold"></h5>
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
                        <small class="text-muted">No Cabang</small>
                        <div id="modal-cabang" class="fw-semibold"></div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Tanggal Absen</small>
                        <div id="modal-tanggal" class="fw-semibold"></div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Jam Masuk</small>
                        <div id="modal-jam-masuk" class="fw-semibold"></div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Jam Keluar</small>
                        <div id="modal-jam-keluar" class="fw-semibold"></div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Status Absen</small>
                        <div id="modal-status-absen" class="fw-semibold"></div>
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
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function () {
                $('#nikFilter, #unitFilter').select2({
                    width: '100%',
                    placeholder: function() { return $(this).data('placeholder') || '-- Pilih --'; },
                    allowClear: true
                });

                window.absenPulang = function(id) {
                    if (!confirm('Yakin absen pulang sekarang?')) return;
                    fetch(`/relawan/${id}/pulang`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.error || 'Gagal absen pulang');
                        }
                    })
                    .catch(() => alert('Error koneksi'));
                };

                const checkbox = document.getElementById('absenAllCheckbox');
                if (checkbox) {
                    checkbox.addEventListener('change', function () {
                        document.getElementById('pegawaiSelectBox').style.display = this.checked ? 'none' : 'block';
                    });
                }
            });

            // Toggle Alasan (wajib jika bukan Hadir)
            document.addEventListener('DOMContentLoaded', function () {
                const selectKehadiran = document.querySelector('select[name="kehadiran"]');
                const alasanContainer = document.getElementById('alasanContainer');
                const textareaAlasan  = alasanContainer?.querySelector('textarea[name="alasan"]');

                if (!selectKehadiran || !alasanContainer || !textareaAlasan) return;

                function toggleAlasan() {
                    const value = selectKehadiran.value.trim();
                    const perluAlasan = value && value !== 'Hadir';

                    alasanContainer.style.display = perluAlasan ? 'block' : 'none';

                    if (perluAlasan) {
                        textareaAlasan.setAttribute('required', 'required');
                    } else {
                        textareaAlasan.removeAttribute('required');
                    }
                }

                selectKehadiran.addEventListener('change', toggleAlasan);
                toggleAlasan(); // Jalankan pertama kali
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
                $('#modal-jam-masuk').text(button.data('jam-masuk'));
                $('#modal-jam-keluar').text(button.data('jam-keluar'));
                $('#modal-status-absen').text(button.data('status-absen'));
                $('#modal-keterangan').text(button.data('keterangan'));
                $('#modal-alasan').text(button.data('alasan'));
            });
        </script>
    @endpush
@endsection