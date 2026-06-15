@extends('layouts.app')

@section('title', 'Pindah Golongan')

@section('content')
<main>
    <div class="container-fluid px-4">
        <h2 class="mt-4">Data Murid Pindah Golongan</h2>

        <div class="card mb-4">
            <div class="card-body">

                {{-- Tombol Update --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 fw-semibold">Data Pindah Golongan</h5>
                    <a href="{{ route('pindah-golongan.index', ['sync' => 1]) }}"
                       class="btn btn-outline-primary"
                       onclick="return confirm('Jalankan sinkronisasi dari Google Sheet sekarang?')">
                        Update Golongan
                    </a>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- FILTER --}}
                <form method="GET" action="{{ route('pindah-golongan.index') }}" id="filterForm" class="mb-4">
                    <div class="row g-3 align-items-end">

                        @if (auth()->check() && (auth()->user()->is_admin ?? false))
                            <div class="col-md-3 col-lg-2">
                                <label class="form-label fw-bold small">Unit</label>
                                <select name="unit" id="unitFilter" class="form-select form-select-sm">
                                    <option value="">-- Semua Unit --</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u }}" {{ request('unit') == $u ? 'selected' : '' }}>
                                            {{ $u }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-5 col-lg-4">
                            <label class="form-label fw-bold small">NIM | Nama Murid</label>
                            <select name="nim" id="nimFilter" class="form-select form-select-sm">
                                <option value="">— Cari NIM atau Nama —</option>
                                @foreach($muridOptions as $m)
                                    @php $nimPad = str_pad($m->nim, 3, '0', STR_PAD_LEFT); @endphp
                                    <option value="{{ $m->nim }}" {{ request('nim') == $m->nim ? 'selected' : '' }}>
                                        {{ $nimPad }} | {{ $m->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold small">Tanggal Dari</label>
                            <input type="date" name="tanggal_dari" id="tanggal_dari"
                                   class="form-control form-control-sm"
                                   value="{{ request('tanggal_dari') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold small">Tanggal Sampai</label>
                            <input type="date" name="tanggal_sampai" id="tanggal_sampai"
                                   class="form-control form-control-sm"
                                   value="{{ request('tanggal_sampai') }}">
                        </div>

                        <div class="col-md-2 d-flex gap-2">
                            <button class="btn btn-primary btn-sm flex-grow-1">Filter</button>
                            <a href="{{ route('pindah-golongan.index') }}"
                               class="btn btn-outline-secondary btn-sm flex-grow-1">Reset</a>
                        </div>

                    </div>
                </form>

                {{-- TABLE --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead class="table-light">
                        <tr>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Guru</th>
                            <th>Gol Lama</th>
                            <th>Kode Lama</th>
                            <th>SPP Lama</th>
                            <th>Unit</th>
                            <th>Cabang</th>
                            <th>Gol Baru</th>
                            <th>Kode Baru</th>
                            <th>SPP Baru</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Alasan</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($data as $item)
                            @php
                                $tgl = $item->tanggal_pindah_golongan
                                    ? \Carbon\Carbon::parse($item->tanggal_pindah_golongan)->format('d-m-Y')
                                    : '-';
                            @endphp

                            <tr>
                                <td>{{ $item->nim }}</td>
                                <td>{{ $item->nama }}</td>
                                <td>{{ $item->guru ?? '-' }}</td>
                                <td>{{ $item->gol ?? '-' }}</td>
                                <td>{{ $item->kd ?? '-' }}</td>
                                <td class="text-end">{{ $item->spp ?? 0 }}</td>
                                <td>{{ $item->bimba_unit ?? '-' }}</td>
                                <td>{{ $item->no_cabang ?? '-' }}</td>
                                <td>{{ $item->gol_baru ?? '-' }}</td>
                                <td>{{ $item->kd_baru ?? '-' }}</td>
                                <td class="text-end">{{ $item->spp_baru ?? 0 }}</td>
                                <td>{{ $tgl }}</td>
                                <td>{{ $item->keterangan ?? '-' }}</td>
                                <td>{{ $item->alasan_pindah ?? '-' }}</td>

                                <td>
                                    <a href="{{ route('pindah-golongan.edit', $item->id) }}"
                                       class="btn btn-warning btn-sm">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="text-center text-muted py-3">
                                    Tidak ada data
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PETUNJUK --}}
                <div class="mt-4 border-top pt-3">
                    <h6>Cara Penggunaan</h6>
                    <ul class="small text-muted">
                        <li>Kirim link form ke orang tua</li>
                        <li>
                            <a href="#" onclick="copyLink(this); return false;"
                               data-url="https://docs.google.com/forms/d/e/1FAIpQLSd2TtFBNPaUMJ7vq93Y2ZAevDQYVT_QW_iEcCkNwTwN08TJnQ/viewform?usp=dialog">
                                📋 Salin Link Form
                            </a>
                        </li>
                        <li>Setelah diisi → klik Update Golongan</li>
                    </ul>
                </div>

            </div>
        </div>
    </div>
</main>

{{-- COPY LINK FIX (FULL COMPATIBLE) --}}
<script>
function copyLink(el) {
    const url = el.getAttribute('data-url');

    // fallback method (WORKS 100% browser)
    const tempInput = document.createElement("input");
    document.body.appendChild(tempInput);
    tempInput.value = url;
    tempInput.select();
    document.execCommand("copy");
    document.body.removeChild(tempInput);

    const old = el.innerText;
    el.innerText = "✔ Tersalin!";
    setTimeout(() => el.innerText = old, 1500);
}
</script>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function () {

    $('#nimFilter').select2({
        width: '100%',
        placeholder: "Cari NIM atau Nama",
        allowClear: true,
        minimumInputLength: 2
    });

    $('#unitFilter').select2({
        width: '100%',
        placeholder: "Semua Unit",
        allowClear: true
    });

    $('#unitFilter, #tanggal_dari, #tanggal_sampai').on('change', function () {
        $('#filterForm').submit();
    });

    $('#nimFilter').on('select2:select', function () {
        $('#filterForm').submit();
    });

});
</script>
@endpush

@endsection