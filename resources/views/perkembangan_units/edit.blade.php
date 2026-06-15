@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Data Perkembangan Unit</h1>

    <form action="{{ route('perkembangan_units.update', $unit->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>TGL</label>
            <input type="text" name="tgl" class="form-control" value="{{ old('tgl', $unit->tgl) }}" required>
        </div>

        <div class="mb-3">
            <label>BL (Bulan)</label>
            <input type="text" name="bl" class="form-control" value="{{ old('bl', $unit->bl) }}" required>
        </div>

        <h4>Jumlah Unit per Hari</h4>
        <div class="row">
            @for($i = 1; $i <= 31; $i++)
                @php $col = str_pad($i,2,'0',STR_PAD_LEFT); @endphp
                <div class="col-md-1 mb-2">
                    <label>{{ $col }}</label>
                    <input type="number" name="{{ $col }}" class="form-control" value="{{ old($col, $unit->$col) }}" required>
                </div>
            @endfor
        </div>

        <button type="submit" class="btn btn-success mt-3">Update</button>
        <a href="{{ route('perkembangan_units.index') }}" class="btn btn-secondary mt-3">Kembali</a>
    </form>
</div>
@endsection
