@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">Edit Pengajuan</h4>

    <form action="{{ route('pengajuan.update', $pengajuan->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-2">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="{{ $pengajuan->tanggal }}" required>
        </div>
        <div class="mb-2">
            <label>Keterangan Pengajuan</label>
            <textarea name="keterangan_pengajuan" class="form-control" required>{{ $pengajuan->keterangan_pengajuan }}</textarea>
        </div>
        <div class="mb-2">
            <label>Harga</label>
            <input type="number" name="harga" class="form-control" value="{{ $pengajuan->harga }}" required>
        </div>
        <div class="mb-2">
            <label>Jumlah</label>
            <input type="number" name="jumlah" class="form-control" value="{{ $pengajuan->jumlah }}" required>
        </div>

        <button type="submit" class="btn btn-success mt-2">Update</button>
        <a href="{{ route('pengajuan.index') }}" class="btn btn-secondary mt-2">Kembali</a>
    </form>
</div>
@endsection
