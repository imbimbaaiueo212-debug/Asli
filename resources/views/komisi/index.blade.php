{{-- resources/views/komisi/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Komisi')
@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="mb-4 text-primary">Komisi</h3>

            {{-- Filter Periode + Unit --}}
            <form method="GET" class="row mb-4 g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Unit biMBA</label>
                    <select name="unit_id" class="form-select">
                        <option value="">-- Semua Unit --</option>
                        @foreach($unitOptions ?? [] as $opt)
                            <option value="{{ $opt['value'] }}"
                                    {{ request('unit_id') == $opt['value'] ? 'selected' : '' }}>
                                {{ $opt['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tahun Awal --}}
                <div class="col-md-2">
                    <label class="form-label fw-bold">Tahun Awal</label>
                    <input type="number" name="tahun_awal"
                        value="{{ $tahunAwal ?? now()->subMonth()->year }}"
                        class="form-control" placeholder="2026" min="2020">
                </div>

                {{-- Bulan Awal (Default = Bulan Lalu) --}}
                <div class="col-md-2">
                    <label class="form-label fw-bold">Bulan Awal</label>
                    <select name="bulan_awal" class="form-select">
                        <option value="">-- Pilih Bulan --</option>
                        @foreach([1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                                7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'] as $i => $nama)
                            <option value="{{ $i }}"
                                    {{ $i == ($bulanAwal ?? now()->subMonth()->month) ? 'selected' : '' }}>
                                {{ $nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tahun Akhir --}}
                <div class="col-md-2">
                    <label class="form-label fw-bold">Tahun Akhir</label>
                    <input type="number" name="tahun_akhir"
                        value="{{ $tahunAkhir ?? now()->subMonth()->year }}"
                        class="form-control" placeholder="2026" min="2020">
                </div>

                {{-- Bulan Akhir (Default = Bulan Lalu) --}}
                <div class="col-md-2">
                    <label class="form-label fw-bold">Bulan Akhir</label>
                    <select name="bulan_akhir" class="form-select">
                        <option value="">-- Pilih Bulan --</option>
                        @foreach([1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                                7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'] as $i => $nama)
                            <option value="{{ $i }}"
                                    {{ $i == ($bulanAkhir ?? now()->subMonth()->month) ? 'selected' : '' }}>
                                {{ $nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <a href="{{ route('komisi.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>

            {{-- Tombol Sync --}}
            {{-- TOMBOL SYNC (DENGAN FILTER PERIODE) --}}
<form action="{{ route('komisi.sync') }}" method="POST" class="d-inline mb-4">
    @csrf
    {{-- Kirimkan periode yang sedang difilter --}}
    <input type="hidden" name="bulan" value="{{ $bulanAwal ?? now()->month }}">
    <input type="hidden" name="tahun" value="{{ $tahunAwal ?? now()->year }}">

    <button type="submit" class="btn btn-primary btn-sm"
        onclick="return confirm('Sync komisi untuk periode {{ $periodeText ?? 'bulan ini' }}?\nData akan direset dan dihitung ulang.')">
        🔄 Sync Komisi Otomatis
    </button>
</form>

            {{-- Info Periode --}}
            <div class="alert alert-info d-flex align-items-center mb-4">
                <strong>Periode:</strong>&nbsp;
                <span class="fw-bold text-primary">{{ $periodeText ?? 'Belum ada periode' }}</span>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <strong>Jumlah Karyawan:</strong> {{ $data_komisi->count() }}
            </div>

            {{-- Tabel Komisi --}}
            <div class="table-responsive">
                <table class="table table-bordered table-sm text-center align-middle">
                    <thead class="table-primary">
                        <tr class="fw-bold">
                            <th rowspan="2">NIK</th>
                            <th rowspan="2">NAMA</th>
                            <th rowspan="2" class="text-center" style="min-width: 130px;">INFO</th>
                            <th colspan="2" class="table-success">JUMLAH SPP</th>
                            <th colspan="5" class="table-warning">RINCIAN KOMISI</th>
                            <th colspan="9" class="table-purple">DATA MURID biMBA AIUEO</th>
                            <th colspan="5" class="table-pink ">DATA MURID ENGLISH</th>
                            <th colspan="2" class="table-danger">KOMISI MB KEPALA UNIT</th>
                        </tr>
                        <tr class="table-light">
                            <th>biMBA</th>
                            <th>ENGLISH</th>
                            <th>MB biMBA</th>
                            <th>MT biMBA</th>
                            <th>MB ENGLISH</th>
                            <th>MT ENGLISH</th>
                            <th class="bg-success text-white fw-bold">DIBAYARKAN</th>
                            <th>AM1</th><th>AM2</th><th>MGRS</th><th>MDF</th><th>BNF</th><th>BNF2</th>
                            <th>MB</th><th>MK</th><th>MT</th>
                            <th>AM1</th><th>AM2</th><th>MB</th><th>MK</th><th>MT</th>
                            <th>MB UMUM</th>
                            <th>INSENTIF</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data_komisi as $k)
                        <tr>
                            <td>{{ $k->nik ?? '-' }}</td>
                            <td class="text-start fw-bold">{{ $k->nama }}</td>
                            
                            <!-- ==================== KOLOM INFO + MODAL ==================== -->
                            <td class="text-center">
                                <button type="button"
                                        class="btn btn-outline-info btn-sm info-pegawai-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#infoModal"
                                        data-nama="{{ $k->nama ?? '-' }}"
                                        data-nik="{{ $k->nik ?? '-' }}"
                                        data-jabatan="{{ $k->jabatan ?? '-' }}"
                                        data-status="{{ $k->status ?? '-' }}"
                                        data-departemen="{{ $k->departemen ?? '-' }}"
                                        data-unit="{{ $k->bimba_unit ?? '-' }}"
                                        data-cabang="{{ $k->no_cabang ?? '-' }}"
                                        @php
                                            $bulan = $k->masa_kerja ?? 0;
                                            $tahun = floor($bulan / 12);
                                            $sisaBulan = $bulan % 12;
                                        @endphp

                                        data-masakerja="{{ $k->profile?->masa_kerja_format ?? ($tahun . ' tahun ' . $sisaBulan . ' bulan') }}"
                                    <i class="bi bi-info-circle"></i> Info
                                </button>
                            </td>

                            {{-- JUMLAH SPP --}}
                            <td>{{ number_format($k->spp_bimba ?? 0, 0, ',', '.') }}</td>
                            <td>{{ number_format($k->spp_english ?? 0, 0, ',', '.') }}</td>

                            {{-- RINCIAN KOMISI --}}
                            <td class="fw-bold">{{ number_format($k->komisi_mb_bimba ?? 0, 0, ',', '.') }}</td>
                            <td>{{ number_format($k->komisi_mt_bimba ?? 0, 0, ',', '.') }}</td>
                            <td>{{ number_format($k->komisi_mb_english ?? 0, 0, ',', '.') }}</td>
                            <td>{{ number_format($k->komisi_mt_english ?? 0, 0, ',', '.') }}</td>
                            <td class="text-white fw-bold text-center
                                {{ ($k->sudah_dibayar ?? 0) >= (($k->komisi_mb_bimba ?? 0) + ($k->komisi_mt_bimba ?? 0)) ? 'bg-success' : 'bg-danger' }}">
                                {{ number_format($k->sudah_dibayar ?? 0, 0, ',', '.') }}
                            </td>

                            {{-- DATA MURID biMBA AIUEO --}}
                            <td>{{ $k->am1_bimba ?? '-' }}</td>
                            <td>{{ $k->am2_bimba ?? '-' }}</td>
                            <td>{{ $k->mgrs ?? '-' }}</td>
                            <td>{{ $k->mdf ?? '-' }}</td>
                            <td>{{ $k->bnf ?? '-' }}</td>
                            <td>{{ $k->bnf2 ?? '-' }}</td>
                            <td>{{ $k->murid_mb_bimba ?? '-' }}</td>
                            <td>{{ $k->mk_bimba ?? '-' }}</td>
                            <td>{{ $k->murid_mt_bimba ?? '-' }}</td>

                            {{-- DATA MURID ENGLISH --}}
                            <td>{{ $k->am1_english ?? '-' }}</td>
                            <td>{{ $k->am2_english ?? '-' }}</td>
                            <td>{{ $k->murid_mb_english ?? '-' }}</td>
                            <td>{{ $k->mk_english ?? '-' }}</td>
                            <td>{{ $k->murid_mt_english ?? '-' }}</td>

                            {{-- KOMISI MB KEPALA UNIT --}}
                            <td class="fw-bold">{{ number_format($k->mb_umum_ku ?? 0, 0, ',', '.') }}</td>
                            <td>{{ number_format($k->mb_insentif_ku ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="38" class="text-center py-5 text-muted">
                                <h5>Belum ada data komisi untuk periode ini.</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
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
                    <div class="col-md-6">
                        <small class="text-muted">Masa Kerja</small>
                        <div id="modal-masakerja" class="fw-semibold"></div>
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
        if (!infoModal) return;
        
        infoModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('modal-nama').textContent = button.getAttribute('data-nama');
            document.getElementById('modal-nik').textContent = button.getAttribute('data-nik');
            document.getElementById('modal-jabatan').textContent = button.getAttribute('data-jabatan');
            document.getElementById('modal-status').textContent = button.getAttribute('data-status');
            document.getElementById('modal-departemen').textContent = button.getAttribute('data-departemen');
            document.getElementById('modal-unit').textContent = button.getAttribute('data-unit');
            document.getElementById('modal-cabang').textContent = button.getAttribute('data-cabang');
            document.getElementById('modal-masakerja').textContent = button.getAttribute('data-masakerja');
        });
    });
</script>
@endpush

@push('styles')
<style>
    .table-purple { background-color: #6f42c1 !important; }
    .table-pink   { background-color: #e83e8c !important; }
</style>
@endpush

@endsection