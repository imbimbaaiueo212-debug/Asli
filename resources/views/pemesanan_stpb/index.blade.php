@extends('layouts.app')

@section('title', 'Pemesanan STPB')

@section('content')
<div class="container">
    <h1>Pemesanan STPB</h1>
    <a href="{{ route('pemesanan_stpb.create') }}" class="btn btn-primary mb-3">Tambah Data</a>

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

                    <th>TGL Pemesanan</th>
                    <th>NIM</th>
                    <th>NAMA MURID</th>
                    <th>TMPT LAHIR</th>
                    <th>TGL LAHIR</th>
                    <th>TGL MASUK</th>
                    <th>NAMA AYAH/IBU</th>
                    <th>LEVEL</th>
                    <th>TGL LULUS</th>
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
                            <td>{{ $order->unit->biMBA_unit ?? '-' }}</td>
                        @endif

                        <td>{{ $order->tgl_pemesanan?->translatedFormat('d M Y') ?? '-' }}</td>
                        <td>{{ $order->nim }}</td>
                        <td>{{ $order->nama_murid }}</td>
                        <td>{{ $order->tmpt_lahir }}</td>
                        <td>{{ $order->tgl_lahir ? $order->tgl_lahir->format('Y-m-d') : '-' }}</td>
                        <td>{{ $order->tgl_masuk ? $order->tgl_masuk->format('Y-m-d') : '-' }}</td>
                        <td>{{ $order->nama_orang_tua }}</td>
                        <td>{{ $order->level }}</td>
                        <td>{{ $order->tgl_lulus ? $order->tgl_lulus->format('Y-m-d') : '-' }}</td>
                        <td>{{ $order->minggu }}</td>
                        <td>{{ $order->keterangan }}</td>
                        <td>
                            <a href="{{ route('pemesanan_stpb.edit', $order->id) }}" class="btn btn-sm btn-warning mb-1">Edit</a>

                            @if (auth()->user()?->role === 'admin')
                                <form action="{{ route('pemesanan_stpb.destroy', $order->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus data ini?')">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->check() && (auth()->user()->is_admin ?? false) ? '14' : '13' }}" class="text-center py-5 text-muted">
                            Belum ada data pemesanan STPB.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection