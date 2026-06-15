<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Garansi BCA 372 Bebas</title>

<style>
@page {
    size: A5 landscape;
    margin: 10mm;
}

body {
    margin: 0;
    padding: 0;
    font-family: DejaVu Sans, sans-serif;
    font-size: 11px;
}

.page {
    position: relative;
    
    padding: 16px 20px;
    box-sizing: border-box;
    text-align: center;

    page-break-inside: avoid;
    overflow: hidden;
}

/* watermark */
.watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 70px;
    font-weight: bold;
    color: #1e5aa7;
    opacity: 0.05;
    white-space: nowrap;
}

.title {
    font-size: 22px;
    font-weight: bold;
    letter-spacing: 2px;
    margin-bottom: 6px;
}

.subtitle {
    font-size: 12px;
    margin-bottom: 14px;
}

.name {
    font-size: 17px;
    font-weight: bold;
    margin: 10px 0 4px;
}

.unit {
    font-size: 12px;
    font-weight: bold;
    color: #1e5aa7;
    margin-bottom: 10px;
}

.amount {
    font-size: 18px;
    font-weight: bold;
    margin: 10px 0 4px;
}

.amount-text {
    font-size: 11px;
    font-style: italic;
    margin-bottom: 10px;
}

.desc {
    font-size: 11px;
    margin: 0 25px 10px;
    line-height: 1.4;
}

.footer {
    margin-top: 18px;
    font-size: 11px;
}

.line {
    width: 120px;
    margin: 30px auto 4px;
    border-top: 1px solid #000;
}
.logo {
    width: 50px;        /* atur sesuai selera */
    margin: 0 auto 10px; /* tengah + jarak bawah */
    display: block;
}
.watermark-img {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    
    width: 500px;          /* sesuaikan */
    opacity: 0.08;         /* transparan */
    z-index: 0;
}


</style>
</head>

<body>
<div class="page">

    <div class="corner-tr"></div>
    <div class="corner-bl"></div>

    <img src="{{ public_path('template/img/terakhir.png') }}"
         class="watermark-img">

    <img src="{{ public_path('template/img/logoslip.png') }}"
         class="logo">

    <div class="title">GARANSI BCA 372 BEBAS</div>
    <div class="subtitle"></div>

    <div class="desc">Diberikan kepada:</div>

    <div class="name">{{ strtoupper($data->nama_murid) }}</div>
    <div class="unit">{{ $data->bimba_unit }}</div>

    <div class="desc">
        Tempat / Tanggal Lahir:<br>
        <strong>{{ $data->tempat_tanggal_lahir }}</strong>
    </div>

    <div class="desc">
        Tanggal Masuk:
        <strong>{{ $data->tanggal_masuk?->translatedFormat('d F Y') }}</strong>
    </div>

    <div class="desc">
        Nama Orang Tua / Wali:<br>
        <strong>{{ $data->nama_orang_tua_wali }}</strong>
    </div>

    <div class="desc">
        Nomor Virtual Account:<br>
        <strong>{{ $data->virtual_account ?? '-' }}</strong>
    </div>

    <div class="desc">
        Surat Garansi ini diberikan pada tanggal
        <strong>
            {{ $data->tanggal_diberikan?->translatedFormat('d F Y') }}
        </strong>
    </div>

</div>
</body>
</html>
