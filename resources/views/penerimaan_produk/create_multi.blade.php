@extends('layouts.app')

@section('title', 'Tambah Multi Penerimaan Produk')

@section('content')
    <div class="container-fluid py-4">
        <h1 class="mb-4 fw-bold text-center text-primary">Tambah Penerimaan Produk</h1>

        <form action="{{ route('penerimaan_produk.store_multi') }}" method="POST">
            @csrf

            {{-- ===== DATA MASTER STA & STPB ===== --}}
            @if(!empty($produkSTA))
                <input type="hidden" id="sta-data" data-kode="{{ $produkSTA->kode }}" data-jenis="{{ $produkSTA->jenis }}"
                    data-kategori="{{ $produkSTA->kategori }}" data-nama="{{ $produkSTA->nama_produk }}"
                    data-satuan="{{ $produkSTA->satuan }}" data-harga="{{ $produkSTA->harga }}"
                    data-status="{{ $produkSTA->status }}" data-isi="{{ $produkSTA->isi }}">
            @endif

            @if(!empty($produkSTPB))
                <input type="hidden" id="stpb-data" data-kode="{{ $produkSTPB->kode }}" data-jenis="{{ $produkSTPB->jenis }}"
                    data-kategori="{{ $produkSTPB->kategori }}" data-nama="{{ $produkSTPB->nama_produk }}"
                    data-satuan="{{ $produkSTPB->satuan }}" data-harga="{{ $produkSTPB->harga }}"
                    data-status="{{ $produkSTPB->status }}" data-isi="{{ $produkSTPB->isi }}">
            @endif

            <!-- Data Umum -->
            <div class="card mb-4 shadow-lg border-0 rounded-4">
                <div class="card-header bg-gradient bg-primary text-white py-3 rounded-top-4">
                    <h5 class="mb-0 text-center">Data Penerimaan</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4 align-items-end">

                        <div class="col-md-4">
                            <label class="form-label fw-bold">No. PO <span class="text-danger">*</span></label>
                            <input type="text" name="faktur" class="form-control form-control-lg rounded-3 shadow-sm"
                                value="{{ old('faktur') }}" placeholder="Contoh: FK/2026/001" required autofocus>
                            @error('faktur') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Unit biMBA <span class="text-danger">*</span></label>
                            @if (auth()->check() && (auth()->user()->is_admin ?? false))
                                <!-- Admin: dropdown tetap -->
                                <select name="unit_id" id="unit_id" class="form-select form-control-lg rounded-3 shadow-sm" required
                                        onchange="filterProduk()">
                                    <option value="">-- Pilih Unit --</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->no_cabang }} | {{ strtoupper($unit->biMBA_unit) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            @else
                                <!-- Non-admin: langsung pakai unit login -->
                                @php
                                    $userUnit = auth()->user()->bimba_unit ?? null;
                                    $unitDisplay = 'Unit belum diatur';
                                    $defaultUnitId = '';
                                    if ($userUnit) {
                                        $unit = \App\Models\Unit::where('biMBA_unit', $userUnit)->first();
                                        if ($unit) {
                                            $unitDisplay = $unit->no_cabang . ' | ' . strtoupper($unit->biMBA_unit);
                                            $defaultUnitId = $unit->id;
                                        }
                                    }
                                @endphp
                                <input type="hidden" name="unit_id" id="unit_id" value="{{ old('unit_id', $defaultUnitId) }}" required>
                                <input type="text" class="form-control form-control-lg rounded-3 shadow-sm bg-light text-center fw-bold"
                                       value="{{ $unitDisplay }}" readonly>
                                @if (!$defaultUnitId)
                                    <small class="text-danger d-block mt-1">Hubungi admin untuk mengatur unit Anda.</small>
                                @endif
                                @error('unit_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            @endif
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tanggal Penerimaan <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control form-control-lg rounded-3 shadow-sm"
       value="{{ $selectedTanggal ?? now()->format('Y-m-d') }}" required
       onblur="filterProduk()"
       onkeydown="if(event.key === 'Enter') filterProduk();">
                            @error('tanggal') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Info Message -->
                    @if(isset($infoMessage))
                        <div class="alert {{ $produks->isEmpty() ? 'alert-warning' : 'alert-info' }} alert-dismissible fade show mt-4 rounded-3 shadow-sm">
                            <strong><i class="fas fa-info-circle me-2"></i>Info:</strong> {{ $infoMessage }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tabel Produk -->
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-gradient bg-success text-white d-flex justify-content-between align-items-center py-3 rounded-top-4">
                    <h5 class="mb-0">Daftar Produk yang Diterima</h5>
                    <button type="button" id="tambah-baris" class="btn btn-light btn-sm rounded-pill px-4 shadow-sm">
                        <i class="fas fa-plus me-2"></i> Tambah Baris
                    </button>
                </div>
                <div class="card-body p-2">
    <div class="table-responsive" style="overflow-x:auto;">
        <table class="table table-bordered table-hover align-middle mb-0" id="tabel-produk" style="min-width:1400px;">
            
            <thead class="table-light text-center">
                <tr>
                    <th style="min-width:220px">Produk</th>
                    <th style="min-width:120px">Jenis</th>
                    <th style="min-width:120px">Kategori</th>
                    <th style="min-width:120px">Label</th>
                    <th style="min-width:80px">Order</th>
                    <th style="width:100px">Diterima</th>
                    <th style="width:100px">Satuan</th>
                    <th style="min-width:130px">Harga</th>
                    <th style="min-width:140px">Total</th>
                    <th style="min-width:120px">Status</th>
                    <th style="min-width:50px">Isi</th>
                    <th style="width:60px">Aksi</th>
                </tr>
            </thead>

            <tbody>
                <tr class="baris-produk align-middle">

                    <td>
                        <select name="items[0][label]" 
                            class="form-select form-select-sm label-select" required>
                            @if($produks->isEmpty())
                                <option value="">-- Tidak ada produk --</option>
                            @else
                                <option value="">-- Pilih Produk --</option>
                                @foreach($produks as $produk)
                                <option value="{{ $produk['kode'] }}"
                                    data-order="{{ $produk['order_qty'] ?? 0 }}"
                                    data-jenis="{{ $produk['jenis'] }}"
                                    data-kategori="{{ $produk['kategori'] ?? '' }}"
                                    data-nama="{{ $produk['nama_produk'] ?? '' }}"
                                    data-satuan="{{ $produk['satuan'] ?? 'Set' }}"
                                    data-harga="{{ $produk['harga'] ?? 0 }}"
                                    data-status="{{ $produk['status'] ?? '' }}"
                                    data-isi="{{ $produk['isi'] ?? '' }}">
                                    {{ $produk['nama_produk'] ?? $produk['label'] }}
                                </option>
                                @endforeach
                            @endif
                        </select>
                    </td>

                    <td><input type="text" name="items[0][jenis]" class="form-control form-control-sm bg-light jenis-field" readonly></td>

                    <td><input type="text" name="items[0][kategori]" class="form-control form-control-sm bg-light kategori-field" readonly></td>

                    <td><input type="text" name="items[0][nama_produk]" class="form-control form-control-sm bg-light nama-field" readonly></td>

                    <td>
                        <input type="number" class="form-control form-control-sm text-center bg-light order-field" value="0">
                    </td>

                    <td>
                        <input type="number" name="items[0][jumlah]" 
                            class="form-control form-control-sm text-end jumlah-field" 
                            value="0" min="0">
                    </td>

                    <td>
                        <input type="text" name="items[0][satuan]" 
                            class="form-control form-control-sm bg-light satuan-field" readonly>
                    </td>

                    <td>
                        <input type="text" name="items[0][harga]" 
                            class="form-control form-control-sm text-end bg-light harga-field" readonly>
                        <small class="text-muted d-block text-end harga-satuan-display"></small>
                    </td>

                    <td class="text-end fw-bold text-success total-row-display">
                        Rp 0
                    </td>

                    <td class="text-center">
                        <span class="badge bg-secondary status-badge">-</span>
                        <input type="hidden" name="items[0][status]" class="status-field">
                    </td>

                    <td>
                        <input name="items[0][isi]" 
                            class="form-control form-control-sm bg-light isi-field" 
                            rows="2" readonly>
                    </td>

                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm hapus-baris">
                            ✕
                        </button>
                    </td>

                </tr>
            </tbody>

            <tfoot class="bg-light">
                <tr>
                    <td colspan="8" class="text-end fw-bold">GRAND TOTAL</td>
                    <td class="text-end fw-bold text-primary" id="grand-total">Rp 0</td>
                    <td colspan="3"></td>
                </tr>
                <tr>
                    <td colspan="10" class="text-end">Jumlah Baris</td>
                    <td class="text-center fw-bold" id="total-item">1</td>
                </tr>
            </tfoot>

        </table>
    </div>
</div>
            </div>

            <!-- Section Sertifikat Murid -->
            @if($sertifikatPending->isNotEmpty())
                <!-- kode sertifikat sama seperti sebelumnya -->
            @else
                <div class="alert alert-info mt-5 text-center shadow-sm rounded-3">
                    Tidak ada pemesanan sertifikat yang belum diterima untuk periode ini.
                </div>
            @endif

            <div class="mt-5 text-end">
                <a href="{{ route('penerimaan_produk.index') }}"
                    class="btn btn-outline-secondary btn-lg px-5 me-3 rounded-3 shadow-sm">
                    <i class="fas fa-arrow-left me-2"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary btn-lg px-5 rounded-3 shadow-lg">
                    <i class="fas fa-save me-2"></i> Simpan Semua Data
                </button>
            </div>
        </form>
    </div>

    <!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

   <script>
/* ================= UTIL ================= */
let rowIndex = 1;   // ← dipindah ke atas agar tidak duplikat

/* ================= SELECT2 INIT ================= */
function initSelect2() {
    // 🔥 FIX UTAMA: Destroy dulu semua Select2 sebelum init ulang
    $('.label-select').select2('destroy');

    $('.label-select').select2({
        placeholder: "-- Pilih atau Ketik Nama Produk --",
        allowClear: true,
        width: '100%',
        language: {
            noResults: function() {
                return "Produk tidak ditemukan";
            }
        }
    }).on('change', function() {
        isiOtomatis(this);
        updateAllDropdowns();
    });
}

/* ================= ROW HANDLER ================= */
document.getElementById('tambah-baris').addEventListener('click', () => {
    const tbody = document.querySelector('#tabel-produk tbody');
    const template = tbody.querySelector('.baris-produk');
    const newRow = template.cloneNode(true);

    newRow.querySelectorAll('input, select, textarea').forEach(el => {
        el.value = '';
        if (el.classList.contains('order-field')) el.value = 0;
        if (el.classList.contains('jumlah-field')) el.value = 0;

        if (el.name) el.name = el.name.replace(/\[\d+\]/, `[${rowIndex}]`);
    });

    newRow.querySelector('.harga-satuan-display').textContent = '';
    newRow.querySelector('.total-row-display').textContent = 'Rp 0';

    tbody.appendChild(newRow);
    rowIndex++;

    document.getElementById('total-item').textContent =
        document.querySelectorAll('.baris-produk').length;

    updateGrandTotal();
    updateAllDropdowns();

    // Inisialisasi Select2 pada baris baru
    initSelect2();
});

/* ================= INIT SELECT2 SAAT LOAD ================= */
document.addEventListener('DOMContentLoaded', () => {
    updateGrandTotal();
    updateAllDropdowns();
    initSelect2();   // ← Sudah benar

    // Hanya trigger filterProduk SAAT PERTAMA KALI LOAD...
    const currentParams = new URLSearchParams(window.location.search);
    if (!currentParams.has('unit_id') && !currentParams.has('tanggal')) {
        const unitEl = document.querySelector('[name="unit_id"]');
        if (unitEl && unitEl.value) {
            filterProduk();
        }
    }
});

function formatAngka(angka) {
    if (!angka || angka == 0) return '0';
    return parseInt(angka).toLocaleString('id-ID');
}

function formatRupiah(angka) {
    return 'Rp ' + formatAngka(angka);
}

/* ================= HITUNG TOTAL ================= */
function hitungTotalRow(row) {
    const jumlah = parseInt(row.querySelector('.jumlah-field').value) || 0;
    const harga  = parseInt(row.querySelector('.harga-field').value) || 0;
    const total  = jumlah * harga;

    row.querySelector('.total-row-display').textContent = formatRupiah(total);
    updateGrandTotal();
}

function updateGrandTotal() {
    let grand = 0;
    document.querySelectorAll('.baris-produk').forEach(row => {
        const jumlah = parseInt(row.querySelector('.jumlah-field').value) || 0;
        const harga  = parseInt(row.querySelector('.harga-field').value) || 0;
        grand += jumlah * harga;
    });
    document.getElementById('grand-total').textContent = formatRupiah(grand);
}

/* ================= ISI OTOMATIS ================= */
function isiOtomatis(selectElement) {
    const row    = selectElement.closest('tr');
    const option = selectElement.options[selectElement.selectedIndex];

    row.querySelector('.jenis-field').value   = '';
    row.querySelector('.kategori-field').value= '';
    row.querySelector('.nama-field').value    = '';
    row.querySelector('.satuan-field').value  = '';
    row.querySelector('.harga-field').value   = '';
    row.querySelector('.status-field').value  = '';
    row.querySelector('.isi-field').value     = '';
    row.querySelector('.order-field').value   = 0;
    row.querySelector('.jumlah-field').value  = 0;
    row.querySelector('.harga-satuan-display').textContent = '';
    row.querySelector('.total-row-display').textContent = 'Rp 0';

    if (!option.value) {
        updateGrandTotal();
        return;
    }

    const orderQty = parseInt(option.dataset.order || 0);

    const data = {
        jenis: option.dataset.jenis || '',
        kategori: option.dataset.kategori || '',
        nama: option.dataset.nama || '',
        satuan: option.dataset.satuan || 'Set',
        harga: Math.round(parseFloat(option.dataset.harga || 0)),
        status: option.dataset.status || '',
        isi: option.dataset.isi || ''
    };

    row.querySelector('.jenis-field').value    = data.jenis;
    row.querySelector('.kategori-field').value = data.kategori;
    row.querySelector('.nama-field').value     = data.nama;
    row.querySelector('.satuan-field').value   = data.satuan;
    row.querySelector('.harga-field').value    = data.harga;
    row.querySelector('.status-field').value   = data.status;
    row.querySelector('.status-badge').textContent = data.status.toUpperCase();
    row.querySelector('.isi-field').value      = data.isi;
    row.querySelector('.order-field').value    = orderQty;

    row.querySelector('.jumlah-field').value = 0;
    row.querySelector('.harga-satuan-display').textContent = formatAngka(data.harga);

    hitungTotalRow(row);
}

/* ================= FILTER PAGE ================= */
function filterProduk() {
    const unitEl = document.querySelector('[name="unit_id"]');
    const tanggalEl = document.querySelector('[name="tanggal"]');

    const unitId = unitEl ? unitEl.value.trim() : '';
    const tanggal = tanggalEl ? tanggalEl.value.trim() : '';

    if (!unitId) {
        console.warn('Unit ID tidak ditemukan, tidak bisa filter');
        return;
    }

    const params = new URLSearchParams(window.location.search);

    let needReload = false;

    if (unitId && params.get('unit_id') !== unitId) {
        params.set('unit_id', unitId);
        needReload = true;
    }
    if (tanggal && params.get('tanggal') !== tanggal) {
        params.set('tanggal', tanggal);
        needReload = true;
    }

    if (needReload) {
        window.location.search = params.toString();
    }
}

/* ================= ROW HANDLER (HAPUS YANG DUPLIKAT) ================= */
document.addEventListener('click', e => {
    if (e.target.closest('.hapus-baris')) {
        if (document.querySelectorAll('.baris-produk').length > 1) {
            e.target.closest('tr').remove();
            document.getElementById('total-item').textContent =
                document.querySelectorAll('.baris-produk').length;
            updateGrandTotal();
            updateAllDropdowns();
        } else {
            alert('Minimal harus ada 1 baris produk!');
        }
    }
});

/* ================= DROPDOWN UNIQUE ================= */
function updateAllDropdowns() {
    const allSelects = document.querySelectorAll('.label-select');
    const selectedValues = [];

    allSelects.forEach(select => {
        if (select.value) selectedValues.push(select.value);
    });

    allSelects.forEach(select => {
        const currentValue = select.value;
        const options = select.querySelectorAll('option');

        options.forEach(option => {
            if (option.value && option.value !== currentValue) {
                option.style.display = selectedValues.includes(option.value) ? 'none' : '';
            } else {
                option.style.display = '';
            }
        });
    });
}

/* ================= EVENT GLOBAL ================= */
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('label-select')) {
        isiOtomatis(e.target);
        updateAllDropdowns();
    }
});

document.addEventListener('input', function (e) {
    if (e.target.classList.contains('jumlah-field')) {
        const row = e.target.closest('tr');
        hitungTotalRow(row);
    }
});
</script>
@endsection