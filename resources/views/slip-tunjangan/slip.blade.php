@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="text-center mb-4">SLIP PEMBAYARAN TUNJANGAN</h3>

    {{-- Identitas Staff --}}
    <table class="table table-bordered">
        <tr>
            <td width="20%">Nomor Induk</td>
            <td>{{ $slip->nomor_induk ?? '-' }}</td>
            <td width="20%">Unit</td>
            <td>{{ $slip->unit ?? '-' }}</td>
        </tr>
        <tr>
            <td>Nama Staff</td>
            <td>{{ $slip->nama_staff }}</td>
            <td>Tanggal Masuk</td>
            <td>{{ $slip->tanggal_masuk ? \Carbon\Carbon::parse($slip->tanggal_masuk)->format('d M Y') : '-' }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>{{ $slip->jabatan ?? '-' }}</td>
            <td>Bulan</td>
            <td>{{ $slip->bulan ?? '-' }}</td>
        </tr>
    </table>

    {{-- Pendapatan & Potongan --}}
    <div class="row">
        <div class="col-md-6">
            <h5>Pendapatan</h5>
            <table class="table table-sm table-striped">
                <tr><td>Tunjangan Pokok</td><td class="text-end">Rp {{ number_format($slip->tunjangan_pokok,0,',','.') }}</td></tr>
                <tr><td>Tunjangan Harian</td><td class="text-end">Rp {{ number_format($slip->tunjangan_harian,0,',','.') }}</td></tr>
                <tr><td>Tunjangan Fungsional</td><td class="text-end">Rp {{ number_format($slip->tunjangan_fungsional,0,',','.') }}</td></tr>
                <tr><td>Tunjangan Kesehatan</td><td class="text-end">Rp {{ number_format($slip->tunjangan_kesehatan,0,',','.') }}</td></tr>
                <tr><td>Tunjangan Kerajinan</td><td class="text-end">Rp {{ number_format($slip->tunjangan_kerajinan,0,',','.') }}</td></tr>
                <tr><td>Komisi English biMBA</td><td class="text-end">Rp {{ number_format($slip->komisi_english,0,',','.') }}</td></tr>
                <tr><td>Komisi Mentor Magang</td><td class="text-end">Rp {{ number_format($slip->komisi_mentor,0,',','.') }}</td></tr>
                <tr><td>Kekurangan Tunjangan</td><td class="text-end">Rp {{ number_format($slip->kekurangan_tunjangan,0,',','.') }}</td></tr>
                <tr><td>Tunjangan Keluarga</td><td class="text-end">Rp {{ number_format($slip->tunjangan_keluarga,0,',','.') }}</td></tr>
                <tr><td>Lain-lain Pendapatan</td><td class="text-end">Rp {{ number_format($slip->lain_lain_pendapatan,0,',','.') }}</td></tr>
                <tr class="table-success fw-bold"><td>Total Pendapatan</td><td class="text-end">Rp {{ number_format($slip->total_pendapatan,0,',','.') }}</td></tr>
            </table>
        </div>

        <div class="col-md-6">
            <h5>Potongan</h5>
            <table class="table table-sm table-striped">
                <tr><td>Sakit</td><td class="text-end">Rp {{ number_format($slip->sakit,0,',','.') }}</td></tr>
                <tr><td>Izin</td><td class="text-end">Rp {{ number_format($slip->izin,0,',','.') }}</td></tr>
                <tr><td>Alpa</td><td class="text-end">Rp {{ number_format($slip->alpa,0,',','.') }}</td></tr>
                <tr><td>Tidak Aktif</td><td class="text-end">Rp {{ number_format($slip->tidak_aktif,0,',','.') }}</td></tr>
                <tr><td>Kelebihan Tunjangan</td><td class="text-end">Rp {{ number_format($slip->kelebihan_tunjangan,0,',','.') }}</td></tr>
                <tr><td>Lain-lain Potongan</td><td class="text-end">Rp {{ number_format($slip->lain_lain_potongan,0,',','.') }}</td></tr>
                <tr class="table-danger fw-bold"><td>Total Potongan</td><td class="text-end">Rp {{ number_format($slip->total_potongan,0,',','.') }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Dibayarkan --}}
    <h5 class="mt-3">Jumlah Dibayarkan</h5>
    <table class="table table-bordered">
        <tr>
            <td class="fw-bold">Take Home Pay</td>
            <td class="text-end fw-bold">Rp {{ number_format($slip->dibayarkan,0,',','.') }}</td>
        </tr>
    </table>

    {{-- Rekening & Email --}}
    <h5>Rekening & Email</h5>
    <p>
        {{ $slip->bank ?? '-' }} | {{ $slip->no_rekening ?? '-' }} | {{ $slip->atas_nama ?? '-' }} <br>
        {{ $slip->email ?? '-' }}
    </p>

    {{-- Tanda Tangan --}}
    <div class="row mt-5">
        <div class="col text-center">
            <p>Yang menyerahkan,</p>
            <br><br><br>
            <p>___________________</p>
            <p>Kepala Unit</p>
        </div>
        <div class="col text-center">
            <p>Penerima,</p>
            <br><br><br>
            <p>___________________</p>
            <p>{{ $slip->nama_staff }}</p>
        </div>
    </div>

    <a href="{{ route('slip-tunjangan.index') }}" class="btn btn-secondary mt-3">Kembali</a>
</div>
@endsection
