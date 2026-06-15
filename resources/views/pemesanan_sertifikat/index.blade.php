@extends('layouts.app')

@section('title', 'Pemesanan Sertifikat')

@section('content')
<div class="card card-body">
    <h1>Pemesanan Sertifikat</h1>
    <div class="px-3 px-md-0">
    <a href="{{ route('pemesanan_sertifikat.create') }}" class="btn btn-primary mb-3">Tambah Data</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-sm text-center">
            <thead>
                <tr>
                    <th>NO</th>

                    {{-- KOLOM UNIT – HANYA UNTUK ADMIN --}}
                    @if (auth()->check() && (auth()->user()->is_admin ?? false))
                        <th>UNIT</th>
                    @endif

                    <th>NIM</th>
                    <th>NAMA MURID</th>
                    <th>TMPT LAHIR</th>
                    <th>TGL LAHIR</th>
                    <th>TGL MASUK</th>
                    <th>LEVEL</th>
                    <th>MINGGU</th>
                    <th>KETERANGAN</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $index => $order)
                    <tr>
                        <td>{{ $index + 1 }}</td>

                        {{-- Hanya admin yang melihat kolom ini --}}
                        @if (auth()->check() && (auth()->user()->is_admin ?? false))
                            <td>{{ $order->bimba_unit ?? '-' }}</td>
                        @endif

                        <td>{{ $order->nim }}</td>
                        <td>{{ $order->nama_murid }}</td>
                        <td>{{ $order->tmpt_lahir }}</td>
                        <td>{{ $order->tgl_lahir ? \Carbon\Carbon::parse($order->tgl_lahir)->format('d-m-Y') : '-' }}</td>
                        <td>{{ $order->tgl_masuk ? \Carbon\Carbon::parse($order->tgl_masuk)->format('d-m-Y') : '-' }}</td>
                        <td>{{ $order->level }}</td>
                        <td>{{ $order->minggu }}</td>
                        <td>{{ $order->keterangan }}</td>
                        <td>
                            <a href="{{ route('pemesanan_sertifikat.edit', $order->id) }}" class="btn btn-sm btn-warning mb-1">Edit</a>

                            @if (auth()->user()?->role === 'admin')
                                <form action="{{ route('pemesanan_sertifikat.destroy', $order->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus data ini?')">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->check() && (auth()->user()->is_admin ?? false) ? '11' : '10' }}" class="text-center py-5 text-muted">
                            Belum ada data pemesanan sertifikat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection