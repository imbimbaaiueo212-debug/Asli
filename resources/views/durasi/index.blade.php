@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Durasi Kegiatan</h3>
        <a href="{{ route('durasi.create') }}" class="btn btn-primary mb-3">Tambah Durasi</a>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('durasi.import') }}" method="POST" enctype="multipart/form-data" class="import-form">
            @csrf
            <input type="file" name="file" required>
            <button type="submit">Import Excel</button>
        </form>

        <table class="table table-bordered text-center align-middle">
            <thead>
                <tr>
                    <th colspan="2">DURASI KEGIATAN</th>
                    <th>Aksi</th>
                </tr>
                <tr>
                    <th>WAKTU / MGG</th>
                    <th>WAKTU / BLN</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
@foreach ($durasiList as $item)
    @php
        // ambil hanya angka dari string seperti "160 Jam" -> 160
        $waktuBlnInt = intval(preg_replace('/\D/', '', $item->waktu_bln));
        // normalisasi nama kode (RB40 vs rb40)
        $kode = strtolower($item->waktu_mgg);
    @endphp

    <tr class="{{ ($kode === 'rb40' && $waktuBlnInt === 160) ? 'table-warning' : '' }}">
        <td>{{ $item->waktu_mgg }}</td>
        <td>{{ $item->waktu_bln }}</td>
        <td>
    <a href="{{ route('durasi.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>

    @if (auth()->user()?->role === 'admin')
        <form action="{{ route('durasi.destroy', $item->id) }}" method="POST"
              style="display:inline-block;" 
              onsubmit="return confirm('Yakin mau hapus data ini?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
        </form>
    @endif
</td>
    </tr>
@endforeach
</tbody>


        </table>
    </div>
@endsection
