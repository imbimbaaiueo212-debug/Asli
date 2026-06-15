@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Tambah Data KTR</h3>

    <form action="{{ route('ktr.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="waktu" class="form-label">Waktu / MGG</label>
            <input type="date" name="waktu" id="waktu" class="form-control" value="{{ old('waktu') }}" required>
        </div>

        <div class="mb-3">
            <label for="kategori" class="form-label">Kategori</label>
            <input type="text" name="kategori" id="kategori" class="form-control" value="{{ old('kategori') }}" placeholder="Contoh: Pemasukan / Pengeluaran" required>
        </div>

        <div class="mb-3">
            <label for="jumlah" class="form-label">Jumlah (Rp)</label>
            <input type="number" name="jumlah" id="jumlah" class="form-control" value="{{ old('jumlah') }}" placeholder="Contoh: 500000" required>
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('ktr.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
