@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Tambah Durasi</h3>

    <form action="{{ route('durasi.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="waktu_mgg" class="form-label">WAKTU / MGG</label>
            <input type="text" name="waktu_mgg" id="waktu_mgg" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="waktu_bln" class="form-label">WAKTU / BLN</label>
            <input type="text" name="waktu_bln" id="waktu_bln" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
    </form>
</div>
@endsection
