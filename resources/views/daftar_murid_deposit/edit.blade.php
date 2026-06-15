@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Edit Data Deposit Murid</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('daftar_murid_deposit.update', $muridDeposit->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Tanggal Transaksi</label>
            <input type="date" name="tanggal_transaksi" class="form-control" value="{{ $muridDeposit->tanggal_transaksi }}" required>
        </div>

        <div class="mb-3">
            <label>Alert</label>
            <input type="text" name="alert" class="form-control" value="{{ $muridDeposit->alert }}">
        </div>

        <div class="mb-3">
            <label>NIM</label>
            <input type="text" name="nim" class="form-control" value="{{ $muridDeposit->nim }}" required>
        </div>

        <div class="mb-3">
            <label>Nama Murid</label>
            <input type="text" name="nama_murid" class="form-control" value="{{ $muridDeposit->nama_murid }}" required>
        </div>

        <div class="mb-3">
            <label>Kelas</label>
            <input type="text" name="kelas" class="form-control" value="{{ $muridDeposit->kelas }}" required>
        </div>

        <div class="mb-3">
            <label>Status</label>
            <input type="text" name="status" class="form-control" value="{{ $muridDeposit->status }}" required>
        </div>

        <div class="mb-3">
            <label>Nama Guru</label>
            <input type="text" name="nama_guru" class="form-control" value="{{ $muridDeposit->nama_guru }}" required>
        </div>

        <div class="mb-3">
            <label>Jumlah Deposit</label>
            <input type="number" step="0.01" name="jumlah_deposit" class="form-control" value="{{ $muridDeposit->jumlah_deposit }}" required>
        </div>

        <div class="mb-3">
            <label>Kategori Deposit</label>
            <input type="text" name="kategori_deposit" class="form-control" value="{{ $muridDeposit->kategori_deposit }}" required>
        </div>

        <div class="mb-3">
            <label>Status Deposit</label>
            <input type="text" name="status_deposit" class="form-control" value="{{ $muridDeposit->status_deposit }}" required>
        </div>

        <div class="mb-3">
            <label>Keterangan Deposit</label>
            <textarea name="keterangan_deposit" class="form-control">{{ $muridDeposit->keterangan_deposit }}</textarea>
        </div>

        <!-- Tombol Kembali & Update -->
        <a href="{{ route('daftar_murid_deposit.index') }}" class="btn btn-secondary">Kembali</a>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
