@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Harga Saptataruna</h1>
    <a href="{{ route('harga.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    <form action="{{ route('harga.update', $harga->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- KATEGORI --}}
        <div class="mb-3">
            <label>Kategori <span class="text-danger">*</span></label>
            <select name="kategori" class="form-control" required>
                <option value="BIAYA PENDAFTARAN" {{ $harga->kategori == 'BIAYA PENDAFTARAN' ? 'selected' : '' }}>
                    BIAYA PENDAFTARAN
                </option>
                <option value="PENJUALAN" {{ $harga->kategori == 'PENJUALAN' ? 'selected' : '' }}>
                    PENJUALAN
                </option>
                <option value="BIAYA SPP PER BULAN" {{ $harga->kategori == 'BIAYA SPP PER BULAN' ? 'selected' : '' }}>
                    BIAYA SPP PER BULAN
                </option>
            </select>
        </div>

        {{-- SUB KATEGORI --}}
        <div class="mb-3">
            <label>Sub Kategori</label>
            <select name="sub_kategori" class="form-control">
                <option value="">-</option>
                <option value="Kelas Standar (1 guru, 4 murid)" 
                    {{ $harga->sub_kategori == 'Kelas Standar (1 guru, 4 murid)' ? 'selected' : '' }}>
                    Kelas Standar (1 guru, 4 murid)
                </option>
                <option value="Kelas Khusus (1 guru, 2 murid)" 
                    {{ $harga->sub_kategori == 'Kelas Khusus (1 guru, 2 murid)' ? 'selected' : '' }}>
                    Kelas Khusus (1 guru, 2 murid)
                </option>
                <option value="Kelas Beasiswa (1 guru, 4 murid)" 
                    {{ $harga->sub_kategori == 'Kelas Beasiswa (1 guru, 4 murid)' ? 'selected' : '' }}>
                    Kelas Beasiswa (1 guru, 4 murid)
                </option>
                <option value="Kelas English (1 guru, 6 s/d 10 murid)" 
                    {{ $harga->sub_kategori == 'Kelas English (1 guru, 6 s/d 10 murid)' ? 'selected' : '' }}>
                    Kelas English (1 guru, 6 s/d 10 murid)
                </option>
            </select>
        </div>

        <div class="mb-3">
            <label>Kode</label>
            <input type="text" name="kode" class="form-control" value="{{ $harga->kode }}">
        </div>

        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" value="{{ $harga->nama }}">
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Duafa</label>
                <input type="text" name="duafa"
                    class="form-control"
                    value="{{ number_format($harga->duafa, 0, ',', '.') }}">
            </div>

            <div class="col-md-4 mb-3">
                <label>Promo Khusus</label>
                <input type="text" name="promo_2019"
                    class="form-control"
                    value="{{ number_format($harga->promo_2019, 0, ',', '.') }}">
            </div>
            <div class="col-md-4 mb-3">
                <label>Daftar Ulang</label>
                <input type="text" name="daftar_ulang"
                    class="form-control"
                    value="{{ number_format($harga->daftar_ulang, 0, ',', '.') }}">
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-3">
                <label>Spesial</label>
                <input type="text" name="spesial"
                    class="form-control"
                    value="{{ number_format($harga->spesial, 0, ',', '.') }}">
            </div>
            <div class="col-md-3 mb-3">
                <label>Umum 1</label>
                <input type="text" name="umum1"
                    class="form-control"
                    value="{{ number_format($harga->umum1, 0, ',', '.') }}">
            </div>
            <div class="col-md-3 mb-3">
                <label>Promo Gratis</label>
                <input type="text" name="umum2"
                    class="form-control"
                    value="{{ number_format($harga->umum2, 0, ',', '.') }}">
            </div>
            <div class="col-md-3 mb-3">
                <label>Harga</label>
                <input type="text" name="harga" class="form-control" 
                       value="{{ number_format($harga->harga, 0, ',', '.') }}">
            </div>
        </div>

        <!-- Baris Baru untuk Promo Umum -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Promo Umum</label>
                <input type="text" name="promo_umum"
                    class="form-control"
                    value="{{ number_format($harga->promo_umum, 0, ',', '.') }}">
            </div>
        </div>

        <div class="mb-3">
            <label>A - F</label>
            <div class="row g-2">
                <div class="col-md-2">
                    <input type="number" step="0.01" min="0" name="a" class="form-control" placeholder="A" value="{{ $harga->a }}">
                </div>
                <div class="col-md-2">
                    <input type="number" step="0.01" min="0" name="b" class="form-control" placeholder="B" value="{{ $harga->b }}">
                </div>
                <div class="col-md-2">
                    <input type="number" step="0.01" min="0" name="c" class="form-control" placeholder="C" value="{{ $harga->c }}">
                </div>
                <div class="col-md-2">
                    <input type="number" step="0.01" min="0" name="d" class="form-control" placeholder="D" value="{{ $harga->d }}">
                </div>
                <div class="col-md-2">
                    <input type="number" step="0.01" min="0" name="e" class="form-control" placeholder="E" value="{{ $harga->e }}">
                </div>
                <div class="col-md-2">
                    <input type="number" step="0.01" min="0" name="f" class="form-control" placeholder="F" value="{{ $harga->f }}">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success">Update Data</button>
    </form>
</div>
@endsection