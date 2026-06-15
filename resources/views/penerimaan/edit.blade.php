@extends('layouts.app')

@section('title', 'Edit Data Penerimaan')

@section('content')
<div class="container">
    <h4 class="mb-3">Edit Data Penerimaan #{{ $penerimaan->kwitansi }}</h4>

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

    <form action="{{ route('penerimaan.update', $penerimaan->id) }}" method="POST" id="form-penerimaan" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">NIM <span class="text-danger">*</span></label>
                <select name="nim" id="nimSelect" class="form-select" required>
                    <option value="" disabled>-- Pilih NIM --</option>
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
                                data-no_cabang="{{ $murid->no_cabang ?? '' }}"
                                {{ old('nim', $penerimaan->nim) == $nimWithZero ? 'selected' : '' }}>
                            {{ $nimWithZero }} - {{ $murid->nama ?? 'Nama tidak ada' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-5">
                <label class="form-label">Nama Murid</label>
                <input type="text" id="namaMuridInput" name="nama_murid" class="form-control" 
                       value="{{ old('nama_murid', $penerimaan->nama_murid) }}" readonly required>
            </div>

            <!-- Kolom lainnya (kelas, gol, kd, status, guru, spp) -->
            <div class="col-md-2">
                <label class="form-label">Kelas</label>
                <input type="text" id="kelasInput" name="kelas" class="form-control" 
                       value="{{ old('kelas', $penerimaan->kelas) }}" readonly>
            </div>
            <div class="col-md-1">
                <label class="form-label">Gol</label>
                <input type="text" id="golInput" name="gol" class="form-control" 
                       value="{{ old('gol', $penerimaan->gol) }}" readonly>
            </div>
            <div class="col-md-1">
                <label class="form-label">KD</label>
                <input type="text" id="kdInput" name="kd" class="form-control" 
                       value="{{ old('kd', $penerimaan->kd) }}" readonly>
            </div>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <input type="text" id="statusInput" name="status" class="form-control" 
                       value="{{ old('status', $penerimaan->status) }}" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Guru</label>
                <input type="text" id="guruInput" name="guru" class="form-control" 
                       value="{{ old('guru', $penerimaan->guru) }}" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nilai SPP / Bulan</label>
                <input type="text" id="nilai_spp" class="form-control text-end fw-bold" readonly>
            </div>
        </div>

        {{-- Bimba Unit & No Cabang --}}
        @if (auth()->user()->isAdminUser())
            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <label class="form-label">Bimba Unit</label>
                    <input type="text" name="bimba_unit" class="form-control" 
                           value="{{ old('bimba_unit', $penerimaan->bimba_unit) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">No Cabang</label>
                    <input type="text" name="no_cabang" class="form-control" 
                           value="{{ old('no_cabang', $penerimaan->no_cabang) }}">
                </div>
            </div>
        @else
            <input type="hidden" name="bimba_unit" value="{{ $penerimaan->bimba_unit }}">
            <input type="hidden" name="no_cabang" value="{{ $penerimaan->no_cabang }}">
        @endif

        <hr class="my-4">

        <h5 class="text-primary">Informasi Pembayaran</h5>
        <div class="row g-3">
            <div class="col-md-6 col-lg-4">
                <label class="form-label fw-bold">Nomor Kwitansi</label>
                <div class="bg-light p-3 rounded border text-center mb-2">
                    <div class="fs-4 fw-bold text-primary">{{ $penerimaan->kwitansi }}</div>
                </div>
                <small class="text-muted">Kwitansi tidak dapat diubah</small>
            </div>

            <div class="col-md-3">
                <label class="form-label">Via Pembayaran</label>
                <select name="via" id="via" class="form-select" required>
                    <option value="cash" {{ old('via', $penerimaan->via) == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="transfer" {{ old('via', $penerimaan->via) == 'transfer' ? 'selected' : '' }}>Transfer</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Tanggal Bayar</label>
                <input type="date" name="tanggal" class="form-control" 
                    value="{{ old('tanggal', \Carbon\Carbon::parse($penerimaan->tanggal)->format('Y-m-d')) }}" required>
            </div>
        </div>

        <!-- Bukti Transfer -->
        <div class="row g-3 mt-3">
            <div class="col-md-6" id="bukti_transfer_container">
                <label class="form-label">Bukti Transfer</label>
                @if($penerimaan->bukti_transfer_path)
                    <div class="mb-2">
                        <small class="text-success">File saat ini: 
                            <a href="{{ Storage::url($penerimaan->bukti_transfer_path) }}" target="_blank">Lihat File</a>
                        </small>
                    </div>
                @endif
                <input type="file" name="bukti_transfer" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                <small class="text-muted">Kosongkan jika tidak ingin mengganti</small>
            </div>
        </div>

        <hr class="my-4">

        <!-- SPP Section -->
<h5 class="text-primary">Pembayaran SPP</h5>
<div class="row g-3">
    
    <div class="col-md-4 mt-3">
    <label class="form-label">Voucher</label>
    <select id="voucher" name="voucher" class="form-select">
        <!-- Diisi oleh JS -->
    </select>
</div>

    <!-- Jumlah Bulan -->
    <div class="col-md-4">
        <label class="form-label text-danger fw-bold">
            Jumlah Bulan <span class="text-danger">*</span>
        </label>
        <select name="spp" id="spp_dropdown" class="form-select fs-5 fw-bold text-start">
            <!-- diisi via JS -->
        </select>
    </div>

    <!-- Untuk Bulan Tahun + Info + Total SPP -->
    <div class="col-md-8">
        <label class="form-label text-success fw-bold">
            Untuk Bulan Tahun <span class="text-danger">*</span>
        </label>
        <div id="bulan-container">
            @if($penerimaan->bulan && $penerimaan->tahun)
                <div class="d-flex gap-2 mb-2 align-items-center bulan-row">
                    <select name="bulan_bayar[]" class="form-select" style="width:180px;">
                        @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $b)
                            <option value="{{ $b }}" {{ strtolower($b) == strtolower($penerimaan->bulan) ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="tahun_bayar[]" class="form-control" style="width:100px;" 
                           value="{{ old('tahun_bayar.0', $penerimaan->tahun) }}" min="2020">
                    <button type="button" class="btn btn-success btn-sm btn-add-remove">Tambah</button>
                </div>
            @endif
        </div>

        <button type="button" id="tambah-bulan-lagi" class="btn btn-link text-primary p-0 mt-1">
            + Tambah bulan lain
        </button>

        <!-- BAGIAN YANG ANDA MINTA -->
        <div id="info-bulan" class="mt-3 p-3 bg-light rounded small"></div>
        
        <div class="row mt-3">
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
</div>

        {{-- ================= BIAYA DAFTAR ================= --}}
            <hr class="my-4">

           {{-- ================= BIAYA DAFTAR ================= --}}
<h5 class="text-primary">Biaya Daftar</h5>

<div class="row g-3 mb-4">

    <!-- KELAS -->
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
                        data-harga-promo_umum="{{ $item['harga_promo_umum'] }}"
                        @if(
                            old('daftar_kode') == $item['kode'] || 
                            (!old('daftar_kode') && $penerimaan->daftar > 0)
                        )
                            selected
                        @endif>
                    {{ $item['nama'] }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- JENIS HARGA -->
    <div class="col-md-4">
        <label class="form-label fw-bold">Jenis Harga</label>
        <select name="daftar_tipe_harga" id="daftar_tipe_harga" class="form-select">
            <option value="">-- Pilih Jenis Harga --</option>
            <option value="harga_daftar"   @if(old('daftar_tipe_harga') == 'harga_daftar') selected @endif>Daftar Ulang</option>
            <option value="harga_duafa"    @if(old('daftar_tipe_harga') == 'harga_duafa') selected @endif>Dhuafa</option>
            <option value="harga_promo"    @if(old('daftar_tipe_harga') == 'harga_promo' || $penerimaan->daftar > 0) selected @endif>Promo Khusus</option>
            <option value="harga_spesial"  @if(old('daftar_tipe_harga') == 'harga_spesial') selected @endif>Spesial</option>
            <option value="harga_umum1"    @if(old('daftar_tipe_harga') == 'harga_umum1') selected @endif>Umum 1</option>
            <option value="harga_promo_umum" @if(old('daftar_tipe_harga') == 'harga_promo_umum') selected @endif>Promo Umum</option>
        </select>
    </div>

    <!-- TOTAL -->
    <div class="col-md-3">
        <label class="form-label fw-bold text-primary">Total Biaya Daftar</label>
        <input type="text" id="total_daftar" 
               class="form-control text-end bg-success fw-bold text-white fs-5" 
               readonly 
               value="{{ number_format(old('daftar', $penerimaan->daftar ?? 0)) }}">
    </div>

</div>

<input type="hidden" name="daftar" id="daftar_hidden" value="{{ old('daftar', $penerimaan->daftar ?? 0) }}">
        <div class="row g-3">

                {{-- HAPUS BLOK BIAYA DAFTAR YANG LAMA DARI SINI --}}

              <!-- KAOS PENDEK -->
<div class="col-md-4">
    <label class="form-label">Kaos Pendek</label>
    <div id="kaos-pendek-container">
        <!-- Baris default -->
        <div class="kaos-pendek-row d-flex gap-2 mb-2 align-items-end">
            <select name="kaos_pendek_kode[]" class="form-select kaos-pendek-select" style="width: 60%;">
                <option value="">-- Pilih Ukuran --</option>
                @foreach($kaosPendekList as $kaos)
                    <option value="{{ $kaos['kode'] }}" data-harga="{{ $kaos['harga'] }}">
                        {{ $kaos['kode'] }}
                    </option>
                @endforeach
            </select>
            <input type="number" name="kaos_pendek_qty[]" class="form-control kaos-pendek-qty" value="0" min="0" style="width: 80px;">
            <button type="button" class="btn btn-success btn-sm btn-add-kaos-pendek">Tambah</button>
        </div>
    </div>
    <input type="hidden" name="kaos_pendek" id="kaos_pendek_hidden" value="{{ $penerimaan->kaos ?? 0 }}">
    <div id="ukuran-pendek-container" class="mt-2"></div>
</div>

<!-- KAOS PANJANG -->
<div class="col-md-4">
    <label class="form-label">Kaos Panjang (Lengan Panjang)</label>
    <div id="kaos-panjang-container">
        <!-- Baris default -->
        <div class="kaos-panjang-row d-flex gap-2 mb-2 align-items-end">
            <select name="kaos_panjang_kode[]" class="form-select kaos-panjang-select" style="width: 60%;">
                <option value="">-- Pilih Ukuran --</option>
                @foreach($kaosPanjangList as $kaos)
                    <option value="{{ $kaos['kode'] }}" data-harga="{{ $kaos['harga'] }}">
                        {{ $kaos['kode'] }}
                    </option>
                @endforeach
            </select>
            <input type="number" name="kaos_panjang_qty[]" class="form-control kaos-panjang-qty" value="0" min="0" style="width: 80px;">
            <button type="button" class="btn btn-success btn-sm btn-add-kaos-panjang">Tambah</button>
        </div>
    </div>
    <input type="hidden" name="kaos_panjang" id="kaos_panjang_hidden" value="{{ $penerimaan->kaos_lengan_panjang ?? 0 }}">
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
        <!-- ... (lanjutkan dengan semua field lain yang ada di create) ... -->

        <div class="mt-5 text-end">
            <button type="submit" class="btn btn-primary btn-lg px-5">Update Pembayaran</button>
            <a href="{{ route('penerimaan.index') }}" class="btn btn-outline-secondary btn-lg px-5 ms-3">Kembali</a>
        </div>
    </form>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {

    let sppPerBulan = {{ $penerimaan->spp ?? 0 }};
    let voucherDiskon = 50000;

    function formatRupiah(angka) {
        if (!angka) return '0';
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function unformatRupiah(str) {
        return parseInt((str || '0').replace(/\./g, '')) || 0;
    }

    // ==================== NIM SELECT ====================
    $('#nimSelect').select2({ 
        placeholder: "-- Pilih NIM --", 
        width: '100%' 
    }).val('{{ str_pad($penerimaan->nim ?? "", 9, "0", STR_PAD_LEFT) }}').trigger('change');

    // ==================== SPP DROPDOWN ====================
    function updateSppDropdown(harga) {
        const el = $('#spp_dropdown');
        el.empty().append('<option value="">-- Pilih jumlah bulan --</option>');
        if (harga <= 0) return;

        for (let i = 1; i <= 12; i++) {
            const total = harga * i;
            el.append(`<option value="${total}">${i} bulan - Rp ${formatRupiah(total)}</option>`);
        }
        const existingSpp = {{ $penerimaan->spp ?? 0 }};
        if (existingSpp > 0) el.val(existingSpp);
    }
    updateSppDropdown(sppPerBulan);

    // ==================== VOUCHER ====================
    let allVouchers = @json($vouchers);
    let currentVoucher = "{{ $penerimaan->voucher ?? '' }}";

    function initVoucherSelect() {
        const $select = $('#voucher');
        $select.empty();

        if (currentVoucher && !allVouchers.some(v => v.no_voucher === currentVoucher)) {
            allVouchers.unshift({ no_voucher: currentVoucher, jumlah_voucher: 0 });
        }

        allVouchers.forEach(function(v) {
            const sisa = v.jumlah_voucher ?? 0;
            let optionText = `${v.no_voucher} - Rp 50.000`;
            if (sisa > 0) optionText += ` (sisa: ${sisa})`;

            const isCurrent = (currentVoucher && v.no_voucher === currentVoucher);
            const option = new Option(optionText, v.no_voucher, isCurrent, isCurrent);
            if (sisa <= 0 && !isCurrent) option.disabled = true;
            $select.append(option);
        });

        $select.select2({ placeholder: "Pilih voucher", allowClear: true, width: '100%' });
        if (currentVoucher) {
            setTimeout(() => $select.val(currentVoucher).trigger('change'), 300);
        }
    }
    initVoucherSelect();

    // ==================== POPULATE EXISTING SINGLE ITEMS ====================
function populateExistingSingleItems() {
    const mappings = [
        { container: '#kpk-container',       value: "{{ $existingKpk ?? '' }}", field: 'kpk' },
        { container: '#tas-container',       value: "{{ $existingTas ?? '' }}", field: 'tas' },
        { container: '#sertifikat-container',value: "{{ $existingSertifikat ?? '' }}", field: 'sertifikat' },
        { container: '#stpb-container',      value: "{{ $existingStpb ?? '' }}", field: 'stpb' },
        { container: '#rbas-container',      value: "{{ $existingRbas ?? '' }}", field: 'rbas' },
        { container: '#bcabs01-container',   value: "{{ $existingBcabs01 ?? '' }}", field: 'bcabs01' },
        { container: '#bcabs02-container',   value: "{{ $existingBcabs02 ?? '' }}", field: 'bcabs02' }
    ];

    mappings.forEach(item => {
        if (!item.value || item.value == '0') return;

        const $select = $(`${item.container} select`);
        if ($select.length === 0) return;

        let found = false;

        // Coba cocokkan berdasarkan total amount (paling reliable)
        $select.find('option').each(function() {
            const harga = parseInt($(this).data('harga')) || 0;
            const qty   = parseInt($(this).data('qty')) || 0;
            const total = harga * qty;

            // Cocokkan dengan total yang tersimpan di DB
            if (total == parseInt(item.value)) {
                $(this).prop('selected', true);
                found = true;
                return false; // break
            }
        });

        // Fallback: jika tidak ketemu, coba cocokkan kode saja
        if (!found) {
            $select.val(item.value);
        }

        $select.trigger('change');
    });
}

    // ==================== HITUNG TOTAL LAIN-LAIN ====================
    function hitungTotalLain() {
        let totalKaosPendek = 0;
        let totalKaosPanjang = 0;
        let totalKpk = 0;
        let totalTas = 0;
        let totalSertifikat = 0;
        let totalStpb = 0;
        let totalRbas = 0;
        let totalBcabs01 = 0;
        let totalBcabs02 = 0;

        // Kaos Pendek
        $('.kaos-pendek-row').each(function() {
            const harga = parseInt($(this).find('.kaos-pendek-select option:selected').data('harga')) || 0;
            const qty   = parseInt($(this).find('.kaos-pendek-qty').val()) || 0;
            totalKaosPendek += harga * qty;
        });

        // Kaos Panjang
        $('.kaos-panjang-row').each(function() {
            const harga = parseInt($(this).find('.kaos-panjang-select option:selected').data('harga')) || 0;
            const qty   = parseInt($(this).find('.kaos-panjang-qty').val()) || 0;
            totalKaosPanjang += harga * qty;
        });

        // Single Select
        function getTotalFromSelect(container) {
            const selected = $(container + ' select option:selected');
            const harga = parseInt(selected.data('harga')) || 0;
            const qty   = parseInt(selected.data('qty')) || 0;
            return harga * qty;
        }

        totalKpk       = getTotalFromSelect('#kpk-container');
        totalTas       = getTotalFromSelect('#tas-container');
        totalSertifikat= getTotalFromSelect('#sertifikat-container');
        totalStpb      = getTotalFromSelect('#stpb-container');
        totalRbas      = getTotalFromSelect('#rbas-container');
        totalBcabs01   = getTotalFromSelect('#bcabs01-container');
        totalBcabs02   = getTotalFromSelect('#bcabs02-container');

        // Simpan ke hidden field
        $('#kaos_pendek_hidden').val(totalKaosPendek);
        $('#kaos_panjang_hidden').val(totalKaosPanjang);
        $('#kpk_hidden').val(totalKpk);
        $('#tas_hidden').val(totalTas);
        $('#sertifikat_hidden').val(totalSertifikat);
        $('#stpb_hidden').val(totalStpb);
        $('#rbas_hidden').val(totalRbas);
        $('#bcabs01_hidden').val(totalBcabs01);
        $('#bcabs02_hidden').val(totalBcabs02);

        return {
            kaosPendek: totalKaosPendek,
            kaosPanjang: totalKaosPanjang,
            kpk: totalKpk,
            tas: totalTas,
            sertifikat: totalSertifikat,
            stpb: totalStpb,
            rbas: totalRbas,
            bcabs01: totalBcabs01,
            bcabs02: totalBcabs02
        };
    }

    // ==================== HITUNG TOTAL KESELURUHAN ====================
    function hitungTotal() {
        const totalsLain = hitungTotalLain();

        let totalSpp = parseInt($('#spp_dropdown').val()) || 0;
        let voucherTerpilih = $('#voucher').val();
        let diskonVoucher = voucherTerpilih ? voucherDiskon : 0;
        let sppSetelahDiskon = Math.max(0, totalSpp - diskonVoucher);

        let totalDaftar = unformatRupiah($('#daftar_hidden').val() || 0);
        let totalEvent = unformatRupiah($('input[name="event"]').val() || 0);
        let totalLainLain = unformatRupiah($('input[name="lain_lain"]').val() || 0);

        let totalLain = totalsLain.kaosPendek + totalsLain.kaosPanjang + 
                        totalsLain.kpk + totalsLain.tas + totalsLain.sertifikat + 
                        totalsLain.stpb + totalsLain.rbas + totalsLain.bcabs01 + 
                        totalsLain.bcabs02 + totalEvent + totalLainLain;

        let grandTotal = sppSetelahDiskon + totalDaftar + totalLain;

        $('#total_spp').val(formatRupiah(sppSetelahDiskon));
        $('#total_daftar').val(formatRupiah(totalDaftar));
        $('#total_lain').val(formatRupiah(totalLain));
        $('#grand_total').val(formatRupiah(grandTotal));
    }

    function hitungDaftar() {
        const kode = $('#daftar_select').val();
        const tipe = $('#daftar_tipe_harga').val();
        let harga = 0;

        if (kode && tipe) {
            let dataKey = tipe.replace('harga_', 'harga-');
            if (tipe === 'harga_promo_umum') dataKey = 'harga-promo_umum';
            harga = parseFloat($('#daftar_select option:selected').data(dataKey)) || 0;
        } else {
            harga = unformatRupiah($('#daftar_hidden').val());
        }

        $('#daftar_hidden').val(harga);
        $('#total_daftar').val(formatRupiah(harga || 0));
        hitungTotal();
    }

    // ==================== POPULATE KAOS ====================
    function addExistingKaos() {
        const pendekString = "{{ $existingKaosPendek ?? '' }}";
        const panjangString = "{{ $existingKaosPanjang ?? '' }}";

        if (pendekString.trim() !== '') {
            const sizes = pendekString.split(',').map(s => s.trim()).filter(s => s !== '');
            sizes.forEach(function(size) {
                let options = '';
                @foreach($kaosPendekList as $kaos)
                    options += `<option value="{{ $kaos['kode'] }}" data-harga="{{ $kaos['harga'] }}" 
                        ${size === "{{ $kaos['kode'] }}" ? 'selected' : ''}>
                        {{ $kaos['kode'] }}
                    </option>`;
                @endforeach

                const row = `
                    <div class="kaos-pendek-row d-flex gap-2 mb-2 align-items-end">
                        <select name="kaos_pendek_kode[]" class="form-select kaos-pendek-select" style="width: 60%;">
                            <option value="">-- Pilih Ukuran --</option>
                            ${options}
                        </select>
                        <input type="number" name="kaos_pendek_qty[]" class="form-control kaos-pendek-qty" value="1" min="0" style="width: 80px;">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-kaos-pendek">Hapus</button>
                    </div>`;
                $('#kaos-pendek-container').append(row);
            });
        }

        if (panjangString.trim() !== '') {
            const sizes = panjangString.split(',').map(s => s.trim()).filter(s => s !== '');
            sizes.forEach(function(size) {
                let options = '';
                @foreach($kaosPanjangList as $kaos)
                    options += `<option value="{{ $kaos['kode'] }}" data-harga="{{ $kaos['harga'] }}" 
                        ${size === "{{ $kaos['kode'] }}" ? 'selected' : ''}>
                        {{ $kaos['kode'] }}
                    </option>`;
                @endforeach

                const row = `
                    <div class="kaos-panjang-row d-flex gap-2 mb-2 align-items-end">
                        <select name="kaos_panjang_kode[]" class="form-select kaos-panjang-select" style="width: 60%;">
                            <option value="">-- Pilih Ukuran --</option>
                            ${options}
                        </select>
                        <input type="number" name="kaos_panjang_qty[]" class="form-control kaos-panjang-qty" value="1" min="0" style="width: 80px;">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-kaos-panjang">Hapus</button>
                    </div>`;
                $('#kaos-panjang-container').append(row);
            });
        }
    }

    // ==================== INIT ALL ====================
function initAll() {
    addExistingKaos();           // ini sudah ada
    setTimeout(() => {
        populateExistingSingleItems();
        hitungDaftar();
        hitungTotal();
        hitungTotalKaos();       // ← Tambahkan ini
    }, 1000);
}

    initAll();

    // ==================== EVENT LISTENERS ====================
    $('#daftar_select, #daftar_tipe_harga').on('change', hitungDaftar);
    $('#voucher, #spp_dropdown, input[name="event"], input[name="lain_lain"]').on('change keyup', hitungTotal);

    $('#kpk-container, #tas-container, #sertifikat-container, #stpb-container, #rbas-container, #bcabs01-container, #bcabs02-container')
        .on('change', 'select', hitungTotal);

    $('#kaos-pendek-container, #kaos-panjang-container')
        .on('change', 'select, input[type="number"]', hitungTotal);

});

// ==================== HANDLE MULTIPLE KAOS (Tambah & Hapus) ====================
function hitungTotalKaos() {
    let totalPendek = 0;
    let totalPanjang = 0;

    // Kaos Pendek
    $('.kaos-pendek-row').each(function() {
        const harga = parseInt($(this).find('.kaos-pendek-select option:selected').data('harga')) || 0;
        const qty   = parseInt($(this).find('.kaos-pendek-qty').val()) || 0;
        totalPendek += harga * qty;
    });

    // Kaos Panjang
    $('.kaos-panjang-row').each(function() {
        const harga = parseInt($(this).find('.kaos-panjang-select option:selected').data('harga')) || 0;
        const qty   = parseInt($(this).find('.kaos-panjang-qty').val()) || 0;
        totalPanjang += harga * qty;
    });

    $('#kaos_pendek_hidden').val(totalPendek);
    $('#kaos_panjang_hidden').val(totalPanjang);

    hitungTotal();           // update grand total
}

// ==================== TAMBAH & HAPUS BARIS KAOS PENDEK ====================
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

$(document).on('click', '.btn-remove-kaos-pendek', function() {
    if ($('.kaos-pendek-row').length > 1) {
        $(this).closest('.kaos-pendek-row').remove();
        hitungTotalKaos();
    }
});

// ==================== TAMBAH & HAPUS BARIS KAOS PANJANG ====================
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

$(document).on('click', '.btn-remove-kaos-panjang', function() {
    if ($('.kaos-panjang-row').length > 1) {
        $(this).closest('.kaos-panjang-row').remove();
        hitungTotalKaos();
    }
});

// Event change untuk kaos
$('#kaos-pendek-container, #kaos-panjang-container')
    .on('change', 'select, input[type="number"]', hitungTotalKaos);
</script>+

@endsection