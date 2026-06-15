<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Pembayaran Komisi - {{ $profile->nama }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; margin: 40px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 10px; text-align: left; }
        th { background-color: #f0f0f0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .uppercase { text-transform: uppercase; }
        .bold { font-weight: bold; }
        .mt-20 { margin-top: 20px; }
        .mt-40 { margin-top: 40px; }
        .logo { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="logo">
    <h1>biMBA AIUEO</h1>
    <h2>PEMBAYARAN KOMISI</h2>
</div>

<table>
    <tr>
        <td width="15%"><strong>NO</strong></td>
        <td>{{ $komisi->nomor_urut ?? '-' }}</td>
        <td width="15%"><strong>NAMA</strong></td>
        <td><strong>{{ strtoupper($profile->nama) }}</strong></td>
    </tr>
    <tr>
        <td><strong>JABATAN</strong></td>
        <td>{{ $profile->jabatan }}</td>
        <td><strong>STATUS</strong></td>
        <td>{{ $profile->status_karyawan }}</td>
    </tr>
    <tr>
        <td><strong>DEPARTEMEN</strong></td>
        <td>{{ $profile->departemen }}</td>
        <td><strong>MASA KERJA</strong></td>
        <td>{{ $profile->masa_kerja ?? '-' }}</td>
    </tr>
</table>

<h3>DATA BANK:</h3>
<table>
    <tr>
        <td width="25%"><strong>NO REKENING</strong></td>
        <td>{{ $profile->no_rekening ?? '-' }}</td>
    </tr>
    <tr>
        <td><strong>BANK</strong></td>
        <td>{{ $profile->bank ?? '-' }}</td>
    </tr>
    <tr>
        <td><strong>ATAS NAMA</strong></td>
        <td>{{ $profile->atas_nama_rekening ?? $profile->nama }}</td>
    </tr>
</table>

<div class="mt-20">
    <h3>biMBA AIUEO:</h3>
    <table>
        <tr>
            <td width="40%">THP (Terima Bersih)</td>
            <td class="text-right bold">Rp {{ number_format($totalKomisiBimba, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>INSENTIF (MB Umum KU)</td>
            <td class="text-right">Rp {{ number_format($komisi->mb_insentif_ku, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>KURANG LEBIH BULAN</td>
            <td class="text-right">Rp 0</td>
        </tr>
    </table>
</div>

<div class="mt-20">
    <h3>ENGLISH biMBA:</h3>
    <table>
        <tr>
            <td width="40%">THP (Terima Bersih)</td>
            <td class="text-right bold">Rp {{ number_format($totalKomisiEnglish, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>KURANG LEBIH BULAN</td>
            <td class="text-right">Rp 0</td>
        </tr>
    </table>
</div>

<div class="mt-40">
    <h3>KOSONGKAN:</h3>
    <table>
        <tr><td height="80"></td></tr>
    </table>
</div>

<div style="margin-top: 50px; float: right; text-align: center;">
    <p>Hormat Kami,</p>
    <br><br><br>
    <p><strong>(_________________________)</strong></p>
    <p>Kepala Unit / Finance</p>
</div>

</body>
</html>