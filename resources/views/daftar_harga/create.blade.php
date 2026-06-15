@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Tambah Data Daftar Harga</h1>

    <a href="{{ route('daftar-harga.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('daftar-harga.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Kategori</label>
            <input type="text" name="kategori" class="form-control" placeholder="Biaya Pendaftaran / Penjualan / Biaya SPP Perbulan">
        </div>

        <div class="mb-3">
            <label>Sub Kategori</label>
            <input type="text" name="sub_kategori" class="form-control" placeholder="Duafa, Promo 2019, Daftar Ulang, Daftar Ulang, Spesial, Umum 1, Umum 2, dll">
        </div>

        <div class="mb-3">
            <label>Unit</label>
            <input type="text" name="unit" class="form-control" placeholder="biMBA AIUEO / English biMBA / dll">
        </div>

        <div class="mb-3">
            <label>Deskripsi</label>
            <input type="text" name="deskripsi" class="form-control" placeholder="Keterangan tambahan misal 'Seminggu 3x'">
        </div>

        <h5>Harga</h5>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>A</th>
                    <th>B</th>
                    <th>C</th>
                    <th>D</th>
                    <th>E</th>
                    <th>F</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    @foreach(['a','b','c','d','e','f'] as $col)
                    <td>
                        <input type="number" step="0.01" name="harga_{{ $col }}" class="form-control">
                    </td>
                    @endforeach
                </tr>
            </tbody>
        </table>

        <button type="submit" class="btn btn-success">Simpan</button>
    </form>
</div>
@endsection
