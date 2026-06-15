@extends('layouts.app')

@section('title', 'Rekap RBAS')

@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="mb-4">Rekap RBAS</h3>

                {{-- Filter Form --}}
                {{-- FILTER FORM --}}
<form method="GET" 
      action="{{ route('penerimaan.rbas') }}" 
      id="filterForm" 
      class="card card-header shadow-sm border border-light rounded-3 mb-4">

    <div class="row g-3 align-items-end px-3 py-3">
        <div class="col-12 col-md-3 col-lg-2">
            <label class="form-label small text-muted mb-1">Bulan</label>
            <select name="bulan" 
                    id="bulan" 
                    class="form-select form-select-sm">
                <option value="">-- Semua Bulan --</option>
                @foreach($bulanOptions as $b)
                    <option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>
                        {{ ucfirst($b) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-2 col-lg-2">
            <label class="form-label small text-muted mb-1">Tahun</label>
            <input type="number" 
                   name="tahun" 
                   id="tahun" 
                   class="form-control form-control-sm" 
                   value="{{ $tahun }}" 
                   min="2020" 
                   placeholder="Tahun">
        </div>

        @if (auth()->check() && (auth()->user()->is_admin ?? false))
        <div class="col-12 col-md-3 col-lg-3">
            <label class="form-label small text-muted mb-1">biMBA Unit</label>
            <select name="unit" 
                    id="unit" 
                    class="form-select form-select-sm">
                <option value="">-- Semua Unit --</option>
                @foreach($unitOptions as $u)
                    <option value="{{ $u }}" {{ $unitFilter == $u ? 'selected' : '' }}>
                        {{ $u }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="col-12 col-md-4 col-lg-3">
            <label class="form-label small text-muted mb-1">NIM | Nama Murid</label>
            <select name="search" 
                    id="searchMurid" 
                    class="form-select form-select-sm">
                <option value="">-- Ketik atau Pilih NIM / Nama Murid --</option>
                @foreach($muridAktif as $m)
                    <option value="{{ $m->nim }}" {{ $search == $m->nim ? 'selected' : '' }}>
                        {{ $m->nim }} | {{ $m->nama_murid }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-1 col-lg-2 d-flex align-items-end">
            <a href="{{ route('penerimaan.rbas') }}" 
               class="btn btn-secondary btn-sm w-100">
                Reset
            </a>
        </div>
    </div>
</form>

                {{-- Info Filter Aktif --}}
                @if($unitFilter || $bulan || $tahun != now()->year || $search)
                    <div class="alert alert-info small mb-4">
                        <strong>Filter aktif:</strong>
                        {{ $unitFilter ? 'Unit: ' . $unitFilter : '' }}
                        {{ $bulan ? ($unitFilter ? ' • ' : '') . 'Bulan: ' . ucfirst($bulan) : '' }}
                        {{ $tahun != now()->year ? (($unitFilter || $bulan) ? ' • ' : '') . 'Tahun: ' . $tahun : '' }}
                        {{ $search ? ((($unitFilter || $bulan || $tahun != now()->year)) ? ' • ' : '') . 'Pencarian: ' . $search : '' }}
                    </div>
                @endif

                {{-- Ringkasan Jumlah --}}
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="alert alert-success mb-0 py-3">
                            <strong class="fs-5">✅ Sudah Bayar RBAS: {{ $sudahBayarList->count() }} murid</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-danger mb-0 py-3">
                            <strong class="fs-5">❌ Belum Bayar RBAS: {{ $belumBayarRbas->count() }} murid</strong>
                        </div>
                    </div>
                </div>

                {{-- Tabel Sudah Bayar --}}
                <h5 class="mt-5 text-success card-body">✅ Sudah Bayar RBAS ({{ $sudahBayarList->count() }})</h5>
                <div class="table-responsive mb-5">
                    <table class="table table-bordered table-sm text-center align-middle card-body">
                        <thead class="table-light">
                            <tr>
                                <th>Kwitansi</th>
                                <th width="120">NIM</th>
                                <th>Nama Murid</th>
                                <th width="100">Kelas</th>
                                <th width="150">Unit</th>
                                <th width="150">Guru</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($sudahBayarList->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-muted py-4">
                                        Tidak ada murid yang sudah bayar RBAS dengan filter ini.
                                    </td>
                                </tr>
                            @else
                                @foreach($sudahBayarList as $m)
                                    <tr>
                                        <td>
    <strong>{{ $m->penerimaan_rbas->kwitansi ?? '-' }}</strong>
</td>
                                        <td><strong>{{ $m->nim }}</strong></td>
                                        <td class="text-start">{{ $m->nama_murid }}</td>
                                        <td>{{ $m->kelas ?? '-' }}</td>
                                        <td>{{ $m->bimba_unit ?? '-' }}</td>
                                        <td>{{ $m->guru ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Tabel Belum Bayar --}}
                <h5 class="mt-5 text-danger card-body">❌ Belum Bayar RBAS ({{ $belumBayarRbas->count() }})</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm text-center align-middle card-body">
                        <thead class="table-light card-body">
                            <tr>
                                <th>Kwitansi</th>
                                <th width="120">NIM</th>
                                <th>Nama Murid</th>
                                <th width="100">Kelas</th>
                                <th width="150">Unit</th>
                                <th width="150">Guru</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($belumBayarRbas->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-success fw-bold py-5 fs-4">
                                        🎉 Selamat! Semua murid sudah bayar RBAS!
                                    </td>
                                </tr>
                            @else
                                @foreach($belumBayarRbas as $m)
                                    <tr class="table-light">
                                        <td>{{ $m->kwitansi  }}</td>
                                        <td><strong>{{ $m->nim }}</strong></td>
                                        <td class="text-start">{{ $m->nama_murid }}</td>
                                        <td>{{ $m->kelas ?? '-' }}</td>
                                        <td>{{ $m->bimba_unit ?? '-' }}</td>
                                        <td>{{ $m->guru ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            // Inisialisasi Select2 untuk Unit
            $('#unit').select2({
                width: '100%',
                placeholder: '-- Semua Unit --',
                allowClear: true
            });

            // Inisialisasi Select2 untuk Murid: NIM | Nama
            $('#searchMurid').select2({
                width: '100%',
                placeholder: '-- Ketik NIM atau pilih murid --',
                allowClear: true,
                matcher: function (params, data) {
                    if ($.trim(params.term) === '') {
                        return data;
                    }

                    const term = params.term.toLowerCase();
                    const text = data.text.toLowerCase();           // "051410004 | ALVARO HAFIDZ"
                    const value = (data.element.value || '').toLowerCase(); // "051410004"

                    if (text.indexOf(term) > -1 || value.indexOf(term) > -1) {
                        return data;
                    }

                    return null;
                }
            });

            // Auto submit saat pilih Unit, Bulan, Tahun, atau Murid
            $('#unit, #bulan, #tahun, #searchMurid').on('change', function () {
                if ($(this).attr('id') === 'unit') {
                    $('#searchMurid').val(null).trigger('change');
                }
                $('#filterForm').submit();
            });
        });
    </script>
@endpush