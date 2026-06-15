@extends('layouts.app')

@section('title', 'Daftar Potongan & Tambahan')

@section('content')
<div class="d-flex flex-column vh-100 bg-light">
    <!-- Header -->
    <div class="bg-white border-bottom shadow-sm flex-shrink-0">
        <div class="d-flex justify-content-between align-items-center px-3 py-3">
            <h5 class="mb-0 fw-bold text-primary">Daftar Potongan & Tambahan</h5>
            <a href="{{ route('adjustments.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">
                + Tambah Data
            </a>
        </div>
    </div>

    <!-- Flash Message -->
    @if(session('success'))
        <div class="alert alert-success mx-3 mt-3 mb-0 rounded shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mx-3 mt-3 mb-0 rounded shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filter Card -->
    <div class="px-3 pt-3">
        <div class="card border-0 shadow rounded-3">
            <div class="card-header bg-primary text-white py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-funnel"></i> Filter Data</h6>
            </div>
            <div class="card-body p-3">
                <form method="GET" action="{{ route('adjustments.index') }}">
                    <div class="row g-2">

                        <!-- Unit biMBA – HANYA UNTUK ADMIN -->
                        @if (auth()->check() && (auth()->user()->is_admin ?? false))
                            <div class="col-12">
                                <select name="bimba_unit" class="form-select form-select-sm">
                                    <option value="">-- Semua Unit --</option>
                                    @foreach($bimbaUnits as $unit)
                                        <option value="{{ $unit }}" {{ request('bimba_unit') == $unit ? 'selected' : '' }}>{{ $unit }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-12">
                            <select name="nama" class="form-select form-select-sm">
                                <option value="">-- Semua Nama --</option>
                                @foreach($namas as $name)
                                    <option value="{{ $name }}" {{ request('nama') == $name ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <select name="month" class="form-select form-select-sm">
                                <option value="">-- Bulan --</option>
                                @foreach($months as $key => $name)
                                    <option value="{{ $key }}" {{ request('month') == $key ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <select name="year" class="form-select form-select-sm">
                                <option value="">-- Tahun --</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <select name="type" class="form-select form-select-sm">
                                <option value="">-- Semua Tipe --</option>
                                <option value="potongan" {{ request('type') == 'potongan' ? 'selected' : '' }}>Potongan</option>
                                <option value="tambahan" {{ request('type') == 'tambahan' ? 'selected' : '' }}>Tambahan</option>
                            </select>
                        </div>
                        <div class="col-12 d-grid d-md-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            <a href="{{ route('adjustments.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabel + Pagination -->
    <div class="flex-grow-1 overflow-auto d-flex flex-column">
        <div class="text-center text-muted small py-2 d-md-none">
            ← Geser ke kanan untuk melihat kolom lain →
        </div>

        <div class="flex-grow-1 overflow-auto">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover mb-0 align-middle">
                    <thead class="bg-light sticky-top text-uppercase small fw-bold">
                        <tr>
                            <th class="text-center">No</th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Jabatan</th>

                            {{-- KOLOM UNIT & CABANG – HANYA UNTUK ADMIN --}}
                            @if (auth()->check() && (auth()->user()->is_admin ?? false))
                                <th>Unit</th>
                                <th>Cabang</th>
                            @endif

                            <th>Tgl Masuk</th>
                            <th>Masa Kerja</th>
                            <th class="text-end">Nominal</th>
                            <th>Bulan</th>
                            <th>Tipe</th>
                            <th>Keterangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($adjustments as $index => $adj)
                        <tr>
                            <td class="text-center text-muted">{{ $adjustments->firstItem() + $index }}</td>
                            <td>{{ $adj->nik }}</td>
                            <td class="fw-bold">{{ $adj->nama }}</td>
                            <td>{{ $adj->jabatan ?? '-' }}</td>

                            {{-- Hanya admin yang melihat kolom ini --}}
                            @if (auth()->check() && (auth()->user()->is_admin ?? false))
                                <td>{{ $adj->bimba_unit ?? '-' }}</td>
                                <td>{{ $adj->no_cabang ?? '-' }}</td>
                            @endif

                            <td class="text-nowrap">
                                @if($adj->tanggal_masuk)
                                    {{ $adj->tanggal_masuk->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-nowrap">{{ $adj->masa_kerja }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($adj->nominal, 0, ',', '.') }}</td>
                            <td class="text-nowrap">{{ $adj->month_name }} {{ $adj->year }}</td>
                            <td>
                                <span class="badge rounded-pill {{ $adj->type == 'tambahan' ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                                    {{ $adj->type == 'tambahan' ? 'Tambahan' : 'Potongan' }}
                                </span>
                            </td>
                            <td class="text-truncate" style="max-width: 150px; cursor: pointer;" 
                                onclick="showKeterangan(`{{ addslashes($adj->keterangan ?? '-') }}`)"
                                title="Klik untuk melihat keterangan lengkap">
                                {{ Str::limit($adj->keterangan ?? '-', 30) }}
                                @if(strlen($adj->keterangan ?? '') > 30)
                                    <small class="text-primary ms-1">...</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('adjustments.edit', $adj) }}" class="btn btn-warning btn-sm rounded-pill px-3">
                                        Edit
                                    </a>

                                    @if (auth()->user()?->role === 'admin')
                                        <button type="button" class="btn btn-danger btn-sm rounded-pill px-3" 
                                                onclick="confirmDelete({{ $adj->id }}, '{{ addslashes($adj->nama) }}', '{{ $adj->month_name }} {{ $adj->year }}')">
                                            Hapus
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ auth()->check() && (auth()->user()->is_admin ?? false) ? '13' : '11' }}" class="text-center py-4 text-muted">
                                @if(request()->hasAny(['bimba_unit','nama','month','year','type']))
                                    Tidak ada data sesuai filter.
                                @else
                                    Belum ada data potongan atau tambahan.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="flex-shrink-0 bg-white border-top px-3 py-3 sticky-bottom">
            <div class="d-flex justify-content-center">
                {{ $adjustments->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- Modal Keterangan -->
    <div class="modal fade" id="keteranganModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title">Keterangan Lengkap</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="keteranganText" class="mb-0" style="white-space: pre-wrap; word-wrap: break-word;"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h6 class="modal-title">Konfirmasi Hapus</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Yakin ingin menghapus data berikut?</p>
                    <strong id="deleteNama"></strong><br>
                    <span id="deletePeriode" class="text-muted"></span>
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger btn-sm">Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
function showKeterangan(text) {
    const el = document.getElementById('keteranganText');
    if (!text || text.trim() === '' || text === '-') {
        el.innerHTML = '<em class="text-muted">Tidak ada keterangan</em>';
    } else {
        el.innerHTML = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\n/g, '<br>');
    }
    bootstrap.Modal.getOrCreateInstance(document.getElementById('keteranganModal')).show();
}

function confirmDelete(id, nama, periode) {
    document.getElementById('deleteNama').textContent = nama;
    document.getElementById('deletePeriode').textContent = periode;
    document.getElementById('deleteForm').action = `/adjustments/${id}`;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteModal')).show();
}
</script>

<!-- CSS tetap sama -->
<style>
    @media (max-width: 768px) {
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }
        .vh-100 { height: 100vh !important; }
        .flex-grow-1.overflow-auto { overflow-y: auto !important; -webkit-overflow-scrolling: touch; }
        .card { margin: 0 !important; border-radius: 0 !important; }
        .px-3, .p-3 { padding-left: 12px !important; padding-right: 12px !important; }
        .sticky-bottom { position: sticky; bottom: 0; background: white; z-index: 10; }
        td[onclick] { cursor: pointer; }
    }
</style>
@endsection