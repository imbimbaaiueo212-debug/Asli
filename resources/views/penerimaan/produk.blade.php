@extends('layouts.app')

@section('title', 'Rekap Produk / Atribut')

@section('content')
<div class="card card-body">
    <h1 class="mb-4">Rekap Produk / Atribut</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" class="row g-3 align-items-end mb-4 bg-light p-3 rounded shadow-sm card-body" id="filterForm">

        @if (auth()->check() && (auth()->user()->is_admin ?? false))
        <div class="col-md-3 col-lg-3">
            <label for="unit" class="form-label small text-muted">Bimba Unit</label>
            <select name="unit" id="unit" class="form-control">
                <option value="">-- Semua Unit --</option>
                @foreach($unitList as $unit)
                    <option value="{{ $unit->kode_unit ?? $unit->id }}" {{ request('unit') == ($unit->kode_unit ?? $unit->id) ? 'selected' : '' }}>
                        {{ $unit->biMBA_unit }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="col-md-3 col-lg-3">
            <label for="searchProduk" class="form-label small text-muted">Murid (NIM)</label>
            <select id="searchProduk" name="search" class="form-select">
                <option value="">-- Semua Murid --</option>
                @foreach($namaList as $m)
                    <option value="{{ $m->nim }}" {{ request('search') == $m->nim ? 'selected' : '' }}>
                        {{ $m->nim }} | {{ $m->nama_murid }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2 col-lg-2">
            <label for="periode_dari" class="form-label small text-muted">Periode Dari</label>
            <input type="month" id="periode_dari" name="periode_dari" class="form-control"
                   value="{{ request('periode_dari') }}">
        </div>

        <div class="col-md-2 col-lg-2">
            <label for="periode_sampai" class="form-label small text-muted">Periode Sampai</label>
            <input type="month" id="periode_sampai" name="periode_sampai" class="form-control"
                   value="{{ request('periode_sampai') }}">
        </div>

        <div class="col-auto d-flex align-items-end">
            <a href="{{ route('penerimaan.produk') }}" class="btn btn-secondary ms-2">Reset</a>
        </div>
    </form>

    {{-- Tabel Detail Pembelian per Murid (dalam PCS) --}}
    <div class="table-responsive mb-5">
        <table class="table table-bordered table-striped table-hover align-middle text-center card-body">
            <thead class="table-light card-body">
                <tr>
                    <th class="align-middle">Kwitansi</th>
                    <th class="align-middle">NIM</th>
                    <th class="align-middle">Nama Murid</th>
                    <th class="align-middle">Tanggal Bayar</th>
                    <th>Kaos Pendek</th>
                    <th>Kaos Panjang</th>
                    <th>KPK</th>
                    <th>Tas</th>
                    <th>RBAS</th>
                    <th>BCABS01</th>
                    <th>BCABS02</th>
                    <th>Sertifikat</th>
                    <th>STPB</th>
                    <th>Event</th>
                    <th>Lain-lain</th>
                    <th class="align-middle">Total (PCS)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($penerimaan as $row)
                <tr>
                    <td>{{ $row->kwitansi }}</td>
                    <td>{{ $row->nim }}</td>
                    <td class="text-start">{{ $row->nama_murid }}</td>
                    <td>
                        {{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') : '-' }}
                    </td>

                    {{-- Kaos Pendek --}}
                    <td class="text-center">
                        <div class="fw-bold">{{ $row->kaos_pendek_pcs ?? 0 }} pcs</div>
                        @if($row->kaos_pendek_pcs > 0)
                            <div class="mt-2 serah-container" data-id="{{ $row->id }}" data-field="kaos_pendek" data-current="{{ $row->tgl_kaos_pendek_fmt ? \Carbon\Carbon::createFromFormat('d-m-Y', $row->tgl_kaos_pendek_fmt)->format('Y-m-d') : '' }}">
                                <small class="d-block {{ $row->tgl_kaos_pendek_fmt ? 'text-success' : 'text-danger' }} serah-text">
                                    {{ $row->tgl_kaos_pendek_fmt ? 'Diserahkan: ' . $row->tgl_kaos_pendek_fmt : 'Belum diserahkan' }}
                                </small>
                                <button type="button" class="btn btn-sm {{ $row->tanggal_penyerahan_kaos_pendek ? 'btn-secondary' : 'btn-outline-primary' }} mt-1 edit-serah-btn"
                                        {{ $row->tanggal_penyerahan_kaos_pendek ? 'disabled' : '' }}
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ $row->tanggal_penyerahan_kaos_pendek ? 'Sudah diserahkan, tidak bisa diubah lagi' : 'Ubah tanggal penyerahan' }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        @endif
                    </td>

                    {{-- Kaos Panjang --}}
                    <td class="text-center">
                        <div class="fw-bold">{{ $row->kaos_panjang_pcs ?? 0 }} pcs</div>
                        @if($row->kaos_panjang_pcs > 0)
                            <div class="mt-2 serah-container" data-id="{{ $row->id }}" data-field="kaos_panjang" data-current="{{ $row->tgl_kaos_panjang_fmt ? \Carbon\Carbon::createFromFormat('d-m-Y', $row->tgl_kaos_panjang_fmt)->format('Y-m-d') : '' }}">
                                <small class="d-block {{ $row->tgl_kaos_panjang_fmt ? 'text-success' : 'text-danger' }} serah-text">
                                    {{ $row->tgl_kaos_panjang_fmt ? 'Diserahkan: ' . $row->tgl_kaos_panjang_fmt : 'Belum diserahkan' }}
                                </small>
                                <button type="button" class="btn btn-sm {{ $row->tanggal_penyerahan_kaos_panjang ? 'btn-secondary' : 'btn-outline-primary' }} mt-1 edit-serah-btn"
                                        {{ $row->tanggal_penyerahan_kaos_panjang ? 'disabled' : '' }}
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ $row->tanggal_penyerahan_kaos_panjang ? 'Sudah diserahkan, tidak bisa diubah lagi' : 'Ubah tanggal penyerahan' }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        @endif
                    </td>

                    {{-- KPK --}}
                    <td class="text-center">
                        <div class="fw-bold">{{ $row->kpk_pcs ?? 0 }} pcs</div>
                        @if($row->kpk_pcs > 0)
                            <div class="mt-1 serah-container" data-id="{{ $row->id }}" data-field="kpk" data-current="{{ $row->tgl_kpk_fmt ? \Carbon\Carbon::createFromFormat('d-m-Y', $row->tgl_kpk_fmt)->format('Y-m-d') : '' }}">
                                <small class="d-block {{ $row->tgl_kpk_fmt ? 'text-success' : 'text-danger' }} serah-text">
                                    {{ $row->tgl_kpk_fmt ? 'Diserahkan: ' . $row->tgl_kpk_fmt : 'Belum diserahkan' }}
                                </small>
                                <button type="button" class="btn btn-sm {{ $row->tanggal_penyerahan_kpk ? 'btn-secondary' : 'btn-outline-primary' }} mt-1 edit-serah-btn"
                                        {{ $row->tanggal_penyerahan_kpk ? 'disabled' : '' }}
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ $row->tanggal_penyerahan_kpk ? 'Sudah diserahkan, tidak bisa diubah lagi' : 'Ubah tanggal penyerahan' }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        @endif
                    </td>

                    {{-- Tas --}}
                    <td class="text-center">
                        <div class="fw-bold">{{ $row->tas_pcs ?? 0 }} pcs</div>
                        @if($row->tas_pcs > 0)
                            <div class="mt-1 serah-container" data-id="{{ $row->id }}" data-field="tas" data-current="{{ $row->tgl_tas_fmt ? \Carbon\Carbon::createFromFormat('d-m-Y', $row->tgl_tas_fmt)->format('Y-m-d') : '' }}">
                                <small class="d-block {{ $row->tgl_tas_fmt ? 'text-success' : 'text-danger' }} serah-text">
                                    {{ $row->tgl_tas_fmt ? 'Diserahkan: ' . $row->tgl_tas_fmt : 'Belum diserahkan' }}
                                </small>
                                <button type="button" class="btn btn-sm {{ $row->tanggal_penyerahan_tas ? 'btn-secondary' : 'btn-outline-primary' }} mt-1 edit-serah-btn"
                                        {{ $row->tanggal_penyerahan_tas ? 'disabled' : '' }}
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ $row->tanggal_penyerahan_tas ? 'Sudah diserahkan, tidak bisa diubah lagi' : 'Ubah tanggal penyerahan' }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        @endif
                    </td>

                    {{-- RBAS --}}
                    <td class="text-center">
                        <div class="fw-bold">{{ $row->rbas_pcs ?? 0 }} pcs</div>
                        @if($row->rbas_pcs > 0)
                            <div class="mt-1 serah-container" data-id="{{ $row->id }}" data-field="rbas" data-current="{{ $row->tgl_rbas_fmt ? \Carbon\Carbon::createFromFormat('d-m-Y', $row->tgl_rbas_fmt)->format('Y-m-d') : '' }}">
                                <small class="d-block {{ $row->tgl_rbas_fmt ? 'text-success' : 'text-danger' }} serah-text">
                                    {{ $row->tgl_rbas_fmt ? 'Diserahkan: ' . $row->tgl_rbas_fmt : 'Belum diserahkan' }}
                                </small>
                                <button type="button" class="btn btn-sm {{ $row->tanggal_penyerahan_rbas ? 'btn-secondary' : 'btn-outline-primary' }} mt-1 edit-serah-btn"
                                        {{ $row->tanggal_penyerahan_rbas ? 'disabled' : '' }}
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ $row->tanggal_penyerahan_rbas ? 'Sudah diserahkan, tidak bisa diubah lagi' : 'Ubah tanggal penyerahan' }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        @endif
                    </td>

                    {{-- BCABS01 --}}
                    <td class="text-center">
                        <div class="fw-bold">{{ $row->bcabs01_pcs ?? 0 }} pcs</div>
                        @if($row->bcabs01_pcs > 0)
                            <div class="mt-1 serah-container" data-id="{{ $row->id }}" data-field="bcabs01" data-current="{{ $row->tgl_bcabs01_fmt ? \Carbon\Carbon::createFromFormat('d-m-Y', $row->tgl_bcabs01_fmt)->format('Y-m-d') : '' }}">
                                <small class="d-block {{ $row->tgl_bcabs01_fmt ? 'text-success' : 'text-danger' }} serah-text">
                                    {{ $row->tgl_bcabs01_fmt ? 'Diserahkan: ' . $row->tgl_bcabs01_fmt : 'Belum diserahkan' }}
                                </small>
                                <button type="button" class="btn btn-sm {{ $row->tanggal_penyerahan_bcabs01 ? 'btn-secondary' : 'btn-outline-primary' }} mt-1 edit-serah-btn"
                                        {{ $row->tanggal_penyerahan_bcabs01 ? 'disabled' : '' }}
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ $row->tanggal_penyerahan_bcabs01 ? 'Sudah diserahkan, tidak bisa diubah lagi' : 'Ubah tanggal penyerahan' }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        @endif
                    </td>

                    {{-- BCABS02 --}}
                    <td class="text-center">
                        <div class="fw-bold">{{ $row->bcabs02_pcs ?? 0 }} pcs</div>
                        @if($row->bcabs02_pcs > 0)
                            <div class="mt-1 serah-container" data-id="{{ $row->id }}" data-field="bcabs02" data-current="{{ $row->tgl_bcabs02_fmt ? \Carbon\Carbon::createFromFormat('d-m-Y', $row->tgl_bcabs02_fmt)->format('Y-m-d') : '' }}">
                                <small class="d-block {{ $row->tgl_bcabs02_fmt ? 'text-success' : 'text-danger' }} serah-text">
                                    {{ $row->tgl_bcabs02_fmt ? 'Diserahkan: ' . $row->tgl_bcabs02_fmt : 'Belum diserahkan' }}
                                </small>
                                <button type="button" class="btn btn-sm {{ $row->tanggal_penyerahan_bcabs02 ? 'btn-secondary' : 'btn-outline-primary' }} mt-1 edit-serah-btn"
                                        {{ $row->tanggal_penyerahan_bcabs02 ? 'disabled' : '' }}
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ $row->tanggal_penyerahan_bcabs02 ? 'Sudah diserahkan, tidak bisa diubah lagi' : 'Ubah tanggal penyerahan' }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        @endif
                    </td>

                    {{-- Sertifikat --}}
                    <td class="text-center">
                        <div class="fw-bold">{{ $row->sertifikat_pcs ?? 0 }} pcs</div>
                        @if($row->sertifikat_pcs > 0)
                            <div class="mt-1 serah-container" data-id="{{ $row->id }}" data-field="sertifikat" data-current="{{ $row->tgl_sertifikat_fmt ? \Carbon\Carbon::createFromFormat('d-m-Y', $row->tgl_sertifikat_fmt)->format('Y-m-d') : '' }}">
                                <small class="d-block {{ $row->tgl_sertifikat_fmt ? 'text-success' : 'text-danger' }} serah-text">
                                    {{ $row->tgl_sertifikat_fmt ? 'Diserahkan: ' . $row->tgl_sertifikat_fmt : 'Belum diserahkan' }}
                                </small>
                                <button type="button" class="btn btn-sm {{ $row->tanggal_penyerahan_sertifikat ? 'btn-secondary' : 'btn-outline-primary' }} mt-1 edit-serah-btn"
                                        {{ $row->tanggal_penyerahan_sertifikat ? 'disabled' : '' }}
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ $row->tanggal_penyerahan_sertifikat ? 'Sudah diserahkan, tidak bisa diubah lagi' : 'Ubah tanggal penyerahan' }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        @endif
                    </td>

                    {{-- STPB --}}
                    <td class="text-center">
                        <div class="fw-bold">{{ $row->stpb_pcs ?? 0 }} pcs</div>
                        @if($row->stpb_pcs > 0)
                            <div class="mt-1 serah-container" data-id="{{ $row->id }}" data-field="stpb" data-current="{{ $row->tgl_stpb_fmt ? \Carbon\Carbon::createFromFormat('d-m-Y', $row->tgl_stpb_fmt)->format('Y-m-d') : '' }}">
                                <small class="d-block {{ $row->tgl_stpb_fmt ? 'text-success' : 'text-danger' }} serah-text">
                                    {{ $row->tgl_stpb_fmt ? 'Diserahkan: ' . $row->tgl_stpb_fmt : 'Belum diserahkan' }}
                                </small>
                                <button type="button" class="btn btn-sm {{ $row->tanggal_penyerahan_stpb ? 'btn-secondary' : 'btn-outline-primary' }} mt-1 edit-serah-btn"
                                        {{ $row->tanggal_penyerahan_stpb ? 'disabled' : '' }}
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ $row->tanggal_penyerahan_stpb ? 'Sudah diserahkan, tidak bisa diubah lagi' : 'Ubah tanggal penyerahan' }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        @endif
                    </td>

                    {{-- Event --}}
                    <td class="text-center">
                        <div class="fw-bold">{{ $row->event_pcs ?? 0 }} pcs</div>
                        @if($row->event_pcs > 0)
                            <div class="mt-1 serah-container" data-id="{{ $row->id }}" data-field="event" data-current="{{ $row->tgl_event_fmt ? \Carbon\Carbon::createFromFormat('d-m-Y', $row->tgl_event_fmt)->format('Y-m-d') : '' }}">
                                <small class="d-block {{ $row->tgl_event_fmt ? 'text-success' : 'text-danger' }} serah-text">
                                    {{ $row->tgl_event_fmt ? 'Diserahkan: ' . $row->tgl_event_fmt : 'Belum diserahkan' }}
                                </small>
                                <button type="button" class="btn btn-sm {{ $row->tanggal_penyerahan_event ? 'btn-secondary' : 'btn-outline-primary' }} mt-1 edit-serah-btn"
                                        {{ $row->tanggal_penyerahan_event ? 'disabled' : '' }}
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ $row->tanggal_penyerahan_event ? 'Sudah diserahkan, tidak bisa diubah lagi' : 'Ubah tanggal penyerahan' }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        @endif
                    </td>

                    {{-- Lain-lain --}}
                    <td class="text-center">
                        <div class="fw-bold">{{ $row->lainlain_pcs ?? 0 }} pcs</div>
                        @if($row->lainlain_pcs > 0)
                            <div class="mt-1 serah-container" data-id="{{ $row->id }}" data-field="lainlain" data-current="{{ $row->tgl_lainlain_fmt ? \Carbon\Carbon::createFromFormat('d-m-Y', $row->tgl_lainlain_fmt)->format('Y-m-d') : '' }}">
                                <small class="d-block {{ $row->tgl_lainlain_fmt ? 'text-success' : 'text-danger' }} serah-text">
                                    {{ $row->tgl_lainlain_fmt ? 'Diserahkan: ' . $row->tgl_lainlain_fmt : 'Belum diserahkan' }}
                                </small>
                                <button type="button" class="btn btn-sm {{ $row->tanggal_penyerahan_lainlain ? 'btn-secondary' : 'btn-outline-primary' }} mt-1 edit-serah-btn"
                                        {{ $row->tanggal_penyerahan_lainlain ? 'disabled' : '' }}
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ $row->tanggal_penyerahan_lainlain ? 'Sudah diserahkan, tidak bisa diubah lagi' : 'Ubah tanggal penyerahan' }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        @endif
                    </td>

                    {{-- Total PCS per transaksi --}}
                    <td class="fw-bold">
                        {{ 
                            ($row->kaos_pendek_pcs ?? 0) +
                            ($row->kaos_panjang_pcs ?? 0) +
                            ($row->kpk_pcs ?? 0) +
                            ($row->tas_pcs ?? 0) +
                            ($row->rbas_pcs ?? 0) +
                            ($row->bcabs01_pcs ?? 0) +
                            ($row->bcabs02_pcs ?? 0) +
                            ($row->sertifikat_pcs ?? 0) +
                            ($row->stpb_pcs ?? 0) +
                            ($row->event_pcs ?? 0) +
                            ($row->lainlain_pcs ?? 0)
                        }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="16" class="text-center py-4 text-muted">
                        Tidak ada data penerimaan produk yang ditemukan untuk filter saat ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mb-4">
        {{ $penerimaan->appends(request()->query())->links() }}
    </div>

    {{-- Ringkasan Status Penyerahan Produk + Ukuran Kaos Terintegrasi --}}
<div class="row mt-5">
    {{-- Belum Diserahkan --}}
    <div class="col-lg-6 mb-4">
        <div class="card border-danger shadow">
            <div class="card-header bg-danger text-white text-center">
                <h4 class="mb-0">
                    <i class="fas fa-exclamation-triangle"></i>
                    Belum Diserahkan: <span id="total-belum">{{ number_format($totalBelumDiserahkan, 0, ',', '.') }}</span> PCS
                </h4>
            </div>
            <div class="card-body p-3">
                <table class="table table-sm table-bordered mb-0">
                    <tbody>
                        <tr>
                            <td class="py-2"><strong>Kaos Pendek</strong></td>
                            <td class="text-end fw-bold text-danger py-2">{{ number_format($belumKaosPendek, 0, ',', '.') }}</td>
                        </tr>
                        @if($belumKaosPendek > 0)
                            <tr>
                                <td colspan="2" class="pt-2 pb-1">
                                    <div class="ps-5 small">
                                        <small class="text-muted d-block mb-1">Rincian Ukuran:</small>
                                        KAS     : <span class="fw-bold text-danger">{{ number_format($belumUkuranPendek['KAS'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAM     : <span class="fw-bold text-danger">{{ number_format($belumUkuranPendek['KAM'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAL     : <span class="fw-bold text-danger">{{ number_format($belumUkuranPendek['KAL'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAXL    : <span class="fw-bold text-danger">{{ number_format($belumUkuranPendek['KAXL'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAXXL   : <span class="fw-bold text-danger">{{ number_format($belumUkuranPendek['KAXXL'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAXXXL  : <span class="fw-bold text-danger">{{ number_format($belumUkuranPendek['KAXXXL'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAXXXLS : <span class="fw-bold text-danger">{{ number_format($belumUkuranPendek['KAXXXLS'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endif

                        <tr>
                            <td class="py-2"><strong>Kaos Panjang</strong></td>
                            <td class="text-end fw-bold text-danger py-2">{{ number_format($belumKaosPanjang, 0, ',', '.') }}</td>
                        </tr>
                        @if($belumKaosPanjang > 0)
                            <tr>
                                <td colspan="2" class="pt-2 pb-1">
                                    <div class="ps-5 small">
                                        <small class="text-muted d-block mb-1">Rincian Ukuran:</small>
                                        KAS01     : <span class="fw-bold text-danger">{{ number_format($belumUkuranPanjang['KAS01'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAM01     : <span class="fw-bold text-danger">{{ number_format($belumUkuranPanjang['KAM01'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAL01     : <span class="fw-bold text-danger">{{ number_format($belumUkuranPanjang['KAL01'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAXL01    : <span class="fw-bold text-danger">{{ number_format($belumUkuranPanjang['KAXL01'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAXXL01   : <span class="fw-bold text-danger">{{ number_format($belumUkuranPanjang['KAXXL01'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAXXXL01  : <span class="fw-bold text-danger">{{ number_format($belumUkuranPanjang['KAXXXL01'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAXXXLS01 : <span class="fw-bold text-danger">{{ number_format($belumUkuranPanjang['KAXXXLS01'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endif

                        <tr><td>KPK</td><td class="text-end fw-bold text-danger">{{ number_format($belumKpk, 0, ',', '.') }}</td></tr>
                        <tr><td>Tas</td><td class="text-end fw-bold text-danger">{{ number_format($belumTas, 0, ',', '.') }}</td></tr>
                        <tr><td>RBAS</td><td class="text-end fw-bold text-danger">{{ number_format($belumRbas, 0, ',', '.') }}</td></tr>
                        <tr><td>BCABS01</td><td class="text-end fw-bold text-danger">{{ number_format($belumBcabs01, 0, ',', '.') }}</td></tr>
                        <tr><td>BCABS02</td><td class="text-end fw-bold text-danger">{{ number_format($belumBcabs02, 0, ',', '.') }}</td></tr>
                        <tr><td>Sertifikat</td><td class="text-end fw-bold text-danger">{{ number_format($belumSertifikat, 0, ',', '.') }}</td></tr>
                        <tr><td>STPB</td><td class="text-end fw-bold text-danger">{{ number_format($belumStpb, 0, ',', '.') }}</td></tr>
                        <tr><td>Event</td><td class="text-end fw-bold text-danger">{{ number_format($belumEvent, 0, ',', '.') }}</td></tr>
                        <tr><td>Lain-lain</td><td class="text-end fw-bold text-danger">{{ number_format($belumLainlain, 0, ',', '.') }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Sudah Diserahkan --}}
    <div class="col-lg-6 mb-4">
        <div class="card border-success shadow">
            <div class="card-header bg-success text-white text-center">
                <h4 class="mb-0">
                    <i class="fas fa-check-circle"></i>
                    Sudah Diserahkan: <span id="total-sudah">{{ number_format($totalSudahDiserahkan, 0, ',', '.') }}</span> PCS
                </h4>
            </div>
            <div class="card-body p-3">
                <table class="table table-sm table-bordered mb-0">
                    <tbody>
                        <tr>
                            <td class="py-2"><strong>Kaos Pendek</strong></td>
                            <td class="text-end fw-bold text-success py-2">{{ number_format($sudahKaosPendek, 0, ',', '.') }}</td>
                        </tr>
                        @if($sudahKaosPendek > 0)
                            <tr>
                                <td colspan="2" class="pt-2 pb-1">
                                    <div class="ps-5 small">
                                        <small class="text-muted d-block mb-1">Rincian Ukuran:</small>
                                        KAS     : <span class="fw-bold text-success">{{ number_format($sudahUkuranPendek['KAS'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAM     : <span class="fw-bold text-success">{{ number_format($sudahUkuranPendek['KAM'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAL     : <span class="fw-bold text-success">{{ number_format($sudahUkuranPendek['KAL'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAXL    : <span class="fw-bold text-success">{{ number_format($sudahUkuranPendek['KAXL'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAXXL   : <span class="fw-bold text-success">{{ number_format($sudahUkuranPendek['KAXXL'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAXXXL  : <span class="fw-bold text-success">{{ number_format($sudahUkuranPendek['KAXXXL'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAXXXLS : <span class="fw-bold text-success">{{ number_format($sudahUkuranPendek['KAXXXLS'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endif

                        <tr>
                            <td class="py-2"><strong>Kaos Panjang</strong></td>
                            <td class="text-end fw-bold text-success py-2">{{ number_format($sudahKaosPanjang, 0, ',', '.') }}</td>
                        </tr>
                        @if($sudahKaosPanjang > 0)
                            <tr>
                                <td colspan="2" class="pt-2 pb-1">
                                    <div class="ps-5 small">
                                        <small class="text-muted d-block mb-1">Rincian Ukuran:</small>
                                        KAS01     : <span class="fw-bold text-success">{{ number_format($sudahUkuranPanjang['KAS01'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAM01     : <span class="fw-bold text-success">{{ number_format($sudahUkuranPanjang['KAM01'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAL01     : <span class="fw-bold text-success">{{ number_format($sudahUkuranPanjang['KAL01'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAXL01    : <span class="fw-bold text-success">{{ number_format($sudahUkuranPanjang['KAXL01'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAXXL01   : <span class="fw-bold text-success">{{ number_format($sudahUkuranPanjang['KAXXL01'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAXXXL01  : <span class="fw-bold text-success">{{ number_format($sudahUkuranPanjang['KAXXXL01'] ?? 0, 0, ',', '.') }}</span><br>
                                        KAXXXLS01 : <span class="fw-bold text-success">{{ number_format($sudahUkuranPanjang['KAXXXLS01'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endif

                        <tr><td>KPK</td><td class="text-end fw-bold text-success">{{ number_format($sudahKpk, 0, ',', '.') }}</td></tr>
                        <tr><td>Tas</td><td class="text-end fw-bold text-success">{{ number_format($sudahTas, 0, ',', '.') }}</td></tr>
                        <tr><td>RBAS</td><td class="text-end fw-bold text-success">{{ number_format($sudahRbas, 0, ',', '.') }}</td></tr>
                        <tr><td>BCABS01</td><td class="text-end fw-bold text-success">{{ number_format($sudahBcabs01, 0, ',', '.') }}</td></tr>
                        <tr><td>BCABS02</td><td class="text-end fw-bold text-success">{{ number_format($sudahBcabs02, 0, ',', '.') }}</td></tr>
                        <tr><td>Sertifikat</td><td class="text-end fw-bold text-success">{{ number_format($sudahSertifikat, 0, ',', '.') }}</td></tr>
                        <tr><td>STPB</td><td class="text-end fw-bold text-success">{{ number_format($sudahStpb, 0, ',', '.') }}</td></tr>
                        <tr><td>Event</td><td class="text-end fw-bold text-success">{{ number_format($sudahEvent, 0, ',', '.') }}</td></tr>
                        <tr><td>Lain-lain</td><td class="text-end fw-bold text-success">{{ number_format($sudahLainlain, 0, ',', '.') }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {
    // Inisialisasi Select2
    $('#searchProduk, #unit').select2({ 
        width: '100%', 
        allowClear: true 
    });

    // Auto submit filter saat ada perubahan
    $('#unit, #periode_dari, #periode_sampai').on('change', function () {
        if ($(this).attr('id') === 'unit') {
            $('#searchProduk').val('').trigger('change.select2'); // Clear murid
        }
        $('#filterForm').submit();
    });

    $('#searchProduk').on('change', function () {
        $('#filterForm').submit();
    });

    // ========================================
    // === EDIT UKURAN KAOS ===
    // ========================================
    $(document).on('click', '.edit-ukuran-btn', function () {
        const container = $(this).closest('.ukuran-container');
        const id        = container.data('id');
        const type      = container.data('type');
        const current   = container.data('current') ? container.data('current').split(',').map(s => s.trim()) : [];

        const pcsText   = container.closest('td').find('.fw-bold').text().trim();
        const maxUkuran = parseInt(pcsText) || 0;

        if (maxUkuran === 0) {
            alert('Jumlah pcs 0, tidak bisa edit ukuran.');
            return;
        }

        const ukuranOptions = ['KAS', 'KAM', 'KAL', 'KAXL', 'KAXXL', 'KAXXXL', 'KAXXXLS'];

        let selectHtml = '<select multiple class="form-select form-select-sm ukuran-select">';
        ukuranOptions.forEach(opt => {
            const selected = current.includes(opt) ? 'selected' : '';
            selectHtml += `<option value="${opt}" ${selected}>${opt}</option>`;
        });
        selectHtml += '</select>';

        const formHtml = `
            <div class="mt-2">
                <small class="d-block text-muted mb-2">
                    Harus pilih <strong>tepat ${maxUkuran} ukuran</strong> (sesuai jumlah pcs: ${pcsText})
                </small>
                ${selectHtml}
                <div class="mt-3 text-end">
                    <button class="btn btn-success btn-sm save-ukuran-btn" data-id="${id}" data-type="${type}" data-max="${maxUkuran}" disabled>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Simpan
                    </button>
                    <button class="btn btn-secondary btn-sm cancel-ukuran-btn ms-2">
                        Batal
                    </button>
                </div>
            </div>
        `;

        container.html(`
            <small class="d-block text-primary fw-bold mb-2">Edit Ukuran (wajib ${maxUkuran} ukuran):</small>
            ${formHtml}
        `);

        const $select = container.find('.ukuran-select');
        $select.select2({
            width: '100%',
            placeholder: `Pilih tepat ${maxUkuran} ukuran...`,
            maximumSelectionLength: maxUkuran,
            allowClear: true
        });

        $select.on('change', function () {
            const selectedCount = $(this).val() ? $(this).val().length : 0;
            container.find('.save-ukuran-btn').prop('disabled', selectedCount !== maxUkuran);
        });
    });

    // === SIMPAN UKURAN KAOS → AUTO REFRESH ===
    $(document).on('click', '.save-ukuran-btn', function () {
        const btn       = $(this);
        if (btn.prop('disabled')) return;

        const id        = btn.data('id');
        const type      = btn.data('type');
        const selected  = btn.closest('.ukuran-container').find('.ukuran-select').val() || [];
        const ukuranString = selected.join(',');

        btn.prop('disabled', true);
        btn.find('.spinner-border').removeClass('d-none');
        btn.contents().last().replaceWith(' Menyimpan...');

        $.ajax({
            url: '{{ route("penerimaan.update-ukuran-kaos") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id,
                type: type,
                ukuran: ukuranString
            },
            success: function(response) {
                if (response.success) {
                    alert('Ukuran kaos berhasil disimpan!');
                    location.reload(); // Refresh halaman otomatis
                } else {
                    alert(response.message || 'Gagal menyimpan ukuran.');
                    btn.prop('disabled', false);
                    btn.find('.spinner-border').addClass('d-none');
                    btn.contents().last().replaceWith(' Simpan');
                }
            },
            error: function() {
                alert('Error koneksi atau server.');
                btn.prop('disabled', false);
                btn.find('.spinner-border').addClass('d-none');
                btn.contents().last().replaceWith(' Simpan');
            }
        });
    });

    // === BATAL EDIT UKURAN → REFRESH ===
    $(document).on('click', '.cancel-ukuran-btn', function () {
        location.reload();
    });

    // ========================================
    // === EDIT TANGGAL PENYERAHAN ===
    // ========================================
    $(document).on('click', '.edit-serah-btn', function () {
        const container = $(this).closest('.serah-container');
        const id        = container.data('id');
        const field     = container.data('field');
        const current   = container.data('current') || '';

        const inputHtml = `
            <div class="input-group input-group-sm mt-1">
                <input type="date" class="form-control form-control-sm tanggal-input" value="${current}">
                <button class="btn btn-success btn-sm save-btn" data-id="${id}" data-field="${field}">
                    <i class="fas fa-check"></i>
                </button>
                <button class="btn btn-secondary btn-sm cancel-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        container.html(inputHtml);
        container.find('.tanggal-input').focus();
    });

    // === SIMPAN TANGGAL PENYERAHAN → AUTO REFRESH ===
    $(document).on('click', '.save-btn', function () {
        const btn      = $(this);
        const id       = btn.data('id');
        const field    = btn.data('field');
        const tanggal  = btn.closest('.input-group').find('.tanggal-input').val();

        $.ajax({
            url: '{{ route("penerimaan.update-tanggal-penyerahan") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id,
                field: field,
                tanggal: tanggal || null
            },
            success: function(response) {
                if (response.success) {
                    alert('Tanggal penyerahan berhasil disimpan!');
                    location.reload(); // Refresh halaman otomatis
                } else {
                    alert('Gagal menyimpan tanggal penyerahan.');
                }
            },
            error: function() {
                alert('Error koneksi atau server.');
            }
        });
    });

    // === BATAL EDIT TANGGAL → REFRESH ===
    $(document).on('click', '.cancel-btn', function () {
        location.reload();
    });
});
</script>
@endpush

