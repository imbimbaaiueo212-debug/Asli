@extends('layouts.app')

@section('title', 'Daftar Pengajuan Kas/Barang')

@section('content')
<div class="container py-4">
    <h4 class="mb-4">FORM PENGAJUAN KAS/BARANG</h4>

    <a href="{{ route('pengajuan.create') }}" class="btn btn-primary mb-3 shadow-sm">+ Tambah Data</a>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle text-center">
            <thead>
                <tr>
                    {{-- sesuaikan colspan: sekarang ada 9 kolom --}}
                    <th colspan="9" class="table-dark text-white">DAFTAR PENGAJUAN</th>
                </tr>
                <tr class="table-secondary">
                    <th style="width: 50px;">NO</th>
                    <th style="width: 100px;">TANGGAL</th>
                    <th style="width: 150px;">biMBA UNIT</th>
                    <th style="width: 100px;">NO CABANG</th>
                    <th>KETERANGAN PENGAJUAN</th>
                    <th style="width: 120px;">HARGA (Rp)</th>
                    <th style="width: 80px;">JUMLAH</th>
                    <th style="width: 120px;">TOTAL (Rp)</th>
                    <th style="width: 150px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuan as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>

                        {{-- biMBA Unit & No Cabang dari kolom pengajuan (diisi otomatis dari controller) --}}
                        <td>{{ strtoupper($item->bimba_unit ?? '-') }}</td>
                        <td>{{ $item->no_cabang ?? '-' }}</td>

                        <td class="text-start">{{ $item->keterangan_pengajuan }}</td>
                        <td class="text-end">{{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td>{{ $item->jumlah }}</td>
                        <td class="text-end fw-bold">{{ number_format($item->total, 0, ',', '.') }}</td>
                        <td>
                            <a href="{{ route('pengajuan.edit', $item->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('pengajuan.destroy', $item->id) }}" method="POST" style="display:inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Yakin ingin menghapus pengajuan ini?')" class="btn btn-sm btn-danger">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Belum ada data pengajuan.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                @if(isset($totalPengajuan) && count($pengajuan) > 0)
                    <tr class="table-info fw-bold">
                        <td colspan="7" class="text-end">GRAND TOTAL PENGAJUAN</td>
                        <td class="text-end">{{ number_format($totalPengajuan, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                @endif
            </tfoot>
        </table>
    </div>
</div>
@endsection
