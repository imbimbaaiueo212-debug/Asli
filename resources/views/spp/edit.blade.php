@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">Edit Data SPP</h3>

    <form action="{{ route('spp.update', $spp->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>NIM</label>
                <input type="text" name="nim" class="form-control" value="{{ $spp->nim }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Nama Murid</label>
                <input type="text" name="nama_murid" class="form-control" value="{{ $spp->nama_murid }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Kelas</label>
                <input type="text" name="kelas" class="form-control" value="{{ $spp->kelas }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Tahap</label>
                <input type="text" name="tahap" class="form-control" value="{{ $spp->tahap }}">
            </div>
            <div class="col-md-6 mb-3">
                <label>Gol</label>
                <input type="text" name="gol" class="form-control" value="{{ $spp->gol }}">
            </div>
            <div class="col-md-6 mb-3">
                <label>KD</label>
                <input type="text" name="kd" class="form-control" value="{{ $spp->kd }}">
            </div>
            <div class="col-md-6 mb-3">
                <label>SPP</label>
                <input type="number" name="spp" class="form-control" value="{{ $spp->spp }}">
            </div>
            <div class="col-md-6 mb-3">
                <label>Status</label>
                <input type="text" name="stts" class="form-control" value="{{ $spp->stts }}">
            </div>
            <div class="col-md-6 mb-3">
                <label>Petugas Trial</label>
                <input type="text" name="petugas_trial" class="form-control" value="{{ $spp->petugas_trial }}">
            </div>
            <div class="col-md-6 mb-3">
                <label>Guru</label>
                <input type="text" name="guru" class="form-control" value="{{ $spp->guru }}">
            </div>
            <div class="col-md-6 mb-3">
                <label>Note</label>
                <input type="text" name="note" class="form-control" value="{{ $spp->note }}">
            </div>
            <div class="col-md-12 mb-3">
                <label>Keterangan SPP</label>
                <textarea name="keterangan_spp" class="form-control" rows="2">{{ $spp->keterangan_spp }}</textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('spp.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
