@extends('layouts.app')

@section('title', 'Rekap Jadwal Guru biMBA')

@section('content')
<main>
    <div class="container-fluid px-4">
        <h2 class="mt-4 mb-4 fw-bold">Rekap Jadwal & Absensi Guru biMBA</h2>

        <div class="card shadow-sm mb-4">
            <div class="card-body">

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <h5 class="mb-0 fw-semibold">Rekap Jadwal Guru</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('rekap.updateKodeJadwal') }}" 
                           class="btn btn-outline-primary btn-sm"
                           onclick="return confirm('Yakin sinkron semua data dari Profile?')">
                            <i class="bi bi-arrow-repeat me-1"></i> Sinkron Data
                        </a>
                    </div>
                </div>

                <!-- Filter -->
                <form id="filterForm" class="mb-4">
                    <div class="row g-3 align-items-end">
                        @if (auth()->check() && (auth()->user()->is_admin ?? false))
                            <div class="col-md-4 col-lg-3">
                                <label class="form-label fw-bold small">Unit & Cabang</label>
                                <select id="filterUnitCabang" class="form-select text-start">
                                    <option value="">— Semua Unit —</option>
                                    @foreach($rekap->unique(fn($r) => ($r->bimba_unit ?? '').'|'.($r->no_cabang ?? ''))->sortBy(['bimba_unit', 'no_cabang']) as $row)
                                        <option value="{{ ($row->bimba_unit ?? '').'|'.($row->no_cabang ?? '') }}">
                                            {{ $row->bimba_unit ?? '—' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                       <div class="col-md-{{ auth()->check() && (auth()->user()->is_admin ?? false) ? '5' : '8' }}">
                            <label class="form-label fw-bold small">Pilih Relawan</label>
                            <select id="searchSelect" class="form-select">
                                <option value="">— Semua Relawan —</option>
                                @foreach($rekap->unique('nama_relawan')->sortBy('nik') as $row)
                                    <option value="{{ strtolower($row->nama_relawan) }}">
                                         {{ $row->nik ?? '-' }} | {{ $row->nama_relawan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-auto">
                            <button type="button" onclick="resetFilters()" class="btn btn-outline-secondary">
                                Reset
                            </button>
                        </div>
                    </div>
                </form>

                                <style>
                /* Semua cell */
                #rekapTable th,
                #rekapTable td {
                    white-space: nowrap;
                    text-align: center;
                    padding: 3px 4px;
                    font-size: 15px;
                }


                /* SRJ */
                #rekapTable th:nth-child(n+5):nth-child(-n+12),
                #rekapTable td:nth-child(n+5):nth-child(-n+12) {
                    background-color: #e6f4ea;
                }

                /* SKS */
                #rekapTable th:nth-child(n+13):nth-child(-n+16),
                #rekapTable td:nth-child(n+13):nth-child(-n+16) {
                    background-color: #e7f3ff;
                }

                /* S6 */
                #rekapTable th:nth-child(n+17):nth-child(-n+20),
                #rekapTable td:nth-child(n+17):nth-child(-n+20) {
                    background-color: #fff4e5;
                }
                </style>

                <!-- TABEL -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm" id="rekapTable">
                        <thead class="table-light">
                            <tr>
                                <th rowspan="2" class="text-center">No</th>
                                <th rowspan="2" class="text-center">NIK</th>
                                <th rowspan="2" class="text-center">Nama Relawan</th>
                                <th rowspan="2" class="text-center" style="width: 70px;">Info</th>
                                <th colspan="8" class="bg-success-subtle text-success text-center fw-bold">SRJ</th>
                                <th colspan="4" class="bg-info-subtle text-info fw-bold text-center">SKS</th>
                                <th colspan="4" class="bg-warning-subtle text-warning-emphasis fw-bold text-center">S6</th>
                                <th rowspan="2">Murid</th>
                                <th rowspan="2">Rombim</th>
                                <th rowspan="2">Adj. RB</th>
                                <th rowspan="2" class="text-center">Aksi</th>
                            </tr>
                            <tr class="small fw-medium">
                                @foreach([108,109,110,111,112,113,114,115] as $k) <th>{{ $k }}</th> @endforeach
                                @foreach([208,209,210,211] as $k) <th>{{ $k }}</th> @endforeach
                                @foreach([308,309,310,311] as $k) <th>{{ $k }}</th> @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekap->sortBy('bimba_unit')->sortBy('no_cabang') as $row)
                                @php
                                    $masaKerja = $row->masa_kerja 
                                        ? intdiv($row->masa_kerja, 12) . ' th ' . ($row->masa_kerja % 12) . ' bl' 
                                        : '-';
                                @endphp
                                <tr data-nama="{{ strtolower($row->nama_relawan ?? '') }}"
                                    data-nik="{{ strtolower($row->nik ?? '') }}"
                                    data-unit="{{ $row->bimba_unit }}"
                                    data-cabang="{{ $row->no_cabang }}">
                                    
                                    <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                                    <td class="font-monospace">{{ $row->nik ?? '—' }}</td>
                                    <td class="fw-semibold">{{ $row->nama_relawan }}</td>

                                    <!-- TOMBOL INFO -->
                                    <td class="text-center">
                                        <button class="btn btn-outline-primary btn-sm info-relawan-btn"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#infoModal"
                                            data-nama="{{ $row->nama_relawan }}"
                                            data-nik="{{ $row->nik ?? '-' }}"
                                            data-jabatan="{{ $row->jabatan ?? '-' }}"
                                            data-status="{{ $row->status_karyawan ?? 'Aktif' }}"
                                            data-departemen="{{ $row->departemen ?? '-' }}"
                                            data-unit="{{ $row->bimba_unit ?? '-' }}"
                                            data-cabang="{{ $row->no_cabang ?? '-' }}"
                                            data-masa-kerja="{{ $masaKerja }}"
                                            data-murid="{{ $row->jumlah_murid ?? 0 }}"
                                            data-rombim="{{ $row->jumlah_rombim ?? 0 }}">
                                            <i class="bi bi-info-circle"></i>Info
                                        </button>
                                    </td>

                                    

                                    <!-- SRJ, SKS, S6 tetap sama seperti sebelumnya -->
                                    @foreach([108,109,110,111,112,113,114,115] as $kode)
                                        <td>{{ $row->{"srj_{$kode}"} ?? '' }}</td>
                                    @endforeach
                                    @foreach([208,209,210,211] as $kode)
                                        <td>{{ $row->{"sks_{$kode}"} ?? '' }}</td>
                                    @endforeach
                                    @foreach([308,309,310,311] as $kode)
                                        <td>{{ $row->{"s6_{$kode}"} ?? '' }}</td>
                                    @endforeach

                                    <td>{{ $row->jumlah_murid ?? '—' }}</td>
                                    <td>{{ $row->jumlah_rombim ?? '—' }}</td>
                                    <td class="fw-bold text-danger">{{ $row->penyesuaian_rb ?? 0 }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('rekap.edit', $row->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="20" class="text-center py-5">Belum ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- ==================== MODAL INFO FULL LENGKAP ==================== -->
<div class="modal fade" id="infoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
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
                    <div class="col-md-6">
                        <small class="text-muted">Nama Lengkap</small>
                        <h5 id="modal-nama" class="fw-bold"></h5>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted">NIK</small>
                        <h6 id="modal-nik" class="fw-medium text-primary"></h6>
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
                        <small class="text-muted">Cabang</small>
                        <div id="modal-cabang" class="fw-semibold"></div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted">Jumlah Murid</small>
                        <div id="modal-murid" class="fw-bold text-success fs-5"></div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Rombongan Belajar</small>
                        <div id="modal-rombim" class="fw-bold"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const searchSelect = document.getElementById('searchSelect');
    const filterUnitCabang = document.getElementById('filterUnitCabang');
    const rows = document.querySelectorAll('#rekapTable tbody tr');

    function filterTable() {
        const selected = (searchSelect?.value || '').toLowerCase();
        const unitFilter = filterUnitCabang ? filterUnitCabang.value : '';

        rows.forEach(row => {
            const nama = (row.dataset.nama || '').toLowerCase();
            const unitCabang = `${row.dataset.unit || ''}|${row.dataset.cabang || ''}`;

            const matchSearch = !selected || nama === selected;
            const matchUnit = !unitFilter || unitCabang === unitFilter;

            row.style.display = (matchSearch && matchUnit) ? '' : 'none';
        });
    }

    if (searchSelect) {
        searchSelect.addEventListener('change', filterTable);
    }

    if (filterUnitCabang) {
        filterUnitCabang.addEventListener('change', filterTable);
    }

});
document.addEventListener('DOMContentLoaded', function () {

    const infoButtons = document.querySelectorAll('.info-relawan-btn');

    infoButtons.forEach(btn => {
        btn.addEventListener('click', function () {

            // Ambil data dari tombol
            const nama = this.dataset.nama || '-';
            const nik = this.dataset.nik || '-';
            const jabatan = this.dataset.jabatan || '-';
            const status = this.dataset.status || '-';
            const departemen = this.dataset.departemen || '-';
            const unit = this.dataset.unit || '-';
            const cabang = this.dataset.cabang || '-';
            const masaKerja = this.dataset.masaKerja || '-';
            const murid = this.dataset.murid || '0';
            const rombim = this.dataset.rombim || '0';

            // Inject ke modal
            document.getElementById('modal-nama').textContent = nama;
            document.getElementById('modal-nama-header').textContent = nama;
            document.getElementById('modal-nik').textContent = nik;
            document.getElementById('modal-jabatan').textContent = jabatan;
            document.getElementById('modal-status').textContent = status;
            document.getElementById('modal-departemen').textContent = departemen;
            document.getElementById('modal-unit').textContent = unit;
            document.getElementById('modal-cabang').textContent = cabang;
            document.getElementById('modal-murid').textContent = murid;
            document.getElementById('modal-rombim').textContent = rombim;

        });
    });

});
</script>
@endpush