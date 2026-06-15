@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Tambah Data Pemesanan Raport</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pemesanan_raport.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>NIM</label>
            <input type="text" name="nim" class="form-control" value="{{ old('nim') }}" required>
        </div>

        <div class="mb-3">
            <label>Nama Murid</label>
            <input type="text" name="nama_murid" class="form-control" value="{{ old('nama_murid') }}" required>
        </div>

        <div class="mb-3">
            <label>Gol</label>
            <input type="text" name="gol" class="form-control" value="{{ old('gol') }}">
        </div>

        <div class="mb-3">
            <label>Tgl Masuk</label>
            <input type="date" name="tgl_masuk" class="form-control" value="{{ old('tgl_masuk') }}">
        </div>

        <div class="mb-3">
            <label>Lama Belajar</label>
            <input type="text" name="lama_bljr" class="form-control" value="{{ old('lama_bljr') }}">
        </div>

        <div class="mb-3">
            <label>Guru</label>
            <input type="text" name="guru" class="form-control" value="{{ old('guru') }}">
        </div>

        <div class="mb-3">
            <label>Keterangan</label>
            <textarea name="keterangan" class="form-control">{{ old('keterangan') }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('pemesanan_raport.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
