@extends('layouts.app')

@section('title', 'Sertifikat Beasiswa')

@section('content')
<div class="container-fluid my-4 px-0 px-md-3">
    <div class="card shadow-sm border-0">
        <div class="card-body px-0 px-md-3">

            <h1 class="mb-4 px-3 px-md-0">Masa Aktif (Dhuafa & BNF)</h1>

            <div class="px-3 px-md-0">
                <a href="{{ route('sertifikat-beasiswa.create') }}"
                   class="btn btn-primary mb-3">
                    Tambah Data
                </a>

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
            </div>

            {{-- ================= TABLE ================= --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle w-100 text-nowrap">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama</th>

                            {{-- Desktop only - kolom yang selalu tampil --}}
                            <th class="d-none d-md-table-cell">Virtual Account</th>

                            {{-- Kolom Unit hanya untuk ADMIN --}}
                            @if(auth()->user()->isAdminUser())
                                <th class="d-none d-md-table-cell">Nama Unit</th>
                            @endif

                            <th class="d-none d-md-table-cell">Tanggal Lahir</th>
                            <th class="d-none d-md-table-cell">Alamat</th>
                            <th class="d-none d-md-table-cell">Nama Orang Tua</th>
                            <th class="d-none d-md-table-cell">Golongan</th>
                            <th class="d-none d-md-table-cell">Jumlah Beasiswa</th>
                            <th class="d-none d-md-table-cell">Tanggal Mulai</th>
                            <th class="d-none d-md-table-cell">Tanggal Selesai</th>
                            <th class="d-none d-md-table-cell">Periode</th>

                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($beasiswa as $index => $b)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $b->nim }}</td>
                            <td class="fw-semibold">{{ $b->nama }}</td>

                            {{-- Virtual Account --}}
                            <td class="d-none d-md-table-cell">{{ $b->virtual_account ?? '-' }}</td>

                            {{-- Nama Unit - HANYA ADMIN --}}
                            @if(auth()->user()->isAdminUser())
                                <td class="d-none d-md-table-cell">{{ $b->bimba_unit ?? '-' }}</td>
                            @endif

                            {{-- Kolom lain --}}
                            <td class="d-none d-md-table-cell">
                                {{ optional($b->tanggal_lahir)->format('d-m-Y') ?? '-' }}
                            </td>
                            <td class="d-none d-md-table-cell">{{ $b->alamat ?? '-' }}</td>
                            <td class="d-none d-md-table-cell">{{ $b->nama_orang_tua ?? '-' }}</td>
                            <td class="d-none d-md-table-cell">{{ $b->golongan ?? '-' }}</td>
                            <td class="d-none d-md-table-cell">
                                {{ number_format($b->jumlah_beasiswa ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="d-none d-md-table-cell">
                                {{ optional($b->tanggal_mulai)->format('d-m-Y') ?? '-' }}
                            </td>
                            <td class="d-none d-md-table-cell">
                                {{ optional($b->tanggal_selesai)->format('d-m-Y') ?? '-' }}
                            </td>
                            <td class="d-none d-md-table-cell">{{ $b->periode_bea_ke ?? '-' }}</td>

                            {{-- ================= AKSI ================= --}}
                            <td>
                                {{-- Mobile --}}
                                <button class="btn btn-sm btn-info d-md-none"
                                        data-bs-toggle="modal"
                                        data-bs-target="#detailModal{{ $b->id }}">
                                    Detail
                                </button>

                                {{-- Desktop --}}
                                <a href="{{ route('sertifikat-beasiswa.edit', $b->id) }}"
                                   class="btn btn-sm btn-warning d-none d-md-inline">
                                    Edit
                                </a>
                                <a href="{{ route('sertifikat-beasiswa.pdf', $b->id) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-success">
                                    PDF
                                </a>

                                @if (auth()->user()?->role === 'admin')
                                    <form action="{{ route('sertifikat-beasiswa.destroy', $b->id) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger"
                                                onclick="return confirm('Yakin ingin menghapus data ini?')">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>

                        {{-- ================= MODAL MOBILE ================= --}}
                        <div class="modal fade"
                             id="detailModal{{ $b->id }}"
                             tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            Detail Sertifikat Beasiswa
                                        </h5>
                                        <button type="button"
                                                class="btn-close"
                                                data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Virtual Account:</strong> {{ $b->virtual_account ?? '-' }}</p>
                                        <p><strong>NIM:</strong> {{ $b->nim }}</p>
                                        <p><strong>Nama:</strong> {{ $b->nama }}</p>

                                        {{-- Unit hanya tampil di modal jika ADMIN --}}
                                        @if(auth()->user()->isAdminUser())
                                            <p><strong>Unit:</strong> {{ $b->bimba_unit ?? '-' }}</p>
                                        @endif

                                        <p><strong>Tanggal Lahir:</strong>
                                            {{ optional($b->tanggal_lahir)->format('d-m-Y') ?? '-' }}
                                        </p>
                                        <p><strong>Alamat:</strong> {{ $b->alamat ?? '-' }}</p>
                                        <p><strong>Nama Orang Tua:</strong> {{ $b->nama_orang_tua ?? '-' }}</p>
                                        <p><strong>Golongan:</strong> {{ $b->golongan ?? '-' }}</p>
                                        <p><strong>Jumlah Beasiswa:</strong>
                                            Rp {{ number_format($b->jumlah_beasiswa ?? 0, 0, ',', '.') }}
                                        </p>
                                        <p><strong>Tanggal Mulai:</strong>
                                            {{ optional($b->tanggal_mulai)->format('d-m-Y') ?? '-' }}
                                        </p>
                                        <p><strong>Tanggal Selesai:</strong>
                                            {{ optional($b->tanggal_selesai)->format('d-m-Y') ?? '-' }}
                                        </p>
                                        <p><strong>Periode:</strong> {{ $b->periode_bea_ke ?? '-' }}</p>
                                    </div>
                                    <div class="modal-footer">
                                        <a href="{{ route('sertifikat-beasiswa.edit', $b->id) }}"
                                           class="btn btn-warning btn-sm">
                                            Edit
                                        </a>
                                        <a href="{{ route('sertifikat-beasiswa.pdf', $b->id) }}"
                                           target="_blank"
                                           class="btn btn-success btn-sm">
                                            Lihat PDF
                                        </a>
                                        <button type="button"
                                                class="btn btn-secondary btn-sm"
                                                data-bs-dismiss="modal">
                                            Tutup
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isAdminUser() ? '14' : '13' }}" class="text-center">
                                Tidak ada data Sertifikat Beasiswa
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