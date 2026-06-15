<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Sertifikat Beasiswa biMBA-AIUEO</title>

<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Open+Sans:wght@400;700;900&display=swap" rel="stylesheet">

<style>

/* ============================= */
/* PRINT SETTING */
/* ============================= */
@media print {
    @page {
        size: A4 landscape;
        margin: 0;
    }

    html, body {
        width: 297mm;
        height: 210mm;
        margin: 0;
    }
}

html, body {
    margin: 0;
    padding: 0;
    font-family: 'Open Sans', sans-serif;
    background: #ffffff;
}

/* ============================= */
/* CONTAINER */
/* ============================= */
.certificate-container {
    width: 297mm;
    height: 210mm;
    padding: 10mm 18mm;
    box-sizing: border-box;
    position: relative;
    overflow: hidden;

    border: 14px solid;
    border-image: linear-gradient(45deg, #e74c3c, #f1c40f, #3498db, #2ecc71) 1;

    background: linear-gradient(
        180deg,
        #99d6ff 0%,
        #ffffff 45%,
        #f9e79f 100%
    );
}

/* ============================= */
/* WATERMARK */
/* ============================= */
.watermark-img {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 900px; /* lebih besar agar penuh */
    opacity: 0.06;
    z-index: 0;
    pointer-events: none;
}

/* ============================= */
/* HEADER */
/* ============================= */
.header {
    text-align: center;
    position: relative;
    z-index: 1;
    margin-bottom: 10px;
}

.top-logos {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* LOGO KIRI & KANAN DIPERBESAR */
.logo-small {
    width: 130px;
    margin-top: 50px;
}

/* NAMA YAYASAN */
.yayasan-name {
    font-weight: bold;
    font-size: 24px;
    letter-spacing: 1px;
    color: #1a5276;
    margin-bottom: 100px;
}

/* LOGO TULISAN biMBA DIPERBESAR */
.bimba-title img {
    height: 100px;
    margin-top: -100px;
}

/* SUB HEADER */
.sub-header {
    font-size: 18px;
    font-weight: bold;
    margin-top: 8px;
}

/* JUDUL UTAMA */
.main-title {
    font-family: 'Great Vibes', cursive;
    font-size: 60px;
    margin: 15px 0 20px;
    color: #2c3e50;
}

/* ============================= */
/* CONTENT */
/* ============================= */
.content-body {
    position: relative;
    z-index: 1;
    padding: 0 50px;
}

.given-to {
    font-family: 'Great Vibes', cursive;
    font-size: 36px;
    margin-bottom: 20px;
    color: #34495e;
}

.input-group {
    display: flex;
    align-items: flex-end;
    margin-bottom: 15px;
    font-size: 17px;
}

.label {
    width: 200px;
    font-weight: bold;
    font-style: italic;
    color: #2c3e50;
}

.value {
    flex: 1;
    border-bottom: 2px dotted #555;
    padding-bottom: 5px;
    word-break: break-word;
}

/* ============================= */
/* FOOTER */
/* ============================= */
.footer {
    position: absolute;
    bottom: 25mm;
    right: 60mm;
    text-align: center;
    z-index: 1;
}

.signature-line {
    width: 240px;
    height: 2px;
    background: #000;
    margin-bottom: 8px;
}

.signer-name {
    font-weight: bold;
    font-size: 17px;
}

.signer-title {
    font-size: 13px;
}

</style>
</head>

<body>

<div class="certificate-container">

    <!-- WATERMARK -->
    <img src="{{ asset('template/img/terakhir.png') }}" 
         class="watermark-img" 
         alt="Watermark">

    <!-- HEADER -->
    <div class="header">

        <div class="top-logos">
            <img src="{{ asset('template/img/logoslip.png') }}" class="logo-small">
            <div class="yayasan-name">YAYASAN PENGEMBANGAN ANAK INDONESIA</div>
            <img src="{{ asset('template/img/tutwuri.png') }}" class="logo-small">
        </div>

        <div class="bimba-title">
            <img src="{{ asset('template/img/logotulisan.png') }}" alt="biMBA-AIUEO">
        </div>

        <div class="sub-header">
            bimbingan MINAT Baca dan belajar Anak<br>
            Pendidikan Anak Usia Dini (PAUD)
        </div>

        <div class="main-title">Beasiswa Pendidikan</div>
    </div>

    <!-- CONTENT -->
    <div class="content-body">

        <div class="given-to">Diberikan kepada :</div>

        <div class="input-group">
            <div class="label">Nama</div>
            <div class="value">{{ strtoupper($beasiswa->nama) }}</div>
        </div>

        <div class="input-group">
            <div class="label">Tanggal Lahir</div>
            <div class="value">
                {{ optional($beasiswa->tanggal_lahir)->translatedFormat('d F Y') }}
            </div>
        </div>

        <div class="input-group">
            <div class="label">Alamat</div>
            <div class="value">{{ $beasiswa->alamat }}</div>
        </div>

        <div class="input-group">
            <div class="label">Nama Orang Tua</div>
            <div class="value">{{ $beasiswa->nama_orang_tua }}</div>
        </div>

    </div>

</div>

<script>
window.onload = function() {
    window.print();
}
</script>

</body>
</html>