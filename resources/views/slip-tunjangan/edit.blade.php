@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Slip Tunjangan</h2>
    <form action="{{ route('slip-tunjangan.update', $slip_tunjangan->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Identitas --}}
        <h5>Identitas Staff</h5>
        <div class="row">
            <div class="col-md-4 mb-2">
                <label>Nomor Induk</label>
                <input type="text" name="nomor_induk" class="form-control" value="{{ $slip_tunjangan->nomor_induk }}">
            </div>
            <div class="col-md-4 mb-2">
                <label>Nama Staff</label>
                <input type="text" name="nama_staff" class="form-control" value="{{ $slip_tunjangan->nama_staff }}" required>
            </div>
            <div class="col-md-4 mb-2">
                <label>Jabatan</label>
                <input type="text" name="jabatan" class="form-control" value="{{ $slip_tunjangan->jabatan }}">
            </div>
            <div class="col-md-4 mb-2">
                <label>Unit</label>
                <input type="text" name="unit" class="form-control" value="{{ $slip_tunjangan->unit }}">
            </div>
            <div class="col-md-4 mb-2">
                <label>Tanggal Masuk</label>
                <input type="date" name="tanggal_masuk" class="form-control" value="{{ $slip_tunjangan->tanggal_masuk }}">
            </div>
            <div class="col-md-4 mb-2">
                <label>Bulan</label>
                <input type="text" name="bulan" class="form-control" value="{{ $slip_tunjangan->bulan }}">
            </div>
        </div>

        <hr>
        {{-- Pendapatan --}}
        <h5>Pendapatan</h5>
        <div class="row">
            @foreach ([
                'tunjangan_pokok' => 'Tunjangan Pokok',
                'tunjangan_harian' => 'Tunjangan Harian',
                'tunjangan_fungsional' => 'Tunjangan Fungsional',
                'tunjangan_kesehatan' => 'Tunjangan Kesehatan',
                'tunjangan_kerajinan' => 'Tunjangan Kerajinan',
                'komisi_english' => 'Komisi English biMBA',
                'komisi_mentor' => 'Komisi Mentor Magang',
                'kekurangan_tunjangan' => 'Kekurangan Tunjangan',
                'tunjangan_keluarga' => 'Tunjangan Keluarga',
                'lain_lain_pendapatan' => 'Lain-lain Pendapatan'
            ] as $field => $label)
            <div class="col-md-3 mb-2">
                <label>{{ $label }}</label>
                <input type="number" name="{{ $field }}" class="form-control" value="{{ $slip_tunjangan->$field }}">
            </div>
            @endforeach
        </div>

        <hr>
        {{-- Potongan --}}
        <h5>Potongan</h5>
        <div class="row">
            @foreach ([
                'sakit' => 'Sakit',
                'izin' => 'Izin',
                'alpa' => 'Alpa',
                'tidak_aktif' => 'Tidak Aktif',
                'kelebihan_tunjangan' => 'Kelebihan Tunjangan',
                'lain_lain_potongan' => 'Lain-lain Potongan'
            ] as $field => $label)
            <div class="col-md-3 mb-2">
                <label>{{ $label }}</label>
                <input type="number" name="{{ $field }}" class="form-control" value="{{ $slip_tunjangan->$field }}">
            </div>
            @endforeach
        </div>

        <hr>
        {{-- Pembayaran --}}
        <h5>Rekening & Email</h5>
        <div class="row">
            <div class="col-md-4 mb-2">
                <label>Bank</label>
                <input type="text" name="bank" class="form-control" value="{{ $slip_tunjangan->bank }}">
            </div>
            <div class="col-md-4 mb-2">
                <label>No Rekening</label>
                <input type="text" name="no_rekening" class="form-control" value="{{ $slip_tunjangan->no_rekening }}">
            </div>
            <div class="col-md-4 mb-2">
                <label>Atas Nama</label>
                <input type="text" name="atas_nama" class="form-control" value="{{ $slip_tunjangan->atas_nama }}">
            </div>
            <div class="col-md-6 mb-2">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ $slip_tunjangan->email }}">
            </div>
        </div>

        <button type="submit" class="btn btn-success mt-3">Update</button>
        <a href="{{ route('slip-tunjangan.index') }}" class="btn btn-secondary mt-3">Kembali</a>
    </form>
</div>
@endsection
