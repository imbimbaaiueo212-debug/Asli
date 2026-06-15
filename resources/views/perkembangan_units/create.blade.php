@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tambah Data Perkembangan Unit</h1>

    <form action="{{ route('perkembangan_units.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>TGL</label>
            <input type="number" name="tgl" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>BL (Bulan)</label>
            <input type="number" name="bl" class="form-control" required>
        </div>

        <h4>Jumlah Unit per Hari</h4>
        <div class="row">
            @for($i = 1; $i <= 31; $i++)
                <div class="col-md-1 mb-2">
                    <label>{{ str_pad($i,2,'0',STR_PAD_LEFT) }}</label>
                    <input type="number" name="{{ str_pad($i,2,'0',STR_PAD_LEFT) }}" class="form-control" value="0">
                </div>
            @endfor
        </div>

        <button type="submit" class="btn btn-success mt-3">Simpan</button>
        <a href="{{ route('perkembangan_units.index') }}" class="btn btn-secondary mt-3">Kembali</a>
    </form>
</div>
@endsection
