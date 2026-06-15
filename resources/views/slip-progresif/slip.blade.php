@extends('layouts.app')

@section('content')

<div class="slip">
    <div class="header">
        {{-- Ganti dengan path logo Anda --}}
        <img src="{{ asset('template/img/logo-bimba.png') }}" alt="biMBA AUEO"> 
        <h3>SLIP PEMBAYARAN PROGRESIF STAFF</h3>
        <div class="periode">Periode Pembayaran: {{ $slip->bulan }}</div>
    </div>

    <div class="content">

        {{-- A. DETAIL STAFF --}}
        <table class="data-table">
            <tr><th>No Induk</th><td>: {{ $slip->nomor_induk ?? '-' }}</td></tr>
            <tr><th>Nama Staff</th><td>: <strong>{{ $slip->nama_staff }}</strong></td></tr>
            <tr><th>Jabatan</th><td>: {{ $slip->jabatan }}</td></tr>
            <tr><th>biMBA Unit</th><td>: {{ $slip->unit ?? '-' }}</td></tr>
            <tr><th>Tanggal Masuk</th><td>: {{ $slip->tanggal_masuk }}</td></tr>
        </table>

        {{-- B. RINCIAN MURID (Indikator Progresif) --}}
        <h5 class="section-header">B. Rincian Murid (Indikator Progresif)</h5>
        <table class="detail-table">
            <tr><th>Murid Aktif Bayar SPP (AM2)</th><td>{{ $slip->am2 }}</td></tr>
            <tr><th>Murid Baru biMBA (MB)</th><td>{{ $slip->mb }}</td></tr>
            <tr><th>Murid Trial biMBA (MT)</th><td>{{ $slip->mt }}</td></tr>
            <tr><th>Murid Baru English biMBA (MBE)</th><td>{{ $slip->mbe }}</td></tr>
            <tr><th>Murid Trial English biMBA (MTE)</th><td>{{ $slip->mte }}</td></tr>
            <tr><th>Total Seluruh FM</th><td>{{ $slip->total_fm }}</td></tr>
        </table>

        {{-- C. RINCIAN PENDAPATAN & KOMISI --}}
        <h5 class="section-header">C. Rincian Pendapatan & Komisi</h5>
        <table class="detail-table">
            {{-- Pendapatan SPP --}}
            <tr><th>Penerimaan SPP biMBA-AIUEO</th><td class="rupiah">: Rp {{ number_format($slip->spp_bimba,0,',','.') }}</td></tr>
            <tr><th>Penerimaan SPP English biMBA</th><td class="rupiah">: Rp {{ number_format($slip->spp_english,0,',','.') }}</td></tr>
            
            {{-- Komisi/Progresif --}}
            <tr><th>Nilai Progresif</th><td class="rupiah">: Rp {{ number_format($slip->nilai_progresif,0,',','.') }}</td></tr>
            <tr><th>Total Komisi</th><td class="rupiah">: Rp {{ number_format($slip->total_komisi,0,',','.') }}</td></tr>
            <tr><th>Komisi MB biMBA-AIUEO</th><td class="rupiah">: Rp {{ number_format($slip->komisi_mb_bimba,0,',','.') }}</td></tr>
            <tr><th>Komisi MT biMBA-AIUEO</th><td class="rupiah">: Rp {{ number_format($slip->komisi_mt_bimba,0,',','.') }}</td></tr>
            <tr><th>Komisi Asisten KU</th><td class="rupiah">: Rp {{ number_format($slip->komisi_asku,0,',','.') }}</td></tr>
            
            {{-- Total Bruto --}}
            <tr><th>Total Pendapatan (Bruto)</th><td class="rupiah">: **Rp {{ number_format($slip->total_pendapatan,0,',','.') }}**</td></tr>
        </table>

        {{-- D. ADJUSTMENT --}}
        <h5 class="section-header">D. Rincian Adjustment</h5>
        <table class="detail-table">
            <tr><th>Kekurangan Progressive</th><td class="rupiah">: Rp {{ number_format($slip->kurang,0,',','.') }}</td></tr>
            <tr><th>Kelebihan Progressive</th><td class="rupiah">: Rp {{ number_format($slip->lebih,0,',','.') }}</td></tr>
        </table>

        {{-- TOTAL YANG DIBAYARKAN (Kotak Kuning Besar) --}}
        <table class="total-box">
            <tr>
                <th>Jumlah Yang Dibayarkan</th>
                <td><strong>Rp {{ number_format($slip->dibayarkan,0,',','.') }}</strong></td>
            </tr>
        </table>

        {{-- E. INFORMASI REKENING --}}
        <h5 class="section-header">E. Informasi Pembayaran</h5>
        <table class="detail-table rekening-table">
            <tr><th>Bank</th><td>: {{ $slip->bank }}</td></tr>
            <tr><th>No Rekening</th><td>: {{ $slip->no_rekening }}</td></tr>
            <tr><th>Atas Nama</th><td>: {{ $slip->atas_nama }}</td></tr>
        </table>

        <div class="footer">
            <div class="signature">
                <p>Yang menyerahkan,</p>
                <p><strong>(________________________)</strong></p>
                <p>Kepala Unit</p>
            </div>
            <div class="signature">
                <p>Yang menerima,</p>
                <p><strong>({{ $slip->nama_staff }})</strong></p>
            </div>
        </div>
    </div>
</div>

{{-- Tombol Cetak & Kembali --}}
<center class="no-print" style="margin-top: 30px;">
    <button onclick="window.print()" class="btn btn-primary">Cetak Slip</button>
    <a href="{{ route('slip-progresif.index') }}" class="btn btn-secondary">Kembali</a>
</center>

@endsection