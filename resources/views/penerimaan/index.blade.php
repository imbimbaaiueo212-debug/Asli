@extends('layouts.app')

@section('title', 'Penerimaan SPP')

@section('content')
    <div class="container-fluid">
        <div class="card w-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Data Penerimaan</h4>
                    <div>
                        <a href="{{ route('penerimaan.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-2"></i>Tambah Data
                        </a>
                    </div>
                </div>

                {{-- Import & Export --}}
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <!-- Import Form -->
                    <form action="{{ route('penerimaan.import') }}" method="POST" enctype="multipart/form-data"
                          class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2">
                        @csrf
                        <div class="flex-grow-1">
                            <input type="file" name="file" class="form-control form-control-sm" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Import Excel</button>
                    </form>

                    <!-- Export Button -->
                    <a href="{{ route('penerimaan.export') . '?' . http_build_query(request()->query()) }}"
                       class="btn btn-success btn-sm">
                        <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
                    </a>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

            <form method="GET" action="{{ route('penerimaan.index') }}" id="filter-form" class="card shadow-sm border mb-4">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <!-- Per Page -->
                        <div class="col-auto">
                            <label class="form-label small fw-medium text-muted mb-1">Per Page</label>
                            <input type="number" name="per_page" value="{{ request('per_page', 10) }}" min="1" class="form-control form-control-sm" style="width: 90px;">
                        </div>

                        @if (auth()->check() && (auth()->user()->is_admin ?? false))
                            <div class="col-auto">
                                <label class="form-label small fw-medium text-muted mb-1">Bimba Unit</label>
                                <select name="bimba_unit" class="form-select form-select-sm" style="min-width:220px;">
                                    <option value="">-- Semua Unit --</option>
                                    @foreach($unitList as $value => $label)
                                        <option value="{{ $value }}" {{ request('bimba_unit') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Nama Murid dengan Autocomplete -->
                        <div class="col-md-4">
                            <label class="form-label small fw-medium text-muted mb-1">Nama Murid</label>
                            <select name="search" id="searchSelect" class="form-select form-select-sm">
                                <option value="">-- Semua Murid --</option>

                                @foreach($muridList as $m)
                                    <option value="{{ $m['nama_murid'] }}"
                                        {{ request('search') == $m['nama_murid'] ? 'selected' : '' }}>
                                        
                                        {{ $m['nim'] ?? '-' }} — {{ $m['nama_murid'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Bulan -->
                        <div class="col-auto">
                            <label class="form-label small fw-medium text-muted mb-1">Bulan</label>
                            <select name="bulan" class="form-select form-select-sm" style="width:140px;">
                                <option value="">Semua</option>
                                @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $b)
                                    <option value="{{ $b }}" {{ request('bulan') == $b ? 'selected' : '' }}>{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tahun -->
                        <div class="col-auto">
                            <label class="form-label small fw-medium text-muted mb-1">Tahun</label>
                            <select name="tahun" class="form-select form-select-sm" style="width:120px;">
                                <option value="">Semua</option>
                                @for($y = date('Y') + 1; $y >= date('Y') - 5; $y--)
                                    <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        

                        <div class="col-auto d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-filter me-1"></i>Filter
                            </button>
                            <a href="{{ route('penerimaan.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                    </div>
                </div>
            </form>

                {{-- TABEL RINGKASAN TOTAL --}}
                <div class="card shadow-sm border mb-4">
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm text-center mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Voucher</th>
                                        <th>SPP</th>
                                        <th>Kaos Pendek</th>
                                        <th>Kaos Panjang</th>
                                        <th>KPK</th>
                                        <th>TAS</th>
                                        <th>RBAS</th>
                                        <th>BCABS01</th>
                                        <th>BCABS02</th>
                                        <th>SERTIFIKAT</th>
                                        <th>STPB</th>
                                        <th>EVENT</th>
                                        <th>LAIN-LAIN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="fw-bold">
                                        <td>{{ number_format($totalVoucher, 0, ',', '.') }}</td>
                                        <td>{{ number_format($totalSpp, 0, ',', '.') }}</td>
                                        <td>{{ number_format($totalKaosPendek, 0, ',', '.') }}</td>
                                        <td>{{ number_format($totalKaosPanjang, 0, ',', '.') }}</td>
                                        <td>{{ number_format($totalKpk, 0, ',', '.') }}</td>
                                        <td>{{ number_format($totalTas, 0, ',', '.') }}</td>
                                        <td>{{ number_format($totalRbas, 0, ',', '.') }}</td>
                                        <td>{{ number_format($totalBcabs01, 0, ',', '.') }}</td>
                                        <td>{{ number_format($totalBcabs02, 0, ',', '.') }}</td>
                                        <td>{{ number_format($totalSertifikat, 0, ',', '.') }}</td>
                                        <td>{{ number_format($totalStpb, 0, ',', '.') }}</td>
                                        <td>{{ number_format($totalEvent, 0, ',', '.') }}</td>
                                        <td>{{ number_format($totalLainLain, 0, ',', '.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- TABEL UTAMA DATA PENERIMAAN --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm text-center align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>KWITANSI</th>
                                <th>VIA</th>
                                <th>Bulan</th>
                                <th>Tahun</th>
                                <th>TANGGAL</th>
                                <th>NIM</th>
                                <th>NAMA MURID</th>
                                <th>KELAS</th>
                                <th>GOL</th>
                                <th>KD</th>
                                <th>STATUS</th>
                                <th>GURU</th>

                                {{-- KOLOM BIMBA UNIT & NO CABANG – HANYA UNTUK ADMIN --}}
                                @if (auth()->check() && (auth()->user()->is_admin ?? false))
                                    <th>biMBA UNIT</th>
                                    <th>No Cabang</th>
                                @endif

                                <th>DAFTAR</th>
                                <th>VOUCHER</th>
                                <th>SPP (Rp)</th>
                                <th>Kaos Pendek</th>
                                <th>Kaos Panjang</th>
                                <th>KPK</th>
                                <th>TAS</th>
                                <th>RBAS</th>
                                <th>BCABS01</th>
                                <th>BCABS02</th>
                                <th>SERTIFIKAT</th>
                                <th>STPB</th>
                                <th>EVENT</th>
                                <th>LAIN-LAIN</th>
                                <th>TOTAL</th>
                                <th>BUKTI</th>
                                <th>AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($penerimaan as $item)
                                @php
                                    $rawBukti = $item->bukti_transfer_path ?? null;
                                    $cleanBukti = $rawBukti ? Str::startsWith($rawBukti, 'public/') ? Str::after($rawBukti, 'public/') : $rawBukti : null;
                                    $buktiUrl = $cleanBukti ? asset('public/storage/' . $cleanBukti) : null;
                                    $buktiExt = $cleanBukti ? strtolower(pathinfo($cleanBukti, PATHINFO_EXTENSION)) : null;
                                @endphp
                                <tr>
                                    <td class="text-start">{{ $item->kwitansi ?? '-' }}</td>
                                    <td class="text-start">{{ ucfirst($item->via ?? '-') }}</td>
                                    <td class="text-start">{{ $item->bulan ?? '-' }}</td>
                                    <td class="text-start">{{ $item->tahun ?? '-' }}</td>
                                    <td class="text-start">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') : '-' }}</td>
                                    <td class="text-start">{{ $item->nim }}</td>
                                    <td class="text-start">{{ $item->nama_murid }}</td>
                                    <td class="text-start">{{ $item->kelas ?? '-' }}</td>
                                    <td class="text-start">{{ $item->gol ?? '-' }}</td>
                                    <td class="text-start">{{ $item->kd ?? '-' }}</td>
                                    <td class="text-start">{{ ucfirst($item->status ?? '-') }}</td>
                                    <td class="text-start">{{ $item->guru ?? '-' }}</td>

                                    {{-- KOLOM BIMBA UNIT & NO CABANG – HANYA ADMIN --}}
                                    @if (auth()->check() && (auth()->user()->is_admin ?? false))
                                        <td class="text-start">{{ $item->bimba_unit ?? '-' }}</td>
                                        <td class="text-start">{{ $item->no_cabang ?? '-' }}</td>
                                    @endif

                                    <td>{{ number_format($item->daftar ?? 0, 0, ',', '.') }}</td>
                                    <td>
                                        @if($item->voucher)
                                            {{ number_format(50000, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ number_format($item->spp ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ number_format($item->kaos ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ number_format($item->kaos_lengan_panjang ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ number_format($item->kpk ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ number_format($item->tas ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ number_format($item->RBAS ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ number_format($item->BCABS01 ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ number_format($item->BCABS02 ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ number_format($item->sertifikat ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ number_format($item->stpb ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ number_format($item->event ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ number_format($item->lain_lain ?? 0, 0, ',', '.') }}</td>
                                    <td><strong>{{ number_format($item->total ?? 0, 0, ',', '.') }}</strong></td>

                                    <td>
                                        @if($buktiUrl)
                                            @if(in_array($buktiExt, ['jpg','jpeg','png','gif']))
                                                <a href="{{ $buktiUrl }}" target="_blank">
                                                    <img src="{{ $buktiUrl }}" alt="Bukti" style="max-height: 40px; border-radius: 4px;">
                                                </a>
                                            @else
                                                <a href="{{ $buktiUrl }}" target="_blank" class="btn btn-sm btn-outline-info">Lihat Bukti</a>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        <a href="{{ route('penerimaan.edit', $item->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                        @if(auth()->check() && (auth()->user()->is_admin ?? false))
                                            <form action="{{ route('penerimaan.destroy', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus data ini?')">Hapus</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->check() && (auth()->user()->is_admin ?? false) ? '30' : '28' }}" class="text-center text-muted py-4">
                                        Belum ada data penerimaan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $penerimaan->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {

    const searchInput = $('#search');
    const dropdown    = $('#murid-dropdown');
    const muridList   = @json($muridList ?? []);
    const form        = $('#filter-form');

    // ===============================
    // AUTO FILTER BIMBA UNIT 🔥
    // ===============================
    $('select[name="bimba_unit"]').on('change', function () {
        form.submit();
    });

    // ===============================
    // DROPDOWN MANUAL (SEARCH)
    // ===============================
    function renderDropdown(items) {
        dropdown.empty();

        if (!items.length) {
            dropdown.append('<div class="dropdown-item text-muted py-2">Tidak ditemukan</div>');
            dropdown.show();
            return;
        }

        items.forEach(item => {
            const display = item.nim 
                ? `${item.nim} — ${item.nama_murid}` 
                : item.nama_murid;

            const el = $(`
                <div class="dropdown-item py-2 border-bottom cursor-pointer">
                    ${display}
                </div>
            `);

            el.on('click', function () {
                searchInput.val(item.nama_murid);
                dropdown.hide();
                form.submit();
            });

            dropdown.append(el);
        });

        dropdown.show();
    }

    // ===============================
    // INPUT SEARCH
    // ===============================
    if (searchInput.length) {
        searchInput.on('input', function () {
            const keyword = $(this).val().toLowerCase().trim();

            if (keyword.length < 1) {
                dropdown.hide();
                return;
            }

            const filtered = muridList.filter(item =>
                (item.nama_murid && item.nama_murid.toLowerCase().includes(keyword)) ||
                (item.nim && item.nim.toLowerCase().includes(keyword))
            );

            renderDropdown(filtered);
        });

        searchInput.on('keypress', function (e) {
            if (e.which === 13) form.submit();
        });
    }

    // ===============================
    // CLOSE DROPDOWN OUTSIDE CLICK
    // ===============================
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#search, #murid-dropdown').length) {
            dropdown.hide();
        }
    });

    // ===============================
    // SELECT2 (SEARCH SELECT)
    // ===============================
    if ($('#searchSelect').length) {
        $('#searchSelect').select2({
            placeholder: "-- Pilih atau ketik nama murid --",
            allowClear: true,
            width: '100%'
        });

        $('#searchSelect').on('change', function () {
            form.submit();
        });
    }

});
</script>
@endpush