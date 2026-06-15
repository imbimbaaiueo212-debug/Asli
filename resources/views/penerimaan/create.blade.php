@extends('layouts.app')

@section('title', 'Tambah Data Penerimaan')

@section('content')
<div class="container">
    <h4 class="mb-3">Tambah Data Penerimaan</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('penerimaan.store') }}" method="POST" id="form-penerimaan" enctype="multipart/form-data">
        @csrf

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">NIM <span class="text-danger">*</span></label>
                <select name="nim" id="nimSelect" class="form-select" required>
                    <option value="" disabled selected>-- Pilih NIM --</option>
                    @foreach ($murids as $murid)
                        @php 
                            $nimWithZero = str_pad($murid->nim, 9, '0', STR_PAD_LEFT);
                        @endphp
                        <option value="{{ $nimWithZero }}"
                                data-nama="{{ $murid->nama ?? '' }}"
                                data-kelas="{{ $murid->kelas ?? '' }}"
                                data-gol="{{ $murid->gol ?? '' }}"
                                data-kd="{{ $murid->kd ?? '' }}"
                                data-status="{{ $murid->status ?? 'Aktif' }}"
                                data-guru="{{ $murid->guru ?? '' }}"
                                data-spp="{{ $murid->spp ?? 0 }}"
                                data-bimba_unit="{{ $murid->bimba_unit ?? '' }}"
                                data-no_cabang="{{ $murid->no_cabang ?? '' }}">
                            {{ $nimWithZero }} - {{ $murid->nama ?? 'Nama tidak ada' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-5">
                <label class="form-label">Nama Murid</label>
                <input type="text" id="namaMuridInput" name="nama_murid" class="form-control" readonly required>
            </div>

            <div class="col-md-2">
                <label class="form-label">Kelas</label>
                <input type="text" id="kelasInput" name="kelas" class="form-control" readonly>
            </div>

            <div class="col-md-1">
                <label class="form-label">Gol</label>
                <input type="text" id="golInput" name="gol" class="form-control" readonly>
            </div>

            <div class="col-md-1">
                <label class="form-label">KD</label>
                <input type="text" id="kdInput" name="kd" class="form-control" readonly>
            </div>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <input type="text" id="statusInput" name="status" class="form-control" readonly>
            </div>

            <div class="col-md-4">
                <label class="form-label">Guru</label>
                <input type="text" id="guruInput" name="guru" class="form-control" readonly>
            </div>

            <div class="col-md-4">
                <label class="form-label">Nilai SPP / Bulan</label>
                <input type="text" id="nilai_spp" class="form-control text-end fw-bold" readonly>
            </div>
        </div>

        {{-- BIMBA UNIT & NO CABANG – HANYA TAMPIL JIKA ADMIN --}}
        @if (auth()->user()->isAdminUser())
            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <label class="form-label">Bimba Unit</label>
                    <input type="text" id="bimbaUnitInput" name="bimba_unit" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">No Cabang</label>
                    <input type="text" id="noCabangInput" name="no_cabang" class="form-control">
                </div>
            </div>
        @else
            {{-- Non-admin: simpan otomatis via hidden (tidak muncul di tampilan) --}}
            <input type="hidden" id="bimbaUnitInput" name="bimba_unit">
            <input type="hidden" id="noCabangInput" name="no_cabang">
        @endif

        <hr class="my-4">

        <h5 class="text-primary">Informasi Pembayaran</h5>
        <div class="row g-3">
            <div class="col-md-6 col-lg-4">
                <label class="form-label fw-bold">Preview Nomor Kwitansi</label>
                <div class="bg-light p-3 rounded border text-center mb-2">
                    <div class="fs-4 fw-bold" id="kwitansi-preview">Pilih NIM terlebih dahulu</div>
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="manual_kwitansi_toggle" name="manual_kwitansi">
                    <label class="form-check-label" for="manual_kwitansi_toggle">
                        Gunakan nomor kwitansi manual
                    </label>
                </div>

                <div id="manual_kwitansi_input" style="display:none;">
                    <input type="text" name="kwitansi_base_manual" id="kwitansi_base_manual" 
                           class="form-control" placeholder="Contoh: KW20260114 atau KW-ABC-001" value="">
                    <small class="text-muted d-block mt-1">
                        Akan otomatis ditambah -01, -02, dst
                    </small>
                </div>

                <small class="text-muted d-block mt-2">
                    Format otomatis: KW[3digitNIM][YY][MM][DD][NN]  
                    Contoh: KW00126011401
                </small>
            </div>

            <div class="col-md-3">
                <label class="form-label">Via Pembayaran</label>
                <select name="via" id="via" class="form-select" required>
                    <option value="" disabled selected>-- Pilih --</option>
                    <option value="cash">Cash</option>
                    <option value="transfer">Transfer</option>
                   
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Tanggal Bayar</label>
                <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-md-6" id="bukti_transfer_container">
                <label class="form-label">Bukti Transfer <span class="text-danger">*</span></label>
                <input type="file" name="bukti_transfer" id="bukti_transfer" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                <small class="text-muted">Maks. 5 MB</small>
            </div>
        </div>

        <hr class="my-4">

        <h5 class="text-primary">Pembayaran SPP</h5>
        <div class="row g-3">
            <!--Voucher-->
            <div class="col-md-4 mt-3">
                <label class="form-label">Voucher (bisa multi)</label>
                <select id="voucher" name="voucher[]" class="form-select" multiple>
                    <option value="">-- Tidak pakai voucher --</option>
                    @foreach ($vouchers as $v)
                        <option value="{{ $v->no_voucher ?? $v->id }}" data-nominal="50000">
                            {{ $v->no_voucher ?? 'VCHR-'.$v->id }} - Rp 50.000 (sisa: {{ $v->jumlah_voucher }})
                        </option>
                    @endforeach
                </select>
            </div>
            <!--End-->

            <div class="col-md-4">
                <label class="form-label text-danger fw-bold">
                    Jumlah Bulan <span class="text-danger">*</span>
                </label>
                <select name="spp" id="spp_dropdown" class="form-select fs-5 fw-bold text-start">
                    <option value="">-- Pilih jumlah bulan --</option>
                </select>
                <small id="spp_info" class="text-muted">Harga per bulan muncul setelah pilih murid</small>
            </div>

            <div class="col-md-8">
                <label class="form-label text-success fw-bold">
                    Untuk Bulan Tahun <span class="text-danger">*</span>
                </label>
                <div id="bulan-container">
                    <div class="d-flex gap-2 mb-2 align-items-center bulan-row">
                        <select name="bulan_bayar[]" class="form-select" style="width:180px;">
                            <option value="">Pilih Bulan</option>
                            @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $b)
                                <option value="{{ $b }}">{{ $b }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="tahun_bayar[]" class="form-control" style="width:100px;" value="{{ date('Y') }}" min="2020">
                        <button type="button" class="btn btn-success btn-sm btn-add-remove">Tambah</button>
                    </div>
                </div>
                

                <button type="button" id="tambah-bulan-lagi" class="btn btn-link text-primary p-0 mt-1">
                    + Tambah bulan lain
                </button>

                <div id="info-bulan" class="mt-3 p-3 bg-light rounded small"></div>
                 <div class="col-md-3">
                    <label class="form-label fw-bold text-primary">TOTAL SPP</label>
                    <input type="text"
                        id="total_spp"
                        class="form-control bg-success text-end fs-4 fw-bold text-white"
                        readonly
                        value="0">
                </div>
            </div>
            
        </div>

        <hr class="my-4">

            {{-- ================= BIAYA DAFTAR ================= --}}
            <hr class="my-4">

            <h5 class="text-primary">Biaya Daftar</h5>

            <div class="row g-3 mb-4">

    <!-- ==================== PILIH KELAS ==================== -->
    <div class="col-md-5">
        <label class="form-label fw-bold">Kelas</label>
        <select name="daftar_kode" id="daftar_select" class="form-select">
            <option value="">-- Pilih Kelas --</option>
            @foreach($daftarList as $item)
                <option value="{{ $item['kode'] }}" 
                        data-harga-duafa="{{ $item['harga_duafa'] }}"
                        data-harga-promo="{{ $item['harga_promo'] }}"
                        data-harga-daftar="{{ $item['harga_daftar'] }}"
                        data-harga-spesial="{{ $item['harga_spesial'] }}"
                        data-harga-umum1="{{ $item['harga_umum1'] }}"
                        data-harga-promo_umum="{{ $item['harga_promo_umum'] }}">
                    {{ $item['nama'] }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- ==================== PILIH JENIS HARGA ==================== -->
    <div class="col-md-4">
        <label class="form-label fw-bold">Jenis Harga</label>
        <select name="daftar_tipe_harga" id="daftar_tipe_harga" class="form-select">
            <option value="">-- Pilih Jenis Harga --</option>
            <option value="harga_daftar">Daftar Ulang</option>
            <option value="harga_duafa">Dhuafa</option>
            <option value="harga_promo">Promo Khusus</option>
            <option value="harga_spesial">Spesial</option>
            <option value="harga_umum1">Umum 1</option>
            <option value="harga_umum2">Promo Gratis</option>
            <option value="harga_promo_umum">Promo Umum</option>
        </select>
    </div>

    <!-- ==================== TOTAL BIAYA DAFTAR ==================== -->
    <div class="col-md-3">
        <label class="form-label fw-bold text-primary">Total Biaya Daftar</label>
        <input type="text"
            id="total_daftar"
            class="form-control text-end bg-success fw-bold text-white fs-5"
            readonly
            value="0">
    </div>

</div>

<input type="hidden" name="daftar" id="daftar_hidden" value="0">

            <hr class="my-4">

            {{-- ================= BIAYA LAIN-LAIN ================= --}}
            <h5>Biaya Lain-lain</h5>

            <div class="row g-3">

                {{-- HAPUS BLOK BIAYA DAFTAR YANG LAMA DARI SINI --}}

                <!-- KAOS PENDEK -->
            <div class="col-md-4">
                <label class="form-label">Kaos Pendek</label>
                <div id="kaos-pendek-container">
                    <!-- Baris pertama default -->
                    <div class="kaos-pendek-row d-flex gap-2 mb-2 align-items-end">
                        <select name="kaos_pendek_kode[]" class="form-select kaos-pendek-select" style="width: 60%;">
                            <option value="">-- Pilih Ukuran --</option>
                            @foreach($kaosPendekList as $kaos)
                                <option value="{{ $kaos['kode'] }}" 
                                        data-harga="{{ $kaos['harga'] }}">
                                    {{ $kaos['kode'] }}
                                </option>
                            @endforeach
                        </select>
                        <input type="number" name="kaos_pendek_qty[]" class="form-control kaos-pendek-qty" value="0" min="0" style="width: 80px;">
                        <button type="button" class="btn btn-success btn-sm btn-add-kaos-pendek">Tambah</button>
                    </div>
                </div>
                <input type="hidden" name="kaos_pendek" id="kaos_pendek_hidden" value="0">
                <div id="ukuran-pendek-container" class="mt-2"></div>
            </div>

            <!-- KAOS PANJANG -->
            <div class="col-md-4">
                <label class="form-label">Kaos Panjang (Lengan Panjang)</label>
                <div id="kaos-panjang-container">
                    <div class="kaos-panjang-row d-flex gap-2 mb-2 align-items-end">
                        <select name="kaos_panjang_kode[]" class="form-select kaos-panjang-select" style="width: 60%;">
                            <option value="">-- Pilih Ukuran --</option>
                            @foreach($kaosPanjangList as $kaos)
                                <option value="{{ $kaos['kode'] }}" 
                                        data-harga="{{ $kaos['harga'] }}">
                                    {{ $kaos['kode'] }}
                                </option>
                            @endforeach
                        </select>
                        <input type="number" name="kaos_panjang_qty[]" class="form-control kaos-panjang-qty" value="0" min="0" style="width: 80px;">
                        <button type="button" class="btn btn-success btn-sm btn-add-kaos-panjang">Tambah</button>
                    </div>
                </div>
                <input type="hidden" name="kaos_panjang" id="kaos_panjang_hidden" value="0">
                <div id="ukuran-panjang-container" class="mt-2"></div>
            </div>
            <!--- End --->

           <!-- ==================== KPK ==================== -->
            <div class="col-md-4">
                <label class="form-label">KPK</label>
                <div id="kpk-container">
                    <!-- Baris pertama default -->
                    <div class="kpk-row d-flex gap-2 mb-2 align-items-end">
                        <select name="kpk_kode[]" class="form-select kpk-select" style="width: 75%;">
                            <option value="">-- Pilih Qty/Rp. --</option>
                            @foreach($kpkList as $kpk)
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $kpk['kode'] }}" 
                                            data-harga="{{ $kpk['harga'] }}"
                                            data-qty="{{ $i }}">
                                        {{ $i }} × {{ $kpk['kode'] }} = Rp {{ number_format($kpk['harga'] * $i, 0, ',', '.') }}
                                    </option>
                                @endfor
                            @endforeach
                        </select>
                        <!--<button type="button" class="btn btn-success btn-sm btn-add-kpk">Tambah</button>-->
                    </div>
                </div>

                <input type="hidden" name="kpk" id="kpk_hidden" value="0">
                <div id="kpk-info" class="mt-2 small text-success"></div>
            </div>
            <!-- ==================== TAS ==================== -->
            <div class="col-md-4">
                <label class="form-label">Tas</label>
                <div id="tas-container">
                    <!-- Baris pertama default -->
                    <div class="tas-row d-flex gap-2 mb-2 align-items-end">
                        <select name="tas_kode[]" class="form-select tas-select" style="width: 75%;">
                            <option value="">-- Pilih Qty/Rp. --</option>
                            @foreach($tasList as $tas)
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $tas['kode'] }}" 
                                            data-harga="{{ $tas['harga'] }}"
                                            data-qty="{{ $i }}">
                                        {{ $i }} × {{ $tas['kode'] }} = Rp {{ number_format($tas['harga'] * $i, 0, ',', '.') }}
                                    </option>
                                @endfor
                            @endforeach
                        </select>
                        <!--<button type="button" class="btn btn-success btn-sm btn-add-tas">Tambah</button>-->
                    </div>
                </div>

                <input type="hidden" name="tas" id="tas_hidden" value="0">
                <div id="tas-info" class="mt-2 small text-success"></div>
            </div>

            <!-- ==================== SERTIFIKAT ==================== -->
            <div class="col-md-4">
                <label class="form-label">Sertifikat</label>
                <div id="sertifikat-container">
                    <!-- Baris pertama default -->
                    <div class="sertifikat-row d-flex gap-2 mb-2 align-items-end">
                        <select name="sertifikat_kode[]" class="form-select sertifikat-select" style="width: 75%;">
                            <option value="">-- Pilih Qty/Rp. --</option>
                            @foreach($sertifikatList as $sertifikat)
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $sertifikat['kode'] }}" 
                                            data-harga="{{ $sertifikat['harga'] }}"
                                            data-qty="{{ $i }}">
                                        {{ $i }} × {{ $sertifikat['kode'] }} = Rp {{ number_format($sertifikat['harga'] * $i, 0, ',', '.') }}
                                    </option>
                                @endfor
                            @endforeach
                        </select>
                        <!--<button type="button" class="btn btn-success btn-sm btn-add-sertifikat">Tambah</button>-->
                    </div>
                </div>

                <input type="hidden" name="sertifikat" id="sertifikat_hidden" value="0">
                <div id="sertifikat-info" class="mt-2 small text-success"></div>
            </div>

            <!-- ==================== STPB ==================== -->
            <div class="col-md-4">
                <label class="form-label">STPB</label>
                <div id="stpb-container">
                    <!-- Baris pertama default -->
                    <div class="stpb-row d-flex gap-2 mb-2 align-items-end">
                        <select name="stpb_kode[]" class="form-select stpb-select" style="width: 75%;">
                            <option value="">-- Pilih Qty/Rp. --</option>
                            @foreach($stpbList as $stpb)
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $stpb['kode'] }}" 
                                            data-harga="{{ $stpb['harga'] }}"
                                            data-qty="{{ $i }}">
                                        {{ $i }} × {{ $stpb['kode'] }} = Rp {{ number_format($stpb['harga'] * $i, 0, ',', '.') }}
                                    </option>
                                @endfor
                            @endforeach
                        </select>
                        <!--<button type="button" class="btn btn-success btn-sm btn-add-stpb">Tambah</button>-->
                    </div>
                </div>

                <input type="hidden" name="stpb" id="stpb_hidden" value="0">
                <div id="stpb-info" class="mt-2 small text-success"></div>
            </div>

            <!-- ==================== RBAS ==================== -->
            <div class="col-md-4">
                <label class="form-label">RBAS</label>
                <div id="rbas-container">
                    <!-- Baris pertama default -->
                    <div class="rbas-row d-flex gap-2 mb-2 align-items-end">
                        <select name="rbas_kode[]" class="form-select rbas-select" style="width: 75%;">
                            <option value="">-- Pilih Qty/Rp. --</option>
                            @foreach($rbasList as $rbas)
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $rbas['kode'] }}" 
                                            data-harga="{{ $rbas['harga'] }}"
                                            data-qty="{{ $i }}">
                                        {{ $i }} × {{ $rbas['kode'] }} = Rp {{ number_format($rbas['harga'] * $i, 0, ',', '.') }}
                                    </option>
                                @endfor
                            @endforeach
                        </select>
                        <!--<button type="button" class="btn btn-success btn-sm btn-add-rbas">Tambah</button>-->
                    </div>
                </div>

                <input type="hidden" name="RBAS" id="rbas_hidden" value="0">
                <div id="rbas-info" class="mt-2 small text-success"></div>
            </div>

            <!-- ==================== BCABS01 ==================== -->
            <div class="col-md-4">
                <label class="form-label">BCABS01</label>
                <div id="bcabs01-container">
                    <!-- Baris pertama default -->
                    <div class="bcabs01-row d-flex gap-2 mb-2 align-items-end">
                        <select name="bcabs01_kode[]" class="form-select bcabs01-select" style="width: 75%;">
                            <option value="">-- Pilih Qty/Rp. --</option>
                            @foreach($bcabs01List as $item)
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $item['kode'] }}" 
                                            data-harga="{{ $item['harga'] }}"
                                            data-qty="{{ $i }}">
                                        {{ $i }} × {{ $item['kode'] }} = Rp {{ number_format($item['harga'] * $i, 0, ',', '.') }}
                                    </option>
                                @endfor
                            @endforeach
                        </select>
                        <!--<button type="button" class="btn btn-success btn-sm btn-add-bcabs01">Tambah</button>-->
                    </div>
                </div>

                <input type="hidden" name="BCABS01" id="bcabs01_hidden" value="0">
                <div id="bcabs01-info" class="mt-2 small text-success"></div>
            </div>

            <!-- ==================== BCABS02 ==================== -->
            <div class="col-md-4">
                <label class="form-label">BCABS02</label>
                <div id="bcabs02-container">
                    <!-- Baris pertama default -->
                    <div class="bcabs02-row d-flex gap-2 mb-2 align-items-end">
                        <select name="bcabs02_kode[]" class="form-select bcabs02-select" style="width: 75%;">
                            <option value="">-- Pilih Qty/Rp. --</option>
                            @foreach($bcabs02List as $item)
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $item['kode'] }}" 
                                            data-harga="{{ $item['harga'] }}"
                                            data-qty="{{ $i }}">
                                        {{ $i }} × {{ $item['kode'] }} = Rp {{ number_format($item['harga'] * $i, 0, ',', '.') }}
                                    </option>
                                @endfor
                            @endforeach
                        </select>
                        <!--<button type="button" class="btn btn-success btn-sm btn-add-bcabs02">Tambah</button>-->
                    </div>
                </div>

                <input type="hidden" name="BCABS02" id="bcabs02_hidden" value="0">
                <div id="bcabs02-info" class="mt-2 small text-success"></div>
            </div>          
            
             <div class="col-md-3">
                <label class="form-label">Event</label>
                <input type="text" name="event" class="form-control biaya-lain text-end" value="">
            </div>
            <div class="col-md-3">
                <label class="form-label">Lain-lain</label>
                <input type="text" name="lain_lain" class="form-control biaya-lain text-end" value="0">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold text-primary">TOTAL LAIN-LAIN</label>
                <input type="text"
                    id="total_lain"
                    class="form-control text-end bg-success fw-bold text-white"
                    readonly
                    value="0">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold text-danger">GRAND TOTAL</label>
                <input type="text"
                    id="grand_total"
                    class="form-control bg-warning text-end fs-4 fw-bold"
                    readonly
                    value="0">
            </div>
        </div>

        <div class="mt-5 text-end">
            <button type="submit" class="btn btn-success btn-lg px-5">Simpan Pembayaran</button>
            <a href="{{ route('penerimaan.index') }}" class="btn btn-outline-secondary btn-lg px-5 ms-3">Kembali</a>
        </div>
    </form>
</div>

<!-- Dependencies -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    let sppPerBulan = 0;
    let currentNimLast3 = '';
    let currentTanggal = '{{ date('Y-m-d') }}';
    let isManualMode = false;

    // ────────────────────────────────────────────────
    // Fungsi Bantu
    // ────────────────────────────────────────────────
    function formatRupiah(angka) {
        if (!angka) return '0';
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function unformatRupiah(str) {
        return parseInt((str || '0').replace(/\./g, '')) || 0;
    }

    function generateKwitansiBase() {
        if (!currentNimLast3) return null;
        const tgl = new Date(currentTanggal);
        if (isNaN(tgl.getTime())) return null;

        const yy = String(tgl.getFullYear()).slice(-2).padStart(2, '0');
        const mm = String(tgl.getMonth() + 1).padStart(2, '0');
        const dd = String(tgl.getDate()).padStart(2, '0');

        return `KW${currentNimLast3}${yy}${mm}${dd}`;
    }

        // ================================================
        // HANDLE SHOW/HIDE BUKTI TRANSFER
        // ================================================
        function toggleBuktiTransfer() {
            const via = $('#via').val();
            
            if (via === 'transfer') {
                $('#bukti_transfer_container').slideDown(250);
            } else {
                $('#bukti_transfer_container').slideUp(250);
                $('#bukti_transfer').val(''); // reset file input
            }
        }

        // Event Listener Via Pembayaran
        $('#via').on('change', toggleBuktiTransfer);

    // ================================================
    // UPDATE KWITANSI PREVIEW (VERSI BARU - SUDAH DIGABUNG)
    // ================================================
    function updateKwitansiPreview() {
        const jumlahBulan = $('.bulan-row').length;
        const sppValue = parseInt($('#spp_dropdown').val()) || 0;
        
        const totalPendek  = unformatRupiah($('#kaos_pendek_hidden').val());
        const totalPanjang = unformatRupiah($('#kaos_panjang_hidden').val());
        
        // Cek apakah ada biaya lain (daftar, kaos, kpk, tas, dll)
        let adaBiayaLain = false;
        
        // Biaya yang bisa digabung
        if (unformatRupiah($('#daftar_hidden').val()) > 0) adaBiayaLain = true;
        if (totalPendek > 0) adaBiayaLain = true;
        if (totalPanjang > 0) adaBiayaLain = true;
        if (unformatRupiah($('#kpk_hidden').val()) > 0) adaBiayaLain = true;
        if (unformatRupiah($('#tas_hidden').val()) > 0) adaBiayaLain = true;
        if (unformatRupiah($('#sertifikat_hidden').val()) > 0) adaBiayaLain = true;
        if (unformatRupiah($('#stpb_hidden').val()) > 0) adaBiayaLain = true;
        if (unformatRupiah($('#rbas_hidden').val()) > 0) adaBiayaLain = true;
        if (unformatRupiah($('#bcabs01_hidden').val()) > 0) adaBiayaLain = true;
        if (unformatRupiah($('#bcabs02_hidden').val()) > 0) adaBiayaLain = true;
        
        // Event & Lain-lain
        $('.biaya-lain').each(function() {
            if (unformatRupiah($(this).val()) > 0) adaBiayaLain = true;
        });

        let totalItem = jumlahBulan;

        // LOGIKA BARU: Jika hanya 1 bulan SPP + ada biaya lain → jadi 1 kwitansi
        if (jumlahBulan === 1 && sppValue > 0 && adaBiayaLain) {
            totalItem = 1;
        } 
        // Jika SPP lebih dari 1 bulan → tetap pisah per bulan
        else if (jumlahBulan > 1) {
            totalItem = jumlahBulan; 
            // Biaya lain tetap terpisah jika ada
            if (adaBiayaLain) totalItem++;
        } 
        // Hanya biaya lain (tanpa SPP)
        else if (adaBiayaLain) {
            totalItem = 1;
        }

        let teks = '';

        if (isManualMode) {
            const manualBase = $('#kwitansi_base_manual').val().trim();
            if (!manualBase) {
                teks = 'Masukkan base kwitansi manual';
            } else {
                if (totalItem <= 1) {
                    teks = manualBase;
                } else {
                    const prefix = manualBase.endsWith('-') ? '' : '-';
                    teks = manualBase + prefix + '01 s/d ' + manualBase + prefix + String(totalItem).padStart(2, '0');
                }
            }
        } else {
            const base = generateKwitansiBase();
            if (!base) {
                teks = 'Pilih NIM terlebih dahulu';
            } else {
                if (totalItem <= 1) {
                    teks = base + '01';
                } else {
                    teks = base + '01 s/d ' + base + String(totalItem).padStart(2, '0');
                }
            }
        }

        $('#kwitansi-preview').text(teks);
        $('#kwitansi-preview').removeClass('text-muted').addClass('text-primary');
    }

        // ================================================
        // KWITANSI MANUAL
        // ================================================
        $('#manual_kwitansi_toggle').on('change', function() {
            isManualMode = this.checked;
            if (isManualMode) {
                $('#manual_kwitansi_input').slideDown(200);
                $('#kwitansi_base_manual').focus();
            } else {
                $('#manual_kwitansi_input').slideUp(200);
                $('#kwitansi_base_manual').val('');
            }
            updateKwitansiPreview();
        });

        $('#kwitansi_base_manual').on('input', updateKwitansiPreview);

        // ================================================
        // SELECT2
        // ================================================
        $('#voucher').select2({
            placeholder: "-- Pilih --",
            allowClear: true,
            width: '100%',
            multiple: true
        });

        // ================================================
        // HANDLE MULTIPLE KAOS (BISA BERBEDA UKURAN)
        // ================================================
        function hitungTotalKaos() {
        let totalPendek = 0;
        let totalPanjang = 0;

        // Kaos Pendek
        $('.kaos-pendek-row').each(function() {
            const $row = $(this);
            const $select = $row.find('.kaos-pendek-select');
            const $qty = $row.find('.kaos-pendek-qty');

            const harga = parseFloat($select.find('option:selected').data('harga')) || 0;
            let qty = parseInt($qty.val()) || 0;

            // AUTO SET QTY = 1 saat memilih, dan 0 saat di-clear
            if (harga > 0 && qty === 0) {
                qty = 1;
                $qty.val(1);
            } else if (harga === 0) {
                qty = 0;
                $qty.val(0);
            }

            totalPendek += harga * qty;
        });

        // Kaos Panjang
        $('.kaos-panjang-row').each(function() {
            const $row = $(this);
            const $select = $row.find('.kaos-panjang-select');
            const $qty = $row.find('.kaos-panjang-qty');

            const harga = parseFloat($select.find('option:selected').data('harga')) || 0;
            let qty = parseInt($qty.val()) || 0;

            if (harga > 0 && qty === 0) {
                qty = 1;
                $qty.val(1);
            } else if (harga === 0) {
                qty = 0;
                $qty.val(0);
            }

            totalPanjang += harga * qty;
        });

        $('#kaos_pendek_hidden').val(totalPendek);
        $('#kaos_panjang_hidden').val(totalPanjang);

        updateKaosInfo();
        hitungTotal();
        updateKwitansiPreview();
    }

    function updateKaosInfo() {
        // Info Kaos Pendek
        let pendekHtml = '';
        $('.kaos-pendek-row').each(function() {
            const opt = $(this).find('.kaos-pendek-select option:selected');
            const nama = opt.text().trim() || 'Kaos Pendek';
            const harga = parseFloat(opt.data('harga')) || 0;
            const qty = parseInt($(this).find('.kaos-pendek-qty').val()) || 0;
            if (qty > 0 && harga > 0) {
                pendekHtml += `<div>${qty} × ${nama} = <strong>Rp ${formatRupiah(harga * qty)}</strong></div>`;
            }
        });
        $('#ukuran-pendek-container').html(pendekHtml ? `<div class="alert alert-info py-2 small">${pendekHtml}</div>` : '');

        // Info Kaos Panjang
        let panjangHtml = '';
        $('.kaos-panjang-row').each(function() {
            const opt = $(this).find('.kaos-panjang-select option:selected');
            const nama = opt.text().trim() || 'Kaos Panjang';
            const harga = parseFloat(opt.data('harga')) || 0;
            const qty = parseInt($(this).find('.kaos-panjang-qty').val()) || 0;
            if (qty > 0 && harga > 0) {
                panjangHtml += `<div>${qty} × ${nama} = <strong>Rp ${formatRupiah(harga * qty)}</strong></div>`;
            }
        });
        $('#ukuran-panjang-container').html(panjangHtml ? `<div class="alert alert-info py-2 small">${panjangHtml}</div>` : '');
    }

    // Event Kaos (Multiple Rows)
    $(document).on('change input', '.kaos-pendek-select, .kaos-pendek-qty, .kaos-panjang-select, .kaos-panjang-qty', hitungTotalKaos);

    // Tambah Baris Kaos Pendek
    $(document).on('click', '.btn-add-kaos-pendek', function() {
        const clone = $(this).closest('.kaos-pendek-row').clone();
        clone.find('select').val('');
        clone.find('input[type=number]').val(0);
        clone.find('.btn-add-kaos-pendek')
             .removeClass('btn-success')
             .addClass('btn-danger btn-remove-kaos-pendek')
             .text('Hapus');
        $('#kaos-pendek-container').append(clone);
        hitungTotalKaos();
    });

    // Hapus Baris Kaos Pendek
    $(document).on('click', '.btn-remove-kaos-pendek', function() {
        if ($('.kaos-pendek-row').length > 1) {
            $(this).closest('.kaos-pendek-row').remove();
            hitungTotalKaos();
        }
    });

    // Tambah Baris Kaos Panjang
    $(document).on('click', '.btn-add-kaos-panjang', function() {
        const clone = $(this).closest('.kaos-panjang-row').clone();
        clone.find('select').val('');
        clone.find('input[type=number]').val(0);
        clone.find('.btn-add-kaos-panjang')
             .removeClass('btn-success')
             .addClass('btn-danger btn-remove-kaos-panjang')
             .text('Hapus');
        $('#kaos-panjang-container').append(clone);
        hitungTotalKaos();
    });

    // Hapus Baris Kaos Panjang
    $(document).on('click', '.btn-remove-kaos-panjang', function() {
        if ($('.kaos-panjang-row').length > 1) {
            $(this).closest('.kaos-panjang-row').remove();
            hitungTotalKaos();
        }
    });

// ================================================
// HANDLE DAFTAR (FIXED + AUTO QTY = 1)
// ================================================
function hitungDaftar() {
    const $selectKode = $('#daftar_select');
    const $selectTipe = $('#daftar_tipe_harga');
    const $qtyInput   = $('#daftar_qty');

    const kode = $selectKode.val();
    const tipe = $selectTipe.val();
    
    let harga = 0;
    if (kode) {
        const dataKey = tipe.replace('harga_', 'harga-'); 
        harga = parseFloat($selectKode.find(':selected').data(dataKey)) || 0;
    }

    let qty = parseInt($qtyInput.val()) || 0;

    // AUTO SET QTY
    if (harga > 0 && qty === 0) {
        qty = 1;
        $qtyInput.val(1);
    } else if (harga === 0) {
        qty = 0;
        $qtyInput.val(0);
    }

    const total = harga * qty;

    $('#daftar_hidden').val(total);

    if (qty > 0 && harga > 0) {
        const namaItem = $selectKode.find(':selected').text();
        const namaTipe = $selectTipe.find(':selected').text();
        
        $('#daftar-container').html(`
            <div class="alert alert-info py-2 small">
                ${qty} × ${namaItem} (${namaTipe}) = 
                <strong>Rp ${formatRupiah(total)}</strong>
            </div>
        `);
    } else {
        $('#daftar-container').html('');
    }

    hitungTotal();
    updateKwitansiPreview();
}

    // Event Listener
    $('#daftar_select, #daftar_tipe_harga, #daftar_qty').on('change input', hitungDaftar);

    // ================================================
// HANDLE KPK (Simple - Tanpa Qty Input)
// ================================================
function hitungTotalKpk() {
    let total = 0;
    let infoHtml = '';

    $('.kpk-row').each(function() {
        const $select = $(this).find('.kpk-select');
        const harga = parseFloat($select.find('option:selected').data('harga')) || 0;

        if (harga > 0) {
            total += harga;

            const nama = $select.find('option:selected').text().split(' = ')[0] || 'KPK';
            infoHtml += `<div>${nama} = <strong>Rp ${formatRupiah(harga)}</strong></div>`;
        }
    });

    $('#kpk_hidden').val(total);

    $('#kpk-info').html(infoHtml ? `<div class="alert alert-info py-2 small">${infoHtml}</div>` : '');

    hitungTotal();
    updateKwitansiPreview();
}

// Tambah Baris KPK
$(document).on('click', '.btn-add-kpk', function() {
    const clone = $(this).closest('.kpk-row').clone();
    clone.find('select').val('');
    clone.find('.btn-add-kpk')
         .removeClass('btn-success')
         .addClass('btn-danger btn-remove-kpk')
         .text('Hapus');
    $('#kpk-container').append(clone);
    hitungTotalKpk();
});

// Hapus Baris KPK
$(document).on('click', '.btn-remove-kpk', function() {
    if ($('.kpk-row').length > 1) {
        $(this).closest('.kpk-row').remove();
        hitungTotalKpk();
    }
});

// Event Listener KPK
$(document).on('change', '.kpk-select', hitungTotalKpk);

    // ================================================
// HANDLE KPK (Dengan Qty di Dropdown 1x - 5x)
// ================================================
function hitungTotalKpk() {
    let total = 0;
    let infoHtml = '';

    $('.kpk-row').each(function() {
        const $select = $(this).find('.kpk-select');
        const selectedOption = $select.find('option:selected');
        
        const harga = parseFloat(selectedOption.data('harga')) || 0;
        const qty   = parseInt(selectedOption.data('qty')) || 0;

        if (harga > 0 && qty > 0) {
            total += harga * qty;

            const nama = selectedOption.text().split(' = ')[0] || 'KPK';
            infoHtml += `<div>${nama} = <strong>Rp ${formatRupiah(harga * qty)}</strong></div>`;
        }
    });

    $('#kpk_hidden').val(total);

    $('#kpk-info').html(infoHtml ? `<div class="alert alert-info py-2 small">${infoHtml}</div>` : '');

    hitungTotal();
    updateKwitansiPreview();
}

// Tambah Baris KPK
$(document).on('click', '.btn-add-kpk', function() {
    const clone = $(this).closest('.kpk-row').clone();
    clone.find('select').val('');
    clone.find('.btn-add-kpk')
         .removeClass('btn-success')
         .addClass('btn-danger btn-remove-kpk')
         .text('Hapus');
    $('#kpk-container').append(clone);
    hitungTotalKpk();
});

// Hapus Baris KPK
$(document).on('click', '.btn-remove-kpk', function() {
    if ($('.kpk-row').length > 1) {
        $(this).closest('.kpk-row').remove();
        hitungTotalKpk();
    }
});

// Event Listener KPK
$(document).on('change', '.kpk-select', hitungTotalKpk);

// ================================================
// HANDLE TAS (1x - 5x di Dropdown)
// ================================================
    function hitungTotalTas() {
        let total = 0;
        let infoHtml = '';

        $('.tas-row').each(function() {
            const $select = $(this).find('.tas-select');
            const selectedOption = $select.find('option:selected');
            
            const harga = parseFloat(selectedOption.data('harga')) || 0;
            const qty   = parseInt(selectedOption.data('qty')) || 0;

            if (harga > 0 && qty > 0) {
                total += harga * qty;

                const nama = selectedOption.text().split(' = ')[0] || 'Tas';
                infoHtml += `<div>${nama} = <strong>Rp ${formatRupiah(harga * qty)}</strong></div>`;
            }
        });

        $('#tas_hidden').val(total);

        $('#tas-info').html(infoHtml ? `<div class="alert alert-info py-2 small">${infoHtml}</div>` : '');

        hitungTotal();
        updateKwitansiPreview();
    }

    // Tambah Baris Tas
    $(document).on('click', '.btn-add-tas', function() {
        const clone = $(this).closest('.tas-row').clone();
        clone.find('select').val('');
        clone.find('.btn-add-tas')
            .removeClass('btn-success')
            .addClass('btn-danger btn-remove-tas')
            .text('Hapus');
        $('#tas-container').append(clone);
        hitungTotalTas();
    });

    // Hapus Baris Tas
    $(document).on('click', '.btn-remove-tas', function() {
        if ($('.tas-row').length > 1) {
            $(this).closest('.tas-row').remove();
            hitungTotalTas();
        }
    });

    // Event Listener Tas
    $(document).on('change', '.tas-select', hitungTotalTas);

    // ================================================
    // HANDLE SERTIFIKAT (1x - 5x di Dropdown)
    // ================================================
    function hitungTotalSertifikat() {
        let total = 0;
        let infoHtml = '';

        $('.sertifikat-row').each(function() {
            const $select = $(this).find('.sertifikat-select');
            const selectedOption = $select.find('option:selected');
            
            const harga = parseFloat(selectedOption.data('harga')) || 0;
            const qty   = parseInt(selectedOption.data('qty')) || 0;

            if (harga > 0 && qty > 0) {
                total += harga * qty;

                const nama = selectedOption.text().split(' = ')[0] || 'Sertifikat';
                infoHtml += `<div>${nama} = <strong>Rp ${formatRupiah(harga * qty)}</strong></div>`;
            }
        });

        $('#sertifikat_hidden').val(total);

        $('#sertifikat-info').html(infoHtml ? `<div class="alert alert-info py-2 small">${infoHtml}</div>` : '');

        hitungTotal();
        updateKwitansiPreview();
    }

    // Tambah Baris Sertifikat
    $(document).on('click', '.btn-add-sertifikat', function() {
        const clone = $(this).closest('.sertifikat-row').clone();
        clone.find('select').val('');
        clone.find('.btn-add-sertifikat')
            .removeClass('btn-success')
            .addClass('btn-danger btn-remove-sertifikat')
            .text('Hapus');
        $('#sertifikat-container').append(clone);
        hitungTotalSertifikat();
    });

    // Hapus Baris Sertifikat
    $(document).on('click', '.btn-remove-sertifikat', function() {
        if ($('.sertifikat-row').length > 1) {
            $(this).closest('.sertifikat-row').remove();
            hitungTotalSertifikat();
        }
    });

    // Event Listener Sertifikat
    $(document).on('change', '.sertifikat-select', hitungTotalSertifikat);

    // ================================================
    // HANDLE STPB (1x - 5x di Dropdown)
    // ================================================
    function hitungTotalStpb() {
        let total = 0;
        let infoHtml = '';

        $('.stpb-row').each(function() {
            const $select = $(this).find('.stpb-select');
            const selectedOption = $select.find('option:selected');
            
            const harga = parseFloat(selectedOption.data('harga')) || 0;
            const qty   = parseInt(selectedOption.data('qty')) || 0;

            if (harga > 0 && qty > 0) {
                total += harga * qty;

                const nama = selectedOption.text().split(' = ')[0] || 'STPB';
                infoHtml += `<div>${nama} = <strong>Rp ${formatRupiah(harga * qty)}</strong></div>`;
            }
        });

        $('#stpb_hidden').val(total);

        $('#stpb-info').html(infoHtml ? `<div class="alert alert-info py-2 small">${infoHtml}</div>` : '');

        hitungTotal();
        updateKwitansiPreview();
    }

    // Tambah Baris STPB
    $(document).on('click', '.btn-add-stpb', function() {
        const clone = $(this).closest('.stpb-row').clone();
        clone.find('select').val('');
        clone.find('.btn-add-stpb')
            .removeClass('btn-success')
            .addClass('btn-danger btn-remove-stpb')
            .text('Hapus');
        $('#stpb-container').append(clone);
        hitungTotalStpb();
    });

    // Hapus Baris STPB
    $(document).on('click', '.btn-remove-stpb', function() {
        if ($('.stpb-row').length > 1) {
            $(this).closest('.stpb-row').remove();
            hitungTotalStpb();
        }
    });

    // Event Listener STPB
    $(document).on('change', '.stpb-select', hitungTotalStpb);

    // ================================================
    // HANDLE RBAS (1x - 5x di Dropdown)
    // ================================================
    function hitungTotalRbas() {
        let total = 0;
        let infoHtml = '';

        $('.rbas-row').each(function() {
            const $select = $(this).find('.rbas-select');
            const selectedOption = $select.find('option:selected');
            
            const harga = parseFloat(selectedOption.data('harga')) || 0;
            const qty   = parseInt(selectedOption.data('qty')) || 0;

            if (harga > 0 && qty > 0) {
                total += harga * qty;

                const nama = selectedOption.text().split(' = ')[0] || 'RBAS';
                infoHtml += `<div>${nama} = <strong>Rp ${formatRupiah(harga * qty)}</strong></div>`;
            }
        });

        $('#rbas_hidden').val(total);

        $('#rbas-info').html(infoHtml ? `<div class="alert alert-info py-2 small">${infoHtml}</div>` : '');

        hitungTotal();
        updateKwitansiPreview();
    }

    // Tambah Baris RBAS
    $(document).on('click', '.btn-add-rbas', function() {
        const clone = $(this).closest('.rbas-row').clone();
        clone.find('select').val('');
        clone.find('.btn-add-rbas')
            .removeClass('btn-success')
            .addClass('btn-danger btn-remove-rbas')
            .text('Hapus');
        $('#rbas-container').append(clone);
        hitungTotalRbas();
    });

    // Hapus Baris RBAS
    $(document).on('click', '.btn-remove-rbas', function() {
        if ($('.rbas-row').length > 1) {
            $(this).closest('.rbas-row').remove();
            hitungTotalRbas();
        }
    });

    // Event Listener RBAS
    $(document).on('change', '.rbas-select', hitungTotalRbas);

    // ================================================
    // HANDLE BCABS01 (1x - 5x di Dropdown)
    // ================================================
    function hitungTotalBcabs01() {
        let total = 0;
        let infoHtml = '';

        $('.bcabs01-row').each(function() {
            const $select = $(this).find('.bcabs01-select');
            const selectedOption = $select.find('option:selected');
            
            const harga = parseFloat(selectedOption.data('harga')) || 0;
            const qty   = parseInt(selectedOption.data('qty')) || 0;

            if (harga > 0 && qty > 0) {
                total += harga * qty;

                const nama = selectedOption.text().split(' = ')[0] || 'BCABS01';
                infoHtml += `<div>${nama} = <strong>Rp ${formatRupiah(harga * qty)}</strong></div>`;
            }
        });

        $('#bcabs01_hidden').val(total);

        $('#bcabs01-info').html(infoHtml ? `<div class="alert alert-info py-2 small">${infoHtml}</div>` : '');

        hitungTotal();
        updateKwitansiPreview();
    }

    // Tambah Baris BCABS01
    $(document).on('click', '.btn-add-bcabs01', function() {
        const clone = $(this).closest('.bcabs01-row').clone();
        clone.find('select').val('');
        clone.find('.btn-add-bcabs01')
            .removeClass('btn-success')
            .addClass('btn-danger btn-remove-bcabs01')
            .text('Hapus');
        $('#bcabs01-container').append(clone);
        hitungTotalBcabs01();
    });

    // Hapus Baris BCABS01
    $(document).on('click', '.btn-remove-bcabs01', function() {
        if ($('.bcabs01-row').length > 1) {
            $(this).closest('.bcabs01-row').remove();
            hitungTotalBcabs01();
        }
    });

    // Event Listener BCABS01
    $(document).on('change', '.bcabs01-select', hitungTotalBcabs01);

    // ================================================
    // HANDLE BCABS02 (1x - 5x di Dropdown)
    // ================================================
    function hitungTotalBcabs02() {
        let total = 0;
        let infoHtml = '';

        $('.bcabs02-row').each(function() {
            const $select = $(this).find('.bcabs02-select');
            const selectedOption = $select.find('option:selected');
            
            const harga = parseFloat(selectedOption.data('harga')) || 0;
            const qty   = parseInt(selectedOption.data('qty')) || 0;

            if (harga > 0 && qty > 0) {
                total += harga * qty;

                const nama = selectedOption.text().split(' = ')[0] || 'BCABS02';
                infoHtml += `<div>${nama} = <strong>Rp ${formatRupiah(harga * qty)}</strong></div>`;
            }
        });

        $('#bcabs02_hidden').val(total);

        $('#bcabs02-info').html(infoHtml ? `<div class="alert alert-info py-2 small">${infoHtml}</div>` : '');

        hitungTotal();
        updateKwitansiPreview();
    }

    // Tambah Baris BCABS02
    $(document).on('click', '.btn-add-bcabs02', function() {
        const clone = $(this).closest('.bcabs02-row').clone();
        clone.find('select').val('');
        clone.find('.btn-add-bcabs02')
            .removeClass('btn-success')
            .addClass('btn-danger btn-remove-bcabs02')
            .text('Hapus');
        $('#bcabs02-container').append(clone);
        hitungTotalBcabs02();
    });

    // Hapus Baris BCABS02
    $(document).on('click', '.btn-remove-bcabs02', function() {
        if ($('.bcabs02-row').length > 1) {
            $(this).closest('.bcabs02-row').remove();
            hitungTotalBcabs02();
        }
    });

    // Event Listener BCABS02
    $(document).on('change', '.bcabs02-select', hitungTotalBcabs02);

    // ================================================
    // FUNGSI LOAD VOUCHER
    // ================================================
    function loadVouchersByNim(nim) {
        const voucherSelect = $('#voucher');
        voucherSelect.empty();
        voucherSelect.append('<option value="">-- Tidak pakai voucher --</option>');

        if (!nim) {
            voucherSelect.trigger('change');
            return;
        }

        $.ajax({
            url: '{{ route("penerimaan.vouchers.by.nim") }}',
            type: 'GET',
            data: { nim: nim },
            success: function(data) {
                if (data.length === 0) {
                    voucherSelect.append('<option value="" disabled>Tidak ada voucher tersedia untuk murid ini</option>');
                } else {
                    $.each(data, function(i, v) {
                        voucherSelect.append(
                            `<option value="${v.no_voucher}" data-nominal="50000">
                                ${v.no_voucher} - Rp 50.000 (sisa: ${v.jumlah_voucher})
                             </option>`
                        );
                    });
                }
                voucherSelect.trigger('change');
            },
            error: function() {
                console.error('Gagal memuat voucher');
                voucherSelect.append('<option value="" disabled>Gagal memuat daftar voucher</option>');
                voucherSelect.trigger('change');
            }
        });
    }

    // Event Handler NIM
    $('#nimSelect').select2({
        placeholder: "-- Pilih NIM --",
        width: '100%',
        allowClear: true
    }).on('select2:select', function(e) {
        const opt = $(this).find(':selected');
        currentNimLast3 = String(opt.val()).slice(-3).padStart(3, '0');

        $('#namaMuridInput').val(opt.data('nama'));
        $('#kelasInput').val(opt.data('kelas'));
        $('#golInput').val(opt.data('gol'));
        $('#kdInput').val(opt.data('kd'));
        $('#statusInput').val(opt.data('status'));
        $('#guruInput').val(opt.data('guru'));
        $('#nilai_spp').val(opt.data('spp') > 0 ? 'Rp ' + formatRupiah(opt.data('spp')) : '0');

        const unit = opt.data('bimba_unit') || '';
        const cabang = opt.data('no_cabang') || '';
        $('#bimbaUnitInput').val(unit);
        $('#noCabangInput').val(cabang);

        sppPerBulan = parseInt(opt.data('spp')) || 0;
        updateSppDropdown(sppPerBulan);
        resetBulanRows();
        loadVouchersByNim(opt.val());
        updateKwitansiPreview();
    }).on('select2:clear', function() {
        currentNimLast3 = '';
        $('#namaMuridInput, #kelasInput, #golInput, #kdInput, #statusInput, #guruInput, #nilai_spp, #bimbaUnitInput, #noCabangInput').val('');
        sppPerBulan = 0;
        updateSppDropdown(0);
        resetBulanRows();
        loadVouchersByNim(null);
        updateKwitansiPreview();
    });

    // ================================================
    // SPP & BULAN
    // ================================================
    function updateSppDropdown(harga) {
        const el = $('#spp_dropdown');
        el.empty().append('<option value="">-- Pilih jumlah bulan --</option>');
        if (harga <= 0) {
            $('#spp_info').text('Tidak ada data SPP');
            el.prop('disabled', true);
            return;
        }
        $('#spp_info').text(`Rp ${formatRupiah(harga)} / bulan`);
        for (let i = 1; i <= 12; i++) {
            const total = harga * i;
            el.append(`<option value="${total}">${i} bulan - Rp ${formatRupiah(total)}</option>`);
        }
        el.prop('disabled', false);
    }

    function resetBulanRows() {
        $('#bulan-container').html(`
            <div class="d-flex gap-2 mb-2 align-items-center bulan-row">
                <select name="bulan_bayar[]" class="form-select" style="width:180px;">
                    <option value="">Pilih Bulan</option>
                    ${['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'].map(b => `<option value="${b}">${b}</option>`).join('')}
                </select>
                <input type="number" name="tahun_bayar[]" class="form-control" style="width:100px;" value="${new Date().getFullYear()}" min="2020" required>
                <button type="button" class="btn btn-success btn-sm btn-add-remove">Tambah</button>
            </div>
        `);
        updateInfoBulan();
    }

    function updateInfoBulan() {
    const count = $('.bulan-row').length;
    const sppValue = parseInt($('#spp_dropdown').val()) || 0;
    const totalHarus = count * sppPerBulan;

    let html = '';

    if (sppValue > 0 && count > 0) {

    // Hitung voucher
    let totalVoucher = 0;

    $('#voucher :selected').each(function () {
        totalVoucher += parseInt($(this).data('nominal') || 0);
    });

    // Total tagihan asli
    const totalHarus = count * sppPerBulan;

    // Setelah dipotong voucher
    const totalSetelahVoucher = totalHarus - totalVoucher;

    // Baris utama
    html = `
        <strong>${count} bulan</strong> × 
        Rp ${formatRupiah(sppPerBulan)}
    `;

    // Jika ada voucher
    if (totalVoucher > 0) {
        html += `
            - voucher = 
            <strong>Rp ${formatRupiah(totalSetelahVoucher)}</strong>
            <br>
            <span class="text-primary fw-bold">
                Voucher: - Rp ${formatRupiah(totalVoucher)}
            </span>
            <br>
        `;
    } else {
        html += `
            = <strong>Rp ${formatRupiah(totalHarus)}</strong><br>
        `;
    }

    // Cek selisih pembayaran dropdown vs tagihan
    const selisih = sppValue - totalHarus;

    if (selisih > 0) {
        html += `
            <span class="text-success fw-bold">
                Kelebihan Rp ${formatRupiah(selisih)} → deposit
            </span>
        `;
    } else if (selisih < 0) {
        html += `
            <span class="text-danger fw-bold">
                Kurang Rp ${formatRupiah(Math.abs(selisih))}
            </span>
        `;
    } else {
        html += `
            <span class="text-info fw-bold">
                Sesuai tagihan
            </span>
        `;
    }
}

    $('#info-bulan').html(html);
    hitungTotal();
    updateKwitansiPreview();
}

    function hitungTotal() {

    // =========================
    // TOTAL SPP
    // =========================
    let totalSpp = parseInt($('#spp_dropdown').val()) || 0;

    // Voucher
    let potonganVoucher = 0;

    $('#voucher :selected').each(function() {
        potonganVoucher += parseInt($(this).data('nominal') || 0);
    });

    totalSpp -= potonganVoucher;

    if (totalSpp < 0) {
        totalSpp = 0;
    }

    // =========================
    // TOTAL BIAYA PENDAFTARAN
    // =========================
    let totalDaftar = 0;

    totalDaftar += unformatRupiah($('#daftar_hidden').val());

    // =========================
    // TOTAL LAIN-LAIN
    // =========================
    let totalLain = 0;

    totalLain += unformatRupiah($('#kaos_pendek_hidden').val());
    totalLain += unformatRupiah($('#kaos_panjang_hidden').val());
    totalLain += unformatRupiah($('#kpk_hidden').val());
    totalLain += unformatRupiah($('#tas_hidden').val());
    totalLain += unformatRupiah($('#sertifikat_hidden').val());
    totalLain += unformatRupiah($('#stpb_hidden').val());
    totalLain += unformatRupiah($('#rbas_hidden').val());
    totalLain += unformatRupiah($('#bcabs01_hidden').val());
    totalLain += unformatRupiah($('#bcabs02_hidden').val());

    // Event & lain-lain manual
    $('.biaya-lain').each(function() {
        totalLain += unformatRupiah($(this).val());
    });

    // =========================
    // GRAND TOTAL
    // =========================
    let grandTotal = totalSpp + totalDaftar + totalLain;

    // =========================
    // TAMPILKAN
    // =========================
    $('#total_spp').val(formatRupiah(totalSpp));
    $('#total_daftar').val(formatRupiah(totalDaftar));
    $('#total_lain').val(formatRupiah(totalLain));

    $('#grand_total').val(formatRupiah(grandTotal));

    // tetap support field lama
    $('#total').val(formatRupiah(grandTotal));
}

    // Event Bulan
    $(document).on('click', '.btn-add-remove', function() {
        if ($(this).hasClass('btn-success')) {
            const clone = $(this).closest('.bulan-row').clone(true, true);
            clone.find('select, input[type=number]').val('');
            clone.find('input[type=number]').val(new Date().getFullYear());
            clone.find('.btn-add-remove').removeClass('btn-success').addClass('btn-danger').text('Hapus');
            $('#bulan-container').append(clone);
        } else if ($('.bulan-row').length > 1) {
            $(this).closest('.bulan-row').remove();
        }
        updateInfoBulan();
    });

    $('#tambah-bulan-lagi').click(() => $('.btn-add-remove.btn-success').first()?.click());
    $('#spp_dropdown').on('change', updateInfoBulan);

    $('.biaya-lain').on('input', function() {
        let val = this.value.replace(/\D/g, '');
        this.value = formatRupiah(val);
        hitungTotal();
        updateKwitansiPreview();
    });

    $('#voucher').on('change', function() {
        const max = $('.bulan-row').length;
        const selected = $(this).val()?.length || 0;

        if (selected > max) {
            alert(`Maksimal ${max} voucher untuk ${max} bulan SPP`);
            $(this).val($(this).data('prev') || []).trigger('change');
        } else {
            $(this).data('prev', $(this).val());
            hitungTotal();
            updateInfoBulan();
        }
    }).data('prev', []);

    // ────────────────────────────────────────────────
    // Inisialisasi Awal
    // ────────────────────────────────────────────────
    updateKwitansiPreview();
    updateInfoBulan();
    hitungTotal();
    toggleBuktiTransfer();        // ← Tambahkan ini

    // Restore jika ada error validasi
    @if(old('nim'))
        setTimeout(() => {
            $('#nimSelect').val('{{ old('nim') }}').trigger('change');
        }, 300);
    @endif
});

    // ==================== AUTO FILL LENGKAP DARI REGISTRASI ====================
    @if($selectedNim)
    setTimeout(function() {
        const $nimSelect = $('#nimSelect');
        
        if ($nimSelect.length) {
            // Set value
            $nimSelect.val('{{ $selectedNim }}');
            
            // Trigger Select2 dengan cara paling kuat
            $nimSelect.trigger('change');
            $nimSelect.trigger('change.select2');
            
            // Force trigger event handler secara manual
            const selectedOption = $nimSelect.find('option[value="{{ $selectedNim }}"]');
            if (selectedOption.length) {
                const e = $.Event('select2:select', {
                    params: {
                        data: {
                            id: '{{ $selectedNim }}',
                            text: selectedOption.text(),
                            element: selectedOption[0]
                        }
                    }
                });
                $nimSelect.trigger(e);
            }

            console.log('✅ Auto fill lengkap untuk NIM: {{ $selectedNim }}');
        }
    }, 900); // delay lebih panjang agar Select2 benar-benar siap
    @endif
</script>

<style>
    .select2-container .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px) !important;
        padding-top: 0.375rem !important;
        padding-bottom: 0.375rem !important;
    }
    
</style>
@endsection