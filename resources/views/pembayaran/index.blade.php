@extends('layouts.app')

@section('title', 'Pembayaran Tunjangan')

@section('content')
<div class="card card-body shadow-sm">

    <div class="container-fluid px-0 px-md-3">

        <h2 class="text-center mb-4">Daftar Pembayaran Tunjangan</h2>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mx-3 mx-md-0" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Form Filter --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('pembayaran.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-lg-4">
                <label class="form-label fw-medium small">Nama Relawan</label>
                <select name="nama" class="form-select">
                    <option value="">-- Semua Relawan --</option>
                    @foreach($allNames as $n)
                        <option value="{{ $n }}" {{ ($nama ?? '') == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-12 col-lg-3">
                <label class="form-label fw-medium small">Dari Bulan</label>
                <select name="month_from" class="form-select">
                    <option value="">-- Pilih Bulan --</option>
                    @foreach($allMonths as $b)
                        <option value="{{ $b }}" {{ ($monthFrom ?? '') == $b ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($b)->translatedFormat('F Y') }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-12 col-lg-3">
                <label class="form-label fw-medium small">Sampai Bulan</label>
                <select name="month_to" class="form-select">
                    <option value="">-- Pilih Bulan --</option>
                    @foreach($allMonths as $b)
                        <option value="{{ $b }}" {{ ($monthTo ?? '') == $b ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($b)->translatedFormat('F Y') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-lg-2 d-grid">
                <button type="submit" class="btn btn-primary">Tampilkan</button>
            </div>
        </form>
    </div>
</div>

        {{-- Tabel Desktop / Tablet --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle table-sm">
                <thead class="table-light">
                    <tr class="text-center">
                        <th style="width: 50px;">NO</th>
                        <th style="min-width: 110px;">NIK</th>
                        <th style="min-width: 180px;">NAMA</th>
                        <th style="min-width: 130px;" class="text-center">INFO</th>
                        <th style="min-width: 130px;">PENDAPATAN</th>
                        <th style="min-width: 90px;">SAKIT</th>
                        <th style="min-width: 90px;">IZIN</th>
                        <th style="min-width: 90px;">ALPA</th>
                        <th style="min-width: 110px;">TIDAK AKTIF</th>
                        <th style="min-width: 110px;">KELEBIHAN</th>
                        <th style="min-width: 100px;">LAIN-LAIN</th>
                        <th style="min-width: 130px;">POTONGAN</th>
                        <th style="min-width: 140px;">DIBAYARKAN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembayaranTunjangans as $key => $p)
                        <tr>
                            <td class="text-center">{{ $key + 1 }}</td>
                            <td class="text-center font-monospace">{{ $p->nik ?? '-' }}</td>
                            <td class="fw-medium">{{ $p->nama }}</td>

                            <!-- INFO Modal -->
                            <td class="text-center">
                                <button type="button" 
                                        class="btn btn-outline-info btn-sm px-3 info-pegawai-btn"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#infoModal"
                                        data-nama="{{ $p->nama ?? '-' }}"
                                        data-nik="{{ $p->nik ?? '-' }}"
                                        data-jabatan="{{ $p->jabatan ?? '-' }}"
                                        data-status="{{ $p->status ?? '-' }}"
                                        data-departemen="{{ $p->departemen ?? '-' }}"
                                        data-unit="{{ $p->bimba_unit ?? '-' }}"
                                        data-cabang="{{ $p->no_cabang ?? '-' }}"
                                        data-masakerja="{{ filled($p->masa_kerja) ? $p->masa_kerja : '0 bln' }}"
                                        data-no_rekening="{{ $p->no_rekening ?? '-' }}"
                                        data-bank="{{ $p->bank ?? '-' }}"
                                        data-atas_nama="{{ $p->atas_nama ?? '-' }}">
                                    <i class="bi bi-info-circle me-1"></i>Info
                                </button>
                            </td>

                            <td class="text-end fw-bold">{{ number_format($p->pendapatan ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($p->sakit ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($p->izin ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($p->alpa ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($p->tidak_aktif ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($p->kelebihan ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($p->lain_lain ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold text-danger">
                                {{ number_format(
                                    ($p->sakit ?? 0) + ($p->izin ?? 0) + ($p->alpa ?? 0) +
                                    ($p->tidak_aktif ?? 0) + ($p->kelebihan ?? 0) + ($p->lain_lain ?? 0),
                                    0, ',', '.'
                                ) }}
                            </td>
                            <td class="text-end fw-bold text-success">{{ number_format($p->dibayarkan ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-center py-5 text-muted">
                                Belum ada data pembayaran tunjangan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile View (Card) --}}
        <div class="d-md-none mt-4">
            @forelse($pembayaranTunjangans as $key => $p)
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="fw-bold mb-1">{{ $p->nama }}</h6>
                                <small class="text-muted">{{ $p->nik ?? '-' }}</small>
                            </div>
                            <span class="badge bg-primary fs-6">{{ $key + 1 }}</span>
                        </div>

                        <button type="button" 
                                class="btn btn-outline-info btn-sm w-100 mb-3 info-pegawai-btn"
                                data-bs-toggle="modal" 
                                data-bs-target="#infoModal"
                                data-nama="{{ $p->nama ?? '-' }}"
                                data-nik="{{ $p->nik ?? '-' }}"
                                data-jabatan="{{ $p->jabatan ?? '-' }}"
                                data-status="{{ $p->status ?? '-' }}"
                                data-departemen="{{ $p->departemen ?? '-' }}"
                                data-unit="{{ $p->bimba_unit ?? '-' }}"
                                data-cabang="{{ $p->no_cabang ?? '-' }}"
                                data-masakerja="{{ filled($p->masa_kerja) ? $p->masa_kerja : '0 bln' }}"
                                data-no_rekening="{{ $p->no_rekening ?? '-' }}"
                                data-bank="{{ $p->bank ?? '-' }}"
                                data-atas_nama="{{ $p->atas_nama ?? '-' }}">
                            <i class="bi bi-info-circle"></i> Lihat Detail Info
                        </button>

                        <div class="row g-2 small">
                            <div class="col-6">
                                <div class="text-muted">Pendapatan</div>
                                <div class="fw-bold">{{ number_format($p->pendapatan ?? 0, 0, ',', '.') }}</div>
                            </div>
                            <div class="col-6 text-end">
                                <div class="text-muted">Dibayarkan</div>
                                <div class="fw-bold text-success">{{ number_format($p->dibayarkan ?? 0, 0, ',', '.') }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted">Total Potongan</div>
                                <div class="fw-bold text-danger">
                                    {{ number_format(
                                        ($p->sakit ?? 0) + ($p->izin ?? 0) + ($p->alpa ?? 0) +
                                        ($p->tidak_aktif ?? 0) + ($p->kelebihan ?? 0) + ($p->lain_lain ?? 0),
                                        0, ',', '.'
                                    ) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    Belum ada data pembayaran tunjangan
                </div>
            @endforelse
        </div>

    </div>
</div>

<!-- ==================== MODAL INFO PEGAWAI ==================== -->
<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold" id="infoModalLabel">Informasi Pegawai</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-12">
                        <small class="text-muted">Nama</small>
                        <h5 id="modal-nama" class="fw-bold mb-1"></h5>
                        <small class="text-muted">NIK</small>
                        <p id="modal-nik" class="fw-semibold text-primary mb-0"></p>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <small class="text-muted">Jabatan</small>
                        <div id="modal-jabatan" class="fw-semibold"></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <small class="text-muted">Status</small>
                        <div id="modal-status" class="fw-semibold"></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <small class="text-muted">Departemen</small>
                        <div id="modal-departemen" class="fw-semibold"></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <small class="text-muted">Unit biMBA</small>
                        <div id="modal-unit" class="fw-semibold"></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <small class="text-muted">No. Cabang</small>
                        <div id="modal-cabang" class="fw-semibold"></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <small class="text-muted">Masa Kerja</small>
                        <div id="modal-masakerja" class="fw-semibold"></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <small class="text-muted">No Rekening</small>
                        <div id="modal-no_rekening" class="fw-semibold"></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <small class="text-muted">Bank</small>
                        <div id="modal-bank" class="fw-semibold"></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <small class="text-muted">Atas Nama</small>
                        <div id="modal-atas_nama" class="fw-semibold"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const infoModal = document.getElementById('infoModal');
        if (!infoModal) return;

        infoModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

            document.getElementById('modal-nama').textContent       = button.getAttribute('data-nama');
            document.getElementById('modal-nik').textContent        = button.getAttribute('data-nik');
            document.getElementById('modal-jabatan').textContent    = button.getAttribute('data-jabatan');
            document.getElementById('modal-status').textContent     = button.getAttribute('data-status');
            document.getElementById('modal-departemen').textContent = button.getAttribute('data-departemen');
            document.getElementById('modal-unit').textContent       = button.getAttribute('data-unit');
            document.getElementById('modal-cabang').textContent     = button.getAttribute('data-cabang');
            document.getElementById('modal-masakerja').textContent  = button.getAttribute('data-masakerja');
            document.getElementById('modal-no_rekening').textContent = button.getAttribute('data-no_rekening');
            document.getElementById('modal-bank').textContent       = button.getAttribute('data-bank');
            document.getElementById('modal-atas_nama').textContent  = button.getAttribute('data-atas_nama');
        });
    });
</script>
@endpush
@endsection