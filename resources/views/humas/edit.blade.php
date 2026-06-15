@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Data Humas</h2>

    <form action="{{ route('humas.update', $huma->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Tanggal Registrasi <span class="text-danger">*</span></label>
            <input type="date" 
                   name="tgl_reg" 
                   class="form-control" 
                   value="{{ old('tgl_reg', $huma->tgl_reg ? \Carbon\Carbon::parse($huma->tgl_reg)->format('Y-m-d') : '') }}" 
                   required>
        </div>

        <div class="mb-3">
            <label>NIH <span class="text-danger">*</span></label>
            <input type="text" 
                   name="nih" 
                   class="form-control" 
                   value="{{ old('nih', $huma->nih) }}" 
                   required>
        </div>

        <div class="mb-3">
            <label>Nama <span class="text-danger">*</span></label>
            <input type="text" 
                   name="nama" 
                   class="form-control" 
                   value="{{ old('nama', $huma->nama) }}" 
                   required>
        </div>

        <div class="mb-3">
            <label>Status</label>
            <input type="text" 
                   name="status" 
                   class="form-control" 
                   value="{{ old('status', $huma->status) }}">
        </div>

        <div class="mb-3">
            <label>No Telp/HP</label>
            <input type="text" 
                   name="no_telp" 
                   class="form-control" 
                   value="{{ old('no_telp', $huma->no_telp) }}">
        </div>

        <div class="mb-3">
            <label>Pekerjaan</label>
            <input type="text" 
                   name="pekerjaan" 
                   class="form-control" 
                   value="{{ old('pekerjaan', $huma->pekerjaan) }}">
        </div>

        <div class="mb-3">
            <label>biMBA Unit</label>
            <input type="text" 
                   name="bimba_unit" 
                   class="form-control" 
                   value="{{ old('bimba_unit', $huma->bimba_unit) }}">
        </div>

        <div class="mb-3">
            <label>No. Cabang</label>
            <input type="text" 
                   name="no_cabang" 
                   class="form-control" 
                   value="{{ old('no_cabang', $huma->no_cabang) }}">
        </div>

        <div class="mb-3">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $huma->alamat) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('humas.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection