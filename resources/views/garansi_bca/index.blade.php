@extends('layouts.app')

@section('title', 'Garansi BCA 372')

@section('content')
<div class="card card-body">

    <h1 class="mb-4">Daftar Garansi BCA 372</h1>

    {{-- TOMBOL --}}
    <div class="px-3 px-md-0">
    <a href="{{ route('garansi-bca.create') }}" class="btn btn-primary mb-3">
        Tambah Data
    </a>
    </div>

    {{-- NOTIF --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif


    {{-- =============================== --}}
    {{-- ✅ TABEL 1: GARANSI DIBERIKAN --}}
    {{-- =============================== --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <h4 class="mb-3 text-primary">
                📄 Garansi Yang Sudah Diberikan
            </h4>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Virtual Account</th>
                            <th>Nama Murid</th>
                            <th>TTL</th>
                            <th>Tanggal Masuk</th>
                            <th>Tanggal Diberikan</th>
                            <th>Orang Tua</th>
                            <th>Sumber</th> {{-- 🔥 TAMBAH DI SINI --}}
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $i => $d)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $d->virtual_account ?? '-' }}</td>
                                <td>{{ $d->nama_murid }}</td>
                                <td>{{ $d->tempat_tanggal_lahir }}</td>

                                <td>
                                    {{ $d->tanggal_masuk 
                                        ? $d->tanggal_masuk->format('d-m-Y') 
                                        : '-' }}
                                </td>

                                <td>
                                    {{ $d->tanggal_diberikan 
                                        ? $d->tanggal_diberikan->format('d-m-Y') 
                                        : '-' }}
                                </td>

                                <td>{{ $d->nama_orang_tua_wali }}</td>

                                <td>
                                    <span 
                                        class="badge bg-success"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Garansi diberikan otomatis dari unit">
                                        Pemberian
                                    </span>
                                </td>

                                <td class="text-nowrap">
                                    <a href="{{ route('garansi-bca.edit', $d->id) }}"
                                       class="btn btn-warning btn-sm">Edit</a>

                                    <a href="{{ route('garansi-bca.pdf', $d->id) }}"
                                       target="_blank"
                                       class="btn btn-info btn-sm">PDF</a>

                                    @if(auth()->user()?->role === 'admin')
                                        <form action="{{ route('garansi-bca.destroy', $d->id) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Yakin?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    Tidak ada data
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>


    {{-- =============================== --}}
    {{-- ✅ TABEL 2: PENGAJUAN GARANSI --}}
    {{-- =============================== --}}
    <div class="card shadow-sm border-warning">
        <div class="card-body">

            <h4 class="mb-3 text-success">
                📝 Pengajuan Garansi Murid
            </h4>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-warning">
                        <tr>
                            <th>No</th>
                            <th>Nama Murid</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Alasan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengajuan as $i => $p)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $p->nama_murid }}</td>

                                <td>
                                    {{ $p->tgl_pengajuan 
                                        ? \Carbon\Carbon::parse($p->tgl_pengajuan)->format('d-m-Y') 
                                        : '-' }}
                                </td>

                                <td>{{ $p->alasan ?? '-' }}</td>

                                <td>
                                    @if($p->status == 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($p->status == 'disetujui')
                                        <span class="badge bg-success">Disetujui</span>
                                    @else
                                        <span class="badge bg-danger">Ditolak</span>
                                    @endif
                                </td>

                                <td>
                                    @if($p->status == 'pending')
                                        <form action="{{ route('garansi.approve', $p->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-success btn-sm">Approve</button>
                                        </form>

                                        <form action="{{ route('garansi.reject', $p->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-danger btn-sm">Tolak</button>
                                        </form>
                                    @else
                                        <span class="text-muted">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Tidak ada pengajuan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>
@endsection