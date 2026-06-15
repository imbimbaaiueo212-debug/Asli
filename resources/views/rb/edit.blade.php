@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Edit Penyesuaian RB Guru</h3>

    <form action="{{ route('rb.update', $rb->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Jumlah Murid</label>
            <input type="text" name="jumlah_murid" class="form-control" value="{{ $rb->jumlah_murid }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Slot Rombim</label>
            <input type="text" name="slot_rombim" class="form-control" value="{{ $rb->slot_rombim }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Jam Kegiatan</label>
            <input type="text" name="jam_kegiatan" class="form-control" value="{{ $rb->jam_kegiatan }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Penyesuaian RB</label>
            <input type="text" name="penyesuaian_rb" class="form-control" value="{{ $rb->penyesuaian_rb }}" required>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
    </form>
</div>
@endsection
