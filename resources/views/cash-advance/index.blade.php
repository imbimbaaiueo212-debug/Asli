@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">

            <!-- Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 px-3 px-md-0">
                <h3 class="mb-3 mb-md-0 text-dark fw-bold">Daftar Pengajuan Cash Advance</h3>
                <a href="{{ route('cash-advance.create') }}" class="btn btn-primary btn-lg shadow-sm">
                    <i class="fas fa-plus me-2"></i>Ajukan Baru
                </a>
            </div>

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mx-3 mx-md-0" role="alert">
                    <i class="fas fa-check-circle fa-2x me-3"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mx-3 mx-md-0" role="alert">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Card Tabel -->
            <div class="card shadow-sm border-0 mx-3 mx-md-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 py-3">Nama</th>
                                    <th class="py-3">Bulan Pengajuan</th>
                                    <th class="py-3 text-end">Nominal Pinjaman</th>
                                    <th class="py-3 text-center">Tenor</th>
                                    <th class="py-3 text-center">Status</th>
                                    <th class="py-3 text-center pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cashAdvances as $ca)
                                    <tr>
                                        <td class="ps-4 fw-medium">{{ $ca->nama }}</td>
                                        <td class="fw-medium">
                                            {{ $ca->bulan_pengajuan_indo }}
                                        </td>
                                        <td class="text-end fw-bold text-primary">
                                            Rp {{ number_format($ca->nominal_pinjam, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            {{ $ca->jangka_waktu }} bulan
                                        </td>
                                        <td class="text-center">
                                            @if($ca->status === 'approved')
                                                <span class="badge bg-success fs-6 px-3 py-2">Disetujui</span>
                                            @elseif($ca->status === 'rejected')
                                                <span class="badge bg-danger fs-6 px-3 py-2">Ditolak</span>
                                            @else
                                                <span class="badge bg-warning text-dark fs-6 px-3 py-2">Menunggu</span>
                                            @endif
                                        </td>
                                        <td class="text-center pe-4">
    <!-- Tombol Detail -->
    <a href="{{ route('cash-advance.show', $ca) }}"
       class="btn btn-outline-info btn-sm px-3 me-2">
        <i class="fas fa-eye me-1"></i> Detail
    </a>

    <!-- Tombol Hapus - HANYA UNTUK ADMIN -->
    @if(auth()->user()->isAdminUser()) {{-- Ganti dengan method cek admin Anda jika berbeda --}}
        <form action="{{ route('cash-advance.destroy', $ca) }}" 
              method="POST" 
              class="d-inline"
              onsubmit="return confirm('Yakin ingin menghapus pengajuan cash advance ini?\n\nData pengajuan dan cicilan (jika sudah di-approve) akan dihapus permanen!')">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="btn btn-outline-danger btn-sm px-3">
                <i class="fas fa-trash-alt me-1"></i> Hapus
            </button>
        </form>
    @endif
</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="py-5">
                                                <i class="fas fa-suitcase fa-5x text-muted mb-4 opacity-50"></i>
                                                <h5 class="text-muted">Belum ada pengajuan cash advance</h5>
                                                <p class="text-muted mb-4">Mulai ajukan cash advance untuk karyawan sekarang.</p>
                                                <a href="{{ route('cash-advance.create') }}"
                                                   class="btn btn-primary">
                                                    <i class="fas fa-plus me-2"></i>Ajukan Sekarang
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                @if($cashAdvances->hasPages())
                    <div class="card-footer bg-light border-top-0 py-3">
                        <div class="d-flex justify-content-center">
                            {{ $cashAdvances->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection