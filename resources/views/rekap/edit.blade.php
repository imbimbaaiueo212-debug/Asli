@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-3">Edit Rekap Absensi Relawan</h3>

    <form action="{{ route('rekap.update', $rekap->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <div class="col-md-3">
                <label>NIK</label>
                <input type="text" name="nik" value="{{ $rekap->nik ?? '' }}" class="form-control" placeholder="Masukkan NIK">
            </div>
            <div class="col-md-3">
                <label>Nama Relawan</label>
                <input type="text" name="nama_relawan" value="{{ $rekap->nama_relawan }}" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Jabatan</label>
                <input type="text" name="jabatan" value="{{ $rekap->jabatan }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Departemen</label>
                <input type="text" name="departemen" value="{{ $rekap->departemen }}" class="form-control">
            </div>
        </div>

        <hr>
        <h5 class="mt-3">SR (Senin - Rabu - Jumat)</h5>
        <div class="row">
            @foreach([108,109,110,111,112,113,114,115,116] as $kode)
            <div class="col-md-1 mb-2">
                <input type="number" name="srj_{{ $kode }}" 
                    value="{{ $rekap->{'srj_'.$kode} ?? 0 }}" 
                    class="form-control text-center" placeholder="{{ $kode }}">
            </div>
            @endforeach
        </div>

        <h5 class="mt-3">SKS (Selasa - Kamis - Sabtu)</h5>
        <div class="row">
            @foreach([206,207,208,209,210,211] as $kode)
            <div class="col-md-1 mb-2">
                <input type="number" name="sks_{{ $kode }}" 
                    value="{{ $rekap->{'sks_'.$kode} ?? 0 }}" 
                    class="form-control text-center" placeholder="{{ $kode }}">
            </div>
            @endforeach
        </div>

        <h5 class="mt-3">S6 (Senin - Selasa - Rabu - Kamis - Jumat - Sabtu)</h5>
        <div class="row">
            @foreach([306,307,308,309,310,311] as $kode)
            <div class="col-md-1 mb-2">
                <input type="number" name="s6_{{ $kode }}" 
                    value="{{ $rekap->{'s6_'.$kode} ?? 0 }}" 
                    class="form-control text-center" placeholder="{{ $kode }}">
            </div>
            @endforeach
        </div>

        <hr>
        <div class="row mt-3">
            <div class="col-md-4">
                <label>Jumlah Murid</label>
                <input type="number" name="jumlah_murid" value="{{ $rekap->jumlah_murid ?? 0 }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label>Jumlah Rombim</label>
                <input type="number" name="jumlah_rombim" value="{{ $rekap->jumlah_rombim ?? 0 }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label>Penyesuaian RB</label>
                <input type="number" name="penyesuaian_rb" value="{{ $rekap->penyesuaian_rb ?? 0 }}" class="form-control">
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-success">💾 Update</button>
            <a href="{{ route('rekap.index') }}" class="btn btn-secondary">↩ Kembali</a>
        </div>
    </form>
</div>
@endsection
