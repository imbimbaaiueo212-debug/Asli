@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tambah Data Skim</h1>

    <a href="{{ route('skim.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('skim.store') }}" method="POST">
        @csrf

        <!-- Jabatan -->
        <div class="mb-3">
            <label for="jabatan" class="form-label">Jabatan</label>
            <select name="jabatan" id="jabatan" class="form-control" required>
                <option value="">-- Pilih Jabatan --</option>
                @foreach($jabatanOptions as $jabatan)
                    <option value="{{ $jabatan }}" 
                        {{ old('jabatan') == $jabatan ? 'selected' : '' }}>
                        {{ $jabatan }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Masa Kerja -->
        <div class="mb-3">
            <label>Masa Kerja</label>
            <input type="text" name="masa_kerja" class="form-control" value="{{ old('masa_kerja') }}">
        </div>

        <!-- Status (Dropdown) -->
        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control" required>
                <option value="">-- Pilih Status --</option>
                @foreach($statusOptions as $status)
                    <option value="{{ $status }}" {{ old('status') == $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Tunjangan -->
        <div class="mb-3">
            <label>Tunjangan Pokok</label>
            <input type="number" name="tunj_pokok" class="form-control" value="{{ old('tunj_pokok',0) }}" step="0.01">
        </div>
        <div class="mb-3">
            <label>Harian</label>
            <input type="number" name="harian" class="form-control" value="{{ old('harian',0) }}" step="0.01">
        </div>
        <div class="mb-3">
            <label>Fungsional</label>
            <input type="number" name="fungsional" class="form-control" value="{{ old('fungsional',0) }}" step="0.01">
        </div>
        <div class="mb-3">
            <label>Kesehatan</label>
            <input type="number" name="kesehatan" class="form-control" value="{{ old('kesehatan',0) }}" step="0.01">
        </div>
        <div class="mb-3">
            <label>THP</label>
            <input type="number" name="thp" class="form-control" value="{{ old('thp',0) }}" step="0.01">
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
    </form>
</div>
@endsection
