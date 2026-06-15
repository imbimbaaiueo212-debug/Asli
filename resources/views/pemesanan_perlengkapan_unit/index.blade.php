@extends('layouts.app')

@section('title', 'Daftar Pemesanan Perlengkapan Unit')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-gradient-primary text-black py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">
                            <i class="fas fa-boxes me-3"></i>
                            Daftar Pemesanan Perlengkapan Unit
                        </h3>
                        <a href="{{ route('pemesanan_perlengkapan_unit.create') }}" 
                           class="btn btn-primary btn-lg shadow-sm">
                            <i class="fas fa-plus me-2"></i> Tambah Data
                        </a>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show mx-4 mt-4" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">NO</th>

                                    {{-- KOLOM UNIT – HANYA UNTUK ADMIN --}}
                                    @if (auth()->check() && (auth()->user()->is_admin ?? false))
                                        <th width="12%">UNIT</th>
                                    @endif

                                    <th width="10%">TANGGAL</th>
                                    <th width="8%">MINGGU</th>
                                    <th width="10%">KODE</th>
                                    <th width="10%">KATEGORI</th>
                                    <th width="20%">NAMA BARANG</th>
                                    <th width="8%">JUMLAH</th>
                                    <th width="12%">HARGA SATUAN</th>
                                    <th width="15%">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $index => $order)
                                    <tr class="table-light">
                                        <td class="fw-bold">{{ $loop->iteration }}</td>

                                        {{-- Hanya admin yang melihat kolom ini --}}
                                        @if (auth()->check() && (auth()->user()->is_admin ?? false))
                                            <td>
                                                @if($order->unit)
                                                    <span class="badge bg-info text-dark">
                                                        {{ $order->unit->no_cabang }} | {{ strtoupper($order->unit->biMBA_unit) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">Tidak ada unit</span>
                                                @endif
                                            </td>
                                        @endif

                                        <td>
                                            @if($order->tanggal_pemesanan)
                                                {{ \Carbon\Carbon::parse($order->tanggal_pemesanan)->format('d-m-Y') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <td>
                                            <span class="badge bg-primary fs-6">
                                                {{ $order->minggu ? 'Minggu ' . $order->minggu : '-' }}
                                            </span>
                                        </td>

                                        <td class="fw-bold">{{ $order->kode }}</td>
                                        <td>
                                            @php
                                                $kategoriBadge = match(strtolower($order->kategori ?? '')) {
                                                    'perlengkapan' => 'bg-success',
                                                    'alat tulis'   => 'bg-warning text-dark',
                                                    'kebersihan'   => 'bg-info text-dark',
                                                    'elektronik'   => 'bg-danger',
                                                    'makanan'      => 'bg-primary',
                                                    default        => 'bg-secondary'
                                                };
                                            @endphp
                                            <span class="badge {{ $kategoriBadge }} fs-6">
                                                {{ $order->kategori ? ucwords(strtolower($order->kategori)) : '-' }}
                                            </span>
                                        </td>   
                                        
                                        <td class="text-start fw-bold">{{ $order->nama_barang }}</td>
                                        
                                        <td>{{ number_format($order->jumlah, 0, ',', '.') }}</td>
                                        
                                        <td>Rp {{ number_format($order->harga, 0, ',', '.') }}</td>

                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('pemesanan_perlengkapan_unit.edit', $order->id) }}" 
                                                   class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                @if (auth()->user()?->role === 'admin')
                                                    <form action="{{ route('pemesanan_perlengkapan_unit.destroy', $order->id) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-danger btn-sm" 
                                                                title="Hapus"
                                                                onclick="return confirm('Yakin ingin menghapus pemesanan ini?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->check() && (auth()->user()->is_admin ?? false) ? '10' : '9' }}" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-4x mb-4 opacity-50"></i>
                                            <h5>Belum ada data pemesanan perlengkapan</h5>
                                            <p>Klik tombol "Tambah Pemesanan" untuk memulai.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Jika nanti pakai pagination -->
                    @if(method_exists($orders, 'links'))
                        <div class="card-footer bg-light py-4">
                            <div class="d-flex justify-content-center">
                                {{ $orders->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection