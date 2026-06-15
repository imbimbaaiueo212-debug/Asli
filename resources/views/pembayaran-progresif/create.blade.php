@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Tambah Pembayaran Progresif</h2>
    <a href="{{ route('pembayaran-progresif.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    <form action="{{ route('pembayaran-progresif.store') }}" method="POST">
        @csrf

        <div class="row mb-3">
            <div class="col">
                <label>Nama</label>
                <input type="text" name="nama" class="form-control" required>
            </div>
            <div class="col">
                <label>Jabatan</label>
                <input type="text" name="jabatan" class="form-control">
            </div>
            <div class="col">
                <label>Status</label>
                <input type="text" name="status" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label>Departemen</label>
                <input type="text" name="departemen" class="form-control">
            </div>
            <div class="col">
                <label>Masa Kerja</label>
                <input type="text" name="masa_kerja" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label>No Rekening</label>
                <input type="text" name="no_rekening" class="form-control">
            </div>
            <div class="col">
                <label>Bank</label>
                <input type="text" name="bank" class="form-control">
            </div>
            <div class="col">
                <label>Atas Nama</label>
                <input type="text" name="atas_nama" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label>THP</label>
                <input type="number" name="thp" class="form-control">
            </div>
            <div class="col">
                <label>Kurang</label>
                <input type="number" name="kurang" class="form-control">
            </div>
            <div class="col">
                <label>Lebih</label>
                <input type="number" name="lebih" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label>Bulan</label>
                <input type="text" name="bulan" class="form-control">
            </div>
            <div class="col">
                <label>Transfer</label>
                <input type="number" name="transfer" class="form-control">
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Simpan</button>
        <a href="{{ route('pembayaran-progresif.index') }}" class="btn btn-secondary mt-3">Kembali</a>
    </form>
</div>
@endsection
