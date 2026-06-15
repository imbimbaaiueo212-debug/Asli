@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Edit Garansi BCA 372 Bebas</h1>

    <a href="{{ route('garansi-bca.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('garansi-bca.update', $data->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>No. Virtual Account</label>
            <input type="text" name="virtual_account" class="form-control" value="{{ $data->virtual_account }}">
        </div>
        <div class="mb-3">
            <label>Nama Murid</label>
            <input type="text" name="nama_murid" class="form-control" value="{{ $data->nama_murid }}">
        </div>
        <div class="mb-3">
            <label>Tempat Tgl Lahir</label>
            <input type="text" name="tempat_tanggal_lahir" class="form-control" value="{{ $data->tempat_tanggal_lahir }}">
        </div>
        <div class="mb-3">
    <label>Tanggal Masuk</label>
    <input
        type="date"
        name="tanggal_masuk"
        class="form-control"
        value="{{ old('tanggal_masuk', $data->tanggal_masuk?->format('Y-m-d')) }}"
    >
</div>

        <div class="mb-3">
    <label class="fw-bold">Tanggal Diberikan</label>
    <input type="date"
        name="tanggal_diberikan"
        class="form-control"
        value="{{ old('tanggal_diberikan', now()->format('Y-m-d')) }}">
</div>

        <div class="mb-3">
            <label>Nama Org Tua/Wali</label>
            <input type="text" name="nama_orang_tua_wali" class="form-control" value="{{ $data->nama_orang_tua_wali }}">
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
