@extends('layouts.app')

@section('title', 'Tambah Pemakaian Produk')

@section('content')

@php
    $user = auth()->user();

    // CEK ADMIN
    $isAdmin = $user && $user->role === 'admin';

    $userUnit = $user->bimba_unit ?? null;

    $defaultUnitId = null;
    $unitDisplay = 'Unit belum diatur';

    // KHUSUS NON ADMIN
    if (!$isAdmin && $userUnit) {

        $unit = \App\Models\Unit::where('biMBA_unit', $userUnit)->first();

        if ($unit) {
            $defaultUnitId = $unit->id;
            $unitDisplay = $unit->no_cabang . ' | ' . strtoupper($unit->biMBA_unit);
        }
    }
@endphp

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-11">

            <div class="card shadow-sm">

                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0 text-center">
                        Tambah Pemakaian Produk
                    </h4>
                </div>

                <div class="card-body p-4">

                    @if (!$isAdmin && !$defaultUnitId)
                        <div class="alert alert-danger text-center">
                            <strong>Unit Anda belum diatur!</strong><br>
                            Hubungi admin untuk mengatur unit pada profile user.
                        </div>
                    @endif

                    <form action="{{ route('pemakaian_produk.store') }}"
                          method="POST"
                          id="pemakaianForm">

                        @csrf

                        {{-- ========================= --}}
                        {{-- UNIT --}}
                        {{-- ========================= --}}

                        @if($isAdmin)

                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    Unit biMBA
                                    <span class="text-danger">*</span>
                                </label>

                                <select name="unit_id"
                                        id="unit_id"
                                        class="form-select"
                                        required>

                                    <option value="">
                                        -- Pilih Unit --
                                    </option>

                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}"
                                            {{ old('unit_id') == $unit->id ? 'selected' : '' }}>

                                            {{ $unit->no_cabang }}
                                            |
                                            {{ strtoupper($unit->biMBA_unit) }}

                                        </option>
                                    @endforeach
                                </select>

                                @error('unit_id')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror
                            </div>

                        @else

                            <input type="hidden"
                                   name="unit_id"
                                   id="unit_id"
                                   value="{{ old('unit_id', $defaultUnitId) }}">

                            <div class="mb-4 text-center">

                                <label class="form-label fw-bold d-block">
                                    Unit biMBA Anda
                                </label>

                                <div class="badge bg-primary fs-5 px-4 py-2">
                                    {{ $unitDisplay }}
                                </div>

                                <small class="text-muted d-block mt-2">
                                    Unit otomatis dari profile
                                </small>
                            </div>

                        @endif

                        <div class="row g-3">

                            {{-- ========================= --}}
                            {{-- TANGGAL --}}
                            {{-- ========================= --}}

                            <div class="col-md-6">

                                <label class="form-label fw-bold">
                                    Tanggal Pemakaian
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="date"
                                       name="tanggal"
                                       id="tanggal"
                                       class="form-control"
                                       value="{{ old('tanggal', now()->format('Y-m-d')) }}"
                                       required>

                                <small class="text-muted d-block mt-2">
                                    <strong>Minggu Otomatis:</strong>

                                    <span id="info_minggu"
                                          class="text-primary fw-bold">
                                        Minggu {{ min(ceil(now()->day / 7), 5) }}
                                    </span>
                                </small>
                            </div>

                            {{-- ========================= --}}
                            {{-- DATA MURID --}}
                            {{-- ========================= --}}

                            <div class="col-md-3">
                                <label class="form-label fw-bold">NIM</label>

                                <input type="text"
                                       id="nim"
                                       class="form-control"
                                       readonly
                                       placeholder="Otomatis terisi">
                            </div>

                            <div class="col-md-5">
                                <label class="form-label fw-bold">
                                    Nama Murid
                                </label>

                                <input type="text"
                                       id="nama_murid"
                                       class="form-control"
                                       readonly
                                       placeholder="Otomatis terisi">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-bold">Gol</label>

                                <input type="text"
                                       id="gol"
                                       class="form-control"
                                       readonly>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-bold">Guru</label>

                                <input type="text"
                                       id="guru"
                                       class="form-control"
                                       readonly>
                            </div>

                            {{-- ========================= --}}
                            {{-- MURID --}}
                            {{-- ========================= --}}

                            <div class="col-md-12">

                                <label class="form-label fw-bold">
                                    Pilih Murid
                                    <span class="text-danger">*</span>
                                </label>

                                <select name="murid_id"
                                        id="murid_dropdown"
                                        class="form-select"
                                        required>

                                    <option value="">
                                        -- Pilih Murid --
                                    </option>

                                </select>

                                <small class="text-muted">
                                    Murid aktif sesuai unit akan muncul otomatis
                                </small>
                            </div>

                            {{-- ========================= --}}
                            {{-- PRODUK --}}
                            {{-- ========================= --}}

                            <div class="col-md-8">

                                <label class="form-label fw-bold">
                                    Produk
                                    <span class="text-danger">*</span>
                                </label>

                                <select name="label"
                                        id="produk_dropdown"
                                        class="form-select"
                                        required>

                                    <option value="">
                                        -- Pilih Produk --
                                    </option>

                                </select>
                            </div>

                            <div class="col-md-4">

                                <label class="form-label fw-bold">
                                    Jumlah
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="number"
                                       name="jumlah"
                                       id="jumlah"
                                       class="form-control text-end"
                                       value="1"
                                       min="1"
                                       required>
                            </div>

                            {{-- ========================= --}}
                            {{-- HARGA --}}
                            {{-- ========================= --}}

                            <div class="col-md-4">

                                <label class="form-label">
                                    Harga Satuan (Rp)
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        Rp
                                    </span>

                                    <input type="number"
                                           name="harga"
                                           id="harga"
                                           class="form-control text-end"
                                           readonly
                                           value="0">
                                </div>
                            </div>

                            <div class="col-md-4">

                                <label class="form-label">
                                    Total (Rp)
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        Rp
                                    </span>

                                    <input type="text"
                                           id="total"
                                           class="form-control text-end fw-bold bg-light"
                                           readonly
                                           value="0">
                                </div>
                            </div>

                            {{-- ========================= --}}
                            {{-- DETAIL PRODUK --}}
                            {{-- ========================= --}}

                            <div class="col-md-3">
                                <label class="form-label">Kategori</label>

                                <input type="text"
                                       id="kategori"
                                       class="form-control bg-light"
                                       readonly>

                                <input type="hidden"
                                       name="kategori"
                                       id="kategori_hidden">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Jenis</label>

                                <input type="text"
                                       id="jenis"
                                       class="form-control bg-light"
                                       readonly>

                                <input type="hidden"
                                       name="jenis"
                                       id="jenis_hidden">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Nama Produk</label>

                                <input type="text"
                                       id="nama_produk"
                                       class="form-control bg-light"
                                       readonly>

                                <input type="hidden"
                                       name="nama_produk"
                                       id="nama_produk_hidden">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Satuan</label>

                                <input type="text"
                                       id="satuan"
                                       class="form-control bg-light"
                                       readonly>

                                <input type="hidden"
                                       name="satuan"
                                       id="satuan_hidden">
                            </div>
                        </div>

                        {{-- ========================= --}}
                        {{-- BUTTON --}}
                        {{-- ========================= --}}

                        <div class="mt-5 text-end">

                            <a href="{{ route('pemakaian_produk.index') }}"
                               class="btn btn-secondary btn-lg px-4">

                                Kembali
                            </a>

                            <button type="submit"
                                    class="btn btn-success btn-lg px-5 ms-3"
                                    id="submitBtn">

                                Simpan Pemakaian
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================= --}}
{{-- SELECT2 --}}
{{-- ========================= --}}

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
      rel="stylesheet" />

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>

window.isAdmin = @json($isAdmin);
window.defaultUnitId = @json($defaultUnitId);

document.addEventListener('DOMContentLoaded', function () {

    const unitIdInput    = document.getElementById('unit_id');
    const muridDropdown  = document.getElementById('murid_dropdown');
    const produkDropdown = document.getElementById('produk_dropdown');

    const nimInput       = document.getElementById('nim');
    const namaMuridInput = document.getElementById('nama_murid');
    const golInput       = document.getElementById('gol');
    const guruInput      = document.getElementById('guru');

    const inputTanggal   = document.getElementById('tanggal');
    const infoMinggu     = document.getElementById('info_minggu');

    const inputs = {

        harga: document.getElementById('harga'),
        jumlah: document.getElementById('jumlah'),
        total: document.getElementById('total'),

        kategori: document.getElementById('kategori'),
        kategoriHidden: document.getElementById('kategori_hidden'),

        jenis: document.getElementById('jenis'),
        jenisHidden: document.getElementById('jenis_hidden'),

        namaProduk: document.getElementById('nama_produk'),
        namaProdukHidden: document.getElementById('nama_produk_hidden'),

        satuan: document.getElementById('satuan'),
        satuanHidden: document.getElementById('satuan_hidden')
    };

    // =========================
    // SELECT2
    // =========================

    $('#murid_dropdown').select2({
        placeholder: '-- Pilih Murid --',
        allowClear: true,
        width: '100%'
    });

    $('#produk_dropdown').select2({
        placeholder: '-- Pilih Produk --',
        allowClear: true,
        width: '100%'
    });

    // =========================
    // FORMAT RUPIAH
    // =========================

    function formatRupiah(angka) {

        return new Intl.NumberFormat('id-ID').format(angka);
    }

    // =========================
    // HITUNG TOTAL
    // =========================

    function hitungTotal() {

        const jumlah = parseFloat(inputs.jumlah.value) || 0;
        const harga  = parseFloat(inputs.harga.value) || 0;

        inputs.total.value = formatRupiah(jumlah * harga);
    }

    // =========================
    // HITUNG MINGGU
    // =========================

    function hitungMinggu() {

        if (!inputTanggal.value) return;

        const date = new Date(inputTanggal.value);

        const hari = date.getDate();

        const minggu = Math.min(
            Math.ceil(hari / 7),
            5
        );

        infoMinggu.textContent = 'Minggu ' + minggu;
    }

    // =========================
    // LOAD DATA
    // =========================

    function loadMuridDanProduk() {

        let unitId = '';

        if (window.isAdmin) {

            unitId = unitIdInput.value;

        } else {

            unitId = window.defaultUnitId;
        }

        console.log('UNIT ID:', unitId);

        // RESET
        muridDropdown.innerHTML =
            '<option value="">-- Pilih Murid --</option>';

        produkDropdown.innerHTML =
            '<option value="">-- Pilih Produk --</option>';

        if (!unitId) {

            $('#murid_dropdown').trigger('change');
            $('#produk_dropdown').trigger('change');

            return;
        }

        // =========================
        // LOAD MURID
        // =========================

        fetch(`/pemakaian-produk/murid/${unitId}`)

            .then(response => response.json())

            .then(murids => {

                murids.forEach(murid => {

                    const option = document.createElement('option');

                    option.value = murid.id;

                    option.textContent =
                        `${murid.nim || 'Tanpa NIM'} | ${murid.nama}`;

                    option.dataset.nim   = murid.nim || '';
                    option.dataset.nama  = murid.nama || '';
                    option.dataset.gol   = murid.gol || '';
                    option.dataset.guru  = murid.guru || '';

                    muridDropdown.appendChild(option);
                });

                $('#murid_dropdown').val(null).trigger('change');

                console.log('MURID:', murids);
            })

            .catch(error => {

                console.log(error);
            });

        // =========================
        // LOAD PRODUK
        // =========================

        fetch(`/pemakaian-produk/produk/${unitId}`)

            .then(response => response.json())

            .then(produks => {

                produks.forEach(p => {

                    const option = document.createElement('option');

                    option.value = p.label;

                    option.textContent =
                        `${p.label} (${p.nama_produk || p.label})`;

                    option.dataset.kategori   = p.kategori || '';
                    option.dataset.jenis      = p.jenis || '';
                    option.dataset.namaProduk = p.nama_produk || '';
                    option.dataset.satuan     = p.satuan || '';
                    option.dataset.harga      = p.harga || 0;

                    produkDropdown.appendChild(option);
                });

                $('#produk_dropdown').val(null).trigger('change');
            })

            .catch(error => {

                console.log(error);
            });
    }

    // =========================
    // GANTI UNIT
    // =========================

    if (window.isAdmin) {

        unitIdInput.addEventListener('change', function () {

            // RESET DATA MURID

            $('#murid_dropdown').val(null).trigger('change');

            nimInput.value = '';
            namaMuridInput.value = '';
            golInput.value = '';
            guruInput.value = '';

            loadMuridDanProduk();
        });
    }

    // =========================
    // PILIH MURID
    // =========================

    $('#murid_dropdown').on('change', function () {

        const option = this.options[this.selectedIndex];

        if (option && option.value) {

            nimInput.value =
                option.dataset.nim || '';

            namaMuridInput.value =
                option.dataset.nama || '';

            golInput.value =
                option.dataset.gol || '';

            guruInput.value =
                option.dataset.guru || '';

        } else {

            nimInput.value = '';
            namaMuridInput.value = '';
            golInput.value = '';
            guruInput.value = '';
        }
    });

    // =========================
    // PILIH PRODUK
    // =========================

    $('#produk_dropdown').on('change', function () {

        const option = this.options[this.selectedIndex];

        if (option && option.value) {

            inputs.kategori.value =
                option.dataset.kategori || '';

            inputs.kategoriHidden.value =
                option.dataset.kategori || '';

            inputs.jenis.value =
                option.dataset.jenis || '';

            inputs.jenisHidden.value =
                option.dataset.jenis || '';

            inputs.namaProduk.value =
                option.dataset.namaProduk || '';

            inputs.namaProdukHidden.value =
                option.dataset.namaProduk || '';

            inputs.satuan.value =
                option.dataset.satuan || '';

            inputs.satuanHidden.value =
                option.dataset.satuan || '';

            inputs.harga.value =
                option.dataset.harga || 0;

        } else {

            inputs.kategori.value = '';
            inputs.kategoriHidden.value = '';

            inputs.jenis.value = '';
            inputs.jenisHidden.value = '';

            inputs.namaProduk.value = '';
            inputs.namaProdukHidden.value = '';

            inputs.satuan.value = '';
            inputs.satuanHidden.value = '';

            inputs.harga.value = 0;
        }

        hitungTotal();
    });

    // =========================
    // EVENTS
    // =========================

    inputTanggal.addEventListener(
        'change',
        hitungMinggu
    );

    inputs.jumlah.addEventListener(
        'input',
        hitungTotal
    );

    // =========================
    // INIT
    // =========================

    hitungMinggu();

    hitungTotal();

    loadMuridDanProduk();
});
</script>

@endsection