@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Daftar Voucher</h1>
    <a href="{{ route('vocers.create') }}" class="btn btn-primary mb-3">Tambah Voucher</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>NO</th>
                <th>NUMERATOR</th>
                <th>KATEGORI V</th>
                <th>NILAI V</th>
                <th>TGL PENY</th>
                <th>ST V</th>

                <th>VA MURID HUMAS</th>
                <th>1</th>
                <th>2</th>
                <th>NAMA MURID HUMAS</th>

                <th>VA MURID BARU *</th>
                <th>1'</th>
                <th>2''</th>
                <th>NAMA MURID BARU</th>

                <th>KETERANGAN #</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vocers as $vocer)
            <tr>
                <td>{{ $vocer->id }}</td>
                <td>{{ $vocer->numerator }}</td>
                <td>{{ $vocer->kategori_v }}</td>
                <td>{{ $vocer->nilai_v }}</td>
                <td>{{ $vocer->tgl_peny }}</td>
                <td>{{ $vocer->st_v }}</td>

                <td>{{ $vocer->va_murid_humas }}</td>
                <td>{{ $vocer->va_murid_humas_1 }}</td>
                <td>{{ $vocer->va_murid_humas_2 }}</td>
                <td>{{ $vocer->nama_murid_humas }}</td>

                <td>{{ $vocer->va_murid_baru }}</td>
                <td>{{ $vocer->va_murid_baru_1 }}</td>
                <td>{{ $vocer->va_murid_baru_2 }}</td>
                <td>{{ $vocer->nama_murid_baru }}</td>

                <td>{{ $vocer->keterangan }}</td>

                <td>
                    <a href="{{ route('vocers.edit', $vocer->id) }}" class="btn btn-warning btn-sm">Edit</a>

                    <form action="{{ route('vocers.destroy', $vocer->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data?')">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
