@extends('layouts.app')

@section('title', 'Tambah Penerimaan Produk')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0 text-center">Tambah Penerimaan Produk</h4>
                </div>
                <div class="card-body p-4">

                    <form action="{{ route('penerimaan_produk.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <!-- Faktur & Tanggal -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">No. Faktur <span class="text-danger">*</span></label>
                                <input type="text" name="faktur" class="form-control" value="{{ old('faktur') }}" required>
                                @error('faktur') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Penerimaan <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" id="tanggal" class="form-control" 
                                       value="{{ old('tanggal', now()->format('Y-m-d')) }}" required>
                                @error('tanggal') <small class="text-danger">{{ $message }}</small> @enderror
                                <small class="text-muted d-block mt-2">
                                    <strong>Minggu Otomatis:</strong> 
                                    <span id="info_minggu" class="text-primary fw-bold">
                                        Minggu {{ now()->weekOfMonth ?? 1 }}
                                    </span>
                                </small>
                            </div>

                            <!-- Unit biMBA -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Unit biMBA Tujuan <span class="text-danger">*</span></label>
                                <select name="unit_id" class="form-select" required>
                                    <option value="">-- Pilih Unit --</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_id') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <!-- Produk -->
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Produk <span class="text-danger">*</span></label>
                                <select name="label" id="label" class="form-select" required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($produks as $p)
                                        <option value="{{ $p->label }}"
                                                data-kode="{{ $p->kode }}"
                                                data-jenis="{{ $p->jenis }}"
                                                data-kategori="{{ $p->kategori ?? '' }}"
                                                data-nama="{{ $p->label }}"
                                                data-satuan="{{ $p->satuan }}"
                                                data-harga="{{ $p->harga }}"
                                                data-status="{{ $p->status ?? '' }}"
                                                data-isi="{{ $p->isi ?? '' }}">
                                            {{ $p->kode }} - {{ $p->label }} ({{ $p->jenis }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('label') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <!-- Jumlah -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Jumlah <span class="text-danger">*</span></label>
                                <input type="number" name="jumlah" id="jumlah" class="form-control text-end" min="1" value="{{ old('jumlah', 1) }}" required>
                            </div>

                            <!-- Harga Satuan & Total -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Harga Satuan (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="harga" id="harga" class="form-control text-end" min="0" value="0" readonly>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Total (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="total" class="form-control text-end fw-bold bg-light" readonly value="0">
                                </div>
                            </div>

                            <!-- Field Otomatis (Tampil + Hidden) -->
                            <div class="col-md-3">
                                <label class="form-label">Kategori</label>
                                <input type="text" id="kategori" class="form-control bg-light" readonly>
                                <input type="hidden" name="kategori" id="kategori_hidden">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Jenis</label>
                                <input type="text" id="jenis" class="form-control bg-light" readonly>
                                <input type="hidden" name="jenis" id="jenis_hidden">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Nama Produk</label>
                                <input type="text" id="nama_produk" class="form-control bg-light" readonly>
                                <input type="hidden" name="nama_produk" id="nama_produk_hidden">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Satuan</label>
                                <input type="text" id="satuan" class="form-control bg-light" readonly>
                                <input type="hidden" name="satuan" id="satuan_hidden">
                            </div>

                            <!-- Status & Isi (Otomatis dari produk, tapi bisa diubah manual) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status</label>
                                <input type="text" name="status" id="status" class="form-control" 
                                       value="{{ old('status') }}" placeholder="Otomatis dari produk">
                                <small class="text-muted">Default dari master produk, bisa diubah</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Isi / Keterangan Tambahan</label>
                                <textarea name="isi" id="isi" class="form-control" rows="3" 
                                          placeholder="Otomatis dari produk">{{ old('isi') }}</textarea>
                                <small class="text-muted">Default dari master produk, bisa ditambah/diedit</small>
                            </div>
                        </div>

                        <div class="mt-5 text-end">
                            <a href="{{ route('penerimaan_produk.index') }}" class="btn btn-secondary btn-lg px-4">
                                Kembali
                            </a>
                            <button type="submit" class="btn btn-success btn-lg px-5 ms-3">
                                Simpan Penerimaan
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
    const selectLabel = document.getElementById('label');
    const inputTanggal = document.getElementById('tanggal');
    const infoMinggu = document.getElementById('info_minggu');
    const statusInput = document.getElementById('status');
    const isiTextarea = document.getElementById('isi');

    const inputs = {
        kategori: document.getElementById('kategori'),
        kategoriHidden: document.getElementById('kategori_hidden'),
        jenis: document.getElementById('jenis'),
        jenisHidden: document.getElementById('jenis_hidden'),
        namaProduk: document.getElementById('nama_produk'),
        namaProdukHidden: document.getElementById('nama_produk_hidden'),
        satuan: document.getElementById('satuan'),
        satuanHidden: document.getElementById('satuan_hidden'),
        harga: document.getElementById('harga'),
        jumlah: document.getElementById('jumlah'),
        total: document.getElementById('total')
    };

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    function hitungTotal() {
        const jumlah = parseFloat(inputs.jumlah.value) || 0;
        const harga = parseFloat(inputs.harga.value) || 0;
        const total = jumlah * harga;
        inputs.total.value = total ? formatRupiah(total) : '0';
    }

    function hitungMinggu() {
        if (!inputTanggal.value) {
            infoMinggu.textContent = 'Minggu -';
            return;
        }
        const date = new Date(inputTanggal.value);
        const hari = date.getDate();
        const minggu = Math.min(Math.ceil(hari / 7), 5);
        infoMinggu.textContent = 'Minggu ' + minggu;
    }

    selectLabel.addEventListener('change', function () {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            // Field otomatis
            inputs.kategori.value = option.dataset.kategori || '';
            inputs.kategoriHidden.value = option.dataset.kategori || '';
            inputs.jenis.value = option.dataset.jenis || '';
            inputs.jenisHidden.value = option.dataset.jenis || '';
            inputs.namaProduk.value = option.dataset.nama || '';
            inputs.namaProdukHidden.value = option.dataset.nama || '';
            inputs.satuan.value = option.dataset.satuan || '';
            inputs.satuanHidden.value = option.dataset.satuan || '';
            inputs.harga.value = option.dataset.harga || 0;

            // Status & Isi dari produk
            statusInput.value = option.dataset.status || '';
            isiTextarea.value = option.dataset.isi || '';
        } else {
            // Reset semua
            Object.values(inputs).forEach(input => {
                if (input) input.value = '';
            });
            inputs.harga.value = 0;
            statusInput.value = '';
            isiTextarea.value = '';
        }
        hitungTotal();
    });

    // Event listener
    inputTanggal.addEventListener('change', hitungMinggu);
    inputs.jumlah.addEventListener('input', hitungTotal);
    inputs.harga.addEventListener('input', hitungTotal);

    // Inisialisasi saat load
    hitungMinggu();
    hitungTotal();

    if (selectLabel.value) {
        selectLabel.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection