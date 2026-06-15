@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Daftar Harga</h1>
    <a href="{{ route('daftar-harga.create') }}" class="btn btn-primary mb-3">Tambah Data</a>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>Kategori</th>
                <th>Sub Kategori</th>
                <th>Unit</th>
                <th>Deskripsi</th>
                <th>A</th>
                <th>B</th>
                <th>C</th>
                <th>D</th>
                <th>E</th>
                <th>F</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($daftarHarga as $index => $d)
            <tr>
                <td>{{ $index+1 }}</td>
                <td>{{ $d->kategori ?? '-' }}</td>
                <td>{{ $d->sub_kategori ?? '-' }}</td>
                <td>{{ $d->unit ?? '-' }}</td>
                <td>{{ $d->deskripsi ?? '-' }}</td>
                @foreach(['a','b','c','d','e','f'] as $col)
                <td>{{ $d->{'harga_'.$col} ? number_format($d->{'harga_'.$col},0,',','.') : '-' }}</td>
                @endforeach
                <td>
                    <a href="{{ route('daftar-harga.edit', $d->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('daftar-harga.destroy', $d->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button onclick="return confirm('Yakin ingin menghapus data ini?')" class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="12" class="text-center">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
