<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Progresif {{ data_get($profile, 'nama', '-') }}</title>
    <style>
        @page {
            size: A5 landscape;
            margin: 5mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            margin: 0;
            padding: 0;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .header-table {
            width: 100%;
            margin-bottom: 3px;
        }
        .header-table td {
            vertical-align: middle;
            padding: 2px 3px;
        }
        .logo-left { width: 80px; text-align: center; }
        .logo-center { width: 180px; margin: 0; text-align: center; }
        .logo-right { width: 80px; text-align: center; }
        .info-table {
            width: 100%;
            margin-bottom: 3px;
            font-size: 7pt;
        }
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
        th { text-align: left; background-color: #f2f2f2; }
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
        .section .table-title {
            display: block;
            border: none;
            background: #f5f7ff;
            padding: 4px 6px;
            margin-bottom: 3px;
            font-weight: bold;
            font-size: 7pt;
            line-height: 1.3;
            border-radius: 3px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .signature {
            margin-top: 8mm;
            width: 100%;
            font-size: 7pt;
            padding-top: 2mm;
            border-collapse: collapse;
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
            padding-bottom: 15mm;
            width: 25mm;
        }
        .sig-label { margin-top: 2mm; }
        .footer-transfer {
            margin-top: 4mm;
            font-size: 7pt;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 2px;
        }
    </style>
</head>
<body>

@php
    // Helper format angka
    $fmt = function ($value) {
        return ($value ?? 0) ? number_format($value, 0, ',', '.') : '-';
    };

    // Unit & no cabang
    $unitNama = data_get($unit, 'biMBA_unit', '-');
    $unitCabang = data_get($unit, 'no_cabang');

    // ==================== PERBAIKAN PATH LOGO ====================
    $docRoot = $_SERVER['DOCUMENT_ROOT'];

    // Fungsi helper untuk mengubah gambar menjadi base64
    function getBase64Image($fullPath) {
        if (!file_exists($fullPath)) {
            return ''; // Kosong jika file tidak ditemukan
        }
        $type = pathinfo($fullPath, PATHINFO_EXTENSION);
        $data = file_get_contents($fullPath);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    // Convert logo ke base64
    $logoLeftBase64   = getBase64Image($docRoot . '/template/img/logoslip.png');
    $logoCenterBase64 = getBase64Image($docRoot . '/template/img/logotulisan.png');
    $logoRightBase64  = getBase64Image($docRoot . '/template/img/jajal.png');
@endphp

    <!-- HEADER LOGO -->
    <table class="header-table">
        <tr>
            <td class="logo-left">
                @if($logoLeftBase64)
                    <img src="{{ $logoLeftBase64 }}" style="width:50px; margin:0;" alt="Logo Left">
                @else
                    <div style="width:50px; height:50px; background:#ddd; border:1px solid #ccc; display:flex; align-items:center; justify-content:center; font-size:8pt;">
                        Logo
                    </div>
                @endif
            </td>
            <td class="logo-center">
                <strong>SLIP PEMBAYARAN PROGRESIF</strong><br>
                <strong style="font-size:6pt;">YAYASAN PENGEMBANGAN ANAK INDONESIA</strong><br>
                @if($logoCenterBase64)
                    <img src="{{ $logoCenterBase64 }}" style="width:150px; margin:0;" alt="Logo Center">
                @else
                    <div style="width:150px; height:40px; background:#ddd; border:1px solid #ccc; display:flex; align-items:center; justify-content:center; font-size:8pt;">
                        Logo Tulisan
                    </div>
                @endif
                <br>bimbingan <span style="color:red;">MINAT</span> Baca & Belajar Anak
            </td>
            <td class="logo-right">
                @if($logoRightBase64)
                    <img src="{{ $logoRightBase64 }}" style="width:50px; margin:0;" alt="Logo Right">
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
            <td>Nomor Induk</td>
            <td>:</td>
            <td>{{ data_get($profile, 'nik', '-') }}</td>
            <td>biMBA Unit</td>
            <td>:</td>
            <td>
                {{ $unitNama }}
                @if ($unitCabang && $unitCabang !== '-')
                    ({{ $unitCabang }})
                @endif
            </td>
        </tr>
        <tr>
            <td>Nama Staff</td>
            <td>:</td>
            <td>{{ data_get($profile, 'nama', '-') }}</td>
            <td>Tanggal Masuk</td>
            <td>:</td>
            <td>
                @if (data_get($profile, 'tgl_masuk'))
                    {{ \Carbon\Carbon::parse(data_get($profile, 'tgl_masuk'))->locale('id')->translatedFormat('d F Y') }}
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td>{{ data_get($profile, 'jabatan', '-') }}</td>
            <td>Bulan Bayar</td>
            <td>:</td>
            <td>{{ data_get($rekap, 'bulan', '-') }} {{ data_get($rekap, 'tahun', '-') }}</td>
        </tr>
    </table>

    <div class="section">
        <!-- Tabel Kiri -->
        <div class="table-left">
            <div class="table-title">Rincian Murid</div>
            <table>
                <tbody>
                    <tr><td>Murid Aktif (AM1)</td><td class="text-end">{{ data_get($rekap, 'murid_aktif_am1', 0) ?: 0 }}</td></tr>
                    <tr><td>Murid Aktif Bayar SPP (AM2)</td><td class="text-end">{{ data_get($rekap, 'murid_aktif_am2', 0) ?: 0 }}</td></tr>
                    <tr><td>Murid Garansi</td><td class="text-end">{{ data_get($rekap, 'murid_garansi', 0) ?: 0 }}</td></tr>
                    <tr><td>Murid Dhuafa</td><td class="text-end">{{ data_get($rekap, 'murid_dhuafa', 0) ?: 0 }}</td></tr>
                    <tr><td>Murid BNF (MBNF1)</td><td class="text-end">{{ data_get($rekap, 'murid_bnf1', 0) ?: 0 }}</td></tr>
                    <tr><td>Murid BNF Bayar SPP (MBNF2)</td><td class="text-end">{{ data_get($rekap, 'murid_bnf2', 0) ?: 0 }}</td></tr>
                    <tr><td>Murid Baru biMBA-AIUEO (MB)</td><td class="text-end">{{ data_get($rekap, 'murid_baru_bimba', 0) ?: 0 }}</td></tr>
                    <tr><td>Murid Trial biMBA-AIUEO (MT)</td><td class="text-end">{{ data_get($rekap, 'murid_trial_bimba', 0) ?: 0 }}</td></tr>
                    <tr><td>Murid Baru English (MBE)</td><td class="text-end">{{ data_get($rekap, 'murid_baru_english', 0) ?: 0 }}</td></tr>
                    <tr><td>Murid Trial English (MTE)</td><td class="text-end">{{ data_get($rekap, 'murid_trial_english', 0) ?: 0 }}</td></tr>
                </tbody>
            </table>

            <div class="table-title">Rincian Pendapatan</div>
            <table>
                <tbody>
                    <tr>
                        <td>Penerimaan SPP biMBA-AIUEO</td>
                        <td class="text-end">Rp {{ $fmt(data_get($rekap, 'spp_bimba', 0)) }}</td>
                    </tr>
                    <tr>
                        <td>Penerimaan SPP English</td>
                        <td class="text-end">Rp {{ $fmt(data_get($rekap, 'spp_english', 0)) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Tabel Kanan -->
        <div class="table-right">
            <div class="table-title">Rincian Pembayaran</div>
            <table>
                <tbody>
                    <tr><td>Total FM</td><td class="text-end">{{ data_get($rekap, 'total_fm', 0) ?: 0 }}</td></tr>
                    <tr><td>Nilai Progresif</td><td class="text-end">Rp {{ $fmt(data_get($rekap, 'progresif', 0)) }}</td></tr>
                    <tr><td>Total Komisi</td><td class="text-end">Rp {{ $fmt(data_get($rekap, 'komisi', 0)) }}</td></tr>
                    <tr><td>Komisi MB biMBA</td><td class="text-end">Rp {{ $fmt(data_get($rekap, 'komisi_mb_bimba', 0)) }}</td></tr>
                    <tr><td>Komisi MT biMBA</td><td class="text-end">Rp {{ $fmt(data_get($rekap, 'komisi_mt_bimba', 0)) }}</td></tr>
                    <tr><td>Komisi MB English</td><td class="text-end">Rp {{ $fmt(data_get($rekap, 'komisi_mb_english', 0)) }}</td></tr>
                    <tr><td>Komisi MT English</td><td class="text-end">Rp {{ $fmt(data_get($rekap, 'komisi_mt_english', 0)) }}</td></tr>
                    <tr><td>Total Dibayarkan</td><td class="text-end">Rp {{ $fmt(data_get($rekap, 'dibayarkan', 0)) }}</td></tr>
                </tbody>
            </table>

            <div class="table-title">Rincian Adjustment</div>
            <table>
                <tbody>
                    <tr><td>Kekurangan Progresif</td><td class="text-end">Rp {{ $fmt(data_get($rekap, 'kurang', 0)) }}</td></tr>
                    <tr><td>Kelebihan Progresif</td><td class="text-end">Rp {{ $fmt(data_get($rekap, 'lebih', 0)) }}</td></tr>
                    <tr>
                        <td><strong>Jumlah Dibayarkan</strong></td>
                        <td class="text-end" style="font-weight:bold; color:#0b75ff;">
                            Rp {{ $fmt(data_get($rekap, 'dibayarkan', 0)) }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div style="text-align: right; font-size: 7pt; margin-top: 4mm;">
                                Ditransfer ke:
                                <span style="color: #0b75ff; font-weight: bold;">
                                    {{ data_get($profile, 'bank', '-') }}
                                </span> |
                                <span style="color: #0b75ff; font-weight: bold;">
                                    {{ data_get($profile, 'no_rekening', '-') }}
                                </span> |
                                <span style="color: #0b75ff; font-weight: bold;">
                                    {{ data_get($profile, 'nama', '-') }}
                                </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div style="clear:both;"></div>

    <!-- Footer Tanda Tangan -->
    <table class="signature">
        <tr>
            <td style="width:33.33%;">
                <div class="sig-label">Yang Menyerahkan,<br>Kepala Unit</div>
                <div class="sig-line"></div>
            </td>
            <td style="width:33.33%;">
                <div class="sig-label">Mengetahui,<br>INFINITE MANAGEMENT</div>
                <div class="sig-line"></div>
            </td>
        </tr>
    </table>

</body>
</html>