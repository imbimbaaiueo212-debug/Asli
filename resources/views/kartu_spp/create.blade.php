@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Tambah Kartu SPP</h1>

    <a href="{{ route('kartu-spp.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error) 
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('kartu-spp.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>No Pembayaran</label>
            <input type="text" name="no_pembayaran" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Nama Murid</label>
            <input type="text" name="nama_murid" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Golongan</label>
            <input type="text" name="golongan" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Pembayaran SPP</label>
            <input type="number" step="0.01" name="pembayaran_spp" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>biMBA Unit</label>
            <input type="text" name="bimba_unit" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
    </form>
</div>
@endsection
