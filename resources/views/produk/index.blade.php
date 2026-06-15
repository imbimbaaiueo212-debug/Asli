@extends('layouts.app')

@section('title', 'Daftar Produk')

@section('content')
<div class="container-fluid py-4 pb-5">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11">

            <!-- Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
                <h1 class="fw-bold mb-3 mb-md-0">Daftar Produk</h1>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('produk.create') }}" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus me-1"></i> Tambah Data
                    </a>
                    <a href="{{ route('produk.export') }}" class="btn btn-outline-success shadow-sm">
                        <i class="fas fa-file-export me-1"></i> Export ke Excel
                    </a>
                    <button type="button" class="btn btn-outline-primary shadow-sm" id="btnOpenImportModal">
                        <i class="fas fa-file-import me-1"></i> Import dari Excel
                    </button>
                </div>
            </div>

            <!-- Alerts -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {!! session('error') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Card Filter & Actions -->
            <div class="card shadow border-0 mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('produk.index') }}" id="formFilter">
                        <div class="row g-3">

                            <!-- Filter khusus Admin -->
                            @if(auth()->user()->isAdminUser())
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">
                                    <label class="form-label fw-bold small">Unit</label>
                                    <select name="bimba_unit" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="">Semua Unit</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->biMBA_unit }}"
                                                {{ request('bimba_unit') == $unit->biMBA_unit ? 'selected' : '' }}>
                                                {{ $unit->biMBA_unit }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <!-- Label -->
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">
                                <label class="form-label fw-bold small">Label</label>
                                <select name="label" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">Semua Label</option>
                                    @foreach($labels as $label)
                                        <option value="{{ $label }}" {{ request('label') == $label ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Jenis -->
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">
                                <label class="form-label fw-bold small">Jenis</label>
                                <select name="jenis" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">Semua Jenis</option>
                                    @foreach($jenises as $jenis)
                                        <option value="{{ $jenis }}" {{ request('jenis') == $jenis ? 'selected' : '' }}>
                                            {{ $jenis }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Kategori -->
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">
                                <label class="form-label fw-bold small">Kategori</label>
                                <select name="kategori" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">Semua Kategori</option>
                                    @foreach($kategoris as $kategori)
                                        <option value="{{ $kategori }}" {{ request('kategori') == $kategori ? 'selected' : '' }}>
                                            {{ $kategori }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Pendataan -->
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">
                                <label class="form-label fw-bold small">Pendataan</label>
                                <select name="pendataan" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">Semua</option>
                                    @foreach($pendataans as $item)
                                        <option value="{{ $item }}" {{ request('pendataan') == $item ? 'selected' : '' }}>
                                            {{ $item }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Search & Per Page -->
                            <div class="col-12 col-md-8 col-lg-6 col-xl-4">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control" placeholder="Cari kode / nama produk..." 
                                           value="{{ request('search') }}">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Per Page & Reset -->
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">
                                <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                                    @foreach([10, 20, 50, 100, 200, 500] as $size)
                                        <option value="{{ $size }}" {{ request('per_page', 20) == $size ? 'selected' : '' }}>
                                            {{ $size }} baris
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @if(request()->hasAny(['bimba_unit', 'label', 'jenis', 'kategori', 'pendataan', 'search', 'per_page']))
                                <div class="col-auto">
                                    <a href="{{ route('produk.index') }}" class="btn btn-outline-secondary btn-sm mt-4 mt-md-0">
                                        <i class="fas fa-undo me-1"></i> Reset Filter
                                    </a>
                                </div>
                            @endif

                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Produk -->
            <div class="card shadow border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0 text-center">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 60px;">No</th>
                                    <th>Kode</th>
                                    <th>Kategori</th>
                                    <th>Jenis</th>
                                    <th>Label</th>
                                    <th class="text-start">Nama Produk</th>
                                    <th>Satuan</th>
                                    <th>Berat</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                    <th>Isi</th>
                                    <th>Pendataan</th>
                                    <th style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($produks as $index => $produk)
                                    <tr>
                                        <td>{{ $produks->firstItem() + $index }}</td>
                                        <td class="fw-bold">{{ $produk->kode }}</td>
                                        <td>{{ $produk->kategori ?? '-' }}</td>
                                        <td>{{ $produk->jenis ?? '-' }}</td>
                                        <td>{{ $produk->label ?? '-' }}</td>
                                        <td class="text-start">{{ $produk->nama_produk }}</td>
                                        <td>{{ $produk->satuan ?? 'Pcs' }}</td>
                                        <td>{{ number_format($produk->berat ?? 0, 2, ',', '.') }} gr</td>
                                        <td class="text-end fw-bold text-success">
                                            Rp {{ number_format($produk->harga ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $produk->status == 'Paket' ? 'warning' : 'info' }} text-dark">
                                                {{ $produk->status ?? 'Satuan' }}
                                            </span>
                                        </td>
                                        <td>{{ $produk->isi ?? '-' }}</td>
                                        <td>
                                            @if($produk->pendataan)
                                                <i class="fas fa-check-circle text-success fa-lg"></i>
                                            @else
                                                <i class="fas fa-times-circle text-danger fa-lg"></i>
                                            @endif
                                        </td>
                                        <td>
    <div class="d-flex justify-content-center gap-1">
        <a href="{{ route('produk.edit', $produk) }}" class="btn btn-sm btn-warning" title="Edit">
            <i class="fas fa-edit"></i>
        </a>

        @if (auth()->user()?->role === 'admin')
            <form action="{{ route('produk.destroy', $produk) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" title="Hapus"
                        onclick="return confirm('Yakin ingin menghapus produk ini?')">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        @endif
    </div>
</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center py-5 text-muted">
                                            <i class="fas fa-box-open fa-3x mb-3 d-block text-secondary"></i>
                                            Belum ada data produk
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="text-muted small">
                            Menampilkan {{ $produks->firstItem() }} - {{ $produks->lastItem() }} 
                            dari {{ $produks->total() }} produk
                        </div>
                        {{ $produks->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="modalImportExcel" tabindex="-1" aria-labelledby="modalImportLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalImportLabel">
                    <i class="fas fa-file-import me-2"></i> Import Data Produk
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('produk.import') }}" method="POST" enctype="multipart/form-data" id="formImport">
                @csrf

                <div class="modal-body">
                    @if(auth()->user()->isAdminUser())
                        <div class="mb-4">
                            <label class="form-label fw-bold">Unit Tujuan</label>
                            <select name="bimba_unit" class="form-select" required>
                                <option value="">-- Pilih Unit --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->biMBA_unit }}">{{ $unit->biMBA_unit }}</option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted small">Produk akan dimasukkan ke unit yang dipilih</div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold">File Excel (.xlsx / .xls)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                        <div class="form-text text-muted small mt-1">
                            Pastikan file memiliki kolom: kode, nama_produk, dll. sesuai template.
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitImport">
                        <span id="textNormal">Import Data</span>
                        <span id="textLoading" class="d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                            Sedang memproses...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Buka modal import
    document.getElementById('btnOpenImportModal')?.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('modalImportExcel'));
        modal.show();
    });

    // Loading saat submit import
    document.getElementById('formImport')?.addEventListener('submit', function(e) {
        const btn = document.getElementById('btnSubmitImport');
        const normal = document.getElementById('textNormal');
        const loading = document.getElementById('textLoading');

        normal.classList.add('d-none');
        loading.classList.remove('d-none');
        btn.disabled = true;
    });
</script>
@endpush