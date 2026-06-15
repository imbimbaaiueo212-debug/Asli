<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Imbalan {{ $rekap->nama ?? '-' }}</title>
    <style>
        @page { size: A5 landscape; margin: 3mm; }
       
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            margin: 0;
            padding: 0;
            position: relative;
            background-size: 80% auto;
            opacity: 0.9;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .header-table { width: 100%; margin-bottom: 3px; }
        .header-table td { vertical-align: middle; padding: 2px 3px; }
        .logo-left, .logo-right { width: 60px; text-align: center; }
        .logo-center { width: 180px; text-align: center; font-weight: bold; }
        .info-table { width: 100%; margin-bottom: 3px; font-size: 7pt; }
        .info-table td { padding: 2px 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 2px; }
        th, td { padding: 2px 4px; font-size: 7pt; }
        th { text-align: left; background-color: #f2f2f2; }
        .section { width: 100%; overflow: hidden; margin-top: 3px; }
        .table-left, .table-right { width: 48%; display: inline-block; vertical-align: top; }
        .table-left { margin-right: 2%; }
        .table-title {
            display: block; background: #f5f7ff; padding: 4px 6px; margin-bottom: 3px;
            font-weight: bold; font-size: 7pt; border-radius: 3px;
        }
        .highlight-title { background: #fff4c2 !important; color: #8a6d3b; }
        .success-title { background: #dff0d8 !important; color: #3c763d; }
        .danger-title { background: #ffebee !important; color: #c62828; }
        .signature { margin-top: 8mm; width: 100%; font-size: 7pt; border-collapse: collapse; }
        .signature td { vertical-align: bottom; text-align: center; padding: 0 2mm; }
        .sig-line { display: inline-block; border-bottom: 1px solid #000; padding-bottom: 16mm; width: 25mm; }
        .pay-box { text-align: right; margin-bottom: 10px; }
        .pay-amount { font-weight: bold; color: #0b75ff; font-size: 11pt; }
        .pay-label { margin-top: 1mm; font-size: 7pt; }
        .footer-transfer { margin-top: 4mm; font-size: 7pt; text-align: center; border-top: 1px solid #000; padding-top: 2px; }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 65%;
            max-width: 480px;
            height: auto;
            opacity: 0.10;
            pointer-events: none;
            z-index: -1;
            filter: grayscale(90%) blur(1.2px);
            mix-blend-mode: multiply;
        }
        .adjustment-note {
            margin-top: 4px;
            padding: 4px 6px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            font-size: 6.5pt;
            line-height: 1.3;
        }
    </style>
</head>
<body>

@php
    // ==================== PERBAIKAN PATH GAMBAR ====================
    $docRoot = $_SERVER['DOCUMENT_ROOT'];

    function getBase64Image($fullPath) {
        if (!file_exists($fullPath)) return '';
        $type = pathinfo($fullPath, PATHINFO_EXTENSION);
        $data = file_get_contents($fullPath);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    // Convert gambar ke base64
    $logoLeftBase64    = getBase64Image($docRoot . '/template/img/logoslip.png');
    $logoCenterBase64  = getBase64Image($docRoot . '/template/img/logotulisan.png');
    $logoRightBase64   = getBase64Image($docRoot . '/template/img/jajal.png');
    $watermarkBase64   = getBase64Image($docRoot . '/template/img/terakhir.png');

    $rupiah = fn($v) => ($v && $v > 0) ? number_format($v, 0, ',', '.') : '0';

    // Pendapatan dasar
    $pokok      = $rekap->imbalan_pokok ?? 0;
    $lainnya    = $rekap->imbalan_lainnya ?? 0;
    $insentif   = $rekap->insentif_mentor ?? 0;
    $transport  = $rekap->tambahan_transport ?? 0;
    $kekurangan = $rekap->kekurangan ?? 0;

    // ==================== GUNAKAN NILAI DARI CONTROLLER ====================
    $totalPendapatan    = $totalPendapatan    ?? ($pokok + $lainnya + $insentif + $transport + $kekurangan + ($totalKekuranganAdj ?? 0) + ($totalNominalLembur ?? 0));
    $totalPotongan      = $totalPotongan      ?? 0;
    $yangDibayarkan     = $yangDibayarkan     ?? 0;
    $totalNominalLembur = $totalNominalLembur ?? 0;
    $totalJamLembur     = $totalJamLembur     ?? 0;

    // ==================== TOTAL POTONGAN ====================
    $totalPotonganTetap = 0;
    if ($potongan) {
        $totalPotonganTetap += 
            ($potongan->sakit ?? 0) +
            ($potongan->izin ?? 0) +
            ($potongan->alpa ?? 0) +
            ($potongan->tidak_aktif ?? 0) +
            ($potongan->cash_advance_nominal ?? 0) +
            ($potongan->lainnya ?? 0);
    }

    $cicilanNilai = $rekap->cicilan ?? 0;
    $cicilanKeterangan = $rekap->keterangan_cicilan ?? 'Cicilan Cash Advance';

    $totalPotongan = $totalPotonganTetap + $cicilanNilai + ($totalKelebihanAdj ?? 0);

    // ==================== YANG DIBAYARKAN ====================
    $yangDibayarkan = $totalPendapatan 
                      + ($rekap->jumlah_bagi_hasil ?? 0) 
                      - ($rekap->kelebihan ?? 0) 
                      - $cicilanNilai;

    // Data lain
    $unitName = $rekap->bimba_unit ?? $rekap->biMBA_unit ?? $profile?->bimba_unit ?? $profile?->unit ?? '-';
    $noCabang = $rekap->no_cabang ?? $profile?->no_cabang ?? null;
    $unitDisplay = ($noCabang && $unitName !== '-') ? $noCabang . ' - ' . strtoupper($unitName) : strtoupper($unitName);

    $periodeDisplay = $periode ?? $rekap->bulan ?? 'Periode Tidak Diketahui';

    $noRekening = $noRekening ?? $profile?->no_rekening ?? '-';
    $bank       = $bank ?? $profile?->bank ?? '-';
    $atasNama   = $atasNama ?? $profile?->nama_rekening ?? $rekap->nama ?? '-';

    $tanggalMasuk = $profile?->tgl_masuk
        ? \Carbon\Carbon::parse($profile->tgl_masuk)->translatedFormat('d F Y')
        : '-';
@endphp

    <!-- WATERMARK -->
    @if($watermarkBase64)
        <img src="{{ $watermarkBase64 }}" class="watermark" alt="watermark">
    @endif

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td class="logo-left">
                @if($logoLeftBase64)
                    <img src="{{ $logoLeftBase64 }}" style="width:50px;" alt="Logo Left">
                @else
                    <div style="width:50px; height:50px; background:#ddd; border:1px solid #ccc; display:flex; align-items:center; justify-content:center; font-size:8pt;">Logo</div>
                @endif
            </td>
            <td class="logo-center">
                <strong>SLIP IMBALAN RELAWAN biMBA AIUEO</strong><br>
                <strong style="font-size:6pt;">YAYASAN PENGEMBANGAN ANAK INDONESIA</strong><br>
                @if($logoCenterBase64)
                    <img src="{{ $logoCenterBase64 }}" style="width:150px;" alt="Logo Center">
                @else
                    <div style="width:150px; height:45px; background:#ddd; border:1px solid #ccc; display:flex; align-items:center; justify-content:center; font-size:8pt;">Logo Tulisan</div>
                @endif
                <br>bimbingan <span style="color:red;">MINAT</span> Baca & Belajar Anak
            </td>
            <td class="logo-right">
                @if($logoRightBase64)
                    <img src="{{ $logoRightBase64 }}" style="width:50px;" alt="Logo Right">
                @else
                    <div style="width:50px; height:50px; background:#ddd; border:1px solid #ccc; display:flex; align-items:center; justify-content:center; font-size:8pt;">Logo</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- INFO KARYAWAN -->
    <table class="info-table">
        <tr>
            <td>Nomor Induk / NIK</td><td>:</td><td>{{ $profile->nik ?? $rekap->nomor_induk ?? '-' }}</td>
            <td>biMBA Unit</td><td>:</td><td>{{ $unitDisplay }}</td>
        </tr>
        <tr>
            <td>Nama Relawan</td><td>:</td><td>{{ $rekap->nama ?? $profile?->nama ?? '-' }}</td>
            <td>Tanggal Masuk</td><td>:</td><td>{{ $tanggalMasuk }}</td>
        </tr>
        <tr>
            <td>Posisi</td><td>:</td><td>{{ $rekap->posisi ?? $profile?->jabatan ?? '-' }}</td>
            <td>Bulan Pembayaran</td><td>:</td><td>{{ $periodeDisplay }}</td>
        </tr>
    </table>

    <div class="section">
        <!-- KIRI -->
        <div class="table-left">
            <div class="table-title">Informasi Kinerja</div>
            <table>
                <tr>
                    <td class="label">Status Relawan</td>
                    <td class="value text-end">
                        <strong class="{{ strtolower($statusKaryawan ?? $rekap->status ?? 'Aktif') === 'magang' ? 'text-danger' : 'text-success' }}">
                            {{ $statusKaryawan ?? $rekap->status ?? $rekap->status_karyawan ?? 'Aktif' }}
                        </strong>
                    </td>
                </tr>
                <tr><td>Waktu /Minggu</td><td class="text-end">{{ $rekap->waktu_mgg ?? '-' }}</td></tr>
                <tr><td>Waktu /Bulan</td><td class="text-end">{{ $rekap->waktu_bln ?? '-' }}</td></tr>
                <tr><td>Kategori</td><td class="text-end">{{ $rekap->ktr ?? optional($rekap)->status ?? '-' }}</td></tr>
                <tr><td>Durasi Kerja</td><td class="text-end">{{ $rekap->durasi_kerja ?? $masaKerja ?? '-' }}</td></tr>
                <tr><td>Persentase</td><td class="text-end">{{ $rekap->persen ?? '-' }} %</td></tr>
                <tr><td>Imbalan Pokok</td><td class="text-end">Rp {{ $rupiah($pokok) }}</td></tr>
            </table>

            <div class="table-title success-title">PENDAPATAN</div>
            <table class="table table-borderless">
    <!-- Baris normal (2 kolom → diubah jadi 3 kolom agar rata) -->
    <tr>
        <td>Imbalan Lainnya</td>
        <td class="text-center"></td>
        <td class="text-end">Rp {{ $rupiah($lainnya ?? 0) }}</td>
    </tr>
    <tr>
        <td>Insentif Mentor</td>
        <td class="text-center"></td>
        <td class="text-end">Rp {{ $rupiah($insentif ?? 0) }}</td>
    </tr>

    <!-- Tambahan Transport -->
    @if($transport > 0)
    <tr style="background:#e8f5e9;">
        <td>Tambahan Transport</td>
        <td class="text-center text-success font-weight-bold">
            <span style="font-size:0.9em;">@</span>{{ $rekap->at_hari ?? 0 }} Hari
        </td>
        <td class="text-end text-success font-weight-bold">
            : Rp {{ $rupiah($transport) }}
        </td>
    </tr>
    @endif

    <!-- LEMBUR -->
    @if($totalNominalLembur > 0)
    <tr style="background:#e8f5e9;">
        <td>Lembur</td>
        <td class="text-center text-success font-weight-bold">
            <span style="font-size:0.9em;">@</span>{{ number_format($totalJamLembur, 0, '.', '.') }} Jam
        </td>
        <td class="text-end text-success font-weight-bold">
            : Rp {{ $rupiah($totalNominalLembur) }}
        </td>
    </tr>
    @endif

    <!-- Adjustment -->
    @if($totalKekuranganAdj > 0)
    <tr style="background:#e8f5e9;">
        <td>Tambahan Dibayar (Adjustment)</td>
        <td class="text-center"></td>
        <td class="text-end text-success font-weight-bold">
            Rp {{ $rupiah($totalKekuranganAdj) }}
        </td>
    </tr>
    @endif

    <!-- Total -->
    <tr style="font-weight:bold; background:#f2f2f2;">
        <td>Total Pendapatan</td>
        <td class="text-center"></td>
        <td class="text-end">Rp {{ $rupiah($totalPendapatan) }}</td>
    </tr>
</table>
        </div>

        <!-- KANAN -->
        <div class="table-right">
            <div class="table-title danger-title">POTONGAN</div>
            <table>
                @if($totalPotonganTetap > 0 || $cicilanNilai > 0 || $totalKelebihanAdj > 0)
                    @if($potongan?->sakit > 0) 
                        <tr><td>Potongan Sakit</td><td class="text-end text-danger">- Rp {{ $rupiah($potongan->sakit) }}</td></tr> 
                    @endif
                    @if($potongan?->izin > 0) 
                        <tr><td>Potongan Izin</td><td class="text-end text-danger">- Rp {{ $rupiah($potongan->izin) }}</td></tr> 
                    @endif
                    @if($potongan?->alpa > 0) 
                        <tr><td>Potongan Alpa</td><td class="text-end text-danger">- Rp {{ $rupiah($potongan->alpa) }}</td></tr> 
                    @endif
                    @if($potongan?->tidak_aktif > 0) 
                        <tr><td>Tidak Aktif</td><td class="text-end text-danger">- Rp {{ $rupiah($potongan->tidak_aktif) }}</td></tr> 
                    @endif
                    @if($potongan?->cash_advance_nominal > 0)
                        <tr><td>Cash Advance</td><td class="text-end text-danger">- Rp {{ $rupiah($potongan->cash_advance_nominal) }}</td></tr> 
                    @endif
                    @if($potongan?->lainnya > 0) 
                        <tr><td>Potongan Lain-lain</td><td class="text-end text-danger">- Rp {{ $rupiah($potongan->lainnya) }}</td></tr> 
                    @endif

                    @if($cicilanNilai > 0)
                        <tr style="background:#ffcdd2; font-weight:bold;">
                            <td>CICILAN CASH ADVANCE</td>
                            <td class="text-end text-danger"></td>
                        </tr>
                        <tr style="background:#ffebee;">
                            <td>• {{ $cicilanKeterangan }}</td>
                            <td class="text-end text-danger"><strong>- Rp {{ $rupiah($cicilanNilai) }}</strong></td>
                        </tr>
                    @endif

                    @if($totalKelebihanAdj > 0)
                        <tr style="background:#ffcdd2;">
                            <td>Potongan Adjustment</td>
                            <td class="text-end text-danger font-weight-bold">- Rp {{ $rupiah($totalKelebihanAdj) }}</td>
                        </tr>
                    @endif

                    <tr style="font-weight:bold;background:#ffebee;">
                        <td>Total Potongan</td>
                        <td class="text-end text-danger">- Rp {{ $rupiah($totalPotongan) }}</td>
                    </tr>
                @else
                    <tr><td colspan="2" class="text-center text-muted">Tidak ada potongan</td></tr>
                @endif
            </table>

            @if($keteranganKekuranganAdj || $keteranganKelebihanAdj)
                <div class="adjustment-note">
                    <strong>Keterangan Adjustment:</strong><br>
                    @if($keteranganKekuranganAdj)
                        <span style="color:#155724;">Tambahan Dibayar: {{ Str::limit($keteranganKekuranganAdj, 60) }}</span><br>
                    @endif
                    @if($keteranganKelebihanAdj)
                        <span style="color:#c62828;">Potongan Adjustment: {{ Str::limit($keteranganKelebihanAdj, 60) }}</span>
                    @endif
                </div>
            @endif

            <div class="table-title highlight-title">Total yang dibayarkan</div>
            <div class="pay-box">
                <div class="pay-amount">Rp {{ $rupiah($yangDibayarkan) }}</div>
            </div>

            <div class="table-title">Detail Transfer</div>
            <table>
                <tr><td style="width:40%;">No Rekening</td><td>{{ $noRekening }}</td></tr>
                <tr><td>Bank</td><td>{{ $bank }}</td></tr>
                <tr><td>Atas Nama</td><td>{{ $atasNama }}</td></tr>
            </table>
        </div>
    </div>

    <div style="clear:both;"></div>

    <!-- TANDA TANGAN -->
    <table class="signature">
        <tr>
            <td style="width:50%;">
                <div class="sig-line">Yang Menyerahkan</div>
                <div>Infinite Management</div>
            </td>
            <td style="width:50%;">
                <div class="sig-line">Penerima</div>
                <div>{{ $rekap->nama ?? $profile?->nama ?? '-' }}</div>
            </td>
        </tr>
    </table>

</body>
</html>