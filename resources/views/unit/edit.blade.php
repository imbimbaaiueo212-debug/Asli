@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Unit</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('unit.update', $unit->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>No Cabang</label>
            <input type="text" name="no_cabang" class="form-control" value="{{ $unit->no_cabang }}" required>
        </div>
        <div class="mb-3">
            <label>biMBA Unit</label>
            <input type="text" name="biMBA_unit" class="form-control" value="{{ $unit->biMBA_unit }}" required>
        </div>
        <div class="mb-3">
            <label>Telp/HP</label>
            <input type="text" name="telp" class="form-control" value="{{ $unit->telp }}">
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ $unit->email }}">
        </div>
        <div class="mb-3">
            <label>Bank</label>
            <input type="text" name="bank_nama" class="form-control" value="{{ $unit->bank_nama }}">
        </div>
        <div class="mb-3">
            <label>Nomor Rekening</label>
            <input type="text" name="bank_nomor" class="form-control" value="{{ $unit->bank_nomor }}">
        </div>
        <div class="mb-3">
            <label>Atas Nama</label>
            <input type="text" name="bank_atas_nama" class="form-control" value="{{ $unit->bank_atas_nama }}">
        </div>
        <div class="mb-3">
            <label>Alamat Jalan</label>
            <input type="text" name="alamat_jalan" class="form-control" value="{{ $unit->alamat_jalan }}">
        </div>
        <div class="mb-3">
            <label>RT/RW</label>
            <input type="text" name="alamat_rt_rw" class="form-control" value="{{ $unit->alamat_rt_rw }}">
        </div>
        <div class="mb-3">
            <label>Kode Pos</label>
            <input type="text" name="alamat_kode_pos" class="form-control" value="{{ $unit->alamat_kode_pos }}">
        </div>
        <div class="mb-3">
            <label>Kel/Desa</label>
            <input type="text" name="alamat_kel_des" class="form-control" value="{{ $unit->alamat_kel_des }}">
        </div>
        <div class="mb-3">
            <label>Kecamatan</label>
            <input type="text" name="alamat_kecamatan" class="form-control" value="{{ $unit->alamat_kecamatan }}">
        </div>
        <div class="mb-3">
            <label>Kota/Kab</label>
            <input type="text" name="alamat_kota_kab" class="form-control" value="{{ $unit->alamat_kota_kab }}">
        </div>
        <div class="mb-3">
            <label>Provinsi</label>
            <input type="text" name="alamat_provinsi" class="form-control" value="{{ $unit->alamat_provinsi }}">
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('unit.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
