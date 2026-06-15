@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">Edit Slip Pembayaran Progresif</h3>
    <a href="{{ route('slip-progresif.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('slip-progresif.update', $slip->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <div class="col">
                <label>No Induk</label>
                <input type="text" name="no_induk" class="form-control" value="{{ $slip->no_induk }}">
            </div>
            <div class="col">
                <label>Nama Staff</label>
                <input type="text" name="nama_staff" class="form-control" value="{{ $slip->nama_staff }}" required>
            </div>
            <div class="col">
                <label>Unit biMBA</label>
                <input type="text" name="unit_bimba" class="form-control" value="{{ $slip->unit_bimba }}">
            </div>
            <div class="col">
                <label>Jabatan</label>
                <input type="text" name="jabatan" class="form-control" value="{{ $slip->jabatan }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label>Tanggal Masuk</label>
                <input type="date" name="tanggal_masuk" class="form-control" value="{{ $slip->tanggal_masuk }}">
            </div>
            <div class="col">
                <label>Bulan Bayar</label>
                <input type="text" name="bulan_bayar" class="form-control" value="{{ $slip->bulan_bayar }}">
            </div>
        </div>

        <h5 class="mt-4">Rincian Murid</h5>
        <div class="row mb-3">
            <div class="col"><label>AM1</label><input type="number" name="am1" class="form-control" value="{{ $slip->am1 }}"></div>
            <div class="col"><label>AM2</label><input type="number" name="am2" class="form-control" value="{{ $slip->am2 }}"></div>
            <div class="col"><label>MGRS</label><input type="number" name="mgrs" class="form-control" value="{{ $slip->mgrs }}"></div>
            <div class="col"><label>MDF</label><input type="number" name="mdf" class="form-control" value="{{ $slip->mdf }}"></div>
            <div class="col"><label>BNF1</label><input type="number" name="bnf1" class="form-control" value="{{ $slip->bnf1 }}"></div>
            <div class="col"><label>BNF2</label><input type="number" name="bnf2" class="form-control" value="{{ $slip->bnf2 }}"></div>
        </div>
        <div class="row mb-3">
            <div class="col"><label>MB</label><input type="number" name="mb" class="form-control" value="{{ $slip->mb }}"></div>
            <div class="col"><label>MT</label><input type="number" name="mt" class="form-control" value="{{ $slip->mt }}"></div>
            <div class="col"><label>MBE</label><input type="number" name="mbe" class="form-control" value="{{ $slip->mbe }}"></div>
            <div class="col"><label>MTE</label><input type="number" name="mte" class="form-control" value="{{ $slip->mte }}"></div>
        </div>

        <h5 class="mt-4">Rincian Pendapatan</h5>
        <div class="row mb-3">
            <div class="col"><label>SPP biMBA</label><input type="number" name="spp_bimba" class="form-control" value="{{ $slip->spp_bimba }}"></div>
            <div class="col"><label>SPP English</label><input type="number" name="spp_english" class="form-control" value="{{ $slip->spp_english }}"></div>
        </div>

        <h5 class="mt-4">Rincian Pembayaran</h5>
        <div class="row mb-3">
            <div class="col"><label>Total FM</label><input type="number" name="total_fm" class="form-control" value="{{ $slip->total_fm }}"></div>
            <div class="col"><label>Nilai Progresif</label><input type="number" name="nilai_progresif" class="form-control" value="{{ $slip->nilai_progresif }}"></div>
            <div class="col"><label>Total Komisi</label><input type="number" name="total_komisi" class="form-control" value="{{ $slip->total_komisi }}"></div>
            <div class="col"><label>Kelebihan</label><input type="number" name="lebih" class="form-control" value="{{ $slip->lebih }}"></div>
            <div class="col"><label>Kurang</label><input type="number" name="kurang" class="form-control" value="{{ $slip->kurang }}"></div>
        </div>

        <h5 class="mt-4">Komisi</h5>
        <div class="row mb-3">
            <div class="col"><label>Komisi MB biMBA</label><input type="number" name="komisi_mb" class="form-control" value="{{ $slip->komisi_mb }}"></div>
            <div class="col"><label>Komisi MT biMBA</label><input type="number" name="komisi_mt" class="form-control" value="{{ $slip->komisi_mt }}"></div>
            <div class="col"><label>Komisi MB English</label><input type="number" name="komisi_mbe" class="form-control" value="{{ $slip->komisi_mbe }}"></div>
            <div class="col"><label>Komisi MT English</label><input type="number" name="komisi_mte" class="form-control" value="{{ $slip->komisi_mte }}"></div>
            <div class="col"><label>Komisi ASKU</label><input type="number" name="asku" class="form-control" value="{{ $slip->asku }}"></div>
        </div>

        <div class="mb-3">
            <label>Jumlah Dibayarkan</label>
            <input type="number" name="jumlah_dibayarkan" class="form-control" value="{{ $slip->jumlah_dibayarkan }}">
        </div>

        <button type="submit" class="btn btn-primary mt-3">Update</button>
    </form>
</div>
@endsection
