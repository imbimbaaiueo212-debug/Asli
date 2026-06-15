@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h3>Penyesuaian RB Guru</h3>
        <a href="{{ route('rb.create') }}" class="btn btn-primary mb-3">Tambah Data</a>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('rb.import') }}" method="POST" enctype="multipart/form-data" class="import-form d-inline-block mb-3">
            @csrf
            <div class="input-group">
                <input type="file" name="file" class="form-control" required>
                <button type="submit" class="btn btn-secondary">Import Excel</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th colspan="4">PENYESUAIAN RB GURU</th>
                        <th>Aksi</th>
                    </tr>
                    <tr>
                        <th>JUMLAH MURID</th>
                        <th>SLOT ROMBIM</th>
                        <th>JAM KEGIATAN</th>
                        <th>PENYESUAIAN RB</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lastJumlahMurid = null;
                        $lastSlotRombim = null;
                    @endphp

                    {{-- Memeriksa apakah $data ada dan iterable sebelum loop --}}
                    @forelse ($data as $item)
                        <tr>
                            {{-- Menampilkan 'JUMLAH MURID' hanya jika berbeda dari baris sebelumnya (Merge Cells Illusion) --}}
                            <td>
                                @if ($item->jumlah_murid != $lastJumlahMurid)
                                    {{ $item->jumlah_murid }}
                                    @php $lastJumlahMurid = $item->jumlah_murid; @endphp
                                @endif
                            </td>
                            {{-- Menampilkan 'SLOT ROMBIM' hanya jika berbeda dari baris sebelumnya (Merge Cells Illusion) --}}
                            <td>
                                @if ($item->slot_rombim != $lastSlotRombim)
                                    {{ $item->slot_rombim }}
                                    @php $lastSlotRombim = $item->slot_rombim; @endphp
                                @endif
                            </td>
                            <td>{{ $item->jam_kegiatan }}</td>
                            <td>{{ $item->penyesuaian_rb }}</td>
                           <td>
    <a href="{{ route('rb.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>

    @if (auth()->user()?->role === 'admin')
        <form action="{{ route('rb.destroy', $item->id) }}" method="POST" style="display:inline-block;"
              onsubmit="return confirm('Yakin mau hapus data ini?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
        </form>
    @endif
</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Data Penyesuaian RB Guru belum tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div> {{-- .table-responsive --}}
    </div> {{-- .container --}}
@endsection