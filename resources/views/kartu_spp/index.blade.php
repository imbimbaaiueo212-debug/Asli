@extends('layouts.app')

@section('title', 'Kartu SPP')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .table thead th {
        font-size: 0.9rem;
        background-color: #f8f9fa;
    }
    .table tbody td {
        font-size: 0.85rem;
        vertical-align: middle;
    }
    #kartuSpp {
        transition: all 0.3s ease;
    }
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
</style>
@endpush

@section('content')
<div class="card card-body">
    <h1 class="mb-4 text-center fw-bold">Kartu SPP Murid</h1>

    {{-- FILTER UNIT + BULAN + TAHUN --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body bg-light">
            <form method="GET" action="{{ route('kartu-spp.index') }}" id="filterForm">
                <div class="row g-3 align-items-end">

                    {{-- Filter Unit (Admin Only) --}}
                    @if(auth()->user()?->is_admin)
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Unit biMBA</label>
                        <select name="unit" id="unitFilter" class="form-select">
                            <option value="">-- Semua Unit --</option>
                            @foreach($unitOptions as $unit)
                                <option value="{{ $unit }}" {{ request('unit') == $unit ? 'selected' : '' }}>
                                    {{ $unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Filter Bulan --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Bulan</label>
                        <select name="bulan" id="bulanFilter" class="form-select">
                            <option value="">-- Semua Bulan --</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ request('bulan') == $i ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::create(0, $i)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    {{-- Filter Tahun --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Tahun</label>
                        <select name="tahun" id="tahunFilter" class="form-select">
                            <option value="">-- Semua Tahun --</option>
                            @for($y = now()->year - 3; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Terapkan Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- PILIH MURID --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <label class="form-label fw-bold">Pilih Murid</label>
            <select id="selectNIM" class="form-select">
                <option value="">-- Pilih NIM atau Nama Murid --</option>
                @foreach($murid as $m)
                    <option value="{{ $m->nim }}">{{ $m->nim }} | {{ $m->nama }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- KARTU SPP --}}
    <div id="kartuSpp" class="card shadow-lg" style="display:none;">
        <div class="card-header text-center bg-primary text-white">
            <h5 class="mb-0">biMBA-AIUEO | <span id="headerBulan">{{ now()->translatedFormat('F Y') }}</span></h5>
        </div>
        <div class="card-body">
            <div class="text-center mb-4">
                <h4 class="fw-bold">Kartu SPP Murid</h4>
            </div>

            <table class="table table-borderless">
                <tr><td><strong>NIM</strong></td><td id="nim" class="fw-bold"></td></tr>
                <tr><td><strong>Nama Murid</strong></td><td id="nama" class="fw-bold"></td></tr>
                <tr><td><strong>Golongan</strong></td><td id="golongan"></td></tr>
                <tr><td><strong>Tanggal Masuk</strong></td><td id="tanggal_masuk"></td></tr>
                <tr><td><strong>Pembayaran SPP</strong></td><td id="spp" class="fw-bold"></td></tr>
                <tr><td><strong>Status Bulan Ini</strong></td><td id="status" class="fw-bold"></td></tr>
            </table>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle border-primary">
                    <thead class="table-success">
                    <tr>
                        <th rowspan="2">Bulan</th>
                        <th rowspan="2">Status</th>
                        <th rowspan="2">Tgl. Transaksi</th>
                        <th colspan="3" class="text-center">Voucher</th>
                        <th rowspan="2" class="text-center">SPP (Rp)</th>
                    </tr>
                    <tr>
                        <th class="text-center">Jumlah</th>
                        <th class="text-center text-danger">Dipakai</th>
                        <th class="text-center text-success">Sisa</th>
                    </tr>
                    </thead>
                    <tbody id="riwayatBayar"></tbody>
                </table>
            </div>

            <div class="mt-4 text-center">
                <button id="btnPrintPdf" class="btn btn-success" style="display:none;">
                    <i class="fas fa-print me-2"></i> Cetak Kartu SPP ke PDF
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('#selectNIM').select2({
        placeholder: "-- Pilih NIM atau Nama Murid --",
        width: '100%',
        allowClear: true
    });

    $('#unitFilter').select2({
        width: '100%',
        placeholder: "-- Semua Unit --",
        allowClear: true
    });

    $('#unitFilter').on('change', function() {
        $('#filterForm').submit();
    });

    function loadKartuSpp(nim) {
        if (!nim) {
            $('#kartuSpp').hide();
            $('#btnPrintPdf').hide();
            return;
        }

        const bulan = $('#bulanFilter').val() || '';
        const tahun = $('#tahunFilter').val() || '';

        let url = `/kartu-spp/detail/${nim}`;
        if (bulan || tahun) {
            url += `?bulan=${bulan}&tahun=${tahun}`;
        }

        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error('Data tidak ditemukan');
                return res.json();
            })
            .then(data => {
                $('#nim').text(data.nim);
                $('#nama').text(data.nama);
                $('#golongan').text(data.golongan);
                $('#tanggal_masuk').text(data.tgl_masuk ?? '-');
                $('#spp').text('Rp ' + Number(data.spp || 0).toLocaleString('id-ID'));

                const statusEl = $('#status');
                statusEl.text(data.status_bayar);
                if (data.status_bayar.includes('Sudah bayar') || data.status_bayar.includes('Khusus')) {
                    statusEl.removeClass('text-danger').addClass('text-success');
                } else {
                    statusEl.removeClass('text-success').addClass('text-danger');
                }

                // === UPDATE HEADER BULAN (Diperbaiki) ===
                let headerText = new Date().toLocaleString('id-ID', { month: 'long', year: 'numeric' });
                if (bulan && tahun) {
                    const monthNames = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", 
                                      "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                    headerText = monthNames[parseInt(bulan)] + ' ' + tahun;
                }
                $('#headerBulan').text(headerText);

                // Render Riwayat
                let tbody = '';
                if (data.riwayat && data.riwayat.length > 0) {
                    data.riwayat.forEach(r => {
                        const statusClass = r.status === 'Sudah bayar' ? 'text-success fw-bold' : 'text-danger fw-bold';
                        const nominal = Number(r.jumlah || 0).toLocaleString('id-ID');

                        tbody += `
                        <tr>
                            <td>${r.bulan}</td>
                            <td class="${statusClass}">${r.status}</td>
                            <td>${r.tanggal_transaksi || '-'}</td>
                            <td class="text-center">${r.voucher_jumlah ?? '-'}</td>
                            <td class="text-center text-danger">${r.voucher_dipakai ?? '-'}</td>
                            <td class="text-center text-success">${r.voucher_sisa ?? '-'}</td>
                            <td class="text-end">Rp ${nominal}</td>
                        </tr>`;
                    });
                } else {
                    tbody = `<tr><td colspan="7" class="text-center text-muted">Belum ada data pembayaran untuk periode tersebut</td></tr>`;
                }
                $('#riwayatBayar').html(tbody);

                // Voucher Summary
                if (data.voucher_summary) {
                    const vs = data.voucher_summary;
                    $('#voucherJumlah').html(vs.jumlah + (vs.nominal_total ? ' <small class="text-muted">(' + vs.nominal_total + ')</small>' : ''));
                    $('#voucherDigunakan').text(vs.digunakan || 0);
                    $('#voucherSisa').html((vs.sisa || 0) + (vs.sisa_nominal ? ' <small class="text-muted">(' + vs.sisa_nominal + ')</small>' : ''));
                }

                $('#kartuSpp').show();
                $('#btnPrintPdf').show();

                // Cetak PDF
                $('#btnPrintPdf').off('click').on('click', function() {
                    let pdfUrl = `/kartu-spp/pdf/${nim}`;
                    if (bulan && tahun) pdfUrl += `?bulan=${bulan}&tahun=${tahun}`;
                    window.open(pdfUrl, '_blank');
                });

                $('html, body').animate({ scrollTop: $('#kartuSpp').offset().top - 100 }, 500);
            })
            .catch(err => {
                console.error(err);
                alert('Gagal memuat data kartu SPP: ' + err.message);
                $('#kartuSpp').hide();
                $('#btnPrintPdf').hide();
            });
    }

    $('#selectNIM').on('change', function() {
        loadKartuSpp($(this).val());
    });

    $('.btn-detail').on('click', function() {
        const nim = $(this).data('nim');
        $('#selectNIM').val(nim).trigger('change');
    });
});
</script>
@endpush

@endsection