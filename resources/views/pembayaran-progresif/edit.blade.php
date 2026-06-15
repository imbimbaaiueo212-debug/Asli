@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Pembayaran Progresif</h2>
    <a href="{{ route('pembayaran-progresif.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    <form action="{{ route('pembayaran-progresif.update', $pembayaran_progresif->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <div class="col">
                <label>Nama</label>
                <input type="text" name="nama" value="{{ $pembayaran_progresif->nama }}" class="form-control" required>
            </div>
            <div class="col">
                <label>Jabatan</label>
                <input type="text" name="jabatan" value="{{ $pembayaran_progresif->jabatan }}" class="form-control">
            </div>
            <div class="col">
                <label>Status</label>
                <input type="text" name="status" value="{{ $pembayaran_progresif->status }}" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label>Departemen</label>
                <input type="text" name="departemen" value="{{ $pembayaran_progresif->departemen }}" class="form-control">
            </div>
            <div class="col">
                <label>Masa Kerja</label>
                <input type="text" name="masa_kerja" value="{{ $pembayaran_progresif->masa_kerja }}" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label>No Rekening</label>
                <input type="text" name="no_rekening" value="{{ $pembayaran_progresif->no_rekening }}" class="form-control">
            </div>
            <div class="col">
                <label>Bank</label>
                <input type="text" name="bank" value="{{ $pembayaran_progresif->bank }}" class="form-control">
            </div>
            <div class="col">
                <label>Atas Nama</label>
                <input type="text" name="atas_nama" value="{{ $pembayaran_progresif->atas_nama }}" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label>THP</label>
                <input type="number" name="thp" value="{{ $pembayaran_progresif->thp }}" class="form-control">
            </div>
            <div class="col">
                <label>Kurang</label>
                <input type="number" name="kurang" value="{{ $pembayaran_progresif->kurang }}" class="form-control">
            </div>
            <div class="col">
                <label>Lebih</label>
                <input type="number" name="lebih" value="{{ $pembayaran_progresif->lebih }}" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label>Bulan</label>
                <input type="text" name="bulan" value="{{ $pembayaran_progresif->bulan }}" class="form-control">
            </div>
            <div class="col">
                <label>Transfer</label>
                <input type="number" name="transfer" value="{{ $pembayaran_progresif->transfer }}" class="form-control">
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Update</button>
        <a href="{{ route('pembayaran-progresif.index') }}" class="btn btn-secondary mt-3">Kembali</a>
    </form>
</div>
@endsection
