@extends('layouts.app')
@section('title', 'Data Humas')

@section('content')
<div class="card card=body">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Data Humas Otomatis</h5>
        </div>

        @if(session('success'))
            <div class="alert alert-success mx-4 mt-3">{{ session('success') }}</div>
        @endif

        <div class="card-body">
            <!-- FORM FILTER -->
            <form method="GET" action="{{ route('humas.index') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Nama Orang Tua</label>
                        <select name="nama" class="form-select">
                            <option value="">-- Semua Orang Tua --</option>
                            @foreach($humas->pluck('nama')->unique()->sort() as $nama)
                                <option value="{{ $nama }}" {{ request('nama') == $nama ? 'selected' : '' }}>
                                    {{ $nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if (auth()->check() && (auth()->user()->is_admin ?? false))
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Unit biMBA</label>
                            <select name="unit" class="form-select">
                                <option value="">-- Semua Unit --</option>
                                @foreach($humas->pluck('bimba_unit')->unique()->sort() as $unit)
                                    @if($unit)
                                        <option value="{{ $unit }}" {{ request('unit') == $unit ? 'selected' : '' }}>
                                            {{ $unit }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            Filter
                        </button>
                        @if(request()->hasAny(['nama', 'unit']))
                            <a href="{{ route('humas.index') }}" class="btn btn-secondary">
                                Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>

           <!-- Tombol Import & Export -->
<div class="text-end mb-3">
    <button type="button" class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#importModal">
        <i class="bi bi-cloud-arrow-up"></i> Import Excel
    </button>
    <a href="{{ route('huma.export', request()->query()) }}" class="btn btn-info btn-sm">
        <i class="bi bi-download"></i> Export ke Excel
    </a>
    <a href="{{ route('humas.create') }}" class="btn btn-success btn-sm">
        + Tambah Data
    </a>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Data Humas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('humas.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="file" class="form-label">Pilih File Excel (.xlsx, .xls, .csv)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Import Sekarang</button>
                </form>
                <small class="text-muted mt-2 d-block">
                    Pastikan header sesuai: Tgl Reg, NIH, Nama Humas, Pekerjaan, No. Telp, Unit, Cabang
                </small>
            </div>
        </div>
    </div>
</div>

            <!-- TABEL HUMAS -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-primary">
                        <tr class="text-center">
                            <th>No</th>
                            <th>Tgl Reg</th>
                            <th>NIH</th>
                            <th>Nama Humas</th>
                            <th>Pekerjaan</th>
                            <th>No. Telp</th>
                            <th>Unit</th>
                            <th>Cabang</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($humas as $i => $h)
                            <tr>
                                <td class="text-center">{{ $loop->iteration + $humas->firstItem() - 1 }}</td>
                                <td class="text-center">{{ $h->tgl_reg?->format('d/m/Y') }}</td>
                                <td class="text-center fw-bold text-primary">{{ $h->nih }}</td>
                                <td>
                                    <strong>{{ $h->nama }}</strong>
                                    <br>
                                    <small class="text-success">Otomatis dari murid baru</small>
                                </td>
                                <td>{{ $h->pekerjaan ?? '-' }}</td>
                                <td class="text-center">
                                    @if($h->no_telp)
                                        <a href="https://wa.me/62{{ ltrim($h->no_telp, '0') }}" target="_blank" class="text-success">
                                            {{ $h->no_telp }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $h->bimba_unit ?? '-' }}</td>
                                <td class="text-center fw-bold text-danger">{{ $h->no_cabang ?? '-' }}</td>
                                <td class="text-center">
    <a href="{{ route('humas.edit', $h->id) }}" class="btn btn-sm btn-warning">Edit</a>

    @if (auth()->user()?->role === 'admin')
        <form action="{{ route('humas.destroy', $h->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-danger" onclick="return confirm('Hapus Humas ini?')">Hapus</button>
        </form>
    @endif
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    Belum ada data humas.<br>
                                    <small>Data akan muncul otomatis saat ada murid baru yang dibawa orang tua.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION & TOTAL -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Total: <strong>{{ $humas->total() }}</strong> humas
                    @if($humas->hasPages())
                        — Menampilkan {{ $humas->firstItem() }} sampai {{ $humas->lastItem() }}
                    @endif
                </div>
                {{ $humas->links() }}
            </div>
        </div>
    </div>
</div>
@endsection