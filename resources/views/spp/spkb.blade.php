<!DOCTYPE html>
<html lang="id">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SPKB - {{ $murid->nama ?? '-' }}</title>

<style>

/* ======== SETUP DASAR ======== */
body {
    font-family: Arial, sans-serif;
    font-size: 12pt;
    line-height: 1.5;
    margin: 0;
    padding: 0;
    color: #000;
}

/* ======== WATERMARK (ANTI GAGAL) ======== */
body::before {
    content: "";
    position: fixed;
    top: 50%;
    left: 50%;
    width: 150mm;
    height: 150mm;
    transform: translate(-50%, -50%);
    background: url('{{ asset('template/img/terakhir.png') }}') no-repeat center;
    background-size: contain;
    opacity: 0.06;
    z-index: 0;
    pointer-events: none;
}

/* ======== PAGE SETTING ======== */
@page {
    size: A4;
    margin: 15mm 15mm 20mm 15mm;
}

/* ======== CONTAINER ======== */
.container {
    position: relative;
    z-index: 1;
    width: 180mm;
    max-width: 100%;
    margin: 0 auto;
    border: 1px solid #000;
    padding: 15mm;
    box-sizing: border-box;
    background: transparent; /* WAJIB transparan */
}

/* ======== KOP ======== */
.kop-surat {
    text-align: center;
    margin-bottom: 10mm;
    width: 100%;
}

.kop-surat img {
    max-width: 170mm;
    width: 100%;
    height: auto;
}

/* ======== TITLE ======== */
.section-title {
    text-align: center;
    font-weight: bold;
    font-size: 15pt;
    margin-bottom: 5px;
}

.sub-title {
    text-align: center;
    font-size: 12pt;
    margin-bottom: 15px;
}

/* ======== PARAGRAF ======== */
.address-block p,
.content-paragraph {
    margin: 6px 0;
}

/* ======== FORM TABLE ======== */
.form-data table {
    width: 100%;
    margin: 10px 0;
    border-collapse: collapse;
}

.form-data td {
    padding: 3px 0;
}

.form-data td.label {
    width: 160px;
    font-weight: bold;
    vertical-align: top;
}

/* ======== SIGNATURE ======== */
.signature-block {
    display: table;
    width: 100%;
    margin-top: 50px;
    table-layout: fixed;
    text-align: center;
}

.signature-item {
    display: table-cell;
    width: 33%;
}

.signature-line {
    border-bottom: 1px solid #000;
    width: 80%;
    margin: 50px auto 5px;
    font-size: 10pt;
}

/* ======== FOOTER ======== */
.footer {
    text-align: center;
    font-size: 10pt;
    margin-top: 48px;
    padding-top: 15px;
    border-top: 1px solid #ddd;
}

/* ======== BUTTON PRINT ======== */
#printButton {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    background: #007BFF;
    color: #fff;
    border: none;
    padding: 8px 14px;
    border-radius: 6px;
    cursor: pointer;
}

@media print {

    #printButton {
        display: none;
    }

    .container {
        border: none;
        padding: 0;
        width: 180mm;
    }

    html, body {
        width: 210mm;
        height: 297mm;
    }
}

</style>
</head>

<body>

<button id="printButton" onclick="window.print()">🖨️ Cetak Surat</button>

<div class="container">

    <!-- KOP -->
    <div class="kop-surat">
        <img src="{{ asset('template/img/KOP.jpeg') }}" alt="Kop Surat">
    </div>

    <!-- TITLE -->
    <div class="section-title">
        SURAT PERMOHONAN KETERLAMBATAN BAYAR
    </div>

    <div class="sub-title">
        No. {{ $spp->no_surat ?? '_____/SPKB/biMBA/____/____' }}
    </div>

    <!-- ADDRESS -->
    <div class="address-block">
        <p>Kepada</p>
        <p>Kepala Unit biMBA {{ $murid->unit ?? '-' }}</p>
        <p>di Tempat</p>
    </div>

    <!-- CONTENT -->
    <div class="content-paragraph">
        Saya yang bertandatangan di bawah ini orangtua/wali dari Murid biMBA-AIUEO:
    </div>

    <!-- DATA -->
    <div class="form-data">
        <table>
            <tr>
                <td class="label">Nama Orangtua</td>
                <td>: {{ $murid->orangtua ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Nama Murid</td>
                <td>: {{ $murid->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">NIM</td>
                <td>: {{ $murid->nim ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Golongan & SPP</td>
                <td>: {{ $murid->gol ?? '-' }} / {{ $spp->spp ?? '0' }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Bayar</td>
                <td>: {{ $spp->tgl_bayar ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Unit</td>
                <td>: {{ $murid->unit ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="content-paragraph">
        Mengajukan permohonan agar anak saya dapat mengikuti kegiatan di kelas sampai akhir bulan.
    </div>

    <div class="content-paragraph">
        Saya berjanji akan melunasi kewajiban SPP dalam bulan ini pada tanggal
        {{ $spp->tgl_bayar ?? '-' }}.
    </div>

    <div class="content-paragraph">
        Demikian surat permohonan ini dibuat. Atas perhatian dan kebijaksanaannya saya ucapkan terima kasih.
    </div>

    <!-- TANGGAL -->
    <div style="text-align:right; margin-top:40px;">
        {{ $murid->kota ?? '-' }}, {{ now()->format('d/m/Y') }}
    </div>

    <!-- TTD -->
    <div class="signature-block">
        <div class="signature-item">
            <p>Pemohon,</p>
            <div class="signature-line"></div>
            {{ $murid->orangtua }}
            <p>Orangtua / Wali</p>
        </div>

        <div class="signature-item">
            <p>Menyetujui,</p>
            <div class="signature-line"></div>
            {{ $murid->guru }}
            <p>Wali Kelas</p>
        </div>

        <div class="signature-item">
            <p>Mengetahui,</p>
            <div class="signature-line"></div>
            {{ $kepalaUnit }}
            <p>Kepala Unit</p>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        {{ $footerAlamat ?? 'Alamat Unit' }}<br>
        Telp: {{ $footerTelepon ?? '-' }}<br>
        <strong>{{ $footerWebsite ?? '-' }}</strong>
    </div>

</div>

</body>
</html>