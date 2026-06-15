@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Tambah MBC Murid</h1>

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

    <form action="{{ route('mbc-murid.store') }}" method="POST">
        @csrf

        <!-- Profil Unit -->
        <h5>Profil Unit</h5>
        <div class="mb-3">
            <label>No Cabang</label>
            <input type="text" name="no_cabang" class="form-control">
        </div>
        <div class="mb-3">
            <label>Nama Unit</label>
            <input type="text" name="nama_unit" class="form-control">
        </div>
        <div class="mb-3">
            <label>No Telp/HP</label>
            <input type="text" name="no_telp" class="form-control">
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control">
        </div>
        <div class="mb-3">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control"></textarea>
        </div>

        <!-- Profil Murid -->
        <h5>Profil Murid</h5>
        <div class="mb-3">
            <label>No Pembayaran</label>
            <input type="text" name="no_pembayaran" class="form-control">
        </div>
        <div class="mb-3">
            <label>Nama Murid</label>
            <input type="text" name="nama_murid" class="form-control">
        </div>
        <div class="mb-3">
            <label>Kelas</label>
            <input type="text" name="kelas" class="form-control">
        </div>
        <div class="mb-3">
            <label>Gol & Kode</label>
            <input type="text" name="golongan_kode" class="form-control">
        </div>
        <div class="mb-3">
            <label>SPP</label>
            <input type="number" step="0.01" name="spp" class="form-control">
        </div>
        <div class="mb-3">
            <label>Wali Murid</label>
            <input type="text" name="wali_murid" class="form-control">
        </div>

        <!-- Bill Payment & Virtual Account -->
        <div class="mb-3">
            <label>Bill Payment</label>
            <textarea name="bill_payment" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label>Virtual Account</label>
            <textarea name="virtual_account" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
    </form>
</div>
@endsection
