@extends('layouts.app')

@section('title', 'Slip Progresif')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3 py-md-4">

    {{-- HEADER + TOMBOL PREVIEW PDF --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-4">
        <h3 class="text-center text-md-start fw-bold text-primary mb-0">
            SLIP PEMBAYARAN PROGRESIF
        </h3>
        <button type="button" class="btn btn-outline-danger btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#pdfModal">
            Preview PDF
        </button>
    </div>

    {{-- MODAL PREVIEW PDF --}}
    <div class="modal fade" id="pdfModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen-md-down modal-xl">
            <div class="modal-content rounded-4 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Preview Slip Progresif</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0 bg-light">
                    <iframe src="{{ route('slip-progresif.pdf-preview', [
                        'nama'  => $selectedNama,
                        'bulan' => $selectedBulan,
                        'tahun' => $selectedTahun
                    ]) }}" class="w-100 border-0" style="height:80vh; min-height:500px;"></iframe>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER RESPONSIF --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <form id="filterForm" method="GET" action="{{ route('slip-progresif.index') }}">

            <div class="row g-3">

                @if(auth()->user()->role == 'admin')
<div class="col-12 col-md-3">
    <label class="form-label fw-semibold">biMBA Unit</label>
    <select name="unit" class="form-select form-select-lg rounded-3" onchange="this.form.submit()">
        <option value="">-- Pilih biMBA Unit --</option>

        @foreach ($unitList as $u)
            <option value="{{ $u }}" {{ ($selectedUnit ?? '') == $u ? 'selected' : '' }}>
                {{ $u }}
            </option>
        @endforeach

    </select>
</div>
@endif

                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">Nama Relawan</label>
                    <select name="nama" class="form-select form-select-lg rounded-3" onchange="this.form.submit()">
                        <option value="">-- Pilih Nama Relawan --</option>
                        @foreach ($namaList as $nama)
                            <option value="{{ $nama }}" {{ ($selectedNama ?? '') == $nama ? 'selected' : '' }}>
                                {{ $nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">Bulan</label>
                    <select name="bulan" class="form-select form-select-lg rounded-3" onchange="this.form.submit()">
                        <option value="">(Semua Bulan)</option>
                        @foreach (['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $b)
                            <option value="{{ $b }}" {{ ($selectedBulan ?? '') == $b ? 'selected' : '' }}>
                                {{ $b }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">Tahun</label>
                    <input
                        type="number"
                        name="tahun"
                        class="form-control form-control-lg rounded-3 text-center"
                        value="{{ $selectedTahun ?? date('Y') }}"
                        onchange="this.form.submit()"
                    >
                </div>

            </div>

        </form>
    </div>
</div>

    @if (empty($selectedNama))
        <div class="text-center py-5">
            <i class="fas fa-chart-line fa-5x text-muted mb-4"></i>
            <h5 class="text-muted">Silakan pilih nama relawan untuk melihat slip progresif</h5>
        </div>

    @elseif (!$rekap)
        <div class="alert alert-warning shadow-sm rounded-4 text-center py-4">
            <strong>Data rekap untuk {{ $selectedNama }} tidak ditemukan</strong>
        </div>

    @else
        @php
            $unit = \App\Models\Unit::where('biMBA_unit', 'LIKE', '%' . ($rekap->unit ?? $profile->unit ?? '') . '%')->first();
        @endphp

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
            <div class="card-body p-4 p-md-5">

                {{-- HEADER LOGO & INFO UTAMA --}}
                <div class="text-center mb-4 pb-4 border-bottom border-3 border-primary">
                    <div class="row align-items-center g-3">
                        <div class="col-3 col-md-2">
                            <img src="{{ asset('template/img/logoslip.png') }}" class="img-fluid" style="max-width:80px;" alt="Logo Kiri">
                        </div>
                        <div class="col-6 col-md-8">
                            <h4 class="fw-bold mb-1">YAYASAN PENGEMBANGAN ANAK INDONESIA</h4>
                            <p class="mb-2 fw-bold text-danger">Pusat Pengembangan MINAT Belajar Anak</p>
                            <img src="{{ asset('template/img/logotulisan.png') }}" class="img-fluid" style="max-height:60px;" alt="biMBA">
                            <p class="small mb-0">bimbingan <span class="text-danger">MINAT</span> Baca & belajar Anak</p>
                        </div>
                        <div class="col-3 col-md-2 text-end">
                            <img src="{{ asset('template/img/jajal.png') }}" class="img-fluid" style="max-width:75px;" alt="Logo Kanan">
                        </div>
                    </div>
                </div>

                {{-- INFO STAFF --}}
                <div class="row g-3 mb-4 small">
                    <div class="col-12 col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td width="120"><strong>No. Induk</strong></td>
                                <td>: {{ $profile->nik ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Nama Staff</strong></td>
                                <td>: {{ $rekap->nama }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jabatan</strong></td>
                                <td>: {{ $rekap->jabatan ?? ($profile->jabatan ?? '-') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-12 col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td width="120"><strong>biMBA Unit</strong></td>
                                <td>
                                    : {{ $unit->bimba_unit ?? $rekap->bimba_unit ?? '-' }}
                                    @if ($unit?->no_cabang)
                                        ({{ $unit->no_cabang }})
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Masuk</strong></td>
                                <td>
                                    : {{ $profile->tgl_masuk
                                            ? \Carbon\Carbon::parse($profile->tgl_masuk)->translatedFormat('d F Y')
                                            : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Bulan Bayar</strong></td>
                                <td>: {{ $rekap->bulan }} {{ $rekap->tahun ?? '' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- A & C: Rincian Murid + Pembayaran --}}
                <div class="row g-4 mb-4">
                    <div class="col-12 col-lg-6">
                        <h5 class="text-primary fw-bold mb-3">a. Rincian Murid</h5>
                        <div class="table-responsive rounded-3 shadow-sm">
                            <table class="table table-sm table-hover mb-0">
                                <tbody>
                                    <tr>
                                        <td>Murid Aktif (AM 1)</td>
                                        <td class="text-end fw-bold">{{ $rekap->murid_aktif_am1 ?? 0 }}</td>
                                    </tr>
                                    <tr>
                                        <td>Murid Aktif Bayar SPP (AM 2)</td>
                                        <td class="text-end">{{ $rekap->murid_aktif_am2 ?? 0 }}</td>
                                    </tr>
                                    <tr>
                                        <td>Murid Garansi (MGRS)</td>
                                        <td class="text-end">{{ $rekap->murid_garansi ?? 0 }}</td>
                                    </tr>
                                    <tr>
                                        <td>Murid Dhuafa (MDF)</td>
                                        <td class="text-end">{{ $rekap->murid_dhuafa ?? 0 }}</td>
                                    </tr>
                                    <tr>
                                        <td>Murid BNF (MBNF 1)</td>
                                        <td class="text-end">{{ $rekap->murid_bnf1 ?? 0 }}</td>
                                    </tr>
                                    <tr>
                                        <td>Murid BNF Bayar SPP (MBNF 2)</td>
                                        <td class="text-end">{{ $rekap->murid_bnf2 ?? 0 }}</td>
                                    </tr>
                                    <tr>
                                        <td>Murid Baru biMBA (MB)</td>
                                        <td class="text-end">{{ $rekap->murid_baru_bimba ?? 0 }}</td>
                                    </tr>
                                    <tr>
                                        <td>Murid Trial biMBA (MT)</td>
                                        <td class="text-end">{{ $rekap->murid_trial_bimba ?? 0 }}</td>
                                    </tr>
                                    <tr>
                                        <td>Murid Baru English (MBE)</td>
                                        <td class="text-end">{{ $rekap->murid_baru_english ?? 0 }}</td>
                                    </tr>
                                    <tr>
                                        <td>Murid Trial English (MTE)</td>
                                        <td class="text-end">{{ $rekap->murid_trial_english ?? 0 }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <h5 class="text-success fw-bold mb-3">c. Rincian Pembayaran</h5>
                        <div class="table-responsive rounded-3 shadow-sm">
                            <table class="table table-sm table-hover mb-0">
                                <tbody>
                                    <tr>
                                        <td>Total Seluruh FM</td>
                                        <td class="text-end">
                                            {{ number_format($rekap->total_fm ?? 0, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Nilai Progresif</td>
                                        <td class="text-end fw-bold">
                                            Rp{{ number_format($rekap->progresif ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Total Komisi</td>
                                        <td class="text-end">
                                            Rp{{ number_format($rekap->komisi ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Komisi MB biMBA</td>
                                        <td class="text-end">
                                            Rp{{ number_format($rekap->komisi_mb_bimba ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Komisi MT biMBA</td>
                                        <td class="text-end">
                                            Rp{{ number_format($rekap->komisi_mt_bimba ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Komisi MB English</td>
                                        <td class="text-end">
                                            Rp{{ number_format($rekap->komisi_mb_english ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Komisi MT English</td>
                                        <td class="text-end">
                                            Rp{{ number_format($rekap->komisi_mt_english ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Komisi Asisten KU</td>
                                        <td class="text-end">
                                            Rp{{ number_format($rekap->komisi_asisten ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr class="table-success">
                                        <td><strong>Total Pendapatan</strong></td>
                                        <td class="text-end fw-bold fs-4 text-success">
                                            Rp{{ number_format($rekap->dibayarkan ?? $rekap->progresif ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- B & D: Pendapatan + Adjustment + Rekening --}}
                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        <h5 class="text-info fw-bold mb-3">b. Rincian Pendapatan</h5>
                        <div class="table-responsive rounded-3 shadow-sm">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td>Penerimaan SPP biMBA-AIUEO</td>
                                    <td class="text-end">
                                        Rp{{ number_format($rekap->spp_bimba ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Penerimaan SPP English biMBA</td>
                                    <td class="text-end">
                                        Rp{{ number_format($rekap->spp_english ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <h5 class="text-warning fw-bold mt-4 mb-3">d. Rincian Adjustment</h5>
                        <div class="table-responsive rounded-3 shadow-sm">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td>Kekurangan Progressive</td>
                                    <td class="text-end text-danger">
                                        Rp{{ number_format($rekap->kurang ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Kelebihan Progressive</td>
                                    <td class="text-end text-success">
                                        Rp{{ number_format($rekap->lebih ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <h5 class="text-dark fw-bold mb-3">Data Rekening & Jumlah Dibayarkan</h5>
                        <div class="table-responsive rounded-3 shadow-sm">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td>Bank</td>
                                    <td class="text-end">{{ $profile->bank ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td>No Rekening</td>
                                    <td class="text-end">{{ $profile->no_rekening ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td>Atas Nama</td>
                                    <td class="text-end">{{ $profile->atas_nama ?? '-' }}</td>
                                </tr>
                                <tr class="table-primary">
                                    <td><strong>Jumlah Dibayarkan</strong></td>
                                    <td class="text-end fw-bold fs-3 text-primary">
                                        Rp{{ number_format($rekap->dibayarkan ?? $rekap->progresif ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- TANDA TANGAN --}}
                <div class="mt-5 pt-4">
                    <div class="row g-3 g-md-5 justify-content-center">
                        {{-- Yang Menyerahkan (Kiri) --}}
                        <div class="col-6 col-sm-5 col-md-4">
                            <div class="text-center">
                                <p class="mb-5 fw-medium text-dark">Yang Menyerahkan,</p>
                                <div class="mb-4"></div>
                                <div class="mx-auto border-top border-3 border-dark pt-3"
                                     style="width:220px; max-width:94%;"></div>
                                <small class="text-muted d-block mt-3">(Nama & Tanda Tangan)</small>
                            </div>
                        </div>

                        {{-- Mengetahui (Kanan) --}}
                        <div class="col-6 col-sm-5 col-md-4">
                            <div class="text-center">
                                <p class="mb-5 fw-medium text-dark">Mengetahui,</p>
                                <div class="mb-4"></div>
                                <div class="mx-auto border-top border-3 border-dark pt-3"
                                     style="width:220px; max-width:94%;"></div>
                                <small class="text-muted d-block mt-3">(Kepala Unit / Pengawas)</small>
                            </div>
                        </div>
                    </div>
                </div>

            </div> {{-- end card-body --}}
        </div> {{-- end card --}}

        {{-- TABEL DATA MURID (dengan scroll horizontal di HP) --}}
        <div class="card border-0 shadow-lg rounded-4 mt-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-primary mb-0">Data Murid</h5>

                    {{-- Filter status murid --}}
                    <form method="GET" action="{{ route('slip-progresif.index') }}" class="d-inline">
                        {{-- Pertahankan semua parameter kecuali "status" --}}
                        @foreach(request()->except('status') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach

                        <select name="status" onchange="this.form.submit()"
                                class="form-select form-select-sm w-auto d-inline-block">
                            <option value="">Semua Status</option>
                            <option value="Aktif"  {{ request('status') == 'Aktif'  ? 'selected' : '' }}>Aktif</option>
                            <option value="Keluar" {{ request('status') == 'Keluar' ? 'selected' : '' }}>Keluar</option>
                        </select>
                    </form>
                </div>

                <div class="table-responsive rounded-3 shadow-sm">
                    <table class="table table-bordered table-sm text-center align-middle" style="font-size:0.85rem;">
                        <thead class="table-primary">
                            <tr>
                                <th>NIM</th>
                                <th>NAMA MURID</th>
                                <th>KELAS</th>
                                <th>GOL</th>
                                <th>KD</th>
                                <th>SPP</th>
                                <th>STATUS</th>
                                <th>NOTE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($muridList ?? [] as $murid)
                                @php
                                    // dukung array maupun object
                                    $nim    = is_array($murid) ? ($murid['nim']   ?? '') : ($murid->nim   ?? '');
                                    $namaM  = is_array($murid) ? ($murid['nama']  ?? '') : ($murid->nama  ?? '');
                                    $kelas  = is_array($murid) ? ($murid['kelas'] ?? '') : ($murid->kelas ?? '');
                                    $gol    = is_array($murid) ? ($murid['gol']   ?? '') : ($murid->gol   ?? '');
                                    $kd     = is_array($murid) ? ($murid['kd']    ?? '') : ($murid->kd    ?? '');
                                    $sppRaw = is_array($murid) ? ($murid['spp']   ?? '') : ($murid->spp   ?? '');
                                    $status = is_array($murid) ? ($murid['status'] ?? '') : ($murid->status ?? '');
                                    $note   = is_array($murid) ? ($murid['note']  ?? '') : ($murid->note  ?? '');
                                    $sppNum = preg_replace('/[^0-9]/', '', $sppRaw ?: '0');
                                @endphp
                                <tr>
                                    <td>{{ $nim }}</td>
                                    <td class="text-start">{{ $namaM }}</td>
                                    <td>{{ $kelas }}</td>
                                    <td>{{ $gol }}</td>
                                    <td>{{ $kd }}</td>
                                    <td>{{ number_format($sppNum, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $status == 'Aktif' ? 'success' : 'secondary' }}">
                                            {{ $status ?: '-' }}
                                        </span>
                                    </td>
                                    <td class="text-start small">{{ $note ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-muted py-4">Tidak ada data murid</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- CSS KHUSUS HALAMAN INI --}}
<style>
    @media (max-width: 576px) {
        h3 { font-size: 1.3rem !important; }
        .btn-lg { font-size: 1rem; padding: 0.6rem 1rem; }
        .table { font-size: 0.8rem; }
        .fs-3 { font-size: 1.4rem !important; }
        .fs-4 { font-size: 1.3rem !important; }
    }
    .table-hover tr:hover { background-color: #f8f9fa !important; }
</style>
@endsection
