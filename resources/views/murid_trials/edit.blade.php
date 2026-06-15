@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Murid Trial</h2>
    <form action="{{ route('murid_trials.update', $murid_trial->id) }}" method="POST">
        @csrf @method('PUT')

        <div class="mb-3">
            <label>Tgl Mulai</label>
            <input type="date" name="tgl_mulai" class="form-control" value="{{ old('tgl_mulai', $murid_trial->tgl_mulai) }}">
        </div>
        <div class="mb-3">
            <label>Kelas</label>
            <input type="text" name="kelas" class="form-control" value="{{ old('kelas', $murid_trial->kelas) }}">
        </div>
        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" value="{{ old('nama', $murid_trial->nama) }}" required>
        </div>
        <div class="mb-3">
            <label>Tgl Lahir</label>
            <input type="date" name="tgl_lahir" class="form-control" value="{{ old('tgl_lahir', $murid_trial->tgl_lahir) }}">
        </div>
        <div class="mb-3">
            <label>Usia</label>
            <input type="number" name="usia" class="form-control" value="{{ old('usia', $murid_trial->usia) }}">
        </div>
        <div class="mb-3">
            <label>Guru Trial</label>
            <input type="text" name="guru_trial" class="form-control" value="{{ old('guru_trial', $murid_trial->guru_trial) }}">
        </div>
        <div class="mb-3">
            <label>Info</label>
            <textarea name="info" class="form-control">{{ old('info', $murid_trial->info) }}</textarea>
        </div>
        <div class="mb-3">
            <label>Orangtua</label>
            <input type="text" name="orangtua" class="form-control" value="{{ old('orangtua', $murid_trial->orangtua) }}">
        </div>
        <div class="mb-3">
            <label>No Telp/HP</label>
            <input type="text" name="no_telp" class="form-control" value="{{ old('no_telp', $murid_trial->no_telp) }}">
        </div>
        <div class="mb-3">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control">{{ old('alamat', $murid_trial->alamat) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('murid_trials.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
