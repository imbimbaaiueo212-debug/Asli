@extends('layouts.app')

@section('title', 'Tambah Order Modul')

@section('content')
<div class="container py-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Tambah Order Modul</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('order_modul.store') }}" method="POST" id="orderForm">
                @csrf

                <!-- UNIT -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Unit</label>

                    @if(auth()->check() && (auth()->user()->is_admin ?? false))
                        <!-- ADMIN -->
                        <select name="unit_id" id="unit_id" class="form-select" required>
                            <option value="">-- Pilih Unit --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">
                                    {{ $unit->no_cabang }} | {{ $unit->biMBA_unit }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <!-- USER -->
                        @php
                            $userUnit = auth()->user()->bimba_unit ?? null;
                            $unit = \App\Models\Unit::where('biMBA_unit', $userUnit)->first();
                        @endphp

                        <!-- PENTING: tetap ada id -->
                        <input type="hidden" name="unit_id" id="unit_id" value="{{ $unit->id ?? '' }}">

                        <input type="text"
                               class="form-control bg-light fw-bold"
                               value="{{ $unit ? $unit->no_cabang.' | '.$unit->biMBA_unit : 'Unit tidak ditemukan' }}"
                               readonly>

                        @if(!$unit)
                            <small class="text-danger">Unit belum disetting</small>
                        @endif
                    @endif
                </div>

                <!-- TANGGAL -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Tanggal Order</label>
                    <input type="date" name="tanggal_order" id="tanggal_order"
                           class="form-control"
                           value="{{ now()->format('Y-m-d') }}"
                           required>
                </div>

                <hr>

                <!-- TABLE -->
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="produkTable">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width:30%">Produk</th>
                                <th width="120">Jumlah</th>
                                <th width="150">Harga</th>
                                <th width="150">Total</th>
                                <th width="120">Status</th>
                                <th width="60"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- BUTTON -->
                <div class="d-flex justify-content-between mt-3">
                    <button type="button" id="addRow" class="btn btn-success">
                        + Tambah Produk
                    </button>

                    <h5>Total: Rp <span id="grandTotal">0</span></h5>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        Simpan Order
                    </button>
                    <button type="button" class="btn btn-danger" id="cancelButton">
                        Batal
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let produkList = [];

/* ================= FORMAT ================= */
function formatRupiah(angka) {
    return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

/* ================= LOAD PRODUK ================= */
function loadProduk() {
    let unit = $('#unit_id').val();

    if (!unit) return;

    $.get("{{ route('order_modul.produks_by_unit') }}", { unit_id: unit }, function(res) {

        produkList = res.produks || [];

        // RESET TABLE (PENTING)
        $('#produkTable tbody').html('');

        // kalau ada produk → tambah row
        if (produkList.length > 0) {
            addRow();
        } else {
            $('#produkTable tbody').html(`
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        Tidak ada produk untuk unit ini
                    </td>
                </tr>
            `);
        }
    });
}

/* ================= TAMBAH ROW ================= */
function addRow() {

    if (produkList.length === 0) return;

    let row = `
    <tr>
        <td>
            <select name="produk[]" class="form-select produk">
                <option value="">Pilih Produk</option>
                ${produkList.map(p => `
                    <option value="${p.label}" data-harga="${p.harga}">
                        ${p.label}
                    </option>
                `).join('')}
            </select>
        </td>

        <td>
            <input type="number" name="jumlah[]" 
                class="form-control jumlah text-end" value="0" min="0">
        </td>

        <td class="harga text-end">0</td>
        <td class="total text-end">0</td>
        <td class="status text-center">-</td>

        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm hapus">X</button>
        </td>
    </tr>`;

    $('#produkTable tbody').append(row);
}

/* ================= HITUNG ================= */
function hitung() {
    let grand = 0;

    $('#produkTable tbody tr').each(function() {
        let harga = parseInt($(this).find('.produk option:selected').data('harga')) || 0;
        let qty   = parseInt($(this).find('.jumlah').val()) || 0;
        let total = harga * qty;

        $(this).find('.harga').text(formatRupiah(harga));
        $(this).find('.total').text(formatRupiah(total));

        grand += total;
    });

    $('#grandTotal').text(formatRupiah(grand));
}

/* ================= EVENTS ================= */
$(document).on('change keyup', '.produk, .jumlah', hitung);

$(document).on('click', '.hapus', function() {
    $(this).closest('tr').remove();
    hitung();
});

$('#addRow').click(addRow);

$('#unit_id').change(loadProduk);

$('#cancelButton').click(function() {
    if (confirm('Yakin ingin membatalkan?')) {
        window.location.href = "{{ route('order_modul.index') }}";
    }
});

/* ================= AUTO LOAD SAAT MASUK ================= */
$(document).ready(function() {
    let unit = $('#unit_id').val();

    if (unit) {
        loadProduk(); // 🔥 ini kunci utama
    }
});
</script>
@endpush