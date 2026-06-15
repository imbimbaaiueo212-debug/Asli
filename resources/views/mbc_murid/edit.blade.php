@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Edit MBC Murid</h1>

    <a href="{{ route('mbc-murid.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('mbc-murid.update', $murid->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Profil Unit -->
        <h5>Profil Unit</h5>
        <div class="mb-3">
            <label>No Cabang</label>
            <input type="text" name="no_cabang" class="form-control" value="{{ $murid->no_cabang }}">
        </div>
        <div class="mb-3">
            <label>Nama Unit</label>
            <input type="text" name="nama_unit" class="form-control" value="{{ $murid->nama_unit }}">
        </div>
        <div class="mb-3">
            <label>No Telp/HP</label>
            <input type="text" name="no_telp" class="form-control" value="{{ $murid->no_telp }}">
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ $murid->email }}">
        </div>
        <div class="mb-3">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control">{{ $murid->alamat }}</textarea>
        </div>

        <!-- Profil Murid -->
        <h5>Profil Murid</h5>
        <div class="mb-3">
            <label>No Pembayaran</label>
            <input type="text" name="no_pembayaran" class="form-control" value="{{ $murid->no_pembayaran }}">
        </div>
        <div class="mb-3">
            <label>Nama Murid</label>
            <input type="text" name="nama_murid" class="form-control" value="{{ $murid->nama_murid }}">
        </div>
        <div class="mb-3">
            <label>Kelas</label>
            <input type="text" name="kelas" class="form-control" value="{{ $murid->kelas }}">
        </div>
        <div class="mb-3">
            <label>Gol & Kode</label>
            <input type="text" name="golongan_kode" class="form-control" value="{{ $murid->golongan_kode }}">
        </div>
        <div class="mb-3">
            <label>SPP</label>
            <input type="number" step="0.01" name="spp" class="form-control" value="{{ $murid->spp }}">
        </div>
        <div class="mb-3">
            <label>Wali Murid</label>
            <input type="text" name="wali_murid" class="form-control" value="{{ $murid->wali_murid }}">
        </div>

        <!-- Bill Payment & Virtual Account -->
        <div class="mb-3">
            <label>Bill Payment</label>
            <textarea name="bill_payment" class="form-control">{{ $murid->bill_payment }}</textarea>
        </div>
        <div class="mb-3">
            <label>Virtual Account</label>
            <textarea name="virtual_account" class="form-control">{{ $murid->virtual_account }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
