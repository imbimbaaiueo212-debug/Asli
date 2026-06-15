<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Tunjangan {{ $selectedSlip->nama ?? '' }}</title>
<style>
    @page {
        size: A5 landscape;
        margin: 4mm;
    }
    body {
        font-family: Arial, sans-serif;
        font-size: 9pt;
        margin: 0;
        padding: 0;
    }
    .text-end { text-align: right; }
    .text-center { text-align: center; }
    .header-table {
        width: 100%;
        margin-bottom: 4px;
    }
    .header-table td {
        vertical-align: middle;
        padding: 2.5px 4px;
    }
    .logo-left { width: 75px; text-align: center; }
    .logo-center { width: auto; margin: 2px 0; }
    .logo-right { width: 75px; text-align: center; }
    .info-table {
        width: 100%;
        margin-bottom: 4px;
        font-size: 7pt;
    }
    .info-table td { padding: 2px 4px; }
    .section { width: 100%; overflow: hidden; margin-top: 3px; }
    .table-left,
    .table-right { width: 49%; display: inline-block; vertical-align: top; }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 3px;
    }
    th, td { padding: 3px 5px; font-size: 7pt; }
    th { text-align: left; background-color: #f2f2f2; }
    .signature {
        margin-top: 2mm;
        width: 100%;
        font-size: 7pt;
        text-align: center;
        border-collapse: collapse;
        page-break-inside: avoid;
    }
    .signature td {
        vertical-align: bottom;
        padding: 0 4mm;
    }
    .sig-title { margin-bottom: 2.5mm; }
    .sig-space { height: 12mm; }
    .sig-line-text { margin-bottom: 2mm; }
    .sig-label { margin-bottom: 8mm; }
    .bank-info, .keterangan { font-size: 7pt; margin-top: 3px; }
</style>
</head>
<body>

@php
    // Guard defaults
    $selectedSlip = $selectedSlip ?? null;
    $profile = $profile ?? null;
    $unitData = $unitData ?? null;
    $tunjanganPokok = $tunjanganPokok ?? 0;
    $tunjanganHarian = $tunjanganHarian ?? 0;
    $tunjanganFungsional = $tunjanganFungsional ?? 0;
    $tunjanganKesehatan = $tunjanganKesehatan ?? 0;

    // Bulan Label
    $selectedBulanLabel = '-';
    if (!empty($selectedSlip->bulan)) {
        try {
            $selectedBulanLabel = \Carbon\Carbon::createFromFormat('Y-m', $selectedSlip->bulan)
                ->locale('id')->translatedFormat('F Y');
        } catch (\Throwable $e) {
            $selectedBulanLabel = $selectedSlip->bulan;
        }
    } else {
        if (!empty($selectedBulan ?? null) && !empty($selectedTahun ?? null)) {
            try {
                $bulanMapping = [
                    'Januari'=>'01','Februari'=>'02','Maret'=>'03','April'=>'04',
                    'Mei'=>'05','Juni'=>'06','Juli'=>'07','Agustus'=>'08',
                    'September'=>'09','Oktober'=>'10','November'=>'11','Desember'=>'12'
                ];
                $mn = $bulanMapping[$selectedBulan] ?? $selectedBulan;
                $selectedBulanLabel = \Carbon\Carbon::createFromFormat('Y-m', ($selectedTahun.'-'.$mn))
                    ->locale('id')->translatedFormat('F Y');
            } catch (\Throwable $e) {
                $selectedBulanLabel = ($selectedBulan ?? '-') . ' ' . ($selectedTahun ?? '-');
            }
        }
    }

    // Total Pendapatan
    $totalPendapatan = ($tunjanganPokok ?? 0)
        + ($tunjanganHarian ?? 0)
        + ($tunjanganFungsional ?? 0)
        + ($tunjanganKesehatan ?? 0)
        + ($selectedSlip->tunjangan_kerajinan ?? 0)
        + ($selectedSlip->komisi_english ?? 0)
        + ($selectedSlip->komisi_mentor ?? 0)
        + ($selectedSlip->kekurangan_tunjangan ?? 0)
        + ($selectedSlip->tunjangan_keluarga ?? 0)
        + ($selectedSlip->lain_lain_pendapatan ?? 0);

    // Potongan
    $pot_sakit = $selectedSlip->potongan_sakit ?? 0;
    $pot_izin = $selectedSlip->potongan_izin ?? 0;
    $pot_alpa = $selectedSlip->potongan_alpa ?? 0;
    $pot_tidak_aktif = $selectedSlip->potongan_tidak_aktif ?? 0;
    $pot_kelebihan = $selectedSlip->potongan_kelebihan ?? 0;
    $pot_lain = $selectedSlip->potongan_lain_lain ?? 0;
    $pot_cash = $selectedSlip->potongan_cash_advance ?? 0;
    $pot_cash_note = $selectedSlip->potongan_cash_advance_note ?? null;
    $totalPotongan = $pot_sakit + $pot_izin + $pot_alpa + $pot_tidak_aktif + $pot_kelebihan + $pot_lain + $pot_cash;
    $jumlahDibayarkan = $totalPendapatan - $totalPotongan;

    // ==================== PERBAIKAN PATH LOGO ====================
    $docRoot = $_SERVER['DOCUMENT_ROOT'];

    // Fungsi helper untuk convert gambar ke base64
    function getBase64Image($fullPath) {
        if (!file_exists($fullPath)) {
            return ''; // kalau file tidak ditemukan, kosongkan saja
        }
        $type = pathinfo($fullPath, PATHINFO_EXTENSION);
        $data = file_get_contents($fullPath);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    // Path Logo
    $logoLeftBase64   = getBase64Image($docRoot . '/template/img/logoslip.png');
    $logoCenterBase64 = getBase64Image($docRoot . '/template/img/logotulisan.png');
    $logoRightBase64  = getBase64Image($docRoot . '/template/img/jajal.png');
@endphp

    <table class="header-table">
        <tr>
            <td class="logo-left" style="width:10%; text-align:center; vertical-align:middle;">
                <img src="{{ $logoLeftBase64 }}" style="width:45px;" alt="Logo Left">
            </td>
            <td style="width:80%; text-align:center; vertical-align:middle;">
                <strong style="font-size:9pt;">SLIP PEMBAYARAN TUNJANGAN</strong><br>
                <strong style="font-size:7.5pt;">YAYASAN PENGEMBANGAN ANAK INDONESIA</strong><br>
                <img src="{{ $logoCenterBase64 }}" style="width:140px; margin:2px 0;" alt="Logo Center"><br>
                bimbingan <span style="color:red; font-size:7.5pt;">MINAT</span> Baca & belajar Anak
            </td>
            <td class="logo-right" style="width:10%; text-align:center; vertical-align:middle;">
                <img src="{{ $logoRightBase64 }}" style="width:45px;" alt="Logo Right">
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td>Nomor Induk</td>
            <td>:</td>
            <td>{{ $profile->nik ?? $selectedSlip->nik ?? '-' }}</td>
            <td>biMBA Unit</td>
            <td>:</td>
            <td>{{ $unitData->biMBA_unit ?? '-' }}</td>
        </tr>
        <tr>
            <td>Nama Staff</td>
            <td>:</td>
            <td>{{ $selectedSlip->nama ?? '-' }}</td>
            <td>Tanggal Masuk</td>
            <td>:</td>
            <td>
                {{
                    $tanggalMasuk
                        ? \Carbon\Carbon::parse($tanggalMasuk)->locale('id')->translatedFormat('d F Y')
                        : '-'
                }}
            </td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td>{{ $selectedSlip->jabatan ?? '-' }}</td>
            <td>Bulan</td>
            <td>:</td>
            <td>{{ $selectedBulanLabel ?? '-' }}</td>
        </tr>
    </table>

    <!-- Bagian tabel pendapatan dan potongan tetap sama seperti sebelumnya -->
    <div class="section">
        <div class="table-left">
            <table>
                <tr>
                    <th colspan="2">PENDAPATAN</th>
                </tr>
                @php $letters = range('a', 'z'); $i = 0; @endphp
                <tr><td>{{ $letters[$i++] }}. Tunjangan Pokok</td><td class="text-end">Rp {{ number_format($tunjanganPokok, 0, ',', '.') }}</td></tr>
                <tr><td>{{ $letters[$i++] }}. Tunjangan Harian</td><td class="text-end">Rp {{ number_format($tunjanganHarian, 0, ',', '.') }}</td></tr>
                <tr><td>{{ $letters[$i++] }}. Tunjangan Fungsional</td><td class="text-end">Rp {{ number_format($tunjanganFungsional, 0, ',', '.') }}</td></tr>
                <tr><td>{{ $letters[$i++] }}. Tunjangan Kesehatan</td><td class="text-end">Rp {{ number_format($tunjanganKesehatan, 0, ',', '.') }}</td></tr>
                <tr><td>{{ $letters[$i++] }}. Tunjangan Kerajinan</td><td class="text-end">Rp {{ number_format($selectedSlip->tunjangan_kerajinan ?? 0, 0, ',', '.') }}</td></tr>
                <tr><td>{{ $letters[$i++] }}. Komisi English biMBA</td><td class="text-end">Rp {{ number_format($selectedSlip->komisi_english ?? 0, 0, ',', '.') }}</td></tr>
                <tr><td>{{ $letters[$i++] }}. Komisi Mentor Magang</td><td class="text-end">Rp {{ number_format($selectedSlip->komisi_mentor ?? 0, 0, ',', '.') }}</td></tr>
                <tr><td>{{ $letters[$i++] }}. Kekurangan Tunjangan</td><td class="text-end">Rp {{ number_format($selectedSlip->kekurangan_tunjangan ?? 0, 0, ',', '.') }}</td></tr>
                <tr><td>{{ $letters[$i++] }}. Tunjangan Keluarga</td><td class="text-end">Rp {{ number_format($selectedSlip->tunjangan_keluarga ?? 0, 0, ',', '.') }}</td></tr>
                <tr><td>{{ $letters[$i++] }}. Lain-lain</td><td class="text-end">Rp {{ number_format($selectedSlip->lain_lain_pendapatan ?? 0, 0, ',', '.') }}</td></tr>
                <tr><th>Total Pendapatan</th><th class="text-end">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</th></tr>
                <tr><th>Jumlah Yang Dibayarkan</th><th class="text-end">Rp {{ number_format($jumlahDibayarkan, 0, ',', '.') }}</th></tr>
            </table>
        </div>

        <div class="table-right">
            <table>
                <tr>
                    <th colspan="2">POTONGAN</th>
                </tr>
                @php $letters2 = range('l', 'z'); $j = 0; @endphp
                <tr><td>{{ $letters2[$j++] }}. Sakit</td><td class="text-end">Rp {{ number_format($pot_sakit, 0, ',', '.') }}</td></tr>
                <tr><td>{{ $letters2[$j++] }}. Izin</td><td class="text-end">Rp {{ number_format($pot_izin, 0, ',', '.') }}</td></tr>
                <tr><td>{{ $letters2[$j++] }}. Alpa</td><td class="text-end">Rp {{ number_format($pot_alpa, 0, ',', '.') }}</td></tr>
                <tr><td>{{ $letters2[$j++] }}. Tidak Aktif</td><td class="text-end">Rp {{ number_format($pot_tidak_aktif, 0, ',', '.') }}</td></tr>
                <tr><td>{{ $letters2[$j++] }}. Kelebihan Tunjangan</td><td class="text-end">Rp {{ number_format($pot_kelebihan, 0, ',', '.') }}</td></tr>
                <tr><td>{{ $letters2[$j++] }}. Lain-lain</td><td class="text-end">Rp {{ number_format($pot_lain, 0, ',', '.') }}</td></tr>
                <tr><td>{{ $letters2[$j++] }}. Cash Advance</td><td class="text-end">Rp {{ number_format($pot_cash, 0, ',', '.') }}</td></tr>
                @if(!empty($pot_cash_note))
                    <tr>
                        <td>Catatan Cash Advance</td>
                        <td class="text-end" style="text-align:left; white-space:pre-wrap;">{{ $pot_cash_note }}</td>
                    </tr>
                @endif
                <tr><th>Total Potongan</th><th class="text-end">Rp {{ number_format($totalPotongan, 0, ',', '.') }}</th></tr>
            </table>

            <div class="keterangan">
                <strong>Keterangan:</strong>
                <ul style="margin: 0; padding-left: 10px;">
                    <li style="color: #0b75ffff; font-weight: bold;">Potongan Dengan Izin (Tunjangan Harian : 25 Hari Kerja)</li>
                    <li style="color: #0b75ffff; font-weight: bold;">Potongan Tanpa Izin (Take Home Pay : 25 Hari Kerja)</li>
                    <li style="color: #0b75ffff; font-weight: bold;">Periode Absensi tgl 26 bulan lalu s/d tgl 25 bulan selanjutnya</li>
                </ul>
            </div>
        </div>
    </div>

    <div style="text-align:right; font-size:7pt; margin-top:4mm;">
        Ditransfer ke: 
        <span style="color: #0b75ffff; font-weight: bold;">{{ $profile->bank ?? '-' }}</span> |
        <span style="color: #0b75ffff; font-weight: bold;">{{ $profile->no_rekening ?? '-' }}</span> |
        <span style="color: #0b75ffff; font-weight: bold;">{{ $profile->atas_nama ?? ($profile->nama ?? '-') }}</span> |
        <span>{{ $profile->email ?? '-' }}</span>
    </div>

    <table class="signature">
        <tr>
            <td>
                <div class="sig-title">Yang menyerahkan,</div>
                <div class="sig-space"></div>
                <div class="sig-line-text">__________________</div>
                <div class="sig-label">Kepala Unit</div>
            </td>
            <td>
                <div class="sig-title">Penerima,</div>
                <div class="sig-space"></div>
                <div class="sig-line-text">__________________</div>
                <div class="sig-label">Motivator</div>
            </td>
        </tr>
    </table>

</body>
</html>