@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Tambah Data Humas</h2>

    <form action="{{ route('humas.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Tanggal Registrasi</label>
            <input type="date" name="tgl_reg" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>NIH</label>
            <input type="text" name="nih" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Status</label>
            <input type="text" name="status" class="form-control">
        </div>
        <div class="mb-3">
            <label>No Telp/HP</label>
            <input type="text" name="no_telp" class="form-control">
        </div>
        <div class="mb-3">
            <label>Pekerjaan</label>
            <input type="text" name="pekerjaan" class="form-control">
        </div>
        <div class="mb-3">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('humas.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
