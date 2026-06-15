@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">Tambah Pengajuan</h4>

    <form action="{{ route('pengajuan.store') }}" method="POST">
        @csrf

        {{-- ================= UNIT OTOMATIS DARI USER LOGIN ================= --}}
        <div class="mb-2">
            <label>biMBA Unit</label>
            <input type="text"
                   class="form-control"
                   value="{{ strtoupper(auth()->user()->bimba_unit ?? '-') }}"
                   readonly>
            <input type="hidden" name="bimba_unit" value="{{ auth()->user()->bimba_unit }}">
        </div>

        <div class="mb-2">
            <label>No Cabang</label>
            <input type="text"
                   class="form-control"
                   value="{{ auth()->user()->no_cabang ?? '-' }}"
                   readonly>
            <input type="hidden" name="no_cabang" value="{{ auth()->user()->no_cabang }}">
        </div>
        {{-- ================================================================ --}}

        <div class="mb-2">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" required>
        </div>

        <div class="mb-2">
            <label>Keterangan Pengajuan</label>
            <textarea name="keterangan_pengajuan" class="form-control" required></textarea>
        </div>

        <div class="mb-2">
            <label>Harga</label>
            <input type="number" name="harga" class="form-control" required>
        </div>

        <div class="mb-2">
            <label>Jumlah</label>
            <input type="number" name="jumlah" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success mt-2">Simpan</button>
        <a href="{{ route('pengajuan.index') }}" class="btn btn-secondary mt-2">Kembali</a>
    </form>
</div>
@endsection
