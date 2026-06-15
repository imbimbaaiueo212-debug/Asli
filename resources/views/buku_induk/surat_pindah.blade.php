<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Pindah Murid - biMBA AIUEO</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 20mm;   /* Margin standar A4 */
        }

        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 0;
            line-height: 1.6; 
            font-size: 13.5px;
        }

        .header { 
            text-align: center; 
            margin-bottom: 15px; 
        }

        .kop-surat {
            width: 100% !important;
            max-width: 100% !important;
            height: auto !important;
            margin-bottom: 15px;
            display: block;
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 6px 0;
        }

        td { 
            padding: 4px 6px; 
            vertical-align: top; 
        }

        .label { 
            width: 145px; 
            font-weight: bold;
        }

        p {
            text-align: justify;
            margin: 9px 0;
        }

        .signature { 
            margin-top: 40px; 
            text-align: right; 
        }
    </style>
</head>
<body>

<div class="header">
    <img src="{{ public_path('template/img/KOP3.jpg') }}" 
         class="kop-surat" 
         style="width: 100%; height: auto;"
         alt="Kop Surat biMBA AIUEO">
</div>

<p>Yang Bertanda tangan di bawah ini :</p>

<table>
    <tr>
        <td class="label">Nama</td>
        <td>: {{ $nama_penandatangan }}</td>
    </tr>
    <tr>
        <td class="label">Jabatan</td>
        <td>: {{ $jabatan }}</td>
    </tr>
    <tr>
    <td class="label">Unit</td>
    <td>: {{ $unit }}</td>
    <td style="text-align: right; padding-right: 10px; white-space: nowrap;">
        Cabang ke : {{ $no_cabang }}
    </td>
</tr>
    <tr>
        <td class="label">Alamat Unit</td>
        <td>: {{ $alamat_unit }}</td>
    </tr>
    <tr>
        <td class="label">No Telp/WA</td>
        <td>: {{ $no_telp }}</td>
    </tr>
</table>

<p>Dengan ini menerangkan bahwa benar murid di bawah ini pernah mengikuti kegiatan belajar di biMBA-AIUEO tersebut di atas.</p>

<table>
    <tr><td class="label">Nama Murid</td><td>: <strong>{{ $nama_murid }}</strong></td></tr>
    <tr><td class="label">NIM</td><td>: {{ $nim }}</td></tr>
    <tr><td class="label">Alamat</td><td>: {{ $alamat_murid }}</td></tr>
    <tr><td class="label">Tanggal masuk</td><td>: {{ $tgl_masuk }}</td></tr>
    <tr><td class="label">Tanggal terakhir</td><td>: {{ $tgl_terakhir }}</td></tr>
    <tr><td class="label">Level</td><td>: {{ $level }}</td></tr>
    <tr><td class="label">Modul terakhir</td><td>: {{ $modul_terakhir }}</td></tr>
</table>

<p>Demikianlah surat keterangan ini sebagai informasi antar unit biMBA AIUEO untuk dipergunakan sebagaimana mestinya. Atas perhatian dan kerjasama yang baik, kami ucapkan terima kasih.</p>

<div class="signature">
    <p>{{ $alamat_kota_kab ?? '-' }}, {{ $tanggal_surat }}</p>
    <p>Hormat kami,</p>
    <br><br><br>
    <p><strong></strong></p>
    <p>Mitra/Kepala Unit</p>
</div>

</body>
</html>