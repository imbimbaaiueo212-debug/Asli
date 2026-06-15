@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Tambah Penyesuaian RB Guru</h3>

    <form action="{{ route('rb.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Jumlah Murid</label>
            <input type="text" name="jumlah_murid" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Slot Rombim</label>
            <input type="text" name="slot_rombim" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Jam Kegiatan</label>
            <input type="text" name="jam_kegiatan" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Penyesuaian RB</label>
            <input type="text" name="penyesuaian_rb" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
    </form>
</div>
@endsection
