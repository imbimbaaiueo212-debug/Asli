@extends('layouts.app')

@section('title', 'Edit Pemakaian Produk')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0 text-center">Edit Pemakaian Produk</h4>
                </div>

                <div class="card-body p-4">

                    <form action="{{ route('pemakaian_produk.update', $item->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">

                            <!-- TANGGAL -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Tanggal Pemakaian
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="date"
                                       name="tanggal"
                                       id="tanggal"
                                       class="form-control"
                                       value="{{ old('tanggal', $item->tanggal ? \Illuminate\Support\Carbon::parse($item->tanggal)->format('Y-m-d') : '') }}"
                                       required>

                                @error('tanggal')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror

                                <small class="text-muted d-block mt-2">
                                    <strong>Minggu Otomatis:</strong>

                                    <span id="info_minggu"
                                          class="text-primary fw-bold">
                                        Minggu
                                        {{
                                            $item->tanggal
                                                ? min(
                                                    ceil(
                                                        \Illuminate\Support\Carbon::parse($item->tanggal)->day / 7
                                                    ),
                                                    5
                                                )
                                                : '-'
                                        }}
                                    </span>
                                </small>
                            </div>

                            <!-- UNIT -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Unit biMBA
                                    <span class="text-danger">*</span>
                                </label>

                                <select name="unit_id"
                                        id="unit_id"
                                        class="form-select"
                                        required>

                                    <option value="">-- Pilih Unit --</option>

                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}"
                                            {{ old('unit_id', $item->unit_id) == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->no_cabang }} |
                                            {{ strtoupper($unit->biMBA_unit) }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('unit_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- NIM -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">NIM</label>

                                <input type="text"
                                       id="nim"
                                       class="form-control bg-light"
                                       readonly
                                       value="{{ old('nim', $item->nim) }}">
                            </div>

                            <!-- NAMA MURID -->
                            <div class="col-md-5">
                                <label class="form-label fw-bold">
                                    Nama Murid
                                </label>

                                <input type="text"
                                       id="nama_murid"
                                       class="form-control bg-light"
                                       readonly
                                       value="{{ old('nama_murid', $item->nama_murid) }}">
                            </div>

                            <!-- GOL -->
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Gol</label>

                                <input type="text"
                                       id="gol"
                                       class="form-control bg-light"
                                       readonly
                                       value="{{ old('gol', $item->gol) }}">
                            </div>

                            <!-- GURU -->
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Guru</label>

                                <input type="text"
                                       id="guru"
                                       class="form-control bg-light"
                                       readonly
                                       value="{{ old('guru', $item->guru) }}">
                            </div>

                            <!-- MURID READONLY -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold">
                                    Murid
                                </label>

                                <!-- tetap dikirim -->
                                <input type="hidden"
                                       name="murid_id"
                                       value="{{ $item->murid_id }}">

                                <input type="text"
                                       class="form-control bg-light"
                                       readonly
                                       value="{{ $item->nim }} | {{ $item->nama_murid }}">

                                <small class="text-muted">
                                    Murid tidak dapat diubah setelah pemakaian dibuat
                                </small>
                            </div>

                            <!-- PRODUK -->
                            <div class="col-md-8">
                                <label class="form-label fw-bold">
                                    Produk
                                    <span class="text-danger">*</span>
                                </label>

                                <select name="label"
                                        id="produk_dropdown"
                                        class="form-select"
                                        required>

                                    <option value="">-- Pilih Produk --</option>
                                </select>

                                <small class="text-muted">
                                    Produk tersedia di unit ini
                                </small>
                            </div>

                            <!-- JUMLAH -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold">
                                    Jumlah
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="number"
                                       name="jumlah"
                                       id="jumlah"
                                       class="form-control text-end"
                                       min="1"
                                       value="{{ old('jumlah', $item->jumlah) }}"
                                       required>
                            </div>

                            <!-- HARGA -->
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
                                           value="{{ old('harga', $item->harga) }}">
                                </div>
                            </div>

                            <!-- TOTAL -->
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
                                           value="{{ old('total', number_format($item->total, 0, ',', '.')) }}">
                                </div>
                            </div>

                            <!-- KATEGORI -->
                            <div class="col-md-3">
                                <label class="form-label">Kategori</label>

                                <input type="text"
                                       id="kategori"
                                       class="form-control bg-light"
                                       readonly
                                       value="{{ old('kategori', $item->kategori) }}">

                                <input type="hidden"
                                       name="kategori"
                                       id="kategori_hidden"
                                       value="{{ old('kategori', $item->kategori) }}">
                            </div>

                            <!-- JENIS -->
                            <div class="col-md-3">
                                <label class="form-label">Jenis</label>

                                <input type="text"
                                       id="jenis"
                                       class="form-control bg-light"
                                       readonly
                                       value="{{ old('jenis', $item->jenis) }}">

                                <input type="hidden"
                                       name="jenis"
                                       id="jenis_hidden"
                                       value="{{ old('jenis', $item->jenis) }}">
                            </div>

                            <!-- NAMA PRODUK -->
                            <div class="col-md-3">
                                <label class="form-label">
                                    Nama Produk
                                </label>

                                <input type="text"
                                       id="nama_produk"
                                       class="form-control bg-light"
                                       readonly
                                       value="{{ old('nama_produk', $item->nama_produk) }}">

                                <input type="hidden"
                                       name="nama_produk"
                                       id="nama_produk_hidden"
                                       value="{{ old('nama_produk', $item->nama_produk) }}">
                            </div>

                            <!-- SATUAN -->
                            <div class="col-md-3">
                                <label class="form-label">Satuan</label>

                                <input type="text"
                                       id="satuan"
                                       class="form-control bg-light"
                                       readonly
                                       value="{{ old('satuan', $item->satuan) }}">

                                <input type="hidden"
                                       name="satuan"
                                       id="satuan_hidden"
                                       value="{{ old('satuan', $item->satuan) }}">
                            </div>

                        </div>

                        <div class="mt-5 text-end">

                            <a href="{{ route('pemakaian_produk.index') }}"
                               class="btn btn-secondary btn-lg px-4">
                                Kembali
                            </a>

                            <button type="submit"
                                    class="btn btn-warning btn-lg px-5 ms-3">
                                Update Pemakaian
                            </button>

                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const unitSelect     = document.getElementById('unit_id');
    const produkDropdown = document.getElementById('produk_dropdown');

    const inputTanggal = document.getElementById('tanggal');
    const infoMinggu   = document.getElementById('info_minggu');

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

    const existingLabel = @json($item->label);

    // =========================
    // FORMAT RUPIAH
    // =========================
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka || 0);
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

        if (!inputTanggal.value) {
            infoMinggu.textContent = 'Minggu -';
            return;
        }

        const date = new Date(inputTanggal.value);

        const hari = date.getDate();

        const minggu = Math.min(
            Math.ceil(hari / 7),
            5
        );

        infoMinggu.textContent =
            'Minggu ' + minggu;
    }

    // =========================
    // LOAD PRODUK
    // =========================
    function loadProduk(unitId) {

        if (!unitId) {

            produkDropdown.innerHTML =
                '<option value="">-- Pilih Unit dulu --</option>';

            return;
        }

        fetch(`/pemakaian-produk/produk/${unitId}`)
            .then(response => response.json())

            .then(produks => {

                produkDropdown.innerHTML =
                    '<option value="">-- Pilih Produk --</option>';

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

                    if (p.label == existingLabel) {
                        option.selected = true;
                    }

                    produkDropdown.appendChild(option);
                });

                // trigger isi otomatis
                produkDropdown.dispatchEvent(
                    new Event('change')
                );
            })

            .catch(() => {

                produkDropdown.innerHTML =
                    '<option value="">Gagal memuat produk</option>';
            });
    }

    // =========================
    // PILIH PRODUK
    // =========================
    produkDropdown.addEventListener('change', function () {

        const option =
            this.options[this.selectedIndex];

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
    // EVENT
    // =========================
    unitSelect.addEventListener('change', function () {

        loadProduk(this.value);
    });

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

    if (unitSelect.value) {
        loadProduk(unitSelect.value);
    }

});
</script>
@endsection