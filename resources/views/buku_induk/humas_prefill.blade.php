@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Pre-fill Data ke Form Humas</h3>
    <p class="text-muted">Klik tombol untuk membuka form dengan data sudah terisi otomatis.</p>

    <table class="table table-hover">
        <thead class="table-dark">
            <tr>
                <th>NIM</th>
                <th>Nama</th>
                <th width="200">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td>{{ $item->nim }}</td>
                <td>{{ $item->nama }}</td>
                <td>
                    <a href="{{ $formUrl }}?{{ $entryNim }}={{ urlencode($item->nim) }}&{{ $entryNama }}={{ urlencode($item->nama) }}" 
                       target="_blank" 
                       class="btn btn-sm btn-primary">
                       Isi Form →
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection