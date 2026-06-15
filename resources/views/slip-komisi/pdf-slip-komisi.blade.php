<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Komisi {{ $komisi->profile?->nama ?? 'Staff' }}</title>
    <style>
        @page { size: A5 landscape; margin: 5mm; }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 8pt; 
            margin: 0; 
            padding: 0; 
            color: #000; 
            line-height: 1.3; 
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        
        /* Header Logo */
        .header-table { width: 100%; margin-bottom: 3px; }
        .header-table td { vertical-align: middle; padding: 2px 3px; }
        .logo-left { width: 80px; text-align: center; }
        .logo-center { width: 180px; text-align: center; }
        .logo-right { width: 80px; text-align: center; }

        /* Info Staff */
        .info-table { width: 100%; margin-bottom: 3px; font-size: 7pt; }
        .info-table td { padding: 2px 4px; }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 2px; 
        }
        th, td { 
            padding: 2px 4px; 
            font-size: 7pt; 
        }
        th { 
            text-align: left; 
            background-color: #f2f2f2; 
        }

        .section { 
            width: 100%; 
            overflow: hidden; 
            margin-top: 3px; 
        }
        .table-left, .table-right { 
            width: 48%; 
            display: inline-block; 
            vertical-align: top; 
        }
        .table-left { margin-right: 2%; }

        .table-title {
            display: block;
            background: #f5f7ff;
            padding: 4px 6px;
            margin-bottom: 3px;
            font-weight: bold;
            font-size: 7pt;
            border-radius: 3px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Tanda Tangan */
        .signature {
            margin-top: 8mm;
            width: 100%;
            font-size: 7pt;
            padding-top: 2mm;
            page-break-inside: avoid;
        }
        .signature td {
            vertical-align: bottom;
            text-align: center;
            padding: 0 2mm;
        }
        .sig-line {
            display: inline-block;
            border-bottom: 1px solid #000;
            padding-bottom: 22mm;
            width: 25mm;
        }
        .sig-label { margin-top: 5px; }

        /* Total Besar */
        .pay-amount {
            font-weight: bold;
            color: #0b75ffff;
            font-size: 9pt;
        }
    </style>
</head>
<body>

@php
    $namaBulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $bulanLabel = $namaBulan[$komisi->bulan - 1] . ' ' . $komisi->tahun;

    // TOTAL YANG BENAR
    $jumlahDibayarkan = ($komisi->komisi_mb_bimba ?? 0)
                      + ($komisi->komisi_mt_bimba ?? 0)
                      + ($komisi->komisi_mb_english ?? 0)
                      + ($komisi->komisi_mt_english ?? 0)
                      + ($komisi->mb_insentif_ku ?? 0)
                      + ($komisi->insentif_bimba ?? 0)
                      + ($komisi->lebih_bimba ?? 0)
                      - ($komisi->kurang_bimba ?? 0);

    // ==================== PERBAIKAN PATH LOGO ====================
    $docRoot = $_SERVER['DOCUMENT_ROOT'];

    function getBase64Image($fullPath) {
        if (!file_exists($fullPath)) {
            return ''; 
        }
        $type = pathinfo($fullPath, PATHINFO_EXTENSION);
        $data = file_get_contents($fullPath);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    $logoLeftBase64   = getBase64Image($docRoot . '/template/img/logoslip.png');
    $logoCenterBase64 = getBase64Image($docRoot . '/template/img/logotulisan.png');
    $logoRightBase64  = getBase64Image($docRoot . '/template/img/jajal.png');
@endphp

<!-- HEADER LOGO -->
<table class="header-table">
    <tr>
        <td class="logo-left">
            @if($logoLeftBase64)
                <img src="{{ $logoLeftBase64 }}" style="width:50px;" alt="Logo Left">
            @else
                <div style="width:50px; height:50px; background:#ddd; border:1px solid #ccc; display:flex; align-items:center; justify-content:center; font-size:8pt;">
                    Logo
                </div>
            @endif
        </td>
        <td class="logo-center">
            <strong>SLIP PEMBAYARAN KOMISI MURID BARU & TRIAL</strong><br>
            <strong style="font-size:6pt;">YAYASAN PENGEMBANGAN ANAK INDONESIA</strong><br>
            @if($logoCenterBase64)
                <img src="{{ $logoCenterBase64 }}" style="width:150px;" alt="Logo Center">
            @else
                <div style="width:150px; height:45px; background:#ddd; border:1px solid #ccc; display:flex; align-items:center; justify-content:center; font-size:8pt;">
                    Logo Tulisan
                </div>
            @endif
            <br>bimbingan <span style="color:red;">MINAT</span> Baca & Belajar Anak
        </td>
        <td class="logo-right">
            @if($logoRightBase64)
                <img src="{{ $logoRightBase64 }}" style="width:50px;" alt="Logo Right">
            @else
                <div style="width:50px; height:50px; background:#ddd; border:1px solid #ccc; display:flex; align-items:center; justify-content:center; font-size:8pt;">
                    Logo
                </div>
            @endif
        </td>
    </tr>
</table>

<!-- INFO STAFF -->
<table class="info-table">
    <tr>
        <td>Nomor Induk</td><td>:</td><td>{{ $komisi->profile?->nik ?? '-' }}</td>
        <td>biMBA Unit</td><td>:</td><td>{{ strtoupper($komisi->departemen) }}</td>
    </tr>
    <tr>
        <td>Nama Staff</td><td>:</td><td><strong>{{ $komisi->profile?->nama ?? '-' }}</strong></td>
        <td>Tanggal Masuk</td><td>:</td>
        <td>
            {{ $komisi->profile?->tgl_masuk
                ? \Carbon\Carbon::parse($komisi->profile->tgl_masuk)->translatedFormat('d F Y')
                : '-' }}
        </td>
    </tr>
    <tr>
        <td>Jabatan</td><td>:</td><td>{{ $komisi->jabatan ?? '-' }}</td>
        <td>Bulan Bayar</td><td>:</td><td>{{ $bulanLabel }}</td>
    </tr>
</table>

<div class="section">
    <!-- KIRI -->
    <div class="table-left">
        <div class="table-title">a. Rincian Murid</div>
        <table>
            <tr><td>Murid Aktif (AM 1)</td><td class="text-end">{{ $komisi->am1_bimba ?? 0 }}</td></tr>
            <tr><td>Murid Aktif Bayar SPP (AM 2)</td><td class="text-end">{{ $komisi->am2_bimba ?? 0 }}</td></tr>
            <tr><td>Murid Baru biMBA (MB)</td><td class="text-end">{{ $komisi->murid_mb_bimba ?? 0 }}</td></tr>
            <tr><td>Murid Trial biMBA (MT)</td><td class="text-end">{{ $komisi->murid_mt_bimba ?? 0 }}</td></tr>
            <tr><td>Murid Baru English</td><td class="text-end">{{ $komisi->mb_english ?? 0 }}</td></tr>
            <tr><td>Murid Trial English</td><td class="text-end">{{ $komisi->mt_english ?? 0 }}</td></tr>
        </table>

        <div class="table-title">b. Rincian Pendapatan</div>
        <table>
            <tr><td>Penerimaan SPP biMBA-AIUEO</td><td class="text-end">Rp{{ number_format($komisi->spp_bimba ?? 0, 0, ',', '.') }}</td></tr>
            <tr><td>Penerimaan SPP English</td><td class="text-end">Rp{{ number_format($komisi->total_spp_english ?? 0, 0, ',', '.') }}</td></tr>
        </table>
    </div>

    <!-- KANAN -->
    <div class="table-right">
        <div class="table-title">c. Rincian Komisi</div>
        <table>
            <tr><td>Komisi MB biMBA</td><td class="text-end">Rp{{ number_format($komisi->komisi_mb_bimba ?? 0,0,',','.') }}</td></tr>
            <tr><td>Komisi MT biMBA</td><td class="text-end">Rp{{ number_format($komisi->komisi_mt_bimba ?? 0,0,',','.') }}</td></tr>
            <tr><td>Komisi MB English</td><td class="text-end">Rp{{ number_format($komisi->komisi_mb_english ?? 0,0,',','.') }}</td></tr>
            <tr><td>Komisi MT English</td><td class="text-end">Rp{{ number_format($komisi->komisi_mt_english ?? 0,0,',','.') }}</td></tr>
            <tr><td>Insentif KU</td><td class="text-end">Rp{{ number_format($komisi->mb_insentif_ku ?? 0,0,',','.') }}</td></tr>
            <tr><td>Insentif Tambahan</td><td class="text-end">Rp{{ number_format($komisi->insentif_bimba ?? 0,0,',','.') }}</td></tr>
        </table>

        <div class="table-title">d. Adjustment</div>
        <table>
            <tr><td>Kekurangan</td><td class="text-end text-danger">- Rp{{ number_format($komisi->kurang_bimba ?? 0,0,',','.') }}</td></tr>
            <tr><td>Kelebihan</td><td class="text-end text-success">+ Rp{{ number_format($komisi->lebih_bimba ?? 0,0,',','.') }}</td></tr>
            <tr>
                <td><strong>Jumlah Dibayarkan</strong></td>
                <td class="text-end pay-amount">
                    Rp{{ number_format($jumlahDibayarkan, 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div style="text-align:right; font-size:7pt; margin-top:4mm;">
                        Ditransfer ke:
                        <span style="color:#0b75ffff; font-weight:bold;">
                            {{ $komisi->profile?->bank ?? '-' }}
                        </span> |
                        <span style="color:#0b75ffff; font-weight:bold;">
                            {{ $komisi->profile?->no_rekening ?? '-' }}
                        </span> |
                        <span style="color:#0b75ffff; font-weight:bold;">
                            {{ $komisi->profile?->atas_nama_rekening ?? $komisi->profile?->nama ?? '-' }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

<div style="clear:both;"></div>

<!-- TANDA TANGAN -->
<table class="signature">
    <tr>
        <td style="width:33.33%;">
            <div class="sig-line">Yang Menyerahkan</div>
            <div class="sig-label">Keuangan Unit</div>
        </td>
        <td style="width:33.33%;">
            <div class="sig-line">Penerima</div>
            <div class="sig-label"><strong>{{ $komisi->profile?->nama }}</strong></div>
        </td>
    </tr>
</table>

</body>
</html>