@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Slip Imbalan {{ optional($rekap)->nama ?? '' }}</title>
    <style>
        :root{
            --bg: #ffffff;
            --muted: #7f8c8d;
            --accent: #0b75ff;
            --card-border: #e6e6e6;
            --yellow-bg: #fff4c2;
            --red-light: #ffebee;
            --red-lighter: #ffcdd2;
        }
        html,body{margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#222;-webkit-font-smoothing:antialiased}
        .wrap{max-width:1024px;margin:40px auto;padding:20px;position:relative}
        .card{background:var(--bg);border:1px solid var(--card-border);border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(15,15,15,0.06);overflow:hidden;position:relative}

        /* Watermark Logo */
        .card::before{
            content:"";
            position:absolute;
            top:50%; left:50%;
            transform:translate(-50%,-50%);
            width:680px; height:680px;
            opacity:0.12; pointer-events:none; z-index:0;
            background:url('{{ asset("template/img/terakhir.png") }}') no-repeat center center;
            background-size:150% auto;
        }
        @media print{.card::before{display:none}}

        .header{display:flex;align-items:center;gap:16px;justify-content:space-between;margin-bottom:24px;position:relative;z-index:1}
        .logo{width:90px;flex:0 0 90px;text-align:center}
        .title{flex:1;text-align:center}
        .title h1{margin:0;font-size:20px;letter-spacing:0.3px;color:#2c3e50}
        .title p{margin:8px 0 0;font-size:13px;color:var(--muted)}

        .info-block{background:#f8f9fa;border:1px solid var(--card-border);border-radius:12px;padding:16px;margin-bottom:20px;position:relative;z-index:1}
        .info-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:10px;align-items:center}
        .info-grid .label{font-size:13.5px;color:#444;font-weight:600}
        .info-grid .value{font-weight:700;font-size:13.5px}

        .cols{display:flex;gap:30px;align-items:flex-start;position:relative;z-index:1}
        .col{flex:1;min-width:0}

        .meta-table{margin-bottom:16px;line-height:1.9}
        .meta-table td{vertical-align:middle;padding:5px 8px;font-size:13.5px}
        .meta-table td.label{width:40%;color:#555}
        .meta-table td.value{width:60%}

        .table-wrap{overflow:auto;border-radius:10px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.05)}
        table.data{width:100%;border-collapse:collapse;font-size:13.5px}
        table.data th, table.data td{padding:12px 14px;text-align:left;vertical-align:middle}
        table.data th{background:#eef2f6;font-weight:700;color:#2c3e50}
        table.data td.right{text-align:right;font-weight:600}
        .total-row th, .total-row td{background:var(--yellow-bg);font-weight:700;color:#333}
        .section-header{background:#ffebee;font-weight:bold;padding:12px 14px;text-align:left}

        .highlight{background:var(--yellow-bg);padding:16px 24px;border-radius:12px;font-weight:900;font-size:20px;display:inline-block;box-shadow:0 4px 10px rgba(0,0,0,0.08)}

        .signature-row{display:flex;justify-content:space-between;margin-top:40px;width:100%;position:relative;z-index:1}
        .sig{text-align:center;width:45%;font-size:14.5px;font-weight:600}
        .sig .line{height:80px;display:block}

        .actions{margin-top:24px;text-align:center;position:relative;z-index:1}
        .btn{display:inline-block;padding:12px 24px;border-radius:8px;margin:0 8px;cursor:pointer;text-decoration:none;font-weight:600;transition:transform 0.2s}
        .btn:hover{transform:translateY(-2px)}
        .btn-primary{background:#0b75ff;color:#fff;box-shadow:0 4px 10px rgba(11,117,255,0.3)}
        .btn-success{background:#28a745;color:#fff;box-shadow:0 4px 10px rgba(40,167,69,0.3)}
        .btn-secondary{background:#6c757d;color:#fff}

        @media (max-width:900px){.info-grid{grid-template-columns:repeat(3,1fr)}.cols{flex-direction:column}}
        @media (max-width:560px){.info-grid{grid-template-columns:repeat(2,1fr)}table.data th,table.data td{padding:10px}}
        @media (max-width:600px){.logo-right{display:none}}
        @media print{
            body{background:#fff}
            .actions,.no-print{display:none}
            .card{box-shadow:none;border:0;padding:0;margin:0}
            .wrap{margin:0;padding:20px}
            .highlight{font-size:22px !important}
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        @php
    $rupiah = fn($v) => $v > 0 ? number_format($v, 0, ',', '.') : '-';

    $pokok      = $rekap?->imbalan_pokok ?? 0;
    $lainnya    = $rekap?->imbalan_lainnya ?? 0;
    $insentif   = $rekap?->insentif_mentor ?? 0;
    $transport  = $rekap?->tambahan_transport ?? 0;
    $kekurangan = $rekap?->kekurangan ?? 0;

    // LEMBUR
    $totalJamLembur     = $totalJamLembur ?? 0;
    $totalNominalLembur = $totalNominalLembur ?? 0;

    // TOTAL PENDAPATAN (WAJIB menyertakan lembur)
    $totalPendapatan = $pokok + $lainnya + $insentif + $transport 
                       + $kekurangan 
                       + ($totalKekuranganAdj ?? 0) 
                       + $totalNominalLembur;   // ← Pastikan ini ada!

    $totalPotonganTetap = 0;
    if (!empty($potongan)) {
        $totalPotonganTetap += ($potongan->sakit ?? 0)
                             + ($potongan->izin ?? 0)
                             + ($potongan->alpa ?? 0)
                             + ($potongan->tidak_aktif ?? 0)
                             + ($potongan->cash_advance_nominal ?? 0)
                             + ($potongan->lainnya ?? 0);
    }

    $totalPotongan = $totalPotonganTetap 
                     + ($totalCicilan ?? 0) 
                     + ($totalKelebihanAdj ?? 0);

    $yangDibayarkan = $totalPendapatan 
                      + ($rekap->jumlah_bagi_hasil ?? 0) 
                      - ($rekap->kelebihan ?? 0) 
                      - ($rekap->cicilan ?? 0);

    $tanggalMasuk = $profile?->tgl_masuk
        ? \Carbon\Carbon::parse($profile->tgl_masuk)->translatedFormat('d F Y')
        : '-';
@endphp

        <!-- Header -->
        <div class="header">
            <div class="logo logo-left">
                <img src="{{ asset('template/img/logoslip.png') }}" alt="Logo" style="max-width:72px;height:auto">
            </div>
            <div class="title">
                <h1>SLIP IMBALAN RELAWAN biMBA AIUEO</h1>
                <p>{{ $periode ?? '-' }}</p>
            </div>
            <div class="logo logo-right">
                <img src="{{ asset('template/img/jajal.png') }}" alt="Logo Kanan" style="max-width:72px;height:auto">
            </div>
        </div>

        <!-- Info Karyawan -->
        <div class="info-block">
            <div class="info-grid">

                <!-- NIK -->
                <div class="label">NIK</div>
                <div class="value">
                    {{ $profile?->nik ?? optional($rekap)->nomor_induk ?? '-' }}
                </div>

                <!-- UNIT -->
                <div class="label">Unit</div>
                <div class="value">
                    @if($isAdmin)
                        <select id="select-unit" style="padding:6px 8px;border-radius:6px;border:1px solid #ccc;min-width:200px;">
                            <option value="">-- Semua Unit --</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}"
                                    {{ request('unit_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->biMBA_unit }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <strong>{{ $unit ?? 'biMBA AIUEO' }}</strong>
                    @endif
                </div>

                <!-- NAMA RELAWAN -->
                <div class="label">Nama Relawan</div>
                <div class="value">
                    <select id="select-nama" style="padding:6px 8px;border-radius:6px;border:1px solid #ccc;min-width:200px;">
                        <option value="">-- Pilih Nama --</option>
                        <!-- akan diisi oleh JavaScript -->
                    </select>
                </div>

                <!-- Tanggal Masuk -->
                <div class="label">Tgl Masuk</div>
                <div class="value">{{ $tanggalMasuk }}</div>

                <!-- Posisi -->
                <div class="label">Posisi</div>
                <div class="value">
                    {{ optional($rekap)->posisi ?? ($profile?->jabatan ?? '-') }}
                </div>

                <!-- Bulan -->
                <div class="label">Bulan</div>
                <div class="value">
                    <select id="select-periode" style="padding:6px 8px;border-radius:6px;border:1px solid #ccc;min-width:180px;">
                        @foreach($periodeOptions as $opt)
                            <option value="{{ $opt['value'] }}"
                                {{ ($periodeValue ?? '') === $opt['value'] ? 'selected' : '' }}>
                                {{ $opt['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        @if(!$rekap)
            <div style="padding:18px;background:#fff4c2;border-radius:8px;margin:20px 0;text-align:center;">
                <strong>Tidak ada data untuk periode / pilihan yang dipilih.</strong>
            </div>
        @endif

        <!-- Kolom Pendapatan & Potongan -->
        <div class="cols" style="margin-top:18px">
            <!-- Kiri: Pendapatan -->
            <div class="col left">
                <div class="meta-table">
                    <table style="width:100%">
                        <tr>
<tr>
    <td class="label">Status Relawan</td>
    <td class="value">
        <strong class="{{ strtolower($statusKaryawan ?? $rekap->status ?? 'Aktif') === 'magang' ? 'text-danger' : 'text-success' }}">
            {{ $statusKaryawan ?? $rekap->status ?? $rekap->status_karyawan ?? 'Aktif' }}
        </strong>
    </td>
</tr>
                        <tr><td class="label">Waktu /Mgg</td><td class="value"><strong>{{ optional($rekap)->waktu_mgg ?? '-' }}</strong></td></tr>
                        <tr><td class="label">Waktu /Bln</td><td class="value"><strong>{{ optional($rekap)->waktu_bln ?? '-' }}</strong></td></tr>
                        <tr><td class="label">Kategori</td><td class="value">{{ $rekap->ktr ?? optional($rekap)->status ?? '-' }}</td></tr>
                        <tr><td class="label">Imbalan Pokok</td><td class="value">Rp {{ $rupiah($pokok) }}</td></tr>
                        <tr><td class="label">Durasi Kerja</td><td class="value">{{ optional($rekap)->durasi_kerja ?? $masaKerja ?? '-' }}</td></tr>
                        <tr><td class="label">Kehadiran</td><td class="value">{{ optional($rekap)->persen ?? '-' }} %</td></tr>
                    </table>
                </div>

                <div class="table-wrap">
    <table class="data">
        <thead><tr><th colspan="2">PENDAPATAN</th></tr></thead>
        <tbody>
    <!-- Baris standar -->
    <tr>
        <td>Imbalan Pokok</td>
        <td class="text-center"></td>
        <td class="text-end">Rp {{ $rupiah($pokok) }}</td>
    </tr>
    <tr>
        <td>Imbalan Lainnya</td>
        <td class="text-center"></td>
        <td class="text-end">Rp {{ $rupiah($lainnya) }}</td>
    </tr>
    <tr>
        <td>Insentif Mentor</td>
        <td class="text-center"></td>
        <td class="text-end">Rp {{ $rupiah($insentif) }}</td>
    </tr>

    <!-- Tambahan Transport -->
    @if($transport > 0)
    <tr style="background:#e8f5e9;">
        <td>Tambahan Transport</td>
        <td class="text-center text-success font-weight-bold">
            @️{{ $rekap->at_hari ?? 0 }} Hari
        </td>
        <td class="text-end text-success font-weight-bold">
            Rp {{ $rupiah($transport) }}
        </td>
    </tr>
    @endif

    <!-- Lembur -->
    @if($totalJamLembur > 0)
    <tr style="background:#e8f5e9;">
        <td>Lembur</td>
        <td class="text-center text-success font-weight-bold">
            @️{{ number_format($totalJamLembur, 1, '.', '.') }} Jam
        </td>
        <td class="text-end text-success font-weight-bold">
            Rp {{ $rupiah($totalNominalLembur) }}
        </td>
    </tr>
    @endif

    <!-- Tambahan dari Adjustment -->
    @if($totalKekuranganAdj > 0)
    <tr style="background:#e8f5e9;">
        <td>Tambahan Dibayar (Adjustment)</td>
        <td class="text-center"></td>
        <td class="text-end text-success font-weight-bold">
            Rp {{ $rupiah($totalKekuranganAdj) }}
        </td>
    </tr>
    @endif

    <!-- Kekurangan Imbalan -->
    @if($kekurangan > 0)
    <tr style="background:#e8f5e9;">
        <td>Kekurangan Imbalan (Dibayar)</td>
        <td class="text-center"></td>
        <td class="text-end text-success font-weight-bold">
            Rp {{ $rupiah($kekurangan) }}
        </td>
    </tr>
    @endif

    <!-- Total Pendapatan -->
    <tr class="total-row" style="font-weight:bold; background:#f2f2f2;">
        <td>Total Pendapatan</td>
        <td class="text-center"></td>
        <td class="text-end">Rp {{ $rupiah($totalPendapatan) }}</td>
    </tr>
</tbody>
    </table>
</div>
            </div>

            <!-- Kanan: Potongan -->
<div class="col right">
    <div class="table-wrap">
        <table class="data">
            <thead><tr><th colspan="2">POTONGAN</th></tr></thead>
            <tbody>
                @php $tarifHarian = 24000; @endphp

                <!-- Potongan Tetap -->
                @if($totalPotonganTetap > 0)
                    @if($potongan?->sakit ?? 0 > 0)
                        <tr><td>Sakit</td><td class="right text-danger">{{ intval($potongan->sakit / $tarifHarian) }} Hari</td></tr>
                    @endif
                    @if($potongan?->izin ?? 0 > 0)
                        <tr><td>Izin</td><td class="right text-danger">{{ intval($potongan->izin / $tarifHarian) }} Hari</td></tr>
                    @endif
                    @if($potongan?->alpa ?? 0 > 0)
                        <tr><td>Alpa</td><td class="right text-danger">{{ intval($potongan->alpa / $tarifHarian) }} Hari</td></tr>
                    @endif
                    @if($potongan?->tidak_aktif ?? 0 > 0)
                        <tr><td>Tidak Aktif</td><td class="right text-danger">- Rp {{ $rupiah($potongan->tidak_aktif) }}</td></tr>
                    @endif
                    @if($potongan?->cash_advance_nominal ?? 0 > 0)
                        <tr><td>Cash Advance</td><td class="right text-danger">- Rp {{ $rupiah($potongan->cash_advance_nominal) }}</td></tr>
                    @endif
                    @if($potongan?->lainnya ?? 0 > 0)
                        <tr><td>Lain-lain</td><td class="right text-danger">- Rp {{ $rupiah($potongan->lainnya) }}</td></tr>
                    @endif
                @endif

                <!-- Cicilan Cash Advance -->
                @if($cicilan->isNotEmpty())
                    <tr class="section-header">CICILAN CASH ADVANCE</tr>
                    @foreach($cicilan as $c)
                        <tr style="background:var(--red-lighter);">
                            <td>• {{ $c->keterangan }}</td>
                            <td class="right text-danger"><strong>- Rp {{ $rupiah($c->jumlah) }}</strong></td>
                        </tr>
                    @endforeach
                @endif

                <!-- Potongan dari Adjustment ("potongan" → kelebihan) -->
                @if($totalKelebihanAdj > 0)
                    <tr style="background:var(--red-lighter);">
                        <td>Potongan Adjustment</td>
                        <td class="right text-danger fw-bold">- Rp {{ number_format($totalKelebihanAdj, 0, ',', '.') }}</td>
                    </tr>
                @endif

                <!-- TOTAL -->
                <tr class="total-row">
                    <th>Total Potongan</th>
                    <th class="right text-danger">- Rp {{ $rupiah($totalPotongan) }}</th>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- KETERANGAN ADJUSTMENT -->
    @if($keteranganKekuranganAdj || $keteranganKelebihanAdj)
        <div class="mt-2 p-2 bg-light rounded border small">
            <strong>Keterangan Adjustment:</strong><br>
            @if($keteranganKekuranganAdj)
                <div class="text-success" title="{{ $keteranganKekuranganAdj }}">Tambahan Dibayar: {{ Str::limit($keteranganKekuranganAdj, 50) }}</div>
            @endif
            @if($keteranganKelebihanAdj)
                <div class="text-danger" title="{{ $keteranganKelebihanAdj }}">Potongan Adjustment: {{ Str::limit($keteranganKelebihanAdj, 50) }}</div>
            @endif
        </div>
    @endif

    <!-- Total yang Dibayarkan -->
    <div style="background:#f8f9fa;padding:20px;border-radius:12px;text-align:center;margin-top:20px;">
        <div style="font-size:16px;font-weight:700;margin-bottom:10px;">Total yang dibayarkan</div>
        <div class="highlight">Rp {{ $rupiah($yangDibayarkan) }}</div>
    </div>

    <!-- Info Rekening -->
    <div style="background:#f0f0f0;padding:16px;border-radius:12px;font-size:13.5px;margin-top:20px;">
        <table style="width:100%">
            <tr><td style="width:36%;font-weight:600">No Rekening</td><td><strong>{{ $noRekening ?? '-' }}</strong></td></tr>
            <tr><td style="font-weight:600">Bank</td><td><strong>{{ $bank ?? '-' }}</strong></td></tr>
            <tr><td style="font-weight:600">Atas Nama</td><td><strong>{{ $atasNama ?? '-' }}</strong></td></tr>
        </table>
    </div>
</div>
        </div>

        <!-- Tanda Tangan -->
        <div class="signature-row">
            <div class="sig">
                Yang Menyerahkan<br>
                <span class="line"></span>
                Infinite Management
            </div>
            <div class="sig">
                Penerima<br>
                <span class="line"></span>
                {{ optional($rekap)->nama ?? '-' }}
            </div>
        </div>
    </div>

    <!-- Tombol Aksi -->
    <div class="actions no-print">
        @if($rekap)
            <a href="{{ route('imbalan_rekap.pdf', $rekap->id) }}?periode={{ $periodeValue ?? '' }}" target="_blank" class="btn btn-success">Cetak PDF</a>
        @endif
        <a href="{{ route('imbalan_rekap.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<script>
(function () {
    const selUnit    = document.getElementById('select-unit');
    const selNama    = document.getElementById('select-nama');
    const selPeriode = document.getElementById('select-periode');

    const API_URL = '{{ route("imbalan_rekap.relawan_filter") }}';  // pastikan route ini sudah ada

    function loadNamaOptions(autoSelect = true) {
        const periode = selPeriode?.value?.trim();
        const unitId  = selUnit?.value?.trim() || '';

        if (!periode) {
            selNama.innerHTML = '<option value="">-- Pilih Bulan Terlebih Dahulu --</option>';
            selNama.disabled = true;
            return;
        }

        selNama.innerHTML = '<option value="">Memuat nama relawan...</option>';
        selNama.disabled = true;

        const params = new URLSearchParams({ periode });
        if (unitId) params.append('unit_id', unitId);

        fetch(`${API_URL}?${params.toString()}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => {
            if (!res.ok) throw new Error('Gagal memuat data');
            return res.json();
        })
        .then(data => {
            selNama.innerHTML = '<option value="">-- Pilih Nama Relawan --</option>';

            if (!data || data.length === 0) {
                selNama.innerHTML += '<option value="" disabled>Tidak ada data relawan untuk periode ini</option>';
            } else {
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.nama;
                    selNama.appendChild(opt);
                });
            }

            selNama.disabled = false;

            // Auto select jika ada rekap_id di URL (hanya saat load awal)
            if (autoSelect) {
                const urlParams = new URLSearchParams(window.location.search);
                const currentId = urlParams.get('rekap_id');
                if (currentId) selNama.value = currentId;
            }
        })
        .catch(err => {
            console.error(err);
            selNama.innerHTML = '<option value="">Gagal memuat nama</option>';
            selNama.disabled = false;
        });
    }

    function reloadWithParams() {
        const params = new URLSearchParams();

        const unit   = selUnit?.value?.trim();
        const nama   = selNama?.value?.trim();
        const periode = selPeriode?.value?.trim();

        if (unit)    params.set('unit_id', unit);
        if (nama)    params.set('rekap_id', nama);
        if (periode) params.set('periode', periode);

        const query = params.toString();
        window.location.href = window.location.pathname + (query ? '?' + query : '');
    }

    // Event listeners
    if (selPeriode) {
        selPeriode.addEventListener('change', () => {
            loadNamaOptions(false);   // update dropdown tanpa reload
            // reloadWithParams();    // uncomment jika ingin reload saat ganti bulan
        });
    }

    if (selUnit) {
        selUnit.addEventListener('change', () => {
            loadNamaOptions(false);
            // reloadWithParams();   // uncomment jika ingin reload saat ganti unit
        });
    }

    if (selNama) {
        selNama.addEventListener('change', () => {
            reloadWithParams();       // reload halaman saat pilih nama
        });
    }

    // Load awal
    if (selPeriode?.value) {
        loadNamaOptions(true);
    }

})();
</script>

</body>
</html>
@endsection