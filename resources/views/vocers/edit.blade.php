@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Voucher</h1>
    <a href="{{ route('vocers.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('vocers.update', $vocer->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>NUMERATOR</label>
            <input type="number" name="numerator" class="form-control" value="{{ old('numerator', $vocer->numerator) }}">
        </div>

        <div class="mb-3">
            <label>KATEGORI V</label>
            <input type="text" name="kategori_v" class="form-control" value="{{ old('kategori_v', $vocer->kategori_v) }}">
        </div>

        <div class="mb-3">
            <label>NILAI V</label>
            <input type="number" name="nilai_v" class="form-control" value="{{ old('nilai_v', $vocer->nilai_v) }}">
        </div>

        <div class="mb-3">
            <label>TGL PENY</label>
            <input type="date" name="tgl_peny" class="form-control" value="{{ old('tgl_peny', $vocer->tgl_peny) }}">
        </div>

        <div class="mb-3">
            <label>ST V</label>
            <input type="text" name="st_v" class="form-control" value="{{ old('st_v', $vocer->st_v) }}">
        </div>

        <hr>
        <h5>VA MURID HUMAS</h5>
        <div class="mb-3">
            <input type="text" name="va_murid_humas" class="form-control mb-1" placeholder="VA MURID HUMAS" value="{{ old('va_murid_humas', $vocer->va_murid_humas) }}">
            <input type="text" name="va_murid_humas_1" class="form-control mb-1" placeholder="1" value="{{ old('va_murid_humas_1', $vocer->va_murid_humas_1) }}">
            <input type="text" name="va_murid_humas_2" class="form-control mb-1" placeholder="2" value="{{ old('va_murid_humas_2', $vocer->va_murid_humas_2) }}">
            <input type="text" name="nama_murid_humas" class="form-control" placeholder="NAMA MURID HUMAS" value="{{ old('nama_murid_humas', $vocer->nama_murid_humas) }}">
        </div>

        <hr>
        <h5>VA MURID BARU *</h5>
        <div class="mb-3">
            <input type="text" name="va_murid_baru" class="form-control mb-1" placeholder="VA MURID BARU *" value="{{ old('va_murid_baru', $vocer->va_murid_baru) }}">
            <input type="text" name="va_murid_baru_1" class="form-control mb-1" placeholder="1'" value="{{ old('va_murid_baru_1', $vocer->va_murid_baru_1) }}">
            <input type="text" name="va_murid_baru_2" class="form-control mb-1" placeholder="2''" value="{{ old('va_murid_baru_2', $vocer->va_murid_baru_2) }}">
            <input type="text" name="nama_murid_baru" class="form-control" placeholder="NAMA MURID BARU" value="{{ old('nama_murid_baru', $vocer->nama_murid_baru) }}">
        </div>

        <div class="mb-3">
            <label>KETERANGAN #</label>
            <textarea name="keterangan" class="form-control">{{ old('keterangan', $vocer->keterangan) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
    </form>
</div>
@endsection
