@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Pemesanan Raport Murid Aktif (RBAS)</h2>
    <a href="{{ route('pemesanan_raport.create') }}" class="btn btn-primary mb-3">Tambah Data</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
    <table class="table table-bordered table-sm text-center">
        <thead>
            <tr style="background:#6cddff; font-weight:bold;">
                <th>NO</th>
                <th>NIM</th>
                <th>NAMA MURID</th>
                <th>GOL</th>
                <th>TGL MASUK</th>
                <th>LAMA BLJR</th>
                <th>GURU</th>
                <th>KETERANGAN</th>
                <th>ACTION</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->nim }}</td>
                <td>{{ $item->nama_murid }}</td>
                <td>{{ $item->gol }}</td>
                <td>{{ $item->tgl_masuk }}</td>
                <td>{{ $item->lama_bljr }}</td>
                <td>{{ $item->guru }}</td>
                <td>{{ $item->keterangan }}</td>
                <td>
                    <a href="{{ route('pemesanan_raport.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('pemesanan_raport.destroy', $item->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm"
                            onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
</div>
@endsection
