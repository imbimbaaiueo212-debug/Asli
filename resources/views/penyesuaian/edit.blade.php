@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Edit Penyesuaian KTR</h3>

    <form action="{{ route('penyesuaian.update', $penyesuaian->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="jumlah_murid" class="form-label">Jumlah Murid</label>
            <input type="text" name="jumlah_murid" id="jumlah_murid" class="form-control" value="{{ $penyesuaian->jumlah_murid }}" required>
        </div>
        <div class="mb-3">
            <label for="penyesuaian_ktr" class="form-label">Penyesuaian KTR</label>
            <input type="text" name="penyesuaian_ktr" id="penyesuaian_ktr" class="form-control" value="{{ $penyesuaian->penyesuaian_ktr }}" required>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
    </form>
</div>
@endsection
