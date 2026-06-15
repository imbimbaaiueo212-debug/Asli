@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Edit Data KTR</h3>

    <form action="{{ route('ktr.update', $ktr->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="waktu" class="form-label">Waktu / MGG</label>
            <input type="date" name="waktu" id="waktu" class="form-control" value="{{ old('waktu', $ktr->waktu) }}" required>
        </div>

        <div class="mb-3">
            <label for="kategori" class="form-label">Kategori</label>
            <input type="text" name="kategori" id="kategori" class="form-control" value="{{ old('kategori', $ktr->kategori) }}" required>
        </div>

        <div class="mb-3">
            <label for="jumlah" class="form-label">Jumlah (Rp)</label>
            <input type="number" name="jumlah" id="jumlah" class="form-control" value="{{ old('jumlah', $ktr->jumlah) }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('ktr.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
