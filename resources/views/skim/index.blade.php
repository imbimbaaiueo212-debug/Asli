@extends('layouts.app')

@section('title', 'Skim Tunjangan')

@section('content')
<div class="card card-body">
    <h1 class="mb-4">Daftar Skim Tunjangan</h1>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ==================== Tombol Aksi & Import ==================== --}}
   <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body rounded-2">
        <div class="row g-3 align-items-center">

            <!-- Tombol Tambah -->
            <div class="col-lg-4 col-md-6">
                <a href="{{ route('skim.create') }}"
                   class="btn btn-primary w-100 shadow-sm">
                    <i class="fas fa-plus me-1"></i> Tambah Data Skim
                </a>
            </div>

            <!-- Import Excel -->
            <div class="col-lg-8 col-md-6">
                <form action="{{ route('skim.import') }}"
                      method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="input-group shadow-sm">
                        <input type="file"
                               name="file"
                               class="form-control"
                               accept=".xlsx,.xls"
                               required>
                        <button type="submit"
                                class="btn btn-info text-white">
                            <i class="fas fa-file-import me-1"></i> Import Excel
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>


    {{-- ==================== Tabel Skim Tunjangan ==================== --}}
    <div class="table-responsive">
        {{-- Mengganti div style flex Anda dengan kelas Bootstrap table-responsive --}}
        <table class="table table-bordered table-striped table-hover align-middle text-center">
            <thead>
                <tr>
                    <th colspan="10" class="table-light text-dark fw-bold card-body">SKIM TUNJANGAN</th>
                </tr>
                <tr class="card-body">
                    <th>ID</th>
                    <th>Jabatan</th>
                    <th>Masa Kerja</th>
                    <th>Status</th>
                    <th>Tunjangan Pokok</th>
                    <th>Harian</th>
                    <th>Fungsional</th>
                    <th>Kesehatan</th>
                    <th>THP</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($skims as $skim)
                <tr class="card-body">
                    <td>{{ $skim->id }}</td>
                    <td class="text-start">{{ $skim->jabatan }}</td>
                    <td>{{ $skim->masa_kerja }}</td>
                    <td>{{ $skim->status }}</td>
                    <td class="text-end">Rp {{ number_format($skim->tunj_pokok, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($skim->harian, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($skim->fungsional, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($skim->kesehatan, 0, ',', '.') }}</td>
                    <td class="text-end fw-bold">Rp {{ number_format($skim->thp, 0, ',', '.') }}</td>
                    <td>
    <a href="{{ route('skim.edit', $skim->id) }}" class="btn btn-sm btn-warning">Edit</a>

    @if (auth()->user()?->role === 'admin')
        <form action="{{ route('skim.destroy', $skim->id) }}" method="POST" 
              style="display:inline-block;" 
              onsubmit="return confirm('Yakin ingin menghapus data skim ini? Tindakan ini tidak dapat dibatalkan.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
        </form>
    @endif
</td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center">Belum ada data skim tunjangan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Tambahkan pagination jika ada --}}
    {{-- @if (isset($skims) && method_exists($skims, 'links'))
        <div class="d-flex justify-content-center mt-3">
            {{ $skims->links() }}
        </div>
    @endif --}}
</div>
@endsection