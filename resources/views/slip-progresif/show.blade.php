@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4 class="text-center mb-4 fw-bold">SLIP PEMBAYARAN PROGRESIF</h4>

    <table class="table table-borderless">
        <tr>
            <td>No Induk :</td>
            <td>{{ $profile->no_induk ?? '-' }}</td>
            <td>biMBA Unit :</td>
            <td>{{ $profile->unit ?? '-' }}</td>
        </tr>
        <tr>
            <td>Nama Staff :</td>
            <td>{{ $rekap->nama ?? '-' }}</td>
            <td>Tgl Masuk :</td>
            <td>{{ $profile->tgl_masuk ?? '-' }}</td>
        </tr>
        <tr>
            <td>Jabatan :</td>
            <td>{{ $profile->jabatan ?? '-' }}</td>
            <td>Bulan Bayar :</td>
            <td>{{ $rekap->bulan }} {{ $rekap->tahun }}</td>
        </tr>
    </table>

    <hr>

    {{-- Bagian a. Rincian Murid --}}
    <h5 class="fw-bold mt-4">a. Rincian Murid</h5>
    <table class="table table-sm table-bordered">
        <tr><td>Murid Aktif (AM 1)</td><td>{{ $rekap->murid_aktif_am1 ?? 0 }}</td></tr>
        <tr><td>Murid Aktif Yang Bayar SPP (AM 2)</td><td>{{ $rekap->murid_aktif_am2 ?? 0 }}</td></tr>
        <tr><td>Murid Garansi (MGRS)</td><td>{{ $rekap->murid_garansi ?? 0 }}</td></tr>
        <tr><td>Murid Dhuafa (MDF)</td><td>{{ $rekap->murid_dhuafa ?? 0 }}</td></tr>
        <tr><td>Murid BNF (MBNF 1)</td><td>{{ $rekap->murid_bnf1 ?? 0 }}</td></tr>
        <tr><td>Murid BNF Yang Bayar SPP (MBNF 2)</td><td>{{ $rekap->murid_bnf2 ?? 0 }}</td></tr>
        <tr><td>Murid Baru biMBA-AIUEO (MB)</td><td>{{ $rekap->murid_baru_bimba ?? 0 }}</td></tr>
        <tr><td>Murid Trial biMBA-AIUEO (MT)</td><td>{{ $rekap->murid_trial_bimba ?? 0 }}</td></tr>
        <tr><td>Murid Baru English biMBA (MBE)</td><td>{{ $rekap->murid_baru_english ?? 0 }}</td></tr>
        <tr><td>Murid Trial English biMBA (MTE)</td><td>{{ $rekap->murid_trial_english ?? 0 }}</td></tr>
    </table>

    {{-- Bagian b. Rincian Pendapatan --}}
    <h5 class="fw-bold mt-4">b. Rincian Pendapatan</h5>
    <table class="table table-sm table-bordered">
        <tr><td>Penerimaan SPP biMBA-AIUEO</td><td>Rp{{ number_format($rekap->total_spp_bimba ?? 0, 0, ',', '.') }}</td></tr>
        <tr><td>Penerimaan SPP English biMBA</td><td>Rp{{ number_format($rekap->total_spp_english ?? 0, 0, ',', '.') }}</td></tr>
    </table>

    {{-- Bagian c. Pembayaran --}}
    <h5 class="fw-bold mt-4">c. Rincian Pembayaran</h5>
    <table class="table table-sm table-bordered">
        <tr><td>Total Seluruh FM</td><td>Rp{{ number_format($rekap->total_fm ?? 0, 0, ',', '.') }}</td></tr>
        <tr><td>Nilai Progresif</td><td>Rp{{ number_format($rekap->nilai_progresif ?? 0, 0, ',', '.') }}</td></tr>
        <tr><td>Total Komisi</td><td>Rp{{ number_format($rekap->komisi ?? 0, 0, ',', '.') }}</td></tr>
        <tr><td>Total Pendapatan</td><td>Rp{{ number_format($rekap->total_pendapatan ?? 0, 0, ',', '.') }}</td></tr>
    </table>

    {{-- Bagian d. Rekening --}}
    <h5 class="fw-bold mt-4">d. Data Rekening</h5>
    <table class="table table-sm table-bordered">
        <tr>
            <td>Bank</td>
            <td>{{ $profile->bank ?? '-' }}</td>
        </tr>
        <tr>
            <td>No Rekening</td>
            <td>{{ $profile->no_rekening ?? '-' }}</td>
        </tr>
        <tr>
            <td>Atas Nama</td>
            <td>{{ $profile->atas_nama ?? '-' }}</td>
        </tr>
        <tr>
            <td>THP (Total yang dibayarkan)</td>
            <td>Rp{{ number_format($rekap->thp ?? 0, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="text-end mt-4">
        <a href="{{ route('rekap-progresif.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>
@endsection
