@extends('layouts.app')

@section('title', 'Daftar Harga')

@section('content')
<div class="card card-body">
    <h1>Harga</h1>

    @if(auth()->check() && auth()->user()->role == 'admin')
    <div class="mb-4">
        <a href="{{ route('harga.create') }}" class="btn btn-success">Tambah Data</a>
    </div>
    @endif

    {{-- Looping untuk setiap Kategori Harga --}}
    @foreach (['BIAYA PENDAFTARAN', 'PENJUALAN', 'BIAYA SPP PER BULAN'] as $kategori)
        <h3 class="mt-4 mb-3">{{ $kategori }}</h3>
        
        @php
            $dataKategori = $items->filter(function ($item) use ($kategori) {
                return strtolower(trim($item->kategori)) == strtolower($kategori);
            });
        @endphp

        <div class="table-responsive mb-5">
            
            {{-- ===================== BIAYA PENDAFTARAN ===================== --}}
            @if ($kategori == 'BIAYA PENDAFTARAN')
                <table class="table table-bordered table-hover align-middle card-body">
                    <thead class="table-light card-body">
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Promo Gratis</th>
                            <th>Promo Umum</th>
                            <th>Promo Khusus</th>
                            <th>Spesial</th>
                            <th>Umum1</th>
                            <th>Duafa</th>
                            <th>Daftar Ulang</th>
                            <th style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dataKategori as $item)
                        <tr>
                            <td>{{ $item->kode }}</td>
                            <td>{{ $item->nama }}</td>
                            <td>Rp {{ number_format($item->umum2 ?? 0, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->promo_umum ?? 0, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->promo_2019 ?? 0, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->spesial ?? 0, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->umum1 ?? 0, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->daftar_ulang ?? 0, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->duafa ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if (auth()->user()?->role === 'admin')
                                    <a href="{{ route('harga.edit', $item->id) }}" 
                                       class="btn btn-sm btn-warning me-1"
                                       onclick="return confirm('Yakin ingin mengedit data harga {{ addslashes($item->nama) }}?');">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>

                                    <form action="{{ route('harga.destroy', $item->id) }}" method="POST"
                                          style="display:inline-block;"
                                          onsubmit="return confirm('Yakin ingin menghapus data harga {{ addslashes($item->nama) }}?\n\nTindakan ini tidak dapat dibatalkan!');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">Tidak ada data untuk kategori {{ $kategori }}.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            {{-- ===================== PENJUALAN ===================== --}}
            @elseif($kategori == 'PENJUALAN')
                <table class="table table-bordered table-hover align-middle card-body">
                    <thead class="table-light card-body">
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Harga</th>
                            <th style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dataKategori as $item)
                        <tr>
                            <td>{{ $item->kode }}</td>
                            <td>{{ $item->nama }}</td>
                            <td>Rp {{ number_format($item->harga ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if (auth()->user()?->role === 'admin')
                                    <a href="{{ route('harga.edit', $item->id) }}" 
                                       class="btn btn-sm btn-warning me-1"
                                       onclick="return confirm('Yakin ingin mengedit data harga {{ addslashes($item->nama) }}?');">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>

                                    <form action="{{ route('harga.destroy', $item->id) }}" method="POST"
                                          style="display:inline-block;"
                                          onsubmit="return confirm('Yakin ingin menghapus data harga {{ addslashes($item->nama) }}?\n\nTindakan ini tidak dapat dibatalkan!');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada data untuk kategori {{ $kategori }}.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            {{-- ===================== BIAYA SPP PER BULAN ===================== --}}
            @elseif($kategori == 'BIAYA SPP PER BULAN')
                <table class="table table-bordered table-hover align-middle card-body">
                    <thead class="table-light card-body">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Item</th>
                            <th>A</th>
                            <th>B</th>
                            <th>C</th>
                            <th>D</th>
                            <th>E</th>
                            <th>F</th>
                            <th style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dataKategori->groupBy('sub_kategori') as $subKategori => $groupSub)
                            <tr class="table card-body">
                                <td colspan="9"><strong>{{ $subKategori }}</strong></td>
                            </tr>

                            @foreach ($groupSub as $item)
                            <tr @if(isset($item->kode) && $item->kode === 'S3') class="table-warning fw-bold" @endif>
                                <td>{{ $item->kode }}</td>
                                <td>{{ $item->nama }}</td>
                                <td>Rp {{ number_format($item->a ?? 0, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($item->b ?? 0, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($item->c ?? 0, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($item->d ?? 0, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($item->e ?? 0, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($item->f ?? 0, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if (auth()->user()?->role === 'admin')
                                        <a href="{{ route('harga.edit', $item->id) }}" 
                                           class="btn btn-sm btn-warning me-1"
                                           onclick="return confirm('Yakin ingin mengedit data harga {{ addslashes($item->nama) }}?');">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>

                                        <form action="{{ route('harga.destroy', $item->id) }}" method="POST"
                                              style="display:inline-block;"
                                              onsubmit="return confirm('Yakin ingin menghapus data harga {{ addslashes($item->nama) }}?\n\nTindakan ini tidak dapat dibatalkan!');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data untuk kategori {{ $kategori }}.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    @endforeach
</div>
@endsection