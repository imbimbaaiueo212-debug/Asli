@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tambah Harga Saptataruna</h1>
    <a href="{{ route('harga.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    <form action="{{ route('harga.store') }}" method="POST">
        @csrf

        {{-- KATEGORI --}}
        <div class="mb-3">
            <label>Kategori <span class="text-danger">*</span></label>
            <select name="kategori" id="kategori" class="form-control" required>
                <option value="">Pilih Kategori</option>
                <option value="BIAYA PENDAFTARAN" {{ old('kategori') == 'BIAYA PENDAFTARAN' ? 'selected' : '' }}>BIAYA PENDAFTARAN</option>
                <option value="PENJUALAN" {{ old('kategori') == 'PENJUALAN' ? 'selected' : '' }}>PENJUALAN</option>
                <option value="BIAYA SPP PER BULAN" {{ old('kategori') == 'BIAYA SPP PER BULAN' ? 'selected' : '' }}>BIAYA SPP PER BULAN</option>
            </select>
        </div>

        {{-- SUB KATEGORI --}}
        <div class="mb-3" id="subKategoriDiv" style="display: none;">
            <label>Sub Kategori</label>
            <select name="sub_kategori" class="form-control">
                <option value="">-</option>
                <option value="Kelas Standar (1 guru, 4 murid)" {{ old('sub_kategori') == 'Kelas Standar (1 guru, 4 murid)' ? 'selected' : '' }}>Kelas Standar (1 guru, 4 murid)</option>
                <option value="Kelas Khusus (1 guru, 2 murid)" {{ old('sub_kategori') == 'Kelas Khusus (1 guru, 2 murid)' ? 'selected' : '' }}>Kelas Khusus (1 guru, 2 murid)</option>
                <option value="Kelas Beasiswa (1 guru, 4 murid)" {{ old('sub_kategori') == 'Kelas Beasiswa (1 guru, 4 murid)' ? 'selected' : '' }}>Kelas Beasiswa (1 guru, 4 murid)</option>
                <option value="Kelas English (1 guru, 6 s/d 10 murid)" {{ old('sub_kategori') == 'Kelas English (1 guru, 6 s/d 10 murid)' ? 'selected' : '' }}>Kelas English (1 guru, 6 s/d 10 murid)</option>
            </select>
        </div>

        {{-- INPUT LAINNYA --}}
        <div class="mb-3">
            <label>Kode</label>
            <input type="text" name="kode" class="form-control" value="{{ old('kode') }}">
        </div>

        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" value="{{ old('nama') }}">
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Duafa</label>
                <input type="number" step="0.01" min="0" name="duafa" class="form-control" value="{{ old('duafa') }}">
            </div>
            <div class="col-md-4 mb-3">
                <label>Promo Khusus</label>
                <input type="number" step="0.01" min="0" name="promo_2019" class="form-control" value="{{ old('promo_2019') }}">
            </div>
            <div class="col-md-4 mb-3">
                <label>Daftar Ulang</label>
                <input type="number" step="0.01" min="0" name="daftar_ulang" class="form-control" value="{{ old('daftar_ulang') }}">
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-3">
                <label>Spesial</label>
                <input type="number" step="0.01" min="0" name="spesial" class="form-control" value="{{ old('spesial') }}">
            </div>
            <div class="col-md-3 mb-3">
                <label>Umum 1</label>
                <input type="number" step="0.01" min="0" name="umum1" class="form-control" value="{{ old('umum1') }}">
            </div>
            <div class="col-md-3 mb-3">
                <label>Promo Gratis</label>
                <input type="number" step="0.01" min="0" name="umum2" class="form-control" value="{{ old('umum2') }}">
            </div>
            <div class="col-md-3 mb-3">
                <label>Harga</label>
                <input type="number" step="0.01" min="0" name="harga" class="form-control" value="{{ old('harga') }}">
            </div>
        </div>

        <!-- Baris Baru untuk Promo Umum -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Promo Umum</label>
                <input type="number" step="0.01" min="0" name="promo_umum" 
                       class="form-control" 
                       value="{{ old('promo_umum') }}"
                       placeholder="Promo Umum">
            </div>
        </div>

        <div class="mb-3">
            <label>A - F</label>
            <div class="row">
                <div class="col-md-2"><input type="number" step="0.01" min="0" name="a" class="form-control" placeholder="A" value="{{ old('a') }}"></div>
                <div class="col-md-2"><input type="number" step="0.01" min="0" name="b" class="form-control" placeholder="B" value="{{ old('b') }}"></div>
                <div class="col-md-2"><input type="number" step="0.01" min="0" name="c" class="form-control" placeholder="C" value="{{ old('c') }}"></div>
                <div class="col-md-2"><input type="number" step="0.01" min="0" name="d" class="form-control" placeholder="D" value="{{ old('d') }}"></div>
                <div class="col-md-2"><input type="number" step="0.01" min="0" name="e" class="form-control" placeholder="E" value="{{ old('e') }}"></div>
                <div class="col-md-2"><input type="number" step="0.01" min="0" name="f" class="form-control" placeholder="F" value="{{ old('f') }}"></div>
            </div>
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
    </form>
</div>

<script>
document.getElementById('kategori').addEventListener('change', function() {
    let subDiv = document.getElementById('subKategoriDiv');
    if (this.value === 'BIAYA SPP PER BULAN') {
        subDiv.style.display = 'block';
    } else {
        subDiv.style.display = 'none';
    }
});

// Trigger jika ada old value
@if(old('kategori') === 'BIAYA SPP PER BULAN')
document.getElementById('subKategoriDiv').style.display = 'block';
@endif
</script>
@endsection