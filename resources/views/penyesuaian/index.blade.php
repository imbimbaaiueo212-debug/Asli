@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Penyesuaian KTR KU</h3>
        <a href="{{ route('penyesuaian.create') }}" class="btn btn-primary mb-3">Tambah Data</a>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif


        <form action="{{ route('penyesuaian.import') }}" method="POST" enctype="multipart/form-data" class="import-form">
            @csrf
            <input type="file" name="file" required>
            <button type="submit">Import Excel</button>
        </form>

        <table class="table table-bordered text-center align-middle">
            <thead>
                <tr>
                    <th colspan="2">PENYESUAIAN KTR KU</th>
                    <th>Aksi</th>
                </tr>
                <tr>
                    <th>JUMLAH MURID</th>
                    <th>PENYESUAIAN KTR</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                    <tr>
                        <td>{{ $item->jumlah_murid }}</td>
                        <td>{{ $item->penyesuaian_ktr }}</td>
                        <td>
    <a href="{{ route('penyesuaian.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>

    @if (auth()->user()?->role === 'admin')
        <form action="{{ route('penyesuaian.destroy', $item->id) }}" method="POST"
              style="display:inline-block;" 
              onsubmit="return confirm('Yakin mau hapus?');">
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
