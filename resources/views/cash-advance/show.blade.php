@extends('layouts.app')

@section('content')
<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-12">
            <h1 class="h4 mb-4 fw-bold text-center text-md-start px-3 px-md-0">
                Detail Pengajuan Cash Advance
            </h1>

            <div class="card shadow mx-3 mx-md-0">
                <div class="card-body p-4 p-md-5">

                    <!-- Data Karyawan -->
                    <h2 class="h6 fw-semibold mb-4 text-primary">Data Karyawan</h2>
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <div class="text-sm text-muted mb-1">Nama</div>
                            <div class="h5 fw-semibold">{{ $cashAdvance->nama }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-sm text-muted mb-1">Jabatan</div>
                            <div class="h5">{{ $cashAdvance->jabatan ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-sm text-muted mb-1">No. Telepon</div>
                            <div class="h5">{{ $cashAdvance->no_telepon ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-sm text-muted mb-1">Status Pengajuan</div>
                            <span class="badge rounded-pill px-4 py-2 fs-6
                                {{ $cashAdvance->status == 'approved' ? 'bg-success' :
                                   ($cashAdvance->status == 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                {{ $cashAdvance->status == 'approved' ? 'Disetujui' :
                                   ($cashAdvance->status == 'rejected' ? 'Ditolak' : 'Menunggu Persetujuan') }}
                            </span>
                        </div>
                    </div>

                    <hr class="my-5">

                    <!-- Detail Pinjaman -->
                    <h2 class="h6 fw-semibold mb-4 text-primary">Detail Pinjaman</h2>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="text-sm text-muted mb-1">Bulan Pengajuan</div>
                            <div class="h5">{{ $cashAdvance->bulan_pengajuan_indo }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-sm text-muted mb-1">Nominal Pinjaman</div>
                            <div class="h5 fw-bold text-primary">
                                Rp {{ number_format($cashAdvance->nominal_pinjam, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-sm text-muted mb-1">Jangka Waktu</div>
                            <div class="h5">{{ $cashAdvance->jangka_waktu }} bulan</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-sm text-muted mb-1">Angsuran per Bulan</div>
                            <div class="h5 fw-bold">
                                @php
                                    // Hitung angsuran sementara jika pending/rejected
                                    $angsuran = $cashAdvance->nominal_pinjam / $cashAdvance->jangka_waktu;
                                    $angsuran = round($angsuran); // pembulatan standar
                                @endphp

                                @if($cashAdvance->status == 'approved')
                                    <span class="text-success">
                                        Rp {{ number_format($cashAdvance->angsuran_per_bulan, 0, ',', '.') }}
                                    </span>
                                @elseif($cashAdvance->status == 'pending' || $cashAdvance->status == 'rejected')
                                    <span class="text-warning">
                                        Rp {{ number_format($angsuran, 0, ',', '.') }}
                                        <small class="d-block text-muted fst-italic">(Estimasi)</small>
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="text-sm text-muted mb-1">Keperluan</div>
                            <div class="h5">{{ $cashAdvance->keperluan }}</div>
                        </div>
                    </div>

                    <!-- Tombol Approval (hanya jika pending) -->
                    @if($cashAdvance->status == 'pending')
                        <hr class="my-5">
                        <div class="d-flex flex-column flex-sm-row gap-3">
                            <form action="{{ route('cash-advance.approve', $cashAdvance) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success px-5 py-2">
                                    <i class="bi bi-check-lg me-2"></i>Setujui Pengajuan
                                </button>
                            </form>

                            <form action="{{ route('cash-advance.reject', $cashAdvance) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger px-5 py-2">
                                    <i class="bi bi-x-lg me-2"></i>Tolak Pengajuan
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- Kembali -->
                    <div class="mt-5">
                        <a href="{{ route('cash-advance.index') }}" class="btn btn-outline-secondary px-4">
                            ← Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection