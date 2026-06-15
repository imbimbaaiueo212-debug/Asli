@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Tambah Penyesuaian KTR</h3>

    <form action="{{ route('penyesuaian.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="jumlah_murid" class="form-label">Jumlah Murid</label>
            <input type="text" name="jumlah_murid" id="jumlah_murid" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="penyesuaian_ktr" class="form-label">Penyesuaian KTR</label>
            <input type="text" name="penyesuaian_ktr" id="penyesuaian_ktr" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
    </form>
</div>
@endsection
