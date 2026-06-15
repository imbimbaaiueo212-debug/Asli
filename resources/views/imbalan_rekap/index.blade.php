@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h3 class="mb-4 text-primary fw-bold">Rekapitulasi Imbalan Relawan</h3>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

        @php
    $defaultBulan = now()->subMonth()->format('m');
    $defaultTahun = now()->subMonth()->format('Y');

    $start_bulan = request('start_bulan', $defaultBulan);
    $start_tahun = request('start_tahun', $defaultTahun);
    $end_bulan   = request('end_bulan', $start_bulan);
    $end_tahun   = request('end_tahun', $start_tahun);

    try {
        $year  = (int)$start_tahun;
        $month = (int)$start_bulan;

        $startDate = \Carbon\Carbon::create($year, $month, 1)->subMonth()->day(26);
        $endDate   = \Carbon\Carbon::create($year, $month, 25);

        if ($start_bulan != $end_bulan || $start_tahun != $end_tahun) {
            $endDate = \Carbon\Carbon::create((int)$end_tahun, (int)$end_bulan, 25);
        }

        $periodeText = $startDate->locale('id')->translatedFormat('d F') . 
                       ' — ' . 
                       $endDate->locale('id')->translatedFormat('d F Y');
    } catch (\Exception $e) {
        $periodeText = 'Periode Tidak Valid';
    }
@endphp

    <!-- HEADER + FILTER -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">

                <!-- INFO PERIODE -->
                <div class="col-lg-5 mb-3 mb-lg-0">
                    <h5 class="mb-0 text-primary">
                        Periode Tampil: 
                        <strong class="fs-5">{{ $periodeText }}</strong>
                    </h5>
                </div>

                <!-- FORM FILTER -->
                <div class="col-lg-7">
                    <form method="GET" class="row g-3 align-items-end">

                        {{-- DARI BULAN --}}
                        <div class="col-lg-2 col-md-4 col-6">
                            <label class="small text-muted mb-1">Dari Bulan</label>
                            <select name="start_bulan" class="form-select form-select-sm" onchange="this.form.submit()">
                                @foreach([
                                    '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
                                    '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
                                    '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'
                                ] as $k => $v)
                                    <option value="{{ $k }}" {{ $start_bulan == $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- DARI TAHUN --}}
                        <div class="col-lg-1 col-md-2 col-6">
                            <label class="small text-muted mb-1">Tahun</label>
                            <select name="start_tahun" class="form-select form-select-sm" onchange="this.form.submit()">
                                @for($y = now()->year - 3; $y <= now()->year + 1; $y++)
                                    <option value="{{ $y }}" {{ $start_tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        {{-- SAMPAI BULAN --}}
                        <div class="col-lg-2 col-md-4 col-6">
                            <label class="small text-muted mb-1">Sampai Bulan</label>
                            <select name="end_bulan" class="form-select form-select-sm" onchange="this.form.submit()">
                                @foreach([
                                    '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
                                    '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
                                    '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'
                                ] as $k => $v)
                                    <option value="{{ $k }}" {{ $end_bulan == $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- SAMPAI TAHUN --}}
                        <div class="col-lg-1 col-md-2 col-6">
                            <label class="small text-muted mb-1">Tahun</label>
                            <select name="end_tahun" class="form-select form-select-sm" onchange="this.form.submit()">
                                @for($y = now()->year - 3; $y <= now()->year + 1; $y++)
                                    <option value="{{ $y }}" {{ $end_tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        {{-- FILTER UNIT – HANYA UNTUK ADMIN --}}
                        @if (auth()->check() && (auth()->user()->is_admin ?? false))
                            <div class="col-lg-3 col-md-6">
                                <label class="small text-muted mb-1">Unit biMBA</label>
                                <select name="bimba_unit" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">-- Semua Unit --</option>
                                    @foreach($unitOptions ?? [] as $unit)
                                        <option value="{{ $unit }}" {{ request('bimba_unit') == $unit ? 'selected' : '' }}>
                                            {{ $unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- FILTER NAMA --}}
                        <div class="col-lg-3 col-md-6">
                            <label class="small text-muted mb-1">Nama Relawan</label>
                            <select name="nama" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">-- Semua Nama --</option>
                                @foreach($namaOptions ?? [] as $nama)
                                    <option value="{{ $nama }}" {{ request('nama') == $nama ? 'selected' : '' }}>
                                        {{ $nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 text-end">
                            <a href="{{ route('imbalan_rekap.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- TOMBOL AKSI UTAMA -->
    @php
        $current = \Carbon\Carbon::createFromFormat('Y-m', "$start_tahun-$start_bulan");
        $prev    = $current->copy()->subMonth();
    @endphp

    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">

        <!-- Generate Bulan Sebelumnya -->
        <form action="{{ route('imbalan_rekap.generate') }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="start_bulan" value="{{ $prev->format('m') }}">
            <input type="hidden" name="start_tahun" value="{{ $prev->format('Y') }}">
            <button class="btn btn-warning btn-sm">
                <i class="bi bi-clock-history"></i> Generate Bulan Sebelumnya
            </button>
        </form>

        <!-- Generate Periode Ini -->
        <form action="{{ route('imbalan_rekap.generate') }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="start_bulan" value="{{ $start_bulan }}">
            <input type="hidden" name="start_tahun" value="{{ $start_tahun }}">
            <button class="btn btn-primary btn-sm">
                <i class="bi bi-arrow-repeat"></i> Generate Periode Ini
            </button>
        </form>

        <!-- Bayar Semua Periode Ini -->
        <form method="POST" action="{{ route('imbalan_rekap.bayar_periode') }}" class="d-inline ms-auto">
            @csrf
            <input type="hidden" name="start_bulan" value="{{ $start_bulan }}">
            <input type="hidden" name="start_tahun" value="{{ $start_tahun }}">
            <button class="btn btn-success btn-sm" onclick="return confirm('Yakin ingin tandai SEMUA relawan periode ini sebagai SUDAH DIBAYAR?')">
                <i class="bi bi-cash-stack"></i> Bayar Semua Periode Ini
            </button>
        </form>
    </div>

    <!-- INFO CICILAN -->
    <div class="alert alert-info small mb-4">
        <i class="bi bi-info-circle"></i>
        <strong>Cicilan Cash Advance bersifat manual.</strong> 
        Pilih angsuran di dropdown jika ingin dipotong bulan ini. Jika tidak dipilih, tidak ada potongan cicilan.
    </div>

    <!-- TABEL -->
    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                <table class="table table-sm table-bordered table-hover align-middle mb-0 text-nowrap" style="font-size: 0.8rem; min-width: 3200px;">
                    <thead>
                        <tr class="text-white fw-bold text-center">
                            <th colspan="3" class="bg-primary text-dark">PROFIL RELAWAN</th>
                            <th colspan="2" class="bg-info text-dark">LEMBUR</th>
                            <th colspan="8" class="bg-success text-dark">IMBALAN POKOK</th>
                            <th colspan="2" class="bg-warning text-dark">INSENTIF MENTOR</th>
                            <th colspan="2" class="bg-warning text-dark">TRANSPORT</th>
                            <th colspan="3" class="bg-danger text-dark">KEKURANGAN IMBALAN</th>
                            <th colspan="6" class="bg-success text-dark">KELEBIHAN IMBALAN</th>
                            <th colspan="6" class="bg-info text-dark">BAGI HASIL SPP</th>
                            <th colspan="2" class="bg-secondary text-dark">TOTAL DIBAYARKAN</th>
                            <th colspan="2" class="bg-dark">AKSI</th>
                        </tr>
                        <tr class="table-light text-center fw-bold">
                            <th>NIK</th>
                            <th>NAMA</th>
                            <th class="text-center" style="min-width: 140px;">INFO</th>
                             <th class="bg-info text-dark">LEMBUR</th>
                            <th class="bg-info text-dark">NOMINAL LEMBUR</th>
                            <th>WAKTU/MGG</th>
                            <th>WAKTU/BLN</th>
                            <th>DURASI</th>
                            <th>%</th>
                            <th>KTR</th>
                            <th>POKOK</th>
                            <th>LAINNYA</th>
                            <th>TOTAL</th>
                            <th>INSENTIF</th>
                            <th>KET. INSENTIF</th>
                            <th>TRANSPORT</th>
                            <th>@HARI</th>
                            <th>KEKURANGAN</th>
                            <th>BULAN</th>
                            <th>KET. KEKURANGAN</th>
                            <th>KELEBIHAN</th>
                            <th>BULAN</th>
                            <th>KET. KELEBIHAN</th>
                            <th>CICILAN</th>
                            <th>KET. CICILAN</th>
                            <th>TOTAL KELEBIHAN</th>
                            <th>MURID</th>
                            <th>SPP</th>
                            <th>KEKURANGAN</th>
                            <th>KELEBIHAN</th>
                            <th>BAGI HASIL</th>
                            <th>KET. BAGI HASIL</th>
                            <th>YANG DIBAYARKAN</th>
                            <th>CATATAN</th>
                            <th>STATUS BAYAR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $showAll = request()->has('all');
                            $rows = $showAll ? \App\Models\ImbalanRekap::orderBy('nama')->get() : ($rekaps ?? collect());
                        @endphp

                        @forelse($rows as $index => $r)
                            @php
                                $hasId = !is_null($r->id);
                                $formatRupiah = function($value) {
                                    if (!$value || $value == 0) return '-';
                                    $abs = abs($value);
                                    $formatted = 'Rp ' . number_format($abs, 0, ',', '.');
                                    return $value < 0 ? "<span class='text-danger'>($formatted)</span>" : $formatted;
                                };
                                $isDibayar = $r->status_pembayaran === 'dibayar';
                            @endphp
                            <tr data-id="{{ $hasId ? $r->id : '' }}" class="{{ $isDibayar ? 'table-success' : '' }}">
                                <td class="text-center fw-bold">
                                    {{ $r->profile?->nik ?? ($showAll ? $index + 1 : ($rekaps->firstItem() + $index ?? $index + 1)) }}
                                </td>
                                <td><strong>{{ $r->nama }}</strong></td>

                                <!-- KOLOM INFO -->
                                <td class="text-center">
                                    <button type="button" 
                                            class="btn btn-outline-info btn-sm info-pegawai-btn"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#infoModal"
                                            data-nama="{{ $r->nama ?? '-' }}"
                                            data-nik="{{ $r->profile?->nik ?? '-' }}"
                                            data-jabatan="{{ $r->posisi ?? '-' }}"
                                            data-status="{{ $r->status ?? '-' }}"
                                            data-departemen="{{ $r->departemen ?? '-' }}"
                                            data-unit="{{ $r->bimba_unit ?? '-' }}"
                                            data-cabang="{{ $r->no_cabang ?? '-' }}"
                                            data-masakerja="{{ $r->masa_kerja_formatted ?? '-' }}"
                                            data-bulan="{{ $r->bulan ?? '-' }}">
                                        <i class="bi bi-info-circle"></i> Info
                                    </button>
                                </td>

                                <!-- LEMBUR -->
                                <td class="text-center text-primary fw-bold">
                                    {{ $r->lembur_jam ?? 0 }} Jam
                                </td>
                                <td class="text-end text-primary fw-bold">
                                    Rp {{ number_format($r->lembur_nominal ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="text-center">{{ $r->waktu_mgg ?? '-' }}</td>
                                <td class="text-center">{{ $r->waktu_bln ?? '-' }}</td>
                                <td class="text-end">{{ $r->durasi_kerja ? number_format($r->durasi_kerja, 0, ',', '.') . ' Jam' : '-' }}</td>
                                <td class="text-center">{{ $r->persen ? number_format($r->persen, 2) . '%' : '-' }}</td>
                                <td class="text-center">{{ $r->ktr ?? '-' }}</td>
                                <td class="text-end text-success fw-bold">{!! $formatRupiah($r->imbalan_pokok) !!}</td>

                                <!-- LAINNYA -->
                                <td class="text-end position-relative">
    @if(auth()->check() && (auth()->user()->is_admin ?? false))
        <div class="d-flex align-items-center justify-content-end gap-2">
            <input type="text" class="form-control form-control-sm text-end inline-edit-input"
                value="{{ ($r->imbalan_lainnya ?? 0) > 0 ? number_format($r->imbalan_lainnya, 0, ',', '.') : '' }}"
                data-field="imbalan_lainnya" data-id="{{ $r->id }}"
                style="width:100px;" placeholder="0">
            <i class="bi bi-pencil text-primary edit-icon" title="Edit"></i>
            <span class="saving text-primary" style="display:none; font-size:0.7rem;">Saving...</span>
        </div>
    @else
        <span class="text-end d-block">{{ ($r->imbalan_lainnya ?? 0) > 0 ? 'Rp ' . number_format($r->imbalan_lainnya, 0, ',', '.') : '-' }}</span>
    @endif
</td>

                                <td class="text-end text-primary fw-bold bg-secondary-subtle" id="total_imbalan_{{ $r->id }}">
                                    {!! $formatRupiah($r->total_imbalan) !!}
                                </td>

                                <!-- Insentif Mentor -->
                                <!-- INSENTIF MENTOR -->
<td class="text-end position-relative">
    @if(auth()->check() && (auth()->user()->is_admin ?? false))
        <div class="d-flex align-items-center justify-content-end gap-2">
            <input type="text" class="form-control form-control-sm text-end inline-edit-input"
                value="{{ ($r->insentif_mentor ?? 0) > 0 ? number_format($r->insentif_mentor, 0, ',', '.') : '' }}"
                data-field="insentif_mentor" data-id="{{ $r->id }}"
                style="width:100px;" placeholder="0">
            <i class="bi bi-pencil text-primary edit-icon" title="Edit"></i>
            <span class="saving text-primary" style="display:none; font-size:0.7rem;">Saving...</span>
        </div>
    @else
        <span class="text-end d-block">{{ ($r->insentif_mentor ?? 0) > 0 ? 'Rp ' . number_format($r->insentif_mentor, 0, ',', '.') : '-' }}</span>
    @endif
</td>

<!-- KET. INSENTIF -->
<td class="position-relative">
    @if(auth()->check() && (auth()->user()->is_admin ?? false))
        <div class="d-flex align-items-center gap-2">
            <input type="text" class="form-control form-control-sm inline-edit-input"
                value="{{ $r->keterangan_insentif ?? '' }}" data-field="keterangan_insentif" data-id="{{ $r->id }}"
                style="width:130px;">
            <i class="bi bi-pencil text-primary edit-icon" title="Edit"></i>
            <span class="saving text-primary" style="display:none; font-size:0.7rem;">Saving...</span>
        </div>
    @else
        <span class="text-muted small">{{ Str::limit($r->keterangan_insentif ?? '-', 25) }}</span>
    @endif
</td>

                                <td class="text-end text-warning"><strong>{!! $formatRupiah($r->tambahan_transport) !!}</strong></td>

                                <!-- @HARI -->
<td class="text-center position-relative">
    @if(auth()->check() && (auth()->user()->is_admin ?? false))
        <div class="d-flex align-items-center justify-content-center gap-2">
            <input type="text" class="form-control form-control-sm text-center inline-edit-input"
                value="{{ $r->at_hari ?? '' }}" data-field="at_hari" data-id="{{ $r->id }}"
                style="width:70px;">
            <i class="bi bi-pencil text-primary edit-icon" title="Edit"></i>
            <span class="saving text-primary" style="display:none; font-size:0.7rem;">Saving...</span>
        </div>
    @else
        <span class="text-center d-block">{{ $r->at_hari ?? '-' }}</span>
    @endif
</td>

                                <!-- KEKURANGAN IMBALAN -->
<td class="text-end text-danger position-relative">
    @if(auth()->check() && (auth()->user()->is_admin ?? false))
        <div class="d-flex align-items-center justify-content-end gap-2">
            <input type="text" class="form-control form-control-sm text-end inline-edit-input"
                value="{{ ($r->kekurangan ?? 0) > 0 ? number_format($r->kekurangan, 0, ',', '.') : '' }}"
                data-field="kekurangan" data-id="{{ $r->id }}"
                style="width:100px;" placeholder="0">
            <i class="bi bi-pencil text-primary edit-icon" title="Edit Kekurangan Manual"></i>
            <span class="saving text-primary" style="display:none; font-size:0.7rem;">Saving...</span>
        </div>
    @else
        <span class="text-end d-block text-danger">
            @if(($r->kekurangan ?? 0) > 0)
                Rp {{ number_format($r->kekurangan, 0, ',', '.') }}
                @if(($r->total_kekurangan_adj ?? 0) > 0)
                    <small class="text-warning fw-bold">(+Adj)</small>
                @endif
            @else
                -
            @endif
        </span>
    @endif
</td>

<!-- BULAN KEKURANGAN -->
<td class="text-center">
    {{ $r->bulan_kekurangan_full ?? '-' }}
</td>

<!-- KETERANGAN KEKURANGAN -->
<td class="position-relative">
    <div class="d-flex align-items-center gap-1" 
         title="{{ 
             ($r->keterangan_kekurangan ?? '') . 
             (($r->keterangan_kekurangan && $r->keterangan_kekurangan_adj) ? ' | ' : '') .
             ($r->keterangan_kekurangan_adj ?? '')
         }}" 
         data-bs-toggle="tooltip">
        <i class="bi bi-info-circle text-primary small"></i>
        <span class="text-muted small text-truncate" style="max-width: 130px;">
            @php
                $ket = $r->keterangan_kekurangan ?? '';
                if ($r->keterangan_kekurangan_adj) {
                    $ket .= ($ket ? ' | ' : '') . $r->keterangan_kekurangan_adj;
                }
            @endphp
            {{ $ket ? Str::limit($ket, 22) : '-' }}
        </span>
        @if(($r->total_kekurangan_adj ?? 0) > 0)
            <span class="badge bg-warning text-dark px-1 py-0" style="font-size: 0.65rem;">ADJ</span>
        @endif
    </div>
</td>

                               <!-- KELEBIHAN -->
<td class="text-end text-success position-relative">
    @if(auth()->check() && (auth()->user()->is_admin ?? false))
        <div class="d-flex align-items-center justify-content-end gap-2">
            <input type="text" class="form-control form-control-sm text-end inline-edit-input"
                value="{{ ($r->kelebihan ?? 0) > 0 ? number_format($r->kelebihan, 0, ',', '.') : '' }}"
                data-field="kelebihan" data-id="{{ $r->id }}"
                style="width:100px;" placeholder="0">
            <i class="bi bi-pencil text-primary edit-icon" title="Edit"></i>
            <span class="saving text-primary" style="display:none; font-size:0.7rem;">Saving...</span>
        </div>
    @else
        <span class="text-end d-block text-success">{{ ($r->kelebihan ?? 0) > 0 ? 'Rp ' . number_format($r->kelebihan, 0, ',', '.') : '-' }}</span>
    @endif
</td>
                                <td class="text-center">
                                    {{ $r->bulan_kelebihan_full ?? '-' }}
                                </td>
                                <td class="position-relative">
                                    <div class="d-flex align-items-center gap-1" title="{{ $r->keterangan_kelebihan ?? 'Tidak ada keterangan' }}" data-bs-toggle="tooltip">
                                        <i class="bi bi-info-circle text-primary small"></i>
                                        <span class="text-muted small text-truncate" style="max-width: 110px;">
                                            {{ $r->keterangan_kelebihan ? Str::limit($r->keterangan_kelebihan, 18) : '-' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Cicilan -->
                                <td class="text-end text-warning position-relative">
    @if(auth()->check() && (auth()->user()->is_admin ?? false))
        <!-- Tampilan untuk ADMIN (bisa edit) -->
        <div class="d-flex align-items-center justify-content-end gap-2">
            @php
                $allInstallments = collect();
                $currentInstallmentId = $r->installment_id;

                if ($r->profile && $r->profile->nama) {
                    $allInstallments = \App\Models\CashAdvanceInstallment::join('cash_advances as ca', 'cash_advance_installments.cash_advance_id', '=', 'ca.id')
                        ->whereRaw('TRIM(UPPER(ca.nama)) = ?', [strtoupper(trim($r->profile->nama))])
                        ->select('cash_advance_installments.*')
                        ->orderBy('cash_advance_installments.cicilan_ke')
                        ->get();
                }
            @endphp

            @if($allInstallments->isNotEmpty() || $r->cicilan > 0)
                <select class="form-select form-select-sm inline-edit-cicilan"
                        data-id="{{ $r->id }}"
                        style="width:220px;">
                    <option value="">-- Pilih Cicilan --</option>
                    @foreach($allInstallments as $inst)
                        @php
                            $nominalMurni = (int) $inst->nominal_angsuran;
                            $ket = $inst->cicilan_ke;
                            $optionValue = $inst->id . '|' . $nominalMurni . '|' . $ket;

                            $isSelected = $currentInstallmentId && $currentInstallmentId == $inst->id;
                            $isLunas    = $inst->sudah_dibayar == 1;

                            $disabled = ($isLunas && !$isSelected) ? 'disabled' : '';
                            $lunasText = $isLunas ? ' (Lunas)' : '';
                            $optionStyle = ($isLunas && !$isSelected) ? 'color: #6c757d; font-style: italic;' : '';
                        @endphp
                        <option value="{{ $optionValue }}"
                                {{ $isSelected ? 'selected' : '' }}
                                {{ $disabled }}
                                style="{{ $optionStyle }}">
                            Rp {{ number_format($nominalMurni, 0, ',', '.') }} → Ke-{{ $ket }}{{ $lunasText }}
                        </option>
                    @endforeach
                </select>
            @else
                <span class="text-muted small">-</span>
            @endif

            <i class="bi bi-pencil text-primary edit-icon" title="Pilih cicilan"></i>
            <span class="saving text-primary" style="display:none; font-size:0.7rem;">Saving...</span>
        </div>

    @else
        <!-- Tampilan untuk USER BIASA (readonly / hanya teks) -->
        <span class="text-end d-block text-warning">
            @if($r->installment && $r->installment->nominal_angsuran)
                Rp {{ number_format($r->installment->nominal_angsuran, 0, ',', '.') }} 
                → Ke-{{ $r->installment->cicilan_ke ?? '-' }}
                @if($r->installment->sudah_dibayar == 1)
                    <span class="text-muted">(Lunas)</span>
                @endif
            @elseif($r->cicilan > 0)
                Rp {{ number_format($r->cicilan, 0, ',', '.') }}
            @else
                -
            @endif
        </span>
    @endif
</td>

                                <td class="position-relative">
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" class="form-control form-control-sm" 
                                               value="{{ $r->keterangan_cicilan ?? '' }}"
                                               readonly 
                                               style="width:180px; background:#f8f9fa; font-size:0.8rem;">
                                        <span class="text-muted small">Manual</span>
                                    </div>
                                </td>

                                <td class="text-end text-success fw-bold bg-secondary-subtle">
                                    {!! $formatRupiah($r->total_kelebihan) !!}
                                </td>

                                <!-- Bagi Hasil SPP -->
                                <td class="text-center">{{ $r->jumlah_murid ?? '-' }}</td>
                                <td class="text-end">{!! $formatRupiah($r->jumlah_spp) !!}</td>
                                <td class="text-end text-danger">{!! $formatRupiah($r->kekurangan_spp) !!}</td>
                                <td class="text-end text-success">{!! $formatRupiah($r->kelebihan_spp) !!}</td>
                                <td class="text-end">{!! $formatRupiah($r->jumlah_bagi_hasil) !!}</td>
                                <td>{{ $r->keterangan_bagi_hasil ?? '-' }}</td>

                                <!-- Yang Dibayarkan -->
                                <td class="text-end fw-bold bg-info-subtle {{ ($r->yang_dibayarkan ?? 0) < 0 ? 'text-danger' : 'text-success' }}"
                                    id="yang_dibayarkan_{{ $r->id }}">
                                    {!! $formatRupiah($r->yang_dibayarkan) !!}
                                </td>

                                <td class="position-relative">
                                    @php
                                        $filtered = null;
                                        if ($r->catatan) {
                                            $parts = explode(' | ', $r->catatan);
                                            $allowed = collect($parts)->filter(function ($p) {
                                                $p = strtolower(trim($p));
                                                return str_contains($p, 'izin') ||
                                                       str_contains($p, 'sakit') ||
                                                       str_contains($p, 'alpa') ||
                                                       str_contains($p, 'tidak aktif') ||
                                                       str_contains($p, 'pulang cepat');
                                            });
                                            $filtered = $allowed->implode(' | ');
                                        }
                                    @endphp

                                    @if($filtered)
                                        <div class="d-flex align-items-center gap-1"
                                            title="{{ $filtered }}"
                                            data-bs-toggle="tooltip">
                                            <i class="bi bi-chat-text text-primary small"></i>
                                            <span class="text-muted small text-truncate" style="max-width:200px;">
                                                {{ Str::limit($filtered, 50) }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <!-- AKSI -->
                                <td class="text-center">
                                    @if($isDibayar)
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="bi bi-check-circle-fill me-1"></i> Sudah Dibayar
                                        </span>
                                        @if($r->tanggal_dibayar)
                                            <div class="small text-muted mt-1">
                                                {{ $r->tanggal_dibayar->format('d/m/y H:i') }}
                                            </div>
                                        @endif
                                    @else
                                        <button class="btn btn-sm btn-outline-success btn-bayar-inline"
                                                data-id="{{ $r->id }}"
                                                data-nama="{{ addslashes($r->nama) }}"
                                                title="Tandai sudah dibayar periode ini">
                                            <i class="bi bi-cash-coin"></i> Bayar
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="36" class="text-center text-muted py-5">
                                    Tidak ada data untuk periode <strong>{{ $periodeText }}</strong><br>
                                    <small>Klik <strong>Generate Periode Ini</strong> untuk membuat data.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @unless(request('all'))
        <div class="mt-3 d-flex justify-content-center">
            {{ $rekaps->appends(request()->query())->links() ?? '' }}
        </div>
    @endunless
</div>

<!-- MODAL INFO PEGAWAI -->
<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold" id="infoModalLabel">Informasi Pegawai</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <small class="text-muted">Nama</small>
                        <h5 id="modal-nama" class="fw-bold mb-1"></h5>
                        <small class="text-muted">NIK</small>
                        <p id="modal-nik" class="fw-semibold text-primary mb-0"></p>
                    </div>
                    <div class="col-md-6"><small class="text-muted">Bulan</small><div id="modal-bulan" class="fw-semibold"></div></div>
                    <div class="col-md-6"><small class="text-muted">Jabatan</small><div id="modal-jabatan" class="fw-semibold"></div></div>
                    <div class="col-md-6"><small class="text-muted">Status</small><div id="modal-status" class="fw-semibold"></div></div>
                    <div class="col-md-6"><small class="text-muted">Departemen</small><div id="modal-departemen" class="fw-semibold"></div></div>
                    <div class="col-md-6"><small class="text-muted">Unit biMBA</small><div id="modal-unit" class="fw-semibold"></div></div>
                    <div class="col-md-6"><small class="text-muted">No. Cabang</small><div id="modal-cabang" class="fw-semibold"></div></div>
                    <div class="col-md-6"><small class="text-muted">Masa Kerja</small><div id="modal-masakerja" class="fw-semibold"></div></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
    .inline-edit-input { border: 1px solid #ced4da; background: #fff; transition: all 0.2s; }
    .inline-edit-input:focus { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,0.25); }
    .edit-icon:hover { opacity: 0.7; cursor: pointer; }
    .saving { font-weight: bold; }
    .btn-bayar-inline:hover { background-color: #198754; color: white !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Tooltip
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    if (!csrfToken) {
        console.error('CSRF token tidak ditemukan!');
        return;
    }

    const numericFields = ['imbalan_lainnya', 'insentif_mentor', 'kekurangan', 'kelebihan', 'at_hari'];

    const formatRupiah = (value) => {
        if (!value || value == 0) return '-';
        const abs = Math.abs(value);
        const formatted = 'Rp ' + abs.toLocaleString('id-ID');
        return value < 0 ? `<span class="text-danger">(${formatted})</span>` : formatted;
    };

    // Inline Edit Input
    document.querySelectorAll('.inline-edit-input').forEach(input => {
        const container = input.closest('td');
        const editIcon = container.querySelector('.edit-icon');
        const saving = container.querySelector('.saving');

        const save = () => {
            const recordId = input.dataset.id;
            if (!recordId) return;

            let value = input.value.trim();
            if (value === '' || value === '-') value = null;

            if (numericFields.includes(input.dataset.field) && value !== null) {
                value = parseInt(value.replace(/\./g, ''), 10) || 0;
            }

            if (saving) saving.style.display = 'inline';
            if (editIcon) editIcon.style.display = 'none';

            const formData = new FormData();
            formData.append('id', recordId);
            formData.append(input.dataset.field, value ?? '');
            formData.append('_token', csrfToken);

            fetch("{{ route('imbalan_rekap.update-inline') }}", {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.total_imbalan !== undefined) {
                        document.getElementById('total_imbalan_' + recordId).innerHTML = formatRupiah(data.total_imbalan);
                    }
                    if (data.yang_dibayarkan !== undefined) {
                        const el = document.getElementById('yang_dibayarkan_' + recordId);
                        if (el) {
                            el.innerHTML = formatRupiah(data.yang_dibayarkan);
                            el.className = `text-end fw-bold bg-info-subtle ${data.yang_dibayarkan < 0 ? 'text-danger' : 'text-success'}`;
                        }
                    }
                }
            })
            .catch(err => console.error('Error:', err))
            .finally(() => {
                if (saving) saving.style.display = 'none';
                if (editIcon) editIcon.style.display = 'inline';
            });
        };

        input.addEventListener('blur', save);
        input.addEventListener('keypress', e => e.key === 'Enter' && (e.preventDefault(), input.blur()));
    });

    // === HANDLER CICILAN (PENTING) ===
    document.querySelectorAll('.inline-edit-cicilan').forEach(select => {
        let previousValue = select.value;

        select.addEventListener('change', function () {
            const recordId = this.dataset.id;
            if (!recordId) return;

            const newVal = this.value;
            const container = this.parentElement;
            const saving = container.querySelector('.saving');
            const editIcon = container.querySelector('.edit-icon');

            if (saving) saving.style.display = 'inline';
            if (editIcon) editIcon.style.display = 'none';

            const formData = new FormData();
            formData.append('id', recordId);
            formData.append('_token', csrfToken);

            if (newVal === "") {
                formData.append('installment_id', '');
                formData.append('cicilan', 0);
                formData.append('keterangan_cicilan', '');
            } else {
                const parts = newVal.split('|');
                formData.append('installment_id', parts[0]);
                formData.append('cicilan', parts[1] || 0);
                formData.append('keterangan_cicilan', 'Cicilan Cash Advance | ' + (parts[2] || ''));
            }

            fetch("{{ route('imbalan_rekap.update-inline') }}", {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const elBayar = document.getElementById('yang_dibayarkan_' + recordId);
                    if (elBayar && data.yang_dibayarkan !== undefined) {
                        elBayar.innerHTML = formatRupiah(data.yang_dibayarkan);
                        elBayar.className = `text-end fw-bold bg-info-subtle ${data.yang_dibayarkan < 0 ? 'text-danger' : 'text-success'}`;
                    }

                    const inputKet = this.closest('tr').querySelector('input[readonly]');
                    if (inputKet) inputKet.value = data.keterangan_cicilan || '';
                } else {
                    alert(data.message || 'Gagal menyimpan cicilan');
                    this.value = previousValue;
                }
            })
            .catch(() => {
                alert('Terjadi kesalahan saat menyimpan cicilan');
                this.value = previousValue;
            })
            .finally(() => {
                if (saving) saving.style.display = 'none';
                if (editIcon) editIcon.style.display = 'inline';
            });

            previousValue = newVal;
        });
    });

    // Tombol Bayar Inline
    document.querySelectorAll('.btn-bayar-inline').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const nama = this.dataset.nama;

            if (!confirm(`Tandai ${nama} sebagai SUDAH DIBAYAR untuk periode ini?`)) return;

            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="bi bi-hourglass-split"></i> Proses...';
            this.disabled = true;

            fetch("{{ route('imbalan_rekap.bayar-single') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ id: id })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const td = this.closest('td');
                    td.innerHTML = `
                        <span class="badge bg-success px-3 py-2">
                            <i class="bi bi-check-circle-fill me-1"></i> Sudah Dibayar
                        </span>
                        <div class="small text-muted mt-1">${new Date().toLocaleString('id-ID', {day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'})}</div>
                    `;
                    td.closest('tr').classList.add('table-success');
                } else {
                    alert(data.message || 'Gagal menandai pembayaran');
                    this.innerHTML = originalHTML;
                    this.disabled = false;
                }
            })
            .catch(() => {
                alert('Terjadi kesalahan');
                this.innerHTML = originalHTML;
                this.disabled = false;
            });
        });
    });

    // Modal Info
    const infoModal = document.getElementById('infoModal');
    if (infoModal) {
        infoModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('modal-nama').textContent     = button.getAttribute('data-nama') || '-';
            document.getElementById('modal-nik').textContent      = button.getAttribute('data-nik') || '-';
            document.getElementById('modal-bulan').textContent    = button.getAttribute('data-bulan') || '-';
            document.getElementById('modal-jabatan').textContent  = button.getAttribute('data-jabatan') || '-';
            document.getElementById('modal-status').textContent   = button.getAttribute('data-status') || '-';
            document.getElementById('modal-departemen').textContent = button.getAttribute('data-departemen') || '-';
            document.getElementById('modal-unit').textContent     = button.getAttribute('data-unit') || '-';
            document.getElementById('modal-cabang').textContent   = button.getAttribute('data-cabang') || '-';
            document.getElementById('modal-masakerja').textContent = button.getAttribute('data-masakerja') || '-';
        });
    }
});
</script>
@endsection