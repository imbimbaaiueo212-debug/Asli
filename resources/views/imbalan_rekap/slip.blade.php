@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Slip Imbalan {{ $rekap->nama ?? '' }}</title>
    <style>
        :root{
            --bg: #ffffff;
            --muted: #7f8c8d;
            --accent: #0b75ff;
            --card-border: #e6e6e6;
            --success-bg: #dff0d8;
            --yellow-bg: #fff4c2;
        }
        html,body{margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#222;-webkit-font-smoothing:antialiased}
        .wrap{max-width:1024px;margin:20px auto;padding:16px}
        .card{background:var(--bg);border:1px solid var(--card-border);border-radius:12px;padding:18px;box-shadow:0 6px 18px rgba(15,15,15,0.03);overflow:hidden}
        .header{display:flex;align-items:center;gap:12px;justify-content:space-between;margin-bottom:18px}
        .logo{width:82px;flex:0 0 82px;text-align:center}
        .title{flex:1;text-align:center}
        .title h1{margin:0;font-size:18px;letter-spacing:0.2px;color:#2c3e50}
        .title p{margin:6px 0 0;font-size:12px;color:var(--muted)}
        .info-block{background:#f8f9fa;border:1px solid var(--card-border);border-radius:10px;padding:14px 16px;margin-bottom:18px}
        .info-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:8px;align-items:center}
        .info-grid .label{font-size:13px;color:#444}
        .info-grid .value{font-weight:700;font-size:13px}
        .cols{display:flex;gap:24px;align-items:flex-start}
        .col{flex:1;min-width:0}
        .left .meta-table{margin-bottom:12px;line-height:1.8}
        .left .meta-table td{vertical-align:middle;padding:4px 6px;font-size:13px}
        .left .meta-table td.label{width:40%}
        .left .meta-table td.value{width:60%}
        .table-wrap{overflow:auto;border-radius:8px;margin-bottom:16px}
        table.data{width:100%;border-collapse:collapse;font-size:13px}
        table.data th, table.data td{padding:10px 12px;text-align:left}
        table.data th{background:#f3f3f3;font-weight:700}
        table.data td.right{text-align:right}
        .total-row th, .total-row td{background:#fff4c2;font-weight:700;}
        .highlight{background:var(--yellow-bg);padding:12px 18px;border-radius:10px;font-weight:900;font-size:18px;display:inline-block}
        .signature-row{display:flex;justify-content:space-between;margin-top:32px;width:100%;}
        .sig{text-align:center;width:45%;font-size:14px}
        .sig .line{height:70px;display:block}
        .actions{margin-top:18px;text-align:center}
        .btn{display:inline-block;padding:10px 18px;border-radius:6px;margin:0 6px;cursor:pointer;text-decoration:none}
        .btn-primary{background:#0b75ff;color:#fff;border:none}
        .btn-success{background:#28a745;color:#fff;border:none}
        .btn-secondary{background:#6c757d;color:#fff;border:none}
        @media (max-width:900px){.info-grid{grid-template-columns:repeat(3,1fr)}.cols{flex-direction:column}}
        @media (max-width:560px){.info-grid{grid-template-columns:repeat(2,1fr)}table.data th,table.data td{padding:8px}}
        @media print{body{background:#fff}.actions,.no-print{display:none}.card{box-shadow:none;border:0;padding:0}.wrap{margin:0}}
        @media (max-width:600px){.logo-right{display:none}}
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        @php
            // Helper format rupiah
            $rupiah = fn($v) => $v > 0 ? number_format($v, 0, ',', '.') : '-';

            // HITUNG ULANG TOTAL PENDAPATAN YANG BENAR (ini yang penting!)
            $pokok          = $rekap->imbalan_pokok ?? 0;
            $lainnya        = $rekap->imbalan_lainnya ?? 0;
            $insentif       = $rekap->insentif_mentor ?? 0;
            $transport      = $rekap->tambahan_transport ?? 0;
            $kekurangan     = $rekap->kekurangan ?? 0; // ini tambahan yang dibayarkan bulan ini

            $totalPendapatan = $pokok + $lainnya + $insentif + $transport + $kekurangan;

            // Total potongan (dari tabel potongan_tunjangan)
            $totalPotongan = 0;
            if($potongan) {
                $totalPotongan = ($potongan->sakit ?? 0) + ($potongan->izin ?? 0) + ($potongan->alpa ?? 0) +
                                 ($potongan->tidak_aktif ?? 0) + ($potongan->cash_advance_nominal ?? 0) + ($potongan->lainnya ?? 0);
            }

            // YANG DIBAYARKAN = Total Pendapatan - Total Potongan
            $yangDibayarkan = $totalPendapatan - $totalPotongan;

            $tanggalMasuk = $tanggalMasukFormatted 
                ?? ($profile?->tgl_masuk ? \Carbon\Carbon::parse($profile->tgl_masuk)->translatedFormat('d F Y') : '-');
        @endphp

        {{-- Header --}}
        <div class="header">
            <div class="logo logo-left">
                <img src="{{ asset('template/img/logoslip.png') }}" alt="logo" style="max-width:72px;height:auto">
            </div>
            <div class="title">
                <h1>SLIP IMBALAN RELAWAN biMBA AIUEO</h1>
                <p>{{ $periode }}</p>
            </div>
            <div class="logo logo-right">
                <img src="{{ asset('template/img/jajal.png') }}" alt="logo-right" style="max-width:72px;height:auto">
            </div>
        </div>

        {{-- Info block --}}
        <div class="info-block">
            <div class="info-grid">
                <div class="label">NIK</div>
                <div class="value">{{ $profile->nik ?? $rekap->nomor_induk ?? '-' }}</div>
                <div class="label">Unit</div>
                <div class="value">{{ $unit ?? $rekap->unit ?? 'biMBA AUEO' }}</div>
                <div class="label">Nama Relawan</div>
                <div class="value">
                    <select id="select-rekap" style="padding:6px 8px;border-radius:6px;border:1px solid #ccc;min-width:200px;">
                        <option value="">{{ $rekap->nama ?? '-- Pilih Nama --' }}</option>
                        @foreach($allRekaps as $r)
                            <option value="{{ $r->id }}" @if($r->id == $rekap->id) selected @endif>{{ $r->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="label">Tgl Masuk</div>
                <div class="value">{{ $tanggalMasuk }}</div>
                <div class="label">Posisi</div>
                <div class="value">{{ $rekap->posisi ?? ($profile?->jabatan ?? '-') }}</div>
                <div class="label">Bulan</div>
                <div class="value">
                    <select id="select-periode" style="padding:6px 8px;border-radius:6px;border:1px solid #ccc;min-width:180px;">
                        @foreach($periodeOptions as $opt)
                            <option value="{{ $opt['value'] }}" @if(($periodeValue ?? '') === $opt['value']) selected @endif>
                                {{ $opt['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Main columns --}}
        <div class="cols" style="margin-top:18px">
            {{-- LEFT: PENDAPATAN --}}
            <div class="col left">
                <div class="meta-table" style="margin-bottom:12px">
                    <table style="width:100%">
                        <tr><td class="label">Waktu /Mgg</td><td class="value"><strong>{{ $rekap->waktu_mgg ?? '-' }}</strong></td></tr>
                        <tr><td class="label">Waktu /Bln</td><td class="value"><strong>{{ $rekap->waktu_bln ?? '-' }}</strong></td></tr>
                        <tr><td class="label">Kategori</td><td class="value">{{ $rekap->ktr ?? ($rekap->status ?? '-') }}</td></tr>
                        <tr><td class="label">Imbalan Pokok</td><td class="value">Rp {{ $rupiah($pokok) }}</td></tr>
                        <tr><td class="label">Durasi Kerja</td><td class="value">{{ $rekap->durasi_kerja ?? $masaKerja ?? '-' }}</td></tr>
                        <tr><td class="label">Kehadiran</td><td class="value">{{ $rekap->persen ?? '-' }} %</td></tr>
                    </table>
                </div>

                <div class="table-wrap">
                    <table class="data">
                        <thead><tr><th colspan="2">PENDAPATAN</th></tr></thead>
                        <tbody>
                            <tr><td>Imbalan Pokok</td><td class="right">Rp {{ $rupiah($pokok) }}</td></tr>
                            <tr><td>Imbalan Lainnya</td><td class="right">Rp {{ $rupiah($lainnya) }}</td></tr>
                            <tr><td>Insentif Mentor</td><td class="right">Rp {{ $rupiah($insentif) }}</td></tr>
                            <tr><td>Tambahan Transport</td><td class="right">Rp {{ $rupiah($transport) }}</td></tr>
                            @if($kekurangan > 0)
                                <tr style="background:#e8f5e9;"><td>Kekurangan Imbalan (Dibayar)</td><td class="right text-success fw-bold">Rp {{ $rupiah($kekurangan) }}</td></tr>
                            @endif
                            <tr class="total-row">
                                <th>Total Pendapatan</th>
                                <th class="right">Rp {{ $rupiah($totalPendapatan) }}</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- RIGHT: POTONGAN + TOTAL DIBAYARKAN --}}
            <div class="col right">
                <div class="table-wrap">
                    <table class="data">
                        <thead><tr><th colspan="2">POTONGAN</th></tr></thead>
                        <tbody>
                            @if($totalPotongan > 0)
                                @if($potongan?->sakit > 0)      <tr><td>Potongan Sakit</td><td class="right text-danger">- Rp {{ $rupiah($potongan->sakit) }}</td></tr> @endif
                                @if($potongan?->izin > 0)       <tr><td>Potongan Izin</td><td class="right text-danger">- Rp {{ $rupiah($potongan->izin) }}</td></tr> @endif
                                @if($potongan?->alpa > 0)       <tr><td>Potongan Alpa</td><td class="right text-danger">- Rp {{ $rupiah($potongan->alpa) }}</td></tr> @endif
                                @if($potongan?->tidak_aktif > 0)<tr><td>Tidak Aktif</td><td class="right text-danger">- Rp {{ $rupiah($potongan->tidak_aktif) }}</td></tr> @endif
                                @if($potongan?->cash_advance_nominal > 0)<tr><td>Cash Advance</td><td class="right text-danger">- Rp {{ $rupiah($potongan->cash_advance_nominal) }}</td></tr> @endif
                                @if($potongan?->lainnya > 0)    <tr><td>Lain-lain</td><td class="right text-danger">- Rp {{ $rupiah($potongan->lainnya) }}</td></tr> @endif
                                <tr class="total-row">
                                    <th>Total Potongan</th>
                                    <th class="right text-danger">- Rp {{ $rupiah($totalPotongan) }}</th>
                                </tr>
                            @else
                                <tr><td colspan="2" class="text-center text-muted">Tidak ada potongan</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- TOTAL YANG DIBAYARKAN --}}
                <div style="background:#f8f9fa;padding:16px;border-radius:10px;text-align:center;margin-bottom:16px;">
                    <div style="font-size:16px;font-weight:700;margin-bottom:8px;">Total yang dibayarkan</div>
                    <div class="highlight">Rp {{ $rupiah($yangDibayarkan) }}</div>
                </div>

                {{-- Info Rekening --}}
                <div style="background:#f0f0f0;padding:14px;border-radius:8px;font-size:13px;">
                    <table style="width:100%">
                        <tr><td style="width:36%">No Rekening</td><td><strong>{{ $noRekening ?? '-' }}</strong></td></tr>
                        <tr><td>Bank</td><td><strong>{{ $bank ?? '-' }}</strong></td></tr>
                        <tr><td>Atas Nama</td><td><strong>{{ $atasNama ?? '-' }}</strong></td></tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Signature --}}
        <div class="signature-row">
            <div class="sig">
                Yang Menyerahkan<br>
                <span class="line"></span>
                __________________
            </div>
            <div class="sig">
                Penerima<br>
                <span class="line"></span>
                {{ $rekap->nama }}
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="actions no-print">
        <button class="btn btn-primary" onclick="window.print()">Cetak</button>
        <a href="{{ route('imbalan_rekap.pdf', $rekap->id) }}?periode={{ $periodeValue ?? '' }}" target="_blank" class="btn btn-success">Download PDF</a>
        <a href="{{ route('imbalan_rekap.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<script>
    (function () {
        const selNama = document.getElementById('select-rekap');
        const selPeriode = document.getElementById('select-periode');
        const urlTemplate = "{{ route('imbalan_rekap.slip', ':id') }}";

        function go(id, periode) {
            if (!id) return;
            let url = urlTemplate.replace(':id', id);
            if (periode) url += '?periode=' + periode;
            location.href = url;
        }

        selNama?.addEventListener('change', () => go(selNama.value, selPeriode?.value));
        selPeriode?.addEventListener('change', () => go(selNama?.value || "{{ $rekap->id }}", selPeriode.value));
    })();
</script>
</body>
</html>
@endsection