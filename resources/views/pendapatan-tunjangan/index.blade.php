@extends('layouts.app')

@section('title', 'Pendapatan Tunjangan')

@section('content')
    <div class="card card-body shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Pendapatan Tunjangan</h1>
            <div>
                <!-- Generate Bulan Baru -->
                <form action="{{ route('pendapatan-tunjangan.generate') }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Generate data bulan baru?')">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Generate Bulan Baru
                    </button>
                </form>

                <!-- Filter Bulan -->
                <form method="GET" action="{{ route('pendapatan-tunjangan.index') }}" class="d-inline ms-2">
                    <select name="bulan" onchange="this.form.submit()" class="form-select form-select-sm d-inline-block" style="width: auto;">
                        <option value="">-- Semua Bulan --</option>
                        @foreach($allMonths as $b)
                            <option value="{{ $b }}" {{ ($bulan ?? '') == $b ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::parse($b . '-01')->translatedFormat('F Y') }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

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

        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm text-center align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>NIK</th>
                        <th>NAMA</th>
                        <th class="text-center" style="min-width: 130px;">INFO</th>
                        <th>MASA KERJA</th>
                        <th>THP</th>
                        <th>KERAJINAN</th>
                        <th>ENGLISH</th>
                        <th>MENTOR</th>
                        <th>KEKURANGAN</th>
                        <th>BULAN KEKURANGAN</th>
                        <th>TJ KELUARGA</th>
                        <th>LAIN-LAIN</th>
                        <th>TOTAL</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendapatans as $p)
                        <tr>
                            <td>{{ $p['nik'] ?? '-' }}</td>
                            <td class="text-start fw-semibold">{{ $p['nama'] ?? '-' }}</td>

                            <!-- ==================== KOLOM INFO + MODAL ==================== -->
                            <td class="text-center">
                                <button type="button" 
                                        class="btn btn-outline-info btn-sm info-pegawai-btn"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#infoModal"
                                        data-nama="{{ $p['nama'] ?? '-' }}"
                                        data-nik="{{ $p['nik'] ?? '-' }}"
                                        data-jabatan="{{ $p['jabatan'] ?? '-' }}"
                                        data-status="{{ $p['status'] ?? '-' }}"
                                        data-departemen="{{ $p['departemen'] ?? '-' }}"
                                        data-unit="{{ $p['bimba_unit'] ?? '-' }}"
                                        data-cabang="{{ $p['no_cabang'] ?? '-' }}">
                                    <i class="bi bi-info-circle"></i> Info
                                </button>
                            </td>

                            <td>
                                @if(isset($p['masa_kerja_format']))
                                    {{ $p['masa_kerja_format'] }}
                                @else
                                    @php
                                        $mk = (int) ($p['masa_kerja'] ?? 0);
                                        $years  = floor($mk / 12);
                                        $months = $mk % 12;
                                        $format = ($years > 0 ? $years . ' tahun ' : '') . $months . ' bulan';
                                        echo $format ?: '0 bulan';
                                    @endphp
                                @endif
                            </td>
                            <td>Rp. {{ number_format($p['thp'] ?? 0, 0, ',', '.') }}</td>
                            <td>Rp. {{ number_format($p['kerajinan'] ?? 0, 0, ',', '.') }}</td>
                            <td>Rp. {{ number_format($p['english'] ?? 0, 0, ',', '.') }}</td>
                            <td>Rp. {{ number_format($p['mentor'] ?? 0, 0, ',', '.') }}</td>

                            <td>
                                @if(($p['kekurangan'] ?? 0) > 0)
                                    <span class="text-danger fw-bold" title="Kekurangan yang dibayarkan di bulan ini">
                                        Rp. {{ number_format($p['kekurangan'] ?? 0, 0, ',', '.') }}
                                    </span>
                                @else
                                    Rp. {{ number_format($p['kekurangan'] ?? 0, 0, ',', '.') }}
                                @endif
                            </td>

                            <td>
                                @if($p['bulan_kekurangan'] ?? false)
                                    <span class="badge bg-secondary">{{ $p['bulan_kekurangan'] }}</span>
                                @else
                                    -
                                @endif
                            </td>

                            <td>Rp. {{ number_format($p['tj_keluarga'] ?? 0, 0, ',', '.') }}</td>
                            <td>Rp. {{ number_format($p['lain_lain'] ?? 0, 0, ',', '.') }}</td>
                            <td class="fw-bold">Rp. {{ number_format($p['total'] ?? 0, 0, ',', '.') }}</td>

                           <td>
                                @if(!empty($p['id']))
                                    @if (auth()->user()?->role === 'admin')
                                        <a href="{{ route('pendapatan-tunjangan.edit', $p['id']) }}"
                                        class="btn btn-sm btn-warning">Edit</a>

                                        <form action="{{ route('pendapatan-tunjangan.destroy', $p['id']) }}" method="POST"
                                            style="display:inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Apakah yakin ingin dihapus?')">Hapus</button>
                                        </form>
                                    @else
                                        <span class="badge bg-secondary">Tidak dapat diubah</span>
                                    @endif
                                @else
                                    <span class="badge bg-info text-dark">Otomatis</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="text-center py-4">
                                Belum ada data untuk bulan ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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