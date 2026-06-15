@extends('layouts.app')

@section('title', 'Slip Pendapatan')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3 py-md-4">

    {{-- HEADER + TOMBOL PREVIEW PDF --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-4">
        <h3 class="text-center text-md-start fw-bold text-primary mb-0">
            SLIP PEMBAYARAN TUNJANGAN
        </h3>
        <button type="button" class="btn btn-outline-danger btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#pdfModal">
            <i class="fas fa-file-pdf me-2"></i>Preview PDF
        </button>
    </div>

    {{-- MODAL PREVIEW PDF (Super Responsif) --}}
    <div class="modal fade" id="pdfModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen-md-down modal-xl">
            <div class="modal-content rounded-4 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Preview Slip PDF</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0 bg-light">
                    <iframe 
                        src="{{ route('slip-tunjangan.pdf-preview', [
                            'nama' => $selectedNama ?? '',
                            'bulan' => $selectedBulan ?? '',
                            'tahun' => $selectedTahun ?? date('Y')
                        ]) }}"
                        class="w-100 border-0"
                        style="height:80vh; min-height:500px;">
                    </iframe>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER RESPONSIF --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-body p-4">
            <form id="filterForm" method="GET" action="{{ route('slip-tunjangan.index') }}">
                <div class="row g-3">
                    <div class="col-12 col-md-5">
                        <label class="form-label fw-semibold">Nama Staff</label>
                        <select name="nama" class="form-select form-select-lg rounded-3" onchange="this.form.submit()">
                            <option value="">-- Pilih Nama Staff --</option>
                            @foreach ($namaList as $nama)
                                <option value="{{ $nama }}" {{ ($selectedNama ?? '') == $nama ? 'selected' : '' }}>
                                    {{ $nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Bulan</label>
                        <select name="bulan" class="form-select form-select-lg rounded-3" onchange="this.form.submit()">
                            <option value="">(Semua Bulan)</option>
                            @foreach (['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $b)
                                <option value="{{ $b }}" {{ ($selectedBulan ?? '') == $b ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-semibold">Tahun</label>
                        <input type="number" name="tahun" class="form-control form-control-lg rounded-3 text-center" 
                               value="{{ $selectedTahun ?? date('Y') }}" onchange="this.form.submit()">
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- KETIKA BELUM PILIH NAMA --}}
    @if(empty($selectedNama))
        <div class="text-center py-5">
            <i class="fas fa-user-tie fa-5x text-muted mb-4"></i>
            <h5 class="text-muted">Silakan pilih nama staff untuk menampilkan slip tunjangan</h5>
        </div>

    @elseif(!$selectedSlip)
        <div class="alert alert-warning shadow-sm rounded-4 text-center py-4">
            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i><br>
            <strong>Data slip untuk {{ $selectedNama }} tidak ditemukan</strong>
        </div>

    @else
        {{-- CARD UTAMA SLIP --}}
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-body p-4 p-md-5">

                {{-- HEADER LOGO & JUDUL --}}
                <div class="text-center mb-4 pb-3 border-bottom border-3 border-primary">
                    <div class="row align-items-center g-3">
                        <div class="col-3 col-md-2">
                            <img src="{{ asset('template/img/logoslip.png') }}" class="img-fluid" alt="Logo Kiri">
                        </div>
                        <div class="col-6 col-md-8">
                            <h4 class="fw-bold mb-1" style="font-size:1.1rem;">YAYASAN PENGEMBANGAN ANAK INDONESIA</h4>
                            <p class="mb-2 fw-bold text-danger">Pusat Pengembangan MINAT Belajar Anak</p>
                            <img src="{{ asset('template/img/logotulisan.png') }}" class="img-fluid" style="max-height:60px;" alt="biMBA">
                            <p class="small mb-0">bimbingan <span class="text-danger">MINAT</span> Baca & belajar Anak</p>
                        </div>
                                <div class="col-3 col-md-2">
                                    <img src="{{ asset('template/img/jajal.png') }}" class="img-fluid" alt="Logo Kanan">
                                </div>
                    </div>
                </div>

                {{-- INFO STAFF --}}
                <div class="row g-3 mb-4 text-sm">
                    <div class="col-12 col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td width="120"><strong>No. Induk (NIK)</strong></td>
                                <td>: {{ $profile->nik ?? $selectedSlip->nik ?? '-' }}</td>
                            </tr>
                            <tr><td><strong>Nama Staff</strong></td><td>: {{ $selectedSlip->nama ?? '-' }}</td></tr>
                            <tr><td><strong>Jabatan</strong></td><td>: {{ $selectedSlip->jabatan ?? '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-12 col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr><td width="120"><strong>biMBA Unit</strong></td><td>: {{ $unitData->biMBA_unit ?? '-' }}</td></tr>
                            <tr><td><strong>Tanggal Masuk</strong></td><td>: 
                                {{ $tanggalMasuk ? \Carbon\Carbon::parse($tanggalMasuk)->translatedFormat('d F Y') : '-' }}
                            </td></tr>
                            <tr><td><strong>Bulan</strong></td><td>: 
                                {{ !empty($selectedSlip->bulan) ? \Carbon\Carbon::parse($selectedSlip->bulan)->translatedFormat('F Y') : '-' }}
                            </td></tr>
                        </table>
                    </div>
                </div>

                {{-- PENDAPATAN & POTONGAN (2 KOLOM DI DESKTOP, 1 KOLOM DI HP) --}}
                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        <h5 class="text-success fw-bold mb-3"><i class="fas fa-arrow-up text-success"></i> PENDAPATAN</h5>
                        <div class="table-responsive rounded-3 overflow-hidden shadow-sm">
                            <table class="table table-hover mb-0">
                                <tbody>
                                    <tr><td>Tunjangan Pokok</td><td class="text-end fw-bold">Rp{{ number_format($tunjanganPokok ?? 0, 0, ',', '.') }}</td></tr>
                                    <tr><td>Tunjangan Harian</td><td class="text-end">Rp{{ number_format($tunjanganHarian ?? 0, 0, ',', '.') }}</td></tr>
                                    <tr><td>Tunjangan Fungsional</td><td class="text-end">Rp{{ number_format($tunjanganFungsional ?? 0, 0, ',', '.') }}</td></tr>
                                    <tr><td>Tunjangan Kesehatan</td><td class="text-end">Rp{{ number_format($tunjanganKesehatan ?? 0, 0, ',', '.') }}</td></tr>
                                    <tr><td>Tunjangan Kerajinan</td><td class="text-end">Rp{{ number_format($selectedSlip->tunjangan_kerajinan ?? 0, 0, ',', '.') }}</td></tr>
                                    <tr><td>Komisi English biMBA</td><td class="text-end">Rp{{ number_format($selectedSlip->komisi_english ?? 0, 0, ',', '.') }}</td></tr>
                                    <tr><td>Komisi Mentor Magang</td><td class="text-end">Rp{{ number_format($selectedSlip->komisi_mentor ?? 0, 0, ',', '.') }}</td></tr>
                                    <tr><td>Kekurangan Tunjangan</td><td class="text-end">Rp{{ number_format($selectedSlip->kekurangan_tunjangan ?? 0, 0, ',', '.') }}</td></tr>
                                    <tr><td>Tunjangan Keluarga</td><td class="text-end">Rp{{ number_format($selectedSlip->tunjangan_keluarga ?? 0, 0, ',', '.') }}</td></tr>
                                    <tr><td>Lain-lain Pendapatan</td><td class="text-end">Rp{{ number_format($selectedSlip->lain_lain_pendapatan ?? 0, 0, ',', '.') }}</td></tr>
                                    @php
                                        $totalPendapatan = ($tunjanganPokok ?? 0) + ($tunjanganHarian ?? 0) + ($tunjanganFungsional ?? 0) + ($tunjanganKesehatan ?? 0) +
                                            ($selectedSlip->tunjangan_kerajinan ?? 0) + ($selectedSlip->komisi_english ?? 0) + ($selectedSlip->komisi_mentor ?? 0) +
                                            ($selectedSlip->kekurangan_tunjangan ?? 0) + ($selectedSlip->tunjangan_keluarga ?? 0) + ($selectedSlip->lain_lain_pendapatan ?? 0);
                                        $totalPotongan =
                                        (int)($selectedSlip->potongan_sakit ?? 0) +
                                        (int)($selectedSlip->potongan_izin ?? 0) +
                                        (int)($selectedSlip->potongan_alpa ?? 0) +
                                        (int)($selectedSlip->potongan_tidak_aktif ?? 0) +
                                        (int)($selectedSlip->potongan_kelebihan ?? 0) +
                                        (int)($selectedSlip->potongan_lain_lain ?? 0) +
                                        (int)($selectedSlip->potongan_cash_advance ?? 0);
                                        $jumlahDibayarkan = $totalPendapatan - $totalPotongan;
                                    @endphp
                                    <tr class="table-success"><td><strong>Total Pendapatan</strong></td><td class="text-end fw-bold fs-5">Rp{{ number_format($totalPendapatan, 0, ',', '.') }}</td></tr>
                                    <tr class="table-primary"><td><strong>Jumlah Dibayarkan</strong></td><td class="text-end fw-bold fs-4 text-primary">Rp{{ number_format($jumlahDibayarkan, 0, ',', '.') }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <h5 class="text-danger fw-bold mb-3"><i class="fas fa-arrow-down text-danger"></i> POTONGAN</h5>
                        <div class="table-responsive rounded-3 overflow-hidden shadow-sm">
                            <table class="table table-hover mb-0">
                                <tbody>
                                    @foreach([
                                        'potongan_sakit' => 'Sakit',
                                        'potongan_izin' => 'Izin',
                                        'potongan_alpa' => 'Alpa',
                                        'potongan_tidak_aktif' => 'Tidak Aktif',
                                        'potongan_kelebihan' => 'Kelebihan Tunjangan',
                                        'potongan_lain_lain' => 'Lain-lain'
                                    ] as $field => $label)
                                        <tr>
                                            <td>{{ $label }}</td>
                                            <td class="text-end">Rp{{ number_format($selectedSlip->{$field} ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td>Cash Advance</td>
                                        <td class="text-end">Rp{{ number_format($selectedSlip->potongan_cash_advance ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    @if(!empty($selectedSlip->potongan_cash_advance_note))
                                        <tr><td colspan="2" class="text-danger small">{{ $selectedSlip->potongan_cash_advance_note }}</td></tr>
                                    @endif
                                    <tr class="table-danger">
                                        <td><strong>Total Potongan</strong></td>
                                        <td class="text-end fw-bold fs-5">Rp{{ number_format($totalPotongan, 0, ',', '.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 p-3 bg-light rounded-3">
                            <small class="text-muted">Rekening: {{ $profile->bank ?? '-' }} | {{ $profile->no_rekening ?? '-' }} | {{ $profile->atas_nama ?? '-' }} | {{ $profile->email ?? '-' }}</small>
                        </div>
                    </div>
                </div>

                {{-- TANDA TANGAN --}}
                <div class="row mt-5 text-center">
                    <div class="col-6">
                        <p class="mb-5">Yang Menyerahkan,</p>
                        <div class="border-top border-3 border-dark pt-2" style="width:200px; margin:0 auto;"></div>
                    </div>
                    <div class="col-6">
                        <p class="mb-5">Penerima,</p>
                        <div class="border-top border-3 border-dark pt-2" style="width:200px; margin:0 auto;"></div>
                        <p class="mt-2 fw-bold">{{ $selectedSlip->nama ?? '' }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- CSS KHUSUS HALAMAN INI (tambahkan di dalam blade atau custom.css) --}}
<style>
    @media (max-width: 576px) {
        h3 { font-size: 1.3rem !important; }
        .btn-lg { font-size: 1rem; padding: 0.6rem 1rem; }
        .table { font-size: 0.85rem; }
        .fs-4 { font-size: 1.3rem !important; }
        .fs-5 { font-size: 1.1rem !important; }
    }
    .table-hover tr:hover { background-color: #f8f9fa !important; }
    .rounded-4 { border-radius: 1rem !important; }
</style>
@endsection