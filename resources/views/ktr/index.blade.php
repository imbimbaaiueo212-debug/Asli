@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Data KTR</h3>
        <a href="{{ route('ktr.create') }}" class="btn btn-primary mb-2">Tambah Data</a>
        <form action="{{ route('ktr.import') }}" method="POST" enctype="multipart/form-data" class="import-form">
            @csrf
            <input type="file" name="file" required>
            <button type="submit">Import Excel</button>
        </form>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>NO</th>
                    <th>WAKTU / MGG</th>
                    <th>KATEGORI</th>
                    <th>Rp.</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ktrs as $index => $ktr)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $ktr->waktu }}</td>
                        <td>{{ $ktr->kategori }}</td>
                        <td>Rp {{ number_format($ktr->jumlah, 0, ',', '.') }}</td>
                        <td>
    <a href="{{ route('ktr.edit', $ktr->id) }}" class="btn btn-warning btn-sm">Edit</a>

    @if (auth()->user()?->role === 'admin')
        <form action="{{ route('ktr.destroy', $ktr->id) }}" method="POST" style="display:inline">
            @csrf
            @method('DELETE')
            <button onclick="return confirm('Yakin hapus data?')"
                class="btn btn-danger btn-sm">Hapus</button>
        </form>
    @endif
</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $ktrs->onEachSide(1)->links('pagination::bootstrap-4') }}
    </div>
@endsection
