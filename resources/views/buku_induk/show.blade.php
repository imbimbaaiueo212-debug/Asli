@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Detail Murid: {{ $bukuInduk->nama }}</h2>

    <h4>History Perubahan</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Aksi</th>
                <th>User</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach($histories as $history)
                <tr>
                    <td>{{ $history->created_at }}</td>
                    <td>{{ $history->action }}</td>
                    <td>{{ $history->user }}</td>
                    <td><pre>{{ json_encode($history->data, JSON_PRETTY_PRINT) }}</pre></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
