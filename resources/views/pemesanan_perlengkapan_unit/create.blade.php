@extends('layouts.app')

@section('title', 'Tambah Pemesanan Perlengkapan Unit')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-11 col-lg-9">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-gradient-primary text-black text-center py-4">
                    <h3 class="mb-0">
                        <i class="fas fa-box-open me-2"></i>
                        Tambah Pemesanan Perlengkapan Unit
                    </h3>
                </div>
                <div class="card-body p-5">

                    @php
                        $isAdmin = auth()->check() && (auth()->user()->is_admin ?? false);
                        $userUnit = auth()->user()->bimba_unit ?? null;
                        $defaultUnitId = null;
                        $unitDisplay = 'Unit belum diatur';

                        if (!$isAdmin && $userUnit) {
                            $unit = \App\Models\Unit::where('biMBA_unit', $userUnit)->first();
                            if ($unit) {
                                $defaultUnitId = $unit->id;
                                $unitDisplay = $unit->no_cabang . ' | ' . strtoupper($unit->biMBA_unit);
                            }
                        }
                    @endphp

                    @if (!$isAdmin && !$defaultUnitId)
                        <div class="alert alert-danger mb-4 text-center">
                            <strong>Unit Anda belum diatur!</strong><br>
                            Hubungi admin untuk mengatur unit di profile agar bisa menambah pemesanan perlengkapan.
                        </div>
                    @endif

                    <form action="{{ route('pemesanan_perlengkapan_unit.store') }}" method="POST" id="formPemesanan">
                        @csrf

                        <!-- Hidden Unit (otomatis untuk non-admin) -->
                        @if (!$isAdmin)
                            <input type="hidden" name="unit_id" id="unit_id" value="{{ old('unit_id', $defaultUnitId) }}" required>
                        @endif

                        <!-- Unit biMBA – HANYA UNTUK ADMIN -->
                        @if ($isAdmin)
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-primary">
                                        Unit biMBA <span class="text-danger">*</span>
                                    </label>
                                    <select name="unit_id" id="unit_id" class="form-select form-select-lg @error('unit_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Unit biMBA --</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->no_cabang }} | {{ strtoupper($unit->biMBA_unit) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('unit_id')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Tanggal Pemesanan + Preview Minggu -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-primary">
                                        Tanggal Pemesanan <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="tanggal_pemesanan" id="tanggal_pemesanan"
                                           class="form-control form-control-lg @error('tanggal_pemesanan') is-invalid @enderror"
                                           value="{{ old('tanggal_pemesanan', date('Y-m-d')) }}" required
                                           onchange="hitungMingguPreview(this.value)">

                                    @error('tanggal_pemesanan')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror

                                    <div class="mt-3 p-3 bg-light rounded border text-center">
                                        <small class="text-muted d-block">Minggu Otomatis</small>
                                        <strong id="preview_minggu" class="text-primary fs-4">-</strong>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Non-admin: info unit otomatis + tanggal -->
                            @if ($defaultUnitId)
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-primary">Unit biMBA Anda</label>
                                        <div class="alert alert-info mb-0 py-3 text-center">
                                            <strong>{{ $unitDisplay }}</strong>
                                        </div>
                                        <small class="text-muted d-block mt-2">Unit otomatis dari profile (tidak bisa diubah)</small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-primary">
                                            Tanggal Pemesanan <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" name="tanggal_pemesanan" id="tanggal_pemesanan"
                                               class="form-control form-control-lg @error('tanggal_pemesanan') is-invalid @enderror"
                                               value="{{ old('tanggal_pemesanan', date('Y-m-d')) }}" required
                                               onchange="hitungMingguPreview(this.value)">

                                        @error('tanggal_pemesanan')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror

                                        <div class="mt-3 p-3 bg-light rounded border text-center">
                                            <small class="text-muted d-block">Minggu Otomatis</small>
                                            <strong id="preview_minggu" class="text-primary fs-4">-</strong>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <hr class="my-4 border-primary">

                        <!-- Dropdown Produk Perlengkapan -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-primary">
                                Produk Perlengkapan <span class="text-danger">*</span>
                            </label>

                            <select name="produk_id" id="produk_id" 
                                    class="form-select form-select-lg @error('produk_id') is-invalid @enderror" 
                                    required>
                                <option value="">-- Pilih Produk Perlengkapan --</option>
                                @foreach($produks as $produk)
                                    <option value="{{ $produk->id }}"
                                            data-kode="{{ $produk->kode }}"
                                            data-nama="{{ $produk->nama_produk }}"
                                            data-harga="{{ $produk->harga }}"
                                            data-satuan="{{ $produk->satuan }}"
                                            data-kategori="{{ $produk->kategori ?? $produk->jenis ?? 'Perlengkapan' }}"
                                            {{ old('produk_id') == $produk->id ? 'selected' : '' }}>
                                        {{ $produk->kode }} - {{ $produk->nama_produk }} ({{ $produk->satuan }})
                                    </option>
                                @endforeach
                            </select>

                            <small class="text-muted d-block mt-2">
                                Total produk tersedia: {{ $produks->count() }} item
                            </small>

                            @error('produk_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Info Produk Otomatis -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Kode</label>
                                <input type="text" id="kode" class="form-control bg-light text-center fw-bold" readonly value="-">
                                <input type="hidden" name="kode" id="kode_hidden">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Kategori</label>
                                <input type="text" id="kategori_display" class="form-control bg-light text-center fw-bold" readonly value="-">
                                <input type="hidden" name="kategori" id="kategori_hidden">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Nama Perlengkapan</label>
                                <input type="text" id="nama_barang" class="form-control bg-light fw-bold" readonly value="-">
                                <input type="hidden" name="nama_barang" id="nama_barang_hidden">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-bold">Satuan</label>
                                <input type="text" id="satuan" class="form-control bg-light text-center" readonly value="-">
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Jumlah, Harga, Total -->
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Jumlah <span class="text-danger">*</span></label>
                                <input type="number" name="jumlah" id="jumlah" 
                                       class="form-control form-control-lg text-center @error('jumlah') is-invalid @enderror"
                                       value="{{ old('jumlah', 1) }}" min="1" required>
                                @error('jumlah')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Harga Satuan (Rp)</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="harga" class="form-control bg-light text-end fw-bold" readonly value="0">
                                    <input type="hidden" name="harga" id="harga_hidden" value="0">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-success fs-5">Total (Rp)</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text text-success">Rp</span>
                                    <input type="text" id="total" class="form-control text-end fw-bold fs-4 text-success bg-light" readonly value="0">
                                </div>
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div class="mt-5">
                            <label class="form-label fw-bold">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="4" 
                                      placeholder="Catatan tambahan (opsional)...">{{ old('keterangan') }}</textarea>
                        </div>

                        <!-- Tombol -->
                        <div class="d-flex justify-content-end gap-3 mt-5">
                            <a href="{{ route('pemesanan_perlengkapan_unit.index') }}" 
                               class="btn btn-secondary btn-lg px-5 shadow">
                                <i class="fas fa-arrow-left me-2"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-success btn-lg px-5 shadow" id="submitBtn"
                                    {{ !$defaultUnitId && !$isAdmin ? 'disabled' : '' }}>
                                <i class="fas fa-save me-2"></i> Simpan Pemesanan
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
    const selectProduk = document.getElementById('produk_id');
    const kode = document.getElementById('kode');
    const kodeHidden = document.getElementById('kode_hidden');
    const kategoriDisplay = document.getElementById('kategori_display');
    const kategoriHidden = document.getElementById('kategori_hidden');
    const namaBarang = document.getElementById('nama_barang');
    const namaBarangHidden = document.getElementById('nama_barang_hidden');
    const satuan = document.getElementById('satuan');
    const harga = document.getElementById('harga');
    const hargaHidden = document.getElementById('harga_hidden');
    const jumlah = document.getElementById('jumlah');
    const total = document.getElementById('total');
    const tanggalInput = document.getElementById('tanggal_pemesanan');
    const previewMinggu = document.getElementById('preview_minggu');

    // Update info produk saat pilih
    function updateProdukInfo() {
        const option = selectProduk.options[selectProduk.selectedIndex];
        
        if (option && option.value) {
            kode.value = option.dataset.kode || '-';
            kodeHidden.value = option.dataset.kode || '';

            const kat = option.dataset.kategori || 'Perlengkapan';
            kategoriDisplay.value = kat;
            kategoriHidden.value = kat;

            namaBarang.value = option.dataset.nama || '-';
            namaBarangHidden.value = option.dataset.nama || '';

            satuan.value = option.dataset.satuan || '-';

            const h = parseInt(option.dataset.harga || 0);
            harga.value = h.toLocaleString('id-ID');
            hargaHidden.value = h;
        } else {
            kode.value = '-';
            kodeHidden.value = '';
            kategoriDisplay.value = '-';
            kategoriHidden.value = '';
            namaBarang.value = '-';
            namaBarangHidden.value = '';
            satuan.value = '-';
            harga.value = '0';
            hargaHidden.value = '0';
        }

        hitungTotal();
    }

    // Hitung total otomatis
    function hitungTotal() {
        const j = parseInt(jumlah.value) || 0;
        const h = parseFloat(hargaHidden.value) || 0;
        const t = j * h;
        total.value = t.toLocaleString('id-ID');
    }

    // Preview minggu dari tanggal
    window.hitungMingguPreview = function(tanggal) {
        if (!tanggal) {
            previewMinggu.textContent = '-';
            return;
        }
        const date = new Date(tanggal);
        const day = date.getDate();
        const minggu = Math.min(Math.ceil(day / 7), 5);
        previewMinggu.textContent = minggu;
    }

    // Event listeners
    selectProduk.addEventListener('change', updateProdukInfo);
    jumlah.addEventListener('input', hitungTotal);
    tanggalInput.addEventListener('change', () => hitungMingguPreview(tanggalInput.value));

    // Inisialisasi saat halaman dimuat
    updateProdukInfo();
    hitungMingguPreview(tanggalInput.value);
});
</script>
@endsection