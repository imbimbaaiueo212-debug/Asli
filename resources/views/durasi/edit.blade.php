@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Edit Durasi</h3>

    <form action="{{ route('durasi.update', $durasi->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="waktu_mgg" class="form-label">WAKTU / MGG</label>
            <input type="text" name="waktu_mgg" id="waktu_mgg" class="form-control" value="{{ $durasi->waktu_mgg }}" required>
        </div>
        <div class="mb-3">
            <label for="waktu_bln" class="form-label">WAKTU / BLN</label>
            <input type="text" name="waktu_bln" id="waktu_bln" class="form-control" value="{{ $durasi->waktu_bln }}" required>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
    </form>
</div>
@endsection
